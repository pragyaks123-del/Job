<?php
// Compatibility wrapper: keep old and corrected filenames working.
require_once __DIR__ . '/databse.php';


$host   = 'localhost';
$dbname = 'job_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
   
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function getDB() {
    global $pdo;
    return $pdo;
}