<?php
require_once 'db.php';

// Simple version of homepage - lightweight and fast loading
$featuredStmt = $pdo->query("SELECT * FROM content WHERE featured = 1 LIMIT 1");
$featured = $featuredStmt->fetch();

$recentStmt = $pdo->query("SELECT * FROM content ORDER BY id DESC LIMIT 12");
$recentContent = $recentStmt->fetchAll();

$currentUser = getCurrentUser($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix - Simple</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #000;
            color: white;
        }

        .header {
            background: #141414;
            padding: 1rem 2%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #e50914;
            text-decoration: none;
        }

        .nav {
            display: flex;
            gap: 1rem;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.1);
        }

        .hero {
            height: 60vh;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                        url('<?php echo $featured['thumbnail'] ?? '/placeholder.svg?height=600&width=1200'; ?>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding: 0 2%;
        }

        .hero-content {
            max-width: 500px;
        }

        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero-desc {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.4;
        }

        .btn {
            background: #e50914;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
        }

        .btn:hover {
            background: #f40612;
        }

        .btn-secondary {
            background: rgba(109,109,110,0.7);
        }

        .content-section {
            padding: 2rem 2%;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .content-item {
            cursor: pointer;
            transition: transform 0.3s;
        }

        .content-item:hover {
            transform: scale(1.05);
        }

        .content-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 4px;
        }

        .content-title {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .footer {
            background: #141414;
            padding: 2rem 2%;
            text-align: center;
            margin-top: 3rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .content-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
            
            .content-item img {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="simple_index.php" class="logo">NETFLIX</a>
        <nav class="nav">
            <?php if ($currentUser): ?>
                <a href="watchlist.php">My List</a>
                <a href="search.php">Search</a>
                <a href="profile.php"><?php echo htmlspecialchars($currentUser['username']); ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>

    <?php if ($featured): ?>
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars($featured['title']); ?></h1>
            <p class="hero-desc"><?php echo htmlspecialchars($featured['description']); ?></p>
            <a href="watch.php?id=<?php echo $featured['id']; ?>" class="btn">▶ Play</a>
            <a href="#" class="btn btn-secondary">+ My List</a>
        </div>
    </section>
    <?php endif; ?>

    <section class="content-section">
        <h2 class="section-title">Latest Content</h2>
        <div class="content-grid">
            <?php foreach ($recentContent as $content): ?>
            <div class="content-item" onclick="playContent(<?php echo $content['id']; ?>)">
                <img src="<?php echo htmlspecialchars($content['thumbnail']); ?>" 
                     alt="<?php echo htmlspecialchars($content['title']); ?>">
                <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2024 Netflix Clone. All rights reserved.</p>
    </footer>

    <script>
        function playContent(id) {
            <?php if ($currentUser): ?>
                window.location.href = 'watch.php?id=' + id;
            <?php else: ?>
                window.location.href = 'login.php?redirect=watch.php?id=' + id;
            <?php endif; ?>
        }

        // Simple search functionality
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                const query = prompt('Search for content:');
                if (query) {
                    window.location.href = 'search.php?q=' + encodeURIComponent(query);
                }
            }
        });
    </script>
</body>
</html>
