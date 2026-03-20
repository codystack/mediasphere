<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$admin_id = $_POST['admin_id'] ?? null;
$expense_title = trim($_POST['expense_title'] ?? '');
$description = trim($_POST['description'] ?? '');
$amount = trim($_POST['amount'] ?? '');
$category = trim($_POST['category'] ?? '');
$expense_date = $_POST['expense_date'] ?? null;
$superior_remark = trim($_POST['superior_remark'] ?? '');
$designation = $_SESSION['designation'] ?? 'staff';

// Clean and validate amount
$amount = str_replace(',', '', $amount);
if (!is_numeric($amount)) {
    echo json_encode(["success" => false, "message" => "Amount must be a valid number."]);
    exit;
}

// Validate required fields before file handling
if (empty($admin_id) || empty($expense_title) || empty($amount) || empty($expense_date)) {
    echo json_encode(["success" => false, "message" => "All required fields must be filled."]);
    exit;
}

// 🧾 Handle file upload (only after validation)
$receipt_path = null;
if (isset($_FILES['receipt_path']) && $_FILES['receipt_path']['error'] === UPLOAD_ERR_OK) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($_FILES['receipt_path']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only JPG, PNG, or PDF allowed."]);
        exit;
    }

    $filename = uniqid('expense_') . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/expenses/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['receipt_path']['tmp_name'], $targetPath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload receipt."]);
        exit;
    }

    $receipt_path = 'uploads/expenses/' . $filename;
}

try {
    // 👨‍💼 Super-admin reviewing an existing expense
    if ($designation === 'super-admin' && !empty($_POST['expense_id'])) {
        $expense_id = $_POST['expense_id'];

        $stmt = $pdo->prepare("
            UPDATE expenses
            SET superior_remark = :remark,
                status = 'Approved',
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':remark' => $superior_remark,
            ':id' => $expense_id
        ]);

        echo json_encode(["success" => true, "message" => "Expense reviewed successfully."]);
        exit;
    }

    // 👩‍💻 Staff submitting a new expense
    $stmt = $pdo->prepare("
        INSERT INTO expenses 
            (admin_id, expense_title, description, amount, category, receipt_path, expense_date, superior_remark, status, created_at)
        VALUES 
            (:admin_id, :expense_title, :description, :amount, :category, :receipt_path, :expense_date, :superior_remark, 'Pending', :created_at)
    ");
    $stmt->execute([
        ':admin_id' => $admin_id,
        ':expense_title' => $expense_title,
        ':description' => $description,
        ':amount' => $amount,
        ':category' => $category,
        ':receipt_path' => $receipt_path,
        ':expense_date' => $expense_date,
        ':superior_remark' => $superior_remark,
        ':created_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode(["success" => true, "message" => "Expense submitted successfully."]);

} catch (PDOException $e) {
    // 🧹 Clean up uploaded file if DB insert fails
    if ($receipt_path && file_exists(__DIR__ . '/../' . $receipt_path)) {
        unlink(__DIR__ . '/../' . $receipt_path);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}