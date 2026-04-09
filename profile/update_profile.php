<?php
session_start();
require_once '../config/config.php'; 

if (isset($_POST['update']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // 1. Collect and Sanitize Data
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $skills = mysqli_real_escape_string($conn, $_POST['skills']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $achievements = mysqli_real_escape_string($conn, $_POST['achievements']);
    $interests = mysqli_real_escape_string($conn, $_POST['interests']);

    // 2. Handle Profile Picture Upload
    $pic_query = "";
    if (!empty($_FILES['profile_pic']['name'])) {
        $file_name = time() . '_' . basename($_FILES['profile_pic']['name']);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $file_name;

        // Create uploads folder if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $pic_query = ", profile_pic = '$file_name'";
        }
    }

    // 3. Update Database
    $sql = "UPDATE users SET 
            fullname = '$fullname', 
            skills = '$skills', 
            experience = '$experience', 
            education = '$education', 
            achievements = '$achievements', 
            interests = '$interests' 
            $pic_query 
            WHERE id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        // SUCCESS: Redirect back to profile.php in the same folder
        header("Location: profile.php?status=success");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
} else {
    // If accessed directly without POST
    header("Location: profile.php");
    exit();
}
?>