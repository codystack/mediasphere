<?php
include "./components/header.php";

require_once('./config/db.php');


// Fetch all leaves with optional employee name if needed
$stmt = $pdo->query("
    SELECT l.*, a.first_name, a.last_name 
    FROM leave_applications l
    LEFT JOIN admin a ON l.admin_id = a.id
    ORDER BY l.id DESC
");

$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to get status badge
function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'approved':
            return ['bg-soft-success text-success', 'Approved'];
        case 'pending':
            return ['bg-soft-warning text-warning', 'Pending'];
        case 'rejected':
            return ['bg-soft-danger text-danger', 'Rejected'];
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
                                <h1 class="h2 ls-tight">Leaves</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewLeave" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>New Leave</span>
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
                                <?php if (count($leaves) > 0): ?>
                                <table class="table table-hover table-nowrap" id="leaves">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Leave Type</th>
                                            <th scope="col">Duration</th>
                                            <th scope="col">Start Date</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leaves as $leave): ?>
                                            <?php [$badgeClass, $statusText] = getStatusBadge($leave['status']); ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                            <i class="bi bi-file-earmark-medical"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="d-inline-block h6 font-semibold mb-1">
                                                                <?= htmlspecialchars(($leave['first_name'] ?? '') . ' ' . ($leave['last_name'] ?? '')) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($leave['leave_type']); ?></td>
                                                <td><?= htmlspecialchars($leave['duration']); ?></td>
                                                <td><?= htmlspecialchars($leave['start_date']); ?></td>
                                                <td>
                                                    <span class="badge <?= $badgeClass ?> text-uppercase rounded-pill"><?= $statusText ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-leave btn-square" data-id="<?= $leave['id'] ?>"><i class="bi bi-check-circle"></i></button>
                                                    <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover reject-leave btn-square" data-id="<?= $leave['id'] ?>"><i class="bi bi-x-circle"></i></button>
                                                    <button class="btn btn-sm btn-primary btn-square view-leave" data-id="<?= $leave['id'] ?>"><i class="bi bi-eye"></i></button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-square btn-danger delete-leave" 
                                                        data-id="<?= $leave['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?>" 
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
                                            <img src="./assets/img/no-data.png" width="150" alt="No Leaves">
                                            <p class="mt-3 lead">No leave application yet</p>
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
    include "./modal/new-leave-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/leave-modal.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#leaves').DataTable();
        });
    </script>

    <!-- Create leave -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewLeave form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Submitting...`;

                try {
                    const response = await fetch('./auth/create_leave_auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        form.reset();
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        notyf.error(data.message);
                    }
                } catch (error) {
                    notyf.error('Network or server error.');
                    console.error(error);
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>Apply for Leave`;
            });
        });
    </script>


    <!-- Change leave status -->
    <script>
        $(document).ready(() => {
            const notyf = new Notyf();

            // Approve Leave
            $('.approve-leave').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_leave_status_auth.php', {
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

            // Reject Leave
            $('.reject-leave').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_leave_status_auth.php', {
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
        });
    </script>


    <!-- Delete leave -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE LEAVE =========
            document.querySelectorAll('.delete-leave').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this leave';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b> leave.<br>This action cannot be undone.`;
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
                    const response = await fetch('./auth/leave_delete_auth.php', {
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


    <!-- View leave -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentLeaveId = null;

            const leaveModal = document.getElementById('leaveModal');
            const confirmMessage = document.getElementById('confirmMessage');

            if (!leaveModal || !confirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // Handle "View Leave" button click
            document.querySelectorAll('.view-leave').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentLeaveId = button.dataset.id;

                    confirmMessage.innerHTML = `
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Loading leave details...</p>
                        </div>
                    `;

                    // Show modal
                    leaveModal.classList.add('show');
                    leaveModal.style.display = 'block';

                    // Fetch leave details
                    loadLeaveDetails(currentLeaveId);
                });
            });

            async function loadLeaveDetails(leaveId) {
                try {
                    const response = await fetch('./auth/leave_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: leaveId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        confirmMessage.innerHTML = `<div class="text-danger text-center">${data.message || 'Leave not found.'}</div>`;
                        return;
                    }

                    const leave = data.leave;

                    confirmMessage.innerHTML = `
                        <div class="content-area text-start">
                            <div class="data-details d-md-flex mb-5">
                                <div class="fake-class">
                                    <span class="data-details-title">Applied On</span>
                                    <span class="data-details-info">${leave.created_at || '—'}</span>
                                </div>

                                <div class="fake-class">
                                    <span class="data-details-title">Status</span>
                                    <span class="badge ${leave.status === 'Approved' ? 'bg-soft-success text-success' : leave.status === 'Pending' ? 'bg-soft-warning text-warning' : leave.status === 'Rejected' ? 'bg-soft-danger text-danger' : 'bg-soft-secondary'} ucap">
                                        ${leave.status || 'Unknown'}
                                    </span>
                                </div>
                            </div>

                            <ul class="data-details-list">
                                <li>
                                    <div class="data-details-head">Full Name</div>
                                    <div class="data-details-des">${leave.first_name || ''} ${leave.last_name || ''}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Leave Type</div>
                                    <div class="data-details-des">${leave.leave_type || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Duration</div>
                                    <div class="data-details-des">${leave.duration || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Start Date</div>
                                    <div class="data-details-des">${leave.start_date || '—'}</div>
                                </li>

                                <li>
                                    <div class="data-details-head">Reason</div>
                                    <div class="data-details-des">${leave.reason || '—'}</div>
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
            leaveModal.addEventListener('click', e => {
                if (e.target.classList.contains('modal-close') || e.target === leaveModal) {
                    leaveModal.classList.remove('show');
                    leaveModal.style.display = 'none';
                }
            });

        });
    </script>



</body>

</html>