<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// ===== Session / Authorization Check =====
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ===== Validate Input =====
if (!isset($_POST['id'], $_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$id = intval($_POST['id']);
$status = strtolower(trim($_POST['status']));
$valid_statuses = ['approved', 'rejected', 'pending', 'closed'];

if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE pof_application SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Application has been {$status} successfully."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or invalid ID']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}