<?php
// Manual Database Setup for Netflix Clone
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$error = '';
$db_host = 'localhost';
$db_username = 'root';
$db_password = ''; // Default to empty password
$db_name = 'netflix_clone';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = htmlspecialchars($_POST['db_host']);
    $db_username = htmlspecialchars($_POST['db_username']);
    $db_password = $_POST['db_password']; // Get password as is, don't htmlspecialchars it yet
    $db_name = htmlspecialchars($_POST['db_name']);

    try {
        // Step 1: Connect to MySQL server (without specifying a database)
        // Use backticks for database name in DSN
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $message .= "<div class='message success'>✅ Step 1: Connected to MySQL server successfully!</div>";

        // Step 2: Create the database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $message .= "<div class='message success'>✅ Step 2: Database '$db_name' created or already exists!</div>";

        // Step 3: Connect to the newly created/existing database
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $message .= "<div class='message success'>✅ Step 3: Connected to database '$db_name' successfully!</div>";

        // Step 4: Create tables
        $sql = "
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

        CREATE TABLE IF NOT EXISTS watchlist (
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
        $message .= "<div class='message success'>✅ Step 4: All tables created or already exist!</div>";

        // Step 5: Insert sample data
        $sampleData = "
        INSERT IGNORE INTO content (title, description, genre, release_year, duration, rating, thumbnail, video_url, type, featured, trending) VALUES
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
        $message .= "<div class='message success'>✅ Step 5: Sample data inserted or already exists!</div>";

        // Step 6: Update db.php with the new configuration
        $password_string_for_file = empty($db_password) ? "''" : "'" . addslashes($db_password) . "'"; // Correctly format password for file

        $newDbConfig = "<?php
// Database connection configuration - Auto-generated by manual setup
error_reporting(0); // Hide errors for clean display

\$host = '" . addslashes($db_host) . "';
\$username = '" . addslashes($db_username) . "';
\$password = $password_string_for_file;
\$dbname = '" . addslashes($db_name) . "';

\$pdo = null;

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    \$pdo = null; // Set PDO to null if connection fails
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
    if (!isLoggedIn() || !\$pdo) {
        return null;
    }
    try {
        \$stmt = \$pdo->prepare(\"SELECT * FROM users WHERE id = ?\");
        \$stmt->execute([\$_SESSION['user_id']]);
        return \$stmt->fetch();
    } catch (Exception \$e) {
        return null;
    }
}

function sanitizeInput(\$input) {
    return htmlspecialchars(strip_tags(trim(\$input)));
}
?>";
                
        file_put_contents('db.php', $newDbConfig);
        $message .= "<div class='message success'>✅ Step 6: db.php file updated with your configuration!</div>";
        $message .= "<div class='message success'>🎉 Setup Complete! You can now go to the <a href='index.php'>homepage</a>.</div>";

    } catch (PDOException $e) {
        $error = "<div class='message error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        $message .= $error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix Clone - Manual Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #141414 0%, #000000 100%);
            color: white;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            max-width: 700px;
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
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #b3b3b3;
            font-size: 1.1rem;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            background: rgba(0,0,0,0.5);
            color: white;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #e50914;
        }
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
            font-weight: bold;
        }
        .btn:hover {
            background: #f40612;
        }
        .btn-secondary {
            background: rgba(109,109,110,0.7);
        }
        .message {
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            font-size: 1rem;
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
        .message a {
            color: #e50914;
            text-decoration: none;
            font-weight: bold;
        }
        .message a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">NETFLIX</div>
        <div class="subtitle">Manual Database Setup</div>

        <?php echo $message; ?>
        <?php echo $error; ?>

        <form method="POST">
            <div class="form-group">
                <label for="db_host">Database Host:</label>
                <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
            </div>
            <div class="form-group">
                <label for="db_username">Database Username:</label>
                <input type="text" id="db_username" name="db_username" value="<?php echo htmlspecialchars($db_username); ?>" required>
            </div>
            <div class="form-group">
                <label for="db_password">Database Password:</label>
                <input type="password" id="db_password" name="db_password" value="<?php echo htmlspecialchars($db_password); ?>">
                <small style="color: #b3b3b3;">(Leave empty if your 'root' user has no password, e.g., XAMPP default)</small>
            </div>
            <div class="form-group">
                <label for="db_name">Database Name:</label>
                <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn">💾 Setup Database</button>
                <a href="index.php" class="btn btn-secondary">← Back to Home</a>
            </div>
        </form>
    </div>
</body>
</html>
