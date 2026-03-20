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

// Get form values
$request_id       = $_POST['request_id'] ?? '';
$request_title    = trim($_POST['request_title'] ?? '');
$description      = trim($_POST['description'] ?? '');
$request_type     = trim($_POST['request_type'] ?? '');
$priority         = trim($_POST['priority'] ?? '');
$request_date     = $_POST['request_date'] ?? '';
$superior_remark  = trim($_POST['superior_remark'] ?? '');

// Validate required fields
if (empty($request_id) || empty($request_title) || empty($request_type) || empty($priority) || empty($request_date)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    // Check if request exists
    $check = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
    $check->execute([$request_id]);
    $request = $check->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found.']);
        exit;
    }

    // Handle new attachment upload (optional)
    $attachment_path = $request['attachment_path']; // keep old path by default

    if (isset($_FILES['attachment_path']) && $_FILES['attachment_path']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($_FILES['attachment_path']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, or PDF allowed.']);
            exit;
        }

        $filename = uniqid('request_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/requests/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['attachment_path']['tmp_name'], $targetPath)) {
            // Delete old attachment if it exists
            if (!empty($attachment_path) && file_exists(__DIR__ . '/../' . $attachment_path)) {
                unlink(__DIR__ . '/../' . $attachment_path);
            }
            $attachment_path = 'uploads/requests/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload new attachment.']);
            exit;
        }
    }

    // Update request details
    $stmt = $pdo->prepare("
        UPDATE requests 
        SET 
            request_title    = ?, 
            description      = ?, 
            request_type     = ?, 
            priority         = ?, 
            request_date     = ?, 
            superior_remark  = ?, 
            attachment_path  = ?, 
            updated_at       = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $request_title,
        $description,
        $request_type,
        $priority,
        $request_date,
        $superior_remark,
        $attachment_path,
        $request_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Request updated successfully.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}