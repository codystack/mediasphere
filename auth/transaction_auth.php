<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(['error' => 'Invalid transaction ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    $txn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$txn) {
        echo json_encode(['error' => 'Transaction not found']);
        exit;
    }

    echo json_encode([
        'id' => $txn['id'],
        'reference_number' => $txn['reference_number'],
        'transaction_type' => $txn['transaction_type'],
        'amount' => number_format($txn['amount'], 2),
        'fee' => number_format($txn['fee'], 2),
        'percentage' => $txn['percentage'],
        'duration' => $txn['duration'],
        'start_date' => date('d M, Y', strtotime($txn['start_date'])),
        'end_date' => $txn['end_date'] ? date('d M, Y', strtotime($txn['end_date'])) : 'N/A',
        'status' => ucfirst($txn['status']),
        'remarks' => $txn['remarks'] ?? 'No remarks available',
        'created_at' => date('d M, Y h:i A', strtotime($txn['created_at']))
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
