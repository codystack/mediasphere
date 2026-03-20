<?php
include "./components/header.php";

require_once('./config/db.php');

$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

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
                    <div class="border-bottom pt-6">
                        <div class="row align-items-center">
                            <div class="col-sm-6 col-12">
                                <h1 class="h2 ls-tight">General Information</h1>
                            </div>
                            <div class="col-sm-6 col-12"></div>
                        </div>
                        <ul class="nav nav-tabs overflow-x border-0">
                            <li class="nav-item">
                                <a href="profile" class="nav-link active">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a href="security" class="nav-link">Security</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid max-w-screen-md vstack gap-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xl bg-warning rounded-circle text-white" style="width: 8rem; height: 8rem; border-radius: 50%; overflow: hidden;">
                                            <img alt="" src="<?= $admin['picture']; ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: top;">
                                        </div>
                                        <div class="ms-4">
                                            <span class="h4 d-block mb-0"><?= $fullName ?></span> 
                                            <a href="#" class="text-sm font-semibold text-muted"><?= ucfirst($admin['designation']) ?></a>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    [$badge, $action] = getStatusBadge($admin['status'] ?? '');
                                ?>
                                <div class="ms-auto">
                                    <span class="badge <?= $badge ?> text-uppercase rounded-pill"><?= $action ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-5">
                                <h4>Contact Information</h4>
                            </div>
                            <form id="updateProfileForm" method="POST" enctype="multipart/form-data">
                                <div class="row g-4">
                                    <div class="col-md-6" style="display: none;">
                                        <label class="form-label">Admin ID</label>
                                        <input type="text" class="form-control" name="admin_id" value="<?= $_SESSION['admin_id']; ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($admin['first_name']) ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($admin['last_name']) ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($admin['phone']) ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="Male" <?= $admin['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= $admin['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                            <option value="Other" <?= $admin['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Profile Picture</label>
                                        <input type="file" class="form-control" name="picture">
                                    </div>

                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script>
        document.getElementById('updateProfileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const notyf = new Notyf();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

            try {
                const res = await fetch('./auth/update_profile_auth.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });
                const data = await res.json();
                if (data.success) {
                    notyf.success(data.message);
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    notyf.error(data.message);
                }
            } catch (err) {
                console.error(err);
                notyf.error('Network or server error.');
            }

            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-save me-2"></i> Save Changes`;
        });
    </script>

</body>

</html>