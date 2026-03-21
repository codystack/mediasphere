<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['id'], $_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$id = intval($_POST['id']);
$status = strtolower(trim($_POST['status']));
$valid_statuses = ['pending', 'paid', 'cancelled'];

if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get current invoice
    $stmt = $pdo->prepare("SELECT status FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        throw new Exception('Invoice not found');
    }

    // Prevent double stock reversal
    if ($invoice['status'] === 'cancelled') {
        throw new Exception('Invoice already cancelled');
    }

    // ===== Reverse Stock if Cancelled =====
    if ($status === 'cancelled') {

        $items = $pdo->prepare("
            SELECT product_id, quantity 
            FROM invoice_items 
            WHERE invoice_id = ?
        ");
        $items->execute([$id]);

        while ($item = $items->fetch(PDO::FETCH_ASSOC)) {

            // Restore stock
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

    // ===== Update Invoice Status =====
    $update = $pdo->prepare("
        UPDATE invoices 
        SET status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $update->execute([$status, $id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Invoice {$status} successfully"
    ]);

} catch (Exception $e) {
    $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}