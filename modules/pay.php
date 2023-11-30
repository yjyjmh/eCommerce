<?php

require_once("config.php");
require_once("class_session.php");

session_start();

try {
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB, USER, PW);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

try {
    $pdo->beginTransaction();

    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: ../index.php');
    }

    $total = $_POST['total'];
    $cardnumber = $_POST['cardnumber'];
    $secretcode = $_POST['secretcode'];
    $expiration = $_POST['expiration'];

    $error_log = false;
    $error_flag = false;

    /* Check the payment form parameters */
    if ($cardnumber == "" || $secretcode == "" || $expiration == "") {
        echo "One or more payment fields are empty!";
        $error_flag = true;
    } elseif (!preg_match("/^([0-9]{16})$/", $cardnumber) || !preg_match("/^([0-9]{3})$/", $secretcode)) {
        echo "One or more fields are wrong!";
        $error_flag = true;
    }

    /* Operation Error */
    if ($error_flag == true) {
        echo "<br><a href='showcart.php'>Go back to the Cart</a>";
    } else {
        $stmt = $pdo->prepare("LOCK TABLES products WRITE, cart WRITE");
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT * FROM cart WHERE sid = :username");
        $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
        $stmt->execute();

        $not_av_flag = false;
        $not_av = [];

        while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $record['id_item'];
            $q = $record['quantity'];

            $stmt2 = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();

            $record2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $number = $record2['number'];

            /* The products you've chosen are no longer available or are not present in the needed amount */
            if ($number < $q) {
                $not_av_flag = true;
                $not_av[] = $id;
            }
        }

        if ($not_av_flag) {
            $pdo->commit();
            $stmt = $pdo->prepare("UNLOCK TABLES");
            $stmt->execute();
            echo "Sorry, the products you've chosen are no longer available or are not present in the amount of your choice!";
            echo "<br><a href='store.php'>Go back to the store</a>";
        } else {
            $random_pay = rand(1, 10);
            /* SIMULATING PAYMENT - UNSUCCESSFUL TRANSATION */
            if ($random_pay > 5) {
                $stmt = $pdo->prepare("UNLOCK TABLES");
                $stmt->execute();
                echo "Sorry, Payment Unsuccessful!";
                echo "<br><a href='showcart.php'>Go back to the Cart</a>";
            } else {
                $stmt = $pdo->prepare("SELECT * FROM cart INNER JOIN products ON cart.id_item=products.id WHERE sid = :username");
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();

                while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $n = $record['quantity'];
                    $id = $record['id_item'];

                    /* Decrease the product number from the store */
                    $stmt3 = $pdo->prepare("UPDATE products SET number = number - :quantity WHERE id = :id");
                    $stmt3->bindParam(':quantity', $n, PDO::PARAM_INT);
                    $stmt3->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt3->execute();
                }

                /* ALL OPERATIONS SUCCESSFUL */
                $stmt4 = $pdo->prepare("DELETE FROM cart WHERE sid = :username");
                $stmt4->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt4->execute();

                $stmt5 = $pdo->prepare("UNLOCK TABLES");
                $stmt5->execute();

                echo "Payment Successful!";
                echo "<br><a href='store.php'>Go back to the Store</a> if you want to buy something else...";
            }
        }
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}

include("static/footer.html");
?>
