<?php
// Simple Database Connection Test
echo "<h1>Database Connection Test</h1>";

try {
    // Attempt to connect with common XAMPP/WAMP defaults, including user's provided config
    $configs = [
        ['host' => 'localhost', 'dbname' => 'dboxkgnwakv8on', 'username' => 'uhcrnj1vbersg', 'password' => 'q2hr4nxquppc', 'name' => 'User Provided Config'],
        ['host' => 'localhost', 'dbname' => 'netflix_clone', 'username' => 'root', 'password' => '', 'name' => 'XAMPP Default'],
        ['host' => 'localhost', 'dbname' => 'netflix_clone', 'username' => 'root', 'password' => 'root', 'name' => 'WAMP Default'],
        ['host' => 'localhost', 'dbname' => 'netflix_clone', 'username' => 'root', 'password' => 'mysql', 'name' => 'MAMP Default'],
        ['host' => '127.0.0.1', 'dbname' => 'netflix_clone', 'username' => 'root', 'password' => '', 'name' => 'Alternative Local']
    ];

    $pdo = null;
    $connected_config = null;

    foreach ($configs as $config) {
        try {
            // Try connecting to the specific database
            $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4", $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connected_config = $config;
            break; // Found a working connection
        } catch (PDOException $e) {
            // Try next config
        }
    }

    if ($pdo) {
        echo "<p style='color: green;'>✅ MySQL Connection: SUCCESS (using config: {$connected_config['name']})</p>";
        echo "<p style='color: green;'>Database: {$connected_config['dbname']}</p>";
        
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p style='color: green;'>✅ Tables Found: " . implode(', ', $tables) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No tables found in '{$connected_config['dbname']}' database. They will be created automatically by `db.php`.</p>";
        }
        
        echo "<h2 style='color: green;'>🎉 Everything is working!</h2>";
        echo "<a href='index.php' style='background: #e50914; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Netflix Clone</a>";
        
    } else {
        echo "<p style='color: red;'>❌ Connection Failed: No configurations worked.</p>";
        echo "<h3>Please:</h3>";
        echo "<ul>";
        echo "<li>Start XAMPP Control Panel</li>";
        echo "<li>Click 'Start' next to MySQL</li>";
        echo "<li>Wait for it to turn green</li>";
        echo "<li>If your MySQL 'root' user has a password, ensure it's correctly set in `db.php` or try the `manual_setup.php` (if you still have it).</li>";
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ An unexpected error occurred: " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'>This usually means MySQL is not running or there's a deeper configuration issue.</p>";
}
?>
