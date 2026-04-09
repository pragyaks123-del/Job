<?php
// profile/profile.php
session_start();
require_once '../config/config.php'; 

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login/login.php?error=usernotfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['fullname']); ?> | Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --linkedin-blue: #0a66c2;
            --linkedin-blue-hover: #004182;
            --bg-gray: #f3f2ef;
            --border-gray: #e0e0e0;
            --text-main: rgba(0,0,0,0.9);
            --text-secondary: rgba(0,0,0,0.6);
            --danger: #d11a2a;
        }

        body {
            background-color: var(--bg-gray);
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text-main);
        }

        .main-wrapper {
            max-width: 1128px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .section-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid var(--border-gray);
            margin-bottom: 15px;
            overflow: hidden;
            position: relative;
        }

        .cover-photo {
            height: 200px;
            background: linear-gradient(135deg, #008080, #20b2aa);
        }

        .profile-pic-container {
            position: absolute;
            top: 120px;
            left: 24px;
            z-index: 5;
        }

        .profile-pic, .profile-pic-placeholder {
            width: 152px;
            height: 152px;
            border: 4px solid #fff;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .profile-pic-placeholder {
            background: #e1e9ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            color: #70b5f9;
        }

        .intro-details { padding: 85px 24px 24px 24px; position: relative; }
        .intro-details h1 { font-size: 24px; margin: 0; }
        .headline { font-size: 16px; color: var(--text-secondary); margin-top: 4px; }
        
        .button-group { 
            margin-top: 16px; 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            padding: 8px 24px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-primary { background: var(--linkedin-blue); color: white; }
        .btn-primary:hover { background: var(--linkedin-blue-hover); }
        
        .btn-danger { 
            color: var(--danger); 
            border-color: var(--danger);
            background: transparent;
        }
        .btn-danger:hover { background: #fff5f5; }

        .section-padding { padding: 24px; }
        .section-card h2 { font-size: 20px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .content-text { font-size: 15px; color: #444; line-height: 1.6; }
        
        .skills-container { display: flex; flex-wrap: wrap; gap: 10px; }
        .skill-tag {
            background: #e7f3ff;
            color: var(--linkedin-blue);
            padding: 6px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 600;
        }

        .empty-msg { color: var(--text-secondary); font-style: italic; }
    </style>
</head>
<body>

<div class="main-wrapper">

    <div class="section-card">
        <div class="cover-photo"></div>
        <div class="profile-pic-container">
            <?php 
            $photoPath = "../uploads/" . $user['profile_pic'];
            if(!empty($user['profile_pic']) && file_exists($photoPath)): 
            ?>
                <img src="<?php echo $photoPath; ?>" class="profile-pic" alt="Profile">
            <?php else: ?>
                <div class="profile-pic-placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="intro-details">
            <h1><?php echo htmlspecialchars($user['fullname']); ?></h1>
            <div class="headline"><?php echo ucfirst($user['user_type']); ?> | JobHub Professional Network</div>
            <div class="headline"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></div>
            
            <div class="button-group">
                <a href="edit_profile.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
                
                <a href="../login/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="section-card section-padding">
        <h2><i class="fas fa-trophy" style="color: #f1c40f;"></i> Achievements</h2>
        <div class="content-text">
            <?php echo !empty($user['achievements']) ? nl2br(htmlspecialchars($user['achievements'])) : "<span class='empty-msg'>No achievements added yet.</span>"; ?>
        </div>
    </div>

    <div class="section-card section-padding">
        <h2><i class="fas fa-briefcase" style="color: #7f8c8d;"></i> Professional Experience</h2>
        <div class="content-text">
            <?php echo !empty($user['experience']) ? nl2br(htmlspecialchars($user['experience'])) : "<span class='empty-msg'>Describe your work history here.</span>"; ?>
        </div>
    </div>

    <div class="section-card section-padding">
        <h2><i class="fas fa-university" style="color: #2980b9;"></i> Education</h2>
        <div class="content-text">
            <?php echo !empty($user['education']) ? nl2br(htmlspecialchars($user['education'])) : "<span class='empty-msg'>Add your school or university details.</span>"; ?>
        </div>
    </div>

    <div class="section-card section-padding">
        <h2><i class="fas fa-bolt" style="color: #e67e22;"></i> Skills</h2>
        <div class="skills-container">
            <?php 
            if (!empty($user['skills'])) {
                $skills = explode(',', $user['skills']);
                foreach ($skills as $skill) {
                    echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
                }
            } else {
                echo '<span class="empty-msg">No skills listed.</span>';
            }
            ?>
        </div>
    </div>

    <div class="section-card section-padding">
        <h2><i class="fas fa-heart" style="color: #e74c3c;"></i> Interests</h2>
        <div class="content-text">
            <?php echo !empty($user['interests']) ? htmlspecialchars($user['interests']) : "<span class='empty-msg'>What are you passionate about?</span>"; ?>
        </div>
    </div>

</div>

</body>
</html>