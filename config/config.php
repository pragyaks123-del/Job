<?php
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "jobhub";

$app_url = "http://localhost/Smart%20Job%20Application";
$mail_config = [
    'from_email' => 'jobhub.theproject@gmail.com',
    'from_name' => 'JobHub',
    'reply_to' => 'jobhub.theproject@gmail.com',
];

// Connection create garne
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
