<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join JobHub | Create Your Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --linkedin-blue: #0a66c2;
            --linkedin-hover: #004182;
            --bg-gray: #f3f2ef;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-gray);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .register-wrapper {
            display: flex;
            width: 1100px;
            max-width: 95%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 40px 20px;
        }

        .register-side-image {
            flex: 0.8;
            background: linear-gradient(135deg, #008080, #20b2aa);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .register-side-image { display: none; }
        }

        .register-card {
            flex: 1.5;
            padding: 40px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .register-header h2 { margin: 0; font-size: 1.8rem; color: #333; }
        .register-header p { color: #666; margin-bottom: 25px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .input-group { margin-bottom: 15px; }
        .full-width { grid-column: span 2; }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #555;
        }

        .input-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-field i {
            position: absolute;
            left: 15px;
            color: #888;
        }

        .input-field input, .input-field textarea {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .input-field textarea {
            resize: vertical;
            min-height: 60px;
        }

        .user-type-selector {
            display: flex;
            gap: 10px;
        }

        .user-type-selector input[type="radio"] { display: none; }
        .user-type-selector label {
            flex: 1;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
        }

        .user-type-selector input[type="radio"]:checked + label {
            background-color: #e7f3ff;
            border-color: var(--linkedin-blue);
            color: var(--linkedin-blue);
            font-weight: 600;
        }

        .register-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--linkedin-blue);
            color: white;
            border: none;
            border-radius: 28px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="register-wrapper">
        <div class="register-side-image">
            <div class="overlay-text">
                <i class="fas fa-rocket fa-4x" style="margin-bottom: 20px;"></i>
                <h2>JobHub</h2>
                <p>Build your professional network today.</p>
            </div>
        </div>
        
        <div class="register-card">
            <div class="register-header">
                <h2>Join JobHub</h2>
                <p>Create your professional identity.</p>
            </div>

            <form action="register_process.php" method="POST" onsubmit="return validateRegisterForm()">
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label for="fullname">Full Name</label>
                        <div class="input-field">
                            <i class="fas fa-user"></i>
                            <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <div class="input-field">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-field">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="8+ characters" required>
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label>Account Type</label>
                        <div class="user-type-selector">
                            <input type="radio" name="user_type" value="candidate" id="candidate" checked>
                            <label for="candidate">Candidate</label>
                            <input type="radio" name="user_type" value="employer" id="employer">
                            <label for="employer">Employer</label>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="education">Education</label>
                        <div class="input-field">
                            <i class="fas fa-graduation-cap"></i>
                            <input type="text" id="education" name="education" placeholder="University/Degree">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="skills">Skills</label>
                        <div class="input-field">
                            <i class="fas fa-tools"></i>
                            <input type="text" id="skills" name="skills" placeholder="PHP, Design, Management">
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label for="experience">Work Experience</label>
                        <div class="input-field">
                            <i class="fas fa-briefcase"></i>
                            <textarea id="experience" name="experience" placeholder="Previous roles and responsibilities..."></textarea>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="interests">Interests</label>
                        <div class="input-field">
                            <i class="fas fa-heart"></i>
                            <input type="text" id="interests" name="interests" placeholder="AI, Coding, Hiking">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="achievements">Achievements</label>
                        <div class="input-field">
                            <i class="fas fa-award"></i>
                            <input type="text" id="achievements" name="achievements" placeholder="Awards or Certifications">
                        </div>
                    </div>
                </div>

                <button type="submit" name="register" class="register-btn">Agree & Join</button>
            </form>

            <div class="register-footer">
                <p>Already on JobHub? <a href="login.php">Sign in</a></p>
            </div>
        </div>
    </div>

    <script>
    function validateRegisterForm() {
        const name = document.getElementById('fullname').value.trim();
        const password = document.getElementById('password').value;
        if (name.length < 3) { alert("Name is too short."); return false; }
        if (password.length < 8) { alert("Password must be 8+ characters."); return false; }
        return true;
    }
    </script>
</body>
</html>