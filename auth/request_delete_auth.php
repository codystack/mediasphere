<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// ===== AUTH CHECK =====
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// ===== VALIDATE INPUT =====
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID.']);
    exit;
}

$id = intval($_POST['id']);

try {
    // Check if request exists
    $check = $pdo->prepare("SELECT attachment_path FROM requests WHERE id = ?");
    $check->execute([$id]);
    $request = $check->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found.']);
        exit;
    }

    // Delete attachment file if exists
    if (!empty($request['attachment_path'])) {
        $filePath = __DIR__ . '/../' . $request['attachment_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete record from database
    $delete = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $delete->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Request deleted successfully.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}