<?php
$host   = 'localhost';
$dbname = 'job_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
   
} catch (PDOException $e) {
    die("Exception: " . $e->getMessage());
}