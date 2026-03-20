<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$id = intval($_POST['id']);

try {
    $stmt = $pdo->prepare("
        SELECT r.*, a.first_name, a.last_name
        FROM reports r
        JOIN admin a ON r.admin_id = a.id
        WHERE r.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        echo json_encode(["success" => false, "message" => "Report not found."]);
        exit;
    }

    echo json_encode(["success" => true, "report" => $report]);
} catch (Exception $e) {
    error_log("Report view error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An unexpected server error occurred."]);
}
