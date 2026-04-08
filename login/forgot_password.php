<?php
require_once '../config/config.php';
require_once '../includes/mail_helper.php';

$message = "";
$message_class = "";
$debug_reset_code = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $code = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
    $token_hash = hash("sha256", $code);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 10);

    $sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $token_hash, $expiry, $email);
    $stmt->execute();

    if ($conn->affected_rows > 0) {
        $mail_error = "";
        $subject = "JobHub password reset code";
        $body = "Hello,\n\n"
            . "Your JobHub password reset verification code is: {$code}\n\n"
            . "This code will expire in 10 minutes.\n"
            . "If you did not request a password reset, you can ignore this email.";

        if (send_app_mail($email, $subject, $body, $mail_error)) {
            $safe_email = urlencode($email);
            header("Location: reset_password.php?email={$safe_email}&sent=1");
            exit();
        }

        $message = $mail_error;
        $message_class = "error-msg";
        $debug_reset_code = $code;
    } else {
        $message = "No account found with that email address.";
        $message_class = "error-msg";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | JobHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f2ef; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 380px; text-align: center; }
        .logo { color: #008080; font-size: 26px; font-weight: 700; margin-bottom: 20px; }
        h2 { font-size: 20px; color: #333; margin-bottom: 10px; }
        p { color: #666; font-size: 14px; margin-bottom: 25px; }
        input[type="email"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #008080; color: white; border: none; padding: 12px; width: 100%; border-radius: 25px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #1a9393; }
        .back-link { display: block; margin-top: 20px; color: #0a66c2; text-decoration: none; font-size: 14px; }
        .success-msg { color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 13px; line-height: 1.5; border: 1px solid #c3e6cb; }
        .error-msg { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 13px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo"><i class="fas fa-briefcase"></i> JobHub</div>
        <h2>Forgot Password?</h2>
        <p>Enter your email and we'll send you a link to reset your password.</p>
        
        <?php if($message): ?>
            <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($debug_reset_code): ?>
            <div class="success-msg">Temporary local debug code: <strong><?php echo htmlspecialchars($debug_reset_code); ?></strong></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </div>
</body>
</html>
