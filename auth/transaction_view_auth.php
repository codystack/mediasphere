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
    $stmt = $pdo->prepare("SELECT 
            t.*, 
            u.first_name, 
            u.last_name
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
        LIMIT 1");
    $stmt->execute([$id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        echo json_encode(["success" => false, "message" => "Transaction not found."]);
        exit;
    }

    echo json_encode(["success" => true, "transaction" => $application]);
} catch (Exception $e) {
    error_log("Transaction view error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An unexpected server error occurred."]);
}
