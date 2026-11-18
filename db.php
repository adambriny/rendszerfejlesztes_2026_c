<?php
session_start();

$host = 'localhost';$dbname = 'fitlog_db';
$username = 'root';$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
}

function bejelentkezesEllenorzes() {
    if (!isset($_SESSION['felhasznalo_id'])) {
        header("Location: index.php");
        exit;
    }
}
?>