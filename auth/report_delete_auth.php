<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Auth check
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate inout
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$id = intval($_POST['id']);

try {
    // Check if report exists
    $check = $pdo->prepare("SELECT id FROM reports WHERE id = ?");
    $check->execute([$id]);

    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    // Delete record
    $delete = $pdo->prepare("DELETE FROM reports WHERE id = ?");
    $delete->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Report deleted successfully.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
