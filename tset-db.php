<?php
// Comprehensive Database Test for Netflix Clone
require_once 'db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Test - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #141414;
            color: white;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e50914;
        }

        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .test-card {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .test-title {
            font-size: 1.3rem;
            color: #e50914;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .test-result {
            background: rgba(0,0,0,0.3);
            padding: 1rem;
            border-radius: 4px;
            margin: 0.5rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .success { border-left: 4px solid #4caf50; color: #4caf50; }
        .error { border-left: 4px solid #f44336; color: #f44336; }
        .warning { border-left: 4px solid #ffc107; color: #ffc107; }
        .info { border-left: 4px solid #2196f3; color: #2196f3; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.8rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.5rem;
            border: 1px solid rgba(255,255,255,0.2);
            text-align: left;
        }

        .data-table th {
            background: rgba(229, 9, 20, 0.2);
            color: #e50914;
        }

        .back-btn {
            background: #e50914;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">DATABASE TEST</div>
        </div>

        <div class="test-grid">
            <!-- Connection Test -->
            <div class="test-card">
                <div class="test-title">🔌 Connection Test</div>
                <?php
                try {
                    $testQuery = $pdo->query("SELECT 1");
                    echo '<div class="test-result success">✅ Database connection successful</div>';
                    
                    $serverInfo = $pdo->getAttribute(PDO::ATTR_SERVER_INFO);
                    echo '<div class="test-result info">Server: ' . htmlspecialchars($serverInfo) . '</div>';
                    
                } catch(Exception $e) {
                    echo '<div class="test-result error">❌ Connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <!-- Users Table Test -->
            <div class="test-card">
                <div class="test-title">👥 Users Table</div>
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                    $userCount = $stmt->fetch()['count'];
                    echo '<div class="test-result success">✅ Users table accessible</div>';
                    echo '<div class="test-result info">Total users: ' . $userCount . '</div>';
                    
                    if ($userCount > 0) {
                        $stmt = $pdo->query("SELECT username, email, created_at FROM users LIMIT 3");
                        $users = $stmt->fetchAll();
                        
                        echo '<table class="data-table">';
                        echo '<tr><th>Username</th><th>Email</th><th>Created</th></tr>';
                        foreach ($users as $user) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    
                } catch(Exception $e) {
                    echo '<div class="test-result error">❌ Users table error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <!-- Content Table Test -->
            <div class="test-card">
                <div class="test-title">🎬 Content Table</div>
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM content");
                    $contentCount = $stmt->fetch()['count'];
                    echo '<div class="test-result success">✅ Content table accessible</div>';
                    echo '<div class="test-result info">Total content: ' . $contentCount . '</div>';
                    
                    if ($contentCount > 0) {
                        $stmt = $pdo->query("SELECT title, genre, type, rating FROM content LIMIT 3");
                        $content = $stmt->fetchAll();
                        
                        echo '<table class="data-table">';
                        echo '<tr><th>Title</th><th>Genre</th><th>Type</th><th>Rating</th></tr>';
                        foreach ($content as $item) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($item['title']) . '</td>';
                            echo '<td>' . htmlspecialchars($item['genre']) . '</td>';
                            echo '<td>' . htmlspecialchars($item['type']) . '</td>';
                            echo '<td>' . htmlspecialchars($item['rating']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<div class="test-result warning">⚠️ No content found. Run database.sql to insert sample data.</div>';
                    }
                    
                } catch(Exception $e) {
                    echo '<div class="test-result error">❌ Content table error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <!-- Watchlist Test -->
            <div class="test-card">
                <div class="test-title">📝 Watchlist Table</div>
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM watchlist");
                    $watchlistCount = $stmt->fetch()['count'];
                    echo '<div class="test-result success">✅ Watchlist table accessible</div>';
                    echo '<div class="test-result info">Total watchlist items: ' . $watchlistCount . '</div>';
                    
                    // Test foreign key relationships
                    $stmt = $pdo->query("
                        SELECT w.id, u.username, c.title 
                        FROM watchlist w 
                        JOIN users u ON w.user_id = u.id 
                        JOIN content c ON w.content_id = c.id 
                        LIMIT 3
                    ");
                    $watchlistItems = $stmt->fetchAll();
                    
                    if (!empty($watchlistItems)) {
                        echo '<div class="test-result success">✅ Foreign key relationships working</div>';
                        echo '<table class="data-table">';
                        echo '<tr><th>User</th><th>Content</th></tr>';
                        foreach ($watchlistItems as $item) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($item['username']) . '</td>';
                            echo '<td>' . htmlspecialchars($item['title']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    
                } catch(Exception $e) {
                    echo '<div class="test-result error">❌ Watchlist table error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <!-- Progress Test -->
            <div class="test-card">
                <div class="test-title">⏱️ Progress Table</div>
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM watch_progress");
                    $progressCount = $stmt->fetch()['count'];
                    echo '<div class="test-result success">✅ Progress table accessible</div>';
                    echo '<div class="test-result info">Total progress records: ' . $progressCount . '</div>';
                    
                    if ($progressCount > 0) {
                        $stmt = $pdo->query("
                            SELECT u.username, c.title, wp.progress_time, wp.total_time, wp.completed
                            FROM watch_progress wp
                            JOIN users u ON wp.user_id = u.id 
                            JOIN content c ON wp.content_id = c.id 
                            LIMIT 3
                        ");
                        $progressItems = $stmt->fetchAll();
                        
                        echo '<table class="data-table">';
                        echo '<tr><th>User</th><th>Content</th><th>Progress</th><th>Completed</th></tr>';
                        foreach ($progressItems as $item) {
                            $percentage = round(($item['progress_time'] / $item['total_time']) * 100, 1);
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($item['username']) . '</td>';
                            echo '<td>' . htmlspecialchars($item['title']) . '</td>';
                            echo '<td>' . $percentage . '%</td>';
                            echo '<td>' . ($item['completed'] ? '✅' : '❌') . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                    
                } catch(Exception $e) {
                    echo '<div class="test-result error">❌ Progress table error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <!-- Ratings Test -->
            <div class="test-card">
                <div class="test-title">⭐ Ratings Table</div>
                <?php
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_ratings");
                    $ratingsCount = $stmt->fetch()['count'];
                    echo '<div class="test-result success">✅ Ratings table accessible</div>';
                    echo '<div class="test-result info">Total ratings: ' . $ratingsCount . '</div>';
                    
                    if ($ratingsCount > 0) {
                        $stmt = $pdo->query("
                            SELECT AVG(rating) as avg_rating, 
                                   MIN(rating) as min_rating, 
                                   MAX(rating) as max_rating 
                            FROM user_ratings
                        ");
                        $ratingStats = $stmt->fetch();
                        
                        echo '<div class="test-result info">Average rating: ' . round($ratingStats['avg_rating'], 2) . '</div>';
                        echo '<div class="test-result info">Rating range: ' . $ratingStats['min_rating'] . ' - ' . $ratingStats['max_rating'] . '</div>';
                    }
                    
                } catch(Exception $e) {
                    echo '<div class="test-result error">❌ Ratings table error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="index.php" class="back-btn">← Back to Home</a>
        </div>
    </div>
</body>
</html>
