<?php
include "./components/header.php";
require_once('./config/db.php');

// =======================
// FETCH TRANSACTIONS
// =======================
$stmt = $pdo->query("
    SELECT 
        t.*,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        p.name AS product_name,
        a.first_name AS admin_first,
        a.last_name AS admin_last
    FROM transactions t
    LEFT JOIN users u ON t.customer_id = u.id
    LEFT JOIN products p ON t.product_id = p.id
    LEFT JOIN admin a ON t.admin_id = a.id
    ORDER BY t.id DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =======================
// STATUS BADGE
// =======================
function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));

    switch ($s) {
        case 'completed':
            return ['bg-soft-success text-success', 'Completed'];

        case 'processing':
            return ['bg-soft-warning text-warning', 'Processing'];

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
        <header>
            <div class="container-fluid">
                <div class="pt-6">
                    <div class="row align-items-center">
                        <div class="col-sm col-12">
                            <h1 class="h2 ls-tight">Invoices</h1>
                        </div>
                        <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                            <div class="hstack gap-2 justify-content-sm-end">
                                <a href="#offcanvasAddNewInvoice" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                    <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                    <span>Create New Invoice</span>
                                </a>
                            </div>
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

                        <?php if (count($transactions) > 0): ?>
                        <table class="table table-hover" id="invoices">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($transactions as $transaction): 
                                    [$badgeClass, $statusText] = getStatusBadge($transaction['status']);
                                ?>
                                <tr>

                                    <!-- CUSTOMER -->
                                    <td>
                                        <?= htmlspecialchars(($transaction['transaction_ref'] ?? '')) ?>
                                    </td>

                                    <!-- PRODUCT -->
                                    <td><?= htmlspecialchars($transaction['product_name'] ?? '—') ?></td>

                                    <!-- QTY -->
                                    <td><?= (int)$transaction['quantity'] ?></td>

                                    <!-- TOTAL -->
                                    <td>₦<?= number_format($transaction['total_amount'], 2) ?></td>

                                    <!-- STATUS -->
                                    <td>
                                        <span class="badge <?= $badgeClass ?> rounded-pill">
                                            <?= $statusText ?>
                                        </span>
                                    </td>

                                    <!-- DATE -->
                                    <td><?= date('d M Y, h:i A', strtotime($transaction['created_at'])) ?></td>

                                    <!-- ACTIONS -->
                                    <td class="text-end">

                                        <!-- VIEW Transaction -->
                                        <button 
                                            class="btn btn-sm btn-neutral view-transaction btn-square" 
                                            data-id="<?= $transaction['id'] ?>">
                                           <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- VIEW RECEIPT -->
                                        <a href="./receipt.php?transaction_ref=<?= $transaction['transaction_ref'] ?>"
                                           target="_blank"
                                           class="btn btn-sm btn-dark btn-square">
                                           <i class="bi bi-receipt"></i>
                                        </a>

                                        <!-- PROCESSING -->
                                        <button 
                                            class="btn btn-sm btn-warning transaction-processing btn-square" 
                                            data-id="<?= $transaction['id'] ?>">
                                            <i class="bi bi-hourglass"></i>
                                        </button>

                                        <!-- COMPLETED -->
                                        <button 
                                            class="btn btn-sm btn-success transaction-completed btn-square" 
                                            data-id="<?= $transaction['id'] ?>">
                                            <i class="bi bi-check-circle"></i>
                                        </button>

                                        <!-- CANCEL -->
                                        <button 
                                            class="btn btn-sm btn-danger transaction-cancelled btn-square" 
                                            data-id="<?= $transaction['id'] ?>">
                                            <i class="bi bi-x-circle"></i>
                                        </button>

                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-square btn-danger delete-transaction" 
                                            data-id="<?= $transaction['id'] ?>" 
                                            data-name="<?= htmlspecialchars($transaction['product_name']) ?>" 
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
                                <p class="mt-3">No transactions yet</p>
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
    include "./modal/edit-incoice-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

                
    <!-- Change Status -->
     <script>
        $(document).ready(() => {
            $('#invoices').DataTable();

            const notyf = new Notyf();

            // Processing
            $('.transaction-processing').click(function() {
                const id = $(this).data('id');
                fetch('./auth/transaction_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'processing' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        notyf.error(data.message);
                    }
                });
            });

            // Completed
            $('.transaction-completed').click(function() {
                const id = $(this).data('id');
                fetch('./auth/transaction_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'completed' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        notyf.error(data.message);
                    }
                });
            });

            // Cancelled
            $('.transaction-cancelled').click(function() {
                const id = $(this).data('id');
                fetch('./auth/transaction_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'cancelled' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        notyf.error(data.message);
                    }
                });
            });
        });
    </script>


    <!-- Delete transaction -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentTransactionId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE transaction =========
            document.querySelectorAll('.delete-transaction').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentTransactionId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this transaction';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b>.<br>This action cannot be undone.`;
                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentTransactionId || !currentAction) return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    const response = await fetch('./auth/transaction_delete_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentTransactionId })
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notyf.error(data.message || 'Operation failed.');
                    }
                } catch (error) {
                    console.error(error);
                    notyf.error('Network or server error.');
                }

                confirmButton.disabled = false;
                confirmButton.textContent = 'Delete';
            });
        });
    </script>

    <!-- Dispplay transaction -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentTransactionId = null;

            const transactionModal = document.getElementById('transactionModal');
            const confirmButton = document.getElementById('confirmButton');
            const confirmMessage = document.getElementById('confirmMessage');

            if (!transactionModal || !confirmButton || !confirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // Handle "View Transaction" button click
            document.querySelectorAll('.view-transaction').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentTransactionId = button.dataset.id;

                    confirmMessage.innerHTML = `
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Loading transaction details...</p>
                        </div>
                    `;
                    confirmButton.style.display = 'none';

                    // Show system modal
                    transactionModal.classList.add('show');
                    transactionModal.style.display = 'block';

                    // Fetch transaction details
                    loadTransactionDetails(currentTransactionId);
                });
            });

            async function loadTransactionDetails(transactionId) {
                try {
                    const response = await fetch('./auth/transaction_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: transactionId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        confirmMessage.innerHTML = `<div class="text-danger text-center">${data.message || 'Transaction not found.'}</div>`;
                        return;
                    }

                    const transaction = data.transaction;

                    confirmMessage.innerHTML = `
                        <div class="content-area text-start">
                            <div class="data-details d-md-flex mb-5">
                                <div class="fake-class">
                                    <span class="data-details-title">Transaction Date</span>
                                    <span class="data-details-info">${transaction.created_at}</span>
                                </div>

                                <div class="fake-class">
                                    <span class="data-details-title">Reference</span>
                                    <span class="data-details-info">${transaction.transaction_ref}</span>
                                </div>

                                <div class="fake-class">
                                    <span class="data-details-title">Status</span>
                                    <span class="badge ${transaction.status === 'processing' ? 'bg-soft-warning text-warning' : transaction.status === 'completed' ? 'bg-soft-success text-success' : transaction.status === 'cancelled' ? 'bg-soft-danger text-danger' : 'bg-soft-secondary'} ucap">${transaction.status.toUpperCase()}</span>
                                </div>
                            </div>

                            <ul class="data-details-list">
                                <li>
                                    <div class="data-details-head">Customer</div>
                                    <div class="data-details-des">${transaction.customer_first || ''} ${transaction.customer_last || ''}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Product</div>
                                    <div class="data-details-des">${transaction.product_name || '—'}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Price</div>
                                    <div class="data-details-des">₦${parseFloat(transaction.price || 0).toLocaleString()}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Quantity</div>
                                    <div class="data-details-des">${transaction.quantity || '—'}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Total</div>
                                    <div class="data-details-des">₦${parseFloat(transaction.total_amount || 0).toLocaleString()}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Mode of Payment</div>
                                    <div class="data-details-des">
                                        ${transaction.payment_method 
                                            ? transaction.payment_method.charAt(0).toUpperCase() + transaction.payment_method.slice(1) 
                                            : '—'}
                                    </div>
                                </li>

                                <li>
                                    <div class="data-details-head">Served by</div>
                                    <div class="data-details-des">${transaction.admin_first || ''} ${transaction.admin_last || ''}</div>
                                </li>
                            </ul>
                        </div>
                    `;
                } catch (error) {
                    console.error(error);
                    confirmMessage.innerHTML = `<div class="text-danger text-center">Network or server error.</div>`;
                }
            }

            // Close modal
            transactionModal.addEventListener('click', e => {
                if (
                    e.target.classList.contains('modal-close') ||
                    e.target.classList.contains('btn-close') || // added this
                    e.target === transactionModal
                ) {
                    transactionModal.classList.remove('show');
                    transactionModal.style.display = 'none';
                }
            });

        });
    </script>


</body>

</html>