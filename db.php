<?php
// Database connection configuration for Netflix Clone
// This file will automatically try to connect and set up the database/tables.
error_reporting(0); // Hide errors for clean display

// Prioritize user's provided credentials first
$configs = [
    [
        'host' => 'localhost',
        'dbname' => 'dboxkgnwakv8on',
        'username' => 'uhcrnj1vbersg',
        'password' => 'q2hr4nxquppc',
        'name' => 'User Provided Config'
    ],
    // Fallback to common XAMPP/WAMP defaults
    [
        'host' => 'localhost',
        'dbname' => 'netflix_clone', // Default database name for local setup
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
        'host' => '127.0.0.1',
        'dbname' => 'netflix_clone',
        'username' => 'root',
        'password' => '',
        'name' => 'Alternative Local'
    ]
];

$pdo = null;
$connectionSuccess = false;
$currentDbName = ''; // To store the actual database name used

// Try each configuration to connect and create/use the database
foreach ($configs as $config) {
    try {
        // Attempt to connect without specifying a database first, to create it if needed
        $temp_pdo = new PDO("mysql:host={$config['host']};charset=utf8mb4", $config['username'], $config['password']);
        $temp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}`");
        
        // Now connect to the specific database
        $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4", $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Test the connection by a simple query
        $pdo->query("SELECT 1");
        $connectionSuccess = true;
        $currentDbName = $config['dbname'];
        break; // Connection successful, exit loop
        
    } catch(PDOException $e) {
        $pdo = null; // Reset PDO if connection fails
        continue; // Try next configuration
    }
}

// If no connection worked, display a simple message to start MySQL
if (!$connectionSuccess || !$pdo) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Required</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #141414, #000);
                color: white;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                background: rgba(255,255,255,0.1);
                padding: 2rem;
                border-radius: 10px;
                text-align: center;
            }
            .logo {
                font-size: 3rem;
                color: #e50914;
                font-weight: bold;
                margin-bottom: 1rem;
            }
            .title {
                font-size: 1.5rem;
                margin-bottom: 1rem;
                color: #ff6b6b;
            }
            .steps {
                text-align: left;
                background: rgba(0,0,0,0.3);
                padding: 1.5rem;
                border-radius: 8px;
                margin: 2rem 0;
            }
            .step {
                margin: 1rem 0;
                padding: 0.5rem 0;
            }
            .step-number {
                color: #e50914;
                font-weight: bold;
            }
            .btn {
                background: #e50914;
                color: white;
                padding: 1rem 2rem;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                margin: 0.5rem;
                font-weight: bold;
                cursor: pointer;
            }
            .btn:hover { background: #f40612; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">NETFLIX</div>
            <h1 class="title">Database Connection Failed</h1>
            <p>Please ensure your MySQL server is running.</p>
            
            <div class="steps">
                <div class="step">
                    <span class="step-number">1.</span> Open XAMPP Control Panel
                </div>
                <div class="step">
                    <span class="step-number">2.</span> Click "Start" next to Apache and MySQL
                </div>
                <div class="step">
                    <span class="step-number">3.</span> Wait for both to turn green
                </div>
                <div class="step">
                    <span class="step-number">4.</span> Refresh this page.
                </div>
            </div>
            
            <a href="javascript:location.reload()" class="btn">🔄 Refresh Page</a>
            
            <p style="margin-top: 2rem; color: #999; font-size: 0.9rem;">
                The system will automatically set up the database once MySQL is running.
            </p>
        </div>
    </body>
    </html>
    <?php
    exit(); // Stop execution if no connection
}

// If connection is successful, ensure tables exist and add sample data
try {
    // Check if tables exist (e.g., 'content' table)
    $result = $pdo->query("SHOW TABLES LIKE 'content'");
    if ($result->rowCount() == 0) {
        // Create tables
        $sql = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg',
            subscription_type ENUM('basic', 'standard', 'premium') DEFAULT 'basic',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        CREATE TABLE content (
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

        CREATE TABLE watchlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            content_id INT,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
            UNIQUE KEY unique_watchlist (user_id, content_id)
        );

        CREATE TABLE watch_progress (
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

        CREATE TABLE user_ratings (
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
        
        // Insert sample data
        $sampleData = "
        INSERT INTO content (title, description, genre, release_year, duration, rating, thumbnail, video_url, type, featured, trending) VALUES
        ('Stranger Things', 'A group of kids uncover supernatural mysteries in their small town.', 'Sci-Fi', 2016, 50, 8.7, '/placeholder.svg?height=300&width=200&text=Stranger+Things', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'series', TRUE, TRUE),
        ('The Crown', 'The reign of Queen Elizabeth II from the 1940s to modern times.', 'Drama', 2016, 60, 8.6, '/placeholder.svg?height=300&width=200&text=The+Crown', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'series', TRUE, FALSE),
        ('Black Mirror', 'Anthology series exploring dark aspects of technology and society.', 'Sci-Fi', 2011, 45, 8.8, '/placeholder.svg?height=300&width=200&text=Black+Mirror', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'series', FALSE, TRUE),
        ('Money Heist', 'A criminal mastermind manipulates hostages and police in a heist.', 'Crime', 2017, 70, 8.3, '/placeholder.svg?height=300&width=200&text=Money+Heist', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'series', TRUE, TRUE),
        ('The Witcher', 'A mutated monster hunter struggles to find his place in a world.', 'Fantasy', 2019, 60, 8.2, '/placeholder.svg?height=300&width=200&text=The+Witcher', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'series', FALSE, TRUE),
        ('Extraction', 'A black-market mercenary has nothing to lose when his skills are solicited.', 'Action', 2020, 116, 6.7, '/placeholder.svg?height=300&width=200&text=Extraction', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'movie', TRUE, FALSE),
        ('Bird Box', 'A woman and two children make a desperate bid for safety.', 'Horror', 2018, 124, 6.6, '/placeholder.svg?height=300&width=200&text=Bird+Box', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4', 'movie', FALSE, FALSE),
        ('The Irishman', 'An aging hitman recalls his possible involvement with the slaying of Jimmy Hoffa.', 'Crime', 2019, 209, 7.8, '/placeholder.svg?height=300&width=200&text=The+Irishman', 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4', 'movie', TRUE, FALSE);
        ";
        
        $pdo->exec($sampleData);
    }
    
} catch (PDOException $e) {
    // If table creation or data insertion fails, it means something is wrong with the SQL,
    // but the connection itself is fine. The page will still load.
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser($pdo) {
    if (!isLoggedIn() || !$pdo) {
        return null;
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>
