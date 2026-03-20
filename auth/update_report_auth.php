<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
require_once __DIR__ . '/../config/db.php';

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get input values
$report_id = $_POST['report_id'] ?? '';
$tasks_completed = trim($_POST['tasks_completed'] ?? '');
$issues_or_notes = trim($_POST['issues_or_notes'] ?? '');
$superior_remark = trim($_POST['superior_remark'] ?? '');

// Validate required fields
if (empty($report_id) || empty($tasks_completed) || empty($issues_or_notes)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    // Check if report exists
    $check = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $check->execute([$report_id]);

    if ($check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Report not found.']);
        exit;
    }

    // Update report details
    $stmt = $pdo->prepare("
        UPDATE reports 
        SET 
            tasks_completed = ?, 
            issues_or_notes = ?, 
            superior_remark = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $tasks_completed,
        $issues_or_notes,
        $superior_remark,
        $report_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Report updated successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}