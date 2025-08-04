<?php
require_once '../db.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$contentId = $input['content_id'] ?? 0;
$userId = $_SESSION['user_id'];

$response = ['success' => false, 'message' => ''];

try {
    if ($action === 'add') {
        // Check if already in watchlist
        $checkStmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
        $checkStmt->execute([$userId, $contentId]);
        
        if ($checkStmt->fetch()) {
            $response['message'] = 'Already in watchlist';
        } else {
            $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
            if ($stmt->execute([$userId, $contentId])) {
                $response['success'] = true;
                $response['message'] = 'Added to watchlist';
            }
        }
    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
        if ($stmt->execute([$userId, $contentId])) {
            $response['success'] = true;
            $response['message'] = 'Removed from watchlist';
        }
    }
} catch (Exception $e) {
    $response['message'] = 'Database error';
}

echo json_encode($response);
?>
