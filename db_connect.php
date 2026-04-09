<?php
$host = '127.0.0.1'; 
$port = '3307';      
$db = 'job_portal';
$user = 'root';
$pass = '';

try {
<<<<<<< HEAD
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
=======
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
>>>>>>> e302e926df854f5b40e6cb70188cfc3e8409e7ba
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
<<<<<<< HEAD
?>
=======
?>
>>>>>>> e302e926df854f5b40e6cb70188cfc3e8409e7ba
