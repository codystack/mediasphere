<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="color-scheme" content="dark light">
    <link rel="shortcut icon" href="./assets/img/ms-favicon.svg">

    <title>Media Spahere Limited&trade; :: Forgot Password</title>

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
        <div class="px-5 h-screen bg-surface-secondary d-flex flex-column justify-content-center">
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
                <div class="col-12 col-md-9 col-lg-7 offset-lg-4 h-screen min-h-lg-screen d-flex flex-column justify-content-center py-lg-16 px-lg-20 position-relative">
                    <div class="row">
                        <div class="col-lg-10 col-md-9 col-xl-8 mx-auto">
                            <div class="mb-8">
                                <h1 class="h2 ls-tight font-bolder mt-6">Forgot Password</h1>
                                <p class="mt-2">Enter your email and we will send you a reset link</p>
                            </div>
                            <form id="forgotPasswordForm">
                                <div class="mb-5">
                                    <label class="form-label" for="email">Email address</label> 
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary w-full" id="sendLinkBtn">Send me the link</button>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p class="mt-2">Go back to <a href="./" class="text-danger">Login</a></p>
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
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('forgotPasswordForm');
            const sendLinkBtn = document.getElementById('sendLinkBtn');
            const notyf = new Notyf();

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const email = document.getElementById('email').value.trim();
                if (!email) return notyf.error('Please enter your email.');

                sendLinkBtn.disabled = true;
                sendLinkBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Sending...`;

                try {
                    const response = await fetch('./auth/forgot_password_auth.php', {
                        method: 'POST',
                        body: new URLSearchParams({ email })
                    });
                    const data = await response.json();

                    if (data.success) {
                        notyf.success(data.message);
                    } else {
                        notyf.error(data.message);
                    }
                } catch (error) {
                    console.error(error);
                    notyf.error('Network error. Please try again.');
                }

                sendLinkBtn.disabled = false;
                sendLinkBtn.textContent = 'Send me the link';
            });
        });
    </script>

</body>

</html>