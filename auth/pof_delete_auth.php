<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// ===== AUTH CHECK =====
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ===== VALIDATE INPUT =====
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$id = intval($_POST['id']);

try {
    // Check if application exists
    $check = $pdo->prepare("SELECT id FROM pof_application WHERE id = ?");
    $check->execute([$id]);

    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }

    // Delete record
    $delete = $pdo->prepare("DELETE FROM pof_application WHERE id = ?");
    $delete->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Application deleted successfully.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}