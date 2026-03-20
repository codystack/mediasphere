<?php
session_start();
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Optional: Disable errors in production (already handled)
error_reporting(0);

// Validate input
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing proof ID.']);
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid proof ID.']);
    exit;
}

// Optional: check if user is authorized (for example, admin only)
if (!isset($_SESSION['designation']) || $_SESSION['designation'] !== 'super-admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    // Ensure the record exists before deleting
    $check = $pdo->prepare("SELECT id FROM payment_proofs WHERE id = ?");
    $check->execute([$id]);
    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Payment proof not found.']);
        exit;
    }

    // Delete record
    $stmt = $pdo->prepare("DELETE FROM payment_proofs WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Payment proof deleted successfully.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
