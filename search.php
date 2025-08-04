<?php
// Search Results Page for Netflix Clone
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php'; // Include database connection and helper functions

$search_query = $_GET['q'] ?? '';
$search_results = [];

if ($search_query) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM content WHERE title LIKE ? OR description LIKE ? ORDER BY title ASC");
        $searchTerm = '%' . $search_query . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        $search_results = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error on search.php: " . $e->getMessage());
        $search_results = []; // Ensure empty array on error
    }
}

$currentUser = getCurrentUser($pdo); // Get current user for login check
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for "<?php echo htmlspecialchars($search_query); ?>" - Netflix Clone</title>
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

        /* Header Styles (copied from index.php for consistency) */
        .header {
            position: sticky; /* Changed to sticky for search page */
            top: 0;
            width: 100%;
            background-color: #141414; /* Solid background for search page */
            z-index: 1000;
            padding: 20px 4%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
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

        /* Search Results Specific Styles */
        .search-results-container {
            padding: 2rem 4%;
            margin-top: 80px; /* Space for fixed header */
        }

        .search-results-container h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .content-card {
            cursor: pointer;
            transition: transform 0.3s;
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }

        .content-card:hover {
            transform: scale(1.05);
            z-index: 10;
            box-shadow: 0 0 15px rgba(229, 9, 20, 0.5);
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

        .no-results {
            text-align: center;
            padding: 4rem 0;
            color: #b3b3b3;
        }

        .no-results h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .search-results-container {
                padding: 1rem 2%;
            }
            .results-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            .content-card img {
                height: 225px;
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
                <input type="text" class="search-box" placeholder="Search..." id="searchBox" value="<?php echo htmlspecialchars($search_query); ?>">
                <?php if ($currentUser): ?>
                    <a href="profile.php" class="profile-btn"><?php echo htmlspecialchars($currentUser['username']); ?></a>
                    <a href="logout.php" class="profile-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="profile-btn">Sign In</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="search-results-container">
        <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>

        <?php if (!empty($search_results)): ?>
            <div class="results-grid">
                <?php foreach ($search_results as $content): ?>
                <div class="content-card" onclick="redirectToWatch(<?php echo $content['id']; ?>)">
                    <img src="<?php echo htmlspecialchars($content['thumbnail']); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
                    <div class="content-info">
                        <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                        <div class="content-meta"><?php echo $content['release_year']; ?> • <?php echo htmlspecialchars($content['genre']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h2>No Results Found</h2>
                <p>Try searching for something else.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
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
    </script>
</body>
</html>
