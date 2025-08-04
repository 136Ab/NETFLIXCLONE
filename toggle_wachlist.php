<?php
require_once 'db.php';
requireLogin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'action' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $contentId = $input['content_id'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        if ($contentId <= 0) {
            $response['message'] = 'Invalid content ID';
            echo json_encode($response);
            exit();
        }
        
        // Check if content exists
        $contentStmt = $pdo->prepare("SELECT id, title FROM content WHERE id = ?");
        $contentStmt->execute([$contentId]);
        $content = $contentStmt->fetch();
        
        if (!$content) {
            $response['message'] = 'Content not found';
            echo json_encode($response);
            exit();
        }
        
        // Check if already in watchlist
        $checkStmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
        $checkStmt->execute([$userId, $contentId]);
        $exists = $checkStmt->fetch();
        
        if ($exists) {
            // Remove from watchlist
            $deleteStmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
            if ($deleteStmt->execute([$userId, $contentId])) {
                $response['success'] = true;
                $response['message'] = 'Removed from watchlist';
                $response['action'] = 'removed';
                
                logNetflixError("User {$userId} removed '{$content['title']}' from watchlist", [
                    'user_id' => $userId,
                    'content_id' => $contentId,
                    'content_title' => $content['title'],
                    'action' => 'removed'
                ]);
            } else {
                $response['message'] = 'Failed to remove from watchlist';
            }
        } else {
            // Add to watchlist
            $insertStmt = $pdo->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
            if ($insertStmt->execute([$userId, $contentId])) {
                $response['success'] = true;
                $response['message'] = 'Added to watchlist';
                $response['action'] = 'added';
                
                logNetflixError("User {$userId} added '{$content['title']}' to watchlist", [
                    'user_id' => $userId,
                    'content_id' => $contentId,
                    'content_title' => $content['title'],
                    'action' => 'added'
                ]);
            } else {
                $response['message'] = 'Failed to add to watchlist';
            }
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check watchlist status
        $contentId = $_GET['content_id'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        if ($contentId <= 0) {
            $response['message'] = 'Invalid content ID';
            echo json_encode($response);
            exit();
        }
        
        $checkStmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
        $checkStmt->execute([$userId, $contentId]);
        $exists = $checkStmt->fetch();
        
        $response['success'] = true;
        $response['in_watchlist'] = !!$exists;
        $response['message'] = $exists ? 'In watchlist' : 'Not in watchlist';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Database error occurred';
    logNetflixError("Toggle watchlist error: " . $e->getMessage(), [
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'content_id' => $contentId ?? 'unknown',
        'error' => $e->getMessage()
    ]);
}

echo json_encode($response);
?>
