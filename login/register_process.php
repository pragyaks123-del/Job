<?php
session_start(); 
require_once '../config/config.php'; 

if (isset($_POST['register'])) {
    // 1. Capture and Sanitize Basic Information
    $fullname  = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']); 
    $password  = $_POST['password'];

    // 2. Capture and Sanitize Profile Details
    $education    = mysqli_real_escape_string($conn, trim($_POST['education']));
    $interests    = mysqli_real_escape_string($conn, trim($_POST['interests']));
    $achievements = mysqli_real_escape_string($conn, trim($_POST['achievements']));
    $skills       = isset($_POST['skills']) ? mysqli_real_escape_string($conn, trim($_POST['skills'])) : '';
    $experience   = isset($_POST['experience']) ? mysqli_real_escape_string($conn, trim($_POST['experience'])) : '';

    // 3. Security: Password Hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 4. Duplicate Email Check
    $check_email = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
    $run_check = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($run_check) > 0) {
        echo "<script>alert('This email is already registered!'); window.history.back();</script>";
        exit();
    } else {
        // 5. Insert Query
        $insert_sql = "INSERT INTO users (fullname, email, password, user_type, skills, experience, education, interests, achievements) 
                       VALUES ('$fullname', '$email', '$hashed_password', '$user_type', '$skills', '$experience', '$education', '$interests', '$achievements')";
        
        if (mysqli_query($conn, $insert_sql)) {
            // 6. Get the new user ID for the session
            $new_user_id = mysqli_insert_id($conn); 

            // 7. Set Login Sessions
            $_SESSION['user_id']   = $new_user_id;
            $_SESSION['fullname']  = $fullname;
            $_SESSION['user_type'] = $user_type;

            // 8. THE FINAL PATH FIX
            // Using ../ to go up from 'login/' then into 'profile/'
            echo "<script>
                alert('Registration Successful!');
                window.location.href = '../profile/profile.php'; 
            </script>";
            exit();
        } else {
            echo "Database Error: " . mysqli_error($conn);
        }
    }
} else {
    header("Location: register.php");
    exit();
}
?>