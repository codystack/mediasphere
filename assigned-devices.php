<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all assigned devices with device + admin info
$stmt = $pdo->query("
    SELECT 
        da.id AS assignment_id,
        d.device_name,
        d.serial_number,
        a.first_name,
        a.last_name,
        da.status,
        da.assigned_condition,
        da.returned_condition,
        da.assigned_date,
        da.returned_date
    FROM device_assignments da
    JOIN devices d ON da.device_id = d.id
    JOIN admin a ON da.admin_id = a.id
    ORDER BY da.id DESC
");
$assigned_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for badge color and text
function getDeviceStatusBadge($status) {
    switch (strtolower($status)) {
        case 'assigned': return ['bg-soft-info text-info', 'Assigned'];
        case 'returned': return ['bg-soft-success text-success', 'Returned'];
        default: return ['bg-secondary', 'Unknown'];
    }
}

?>
    <div class="d-flex flex-column flex-lg-row h-lg-full bg-surface-secondary">
        <?php include "./components/side-nav.php"; ?>

        <div class="flex-lg-1 h-screen overflow-y-lg-auto">
            <?php include "./components/top-nav.php"; ?>

            <header>
                <div class="container-fluid">
                    <div class="border-bottom pt-6">
                        <div class="row align-items-center">
                            <div class="col-sm col-12">
                                <h1 class="h2 ls-tight">Assigned Devices</h1>
                            </div>
                        </div>
                        <ul class="nav nav-tabs overflow-x border-0">
                            <li class="nav-item"><a href="devices" class="nav-link">All Devices</a></li>
                            <li class="nav-item"><a href="assigned-devices" class="nav-link active">Assigned Devices</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid">
                    <div class="vstack gap-4">
                        <div class="card">
                            <div class="table-responsive px-10 py-10">
                                <?php if (count($assigned_devices) > 0): ?>
                                <table class="table table-hover table-nowrap" id="assignedDevices">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Device Name</th>
                                            <!-- <th>Serial Number</th> -->
                                            <th>Assigned To</th>
                                            <th>Status</th>
                                            <th>Assigned Date</th>
                                            <th>Return Date</th>
                                            <th>Assigned Condition</th>
                                            <th>Return Condition</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assigned_devices as $assigned_device): 
                                            [$badge, $label] = getDeviceStatusBadge($assigned_device['status'] ?? '');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                        <i class="bi bi-pc-display-horizontal"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="d-inline-block h6 font-semibold mb-1"><?= htmlspecialchars($assigned_device['device_name']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- <td><?= htmlspecialchars($assigned_device['serial_number']) ?></td> -->
                                            <td><?= htmlspecialchars($assigned_device['first_name'] . ' ' . $assigned_device['last_name']) ?></td>
                                            <td><span class="badge <?= $badge ?> text-uppercase rounded-pill"><?= $label ?></span></td>
                                            <td><?= $assigned_device['assigned_date'] ? date('M d, Y', strtotime($assigned_device['assigned_date'])) : '-' ?></td>
                                            <td><?= $assigned_device['returned_date'] ? date('M d, Y', strtotime($assigned_device['returned_date'])) : '-' ?></td>
                                            <td><?= htmlspecialchars($assigned_device['assigned_condition'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($assigned_device['returned_condition'] ?? '-') ?></td>
                                            <td class="text-end">
                                                <?php if (strtolower($assigned_device['status']) === 'assigned'): ?>
                                                <a href="#" 
                                                    class="btn btn-sm btn-square btn-danger return-device" 
                                                    data-id="<?= $assigned_device['assignment_id'] ?>"
                                                    data-name="<?= htmlspecialchars($assigned_device['device_name']) ?>"
                                                    data-bs-toggle="offcanvas" 
                                                    data-bs-target="#offcanvasReturnDevice">
                                                    <i class="bi bi-box-arrow-in-left"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div style="position: relative; height: 250px;">
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" class="text-center">
                                        <img src="./assets/img/no-data-icon.svg" width="100" alt="No Assigned Devices">
                                        <p class="mt-3 lead">No devices have been assigned yet</p>
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
    include "./modal/return-device-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#assignedDevices').DataTable();
        });
    </script>

    <!-- Add New Device -->
    <script>
        document.getElementById('addDeviceForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const notyf = new Notyf();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Adding...`;

            try {
                const res = await fetch('./auth/create_device_auth.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    notyf.success(data.message);
                    form.reset();
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    notyf.error(data.message);
                }
            } catch (err) {
                console.error(err);
                notyf.error('Network or server error.');
            }

            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-plus-square-dotted me-2"></i>Add New Device`;
        });
    </script>

    <!-- Delete Device -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentDeviceId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // DELETE DEVICE
            document.querySelectorAll('.delete-device').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentDeviceId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this device';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b>.<br>This action cannot be undone.`;
                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                });
            });

            // Confirm delete
            confirmButton.addEventListener('click', async () => {
                if (!currentDeviceId || currentAction !== 'delete') return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    const response = await fetch('./auth/delete_device_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentDeviceId })
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notyf.error(data.message || 'Failed to delete device.');
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

    <!-- Edit Device -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditDevice');
            const form = offcanvasEl.querySelector('form');
            const editButtons = document.querySelectorAll('.edit-device');

            // Prefill edit form
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    form.querySelector('[name="device_id"]').value = button.dataset.id;
                    form.querySelector('[name="device_name"]').value = button.dataset.name;
                    form.querySelector('[name="serial_number"]').value = button.dataset.serial;
                    form.querySelector('[name="status"]').value = button.dataset.status;
                });
            });

            // Handle update
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                const formData = new FormData(form);

                try {
                    const response = await fetch('./auth/update_device_auth.php', {
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
                submitBtn.innerHTML = `Update Device`;
            });
        });
    </script>

    <!-- Assign & Return Device -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();

            // ============================
            // ASSIGN DEVICE
            // ============================

            // Prefill Assign Device Offcanvas
            document.querySelectorAll('.assign-device').forEach(button => {
                button.addEventListener('click', () => {
                    const deviceId = button.dataset.id;
                    const deviceName = button.dataset.name || 'Device';

                    // Set hidden input
                    const input = document.getElementById('assign_device_id');
                    if (input) input.value = deviceId;

                    // Optional: show which device is being assigned
                    const label = document.getElementById('offcanvasAssignDeviceLabel');
                    if (label) label.textContent = `Assign ${deviceName}`;
                });
            });

            // Handle Assign Form Submit
            const assignForm = document.getElementById('assignDeviceForm');
            if (assignForm) {
                assignForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const btn = assignForm.querySelector('button[type="submit"]');
                    const formData = new FormData(assignForm);

                    btn.disabled = true;
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Assigning...`;

                    try {
                        const res = await fetch('./auth/assign_device_auth.php', { method: 'POST', body: formData });
                        const data = await res.json();

                        if (data.success) {
                            notyf.success(data.message);
                            assignForm.reset();
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            notyf.error(data.message);
                        }
                    } catch (err) {
                        console.error(err);
                        notyf.error('Network or server error.');
                    }

                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-person-fill-add me-2"></i>Assign Device`;
                });
            }

            // ============================
            // RETURN DEVICE
            // ============================

            // Prefill Return Device Offcanvas
            document.querySelectorAll('.return-device').forEach(button => {
                button.addEventListener('click', () => {
                    const deviceId = button.dataset.id;
                    const deviceName = button.dataset.name || 'Device';

                    // Set hidden input
                    const input = document.getElementById('return_device_id');
                    if (input) input.value = deviceId;

                    // Optional: update title
                    const label = document.getElementById('offcanvasReturnDeviceLabel');
                    if (label) label.textContent = `Return ${deviceName}`;
                });
            });

            // Handle Return Form Submit
            const returnForm = document.getElementById('returnDeviceForm');
            if (returnForm) {
                returnForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const btn = returnForm.querySelector('button[type="submit"]');
                    const formData = new FormData(returnForm);

                    btn.disabled = true;
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                    try {
                        const res = await fetch('./auth/return_device_auth.php', { method: 'POST', body: formData });
                        const data = await res.json();

                        if (data.success) {
                            notyf.success(data.message);
                            returnForm.reset();
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            notyf.error(data.message);
                        }
                    } catch (err) {
                        console.error(err);
                        notyf.error('Network or server error.');
                    }

                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-box-arrow-in-left me-2"></i>Mark as Returned`;
                });
            }
        });
     </script>


</body>

</html>