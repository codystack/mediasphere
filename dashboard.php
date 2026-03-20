<?php
include "./components/header.php";

require_once('./config/db.php');

try {
    // Total site visits
    $stmt = $pdo->query("SELECT COUNT(*) AS total_visits FROM traffic");
    $totalVisits = $stmt->fetchColumn();

    // Total visits from the previous month
    $stmt2 = $pdo->query("
        SELECT COUNT(*) AS last_month_visits 
        FROM traffic 
        WHERE visit_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
          AND visit_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $lastMonthVisits = $stmt2->fetchColumn();

} catch (PDOException $e) {
    $totalVisits = 0;
    $lastMonthVisits = 0;
    error_log("Traffic count error: " . $e->getMessage());
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
} catch (Exception $e) {
    error_log("Error fetching user count: " . $e->getMessage());
    $totalUsers = 0;
}

// try {
//     $stmt = $pdo->query("SELECT COUNT(*) AS total_applications FROM pof_application");
//     $totalApplications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];
// } catch (Exception $e) {
//     error_log("Error fetching application count: " . $e->getMessage());
//     $totalApplications = 0;
// }

// Fetch all Proof of Funds applications
$stmt = $pdo->query("SELECT * FROM pof_application ORDER BY id DESC LIMIT 5");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'approved':
            return ['bg-soft-success text-success', 'Approved'];
        case 'pending':
            return ['bg-soft-warning text-warning', 'Pending'];
        case 'rejected':
            return ['bg-soft-danger text-danger', 'Rejected'];
        case 'closed':
            return ['bg-soft-tertiary text-tertiary', 'Closed'];
        default:
            return ['bg-soft-secondary text-secondary', ucfirst($status ?: 'Unknown')];
    }
}

// Fetch total disbursed amount
$stmt = $pdo->query("SELECT SUM(amount) AS total_amount FROM transactions WHERE status = 'Disbursed'");
$total = $stmt->fetch(PDO::FETCH_ASSOC);

// If no transactions, total_amount will be NULL
$total_amount = $total['total_amount'] ?? 0;

// Fetch total revenue amount
$stmt = $pdo->query("SELECT SUM(amount) AS total_revenue FROM payment_proofs WHERE status = 'Verified'");
$total = $stmt->fetch(PDO::FETCH_ASSOC);

// If no approved of funds, total_revenue will be NULL
$total_revenue = $total['total_revenue'] ?? 0;


// Fetch all payment proofs with user info
$stmt = $pdo->query("
    SELECT pp.*, u.first_name, u.last_name 
    FROM payment_proofs pp
    LEFT JOIN users u ON pp.user_id = u.id
    ORDER BY pp.id ASC LIMIT 4
");
$payment_proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);


