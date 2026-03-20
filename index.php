<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="color-scheme" content="dark light">
    <link rel="shortcut icon" href="./assets/img/ms-favicon.svg">

    <title>Media Spahere Limited&trade;</title>

    <link rel="stylesheet" type="text/css" href="./assets/css/main.css">
    <link rel="stylesheet" type="text/css" href="./assets/css/utilities.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body>
    <div>
        <div class="px-5 py-5 p-lg-0 min-h-screen bg-surface-secondary d-flex flex-column justify-content-center">
            <div class="d-flex justify-content-center">
                <div class="col-lg-5 col-xl-4 p-12 p-xl-20 position-fixed start-0 top-0 h-screen overflow-y-hidden bg-primary d-none d-lg-flex flex-column">
                    <a class="d-block" href="./">
                        <img src="./assets/img/ms-light.svg" class="h-12" alt="Logo">
                    </a>
                    <div class="mt-32 mb-20">
                        <h1 class="ls-tight font-bolder display-6 text-white mb-5">Let’s make today count.</h1>
                        <p class="text-white text-opacity-80">Every login is a new opportunity to lead, grow, and make a difference.</p>
                    </div>
                    <div class="w-56 h-56 rounded-circle position-absolute bottom-0 end-20 transform translate-y-1/3" style="background-color: #DB0000"></div>
                </div>
                <div class="col-12 col-md-9 col-lg-7 offset-lg-4 border-left-lg min-h-screen d-flex flex-column justify-content-center position-relative">
                    <div class="py-lg-16 px-lg-20">
                        <div class="row">
                            <div class="col-lg-10 col-md-9 col-xl-8 mx-auto">
                                <div class="mt-10 mt-lg-5 mb-6 d-lg-block">
                                    <h1 class="ls-tight font-bolder h2">Welcome Back!</h1>
                                    <p class="mt-2">Login using the correct credentials.</p>
                                </div>
                                <form id="adminLoginForm">
                                    <div class="mb-7">
                                        <label class="form-label" for="email">Email address</label> 
                                        <input type="email" class="form-control" name="email" placeholder="Enter email" required>
                                    </div>
                                    <div class="mb-7 position-relative">
                                        <div class="d-flex justify-content-between gap-2 mb-2 align-items-center">
                                            <label class="form-label">Password</label>
                                            <a href="forgot-password" class="text-sm text-muted text-danger-hover text-underline">Forgot password?</a>
                                        </div>
                                        <div class="position-relative">
                                            <input type="password" class="form-control pe-5" name="password" id="passwordField" placeholder="Enter password" required>
                                            <span id="togglePassword" 
                                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999;">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" id="loginSubmit" class="btn btn-primary w-full">Sign in</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="./assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector("#adminLoginForm");
            const submitButton = document.querySelector("#loginSubmit");
            const notyf = new Notyf({
                duration: 3000,
                position: { x: 'right', y: 'top' }
            });

            form.addEventListener("submit", async function (e) {
                e.preventDefault();

                submitButton.disabled = true;
                submitButton.textContent = "Signing in...";

                const formData = new FormData(form);

                try {
                    const res = await fetch("./auth/login_auth.php", {
                        method: "POST",
                        body: formData
                    });

                    const data = await res.json();

                    if (data.success) {
                        notyf.success(data.message);
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    } else {
                        notyf.error(data.message);
                    }
                } catch (err) {
                    notyf.error("Network or server error. Please try again.");
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = "Sign in";
                }
            });
        });
    </script>


    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#passwordField');
        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
</body>

</html>