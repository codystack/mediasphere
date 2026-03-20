<?php
require_once __DIR__ . '/../config/db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

// Validate ID
if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id AND user_id = :user_id LIMIT 1");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    $txn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$txn) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found.']);
        exit;
    }

    // Return formatted transaction data
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $txn['id'],
            'reference_number' => $txn['reference_number'],
            'transaction_type' => $txn['transaction_type'],
            'amount' => $txn['amount'],
            'fee' => $txn['fee'],
            'percentage' => $txn['percentage'],
            'duration' => $txn['duration'],
            'start_date' => $txn['start_date'],
            'end_date' => $txn['end_date'],
            'status' => $txn['status'],
            'remarks' => $txn['remarks'],
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
