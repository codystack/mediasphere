<?php
include "./components/header.php";

require_once('./config/db.php');


// Fetch all staff expenses with employee name
$stmt = $pdo->query("
    SELECT e.*, a.first_name, a.last_name 
    FROM expenses e
    LEFT JOIN admin a ON e.admin_id = a.id
    ORDER BY e.id DESC
");

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to get status badge styling
function getExpenseStatusBadge(string $status = ''): array {
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
                                <h1 class="h2 ls-tight">Expenses</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewExpenses" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>New Expenses</span>
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
                                <?php if (count($expenses) > 0): ?>
                                <table class="table table-hover table-nowrap" id="expenses">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Expenses By</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Date for expenses</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expenses as $expense): ?>
                                            <?php [$badgeClass, $statusText] = getExpenseStatusBadge($expense['status']); ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                            <i class="bi bi-list-check"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="d-inline-block h6 font-semibold mb-1">
                                                                <?= htmlspecialchars($expense['expense_title']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="max-width: 200px; white-space: normal;">
                                                    <?= htmlspecialchars(($expense['first_name'] ?? '') . ' ' . ($expense['last_name'] ?? '')) ?>
                                                </td>
                                                <td style="max-width: 200px; white-space: normal;">
                                                    ₦<?= number_format($expense['amount'], 2) ?>
                                                </td>
                                                <td style="max-width: 200px; white-space: normal;">
                                                    <?= date('M d, Y', strtotime($expense['expense_date'])) ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $badgeClass ?> text-uppercase rounded-pill"><?= $statusText ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-expense btn-square" data-id="<?= $expense['id'] ?>">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover reject-expense btn-square" data-id="<?= $expense['id'] ?>">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary btn-square view-expense" data-id="<?= $expense['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="#"
                                                        class="btn btn-sm btn-square btn-warning edit-expense"
                                                        data-id="<?= htmlspecialchars($expense['id']) ?>"
                                                        data-title="<?= htmlspecialchars($expense['expense_title'] ?? '') ?>"
                                                        data-amount="<?= htmlspecialchars(number_format($expense['amount'], 2)) ?>"
                                                        data-category="<?= htmlspecialchars($expense['category'] ?? '') ?>"
                                                        data-description="<?= htmlspecialchars($expense['description'] ?? '') ?>"
                                                        data-expense-date="<?= htmlspecialchars($expense['expense_date'] ?? '') ?>"
                                                        data-remark="<?= htmlspecialchars($expense['superior_remark'] ?? '') ?>"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#offcanvasEditExpense">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-square btn-danger delete-expense" 
                                                        data-id="<?= $expense['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($expense['first_name'] . ' ' . $expense['last_name']) ?>" 
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
                                            <img src="./assets/img/no-data.png" width="150" alt="No Expenses">
                                            <p class="mt-3 lead">No expense recorded yet</p>
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
    include "./modal/new-expenses-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/expense-modal.php";
    include "./modal/edit-expense-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('offcanvasAddNewExpenses');
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
            const offcanvasEl = document.getElementById('offcanvasEditExpense');
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
            $('#expenses').DataTable();
        });
    </script>

    <!-- Create Create -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewExpenses form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                // Detect if it's super-admin (Review Expense) or staff (Send Expenses)
                const isReview = submitBtn.textContent.toLowerCase().includes('review');

                // Update button while submitting
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${isReview ? 'Reviewing...' : 'Submitting...'}`;

                try {
                    const response = await fetch('./auth/create_expenses_auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        form.reset();

                        // Auto-close offcanvas manually without Bootstrap
                        const offcanvasEl = document.getElementById('offcanvasAddNewExpenses');
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
                    submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>${isReview ? 'Review Expense' : 'Send Expenses'}`;
                }
            });
        });
    </script>

    <!-- Delete Report -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentExpenseId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE EXPENSE =========
            document.querySelectorAll('.delete-expense').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();

                    currentExpenseId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this expense';

                    confirmMessage.innerHTML = `
                        You are about to permanently delete<br>
                        <b>${name}</b> expense record.<br>
                        This action <strong>cannot</strong> be undone.
                    `;

                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentExpenseId || currentAction !== 'delete') return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2"></span>Processing...
                `;

                try {
                    const response = await fetch('./auth/expense_delete_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentExpenseId })
                    });

                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message || 'Expense deleted successfully.');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notyf.error(data.message || 'Failed to delete expense.');
                    }
                } catch (error) {
                    console.error('Error deleting expense:', error);
                    notyf.error('Network or server error.');
                } finally {
                    confirmButton.disabled = false;
                    confirmButton.textContent = 'Delete';
                }
            });
        });
    </script>

    <!-- Change expense status -->
    <script>
        $(document).ready(() => {
            const notyf = new Notyf();

            // Approve Expense
            $('.approve-expense').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_expense_status_auth.php', {
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

            // Reject Expense
            $('.reject-expense').click(function() {
                const id = $(this).data('id');
                fetch('./auth/update_expense_status_auth.php', {
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

    <!-- View expense -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentExpenseId = null;

            const expenseModal = document.getElementById('expenseModal');
            const confirmMessage = document.getElementById('confirmMessage');

            if (!expenseModal || !confirmMessage) {
                console.error('Modal elements not found.');
                return;
            }

            // Handle "View Expense" button click
            document.querySelectorAll('.view-expense').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentExpenseId = button.dataset.id;

                    confirmMessage.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Loading expense details...</p>
                        </div>
                    `;

                    // Show modal (non-Bootstrap)
                    expenseModal.classList.add('show');
                    expenseModal.style.display = 'block';

                    // Fetch expense details
                    loadExpenseDetails(currentExpenseId);
                });
            });

            async function loadExpenseDetails(expenseId) {
                try {
                    const response = await fetch('./auth/expense_view_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: expenseId })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        confirmMessage.innerHTML = `
                            <div class="text-danger text-center py-4">
                                ${data.message || 'Expense not found.'}
                            </div>
                        `;
                        return;
                    }

                    const expense = data.expense;

                    confirmMessage.innerHTML = `
                        <div class="content-area text-start p-3">
                            <div class="data-details d-md-flex mb-4 justify-content-between">
                                <div>
                                    <span class="data-details-title fw-bold d-block">Submitted On</span>
                                    <span class="data-details-info">${expense.created_at || '—'}</span>
                                </div>

                                <div>
                                    <span class="data-details-title fw-bold d-block">Status</span>
                                    <span class="badge ${
                                        expense.status === 'Approved'
                                            ? 'bg-soft-success text-success'
                                            : expense.status === 'Pending'
                                            ? 'bg-soft-warning text-warning'
                                            : expense.status === 'Rejected'
                                            ? 'bg-soft-danger text-danger'
                                            : 'bg-soft-secondary text-secondary'
                                    }">
                                        ${expense.status || 'Unknown'}
                                    </span>
                                </div>
                            </div>

                            <ul class="data-details-list list-unstyled">
                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Title</div>
                                    <div class="data-details-des">${expense.expense_title || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Category</div>
                                    <div class="data-details-des">${expense.category || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Amount</div>
                                    <div class="data-details-des">${expense.amount ? parseFloat(expense.amount).toLocaleString() : '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Expense Date</div>
                                    <div class="data-details-des">${expense.expense_date || '—'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Description</div>
                                    <div class="data-details-des">${expense.description || '<em>No description</em>'}</div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Receipt</div>
                                    <div class="data-details-des">
                                        ${expense.receipt_path ? `<a href="${expense.receipt_path}" target="_blank">View Receipt</a>` : '<em>No receipt uploaded</em>'}
                                    </div>
                                </li>

                                <li class="mb-3">
                                    <div class="data-details-head fw-bold">Superior Remark</div>
                                    <div class="data-details-des">
                                        ${expense.superior_remark || '<em>No remark yet</em>'}
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
            expenseModal.addEventListener('click', e => {
                if (e.target.classList.contains('modal-close') || e.target === expenseModal) {
                    expenseModal.classList.remove('show');
                    expenseModal.style.display = 'none';
                }
            });
        });
    </script>

    <!-- Edit expense -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditExpense');
            if (!offcanvasEl) return;

            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-expense');

            // Make offcanvas scrollable
            offcanvasEl.style.overflowY = 'auto';
            offcanvasEl.style.maxHeight = '100vh';
            offcanvasEl.style.scrollBehavior = 'smooth';
            const offcanvasBody = offcanvasEl.querySelector('.offcanvas-body');
            if (offcanvasBody) {
                offcanvasBody.style.maxHeight = 'calc(100vh - 100px)';
                offcanvasBody.style.overflowY = 'auto';
            }

            // Prefill the edit form when "Edit" is clicked
            editButtons.forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();

                    // Prefill fields from data attributes
                    form.querySelector('[name="expense_id"]').value = button.dataset.id || '';
                    form.querySelector('[name="expense_title"]').value = button.dataset.title || '';
                    form.querySelector('[name="amount"]').value = button.dataset.amount?.replace(/\B(?=(\d{3})+(?!\d))/g, ",") || '';
                    form.querySelector('[name="category"]').value = button.dataset.category || '';
                    form.querySelector('[name="description"]').value = button.dataset.description || '';
                    form.querySelector('[name="expense_date"]').value = button.dataset.expenseDate || '';

                    const superiorRemark = form.querySelector('[name="superior_remark"]');
                    if (superiorRemark) {
                        superiorRemark.value = button.dataset.remark || '';
                    }

                    // Update offcanvas title and button text
                    offcanvasEl.querySelector('.offcanvas-title').textContent = 'Edit Expense';
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Expense`;

                    // Show offcanvas manually
                    offcanvasEl.classList.add('show');
                    offcanvasEl.style.display = 'block';
                    document.body.style.overflow = 'hidden'; // prevent background scroll
                });
            });

            // Format amount input with thousand separators
            const amountInput = form.querySelector('[name="amount"]');
            if (amountInput) {
                amountInput.addEventListener('input', () => {
                    let value = amountInput.value.replace(/,/g, '');
                    if (!isNaN(value) && value !== '') {
                        amountInput.value = Number(value).toLocaleString('en-US');
                    } else {
                        amountInput.value = '';
                    }
                });
            }

            // Handle expense update
            form.addEventListener('submit', async e => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                const formData = new FormData(form);
                // Remove commas before sending
                if (formData.has('amount')) {
                    formData.set('amount', formData.get('amount').replace(/,/g, ''));
                }

                try {
                    const response = await fetch('./auth/update_expense_auth.php', {
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
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Expense`;
            });

            // Manual close event
            offcanvasEl.addEventListener('click', e => {
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