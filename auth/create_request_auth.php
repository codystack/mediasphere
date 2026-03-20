<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$admin_id       = $_POST['admin_id'] ?? null;
$request_title  = trim($_POST['request_title'] ?? '');
$description    = trim($_POST['description'] ?? '');
$request_type   = trim($_POST['request_type'] ?? '');
$priority       = trim($_POST['priority'] ?? '');
$request_date   = $_POST['request_date'] ?? null;
$superior_remark = trim($_POST['superior_remark'] ?? '');
$designation    = $_SESSION['designation'] ?? 'staff';

// Validate required fields
if (empty($admin_id) || empty($request_title) || empty($request_type) || empty($priority) || empty($request_date)) {
    echo json_encode(["success" => false, "message" => "All required fields must be filled."]);
    exit;
}

// Handle file upload (optional)
$attachment_path = null;
if (isset($_FILES['attachment_path']) && $_FILES['attachment_path']['error'] === UPLOAD_ERR_OK) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($_FILES['attachment_path']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only JPG, PNG, or PDF allowed."]);
        exit;
    }

    $filename = uniqid('request_') . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/requests/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['attachment_path']['tmp_name'], $targetPath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload attachment."]);
        exit;
    }

    $attachment_path = 'uploads/requests/' . $filename;
}

try {
    // Super-admin reviewing an existing request
    if ($designation === 'super-admin' && !empty($_POST['request_id'])) {
        $request_id = $_POST['request_id'];

        $stmt = $pdo->prepare("
            UPDATE requests
            SET superior_remark = :remark,
                status = 'Approved',
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':remark' => $superior_remark,
            ':id' => $request_id
        ]);

        echo json_encode(["success" => true, "message" => "Request reviewed successfully."]);
        exit;
    }

    // Staff submitting a new request
    $stmt = $pdo->prepare("
        INSERT INTO requests 
            (admin_id, request_title, description, request_type, priority, attachment_path, request_date, superior_remark, status, created_at)
        VALUES 
            (:admin_id, :request_title, :description, :request_type, :priority, :attachment_path, :request_date, :superior_remark, 'Pending', :created_at)
    ");
    $stmt->execute([
        ':admin_id'        => $admin_id,
        ':request_title'   => $request_title,
        ':description'     => $description,
        ':request_type'    => $request_type,
        ':priority'        => $priority,
        ':attachment_path' => $attachment_path,
        ':request_date'    => $request_date,
        ':superior_remark' => $superior_remark,
        ':created_at'      => date('Y-m-d H:i:s')
    ]);

    echo json_encode(["success" => true, "message" => "Request submitted successfully."]);

} catch (PDOException $e) {
    // Clean up uploaded file if DB insert fails
    if ($attachment_path && file_exists(__DIR__ . '/../' . $attachment_path)) {
        unlink(__DIR__ . '/../' . $attachment_path);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}