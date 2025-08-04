<?php
require_once 'db.php';

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Netflix</title>
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

        .login-container {
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

        .login-btn {
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

        .login-btn:hover {
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

        .signup-link {
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
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">← Back to Home</a>
    
    <div class="login-container">
        <a href="index.php" class="logo">NETFLIX</a>
        
        <h1 class="form-title">Sign In</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" class="form-input" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-input" placeholder="Password" required>
            </div>
            
            <button type="submit" class="login-btn">Sign In</button>
        </form>
        
        <div class="form-footer">
            <span>New to Netflix?</span>
            <a href="signup.php" class="signup-link">Sign up now</a>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>
