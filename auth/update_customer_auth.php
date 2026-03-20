<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';

$user_id   = $_POST['user_id'] ?? null;
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');

// Validate required fields
if (!$user_id || !$first_name || !$last_name || !$email || !$phone) {
    echo json_encode([
        "success" => false,
        "message" => "All fields including user ID are required."
    ]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format."
    ]);
    exit;
}

try {

    // Check if user exists
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $checkUser->execute([$user_id]);

    if ($checkUser->rowCount() === 0) {
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
        exit;
    }

    // Prevent duplicate email (excluding current user)
    $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkEmail->execute([$email, $user_id]);

    if ($checkEmail->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email already in use by another user."
        ]);
        exit;
    }

    // Update user
    $stmt = $pdo->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, email = ?, phone = ?
        WHERE id = ?
    ");

    $stmt->execute([$first_name, $last_name, $email, $phone, $user_id]);

    echo json_encode([
        "success" => true,
        "message" => "Customer updated successfully."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}