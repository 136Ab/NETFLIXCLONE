<?php
require_once 'db.php';
requireLogin();

// Check if user is admin (you should implement proper admin check)
$currentUser = getCurrentUser($pdo);
$isAdmin = true; // Replace with actual admin check

if (!$isAdmin) {
    header('Location: error.php?code=403&message=Access denied');
    exit();
}

$message = '';
$error = '';
$videoId = $_GET['id'] ?? 0;

// Get video details
if ($videoId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();
    
    if (!$video) {
        header('Location: error.php?code=404&message=Video not found');
        exit();
    }
} else {
    $video = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $genre = sanitizeInput($_POST['genre']);
    $releaseYear = (int)$_POST['release_year'];
    $duration = (int)$_POST['duration'];
    $videoUrl = sanitizeInput($_POST['video_url']);
    $thumbnailUrl = sanitizeInput($_POST['thumbnail_url']);
    $type = sanitizeInput($_POST['type']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $trending = isset($_POST['trending']) ? 1 : 0;
    
    if (empty($title) || empty($description) || empty($genre)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            if ($videoId > 0) {
                // Update existing video
                $stmt = $pdo->prepare("
                    UPDATE content SET 
                    title = ?, description = ?, genre = ?, release_year = ?, 
                    duration = ?, video_url = ?, thumbnail = ?, type = ?, 
                    featured = ?, trending = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$title, $description, $genre, $releaseYear, $duration, $videoUrl, $thumbnailUrl, $type, $featured, $trending, $videoId])) {
                    $message = 'Video updated successfully!';
                    logNetflixError("Admin updated video: {$title}", [
                        'admin_id' => $currentUser['id'],
                        'video_id' => $videoId,
                        'action' => 'update'
                    ]);
                } else {
                    $error = 'Failed to update video';
                }
            } else {
                // Add new video
                $stmt = $pdo->prepare("
                    INSERT INTO content (title, description, genre, release_year, duration, video_url, thumbnail, type, featured, trending) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$title, $description, $genre, $releaseYear, $duration, $videoUrl, $thumbnailUrl, $type, $featured, $trending])) {
                    $videoId = $pdo->lastInsertId();
                    $message = 'Video added successfully!';
                    logNetflixError("Admin added new video: {$title}", [
                        'admin_id' => $currentUser['id'],
                        'video_id' => $videoId,
                        'action' => 'create'
                    ]);
                } else {
                    $error = 'Failed to add video';
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $video ? 'Update' : 'Add'; ?> Video - Netflix Admin</title>
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
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #333;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #e50914;
        }

        .back-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .form-container {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 2rem;
        }

        .form-title {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: #e50914;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #e50914;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 1rem;
            background: #333;
            border: 1px solid #555;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            border-color: #e50914;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .form-checkbox input {
            width: 20px;
            height: 20px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #e50914;
            color: white;
        }

        .btn-primary:hover {
            background: #f40612;
        }

        .btn-secondary {
            background: rgba(109,109,110,0.7);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(109,109,110,0.9);
        }

        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4caf50;
            color: #4caf50;
        }

        .error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid #f44336;
            color: #f44336;
        }

        .preview-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
        }

        .preview-title {
            color: #e50914;
            margin-bottom: 1rem;
        }

        .video-preview {
            width: 100%;
            max-width: 400px;
            height: 225px;
            object-fit: cover;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">NETFLIX ADMIN</div>
            <a href="index.php" class="back-btn">← Back to Home</a>
        </div>

        <div class="form-container">
            <h1 class="form-title"><?php echo $video ? 'Update Video' : 'Add New Video'; ?></h1>

            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" id="videoForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="title">Title *</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($video['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="genre">Genre *</label>
                        <select id="genre" name="genre" class="form-select" required>
                            <option value="">Select Genre</option>
                            <option value="Action" <?php echo ($video['genre'] ?? '') === 'Action' ? 'selected' : ''; ?>>Action</option>
                            <option value="Comedy" <?php echo ($video['genre'] ?? '') === 'Comedy' ? 'selected' : ''; ?>>Comedy</option>
                            <option value="Drama" <?php echo ($video['genre'] ?? '') === 'Drama' ? 'selected' : ''; ?>>Drama</option>
                            <option value="Horror" <?php echo ($video['genre'] ?? '') === 'Horror' ? 'selected' : ''; ?>>Horror</option>
                            <option value="Sci-Fi" <?php echo ($video['genre'] ?? '') === 'Sci-Fi' ? 'selected' : ''; ?>>Sci-Fi</option>
                            <option value="Thriller" <?php echo ($video['genre'] ?? '') === 'Thriller' ? 'selected' : ''; ?>>Thriller</option>
                            <option value="Romance" <?php echo ($video['genre'] ?? '') === 'Romance' ? 'selected' : ''; ?>>Romance</option>
                            <option value="Fantasy" <?php echo ($video['genre'] ?? '') === 'Fantasy' ? 'selected' : ''; ?>>Fantasy</option>
                            <option value="Crime" <?php echo ($video['genre'] ?? '') === 'Crime' ? 'selected' : ''; ?>>Crime</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="release_year">Release Year</label>
                        <input type="number" id="release_year" name="release_year" class="form-input" 
                               min="1900" max="2030" value="<?php echo $video['release_year'] ?? date('Y'); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="duration">Duration (minutes)</label>
                        <input type="number" id="duration" name="duration" class="form-input" 
                               min="1" value="<?php echo $video['duration'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Type</label>
                        <select id="type" name="type" class="form-select">
                            <option value="movie" <?php echo ($video['type'] ?? 'movie') === 'movie' ? 'selected' : ''; ?>>Movie</option>
                            <option value="series" <?php echo ($video['type'] ?? '') === 'series' ? 'selected' : ''; ?>>TV Series</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Description *</label>
                    <textarea id="description" name="description" class="form-textarea" required><?php echo htmlspecialchars($video['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="video_url">Video URL</label>
                    <input type="url" id="video_url" name="video_url" class="form-input" 
                           value="<?php echo htmlspecialchars($video['video_url'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="thumbnail_url">Thumbnail URL</label>
                    <input type="url" id="thumbnail_url" name="thumbnail_url" class="form-input" 
                           value="<?php echo htmlspecialchars($video['thumbnail'] ?? ''); ?>">
                </div>

                <div class="form-checkbox">
                    <input type="checkbox" id="featured" name="featured" 
                           <?php echo ($video['featured'] ?? false) ? 'checked' : ''; ?>>
                    <label for="featured">Featured Content</label>
                </div>

                <div class="form-checkbox">
                    <input type="checkbox" id="trending" name="trending" 
                           <?php echo ($video['trending'] ?? false) ? 'checked' : ''; ?>>
                    <label for="trending">Trending Content</label>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $video ? 'Update Video' : 'Add Video'; ?>
                    </button>
                </div>
            </form>

            <?php if ($video && $video['thumbnail']): ?>
            <div class="preview-section">
                <h3 class="preview-title">Current Thumbnail</h3>
                <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" 
                     alt="Thumbnail" class="video-preview">
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('videoForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const genre = document.getElementById('genre').value;
            
            if (!title || !description || !genre) {
                e.preventDefault();
                alert('Please fill in all required fields (Title, Description, Genre)');
                return;
            }
            
            // Validate URLs if provided
            const videoUrl = document.getElementById('video_url').value.trim();
            const thumbnailUrl = document.getElementById('thumbnail_url').value.trim();
            
            if (videoUrl && !isValidUrl(videoUrl)) {
                e.preventDefault();
                alert('Please enter a valid video URL');
                return;
            }
            
            if (thumbnailUrl && !isValidUrl(thumbnailUrl)) {
                e.preventDefault();
                alert('Please enter a valid thumbnail URL');
                return;
            }
        });
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Auto-generate thumbnail preview
        document.getElementById('thumbnail_url').addEventListener('blur', function() {
            const url = this.value.trim();
            if (url && isValidUrl(url)) {
                // You could add thumbnail preview functionality here
                console.log('Thumbnail URL updated:', url);
            }
        });
        
        // Auto-save draft (optional)
        let saveTimeout;
        document.querySelectorAll('.form-input, .form-textarea, .form-select').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    // Save draft to localStorage
                    const formData = new FormData(document.getElementById('videoForm'));
                    const data = Object.fromEntries(formData);
                    localStorage.setItem('video_draft', JSON.stringify(data));
                }, 2000);
            });
        });
        
        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('video_draft');
            if (draft && !<?php echo $video ? 'true' : 'false'; ?>) {
                const data = JSON.parse(draft);
                Object.keys(data).forEach(key => {
                    const element = document.querySelector(`[name="${key}"]`);
                    if (element) {
                        if (element.type === 'checkbox') {
                            element.checked = data[key] === 'on';
                        } else {
                            element.value = data[key];
                        }
                    }
                });
            }
        });
        
        // Clear draft on successful submission
        <?php if ($message): ?>
        localStorage.removeItem('video_draft');
        <?php endif; ?>
    </script>
</body>
</html>
