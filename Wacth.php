<?php
// Video Player Page for Netflix Clone
error_reporting(E_ALL); // Enable all error reporting for debugging
ini_set('display_errors', 1); // Display errors directly on the page for debugging

require_once 'db.php'; // Include database connection and helper functions

$content = null;
$content_id = $_GET['id'] ?? null;

echo "<!-- Debugging Info: -->";
echo "<!-- Content ID from URL: " . htmlspecialchars(print_r($content_id, true)) . " -->";

if ($content_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$content_id]);
        $content = $stmt->fetch();
        echo "<!-- Content fetched: " . htmlspecialchars(print_r($content, true)) . " -->";
    } catch (PDOException $e) {
        error_log("Database error on watch.php: " . $e->getMessage());
        echo "<!-- Database Error: " . htmlspecialchars($e->getMessage()) . " -->";
        $content = null; // Ensure content is null on error
    }
}

// If content not found or database error, display a message and then redirect
if (!$content) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Content Not Found</title>
        <style>
            body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #141414; color: white; text-align: center; padding-top: 50px; }
            h1 { color: #e50914; }
            p { margin-bottom: 20px; }
            .btn { background: #e50914; color: white; padding: 10px 20px; border: none; border-radius: 4px; text-decoration: none; }
        </style>
    </head>
    <body>
        <h1>Video Not Found</h1>
        <p>The video you are looking for could not be found or does not exist.</p>
        <p>Please ensure the ID in the URL is correct and the content exists in your database.</p>
        <a href='index.php' class='btn'>Go Back to Home</a>
    </body>
    </html>";
    // Redirect after a short delay to allow user to see the message
    header('Refresh: 5; URL=index.php'); // Redirects after 5 seconds
    exit();
}

$currentUser = getCurrentUser($pdo); // Get current user for login check
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch <?php echo htmlspecialchars($content['title']); ?> - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #000;
            color: white;
            overflow-x: hidden;
        }

        .video-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background-color: #000;
        }

        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-info {
            padding: 2rem 4%;
            max-width: 900px;
            margin: 0 auto;
        }

        .video-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #e50914;
        }

        .video-info p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #b3b3b3;
        }

        .video-meta {
            font-size: 1rem;
            color: #999;
            margin-bottom: 1rem;
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
            background: #e50914;
            color: white;
        }

        .btn:hover {
            background: #f40612;
        }

        .back-btn {
            background: rgba(109,109,110,0.7);
            margin-top: 2rem;
        }

        .back-btn:hover {
            background: rgba(109,109,110,0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .video-info {
                padding: 1rem 2%;
            }
            .video-info h1 {
                font-size: 1.8rem;
            }
            .video-info p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="video-container">
        <video controls autoplay>
            <source src="<?php echo htmlspecialchars($content['video_url']); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <div class="video-info">
        <h1><?php echo htmlspecialchars($content['title']); ?></h1>
        <div class="video-meta">
            <?php echo htmlspecialchars($content['release_year']); ?> | 
            <?php echo htmlspecialchars($content['genre']); ?> | 
            <?php echo htmlspecialchars($content['duration']); ?> min | 
            Rating: <?php echo htmlspecialchars($content['rating']); ?>
        </div>
        <p><?php echo htmlspecialchars($content['description']); ?></p>
        <a href="index.php" class="btn back-btn">← Back to Home</a>
    </div>
</body>
</html>
