<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// =======================
// AUTH CHECK
// =======================
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

// =======================
// VALIDATION
// =======================
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$id = intval($_POST['id']);

try {

    // =======================
    // FETCH TRANSACTION
    // =======================
    $stmt = $pdo->prepare("
        SELECT 
            t.*,

            -- Customer
            u.first_name AS customer_first,
            u.last_name AS customer_last,
            u.email AS customer_email,

            -- Product
            p.name AS product_name,
            p.image AS product_image,

            -- Admin
            a.first_name AS admin_first,
            a.last_name AS admin_last

        FROM transactions t

        LEFT JOIN users u ON t.customer_id = u.id
        LEFT JOIN products p ON t.product_id = p.id
        LEFT JOIN admin a ON t.admin_id = a.id

        WHERE t.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo json_encode([
            "success" => false,
            "message" => "Transaction not found."
        ]);
        exit;
    }

    // =======================
    // FORMAT EXTRA DATA
    // =======================
    $transaction['customer_name'] = trim(
        ($transaction['customer_first'] ?? '') . ' ' . ($transaction['customer_last'] ?? '')
    );

    $transaction['admin_name'] = trim(
        ($transaction['admin_first'] ?? '') . ' ' . ($transaction['admin_last'] ?? '')
    );

    // =======================
    // RESPONSE
    // =======================
    echo json_encode([
        "success" => true,
        "transaction" => $transaction
    ]);

} catch (Exception $e) {
    error_log("Transaction view error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "An unexpected server error occurred."
    ]);
}