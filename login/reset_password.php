<?php
require_once '../config/config.php';

$email = trim($_GET["email"] ?? $_POST["email"] ?? "");
$sent = isset($_GET["sent"]);
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["code"] ?? "");
    $pass = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter the same email address that received the verification code.";
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $error = "Enter the 6-digit verification code sent to your email.";
    } elseif ($pass !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $code_hash = hash("sha256", $code);
        $sql = "SELECT id, reset_token_expires_at FROM users WHERE email = ? AND reset_token_hash = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $code_hash);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            $error = "That verification code is invalid for this email address.";
        } elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
            $error = "That verification code has expired. Please request a new one.";
        } else {
            $new_hash = password_hash($pass, PASSWORD_DEFAULT);

            // Clear tokens after success so they can't be reused
            $update = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
            $u_stmt = $conn->prepare($update);
            $u_stmt->bind_param("si", $new_hash, $user['id']);

            if ($u_stmt->execute()) {
                header("Location: login.php?reset=success");
                exit();
            }

            $error = "We couldn't update your password right now. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | JobHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f2ef; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        h2 { font-size: 22px; text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #0a66c2; color: white; border: none; padding: 12px; width: 100%; border-radius: 25px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #004182; }
        .error-box { background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid #fecaca; }
        .success-box { background: #dcfce7; color: #166534; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Your Password</h2>

        <?php if ($sent): ?>
            <div class="success-box">A 6-digit verification code was sent to <strong><?php echo htmlspecialchars($email); ?></strong>.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
                <input type="email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($email); ?>" required>
                <input type="text" name="code" placeholder="6-digit verification code" inputmode="numeric" pattern="\d{6}" maxlength="6" required>
                <input type="password" name="password" placeholder="New Password (min 8 chars)" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit" class="btn">Update Password</button>
        </form>
        <a href="forgot_password.php" style="display:block; text-align:center; color:#0a66c2; margin-top:15px;">Request a new code</a>
    </div>
</body>
</html>
