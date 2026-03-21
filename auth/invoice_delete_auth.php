<?php
session_start();
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Disable errors in production
error_reporting(0);

// =======================
// VALIDATE INPUT
// =======================
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing invoice ID.']);
    exit;
}

$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID.']);
    exit;
}

// =======================
// AUTHORIZATION CHECK
// =======================
if (!isset($_SESSION['designation']) || $_SESSION['designation'] !== 'super-admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // =======================
    // FETCH INVOICE
    // =======================
    $stmt = $pdo->prepare("SELECT status FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        throw new Exception('Invoice not found.');
    }

    // =======================
    // REVERSE STOCK (ONLY IF PENDING)
    // =======================
    if ($invoice['status'] === 'pending') {

        $items = $pdo->prepare("
            SELECT product_id, quantity 
            FROM invoice_items 
            WHERE invoice_id = ?
        ");
        $items->execute([$id]);

        while ($item = $items->fetch(PDO::FETCH_ASSOC)) {

            $updateStock = $pdo->prepare("
                UPDATE products 
                SET stock = stock + ?,
                    status = CASE 
                        WHEN stock + ? > 0 THEN 'in_stock' 
                        ELSE 'out_of_stock'
                    END
                WHERE id = ?
            ");

            $updateStock->execute([
                $item['quantity'],
                $item['quantity'],
                $item['product_id']
            ]);
        }
    }

    // =======================
    // DELETE INVOICE
    // =======================
    $delete = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
    $delete->execute([$id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Invoice deleted successfully.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}