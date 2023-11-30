<?php

require_once("modules/config.php");
require_once("modules/class_session.php");

$session_class = new Sessions();
session_start();

if (isset($_SESSION['username'])) {
    header('Location: modules/store.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DB, USER, PW);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

/* Database query for the products */
$query = "SELECT * FROM products";

try {
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query error $query: " . $e->getMessage());
}

include("modules/static/header.html");
?>

<div id="center">
    <?php
    include("modules/storewindow.php");
    include("modules/static/login.html");
    ?>
</div>

<?php
include("modules/static/footer.html");
?>
