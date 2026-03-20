<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? null;
    $tasks_completed = trim($_POST['tasks_completed'] ?? '');
    $issues_or_notes = trim($_POST['issues_or_notes'] ?? '');
    $superior_remark = trim($_POST['superior_remark'] ?? '');
    $designation = $_SESSION['designation'] ?? 'staff';

    if (empty($admin_id) || empty($tasks_completed)) {
        echo json_encode(["success" => false, "message" => "Tasks field cannot be empty."]);
        exit;
    }

    try {
        // If super-admin is reviewing an existing report
        if ($designation === 'super-admin' && !empty($superior_remark)) {
            $report_id = $_POST['report_id'] ?? null;

            if (!$report_id) {
                echo json_encode(["success" => false, "message" => "Missing report ID for review."]);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE reports
                SET superior_remark = :remark, status = 'Reviewed', updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':remark' => $superior_remark,
                ':id' => $report_id
            ]);

            echo json_encode(["success" => true, "message" => "Report reviewed successfully."]);
            exit;
        }

        // Else, staff is submitting a new report
        $stmt = $pdo->prepare("
            INSERT INTO reports (admin_id, tasks_completed, issues_or_notes, status)
            VALUES (:admin_id, :tasks_completed, :issues_or_notes, 'Submitted')
        ");
        $stmt->execute([
            ':admin_id' => $admin_id,
            ':tasks_completed' => $tasks_completed,
            ':issues_or_notes' => $issues_or_notes
        ]);

        echo json_encode(["success" => true, "message" => "Report submitted successfully."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}