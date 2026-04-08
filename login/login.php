<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | JobHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .status-message {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
            text-align: center;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body class="login-page"> 
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-briefcase"></i>
                <h2>Welcome Back!</h2>
                <p>Login to your JobHub account</p>
            </div>

            <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
                <div class="status-message status-success">Your password has been updated. You can log in now.</div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="form-options">
                    <label><input type="checkbox"> Remember me</label>
                    
                    <div style="text-align: right;">
                        <a href="forgot_password.php" style="font-size: 13px; color: #0a66c2; text-decoration: none; font-weight: 600;">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" name="login" class="login-btn">Login Now</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
