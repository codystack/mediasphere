<?php
require_once __DIR__ . '/./config/db.php';
require_once __DIR__ . '/./vendor/tecnickcom/tcpdf/tcpdf.php';

$logoPath = __DIR__ . '/./assets/img/ms-dark.png';

$invoice_number = $_GET['invoice'] ?? '';

if (!$invoice_number) {
    die("Invoice reference missing.");
}

try {

    // =======================
    // GET INVOICE
    // =======================
    $stmt = $pdo->prepare("
        SELECT i.*, 
               u.first_name, u.last_name, u.email
        FROM invoices i
        JOIN users u ON i.customer_id = u.id
        WHERE i.invoice_number = ?
    ");
    $stmt->execute([$invoice_number]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        die("Invoice not found.");
    }

    // =======================
    // GET ITEMS
    // =======================
    $stmt = $pdo->prepare("
        SELECT * FROM invoice_items 
        WHERE invoice_id = ?
    ");
    $stmt->execute([$invoice['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

/* =======================
   CREATE PDF
======================= */
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);

/* =======================
   LOGO
======================= */
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 65, 10, 70);
}

/* =======================
   BUILD ITEMS ROWS
======================= */
$itemsRows = '';
foreach ($items as $item) {
    $itemsRows .= "
        <tr>
            <td>{$item['product_name']}</td>
            <td align='right'>₦" . number_format($item['price'],2) . "</td>
            <td align='right'>{$item['quantity']}</td>
            <td align='right'>₦" . number_format($item['total'],2) . "</td>
        </tr>
    ";
}

/* =======================
   HTML (YOUR DESIGN)
======================= */
$html = "
<table width='350' align='center' cellpadding='0' cellspacing='0' style='font-family:Arial;color:#000;background:#fff;padding:15px;'>

    <p style='text-align:center;margin-top:10px;'>
        <b>Invoice</b><br>
        <small>" . date('d/m/Y H:i:s') . "</small>
    </p>

    <hr>

    <p style='text-align:center;'>
        Invoice No:<br>
        <b>{$invoice['invoice_number']}</b>
    </p>

    <hr>

    <table width='100%' style='font-size:13px;'>
        <tr>
            <td><b>Customer:</b></td>
            <td style='text-align:right;'>{$invoice['first_name']} {$invoice['last_name']}</td>
        </tr>
        <tr>
            <td><b>Email:</b></td>
            <td style='text-align:right;'>{$invoice['email']}</td>
        </tr>
    </table>

    <hr>

    <!-- ITEMS -->
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
            {$itemsRows}
        </tbody>
    </table>

    <hr>

    <!-- TOTALS -->
    <table width='100%' style='font-size:13px;'>
        <tr>
            <td><b>Subtotal</b></td>
            <td style='text-align:right;'>₦" . number_format($invoice['subtotal'],2) . "</td>
        </tr>
        <tr>
            <td><b>Discount</b></td>
            <td style='text-align:right;'>₦" . number_format($invoice['discount'],2) . "</td>
        </tr>
        <tr>
            <td><b>Tax</b></td>
            <td style='text-align:right;'>₦" . number_format($invoice['tax'],2) . "</td>
        </tr>
        <tr>
            <td><b>Total</b></td>
            <td style='text-align:right;'><b>₦" . number_format($invoice['total_amount'],2) . "</b></td>
        </tr>
    </table>

    <hr>

    <!-- PAYMENT -->
    <p><b>Payment Analysis</b></p>
    <table width='100%'>
        <tr>
            <td>Method</td>
            <td style='text-align:right;'>{$invoice['payment_method']}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td style='text-align:right;'>{$invoice['status']}</td>
        </tr>
    </table>

    <hr>

    <p style='text-align:center;font-size:11px;'>
        Thank you for your purchase!<br>
        Keep this invoice for your records.
    </p>

</table>
";

/* =======================
   OUTPUT
======================= */
$pdf->writeHTML($html, true, false, true, false, '');

$filename = "Invoice_{$invoice['invoice_number']}.pdf";

$pdf->Output($filename, 'I');
exit;