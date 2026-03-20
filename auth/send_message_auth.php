<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
session_start();

$current_admin = $_SESSION['admin_id'] ?? null;

// Read JSON POST input
$input = json_decode(file_get_contents('php://input'), true);

$receiver_id = isset($input['receiver_id']) ? (int)$input['receiver_id'] : null;
$message     = isset($input['message']) ? trim($input['message']) : '';

if (!$current_admin || !$receiver_id || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO admin_chats (sender_id, receiver_id, message, status, created_at)
        VALUES (:sender, :receiver, :message, 'sent', NOW())
    ");
    $stmt->execute([
        'sender'   => $current_admin,
        'receiver' => $receiver_id,
        'message'  => htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
    ]);

    echo json_encode([
        'success' => true,
        'message_id' => $pdo->lastInsertId(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}