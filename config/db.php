<?php
$host = '127.0.0.1';
$db   = 'car_workshop';
$user = 'root';
$pass = '';        // αν δεν έχεις password στο XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\PDOException $e) {
    exit("Σφάλμα σύνδεσης: " . $e->getMessage());
}
