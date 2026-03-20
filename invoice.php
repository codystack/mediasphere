<?php
include "./components/header.php";
require_once('./config/db.php');

// Fetch all Proof of Funds applications
$stmt = $pdo->query("SELECT * FROM pof_application ORDER BY id DESC");
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
?>
<div class="d-flex flex-column flex-lg-row h-lg-full bg-surface-secondary">
    <?php include "./components/side-nav.php"; ?>

    <div class="flex-lg-1 h-screen overflow-y-lg-auto">
        <?php include "./components/top-nav.php"; ?>

        <header>
            <div class="container-fluid pt-6">
                <h1 class="h2 ls-tight">Proof of Funds Applications</h1>
            </div>
        </header>

        <main class="py-6 bg-surface-secondary">
            <div class="container-fluid">
                <div class="card">
                    <div class="table-responsive px-6 py-6">
                        <?php if ($applications): ?>
                        <table class="table table-hover" id="pofTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Applicant</th>
                                    <th>Loan Amount</th>
                                    <th>Duration</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Actions</th>
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
                                        <td><?= ucfirst(htmlspecialchars($app['purpose_of_fund'])) ?></td>
                                        <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                                        <td class="text-end">
                                            
                                            <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-application btn-square" data-id="<?= $app['id'] ?>"><i class="bi bi-check-circle"></i></button>
                                            <button class="btn btn-sm btn-neutral bg-warning-hover text-white-hover reject-application btn-square" data-id="<?= $app['id'] ?>"><i class="bi bi-exclamation-circle"></i></button>
                                            <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover close-application btn-square" data-id="<?= $app['id'] ?>"><i class="bi bi-x-circle"></i></button>
                                            <button class="btn btn-sm btn-primary btn-square view-application" data-id="<?= $app['id'] ?>"><i class="bi bi-eye"></i></button>
                                            <!-- <button class="btn btn-sm btn-warning btn-square edit-application" data-id="<?= $app['id'] ?>"><i class="bi bi-pencil"></i></button> -->
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-square btn-danger delete-pof-application" 
                                                data-id="<?= $app['id'] ?>" 
                                                data-name="<?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>" 
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
                                    <p class="mt-3 lead">No application yet</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(() => {
            $('#pofTable').DataTable();

            const notyf = new Notyf();

            // Approve Application
            $('.approve-application').click(function() {
                const id = $(this).data('id');
                fetch('./auth/pof_update_status.php', {
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

            // Reject Application
            $('.reject-application').click(function() {
                const id = $(this).data('id');
                fetch('./auth/pof_update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'rejected' })
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

            // Close Application
            $('.close-application').click(function() {
                const id = $(this).data('id');
                fetch('./auth/pof_update_status.php', {
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
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE APPLICATION =========
            document.querySelectorAll('.delete-pof-application').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this application';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b> application.<br>This action cannot be undone.`;
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
                    const response = await fetch('./auth/pof_delete_auth.php', {
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
                                    <div class="data-details-head">Duration</div>
                                    <div class="data-details-des">${app.loan_duration_months || '—'} Months</div>
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
                                    <div class="data-details-des"><a href="https://app.blinkscore.ng/${app.means_of_identity}" target="_blank">View Document</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Proof of Travel</div>
                                    <div class="data-details-des"><a href="https://app.blinkscore.ng/${app.proof_of_travel}" target="_blank">View</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Utility Bill</div>
                                    <div class="data-details-des"><a href="https://app.blinkscore.ng/${app.utility_bill}" target="_blank">View</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Passport Photo</div>
                                    <div class="data-details-des"><a href="https://app.blinkscore.ng/${app.passport_photo}" target="_blank">View</a></div>
                                </li>

                                <li>
                                    <div class="data-details-head">Signature Sample</div>
                                    <div class="data-details-des"><a href="https://app.blinkscore.ng/${app.signature_sample}" target="_blank">View</a></div>
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