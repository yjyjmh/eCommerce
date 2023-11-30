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

    $query = "DELETE FROM cart WHERE sid = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}

header('Location: store.php');
?>
