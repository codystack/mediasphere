<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';

$id          = $_POST['id'] ?? '';
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price       = $_POST['price'] ?? null;
$stock       = $_POST['stock'] ?? 0;
$status      = $_POST['status'] ?? 'in_stock';

if (!$id || !$name || !$price) {
    echo json_encode(["success" => false, "message" => "Required fields missing."]);
    exit;
}

try {
    // Get existing product
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Product not found."]);
        exit;
    }

    $image_path = $product['image'];

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            echo json_encode(["success" => false, "message" => "Invalid image format."]);
            exit;
        }

        $filename = uniqid('product_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/products/';
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            echo json_encode(["success" => false, "message" => "Image upload failed."]);
            exit;
        }

        // delete old image
        if (!empty($image_path) && file_exists(__DIR__ . '/../' . $image_path)) {
            unlink(__DIR__ . '/../' . $image_path);
        }

        $image_path = 'uploads/products/' . $filename;
    }

    // Auto status from stock (optional override)
    if (!in_array($status, ['in_stock', 'out_of_stock'])) {
        $status = ($stock > 0) ? 'in_stock' : 'out_of_stock';
    }

    $update = $pdo->prepare("
        UPDATE products 
        SET name=?, description=?, price=?, stock=?, status=?, image=?
        WHERE id=?
    ");

    $update->execute([
        $name,
        $description,
        $price,
        $stock,
        $status,
        $image_path,
        $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Product updated successfully."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}