<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all payment proofs with user info
$stmt = $pdo->query("
    SELECT pp.*, u.first_name, u.last_name, u.email 
    FROM payment_proofs pp
    LEFT JOIN users u ON pp.user_id = u.id
    ORDER BY pp.id ASC
");
$payment_proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge(string $status = ''): array {
    $s = strtolower(trim($status));
    switch ($s) {
        case 'verified':
            return ['bg-soft-success text-success', 'Verified'];
        case 'rejected':
            return ['bg-soft-danger text-danger', 'Rejected'];
        case 'pending':
            return ['bg-soft-warning text-warning', 'Pending'];
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
                                <h1 class="h2 ls-tight">Payment Proofs</h1>
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
                                <?php if (count($payment_proofs) > 0): ?>
                                <table class="table table-hover table-nowrap" id="proof">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Applicant</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Uploaded At</th>
                                            <th scope="col">Proof</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payment_proofs as $index => $proof): 
                                            [$badge, $label] = getStatusBadge($proof['status'] ?? '');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                        <i class="bi bi-cash-coin"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="d-inline-block h6 font-semibold mb-1"><?= htmlspecialchars($proof['first_name'] . ' ' . $proof['last_name']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($proof['email']) ?></td>
                                            <td>₦<?= number_format($proof['amount'], 2) ?></td>
                                            <td>
                                                <span class="badge <?= $badge ?> text-uppercase rounded-pill"><?= $label ?></span>
                                            </td>
                                            <td><?= date('M d, Y h:i A', strtotime($proof['uploaded_at'])) ?></td>
                                            <td>
                                                <a href="https://app.blinkscore.ng/<?= htmlspecialchars($proof['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary btn-square">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-neutral bg-success-hover text-white-hover approve-proof btn-square" data-id="<?= $proof['id'] ?>"><i class="bi bi-check-circle"></i></button>
                                                <button class="btn btn-sm btn-neutral bg-danger-hover text-white-hover reject-proof btn-square" data-id="<?= $proof['id'] ?>"><i class="bi bi-x-circle"></i></button>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-square btn-danger delete-proof" 
                                                    data-id="<?= $proof['id'] ?>" 
                                                    data-name="<?= htmlspecialchars($proof['first_name'] . ' ' . $proof['last_name']) ?>" 
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
                                            <p class="mt-3 lead">No payment proofs yet</p>
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
    include "./modal/modal.php";
    include "./auth/upload_proof_auth.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(() => {
            $('#proof').DataTable();

            const notyf = new Notyf();

            // Approve
            $('.approve-proof').click(function() {
                const id = $(this).data('id');
                fetch('./auth/payment_proof_status_auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ id, status: 'verified' })
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

            // Reject
            $('.reject-proof').click(function() {
                const id = $(this).data('id');
                fetch('./auth/payment_proof_status_auth.php', {
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

    <!-- Delete user account-->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentUserId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE USER =========
            document.querySelectorAll('.delete-proof').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentUserId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this user';
                    confirmMessage.innerHTML = `You are about to permanently delete<br> the payment proof from <b>${name}</b>.<br>This action cannot be undone.`;
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
                    const response = await fetch('./auth/delete_payment_proof_auth.php', {
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