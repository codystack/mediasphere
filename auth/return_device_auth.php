<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$assignment_id = trim($_POST['assignment_id'] ?? '');
$returned_condition = trim($_POST['returned_condition'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

if (empty($assignment_id) || empty($returned_condition)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

try {
    // Check if assignment exists and is still active
    $check = $pdo->prepare("SELECT device_id, status FROM device_assignments WHERE id = ?");
    $check->execute([$assignment_id]);
    $assignment = $check->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        echo json_encode(['success' => false, 'message' => 'Assignment not found.']);
        exit;
    }

    if ($assignment['status'] !== 'assigned') {
        echo json_encode(['success' => false, 'message' => 'This device has already been returned.']);
        exit;
    }

    // Update assignment record
    $stmt = $pdo->prepare("UPDATE device_assignments 
                           SET returned_condition = ?, returned_date = NOW(), status = 'returned', remarks = ? 
                           WHERE id = ?");
    $stmt->execute([$returned_condition, $remarks, $assignment_id]);

    // Update device status
    $updateDevice = $pdo->prepare("UPDATE devices SET status = 'available' WHERE id = ?");
    $updateDevice->execute([$assignment['device_id']]);

    echo json_encode(['success' => true, 'message' => 'Device successfully marked as returned.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}