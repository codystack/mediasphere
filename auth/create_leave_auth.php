<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
session_start(); //important if you're using $_SESSION

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? null;
    $leave_type = trim($_POST['leave_type'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    // Basic validation
    if (empty($admin_id) || empty($leave_type) || empty($duration) || empty($start_date) || empty($reason)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    try {
        // Insert leave application
        $stmt = $pdo->prepare("
            INSERT INTO leave_applications (admin_id, leave_type, duration, start_date, reason, status)
            VALUES (:admin_id, :leave_type, :duration, :start_date, :reason, 'Pending')
        ");
        $stmt->execute([
            ':admin_id' => $admin_id,
            ':leave_type' => $leave_type,
            ':duration' => $duration,
            ':start_date' => $start_date,
            ':reason' => $reason
        ]);

        echo json_encode(["success" => true, "message" => "Leave application submitted successfully."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}