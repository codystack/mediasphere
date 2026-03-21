<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/mailer.php';
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

// =======================
// INPUT
// =======================
$customer_id    = $_POST['customer_id'] ?? null;
$admin_id       = $_SESSION['admin_id'] ?? null;
$product_id     = $_POST['product_id'] ?? null;
$price          = $_POST['price'] ?? null;
$quantity       = $_POST['quantity'] ?? null;
$payment_method = $_POST['payment_method'] ?? 'cash';

// =======================
// VALIDATION
// =======================
if (!$customer_id || !$admin_id || !$product_id || !$price || !$quantity) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid quantity."]);
    exit;
}

try {
    // =======================
    // START TRANSACTION
    // =======================
    $pdo->beginTransaction();

    // =======================
    // LOCK PRODUCT
    // =======================
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Product not found.");
    }

    // =======================
    // CHECK STOCK
    // =======================
    if ($product['stock'] < $quantity) {
        throw new Exception("Insufficient stock.");
    }

    // =======================
    // GET CUSTOMER
    // =======================
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception("Customer not found.");
    }

    // =======================
    // GET ADMIN
    // =======================
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_name = $admin ? "{$admin['first_name']} {$admin['last_name']}" : 'Admin';

    $transaction_date = date('Y-m-d H:i:s');

    // =======================
    // TRANSACTION REF
    // =======================
    $transaction_ref = 'TXN-' . strtoupper(uniqid());

    // =======================
    // TOTAL
    // =======================
    $calculated_total = $price * $quantity;

    // =======================
    // INSERT TRANSACTION
    // =======================
    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (transaction_ref, customer_id, admin_id, product_id, price, quantity, total_amount, payment_method, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed')
    ");
    $stmt->execute([
        $transaction_ref,
        $customer_id,
        $admin_id,
        $product_id,
        $price,
        $quantity,
        $calculated_total,
        $payment_method
    ]);

    // =======================
    // UPDATE STOCK
    // =======================
    $newStock = $product['stock'] - $quantity;
    $status = ($newStock > 0) ? 'in_stock' : 'out_of_stock';

    $stmt = $pdo->prepare("UPDATE products SET stock = ?, status = ? WHERE id = ?");
    $stmt->execute([$newStock, $status, $product_id]);

    // =======================
    // COMMIT
    // =======================
    $pdo->commit();

    // =======================
    // CREATE PDF RECEIPT
    // =======================
    $pdf = new TCPDF();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);

    // Build HTML
    $pdfHtml = "
    <h2 style='text-align:center;'>Media Sphere Receipt</h2>
    <p><b>Transaction Ref:</b> {$transaction_ref}</p>
    <p><b>Date:</b> {$transaction_date}</p>
    <p><b>Served by:</b> {$admin_name}</p>
    <p><b>Customer:</b> {$customer['first_name']} {$customer['last_name']}</p>
    <p><b>Product:</b> {$product['name']}</p>
    <p><b>Price:</b> ₦" . number_format($price, 2) . "</p>
    <p><b>Quantity:</b> {$quantity}</p>
    <p><b>Total:</b> ₦" . number_format($calculated_total, 2) . "</p>
    <p><b>Payment Method:</b> {$payment_method}</p>
    <hr>
    ";

    // Product image if exists
    if (!empty($product['image'])) {
        $imagePath = __DIR__ . '/../' . $product['image'];
        if (file_exists($imagePath)) {
            $pdf->Image($imagePath, ($pdf->getPageWidth()-50)/2, '', 50, 50, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
    }

    $pdfHtml .= "<p style='text-align:center;font-size:10px;color:#888;'>Thank you for shopping with Media Sphere.<br>&copy; " . date('Y') . " Media Sphere. All rights reserved.</p>";

    $pdf->writeHTML($pdfHtml, true, false, true, false, '');

    // Save PDF
    $pdfDir = __DIR__ . '/../storage/receipts/';
    if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
    $pdfFile = $pdfDir . "receipt_{$transaction_ref}.pdf";
    $pdf->Output($pdfFile, 'F');

    // =======================
    // SEND EMAIL
    // =======================
    $subject = "Payment Receipt - {$transaction_ref}";

    $message = "
    <table style='width:100%;background:#f5f6fa;font-family:Arial,sans-serif;padding:20px;'>
        <tbody>
            <tr>
                <td>
                    <table style='margin:0 auto;max-width:800px;background:#fff;border-radius:8px;overflow:hidden;'>
                        <tbody>
                            <tr>
                                <td style='text-align:center;padding-top:30px;'>
                                    <a href='https://mediasphere.store/'>
                                        <img src='https://res.cloudinary.com/dzow7ui7e/image/upload/v1773901499/ms-dark_yvbuvl.png' width='200'>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td style='text-align:center;padding:30px 30px 10px 30px;'>
                                    <h2 style='font-size:22px;color:#000000;margin:0;'>Transaction Receipt</h2>
                                </td>
                            </tr>

                            <tr>
                                <td style='text-align:center;padding:0 30px 20px 30px;'>
                                    <p>Hello <b>{$customer['first_name']}</b>,</p>
                                    <p>Your purchase has been successfully completed.</p>

                                    <div style='text-align:center;margin-bottom:15px;'>
                                        <img src='{$product['image']}' style='max-width:150px;border-radius:8px;'>
                                    </div>

                                    <table style='margin:0 auto;background:#f9f9f9;border-radius:8px;padding:15px 25px;text-align:left;border-collapse:separate;border-spacing:0 8px;'>
                                        <tr>
                                            <td style='padding-bottom:8px;'><b>Ref:</b> {$transaction_ref}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding-bottom:8px;'><b>Product:</b> {$product['name']}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding-bottom:8px;'><b>Quantity:</b> {$quantity}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding-bottom:8px;'><b>Served by:</b> {$admin_name}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding-bottom:8px;'><b>Date:</b> {$transaction_date}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total:</b> ₦" . number_format($calculated_total) . "</td>
                                        </tr>
                                    </table>

                                    <p style='margin-top:20px;color:#DB0000;'>
                                        <a href='https://mediasphere.store/receipt.php?transaction_ref={$transaction_ref}'>
                                            Download PDF Receipt
                                        </a>
                                    </p>

                                    <a href='https://gearplug.ng/' target='_blank' style='display:inline-block;margin-top:15px;padding:10px 25px;background:#000;color:#fff;border-radius:5px;text-decoration:none;'>
                                        Shop Online
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td style='text-align:center;padding:20px 30px 40px 30px;'>
                                    <p style='font-size:12px;color:#aaa;'>&copy; " . date('Y') . " Media Sphere</p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    ";

    sendMail($customer['email'], $subject, $message, $pdfFile);

    // Delete temp PDF if desired
    if (file_exists($pdfFile)) unlink($pdfFile);

    echo json_encode(["success" => true, "message" => "Transaction completed successfully."]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}