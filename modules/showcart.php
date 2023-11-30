<?php

require_once("config.php");

try {
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB, USER, PW);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
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
            /* Login Error */
            if($error_log == true) { ?>
                <a href="../index.php">Go back to the Login page</a>
            <?php
            }
            else { ?>
                <b>Your Cart</b><br><br>
                <table>
                    <tr>
                        <td><b><i>Product</i></b></td>
                        <td><b><i>Quantity</i></b></td>
                    </tr>
                <?php
                $query = "SELECT * FROM cart INNER JOIN products ON cart.id_item=products.id WHERE sid=:username";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();

                $t = 0;

                while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {?>
                    <tr>
                        <td><?php print $record['item'];?></td>
                        <td><?php print $record['quantity'];?></td>
                    </tr>
                    <?php
                    $t += $record['prize'] * $record['quantity'];
                } ?>
                </table>
                <br><hr><br>
                <b><i>Total:</i></b> <?php print $t;?>&euro;<br>
                <br><hr><br>
                <form action="pay.php" method="post" enctype="application/x-www-form-urlencoded">
                    <input type="hidden" name="total" value="<?php print $t;?>">
                    <table>
                        <tr>
                            <td>Credit Card Number:</td>
                            <td><input type="text" name="cardnumber" size="30"></td>
                        </tr>
                        <tr>
                            <td>Secred Code:</td>
                            <td><input type="text" name="secretcode" size="5"></td>
                        </tr>
                        <tr>
                            <td>Expiration Date (mm/yy):</td>
                            <td><input type="text" name="expiration" size="5"></td>
                        </tr>
                        <tr>
                            <td><input type="submit" name="checkout" value="Checkout"></td>
                        </tr>
                    </table>
                </form><br>
                <form action="emptycart.php" method="post" enctype="application/x-www-form-urlencoded">
                    <input type="submit" name="emptycart" value="EmptyCart" />
                </form><br>
                <b><a href="store.php">Go back to the store</a></b>
                <?php
            } ?>
        </div>
    </div>
</div>
<?php

include("static/footer.html");

?>