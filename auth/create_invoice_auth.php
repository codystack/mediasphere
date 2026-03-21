<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/mailer.php';
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

/* =======================
   INPUT
======================= */
$customer_id    = $_POST['customer_id'] ?? null;
$admin_id       = $_SESSION['admin_id'] ?? null;
$items          = $_POST['items'] ?? [];
$payment_method = $_POST['payment_method'] ?? 'cash';

$discount       = str_replace(',', '', $_POST['discount'] ?? 0);
$discount_type  = $_POST['discount_type'] ?? 'fixed';

$tax            = str_replace(',', '', $_POST['tax'] ?? 0);
$tax_type       = $_POST['tax_type'] ?? 'fixed';

/* =======================
   VALIDATION
======================= */
if (!$customer_id || !$admin_id || empty($items)) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit;
}

try {

    $pdo->beginTransaction();

    /* =======================
       CUSTOMER
    ======================= */
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception("Customer not found.");
    }

    /* =======================
       INVOICE NUMBER
    ======================= */
    $invoice_number = 'INV-' . date('Y') . '-' . strtoupper(uniqid());

    $subtotal = 0;
    $invoiceItemsData = [];

    /* =======================
       PROCESS ITEMS
    ======================= */
    foreach ($items as $item) {

        $product_id = $item['product_id'];
        $qty        = (int)$item['qty'];

        if ($qty <= 0) continue;

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found.");
        }

        if ($product['stock'] < $qty) {
            throw new Exception("Insufficient stock for {$product['name']}");
        }

        $price = $product['price'];
        $total = $price * $qty;

        $subtotal += $total;

        $invoiceItemsData[] = [
            'product_id'   => $product_id,
            'product_name' => $product['name'],
            'price'        => $price,
            'quantity'     => $qty,
            'total'        => $total
        ];

        // UPDATE STOCK
        $newStock = $product['stock'] - $qty;
        $status = ($newStock > 0) ? 'in_stock' : 'out_of_stock';

        $stmt = $pdo->prepare("UPDATE products SET stock = ?, status = ? WHERE id = ?");
        $stmt->execute([$newStock, $status, $product_id]);
    }

    /* =======================
       DISCOUNT
    ======================= */
    $discountAmount = ($discount_type === 'percent')
        ? ($subtotal * $discount / 100)
        : $discount;

    $afterDiscount = $subtotal - $discountAmount;

    /* =======================
       TAX
    ======================= */
    $taxAmount = ($tax_type === 'percent')
        ? ($afterDiscount * $tax / 100)
        : $tax;

    /* =======================
       TOTAL
    ======================= */
    $total_amount = $afterDiscount + $taxAmount;

    /* =======================
       INSERT INVOICE
    ======================= */
    $stmt = $pdo->prepare("
        INSERT INTO invoices 
        (invoice_number, customer_id, admin_id, subtotal, discount, discount_type, tax, tax_type, total_amount, payment_method, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $invoice_number,
        $customer_id,
        $admin_id,
        $subtotal,
        $discount,
        $discount_type,
        $tax,
        $tax_type,
        $total_amount,
        $payment_method
    ]);

    $invoice_id = $pdo->lastInsertId();

    /* =======================
       INSERT ITEMS
    ======================= */
    $stmt = $pdo->prepare("
        INSERT INTO invoice_items 
        (invoice_id, product_id, product_name, price, quantity, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($invoiceItemsData as $item) {
        $stmt->execute([
            $invoice_id,
            $item['product_id'],
            $item['product_name'],
            $item['price'],
            $item['quantity'],
            $item['total']
        ]);
    }

    /* =======================
       PDF GENERATION
    ======================= */
    $pdf = new TCPDF();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    $itemsHtml = '';
    foreach ($invoiceItemsData as $item) {
        $itemsHtml .= "
        <tr>
            <td>{$item['product_name']}</td>
            <td>{$item['quantity']}</td>
            <td>₦" . number_format($item['price'],2) . "</td>
            <td>₦" . number_format($item['total'],2) . "</td>
        </tr>
        ";
    }

    $pdfHtml = "
    <h2 style='text-align:center;'>Media Sphere Invoice</h2>

    <p><b>Invoice:</b> {$invoice_number}</p>
    <p><b>Customer:</b> {$customer['first_name']} {$customer['last_name']}</p>

    <table border='1' cellpadding='5'>
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Total</th>
    </tr>

    {$itemsHtml}

    <tr><td colspan='3'>Subtotal</td><td>₦" . number_format($subtotal,2) . "</td></tr>
    <tr><td colspan='3'>Discount</td><td>₦" . number_format($discountAmount,2) . "</td></tr>
    <tr><td colspan='3'>Tax</td><td>₦" . number_format($taxAmount,2) . "</td></tr>
    <tr><td colspan='3'><b>Total</b></td><td><b>₦" . number_format($total_amount,2) . "</b></td></tr>
    </table>
    ";

    $pdf->writeHTML($pdfHtml, true, false, true, false, '');

    $pdfDir = __DIR__ . '/../storage/invoices/';
    if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

    $pdfFile = $pdfDir . "invoice_{$invoice_number}.pdf";
    $pdf->Output($pdfFile, 'F');

    /* =======================
       EMAIL (YOUR TEMPLATE PRESERVED)
    ======================= */

    $subject = "Invoice - {$invoice_number}";

    $itemListHtml = '';
    foreach ($invoiceItemsData as $item) {
        $itemListHtml .= "<p>{$item['product_name']} x {$item['quantity']} - ₦" . number_format($item['total'],2) . "</p>";
    }

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
                                    <h2 style='font-size:22px;color:#000000;margin:0;'>Invoice</h2>
                                </td>
                            </tr>

                            <tr>
                                <td style='text-align:center;padding:0 30px 20px 30px;'>
                                    <p>Hello <b>{$customer['first_name']}</b>,</p>
                                    <p>Your invoice has been successfully generated.</p>

                                    <table style='margin:0 auto;background:#f9f9f9;border-radius:8px;padding:15px 25px;text-align:left;border-collapse:separate;border-spacing:0 8px;'>
                                        <tr><td><b>Invoice:</b> {$invoice_number}</td></tr>
                                        <tr><td><b>Items:</b> {$itemListHtml}</td></tr>
                                        <tr><td><b>Subtotal:</b> ₦" . number_format($subtotal) . "</td></tr>
                                        <tr><td><b>Discount:</b> ₦" . number_format($discountAmount) . "</td></tr>
                                        <tr><td><b>Tax:</b> ₦" . number_format($taxAmount) . "</td></tr>
                                        <tr><td><b>Total:</b> ₦" . number_format($total_amount) . "</td></tr>
                                    </table>

                                    <p style='margin-top:20px;color:#DB0000;'>
                                        <a href='https://mediasphere.store/download?invoice={$invoice_number}'>
                                            Download PDF Invoice
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

    if (file_exists($pdfFile)) unlink($pdfFile);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Invoice created successfully",
        "invoice_number" => $invoice_number,
        "total" => $total_amount
    ]);

} catch (Exception $e) {

    if ($pdo->inTransaction()) $pdo->rollBack();

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}