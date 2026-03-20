<?php
include "./components/header.php";

require_once('./config/db.php');


// Fetch all staff weekly reports with employee name
$stmt = $pdo->query("
    SELECT r.*, a.first_name, a.last_name 
    FROM reports r
    LEFT JOIN admin a ON r.admin_id = a.id
    ORDER BY r.id DESC
");

$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to get status badge styling
function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'approved':
            return ['bg-soft-success text-success', 'Approved'];
        case 'reviewed':
            return ['bg-soft-info text-info', 'Reviewed'];
        case 'rejected':
            return ['bg-soft-danger text-danger', 'Rejected'];
        case 'submitted':
            return ['bg-soft-warning text-warning', 'Submitted'];
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
                                <h1 class="h2 ls-tight">Reports</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewReport" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>Add New Report</span>
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
                                <?php if (count($reports) > 0): ?>
                                <table class="table table-hover table-nowrap" id="reports">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Submission Date</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reports as $report): ?>
                                            <?php [$badgeClass, $statusText] = getStatusBadge($report['status']); ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                            <i class="bi bi-person-lines-fill"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="d-inline-block h6 font-semibold mb-1">
                                                                <?= htmlspecialchars(($report['first_name'] ?? '') . ' ' . ($report['last_name'] ?? '')) ?>'s Report
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="max-width: 200px; white-space: normal;">
                                                    <?= date('M d, Y', strtotime($report['created_at'])) ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $badgeClass ?> text-uppercase rounded-pill"><?= $statusText ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-neutral bg-info-hover text-white-hover review-report btn-square" data-id="<?= $report['id'] ?>"><i class="bi bi-clipboard-check"></i></button>
                                                    <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-report btn-square" data-id="<?= $report['id'] ?>"><i class="bi bi-check-circle"></i></button>
                                                    <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover reject-report btn-square" data-id="<?= $report['id'] ?>"><i class="bi bi-x-circle"></i></button>
                                                    <button class="btn btn-sm btn-primary btn-square view-report" data-id="<?= $report['id'] ?>"><i class="bi bi-eye"></i></button>
                                                    <a href="#" 
                                                        class="btn btn-sm btn-square btn-warning edit-report"
                                                        data-id="<?= htmlspecialchars($report['id']) ?>"
                                                        data-tasks="<?= htmlspecialchars($report['tasks_completed']) ?>"
                                                        data-notes="<?= htmlspecialchars($report['issues_or_notes']) ?>"
                                                        data-remark="<?= htmlspecialchars($report['superior_remark']) ?>"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#offcanvasEditReport">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-square btn-danger delete-report" 
                                                        data-id="<?= $report['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($report['first_name'] . ' ' . $report['last_name']) ?>" 
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
                                            <img src="./assets/img/no-data.png" width="150" alt="No Reports">
                                            <p class="mt-3 lead">No report yet</p>
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
    include "./modal/new-report-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/report-modal.php";
    include "./modal/edit-report-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('offcanvasAddNewReport');
            let editors = {};

            // Initialize CKEditor when offcanvas opens
            offcanvasEl.addEventListener('shown.bs.offcanvas', async () => {
                const fields = ['tasks_completed', 'issues_or_notes', 'superior_remark', 'edit_tasks_completed', 'edit_issues_or_notes', 'edit_superior_remark'];

                for (const field of fields) {
                    const textarea = document.getElementById(field);
                    if (!textarea || editors[field]) continue;

                    try {
                        const editor = await ClassicEditor.create(textarea, {
                            toolbar: [
                                'bold', 'italic', 'underline', '|',
                                'bulletedList', 'numberedList', '|',
                                'undo', 'redo'
                            ]
                        });

                        editor.ui.view.editable.element.style.height = '150px';
                        editors[field] = editor;
                        console.log(`✅ CKEditor initialized for #${field}`);
                    } catch (error) {
                        console.error(`❌ Failed to initialize #${field}:`, error);
                    }
                }
            });

            // Destroy CKEditors when offcanvas closes to prevent duplication
            offcanvasEl.addEventListener('hidden.bs.offcanvas', async () => {
                for (const field in editors) {
                    if (editors[field]) {
                        await editors[field].destroy();
                        console.log(`🧹 CKEditor destroyed for #${field}`);
                        delete editors[field];
                    }
                }
            });

            // Before submitting, sync CKEditor content to textareas
            const form = offcanvasEl.querySelector('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    for (const id in editors) {
                        if (editors[id]) {
                            form.querySelector(`#${id}`).value = editors[id].getData();
                        }
                    }
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('offcanvasEditReport');
            let editors = {};

            // Initialize CKEditor when offcanvas opens
            offcanvasEl.addEventListener('shown.bs.offcanvas', async () => {
                const fields = ['edit_tasks_completed', 'edit_issues_or_notes', 'edit_superior_remark'];

                for (const field of fields) {
                    const textarea = document.getElementById(field);
                    if (!textarea || editors[field]) continue;

                    try {
                        const editor = await ClassicEditor.create(textarea, {
                            toolbar: [
                                'bold', 'italic', 'underline', '|',
                                'bulletedList', 'numberedList', '|',
                                'undo', 'redo'
                            ]
                        });

                        editor.ui.view.editable.element.style.height = '150px';
                        editors[field] = editor;
                        console.log(`✅ CKEditor initialized for #${field}`);
                    } catch (error) {
                        console.error(`❌ Failed to initialize #${field}:`, error);
                    }
                }
            });

            // Destroy CKEditors when offcanvas closes to prevent duplication
            offcanvasEl.addEventListener('hidden.bs.offcanvas', async () => {
                for (const field in editors) {
                    if (editors[field]) {
                        await editors[field].destroy();
                        console.log(`🧹 CKEditor destroyed for #${field}`);
                        delete editors[field];
                    }
                }
            });

            // Before submitting, sync CKEditor content to textareas
            const form = offcanvasEl.querySelector('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    for (const id in editors) {
                        if (editors[id]) {
                            form.querySelector(`#${id}`).value = editors[id].getData();
                        }
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#reports').DataTable();
        });
    </script>

    <!-- Create Report -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewReport form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                // Detect if it's super-admin (Review Report) or staff (Send Report)
                const isReview = submitBtn.textContent.toLowerCase().includes('review');

                // Update button while submitting
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${isReview ? 'Reviewing...' : 'Submitting...'}`;

                try {
                    const response = await fetch('./auth/create_report_auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        form.reset();

                        // Auto-close offcanvas manually without Bootstrap
                        const offcanvasEl = document.getElementById('offcanvasAddNewReport');
                        if (offcanvasEl) {
                            offcanvasEl.classList.remove('show');
                            offcanvasEl.style.visibility = 'hidden';
                            offcanvasEl.style.transform = 'translateX(100%)';
                            document.body.classList.remove('offcanvas-backdrop', 'offcanvas-open');
                        }

                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        notyf.error(data.message || 'Something went wrong.');
                    }
                } catch (error) {
                    notyf.error('Network or server error.');
                    console.error(error);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>${isReview ? 'Review Report' : 'Send Report'}`;
                }
            });
        });
    </script>

    <!-- Delete Report -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE LEAVE =========
            document.querySelectorAll('.delete-report').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this report';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b> report.<br>This action cannot be undone.`;
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
                    const response = await fetch('./auth/report_delete_auth.php', {
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

    <!-- Change report status -->
    <script>
        $(document).ready(() => {
            const notyf = new Notyf();

            // Approve Report
            $('.approve-report').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_report_status_auth.php', {
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

            // Review Report
            $('.review-report').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_report_status_auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'reviewed' })
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

            // Reject Report
            $('.reject-report').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_report_status_auth.php', {
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

    <!-- View report -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentReportId = null;

            const reportModal = document.getElementById('reportModal');
            const confirmMessage = document.getElementById('confirmMessage');

            if (!reportModal || !confirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // Handle "View Report" button click
            document.querySelectorAll('.view-report').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentReportId = button.dataset.id;

                    confirmMessage.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Loading report details...</p>
                        </div>
                    `;

                    // Show modal (non-Bootstrap)
                    reportModal.classList.add('show');
                    reportModal.style.display = 'block';

                    // Fetch report details
                    loadReportDetails(currentReportId);
                });
            });

            async function loadReportDetails(reportId) {
                try {
                    const response = await fetch('./auth/report_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: reportId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        confirmMessage.innerHTML = `
                            <div class="text-danger text-center py-4">
                                ${data.message || 'Report not found.'}
                            </div>
                        `;
                        return;
                    }

                    const report = data.report;

                    confirmMessage.innerHTML = `
                        <div class="content-area text-start p-3">
                            <div class="data-details d-md-flex mb-4 justify-content-between">
                                <div>
                                    <span class="data-details-title fw-bold d-block">Submitted On</span>
                                    <span class="data-details-info">${report.created_at || '—'}</span>
                                </div>

                                <div>
                                    <span class="data-details-title fw-bold d-block">Status</span>
                                    <span class="badge ${
                                        report.status === 'Approved'
                                            ? 'bg-soft-success text-success'
                                            : report.status === 'Submitted'
                                            ? 'bg-soft-warning text-warning'
                                            : report.status === 'Reviewed'
                                            ? 'bg-soft-info text-info'
                                            : report.status === 'Rejected'
                                            ? 'bg-soft-danger text-danger'
                                            : 'bg-soft-secondary text-secondary'
                                    }">
                                        ${report.status || 'Unknown'}
                                    </span>
                                </div>
                            </div>

                            <ul class="data-details-list list-unstyled">
                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Full Name</div>
                                    <div class="data-details-des">${report.first_name || ''} ${report.last_name || ''}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Tasks Completed</div>
                                    <div class="data-details-des">
                                        <div>
                                            ${report.tasks_completed || '—'}
                                        </div>
                                    </div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Notes / Issues</div>
                                    <div class="data-details-des">
                                        <div>
                                            ${report.issues_or_notes || '—'}
                                        </div>
                                    </div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Superior Remark</div>
                                    <div class="data-details-des">
                                        <div>
                                            ${report.superior_remark || '<em>No remark yet</em>'}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    `;
                } catch (error) {
                    console.error(error);
                    confirmMessage.innerHTML = `
                        <div class="text-danger text-center py-4">Network or server error.</div>
                    `;
                }
            }

            // Close modal logic (simple, no Bootstrap)
            reportModal.addEventListener('click', e => {
                if (e.target.classList.contains('modal-close') || e.target === reportModal) {
                    reportModal.classList.remove('show');
                    reportModal.style.display = 'none';
                }
            });
        });
    </script>

    <!-- Edit report -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditReport');
            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-report');

            // ✅ Make offcanvas scrollable
            offcanvasEl.style.overflowY = 'auto';
            offcanvasEl.style.maxHeight = '100vh'; // Limit height to viewport
            offcanvasEl.style.scrollBehavior = 'smooth';
            offcanvasEl.querySelector('.offcanvas-body').style.maxHeight = 'calc(100vh - 100px)';
            offcanvasEl.querySelector('.offcanvas-body').style.overflowY = 'auto';

            // Prefill the edit form when "Edit" is clicked
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Fill form fields from data attributes
                    form.querySelector('[name="report_id"]').value = button.dataset.id || '';
                    form.querySelector('[name="tasks_completed"]').value = button.dataset.tasks || '';
                    form.querySelector('[name="issues_or_notes"]').value = button.dataset.notes || '';
                    const superiorRemark = form.querySelector('[name="superior_remark"]');
                    if (superiorRemark) {
                        superiorRemark.value = button.dataset.remark || '';
                    }

                    // Update offcanvas title and button text
                    offcanvasEl.querySelector('.offcanvas-title').textContent = 'Edit Report';
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Report`;

                    // Show offcanvas manually (no Bootstrap)
                    offcanvasEl.classList.add('show');
                    offcanvasEl.style.display = 'block';
                    document.body.style.overflow = 'hidden'; // prevent background scroll
                });
            });

            // Handle report update
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                const formData = new FormData(form);

                try {
                    const response = await fetch('./auth/update_report_auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        form.reset();

                        // Close offcanvas manually
                        setTimeout(() => {
                            offcanvasEl.classList.remove('show');
                            offcanvasEl.style.display = 'none';
                            document.body.style.overflow = ''; // restore scroll
                            window.location.reload();
                        }, 1000);
                    } else {
                        notyf.error(data.message || 'An error occurred.');
                    }
                } catch (error) {
                    console.error(error);
                    notyf.error('Network or server error.');
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Report`;
            });

            // Manual close event
            offcanvasEl.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-close') || e.target === offcanvasEl) {
                    offcanvasEl.classList.remove('show');
                    offcanvasEl.style.display = 'none';
                    document.body.style.overflow = ''; // restore background scroll
                }
            });
        });
    </script>


</body>

</html>