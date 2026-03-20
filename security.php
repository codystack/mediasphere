<?php
include "./components/header.php";
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
                                <a href="profile" class="nav-link">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a href="security" class="nav-link active">Security</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid max-w-screen-md vstack gap-6">
                    <div class="card">
                        <div class="card-body row g-5">
                            <div class="col-md-7">
                                <h5 class="h4 mb-5">Change password</h5>
                                <form id="changePasswordForm" method="POST">
                                    <div class="mb-5">
                                        <label class="form-label">Current Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control" name="current_password" id="currentPasswordField" placeholder="Current password" required minlength="8">
                                            <span id="toggleCurrentPassword" 
                                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-5">
                                        <label class="form-label">New Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control" name="password" id="passwordField" placeholder="Enter new password" required minlength="8">
                                            <span id="togglePassword" 
                                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-5">
                                        <label class="form-label">Confirm Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control" name="confirm_password" id="confirmPasswordField" placeholder="Confirm new password" required minlength="8">
                                            <span id="toggleConfirmPassword" 
                                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-end"><button type="submit" class="btn btn-sm btn-primary">Change password</button></div>
                                </form>
                            </div>
                            <div class="col-md-5">
                                <div class="card bg-dark border-0 shadow-none ml-md-4">
                                    <div class="card-body">
                                        <h5 class="text-white mb-2">Password requirements</h5>
                                        <p class="text-sm text-white mb-3">In order to create a strong password, here are some rules to have in mind:</p>
                                        <ul class="text-warning font-code pl-4 mb-0">
                                            <li class="text-xs">Minimum 8 character</li>
                                            <li class="text-xs">At least one lowercase character</li>
                                            <li class="text-xs">At least one uppercase character</li>
                                            <li class="text-xs">Can’t be the same as the previous password</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const notyf = new Notyf();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

            try {
                const res = await fetch('./auth/change_password_auth.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // ensures session cookie is sent
                });
                const data = await res.json();

                if (data.success) {
                    notyf.success(data.message);
                    form.reset();
                } else {
                    notyf.error(data.message);
                }
            } catch (err) {
                console.error(err);
                notyf.error('Network or server error.');
            }

            btn.disabled = false;
            btn.innerHTML = `Change password`;
        });
    </script>


    <script>
        // Password visibility toggles
        document.querySelector('#toggleCurrentPassword').addEventListener('click', function () {
            const field = document.querySelector('#currentPasswordField');
            const type = field.type === 'password' ? 'text' : 'password';
            field.type = type;
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        document.querySelector('#togglePassword').addEventListener('click', function () {
            const field = document.querySelector('#passwordField');
            const type = field.type === 'password' ? 'text' : 'password';
            field.type = type;
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        document.querySelector('#toggleConfirmPassword').addEventListener('click', function () {
            const field = document.querySelector('#confirmPasswordField');
            const type = field.type === 'password' ? 'text' : 'password';
            field.type = type;
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
</body>

</html>