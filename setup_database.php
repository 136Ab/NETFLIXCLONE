<?php
// Automatic Database Setup for Netflix Clone
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? 1;
$message = '';
$error = '';

// Database configurations to try
$configs = [
    [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'name' => 'XAMPP (Default)'
    ],
    [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'name' => 'WAMP'
    ],
    [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'mysql',
        'name' => 'MAMP'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix Clone - Database Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #141414 0%, #000000 100%);
            color: white;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .logo {
            text-align: center;
            font-size: 3rem;
            font-weight: bold;
            color: #e50914;
            margin-bottom: 1rem;
        }

        .subtitle {
            text-align: center;
            color: #b3b3b3;
            margin-bottom: 2rem;
        }

        .step {
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid #e50914;
        }

        .step-title {
            font-size: 1.5rem;
            color: #e50914;
            margin-bottom: 1rem;
        }

        .config-test {
            background: rgba(255,255,255,0.05);
            padding: 1rem;
            border-radius: 6px;
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .config-name {
            font-weight: bold;
        }

        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .success { background: #4caf50; color: white; }
        .error { background: #f44336; color: white; }
        .testing { background: #ff9800; color: white; }

        .btn {
            background: #e50914;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #f40612;
        }

        .btn-secondary {
            background: rgba(109,109,110,0.7);
        }

        .code-block {
            background: #000;
            color: #00ff00;
            padding: 1rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 1rem 0;
            overflow-x: auto;
        }

        .progress {
            width: 100%;
            height: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e50914, #f40612);
            transition: width 0.3s;
        }

        .message {
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4caf50;
            color: #4caf50;
        }

        .message.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">NETFLIX</div>
        <div class="subtitle">Database Setup Wizard</div>

        <?php if ($step == 1): ?>
        <!-- Step 1: Test Connections -->
        <div class="step">
            <div class="step-title">Step 1: Testing Database Connections</div>
            <p>Testing different database configurations...</p>
            
            <div class="progress">
                <div class="progress-bar" style="width: 25%"></div>
            </div>

            <?php
            $workingConfig = null;
            foreach ($configs as $index => $config):
                echo "<div class='config-test'>";
                echo "<div class='config-name'>{$config['name']}</div>";
                
                try {
                    $testPdo = new PDO(
                        "mysql:host={$config['host']};charset=utf8mb4", 
                        $config['username'], 
                        $config['password']
                    );
                    $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo "<div class='status success'>✅ Connected</div>";
                    if (!$workingConfig) {
                        $workingConfig = $config;
                    }
                } catch(PDOException $e) {
                    echo "<div class='status error'>❌ Failed</div>";
                }
                echo "</div>";
            endforeach;
            ?>

            <?php if ($workingConfig): ?>
                <div class="message success">
                    ✅ Found working configuration: <?php echo $workingConfig['name']; ?>
                </div>
                <a href="?step=2&config=<?php echo urlencode(json_encode($workingConfig)); ?>" class="btn">
                    Continue with <?php echo $workingConfig['name']; ?>
                </a>
            <?php else: ?>
                <div class="message error">
                    ❌ No working database configuration found. Please:
                    <ul style="margin-top: 10px;">
                        <li>Start your MySQL server (XAMPP/WAMP/MAMP)</li>
                        <li>Check your database credentials</li>
                        <li>Make sure MySQL is running on port 3306</li>
                    </ul>
                </div>
                <a href="?step=1" class="btn">Try Again</a>
            <?php endif; ?>
        </div>

        <?php elseif ($step == 2): ?>
        <!-- Step 2: Create Database -->
        <div class="step">
            <div class="step-title">Step 2: Creating Database</div>
            
            <div class="progress">
                <div class="progress-bar" style="width: 50%"></div>
            </div>

            <?php
            $config = json_decode(urldecode($_GET['config']), true);
            
            try {
                $pdo = new PDO(
                    "mysql:host={$config['host']};charset=utf8mb4", 
                    $config['username'], 
                    $config['password']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS netflix_clone");
                $pdo->exec("USE netflix_clone");
                
                echo "<div class='message success'>✅ Database 'netflix_clone' created successfully!</div>";
                
            } catch(PDOException $e) {
                echo "<div class='message error'>❌ Error creating database: " . $e->getMessage() . "</div>";
            }
            ?>

            <a href="?step=3&config=<?php echo urlencode(json_encode($config)); ?>" class="btn">
                Continue to Create Tables
            </a>
        </div>

        <?php elseif ($step == 3): ?>
        <!-- Step 3: Create Tables -->
        <div class="step">
            <div class="step-title">Step 3: Creating Tables</div>
            
            <div class="progress">
                <div class="progress-bar" style="width: 75%"></div>
            </div>

            <?php
            $config = json_decode(urldecode($_GET['config']), true);
            
            try {
                $pdo = new PDO(
                    "mysql:host={$config['host']};dbname=netflix_clone;charset=utf8mb4", 
                    $config['username'], 
                    $config['password']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Read and execute database.sql
                $sql = "
                -- Users table
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg',
                    subscription_type ENUM('basic', 'standard', 'premium') DEFAULT 'basic',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                );

                -- Content table
                CREATE TABLE IF NOT EXISTS content (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    genre VARCHAR(100),
                    release_year INT,
                    duration INT,
                    rating DECIMAL(3,1) DEFAULT 0.0,
                    thumbnail VARCHAR(255),
                    video_url VARCHAR(255),
                    trailer_url VARCHAR(255),
                    type ENUM('movie', 'series') DEFAULT 'movie',
                    featured BOOLEAN DEFAULT FALSE,
                    trending BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                -- Watchlist table
                CREATE TABLE IF NOT EXISTS watchlist (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    content_id INT,
                    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_watchlist (user_id, content_id)
                );

                -- Watch progress table
                CREATE TABLE IF NOT EXISTS watch_progress (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    content_id INT,
                    progress_time INT DEFAULT 0,
                    total_time INT DEFAULT 0,
                    completed BOOLEAN DEFAULT FALSE,
                    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_progress (user_id, content_id)
                );

                -- User ratings table
                CREATE TABLE IF NOT EXISTS user_ratings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    content_id INT,
                    rating INT CHECK (rating >= 1 AND rating <= 5),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_rating (user_id, content_id)
                );
                ";
                
                $pdo->exec($sql);
                echo "<div class='message success'>✅ All tables created successfully!</div>";
                
            } catch(PDOException $e) {
                echo "<div class='message error'>❌ Error creating tables: " . $e->getMessage() . "</div>";
            }
            ?>

            <a href="?step=4&config=<?php echo urlencode(json_encode($config)); ?>" class="btn">
                Continue to Add Sample Data
            </a>
        </div>

        <?php elseif ($step == 4): ?>
        <!-- Step 4: Insert Sample Data -->
        <div class="step">
            <div class="step-title">Step 4: Adding Sample Data</div>
            
            <div class="progress">
                <div class="progress-bar" style="width: 100%"></div>
            </div>

            <?php
            $config = json_decode(urldecode($_GET['config']), true);
            
            try {
                $pdo = new PDO(
                    "mysql:host={$config['host']};dbname=netflix_clone;charset=utf8mb4", 
                    $config['username'], 
                    $config['password']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Insert sample content
                $sampleData = "
                INSERT IGNORE INTO content (title, description, genre, release_year, duration, rating, thumbnail, video_url, type, featured, trending) VALUES
                ('Stranger Things', 'A group of kids uncover supernatural mysteries in their small town.', 'Sci-Fi', 2016, 50, 8.7, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'series', TRUE, TRUE),
                ('The Crown', 'The reign of Queen Elizabeth II from the 1940s to modern times.', 'Drama', 2016, 60, 8.6, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'series', TRUE, FALSE),
                ('Black Mirror', 'Anthology series exploring dark aspects of technology and society.', 'Sci-Fi', 2011, 45, 8.8, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'series', FALSE, TRUE),
                ('Money Heist', 'A criminal mastermind manipulates hostages and police in a heist.', 'Crime', 2017, 70, 8.3, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'series', TRUE, TRUE),
                ('The Witcher', 'A mutated monster hunter struggles to find his place in a world.', 'Fantasy', 2019, 60, 8.2, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'series', FALSE, TRUE),
                ('Extraction', 'A black-market mercenary has nothing to lose when his skills are solicited.', 'Action', 2020, 116, 6.7, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'movie', TRUE, FALSE),
                ('Bird Box', 'A woman and two children make a desperate bid for safety.', 'Horror', 2018, 124, 6.6, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'movie', FALSE, FALSE),
                ('The Irishman', 'An aging hitman recalls his possible involvement with the slaying of Jimmy Hoffa.', 'Crime', 2019, 209, 7.8, '/placeholder.svg?height=300&width=200', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'movie', TRUE, FALSE);
                ";
                
                $pdo->exec($sampleData);
                echo "<div class='message success'>✅ Sample data added successfully!</div>";
                
                // Update db.php file
                $newDbConfig = "<?php
// Database connection configuration - Auto-generated by setup
\$host = '{$config['host']}';
\$dbname = 'netflix_clone';
\$username = '{$config['username']}';
\$password = '{$config['password']}';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Connection failed: \" . \$e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset(\$_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser(\$pdo) {
    if (!isLoggedIn()) {
        return null;
    }

    \$stmt = \$pdo->prepare(\"SELECT * FROM users WHERE id = ?\");
    \$stmt->execute([\$_SESSION['user_id']]);
    return \$stmt->fetch();
}

function sanitizeInput(\$input) {
    return htmlspecialchars(strip_tags(trim(\$input)));
}
?>";
                
                file_put_contents('db.php', $newDbConfig);
                echo "<div class='message success'>✅ db.php file updated with working configuration!</div>";
                
            } catch(PDOException $e) {
                echo "<div class='message error'>❌ Error adding sample data: " . $e->getMessage() . "</div>";
            }
            ?>

            <div class="message success">
                🎉 <strong>Setup Complete!</strong><br>
                Your Netflix clone database is ready to use!
            </div>

            <a href="index.php" class="btn">Go to Netflix Clone</a>
            <a href="test-connection.php" class="btn btn-secondary">Test Connection</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh progress
        <?php if ($step < 4): ?>
        setTimeout(function() {
            // Auto-advance after showing results
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
