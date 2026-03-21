<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                                <h1 class="h2 ls-tight">Customers</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNewCustomer" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>Add New Customer</span>
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
                                <?php if (count($users) > 0): ?>
                                <table class="table table-hover table-nowrap" id="users">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">SN</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Date Created</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $index => $user): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['phone']) ?></td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                            <td class="text-end">
                                                <a href="#"
                                                    class="btn btn-sm btn-square btn-primary edit-user"
                                                    data-id="<?= $user['id'] ?>"
                                                    data-first="<?= htmlspecialchars($user['first_name']) ?>"
                                                    data-last="<?= htmlspecialchars($user['last_name']) ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($user['phone']) ?>"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#offcanvasEditCustomer">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-square btn-danger delete-user" 
                                                    data-id="<?= $user['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>" 
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
                                            <img src="./assets/img/no-data.png" width="150" alt="No Customers">
                                            <p class="mt-3 lead">No customer yet</p>
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
    include "./modal/new-customer-offcanvas.php";
    include "./modal/modal.php";
    include "./modal/edit-customer-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#users').DataTable();
        });
    </script>

    <!-- Create customer -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector('#offcanvasAddNewCustomer form');
            if (!form) return; // safely skip if form not found

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const notyf = new Notyf();
                const formData = new FormData(form);

                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Creating...`;

                try {
                const response = await fetch('./auth/create_customer_auth.php', {
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
                submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>Add New Customer`;
            });
        });
    </script>

    <!-- Edit customer -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditCustomer');
            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-user');

            // === Handle "Edit" button click (prefill the form) ===
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();

                    // Get customer data from button attributes
                    const id = button.dataset.id;
                    const first = button.dataset.first;
                    const last = button.dataset.last;
                    const email = button.dataset.email;
                    const phone = button.dataset.phone;

                    // Fill the form fields
                    form.querySelector('[name="user_id"]').value = id;
                    form.querySelector('[name="first_name"]').value = first;
                    form.querySelector('[name="last_name"]').value = last;
                    form.querySelector('[name="email"]').value = email;
                    form.querySelector('[name="phone"]').value = phone;

                    // Update submit button text
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = `Update Customer`;
                });
            });

            // === Handle form submission (update customer) ===
            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                    const formData = new FormData(form);

                    try {
                        const response = await fetch('./auth/update_customer_auth.php', {
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
                    submitBtn.innerHTML = `Update Customer`;
                });
            }
        });
    </script>

    <!-- Delete customer -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE USER =========
            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this user';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b>.<br>This action cannot be undone.`;
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
                    const response = await fetch('./auth/delete_user_auth.php', {
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

</body>

</html>