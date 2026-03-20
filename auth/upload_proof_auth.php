<?php
require_once __DIR__ . '/../config/db.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    if (!isset($_FILES['proof_file'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
        exit;
    }

    $file = $_FILES['proof_file'];
    $target_dir = __DIR__ . '/../uploads/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_name = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $file_name;

    // Allowed file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, or PDF allowed.']);
        exit;
    }

    // Move file to uploads folder
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        try {
            // Save with status = Pending
            $stmt = $pdo->prepare("
                INSERT INTO payment_proofs (user_id, file_path, status, uploaded_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, 'uploads/' . $file_name, 'Pending']); // store relative path

            echo json_encode(['success' => true, 'message' => 'Proof of payment uploaded successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading file. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>