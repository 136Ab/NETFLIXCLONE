<?php
// Netflix Clone Homepage - Fully Automatic Setup
error_reporting(0); // Hide errors for clean display

// Initialize variables
$featuredContent = [];
$trendingContent = [];
$contentByGenre = [];
$currentUser = null;
$databaseConnected = false;

// Try to connect to database (db.php handles connection and setup)
try {
    include 'db.php';
    
    if ($pdo) { // Check if $pdo object was successfully created in db.php
        $databaseConnected = true;
        
        // Get featured content
        $featuredStmt = $pdo->query("SELECT * FROM content WHERE featured = 1 ORDER BY RAND() LIMIT 5");
        $featuredContent = $featuredStmt->fetchAll();

        // Get trending content  
        $trendingStmt = $pdo->query("SELECT * FROM content WHERE trending = 1 ORDER BY RAND() LIMIT 10");
        $trendingContent = $trendingStmt->fetchAll();

        // Get content by genre
        $genreStmt = $pdo->query("SELECT DISTINCT genre FROM content ORDER BY genre");
        $genres = $genreStmt->fetchAll();

        foreach ($genres as $genre) {
            $stmt = $pdo->prepare("SELECT * FROM content WHERE genre = ? LIMIT 8");
            $stmt->execute([$genre['genre']]);
            $contentByGenre[$genre['genre']] = $stmt->fetchAll();
        }

        $currentUser = getCurrentUser($pdo);
    }
} catch (Exception $e) {
    // This catch block is mostly for unexpected errors, db.php handles connection errors
    $databaseConnected = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Netflix Clone</title>
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
        overflow-x: hidden;
    }

    /* Header Styles */
    .header {
        position: fixed;
        top: 0;
        width: 100%;
        background: linear-gradient(180deg, rgba(0,0,0,0.7) 10%, transparent);
        z-index: 1000;
        padding: 20px 4%;
        transition: background-color 0.4s;
    }

    .header.scrolled {
        background-color: #141414;
    }

    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        font-size: 2rem;
        font-weight: bold;
        color: #e50914;
        text-decoration: none;
    }

    .nav-links {
        display: flex;
        list-style: none;
        gap: 2rem;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        transition: color 0.3s;
    }

    .nav-links a:hover {
        color: #b3b3b3;
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .search-box {
        padding: 8px 12px;
        background: rgba(0,0,0,0.5);
        border: 1px solid #333;
        border-radius: 4px;
        color: white;
        outline: none;
    }

    .profile-btn {
        background: #e50914;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .profile-btn:hover {
        background: #f40612;
    }

    /* Hero Section */
    .hero {
        height: 100vh;
        background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('/placeholder.svg?height=1080&width=1920&text=Netflix+Hero');
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        padding: 0 4%;
    }

    .hero-content {
        max-width: 500px;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
    }

    .hero-description {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        line-height: 1.5;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    }

    .hero-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-primary {
        background: white;
        color: black;
    }

    .btn-primary:hover {
        background: rgba(255,255,255,0.8);
    }

    .btn-secondary {
        background: rgba(109,109,110,0.7);
        color: white;
    }

    .btn-secondary:hover {
        background: rgba(109,109,110,0.4);
    }

    .btn-setup {
        background: #e50914;
        color: white;
    }

    .btn-setup:hover {
        background: #f40612;
    }

    /* Content Sections */
    .content-section {
        padding: 2rem 4%;
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .content-row {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .content-row::-webkit-scrollbar {
        display: none;
    }

    .content-card {
        min-width: 200px;
        cursor: pointer;
        transition: transform 0.3s;
        position: relative;
    }

    .content-card:hover {
        transform: scale(1.05);
    }

    .content-card img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }

    .content-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        padding: 1rem;
        border-radius: 0 0 8px 8px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .content-card:hover .content-info {
        opacity: 1;
    }

    .content-title {
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .content-meta {
        font-size: 0.9rem;
        color: #b3b3b3;
    }

    /* Setup Message */
    .setup-message {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(229, 9, 20, 0.1);
        border: 1px solid #e50914;
        border-radius: 8px;
        margin: 2rem 4%;
    }

    .setup-message h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #e50914;
    }

    .setup-message p {
        font-size: 1.1rem;
        margin-bottom: 2rem;
        color: #b3b3b3;
    }

    .setup-steps {
        background: rgba(0,0,0,0.3);
        padding: 2rem;
        border-radius: 8px;
        margin: 2rem 0;
        text-align: left;
    }

    .step {
        margin: 1rem 0;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .step:last-child {
        border-bottom: none;
    }

    .step-number {
        color: #e50914;
        font-weight: bold;
        margin-right: 0.5rem;
    }

    /* No Content Message */
    .no-content {
        text-align: center;
        padding: 4rem 0;
        color: #b3b3b3;
    }

    .no-content h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .nav-links {
            display: none;
        }

        .hero-title {
            font-size: 2.5rem;
        }

        .hero-description {
            font-size: 1rem;
        }

        .content-section {
            padding: 1rem 2%;
        }

        .content-card {
            min-width: 150px;
        }

        .content-card img {
            height: 225px;
        }

        .setup-message {
            margin: 2rem;
            padding: 2rem 1rem;
        }
    }
</style>
</head>
<body>
<!-- Header -->
<header class="header" id="header">
    <nav class="nav">
        <a href="index.php" class="logo">NETFLIX</a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="search.php">Browse</a></li>
            <li><a href="watchlist.php">My List</a></li>
        </ul>
        <div class="user-menu">
            <input type="text" class="search-box" placeholder="Search..." id="searchBox">
            <?php if ($currentUser): ?>
                <a href="profile.php" class="profile-btn"><?php echo htmlspecialchars($currentUser['username']); ?></a>
                <a href="logout.php" class="profile-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="profile-btn">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- Database Setup Message (Only show if database not connected) -->
<?php if (!$databaseConnected): ?>
<div class="setup-message">
    <h2>🚀 Database Connection Required</h2>
    <p>Your Netflix clone needs MySQL to be running. Please follow these simple steps:</p>
    
    <div class="setup-steps">
        <div class="step">
            <span class="step-number">1.</span> Open XAMPP Control Panel and **Start Apache and MySQL**.
        </div>
        <div class="step">
            <span class="step-number">2.</span> Wait for both to turn green.
        </div>
        <div class="step">
            <span class="step-number">3.</span> Refresh this page. The database and content will be set up automatically!
        </div>
    </div>
    
    <div class="hero-buttons">
        <a href="javascript:location.reload()" class="btn btn-setup">🔄 Refresh Page</a>
    </div>
</div>
<?php endif; ?>

<!-- Hero Section -->
<?php if (!empty($featuredContent)): ?>
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title"><?php echo htmlspecialchars($featuredContent[0]['title']); ?></h1>
        <p class="hero-description"><?php echo htmlspecialchars($featuredContent[0]['description']); ?></p>
        <div class="hero-buttons">
            <a href="watch.php?id=<?php echo $featuredContent[0]['id']; ?>" class="btn btn-primary">
                ▶ Play
            </a>
            <a href="#" class="btn btn-secondary" onclick="addToWatchlist(<?php echo $featuredContent[0]['id']; ?>)">
                + My List
            </a>
        </div>
    </div>
</section>
<?php elseif ($databaseConnected): ?>
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Welcome to Netflix</h1>
        <p class="hero-description">Unlimited movies, TV shows, and more. Watch anywhere. Cancel anytime.</p>
        <div class="hero-buttons">
            <a href="signup.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-secondary">Sign In</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Trending Now -->
<?php if (!empty($trendingContent)): ?>
<section class="content-section">
    <h2 class="section-title">Trending Now</h2>
    <div class="content-row">
        <?php foreach ($trendingContent as $content): ?>
        <div class="content-card" onclick="redirectToWatch(<?php echo $content['id']; ?>)">
            <img src="<?php echo htmlspecialchars($content['thumbnail']); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
            <div class="content-info">
                <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                <div class="content-meta"><?php echo $content['release_year']; ?> • <?php echo htmlspecialchars($content['genre']); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Content by Genre -->
<?php foreach ($contentByGenre as $genre => $contents): ?>
<?php if (!empty($contents)): ?>
<section class="content-section">
    <h2 class="section-title"><?php echo htmlspecialchars($genre); ?></h2>
    <div class="content-row">
        <?php foreach ($contents as $content): ?>
        <div class="content-card" onclick="redirectToWatch(<?php echo $content['id']; ?>)">
            <img src="<?php echo htmlspecialchars($content['thumbnail']); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
            <div class="content-info">
                <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                <div class="content-meta"><?php echo $content['release_year']; ?> • <?php echo $content['duration']; ?> min</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<?php endforeach; ?>

<!-- Success Message (Only show if database connected and has content) -->
<?php if ($databaseConnected && !empty($featuredContent)): ?>
<div style="text-align: center; padding: 2rem; background: rgba(76, 175, 80, 0.1); border: 1px solid #4caf50; border-radius: 8px; margin: 2rem 4%;">
    <h3 style="color: #4caf50; margin-bottom: 1rem;">🎉 Netflix Clone is Working!</h3>
    <p style="color: #b3b3b3;">Database connected successfully with sample content loaded.</p>
</div>
<?php endif; ?>

<script>
    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.getElementById('header');
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Search functionality
    document.getElementById('searchBox').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                window.location.href = `search.php?q=${encodeURIComponent(query)}`;
            }
        }
    });

    // Redirect to watch page
    function redirectToWatch(contentId) {
        <?php if ($currentUser): ?>
            window.location.href = `watch.php?id=${contentId}`;
        <?php else: ?>
            window.location.href = `login.php?redirect=watch.php?id=${contentId}`;
        <?php endif; ?>
    }

    // Add to watchlist
    function addToWatchlist(contentId) {
        <?php if ($currentUser): ?>
            alert('Added to watchlist! (Feature coming soon)');
        <?php else: ?>
            window.location.href = 'login.php';
        <?php endif; ?>
    }
</script>
</body>
</html>
