<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Device ID is required.']);
    exit;
}

try {
    // Check existence
    $check = $pdo->prepare("SELECT id FROM devices WHERE id = ?");
    $check->execute([$id]);

    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Device not found.']);
        exit;
    }

    // Delete
    $delete = $pdo->prepare("DELETE FROM devices WHERE id = ?");
    $delete->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Device deleted successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
