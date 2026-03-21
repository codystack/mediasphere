<?php
session_start();
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Optional: Disable errors in production
error_reporting(0);

// =======================
// VALIDATE INPUT
// =======================
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing transaction ID.']);
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID.']);
    exit;
}

// =======================
// AUTHORIZATION CHECK
// =======================
if (!isset($_SESSION['designation']) || $_SESSION['designation'] !== 'super-admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    // =======================
    // CHECK IF EXISTS
    // =======================
    $check = $pdo->prepare("SELECT id FROM transactions WHERE id = ?");
    $check->execute([$id]);

    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found.']);
        exit;
    }

    // =======================
    // DELETE TRANSACTION
    // =======================
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Transaction deleted successfully.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}