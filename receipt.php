<?php
session_start();
require_once __DIR__ . '/./config/db.php';
require_once __DIR__ . '/./vendor/tecnickcom/tcpdf/tcpdf.php';
$logoPath = __DIR__ . '/./assets/img/ms-dark.png';

// Get transaction_ref from query
$transaction_ref = $_GET['transaction_ref'] ?? '';

if (!$transaction_ref) {
    die("Transaction reference missing.");
}

try {
    // Fetch transaction with customer and product info
    $stmt = $pdo->prepare("
        SELECT t.*, 
               u.first_name AS customer_first, u.last_name AS customer_last, u.email AS customer_email,
               p.name AS product_name, p.image AS product_image
        FROM transactions t
        JOIN users u ON t.customer_id = u.id
        JOIN products p ON t.product_id = p.id
        WHERE t.transaction_ref = ?
    ");
    $stmt->execute([$transaction_ref]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        die("Transaction not found.");
    }

    // Calculate total amount
    $total = number_format($transaction['total_amount'], 2);
    $price = number_format($transaction['price'], 2);

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Create new PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15, true);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('dejavusans', '', 12);

// Add logo FIRST
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 65, 10, 70);
}

// Build HTML content
$html = "
<table width='350' align='center' cellpadding='0' cellspacing='0' font-family:Arial, sans-serif;color:#000;background:#fff;padding:15px;>

    <!-- Logo -->
    <p style='margin-bottom:5px;margin-top:10px;'>
        <table width='100%'>
            <tr>
                <td align='center'>
                    <b>Sales Receipt</b><br>
                    <small>" . date('d/m/Y H:i:s') . "</small>
                </td>
            </tr>
        </table>
    </p>

    <hr style='border-top:1px dashed #000;'>

    <!-- Transaction Ref -->
    <p style='text-align:center;font-size:13px;'>
        Transaction Ref:<br>
        <b>{$transaction['transaction_ref']}</b>
    </p>

    <hr style='border-top:1px dashed #000;'>

    <!-- Customer Info -->
    <table width='100%' style='font-size:13px;'>
        <tr>
            <td><b>Customer:</b></td>
            <td style='text-align:right;'>{$transaction['customer_first']} {$transaction['customer_last']}</td>
        </tr>
        <tr>
            <td><b>Email:</b></td>
            <td style='text-align:right;'>{$transaction['customer_email']}</td>
        </tr>
    </table>

    <hr style='border-top:1px dashed #000;'>

    <!-- Product Table -->
    <table width='100%' style='font-size:13px;border-collapse:collapse;'>
        <thead>
            <tr>
                <th align='left'>Item</th>
                <th align='right'>Price</th>
                <th align='right'>Qty</th>
                <th align='right'>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$transaction['product_name']}</td>
                <td align='right'>₦{$price}</td>
                <td align='right'>{$transaction['quantity']}</td>
                <td align='right'>₦{$total}</td>
            </tr>
        </tbody>
    </table>

    <hr style='border-top:1px dashed #000;'>

    <!-- Totals -->
    <table width='100%' style='font-size:13px;'>
        <tr>
            <td><b>Total</b></td>
            <td style='text-align:right;'><b>₦{$total}</b></td>
        </tr>
    </table>

    <hr style='border-top:1px dashed #000;'>

    <!-- Payment -->
    <p style='font-size:13px;margin-bottom:5px;'><b>Payment Analysis</b></p>
    <table width='100%' style='font-size:13px;'>
        <tr>
            <td>Method</td>
            <td style='text-align:right;'>{$transaction['payment_method']}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td style='text-align:right;'>{$transaction['status']}</td>
        </tr>
    </table>

    <hr style='border-top:1px dashed #000;'>

    <!-- Footer -->
    <p style='font-size:11px;text-align:center;margin-top:10px;'>
        Thank you for your purchase!<br>
        Items sold are not returnable.<br>
        Keep this receipt for your records.
    </p>
</table>
";

// Include product image if exists
if (!empty($transaction['product_image']) && file_exists(__DIR__ . '/../' . $transaction['product_image'])) {
    $imagePath = __DIR__ . '/../' . $transaction['product_image'];
    $html .= "<p style='text-align:center;'><img src='{$imagePath}' width='150' style='border-radius:8px;'></p>";
}

// Footer note
$html .= "<p style='text-align:center;font-size:10px;color:#888;'>Thank you for shopping with Media Sphere.<br>&copy; " . date('Y') . " Media Sphere. All rights reserved.</p>";

// Output HTML to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Set PDF filename
$filename = "Receipt_{$transaction['transaction_ref']}.pdf";

// Output PDF for download
$pdf->Output($filename, 'I'); // Use 'D' to force download instead of inline view
exit;