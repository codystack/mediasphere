<?php
include "./components/header.php";

require_once('./config/db.php');

// Fetch all notices
$stmt = $pdo->query("SELECT * FROM notices ORDER BY id DESC");
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for status badge
function getNoticeStatusBadge($status) {
    switch (strtolower($status)) {
        case 'active': return ['bg-soft-success text-success', 'Active'];
        case 'expired': return ['bg-soft-danger text-danger', 'Expired'];
        case 'scheduled': return ['bg-soft-warning text-warning', 'Scheduled'];
        default: return ['bg-secondary text-dark', 'Unknown'];
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
                                <h1 class="h2 ls-tight">Notice Board</h1>
                            </div>
                            <div class="col-sm-auto col-12 mt-4 mt-sm-0">
                                <div class="hstack gap-2 justify-content-sm-end">
                                    <a href="#offcanvasAddNotice" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>
                                        <span>Create a Notice</span>
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
                                <?php if (count($notices) > 0): ?>
                                <table class="table table-hover table-nowrap" id="notices">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Date Created</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notices as $notice): 
                                            [$badge, $label] = getNoticeStatusBadge($notice['status'] ?? '');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape rounded-circle text-sm icon-sm bg-tertiary bg-opacity-20 text-tertiary">
                                                        <i class="bi bi-megaphone"></i>
                                                    </div>
                                                    <div class="ms-3">
                                                        <span class="d-inline-block h6 font-semibold mb-1">
                                                            <?= htmlspecialchars($notice['title']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge <?= $badge ?> text-uppercase rounded-pill"><?= $label ?></span></td>
                                            <td><?= date('M d, Y', strtotime($notice['created_at'])) ?></td>
                                            <td class="text-end">
                                                <a href="#" 
                                                    class="btn btn-sm btn-square btn-primary edit-notice"
                                                    data-id="<?= $notice['id'] ?>"
                                                    data-title="<?= htmlspecialchars($notice['title']) ?>"
                                                    data-message="<?= htmlspecialchars($notice['message']) ?>"
                                                    data-status="<?= htmlspecialchars($notice['status']) ?>"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#offcanvasEditNotice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-square btn-danger delete-notice" 
                                                    data-id="<?= $notice['id'] ?>" 
                                                    data-title="<?= htmlspecialchars($notice['title']) ?>" 
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
                                        <img src="./assets/img/no-data-icon.svg" width="100" alt="No Notices">
                                        <p class="mt-3 lead">No notices have been created yet</p>
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
    include "./modal/create-notice-offcanvas.php";
    include "./modal/edit-notice-offcanvas.php";
    ?>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            $('#notices').DataTable();
        });
    </script>

    <!-- CK Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('offcanvasAddNotice');
            let editors = {};

            // Initialize CKEditor when offcanvas opens
            offcanvasEl.addEventListener('shown.bs.offcanvas', async () => {
                const fields = ['message'];

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
            const offcanvasEl = document.getElementById('offcanvasEditNotice');
            let editors = {};

            // Initialize CKEditor when offcanvas opens
            offcanvasEl.addEventListener('shown.bs.offcanvas', async () => {
                const fields = ['editMessage'];

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
            const form = document.getElementById('createNoticeForm');
            if (!form) return; // Safety guard

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const notyf = new Notyf();
                const btn = form.querySelector('button[type="submit"]');
                const formData = new FormData(form);

                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Sending...`;

                try {
                    const res = await fetch('./auth/create_notice_auth.php', {
                        method: 'POST',
                        body: formData
                    });
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
                btn.innerHTML = `<i class="bi bi-send me-2"></i> Send Notice`;
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            const offcanvasEl = document.getElementById('offcanvasEditNotice');
            const form = offcanvasEl.querySelector('form');

            // Prefill edit form
            document.querySelectorAll('.edit-notice').forEach(button => {
                button.addEventListener('click', () => {
                    form.querySelector('[name="id"]').value = button.dataset.id;
                    form.querySelector('[name="title"]').value = button.dataset.title;
                    form.querySelector('[name="message"]').value = button.dataset.message;
                    form.querySelector('[name="status"]').value = button.dataset.status;
                });
            });

            // Submit update
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

                const formData = new FormData(form);

                try {
                    const res = await fetch('./auth/edit_notice_auth.php', { method: 'POST', body: formData });
                    const data = await res.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        notyf.error(data.message);
                    }
                } catch (err) {
                    console.error(err);
                    notyf.error('Network or server error.');
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i class="bi bi-send me-2"></i> Update Notice`;
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notyf = new Notyf();
            let currentNoticeId = null;
            let currentAction = null;

            const confirmMessage = document.getElementById('confirmActionMessage');
            const confirmButton = document.getElementById('confirmActionButton');

            // ======== DELETE NOTICE =========
            document.querySelectorAll('.delete-notice').forEach(button => {
                button.addEventListener('click', e => {
                    e.preventDefault();
                    currentNoticeId = button.dataset.id;
                    currentAction = 'delete';
                    const name = button.dataset.name || 'this notice';
                    confirmMessage.innerHTML = `You are about to permanently delete<br><b>${name}</b>.<br>This action cannot be undone.`;
                    confirmButton.textContent = 'Delete';
                    confirmButton.className = 'btn btn-danger';
                    confirmButton.dataset.action = 'delete';
                });
            });

            // ======== CONFIRM ACTION HANDLER =========
            confirmButton.addEventListener('click', async () => {
                if (!currentNoticeId || !currentAction) return;

                confirmButton.disabled = true;
                confirmButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;

                try {
                    const response = await fetch('./auth/delete_notice_auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: currentNoticeId })
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