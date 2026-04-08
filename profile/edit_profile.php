<?php
session_start();
require_once '../config/config.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 3. Update profile logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fullname     = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);
    $achievements = mysqli_real_escape_string($conn, $_POST['achievements']);
    $experience   = mysqli_real_escape_string($conn, $_POST['experience']);
    $education    = mysqli_real_escape_string($conn, $_POST['education']);
    $skills       = mysqli_real_escape_string($conn, $_POST['skills']);
    $interests    = mysqli_real_escape_string($conn, $_POST['interests']);

    // Handle Profile Picture
    $profile_pic = $user['profile_pic']; // Default to old pic

    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $new_file_name = time() . "_" . $user_id . "." . $file_extension;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_dir . $new_file_name)) {
            $profile_pic = $new_file_name;
        }
    }

    // Update query using Prepared Statement
    $update = $conn->prepare("UPDATE users SET fullname=?, email=?, achievements=?, experience=?, education=?, skills=?, interests=?, profile_pic=? WHERE id=?");
    $update->bind_param("ssssssssi", $fullname, $email, $achievements, $experience, $education, $skills, $interests, $profile_pic, $user_id);

    if ($update->execute()) {
        header("Location: profile.php?status=success");
        exit();
    } else {
        $error = "Update failed! Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | JobHub</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container">
    <h2>
        <span>Edit Public Profile</span>
        <i class="fas fa-user-edit" style="color: var(--linkedin-blue);"></i>
    </h2>

    <?php if(isset($error)): ?>
        <p class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="profile-pic-preview">
            <?php if(!empty($user['profile_pic'])): ?>
                <img src="../uploads/<?php echo $user['profile_pic']; ?>" alt="Current Profile" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <i class="fas fa-user-circle" style="font-size: 50px; color: #ccc;"></i>
            <?php endif; ?>
            
            <div style="flex-grow: 1;">
                <label>Update Profile Photo</label>
                <input type="file" name="profile_pic" accept="image/*" style="margin-bottom: 0;">
            </div>
        </div>

        <label>Full Name</label>
        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Achievements</label>
        <textarea name="achievements" placeholder="List your major accomplishments..."><?php echo htmlspecialchars($user['achievements']); ?></textarea>

        <label>Professional Experience</label>
        <textarea name="experience" placeholder="Where have you worked before?"><?php echo htmlspecialchars($user['experience']); ?></textarea>

        <label>Education</label>
        <textarea name="education" placeholder="E.g. Bachelor in Computer Science..."><?php echo htmlspecialchars($user['education']); ?></textarea>

        <label>Skills (separate with commas)</label>
        <input type="text" name="skills" value="<?php echo htmlspecialchars($user['skills']); ?>" placeholder="PHP, MySQL, JavaScript, UI Design">

        <label>Interests</label>
        <textarea name="interests" style="min-height: 60px;"><?php echo htmlspecialchars($user['interests']); ?></textarea>

        <div style="overflow: hidden; border-top: 1px solid var(--border-color); padding-top: 20px;">
            <button type="submit">Save Changes</button>
        </div>
    </form>

    <div style="padding: 0 24px 24px;">
        <a href="profile.php"><i class="fas fa-arrow-left"></i> Back to Profile</a>
    </div>
</div>

</body>
</html>