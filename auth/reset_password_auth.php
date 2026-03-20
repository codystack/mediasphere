<?php
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request");
    }

    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    // Validate token
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM admin_password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        echo json_encode(["success" => false, "message" => "Invalid or expired token."]);
        exit;
    }

    if (strtotime($reset['expires_at']) < time()) {
        echo json_encode(["success" => false, "message" => "This reset link has expired."]);
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update admin password
    $update = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $update->execute([$hashedPassword, $reset['user_id']]);

    // Delete the used token
    $delete = $pdo->prepare("DELETE FROM admin_password_resets WHERE token = ?");
    $delete->execute([$token]);

    echo json_encode(["success" => true, "message" => "Password reset successful. You can now log in."]);
    ob_end_flush();
    exit;

} catch (Throwable $e) {
    error_log("Reset password error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error. Please try again later."]);
    ob_end_flush();
    exit;
}
