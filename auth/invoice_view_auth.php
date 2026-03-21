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
    // FETCH INVOICE
    // =======================
    $stmt = $pdo->prepare("
        SELECT 
            i.*,

            -- Customer
            u.first_name AS customer_first,
            u.last_name AS customer_last,
            u.email AS customer_email,

            -- Admin
            a.first_name AS admin_first,
            a.last_name AS admin_last

        FROM invoices i

        LEFT JOIN users u ON i.customer_id = u.id
        LEFT JOIN admin a ON i.admin_id = a.id

        WHERE i.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        echo json_encode([
            "success" => false,
            "message" => "Invoice not found."
        ]);
        exit;
    }

    // =======================
    // FETCH INVOICE ITEMS
    // =======================
    $stmt = $pdo->prepare("
        SELECT 
            product_name,
            price,
            quantity,
            total
        FROM invoice_items
        WHERE invoice_id = ?
    ");

    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =======================
    // FORMAT EXTRA DATA
    // =======================
    $invoice['customer_name'] = trim(
        ($invoice['customer_first'] ?? '') . ' ' . ($invoice['customer_last'] ?? '')
    );

    $invoice['admin_name'] = trim(
        ($invoice['admin_first'] ?? '') . ' ' . ($invoice['admin_last'] ?? '')
    );

    // Optional: format numbers (frontend-ready)
    $invoice['subtotal_formatted'] = number_format($invoice['subtotal'], 2);
    $invoice['discount_formatted'] = number_format($invoice['discount'], 2);
    $invoice['tax_formatted'] = number_format($invoice['tax'], 2);
    $invoice['total_formatted'] = number_format($invoice['total_amount'], 2);

    // =======================
    // RESPONSE
    // =======================
    echo json_encode([
        "success" => true,
        "invoice" => $invoice,
        "items" => $items
    ]);

} catch (Exception $e) {
    error_log("Invoice view error: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "message" => "An unexpected server error occurred."
    ]);
}