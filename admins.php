<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all admins
$stmt = $pdo->query("SELECT * FROM admin ORDER BY id DESC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'active':
            return ['bg-soft-success text-success', 'Active'];
        case 'inactive':
            return ['bg-soft-warning text-warning', 'Inactive'];
        case 'suspended':
            return ['bg-soft-danger text-danger', 'Suspended'];
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
                                <h1 class="h2 ls-tight">Staff</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewStaff" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>Add New Staff</span>
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
                                <table class="table table-hover table-nowrap" id="admin">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Designation</th>
                                            <th scope="col">Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($admins) > 0): ?>
                                            <?php foreach ($admins as $admin): 
                                                [$badge, $action] = getStatusBadge($admin['status'] ?? '');
                                            ?>
                                        <tr>
                                            <td>
                                                <img alt="avatar" src="<?= htmlspecialchars($admin['picture']) ?>" class="avatar avatar-sm rounded-circle me-2">
                                                <a class="text-heading text-primary-hover font-semibold" href="#"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></a>
                                            </td>
                                            <td><?= htmlspecialchars($admin['email']) ?></td>
                                            <td><?= htmlspecialchars($admin['phone']) ?></td>
                                            <td><?= ucfirst(htmlspecialchars($admin['designation'])) ?></td>
                                            <td>
                                                <span class="badge <?= $badge ?> text-uppercase rounded-pill"><?= $action ?></span>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($admin['status'] === 'active'): ?>
                                                    <a href="#" 
                                                        class="btn btn-sm btn-square btn-danger suspend-admin" 
                                                        data-id="<?= $admin['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmActionModal">
                                                        <i class="bi bi-shield-slash"></i>
                                                    </a>
                                                <?php elseif ($admin['status'] === 'suspended'): ?>
                                                    <a href="#" 
                                                        class="btn btn-sm btn-square btn-success unsuspend-admin" 
                                                        data-id="<?= $admin['id'] ?>" 
                                                        data-name="<?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmActionModal">
                                                        <i class="bi bi-shield-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="#"
                                                    class="btn btn-sm btn-square btn-primary edit-admin"
                                                    data-id="<?= $admin['id'] ?>"
                                                    data-first="<?= htmlspecialchars($admin['first_name']) ?>"
                                                    data-last="<?= htmlspecialchars($admin['last_name']) ?>"
                                                    data-email="<?= htmlspecialchars($admin['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($admin['phone']) ?>"
                                                    data-gender="<?= htmlspecialchars($admin['gender']) ?>"
                                                    data-designation="<?= htmlspecialchars($admin['designation']) ?>"
                                                    data-status="<?= htmlspecialchars($admin['status']) ?>"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#offcanvasEditStaff">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-square btn-danger delete-admin" 
                                                    data-id="<?= $admin['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmActionModal">
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="9" class="text-center text-muted">No admin accounts found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php 
    include "./modal/new-admin-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/edit-admin-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#admin').DataTable();
        });
    </script>

    <!-- Create admin user account -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewStaff form');
            if (!form) return; // safely skip if form not found

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Creating...`;

                try {
                const response = await fetch('./auth/create_admin_auth.php', {
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
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>Admin New Admin`;
            });
        });
    </script>

    <!-- Change admin user status -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentAdminId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== SUSPEND =========
            document.querySelectorAll('.suspend-admin').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentAdminId = button.dataset.id;
                    currentAction = 'suspend';
                    const name = button.dataset.name || 'this admin';
                    confirmMessage.innerHTML = `Are you sure you want to suspend<br><b>${name}</b>?`;
                    confirmButton.textContent = 'Suspend';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'suspend';
                });
            });

            // ======== UNSUSPEND =========
            document.querySelectorAll('.unsuspend-admin').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentAdminId = button.dataset.id;
                    currentAction = 'unsuspend';
                    const name = button.dataset.name || 'this admin';
                    confirmMessage.innerHTML = `Are you sure you want to unsuspend<br><b>${name}</b>?`;
                    confirmButton.textContent = 'Unsuspend';
                    confirmButton.className = 'btn btn-success';
                    confirmButton.dataset.action = 'unsuspend';
                });
            });

            // ======== DELETE ADMIN =========
            document.querySelectorAll('.delete-admin').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentAdminId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this admin';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b>.<br>This action cannot be undone.`;
                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentAdminId || !currentAction) return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    let response;
                    let data;

                    if (currentAction === 'delete') {
                        // DELETE ADMIN
                        response = await fetch('./auth/delete_admin_auth.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ id: currentAdminId })
                        });
                        data = await response.json();
                    } else {
                        // SUSPEND/UNSUSPEND ADMIN
                        const newStatus = currentAction === 'suspend' ? 'suspended' : 'active';
                        response = await fetch('./auth/update_admin_status_auth.php', {
                            method: 'POST',
                            body: new URLSearchParams({
                                id: currentAdminId,
                                status: newStatus
                            })
                        });
                        data = await response.json();
                    }

                    // ====== HANDLE RESPONSE ======
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

                // Reset button text
                if (currentAction === 'suspend') confirmButton.textContent = 'Suspend';
                if (currentAction === 'unsuspend') confirmButton.textContent = 'Unsuspend';
                if (currentAction === 'delete') confirmButton.textContent = 'Delete';
            });
        });
    </script>

    <!-- Edit admin user account -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditStaff');
            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-admin');

            // === Handle "Edit" button click (prefill the form) ===
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Get admin data from button attributes
                    const id = button.dataset.id;
                    const first = button.dataset.first;
                    const last = button.dataset.last;
                    const email = button.dataset.email;
                    const phone = button.dataset.phone;
                    const gender = button.dataset.gender;
                    const designation = button.dataset.designation;

                    // Fill the form fields
                    form.querySelector('[name="admin_id"]').value = id;
                    form.querySelector('[name="first_name"]').value = first;
                    form.querySelector('[name="last_name"]').value = last;
                    form.querySelector('[name="email"]').value = email;
                    form.querySelector('[name="phone"]').value = phone;
                    form.querySelector('[name="gender"]').value = gender;
                    form.querySelector('[name="designation"]').value = designation;

                    // Update submit button text
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = `Update Admin Account`;
                });
            });

            // === Handle form submission (update admin) ===
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                    const formData = new FormData(form);

                    try {
                        const response = await fetch('./auth/update_admin_auth.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            notyf.success(data.message);
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            notyf.error(data.message);
                        }
                    } catch (error) {
                        console.error(error);
                        notyf.error('Network or server error.');
                    }

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `Update Admin Account`;
                });
            }
        });
    </script>



</body>

</html>