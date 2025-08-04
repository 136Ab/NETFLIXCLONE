<?php
require_once 'db.php';
requireLogin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'average_rating' => 0];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $contentId = $input['content_id'] ?? 0;
        $rating = $input['rating'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        // Validate rating
        if ($rating < 1 || $rating > 5) {
            $response['message'] = 'Rating must be between 1 and 5';
            echo json_encode($response);
            exit();
        }
        
        // Check if content exists
        $contentStmt = $pdo->prepare("SELECT id FROM content WHERE id = ?");
        $contentStmt->execute([$contentId]);
        if (!$contentStmt->fetch()) {
            $response['message'] = 'Content not found';
            echo json_encode($response);
            exit();
        }
        
        // Insert or update rating
        $stmt = $pdo->prepare("
            INSERT INTO user_ratings (user_id, content_id, rating) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)
        ");
        
        if ($stmt->execute([$userId, $contentId, $rating])) {
            // Calculate new average rating
            $avgStmt = $pdo->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
                FROM user_ratings 
                WHERE content_id = ?
            ");
            $avgStmt->execute([$contentId]);
            $avgResult = $avgStmt->fetch();
            
            // Update content table with new average
            $updateStmt = $pdo->prepare("UPDATE content SET rating = ? WHERE id = ?");
            $updateStmt->execute([round($avgResult['avg_rating'], 1), $contentId]);
            
            $response['success'] = true;
            $response['message'] = 'Rating saved successfully';
            $response['average_rating'] = round($avgResult['avg_rating'], 1);
            $response['total_ratings'] = $avgResult['total_ratings'];
            
            // Log the rating activity
            logNetflixError("User {$userId} rated content {$contentId} with {$rating} stars", [
                'user_id' => $userId,
                'content_id' => $contentId,
                'rating' => $rating,
                'new_average' => $response['average_rating']
            ]);
        } else {
            $response['message'] = 'Failed to save rating';
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get user's rating for specific content
        $contentId = $_GET['content_id'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT rating FROM user_ratings WHERE user_id = ? AND content_id = ?");
        $stmt->execute([$userId, $contentId]);
        $userRating = $stmt->fetch();
        
        // Get average rating
        $avgStmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
            FROM user_ratings 
            WHERE content_id = ?
        ");
        $avgStmt->execute([$contentId]);
        $avgResult = $avgStmt->fetch();
        
        $response['success'] = true;
        $response['user_rating'] = $userRating ? $userRating['rating'] : 0;
        $response['average_rating'] = $avgResult['avg_rating'] ? round($avgResult['avg_rating'], 1) : 0;
        $response['total_ratings'] = $avgResult['total_ratings'] ?? 0;
    }
    
} catch (Exception $e) {
    $response['message'] = 'Database error occurred';
    logNetflixError("Rating error: " . $e->getMessage(), [
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'content_id' => $contentId ?? 'unknown',
        'error' => $e->getMessage()
    ]);
}

echo json_encode($response);
?>
