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

$error_flag = false;

include("static/header.html");

?>
<div id="center">
    <div id="bar">
        <div id="login">
            <b>Login</b><br><br>
            <?php
            if(!empty($_POST) && !isset($_SESSION['username'])) {
                $username = $_POST['username'];
                $password = $_POST['password'];

                /* LIMIT 1: stop searching if you find a match */
                $query = "SELECT * FROM users WHERE username=:username AND password=:password LIMIT 1";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->execute();

                if(!$stmt->rowCount()) {
                    echo "Wrong username or password!";
                    $error_flag = true;
                }
                else {
                    /* Setup the SESSION */
                    $_SESSION['username'] = $username;
                    print "Logged as " .$_SESSION['username']; ?>
                    <br><br>
                    <a href="store.php?logout">Logout</a>
                <?php
                }
            }
            else if(isset($_SESSION['username'])) {
                /* Add Inclusion feature to include any newly added pages in store */ 
                if(isset($_GET['page'])) {?>
                    <div><?php
                    include($_GET['page'] . '.php');
                } ?>
            </div>
            <?php
                print "Logged as " .$_SESSION['username']; ?>
                <br><br>
                <a href="store.php?logout">Logout</a>
            <?php
            }
            else {
                session_regenerate_id();
                echo "Session Expired!";
                $error_flag = true;
            }
            ?>
        </div>
        <div id="cart">
            <b>Cart</b><br><br>
            <?php
            $query = "SELECT * FROM cart INNER JOIN products ON cart.id_item=products.id WHERE sid=:username";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount() == 0) {
                echo "The cart is empty";
            }
            else {
                $t = 0;
                while($record = $stmt->fetch(PDO::FETCH_ASSOC)) {?>
                    <table>
                        <tr>
                            <tr><?php print $record['item']. " [" .$record['quantity']. "]";?></tr>
                        </tr>
                    </table>
                    <?php
                    $t += $record['prize'] * $record['quantity'];
                }?>
                <br><b><i>Total:</i></b>
                <?php
                print " " .$t;?>&euro;<br>
                <br><hr><br>
                <b><i><a href="showcart.php">Show Cart</a></i></b>
                <?php
            }
            ?>
        </div>
    </div>
    <div id="navigation">
        <div id="pagenav">
            <?php 
            if($error_flag == true) { ?>
                <a href="../index.php">Go back to the Login page</a>
            <?php 
            }
            else { ?>
                <b>Store</b><br><br>
                <table>
                    <tr>
                        <td><b><i>Product</i></b></td>
                        <td><b><i>Price</i></b></td>
                        <td><b><i>Quantity</i></b></td>
                        <td><b><i>Select</i></b></td>
                    </tr>
                <?php    
                /* Database query for the products */
                $query = "SELECT * FROM products";
                $products = $pdo->query($query);

                if (!$products) {
                    die("Query error  $query: " . $pdo->errorInfo());
                }
                
                while($product = $products->fetch(PDO::FETCH_ASSOC)) { ?>    
                    <table>
                        <tr>
                            <td><?php print $product['item'];?></td>
                            <td><?php print $product['prize'];?> &euro;</td>
                            <td>
                                <form action="addcart.php" method="post">
                                    <input type="text" name="quantity[<?php print $product['id'];?>]" value="1" size="3">
                                    <input type="hidden" name="itemsid[]" value="<?php print $product['id'];?>">
                            </td>
                            <td><input type="checkbox" name="sel[<?php print $product['id'];?>]" value="<?php print $product['item']?>"></td>
                        </tr>
                    </table>
                <?php
                }
            }
            if($error_flag == false) { ?>
            <br>
            <input type="submit" value="Add to Cart">
            </form>
            <?php } ?>
        </div>
    </div>
</div>
<?php

include("static/footer.html");

?>