try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_devices FROM devices");
    $totalDevices = $stmt->fetch(PDO::FETCH_ASSOC)['total_devices'];
} catch (Exception $e) {
    error_log("Error fetching devices count: " . $e->getMessage());
    $totalDevces = 0;
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
                                    <div class="row mb-3 mt-3">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Amount Disbursed</span>
                                            <span class="h3 font-bold mb-0">₦<?= number_format($total_amount, 2) ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-primary text-white text-2xl rounded-circle">
                                                <i class="bi bi-cash-stack"></i>
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
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Total Users</span> 
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
                        <div class="col-xl-4 col-sm-6 col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3 mt-3">
                                        <div class="col">
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Total Devices</span> 
                                            <span class="h3 font-bold mb-0"><?= number_format($totalDevices) ?></span></div>
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
                                            <span class="h6 font-semibold text-muted text-sm d-block mb-2">Site Traffic</span>
                                            <span class="h3 font-bold mb-0"><?= number_format($totalVisits) ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape icon-lg bg-warning text-white text-xl rounded-circle">
                                                <i class="bi bi-stoplights"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-0 text-sm">
                                        <span class="badge badge-pill bg-soft-warning text-warning me-2"><?= number_format($lastMonthVisits) ?></span>
                                        <span class="text-nowrap text-xs text-muted">Last Month</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-6 mb-6">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header border-bottom d-flex align-items-center">
                                    <h5 class="mb-0">Latest Applications</h5>
                                    <div class="ms-auto text-end">
                                        <a href="proof-of-funds" class="text-sm font-semibold">View all</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <?php if ($applications): ?>
                                    <table class="table table-hover table-nowrap">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Applicant</th>
                                                <th scope="col">Loan Amount</th>
                                                <th scope="col">Duration</th>
                                                <th scope="col">Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($applications as $app): 
                                                [$badge, $label] = getStatusBadge($app['status']);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                            <i class="bi bi-file-earmark-pdf"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="d-inline-block h6 font-semibold mb-1" href="#"><?= htmlspecialchars($app['first_name'].' '.$app['last_name']) ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>₦<?= number_format($app['loan_amount'], 2) ?></td>
                                                <td><?= htmlspecialchars($app['loan_duration_months']) ?> Months</td>
                                                <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-primary btn-square view-application" data-id="<?= $app['id'] ?>"><i class="bi bi-eye"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php else: ?>
                                        <div style="position: relative; height: 250px;">
                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" class="text-center">
                                                <img src="./assets/img/no-data.png" width="150" alt="No Devices">
                                                <p class="mt-3 lead">No application yet</p>
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
    include "./modal/application-modal.php";
    ?>

    <script src="./assets/js/main.js"></script>

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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentApplicationId = null;

            const applicationModal = document.getElementById('applicationModal');
            const confirmButton = document.getElementById('confirmButton');
            const confirmMessage = document.getElementById('confirmMessage');

            if (!applicationModal || !confirmButton || !confirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // Handle "View Application" button click
            document.querySelectorAll('.view-application').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentApplicationId = button.dataset.id;

                    confirmMessage.innerHTML = `
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Loading application details...</p>
                        </div>
                    `;
                    confirmButton.style.display = 'none';

                    // Show system modal
                    applicationModal.classList.add('show');
                    applicationModal.style.display = 'block';

                    // Fetch application details
                    loadApplicationDetails(currentApplicationId);
                });
            });

            async function loadApplicationDetails(applicationId) {
                try {
                    const response = await fetch('./auth/pof_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: applicationId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        confirmMessage.innerHTML = `<div class="text-danger text-center">${data.message || 'Application not found.'}</div>`;
                        return;
                    }

                    const app = data.application;

                    confirmMessage.innerHTML = `
                        <div class="content-area text-start">
                            <div class="data-details d-md-flex mb-5">
                                <div class="fake-class">
                                    <span class="data-details-title">Application Date</span>
                                    <span class="data-details-info">${app.created_at}</span>
                                </div>

                                <div class="fake-class"></div>

                                <div class="fake-class">
                                    <span class="data-details-title">Status</span>
                                    <span class="badge ${app.status === 'approved' ? 'bg-soft-success text-success' : app.status === 'rejected' ? 'bg-soft-danger text-danger' : app.status === 'pending' ? 'bg-soft-warning text-warning' : app.status === 'closed' ? 'bg-soft-tertiary text-tertiary' : 'bg-soft-secondary'} ucap">${app.status.toUpperCase()}</span>
                                </div>
                            </div>

                            <h6 class="card-sub-title mt-5 mb-2">Personal Details</h6>
                            <ul class="data-details-list">
                                <li>
                                    <div class="data-details-head">Full Name</div>
                                    <div class="data-details-des">${app.first_name || ''} ${app.last_name || ''} ${app.other_names || ''}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Maiden Name</div>
                                    <div class="data-details-des">${app.mothers_maiden_name || '—'}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Email</div>
                                    <div class="data-details-des">${app.email_address || '—'}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Phone</div>
                                    <div class="data-details-des">${app.phone_number || '—'}</div>
                                </li>
                                
                                <li>
                                    <div class="data-details-head">Date of Birth</div>
                                    <div class="data-details-des">${app.date_of_birth || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Place of Birth</div>
                                    <div class="data-details-des">${app.place_of_birth || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Gender</div>
                                    <div class="data-details-des">${app.gender || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Nationality</div>
                                    <div class="data-details-des">${app.nationality || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">State of Origin</div>
                                    <div class="data-details-des">${app.state_of_origin || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">LGA</div>
                                    <div class="data-details-des">${app.lga || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Hometown</div>
                                    <div class="data-details-des">${app.hometown || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Marital Status</div>
                                    <div class="data-details-des">${app.marital_status || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Religion</div>
                                    <div class="data-details-des">${app.religion || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Occupation</div>
                                    <div class="data-details-des">${app.occupation || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Workplace</div>
                                    <div class="data-details-des">${app.workplace || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Workplace Address</div>
                                    <div class="data-details-des">${app.workplace_address || '—'}</div>
                                </li>
                            </ul>

                            <h6 class="card-sub-title mt-5 mb-2">Next of Kin</h6>
                            <ul class="data-details-list mb-5">
                                <li>
                                    <div class="data-details-head">Full Name</div>
                                    <div class="data-details-des">${app.kin_first_name || ''} ${app.kin_last_name || ''}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Email</div>
                                    <div class="data-details-des">${app.kin_email || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Phone</div>
                                    <div class="data-details-des">${app.kin_phone || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Date of Birth</div>
                                    <div class="data-details-des">${app.kin_date_of_birth || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Gender</div>
                                    <div class="data-details-des">${app.kin_gender || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Relationship</div>
                                    <div class="data-details-des">${app.kin_relationship || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Address</div>
                                    <div class="data-details-des">${app.kin_residential_address || '—'}</div>
                                </li>
                            </ul>

                            <h6 class="card-sub-title mt-5 mb-2">Loan Details</h6>
                            <ul class="data-details-list mb-5">
                                <li>
                                    <div class="data-details-head">Loan Amount</div>
                                    <div class="data-details-des">₦${parseFloat(app.loan_amount || 0).toLocaleString()}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Bank Type</div>
                                    <div class="data-details-des">${app.bank_type || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Duration (Months)</div>
                                    <div class="data-details-des">${app.loan_duration_months || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Start Date</div>
                                    <div class="data-details-des">${app.loan_start_date || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">End Date</div>
                                    <div class="data-details-des">${app.loan_end_date || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Purpose</div>
                                    <div class="data-details-des">${app.purpose_of_fund || '—'}</div>
                                </li>
                            </ul>

                            <h6 class="card-sub-title mt-4 mb-2">Identity & Documents</h6>
                            <ul class="data-details-list">
                                <li>
                                    <div class="data-details-head">ID Type</div>
                                    <div class="data-details-des">${app.id_type || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">ID Number</div>
                                    <div class="data-details-des">${app.id_number || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">ID Expiry</div>
                                    <div class="data-details-des">${app.id_expiry_date || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">NIN</div>
                                    <div class="data-details-des">${app.nin || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Means of Identity</div>
                                    <div class="data-details-des"><a href="http://localhost/blinkscore_app/${app.means_of_identity}" target="_blank">View Document</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Proof of Travel</div>
                                    <div class="data-details-des"><a href="http://localhost/blinkscore_app/${app.proof_of_travel}" target="_blank">View</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Utility Bill</div>
                                    <div class="data-details-des"><a href="http://localhost/blinkscore_app/${app.utility_bill}" target="_blank">View</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Passport Photo</div>
                                    <div class="data-details-des"><a href="http://localhost/blinkscore_app/${app.passport_photo}" target="_blank">View</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Signature Sample</div>
                                    <div class="data-details-des"><a href="http://localhost/blinkscore_app/${app.signature_sample}" target="_blank">View</a></div>
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
            applicationModal.addEventListener('click', e => {
                if (e.target.classList.contains('modal-close') || e.target === applicationModal) {
                    applicationModal.classList.remove('show');
                    applicationModal.style.display = 'none';
                }
            });
        });
    </script>


</body>

</html>