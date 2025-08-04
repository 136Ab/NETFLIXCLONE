<?php
// Quick Database Connection Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

$configs = [
    [
        'host' => 'localhost',
        'dbname' => 'netflix_clone',
        'username' => 'root',
        'password' => '',
        'name' => 'XAMPP Default'
    ],
    [
        'host' => 'localhost',
        'dbname' => 'netflix_clone',
        'username' => 'root',
        'password' => 'root',
        'name' => 'WAMP Default'
    ],
    [
        'host' => 'localhost',
        'dbname' => 'netflix_clone',
        'username' => 'root',
        'password' => 'mysql',
        'name' => 'MAMP Default'
    ],
    [
        'host' => 'localhost',
        'dbname' => 'test',
        'username' => 'root',
        'password' => '',
        'name' => 'Test Database'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #141414;
            color: white;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
        }
        .logo {
            text-align: center;
            font-size: 2rem;
            color: #e50914;
            margin-bottom: 20px;
        }
        .test-result {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #666;
        }
        .success {
            border-left-color: #4caf50;
            color: #4caf50;
        }
        .error {
            border-left-color: #f44336;
            color: #f44336;
        }
        .btn {
            background: #e50914;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">NETFLIX - Quick Test</div>
        
        <h3>Testing Database Configurations:</h3>
        
        <?php foreach ($configs as $config): ?>
            <div class="test-result <?php
                try {
                    $pdo = new PDO(
                        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4", 
                        $config['username'], 
                        $config['password']
                    );
                    echo 'success';
                    $success = true;
                } catch(PDOException $e) {
                    echo 'error';
                    $success = false;
                }
            ?>">
                <strong><?php echo $config['name']; ?></strong><br>
                Host: <?php echo $config['host']; ?><br>
                Database: <?php echo $config['dbname']; ?><br>
                Username: <?php echo $config['username']; ?><br>
                Password: <?php echo $config['password'] ? '***' : '(empty)'; ?><br>
                Status: <?php echo $success ? '✅ SUCCESS' : '❌ FAILED'; ?>
                <?php if (!$success): ?>
                    <br>Error: <?php echo $e->getMessage(); ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="setup_database.php" class="btn">Auto Setup Database</a>
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    </div>
</body>
</html>
