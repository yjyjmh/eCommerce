<?php

require_once("config.php");
require_once("class_session.php");

session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
}

/* Get the parameters from the form */
$itemsid = $_POST['itemsid'];
$quantity = $_POST['quantity'];
$sel = $_POST['sel'];

try {
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB, USER, PW);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error_log = false;

include("static/header.html");
?>
<div id="center">
    <div id="bar">
        <?php include("logstatus.php"); ?>
    </div>
    <div id="navigation">
        <div id="pagenav">
            <?php
            /* Update the cart using the cart parameters */
            if (is_array($itemsid) && !empty($itemsid)) {
                try {
                    $pdo->beginTransaction();

                    foreach ($itemsid as $id) {
                        $q = $quantity[$id];
                        if (isset($sel[$id])) {
                            $s = $sel[$id];
                            $query = "SELECT * FROM cart WHERE sid=:username AND id_item=:id";
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->execute();

                            if ($stmt->rowCount() == 0) {
                                $query = "INSERT INTO cart (sid, id_item, quantity) VALUES (:username, :id, :quantity)";
                            } else {
                                $query = "UPDATE cart SET quantity=quantity+:quantity WHERE sid=:username AND id_item=:id";
                            }

                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt->bindParam(':quantity', $q, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }

                    $pdo->commit();
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    die("Error: " . $e->getMessage());
                }
            }

            /* Login error */
            if ($error_log == true) { ?>
                <a href="../index.php">Go back to the Login page</a>
            <?php
            } else {
                header('Location: store.php');
            }
            ?>
        </div>
    </div>
</div>
<?php
include("static/footer.html");
?>
