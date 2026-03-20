<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }

    $user_id = $_POST['user_id'] ?? null;
    $transaction_type = trim($_POST['transaction_type'] ?? '');
    $amount = floatval(str_replace(',', '', $_POST['amount'] ?? 0));
    $fee = floatval(str_replace(',', '', $_POST['fee'] ?? 0));
    $percentage = floatval($_POST['percentage'] ?? 0);
    $duration = trim($_POST['duration'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $remarks = trim($_POST['remarks'] ?? '');

    if (!$user_id || !$transaction_type || !$amount || !$fee || !$percentage || !$duration || !$start_date || !$end_date || !$remarks) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit;
    }

    // Generate unique reference number
    $reference_number = 'REF-' . strtoupper(uniqid());

    $stmt = $pdo->prepare("
        INSERT INTO transactions (
            user_id, transaction_type, amount, fee, percentage, duration,
            start_date, end_date, remarks, reference_number, status
        ) VALUES (
            :user_id, :transaction_type, :amount, :fee, :percentage, :duration,
            :start_date, :end_date, :remarks, :reference_number, 'Approved'
        )
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':transaction_type' => $transaction_type,
        ':amount' => $amount,
        ':fee' => $fee,
        ':percentage' => $percentage,
        ':duration' => $duration,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
        ':remarks' => $remarks,
        ':reference_number' => $reference_number
    ]);

    echo json_encode(['success' => true, 'message' => 'Offer created successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}