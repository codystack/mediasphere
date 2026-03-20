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

try {
    $stmt = $pdo->prepare("UPDATE admin SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);

    echo json_encode([
        "success" => true,
        "message" => "Admin status updated to " . ucfirst($new_status) . "."
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}