<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';

// Collect inputs
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price       = $_POST['price'] ?? null;
$stock       = $_POST['stock'] ?? 0;

// Validate
if (!$name || !$price) {
    echo json_encode(["success" => false, "message" => "Product name and price are required."]);
    exit;
}

if (!is_numeric($price)) {
    echo json_encode(["success" => false, "message" => "Invalid price value."]);
    exit;
}

// Handle image upload
$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        echo json_encode(["success" => false, "message" => "Invalid image format."]);
        exit;
    }

    $filename = uniqid('product_') . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/products/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload image."]);
        exit;
    }

    $image_path = 'uploads/products/' . $filename;
}

// Auto status from stock
$status = ($stock > 0) ? 'in_stock' : 'out_of_stock';

try {

    $stmt = $pdo->prepare("
        INSERT INTO products 
        (name, description, price, stock, status, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $description,
        $price,
        $stock,
        $status,
        $image_path
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Product added successfully."
    ]);

} catch (Exception $e) {

    // delete uploaded image if DB fails
    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
        unlink(__DIR__ . '/../' . $image_path);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}