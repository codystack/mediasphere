<?php
include "./components/header.php";

require_once('./config/db.php');


try {
    // Total transactions
    $stmt = $pdo->query("SELECT COUNT(*) AS total_transactions FROM transactions");
    $totalTransactions = $stmt->fetchColumn();

    // Total transactions from the previous month
    $stmt2 = $pdo->query("
        SELECT COUNT(*) AS last_month_transactions 
        FROM transactions 
        WHERE created_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
          AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $lastMonthTransactions = $stmt2->fetchColumn();

} catch (PDOException $e) {
    $totalTransactions = 0;
    $lastMonthTransactions = 0;
    error_log("Transaction count error: " . $e->getMessage());
}

try {
    // Total completed amount
    $stmt = $pdo->query("
        SELECT SUM(total_amount) AS total_amount 
        FROM transactions 
        WHERE status = 'completed'
    ");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_amount = $total['total_amount'] ?? 0;

    // Last month completed amount
    $stmt2 = $pdo->query("
        SELECT SUM(total_amount) AS last_month_amount 
        FROM transactions 
        WHERE status = 'completed'
        AND created_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
        AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $lastMonth = $stmt2->fetch(PDO::FETCH_ASSOC);
    $last_month_amount = $lastMonth['last_month_amount'] ?? 0;

} catch (PDOException $e) {
    $total_amount = 0;
    $last_month_amount = 0;
    error_log("Revenue error: " . $e->getMessage());
}


try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
} catch (Exception $e) {
    error_log("Error fetching user count: " . $e->getMessage());
    $totalUsers = 0;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_products FROM products");
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];
} catch (Exception $e) {
    error_log("Error fetching products count: " . $e->getMessage());
    $totalProducts = 0;
}

// Fetch transactions
try {
    // Fetch latest 5 transactions
    $stmt = $pdo->query("
        SELECT 
            t.*,
            u.first_name,
            u.last_name,
            p.name AS product_name
        FROM transactions t
        LEFT JOIN users u ON t.customer_id = u.id
        LEFT JOIN products p ON t.product_id = p.id
        ORDER BY t.id DESC
        LIMIT 5
    ");

    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $transactions = [];
    error_log("Top transactions error: " . $e->getMessage());
}

// Fetch transaction status badge
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
            return ['bg-soft-secondary text-secondary', ucfirst($status ?: 'Unknown')];
    }
}


// Fetch total disbursed amount
// $stmt = $pdo->query("SELECT SUM(amount) AS total_amount FROM transactions WHERE status = 'Disbursed'");
// $total = $stmt->fetch(PDO::FETCH_ASSOC);

// If no transactions, total_amount will be NULL
$total_amount = $total['total_amount'] ?? 0;

// Fetch total revenue amount
// $stmt = $pdo->query("SELECT SUM(amount) AS total_revenue FROM payment_proofs WHERE status = 'Verified'");
// $total = $stmt->fetch(PDO::FETCH_ASSOC);

