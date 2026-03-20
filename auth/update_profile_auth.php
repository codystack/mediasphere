<?php
session_start(); // Add this line!
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

try {
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $admin_id = $_SESSION['admin_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $gender = trim($_POST['gender']);

    // Handle profile picture upload (optional)
    $picture = null;
    if (!empty($_FILES['picture']['name'])) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = uniqid() . "_" . basename($_FILES['picture']['name']);
        $targetFile = $uploadDir . $fileName;
        move_uploaded_file($_FILES['picture']['tmp_name'], $targetFile);
        $picture = 'uploads/'.$fileName;
    }

    if ($picture) {
        $stmt = $pdo->prepare("UPDATE admin SET first_name=?, last_name=?, phone=?, gender=?, picture=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$first_name, $last_name, $phone, $gender, $picture, $admin_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET first_name=?, last_name=?, phone=?, gender=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$first_name, $last_name, $phone, $gender, $admin_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}