<?php
require_once '../db.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$contentId = $input['content_id'] ?? 0;
$progressTime = $input['progress_time'] ?? 0;
$totalTime = $input['total_time'] ?? 0;
$completed = $input['completed'] ?? false;
$userId = $_SESSION['user_id'];

$response = ['success' => false];

try {
    $stmt = $pdo->prepare("
        INSERT INTO watch_progress (user_id, content_id, progress_time, total_time, completed) 
        VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        progress_time = VALUES(progress_time), 
        total_time = VALUES(total_time), 
        completed = VALUES(completed),
        last_watched = CURRENT_TIMESTAMP
    ");
    
    if ($stmt->execute([$userId, $contentId, $progressTime, $totalTime, $completed])) {
        $response['success'] = true;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

echo json_encode($response);
?>
