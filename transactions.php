<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all transactions
$stmt = $pdo->query("
    SELECT 
        t.*, 
        u.first_name, 
        u.last_name, 
        u.email, 
        u.phone 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'disbursed':
            return ['bg-soft-success text-success', 'Disbursed'];
        case 'approved':
            return ['bg-soft-warning text-warning', 'Approved'];
        case 'closed':
            return ['bg-soft-danger text-danger', 'Closed'];
        default:
            return ['bg-soft-secondary text-secondary', ucfirst($status ?: 'Unknown')];
    }
}
?>
    <div class="d-flex flex-column flex-lg-row h-lg-full bg-surface-secondary">
        <?php include "./components/side-nav.php"; ?>

        <div class="flex-lg-1 h-screen overflow-y-lg-auto">
            <?php include "./components/top-nav.php"; ?>

            <header>
                <div class="container-fluid">
                    <div class="pt-6">
                        <div class="row align-items-center">
                            <div class="col-sm col-12">
                                <h1 class="h2 ls-tight">Transactions</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewOffer" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>Add New Offer</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid">
                    <div class="vstack gap-4">
                        <div class="card">
                            <div class="table-responsive px-10 py-10">
                                <?php if (count($transactions) > 0): ?>
                                <table class="table table-hover table-nowrap" id="transactions">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Fee</th>
                                            <th scope="col">Percentage</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($transactions as $transaction):
                                                [$badgeClass, $statusText] = getStatusBadge($transaction['status']);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                        <i class="bi bi-arrow-down-up"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="d-inline-block h6 font-semibold mb-1" href="#"><?= htmlspecialchars(($transaction['first_name'] ?? '') . ' ' . ($transaction['last_name'] ?? '')) ?></td></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₦<?= number_format($transaction['amount'], 2) ?></td>
                                            <td>₦<?= number_format($transaction['fee'], 2) ?></td>
                                            <td><?= htmlspecialchars($transaction['percentage'] ?? '—') ?>%</td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?> text-uppercase rounded-pill"><?= $statusText ?></span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-application btn-square" data-id="<?= $transaction['id'] ?>"><i class="bi bi-check-circle"></i></button>
                                                <button class="btn btn-sm btn-neutral bg-warning-hover text-white-hover disburse-application btn-square" data-id="<?= $transaction['id'] ?>"><i class="bi bi-cash"></i></button>
                                                <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover close-application btn-square" data-id="<?= $transaction['id'] ?>"><i class="bi bi-x-circle"></i></button>
                                                <button class="btn btn-sm btn-primary btn-square view-transaction" data-id="<?= $transaction['id'] ?>"><i class="bi bi-eye"></i></button>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-square btn-danger delete-transaction" 
                                                    data-id="<?= $transaction['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']) ?>" 
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
                                    <div style="position: relative; height: 250px;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" class="text-center">
                                            <img src="./assets/img/no-data.png" width="150" alt="No Devices">
                                            <p class="mt-3 lead">No Transaction offers yet</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php 
    include "./modal/new-offer-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/transaction-modal.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(() => {
            $('#transactions').DataTable();

            const notyf = new Notyf();

            // Approve
            $('.approve-application').click(function() {
                const id = $(this).data('id');
                fetch('./auth/transaction_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'approved' })
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

            // Disburse
            $('.disburse-application').click(function() {
                const id = $(this).data('id');
                fetch('./auth/transaction_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'disbursed' })
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

            // Close
            $('.close-application').click(function() {
                const id = $(this).data('id');
                fetch('./auth/transaction_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'closed' })
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf({
                duration: 3000,
                position: { x: 'right', y: 'top' },
                dismissible: true
            });

            const createOfferForm = document.getElementById('createOfferForm');
            if (!createOfferForm) return;

            createOfferForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    const formData = new FormData(this);
                    const response = await fetch('./auth/create_offer_auth.php', {
                        method: 'POST',
                        body: new URLSearchParams([...formData])
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        notyf.error(data.message || 'Failed to create offer.');
                    }
                } catch (error) {
                    console.error(error);
                    notyf.error('Network or server error.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `
                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>
                        Create Offer
                    `;
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE APPLICATION =========
            document.querySelectorAll('.delete-transaction').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this transaction';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b> transaction.<br>This action cannot be undone.`;
                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentUserId || !currentAction) return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    const response = await fetch('./auth/transaction_delete_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentUserId })
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
                                    <span class="data-details-info">${transaction.reference_number}</span>
                                </div>

                                <div class="fake-class">
                                    <span class="data-details-title">Status</span>
                                    <span class="badge ${transaction.status === 'Approved' ? 'bg-soft-warning text-warning' : transaction.status === 'Disbursed' ? 'bg-soft-success text-success' : transaction.status === 'Closed' ? 'bg-soft-danger text-danger' : 'bg-soft-secondary'} ucap">${transaction.status.toUpperCase()}</span>
                                </div>
                            </div>

                            <ul class="data-details-list">
                                <li>
                                    <div class="data-details-head">Full Name</div>
                                    <div class="data-details-des">${transaction.first_name || ''} ${transaction.last_name || ''}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Transaction Type</div>
                                    <div class="data-details-des">${transaction.transaction_type || '—'}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Amount</div>
                                    <div class="data-details-des">₦${parseFloat(transaction.amount || 0).toLocaleString()}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Transaction Fee</div>
                                    <div class="data-details-des">₦${parseFloat(transaction.fee || 0).toLocaleString()}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Percentage</div>
                                    <div class="data-details-des">${transaction.percentage || '—'}%</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Duration</div>
                                    <div class="data-details-des">${transaction.duration || '—'} Months</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Start Date</div>
                                    <div class="data-details-des">${transaction.start_date || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">End Date</div>
                                    <div class="data-details-des">${transaction.end_date || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Remark</div>
                                    <div class="data-details-des">${transaction.remarks || '—'}</div>
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
                if (e.target.classList.contains('modal-close') || e.target === transactionModal) {
                    transactionModal.classList.remove('show');
                    transactionModal.style.display = 'none';
                }
            });

        });
    </script>


</body>

</html>