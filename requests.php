<?php
include "./components/header.php";

require_once('./config/db.php');


// Fetch all staff requests with employee names
$stmt = $pdo->query("
    SELECT r.*, a.first_name, a.last_name
    FROM requests r
    LEFT JOIN admin a ON r.admin_id = a.id
    ORDER BY r.id DESC
");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Helper to get a status badge style and label for requests
 */
function getRequestStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'approved':
            return ['bg-soft-success text-success', 'Approved'];
        case 'pending':
            return ['bg-soft-warning text-warning', 'Pending'];
        case 'rejected':
            return ['bg-soft-danger text-danger', 'Rejected'];
        case 'completed':
            return ['bg-soft-info text-info', 'Completed'];
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
                                <h1 class="h2 ls-tight">Requests</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewRequest" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>New Requests</span>
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
                                <?php if (count($requests) > 0): ?>
                                <table class="table table-hover table-nowrap" id="requests">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Requested By</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Date Requested</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $request): ?>
                                            <?php [$badgeClass, $statusText] = getRequestStatusBadge($request['status']); ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                            <i class="bi bi-file-zip"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="d-inline-block h6 font-semibold mb-1">
                                                                <?= htmlspecialchars($request['request_title'] ?? 'Untitled Request') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td style="max-width: 200px; white-space: normal;">
                                                    <?= htmlspecialchars(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '')) ?>
                                                </td>

                                                <td style="max-width: 200px; white-space: normal;">
                                                    <?= htmlspecialchars($request['category'] ?? 'General') ?>
                                                </td>

                                                <td style="max-width: 200px; white-space: normal;">
                                                    <?= !empty($request['request_date']) ? date('M d, Y', strtotime($request['request_date'])) : '—' ?>
                                                </td>

                                                <td>
                                                    <span class="badge <?= $badgeClass ?> text-uppercase rounded-pill"><?= $statusText ?></span>
                                                </td>

                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-request btn-square" data-id="<?= $request['id'] ?>">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>

                                                    <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover reject-request btn-square" data-id="<?= $request['id'] ?>">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>

                                                    <button class="btn btn-sm btn-neutral bg-info-hover text-white-hover complete-request btn-square" data-id="<?= $request['id'] ?>">
                                                        <i class="bi bi-list-check"></i>
                                                    </button>

                                                    <button class="btn btn-sm btn-primary btn-square view-request" data-id="<?= $request['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <a href="#"
                                                        class="btn btn-sm btn-square btn-warning edit-request"
                                                        data-id="<?= htmlspecialchars($request['id']) ?>"
                                                        data-title="<?= htmlspecialchars($request['request_title'] ?? '') ?>"
                                                        data-type="<?= htmlspecialchars($request['request_type'] ?? '') ?>"
                                                        data-priority="<?= htmlspecialchars($request['priority'] ?? '') ?>"
                                                        data-description="<?= htmlspecialchars($request['description'] ?? '') ?>"
                                                        data-request-date="<?= htmlspecialchars($request['request_date'] ?? '') ?>"
                                                        data-remark="<?= htmlspecialchars($request['superior_remark'] ?? '') ?>"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#offcanvasEditRequest">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-square btn-danger delete-request" 
                                                        data-id="<?= $request['id'] ?>" 
                                                        data-name="<?= htmlspecialchars(($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? '')) ?>" 
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
                                            <img src="./assets/img/no-data.png" width="150" alt="No Requests">
                                            <p class="mt-3 lead">No request recorded yet</p>
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
    include "./modal/new-request-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/request-modal.php";
    include "./modal/edit-request-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('offcanvasAddNewRequest');
            let editors = {};

            // Initialize CKEditor when offcanvas opens
            offcanvasEl.addEventListener('shown.bs.offcanvas', async () => {
                const fields = ['description', 'superior_remark'];

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
            const offcanvasEl = document.getElementById('offcanvasEditRequest');
            let editors = {};

            // Initialize CKEditor when offcanvas opens
            offcanvasEl.addEventListener('shown.bs.offcanvas', async () => {
                const fields = ['edit-description', 'edit-superior_remark'];

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
            $('#requests').DataTable();
        });
    </script>

    <!-- Create Request -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewRequest form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                // Detect if it's super-admin (Review Request) or staff (Submit Request)
                const isReview = submitBtn.textContent.toLowerCase().includes('review');

                // Update button while submitting
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${isReview ? 'Reviewing...' : 'Submitting...'}`;

                try {
                    const response = await fetch('./auth/create_request_auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        form.reset();

                        // Auto-close offcanvas manually without Bootstrap
                        const offcanvasEl = document.getElementById('offcanvasAddNewRequest');
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
                    submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-send"></i></span>${isReview ? 'Review Request' : 'Send Request'}`;
                }
            });
        });
    </script>


    <!-- Delete Request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentRequestId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE REQUEST =========
            document.querySelectorAll('.delete-request').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();

                    currentRequestId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this request';

                    confirmMessage.innerHTML = `
                        You are about to permanently delete<br>
                        <b>${name}'s</b> request.<br>
                        This action <strong>cannot</strong> be undone.
                    `;

                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentRequestId || currentAction !== 'delete') return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2"></span>Processing...
                `;

                try {
                    const response = await fetch('./auth/request_delete_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentRequestId })
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message || 'Request deleted successfully.');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notyf.error(data.message || 'Failed to delete request.');
                    }
                } catch (error) {
                    console.error('Error deleting request:', error);
                    notyf.error('Network or server error.');
                } finally {
                    confirmButton.disabled = false;
                    confirmButton.textContent = 'Delete';
                }
            });
        });
    </script>


    <!-- Change request status -->
    <script>
        $(document).ready(() => {
            const notyf = new Notyf();

            // Approve request
            $('.approve-request').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_request_status_auth.php', {
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

            // Reject request
            $('.reject-request').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_request_status_auth.php', {
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

            // Complete request
            $('.complete-request').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_request_status_auth.php', {
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
        });
    </script>

    <!-- View request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentRequestId = null;

            const requestModal = document.getElementById('requestModal');
            const confirmMessage = document.getElementById('confirmMessage');

            if (!requestModal || !confirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // Handle "View Request" button click
            document.querySelectorAll('.view-request').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentRequestId = button.dataset.id;

                    confirmMessage.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Loading request details...</p>
                        </div>
                    `;

                    // Show modal manually (no Bootstrap)
                    requestModal.classList.add('show');
                    requestModal.style.display = 'block';

                    // Fetch request details
                    loadRequestDetails(currentRequestId);
                });
            });

            async function loadRequestDetails(requestId) {
                try {
                    const response = await fetch('./auth/request_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: requestId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        confirmMessage.innerHTML = `
                            <div class="text-danger text-center py-4">
                                ${data.message || 'Request not found.'}
                            </div>
                        `;
                        return;
                    }

                    const request = data.request;

                    confirmMessage.innerHTML = `
                        <div class="content-area text-start p-3">
                            <div class="data-details d-md-flex mb-4 justify-content-between">
                                <div>
                                    <span class="data-details-title fw-bold d-block">Submitted On</span>
                                    <span class="data-details-info">${request.created_at || '—'}</span>
                                </div>

                                <div>
                                    <span class="data-details-title fw-bold d-block">Status</span>
                                    <span class="badge ${
                                        request.status === 'Approved'
                                            ? 'bg-soft-success text-success'
                                            : request.status === 'Pending'
                                            ? 'bg-soft-warning text-warning'
                                            : request.status === 'Rejected'
                                            ? 'bg-soft-danger text-danger'
                                            : request.status === 'Completed'
                                            ? 'bg-soft-info text-info'
                                            : 'bg-soft-secondary text-secondary'
                                    }">
                                        ${request.status || 'Unknown'}
                                    </span>
                                </div>
                            </div>

                            <ul class="data-details-list list-unstyled">
                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Title</div>
                                    <div class="data-details-des">${request.request_title || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Category</div>
                                    <div class="data-details-des">${request.request_type || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Priority</div>
                                    <div class="data-details-des">${request.priority || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Request Date</div>
                                    <div class="data-details-des">${request.request_date || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Description</div>
                                    <div class="data-details-des">${request.description || '<em>No description</em>'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Attachment</div>
                                    <div class="data-details-des">
                                        ${request.attachment_path ? `<a href="${request.attachment_path}" target="_blank">View Attachment</a>` : '<em>No attachment uploaded</em>'}
                                    </div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Superior Remark</div>
                                    <div class="data-details-des">
                                        ${request.superior_remark || '<em>No remark yet</em>'}
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

            // Close modal (simple, non-Bootstrap)
            requestModal.addEventListener('click', e => {
                if (e.target.classList.contains('modal-close') || e.target === requestModal) {
                    requestModal.classList.remove('show');
                    requestModal.style.display = 'none';
                }
            });
        });
    </script>

    <!-- Edit request -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditRequest');
            if (!offcanvasEl) return;

            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-request');

            // Make offcanvas scrollable
            offcanvasEl.style.overflowY = 'auto';
            offcanvasEl.style.maxHeight = '100vh';
            offcanvasEl.style.scrollBehavior = 'smooth';
            const offcanvasBody = offcanvasEl.querySelector('.offcanvas-body');
            if (offcanvasBody) {
                offcanvasBody.style.maxHeight = 'calc(100vh - 100px)';
                offcanvasBody.style.overflowY = 'auto';
            }

            // Prefill form when Edit button is clicked
            editButtons.forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();

                    // Prefill fields
                    form.querySelector('[name="request_id"]').value = button.dataset.id || '';
                    form.querySelector('[name="request_title"]').value = button.dataset.title || '';
                    form.querySelector('[name="request_type"]').value = button.dataset.type || '';
                    form.querySelector('[name="priority"]').value = button.dataset.priority || '';
                    form.querySelector('[name="description"]').value = button.dataset.description || '';
                    form.querySelector('[name="request_date"]').value = button.dataset.requestDate || '';

                    const superiorRemark = form.querySelector('[name="superior_remark"]');
                    if (superiorRemark) {
                        superiorRemark.value = button.dataset.remark || '';
                    }

                    // Update offcanvas header and submit button text
                    offcanvasEl.querySelector('.offcanvas-title').textContent = 'Edit Request';
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Request`;

                    // Show offcanvas manually (no Bootstrap)
                    offcanvasEl.classList.add('show');
                    offcanvasEl.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });

            // Handle form submission (Update Request)
            form.addEventListener('submit', async e => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                const formData = new FormData(form);

                try {
                    const response = await fetch('./auth/update_request_auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        form.reset();

                        setTimeout(() => {
                            offcanvasEl.classList.remove('show');
                            offcanvasEl.style.display = 'none';
                            document.body.style.overflow = '';
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
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Request`;
            });

            // Manual close (no Bootstrap dependency)
            offcanvasEl.addEventListener('click', e => {
                if (e.target.classList.contains('btn-close') || e.target === offcanvasEl) {
                    offcanvasEl.classList.remove('show');
                    offcanvasEl.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });
    </script>

</body>

</html>