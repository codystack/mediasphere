<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';

$id = $_POST['id'] ?? null;
$new_status = $_POST['status'] ?? null;

if (!$id || !$new_status) {
    echo json_encode(["success" => false, "message" => "Invalid parameters."]);
    exit;
}

// Optional: whitelist allowed statuses to prevent invalid values
$allowedStatuses = ['pending', 'approved', 'rejected', 'completed'];
if (!in_array($new_status, $allowedStatuses)) {
    echo json_encode(["success" => false, "message" => "Invalid status value."]);
    exit;
}

try {
    // Update the requests table instead of expenses
    $stmt = $pdo->prepare("UPDATE requests SET status = :status, updated_at = NOW() WHERE id = :id");
    $stmt->execute([
        ':status' => $new_status,
        ':id' => $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Request status updated to " . ucfirst($new_status) . "."
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
