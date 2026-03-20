<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$id = intval($_POST['id']);

try {
    // Fetch request with requester info
    $stmt = $pdo->prepare("
        SELECT r.*, a.first_name, a.last_name
        FROM requests r
        JOIN admin a ON r.admin_id = a.id
        WHERE r.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(["success" => false, "message" => "Request not found."]);
        exit;
    }

    echo json_encode(["success" => true, "request" => $request]);
} catch (Exception $e) {
    error_log("Request view error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An unexpected server error occurred."]);
}