// If no approved of funds, total_revenue will be NULL
$total_revenue = $total['total_revenue'] ?? 0;


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
                                <h1 class="ls-tight"><span style="font-weight: 300">Hello,</span> <?= $firstName ?></h1>
                                <span class="eyebrow mb-1" id="greet"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid">
                    <div class="row g-6 mb-6">
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">
                                                Total Transactions
                                            </span>
                                            <span class="h3 font-bold mb-0">
                                                <?= number_format($totalTransactions) ?>
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-primary text-white text-xl rounded-circle">
                                                <i class="bi bi-receipt"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-0 text-sm">
                                        <span class="badge badge-pill bg-soft-primary text-primary me-2">
                                            <?= number_format($lastMonthTransactions) ?>
                                        </span>
                                        <span class="text-nowrap text-xs text-muted">Last Month</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3 mt-3">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Total Customers</span> 
                                            <span class="h3 font-bold mb-0"><?= number_format($totalUsers) ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg text-white text-2xl rounded-circle" style="background-color: #5c60f5;">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3 mt-3">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Total Products</span> 
                                            <span class="h3 font-bold mb-0"><?= number_format($totalProducts) ?></span></div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-info text-white text-2xl rounded-circle">
                                                <i class="bi bi-pc-display-horizontal"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">
                                                Total Revenue
                                            </span>
                                            <span class="h3 font-bold mb-0">
                                                ₦<?= number_format($total_amount, 2) ?>
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-danger text-white text-xl rounded-circle">
                                                <i class="bi bi-cash-stack"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-0 text-sm">
                                        <span class="badge badge-pill bg-soft-success text-success me-2">
                                            ₦<?= number_format($last_month_amount, 2) ?>
                                        </span>
                                        <span class="text-nowrap text-xs text-muted">Last Month</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3 mt-3">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Revenue Generated</span> 
                                            <span class="h3 font-bold mb-0">₦<?= number_format($total_revenue, 2) ?></span></div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-success text-white text-2xl rounded-circle">
                                                <i class="bi bi-bank2"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3 mt-3">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Total Applications</span> 
                                            <span class="h3 font-bold mb-0"><?= number_format($totalApplications) ?></span></div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-danger text-white text-2xl rounded-circle">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-6 mb-6">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header border-bottom d-flex align-items-center">
                                    <h5 class="mb-0">Latest Transactions</h5>
                                    <div class="ms-auto text-end">
                                        <a href="transactions" class="text-sm font-semibold">View all</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <?php if (!empty($transactions)): ?>
                                    <table class="table table-hover table-nowrap">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Product</th>
                                                <th scope="col">Amount</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transactions as $transaction): 
                                                [$badge, $label] = getStatusBadge($transaction['status']);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                            <i class="bi bi-cart"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="d-inline-block h6 font-semibold mb-1">
                                                                <?= htmlspecialchars($transaction['product_name'] ?? '—') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    ₦<?= number_format($transaction['total_amount'], 2) ?>
                                                </td>

                                                <td class="text-end">
                                                    <button 
                                                        class="btn btn-sm btn-primary view-transaction btn-square" 
                                                        data-id="<?= $transaction['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <?php else: ?>
                                        <div style="position: relative; height: 250px;">
                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" class="text-center">
                                                <img src="./assets/img/no-data.png" width="150" alt="No Transactions">
                                                <p class="mt-3 lead">No transactions yet</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card h-full">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-center">
                                        <h5 class="mb-0">Latest Payment Proofs</h5>
                                        <div class="ms-auto text-end">
                                            <a href="payment-proofs" class="text-sm font-semibold">See all</a>
                                        </div>
                                    </div>
                                    <div class="list-group gap-4">
                                        <?php if ($payment_proofs): ?>
                                        <?php foreach ($payment_proofs as $proof): ?>
                                            <div class="list-group-item d-flex align-items-center border rounded">
                                                <div class="me-4">
                                                    <div class="avatar rounded-circle">
                                                        <img alt="icon" src="./assets/img/bank-icon.svg">
                                                    </div>
                                                </div>
                                                <div class="flex-fill">
                                                    <a href="#" class="d-block h6 font-semibold mb-1">
                                                        <?= htmlspecialchars($proof['first_name'] . ' ' . $proof['last_name']); ?>
                                                    </a>
                                                    <span class="d-block text-sm text-muted">
                                                        ₦<?= number_format($proof['amount'], 2) ?> 
                                                    </span>
                                                </div>
                                                <div class="ms-auto text-end">
                                                    <div class="dropdown">
                                                        <a class="text-muted" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </a>
                                                        <div class="dropdown-menu">
                                                            <a href="https://app.blinkscore.ng/<?= htmlspecialchars($proof['file_path']) ?>" target="_blank" class="dropdown-item">View Proof</a>
                                                            <a href="payment-proofs" class="dropdown-item">View All</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php else: ?>
                                            <div style="position: relative; height: 250px;">
                                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" class="text-center">
                                                    <img src="./assets/img/no-data-icon.svg" width="90" alt="No Devices">
                                                    <p class="mt-3 lead">No device yet</p>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php
    include "./modal/modal.php";
    include "./modal/transaction-modal.php";
    ?>

    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        //Greet User
        var time = new Date().getHours();
        if (time < 4) {
            greeting = "You should be in bed 🙄!";
        }  else if (time < 12) {
            greeting = "Good morning, wash your hands 🌤";
        } else if (time < 16) {
            greeting = "It's lunch 🍛 time, what's on the menu!";
        } else {
            greeting = "Good Evening 🌙, how was your day?";
        }
        document.getElementById("greet").innerHTML = greeting;
    </script>

    <!-- Display Transactions -->
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