<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
session_start();

// ------------------- ID VALIDATION -------------------
$current_admin = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
$receiver_id  = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : null;

if (!$current_admin) {
    http_response_code(401);
    echo json_encode(['error' => 'Admin not logged in']);
    exit;
}

if (!$receiver_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Receiver ID missing']);
    exit;
}

// ------------------- FETCH MESSAGES -------------------
try {
    $stmt = $pdo->prepare("
        SELECT id, sender_id, receiver_id, message, status, created_at, parent_message_id
        FROM admin_chats
        WHERE (sender_id = :admin1 AND receiver_id = :recv1)
           OR (sender_id = :recv2 AND receiver_id = :admin2)
        ORDER BY created_at ASC
    ");
    $stmt->execute([
        'admin1' => $current_admin,
        'recv1'  => $receiver_id,
        'recv2'  => $receiver_id,
        'admin2' => $current_admin
    ]);

    $rawMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $messages = [];
    $parentStmt = $pdo->prepare("SELECT message FROM admin_chats WHERE id = :id");

    foreach ($rawMessages as $msg) {
        $parentMsg = null;
        if (!empty($msg['parent_message_id'])) {
            $parentStmt->execute(['id' => $msg['parent_message_id']]);
            $parentMsg = $parentStmt->fetchColumn();
        }

        $createdAt = new DateTime($msg['created_at']);
        $now = new DateTime();
        $diff = $now->diff($createdAt);

        if ($diff->days === 0) {
            $date_group = 'Today';
        } elseif ($diff->days === 1) {
            $date_group = 'Yesterday';
        } else {
            $date_group = $createdAt->format('d M Y');
        }

        $messages[] = [
            'id' => $msg['id'],
            'message' => htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8'),
            'status' => $msg['status'],
            'created_at' => $msg['created_at'],
            'formatted_time' => $createdAt->format('H:i'),
            'date_group' => $date_group,
            'sender_id' => $msg['sender_id'],
            'receiver_id' => $msg['receiver_id'],
            'parent_message' => $parentMsg ? htmlspecialchars($parentMsg, ENT_QUOTES, 'UTF-8') : null,
            'is_sender' => $msg['sender_id'] == $current_admin
        ];
    }

    // ------------------- MARK AS READ -------------------
    $markReadStmt = $pdo->prepare("
        UPDATE admin_chats
        SET status = 'read', updated_at = NOW()
        WHERE sender_id = :receiver AND receiver_id = :current_admin AND status != 'read'
    ");
    $markReadStmt->execute([
        'receiver' => $receiver_id,
        'current_admin' => $current_admin
    ]);

    echo json_encode($messages);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}