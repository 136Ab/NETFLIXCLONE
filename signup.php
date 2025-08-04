
<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already taken';
            } else {
                // Create new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashedPassword])) {
                    $success = 'Account created successfully! You can now sign in.';
                } else {
                    $error = 'Error creating account. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Netflix</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('/placeholder.svg?height=1080&width=1920');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signup-container {
            background: rgba(0,0,0,0.75);
            padding: 3rem;
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            color: white;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #e50914;
            text-align: center;
            margin-bottom: 2rem;
            text-decoration: none;
        }

        .form-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            background: #333;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: background-color 0.3s;
        }

        .form-input:focus {
            background: #454545;
        }

        .form-input::placeholder {
            color: #8c8c8c;
        }

        .error-message {
            background: #e87c03;
            color: white;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            background: #46d369;
            color: white;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .signup-btn {
            width: 100%;
            padding: 1rem;
            background: #e50914;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .signup-btn:hover {
            background: #f40612;
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
        }

        .form-footer a {
            color: #737373;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .signin-link {
            color: white;
            margin-left: 0.5rem;
        }

        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .signup-container {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">← Back to Home</a>
    
    <div class="signup-container">
        <a href="index.php" class="logo">NETFLIX</a>
        
        <h1 class="form-title">Sign Up</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="signupForm">
            <div class="form-group">
                <input type="text" name="username" class="form-input" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" class="form-input" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-input" placeholder="Password" required minlength="6">
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" class="form-input" placeholder="Confirm Password" required>
            </div>
            
            <button type="submit" class="signup-btn">Sign Up</button>
        </form>
        
        <div class="form-footer">
            <span>Already have an account?</span>
            <a href="login.php" class="signin-link">Sign in</a>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return;
            }
        });

        // Auto-redirect after successful signup
        <?php if ($success): ?>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
