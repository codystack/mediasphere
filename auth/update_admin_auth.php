<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';

$admin_id = $_POST['admin_id'] ?? null;
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$designation = trim($_POST['designation'] ?? '');

if (!$first_name || !$last_name || !$email || !$phone || !$gender || !$designation) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

try {
    if ($admin_id) {
        // UPDATE existing admin
        $stmt = $pdo->prepare("UPDATE admin SET first_name=?, last_name=?, email=?, phone=?, gender=?, designation=? WHERE id=?");
        $stmt->execute([$first_name, $last_name, $email, $phone, $gender, $designation, $admin_id]);
        echo json_encode(["success" => true, "message" => "Admin account updated successfully."]);
    } else {
        // CREATE new admin (you can reuse your existing registration logic)
        echo json_encode(["success" => false, "message" => "Invalid request – missing admin ID."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}