<?php
include "./components/header.php";
require_once('./config/db.php');

// =======================
// FETCH INVOICES
// =======================
$stmt = $pdo->query("
    SELECT 
        i.*,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        a.first_name AS admin_first,
        a.last_name AS admin_last
    FROM invoices i
    LEFT JOIN users u ON i.customer_id = u.id
    LEFT JOIN admin a ON i.admin_id = a.id
    ORDER BY i.id DESC
");

$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getInvoiceItems($pdo, $invoice_id) {
    $stmt = $pdo->prepare("
        SELECT 
            ii.*,
            p.image
        FROM invoice_items ii
        LEFT JOIN products p ON ii.product_id = p.id
        WHERE ii.invoice_id = ?
    ");

    $stmt->execute([$invoice_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->query("SELECT id, name, price FROM products WHERE stock > 0");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT id, first_name, last_name FROM users");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =======================
// STATUS BADGE
// =======================
function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));

    switch ($s) {
        case 'paid':
            return ['bg-soft-success text-success', 'Paid'];

        case 'pending':
            return ['bg-soft-warning text-warning', 'Pending'];

        case 'cancelled':
            return ['bg-soft-danger text-danger', 'Cancelled'];

        default:
            return ['bg-soft-secondary text-secondary', ucfirst($status ?: 'Pending')];
    }
}
?>

<div class="d-flex flex-column flex-lg-row h-lg-full bg-surface-secondary">
    <?php include "./components/side-nav.php"; ?>

    <div class="flex-lg-1 h-screen overflow-y-lg-auto">
        <?php include "./components/top-nav.php"; ?>

        <!-- HEADER -->
        <header>
            <div class="container-fluid">
                <div class="pt-6">
                    <div class="row align-items-center">
                        <div class="col-sm col-12">
                            <h1 class="h2 ls-tight">Invoices</h1>
                        </div>

                        <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                            <a href="#offcanvasAddNewInvoice" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                <i class="bi bi-plus-square-dotted pe-2"></i>
                                Create Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="py-6 bg-surface-secondary">
            <div class="container-fluid">

                <div class="card">
                    <div class="table-responsive p-4">

                        <?php if (count($invoices) > 0): ?>
                        <table class="table table-hover" id="invoices">

                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($invoices as $invoice): 
                                    [$badgeClass, $statusText] = getStatusBadge($invoice['status']);
                                ?>
                                <tr>

                                    <!-- INVOICE NUMBER -->
                                    <td>
                                        <?= htmlspecialchars($invoice['invoice_number']) ?>
                                    </td>

                                    <!-- CUSTOMER -->
                                    <td>
                                        <?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?>
                                    </td>

                                    <!-- TOTAL -->
                                    <td>
                                        ₦<?= number_format($invoice['total_amount'], 2) ?>
                                    </td>

                                    <!-- STATUS -->
                                    <td>
                                        <span class="badge <?= $badgeClass ?> rounded-pill">
                                            <?= $statusText ?>
                                        </span>
                                    </td>

                                    <!-- DATE -->
                                    <td>
                                        <?= date('d M Y, h:i A', strtotime($invoice['created_at'])) ?>
                                    </td>

                                    <!-- ACTIONS -->
                                    <td class="text-end">

                                        <button 
                                            class="btn btn-sm btn-primary view-invoice btn-square" 
                                            data-id="<?= $invoice['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- MARK PAID -->
                                        <button class="btn btn-sm btn-success invoice-paid btn-square" data-id="<?= $invoice['id'] ?>">
                                            <i class="bi bi-check-circle"></i>
                                        </button>

                                        <!-- CANCEL -->
                                        <button class="btn btn-sm btn-danger invoice-cancel btn-square" data-id="<?= $invoice['id'] ?>">
                                            <i class="bi bi-x-circle"></i>
                                        </button>

                                        <!-- DELETE -->
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-square btn-danger delete-invoice" 
                                            data-id="<?= $invoice['id'] ?>" 
                                            data-name="<?= htmlspecialchars($invoice['invoice_number']) ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmActionModal">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </td>

                                </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <img src="./assets/img/no-data.png" width="120">
                                <p class="mt-3">No invoices yet</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>


    <?php
    include "./modal/new-invoice-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/invoice-modal.php";
    include "./modal/edit-invoice-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#invoices').DataTable();
        });
    </script>

    <script>
        let itemIndex = 0;

        const products = <?= json_encode($products ?? []) ?>;

        const container = document.getElementById('itemsContainer');
        const addBtn = document.getElementById('addItemBtn');

        /* =========================
        HELPERS
        ========================= */
        function formatWithCommas(value) {
            value = value.replace(/,/g, '');
            if (!value) return '';
            return parseFloat(value).toLocaleString();
        }

        function getRawNumber(value) {
            return parseFloat(String(value).replace(/,/g, '')) || 0;
        }

        /* =========================
        ADD ITEM
        ========================= */
        function addItem() {
            const row = document.createElement('div');
            row.classList.add('row', 'mb-2', 'item-row');

            let options = '<option disabled selected>Select Product</option>';
            products.forEach(p => {
                options += `<option value="${p.id}" data-price="${p.price}">
                    ${p.name} (₦${parseFloat(p.price).toLocaleString()})
                </option>`;
            });

            row.innerHTML = `
                <div class="col-md-5">
                    <select name="items[${itemIndex}][product_id]" class="form-select product" required>
                        ${options}
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="number" name="items[${itemIndex}][qty]" class="form-control qty" value="1" min="1" required>
                </div>

                <div class="col-md-3">
                    <input type="text" class="form-control subtotal" readonly>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-item">X</button>
                </div>
            `;

            container.appendChild(row);
            itemIndex++; // 🔥 IMPORTANT
        }

                addBtn.addEventListener('click', addItem);

                /* =========================
                REMOVE ITEM
                ========================= */
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-item')) {
                        e.target.closest('.item-row').remove();
                        calculateTotals();
                    }
                });

                /* =========================
                LISTEN INPUTS
                ========================= */
                document.addEventListener('input', function(e) {
                    if (
                        e.target.classList.contains('product') ||
                        e.target.classList.contains('qty') ||
                        e.target.id === 'discountValue' ||
                        e.target.id === 'taxValue'
                    ) {
                        calculateTotals();
                    }
                });

                /* =========================
                FORMAT DISCOUNT & TAX
                ========================= */
                document.addEventListener('input', function(e) {
                    if (e.target.id === 'discountValue' || e.target.id === 'taxValue') {
                        let value = e.target.value.replace(/,/g, '');

                        if (!isNaN(value) && value !== '') {
                            e.target.value = parseFloat(value).toLocaleString();
                        }
                    }
                });

                /* =========================
                CALCULATE TOTALS
                ========================= */
                function calculateTotals() {
                    let subtotal = 0;

                    document.querySelectorAll('.item-row').forEach(row => {
                        const product = row.querySelector('.product');
                        const qty = parseFloat(row.querySelector('.qty').value) || 0;

                        const price = product?.options[product.selectedIndex]?.dataset.price || 0;

                        const total = price * qty;

                        row.querySelector('.subtotal').value = total.toLocaleString();

                        subtotal += total;
                    });

                    // SUBTOTAL DISPLAY
                    document.getElementById('subTotalDisplay').innerText = subtotal.toLocaleString();

                    // DISCOUNT
                    const discountType = document.getElementById('discountType').value;
                    const discountValue = getRawNumber(document.getElementById('discountValue').value);

                    let discount = discountType === 'percent'
                        ? (subtotal * discountValue / 100)
                        : discountValue;

                    let afterDiscount = subtotal - discount;

                    // TAX
                    const taxType = document.getElementById('taxType').value;
                    const taxValue = getRawNumber(document.getElementById('taxValue').value);

                    let tax = taxType === 'percent'
                        ? (afterDiscount * taxValue / 100)
                        : taxValue;

                    // GRAND TOTAL
                    let grandTotal = afterDiscount + tax;

                    // DISPLAY
                    document.getElementById('grandTotalDisplay').innerText = grandTotal.toLocaleString();
                }

                /* =========================
                INITIAL RUN
                ========================= */
                calculateTotals();
    </script>

    <!-- Create invoice -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewInvoice form');
            if (!form) return; // safely skip if form not found

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Creating...`;

                try {
                const response = await fetch('./auth/create_invoice_auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    notyf.success(data.message);
                    form.reset();
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    notyf.error(data.message);
                }
                } catch (error) {
                notyf.error('Network or server error.');
                console.error(error);
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>Add New`;
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentInvoiceId = null;

            const invoiceModal = document.getElementById('invoiceModal');
            const confirmButton = document.getElementById('confirmButton');
            const newConfirmMessage = document.getElementById('newConfirmMessage');

            if (!invoiceModal || !confirmButton || !newConfirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // =========================
            // VIEW INVOICE CLICK
            // =========================
            document.querySelectorAll('.view-invoice').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentInvoiceId = button.dataset.id;

                    newConfirmMessage.innerHTML = `
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3"></div>
                            <p>Loading invoice details...</p>
                        </div>
                    `;

                    confirmButton.style.display = 'none';

                    invoiceModal.classList.add('show');
                    invoiceModal.style.display = 'block';

                    loadInvoiceDetails(currentInvoiceId);
                });
            });

            // =========================
            // LOAD INVOICE DETAILS
            // =========================
            async function loadInvoiceDetails(invoiceId) {
                try {
                    const response = await fetch('./auth/invoice_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: invoiceId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        newConfirmMessage.innerHTML = `<div class="text-danger text-center">${data.message}</div>`;
                        return;
                    }

                    const invoice = data.invoice;
                    const items = data.items || [];

                    // =========================
                    // STATUS BADGE
                    // =========================
                    let statusClass = 'bg-soft-secondary';
                    if (invoice.status === 'pending') statusClass = 'bg-soft-warning text-warning';
                    if (invoice.status === 'paid') statusClass = 'bg-soft-success text-success';
                    if (invoice.status === 'cancelled') statusClass = 'bg-soft-danger text-danger';

                    // =========================
                    // BUILD ITEMS TABLE
                    // =========================
                    let itemsHTML = '';
                    items.forEach(item => {
                        itemsHTML += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>₦${parseFloat(item.price).toLocaleString()}</td>
                                <td>${item.quantity}</td>
                                <td>₦${parseFloat(item.total).toLocaleString()}</td>
                            </tr>
                        `;
                    });

                    // =========================
                    // RENDER
                    // =========================
                    newConfirmMessage.innerHTML = `
                        <div class="content-area text-start">

                            <!-- TOP INFO -->
                            <div class="data-details d-md-flex mb-4">
                                <div class="me-4">
                                    <span class="data-details-title">Invoice Date</span>
                                    <span class="data-details-info">${invoice.created_at}</span>
                                </div>

                                <div class="me-4">
                                    <span class="data-details-title">Invoice No</span>
                                    <span class="data-details-info">${invoice.invoice_number}</span>
                                </div>

                                <div>
                                    <span class="data-details-title">Status</span>
                                    <span class="badge ${statusClass}">
                                        ${invoice.status.toUpperCase()}
                                    </span>
                                </div>
                            </div>

                            <!-- CUSTOMER -->
                            <ul class="data-details-list">
                                <li>
                                    <div class="data-details-head">Customer</div>
                                    <div class="data-details-des">${invoice.customer_name}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Email</div>
                                    <div class="data-details-des">${invoice.customer_email}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Served By</div>
                                    <div class="data-details-des">${invoice.admin_name || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Payment Method</div>
                                    <div class="data-details-des">
                                        ${invoice.payment_method 
                                            ? invoice.payment_method.charAt(0).toUpperCase() + invoice.payment_method.slice(1)
                                            : '—'}
                                    </div>
                                </li>
                            </ul>

                            <!-- ITEMS TABLE -->
                            <div class="table-responsive mt-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHTML || '<tr><td colspan="4" class="text-center">No items</td></tr>'}
                                    </tbody>
                                </table>
                            </div>

                            <!-- TOTALS -->
                            <div class="mt-4 text-end">
                                <p>Subtotal: ₦${parseFloat(invoice.subtotal).toLocaleString()}</p>
                                <p>Discount: ₦${parseFloat(invoice.discount).toLocaleString()}</p>
                                <p>Tax: ₦${parseFloat(invoice.tax).toLocaleString()}</p>
                                <h5><strong>Total: ₦${parseFloat(invoice.total_amount).toLocaleString()}</strong></h5>
                            </div>

                        </div>
                    `;

                } catch (error) {
                    console.error(error);
                    newConfirmMessage.innerHTML = `<div class="text-danger text-center">Network or server error.</div>`;
                }
            }

            // =========================
            // CLOSE MODAL
            // =========================
            invoiceModal.addEventListener('click', e => {
                if (
                    e.target.classList.contains('modal-close') ||
                    e.target.classList.contains('btn-close') ||
                    e.target === invoiceModal
                ) {
                    invoiceModal.classList.remove('show');
                    invoiceModal.style.display = 'none';
                }
            });

        });
    </script>

</body>

</html>