        <?php
            $currentPage = basename($_SERVER['PHP_SELF'], ".php"); 
            // e.g. "dashboard" if you’re on dashboard.php

            require_once('./config/db.php');

            try {
                $stmt = $pdo->query("SELECT COUNT(*) AS pending_applications FROM pof_application WHERE status = 'pending'");
                $countPendingApplications = $stmt->fetch(PDO::FETCH_ASSOC)['pending_applications'];
            } catch (Exception $e) {
                error_log("Error fetching application count: " . $e->getMessage());
                $countPendingApplications = 0;
            }

            try {
                $stmt = $pdo->query("SELECT COUNT(*) AS pending_payment_proofs FROM payment_proofs WHERE status = 'pending'");
                $countPendingPaymentProofs = $stmt->fetch(PDO::FETCH_ASSOC)['pending_payment_proofs'];
            } catch (Exception $e) {
                error_log("Error fetching application count: " . $e->getMessage());
                $countPendingPaymentProofs = 0;
            }

            try {
                $stmt = $pdo->query("SELECT COUNT(*) AS approved_transactions FROM transactions WHERE status = 'Approved''");
                $countApprovedTransactions = $stmt->fetch(PDO::FETCH_ASSOC)['approved_transactions'];
            } catch (Exception $e) {
                error_log("Error fetching application count: " . $e->getMessage());
                $countApprovedTransactions = 0;
            }

            try {
                $stmt = $pdo->query("SELECT COUNT(*) AS pending_expenses FROM expenses WHERE status = 'Pending'");
                $countPendingExpenses = $stmt->fetch(PDO::FETCH_ASSOC)['pending_expenses'];
            } catch (Exception $e) {
                error_log("Error fetching expenses count: " . $e->getMessage());
                $countPendingExpenses = 0;
            }

            try {
                $stmt = $pdo->query("SELECT COUNT(*) AS pending_requests FROM requests WHERE status = 'Pending'");
                $countPendingRequests = $stmt->fetch(PDO::FETCH_ASSOC)['pending_requests'];
            } catch (Exception $e) {
                error_log("Error fetching requests count: " . $e->getMessage());
                $countPendingRequests = 0;
            }
        ?>
        <nav class="navbar show navbar-vertical h-lg-screen navbar-expand-lg px-0 py-3 navbar-light bg-white border-bottom border-bottom-lg-0 border-end-lg scrollbar" id="sidebar">
            <div class="container-fluid">
                <button class="navbar-toggler ms-n2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse" aria-controls="sidebarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand d-inline-block py-lg-2 mb-lg-5 px-lg-6 me-0" href="dashboard">
                    <img src="./assets/img/ms-dark.svg" width="200" alt="logo">
                </a>
                <div class="navbar-user d-lg-none">
                    <div class="dropdown">
                        <a href="#" id="sidebarAvatar" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar-parent-child">
                                <img alt="avatar" src="<?= $avatar ?>" class="avatar avatar- rounded-circle">
                                <span class="avatar-child avatar-badge bg-success"></span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarAvatar">
                            <a href="profile" class="dropdown-item">Profile</a>
                            <a href="security" class="dropdown-item">Security</a>
                            <hr class="dropdown-divider">
                            <a href="logout" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>

                <div class="collapse navbar-collapse" id="sidebarCollapse">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link py-2 <?= ($currentPage === 'dashboard') ? 'active' : '' ?>" href="dashboard">
                                <i class="bi bi-grid-1x2"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 d-flex align-items-center <?= ($currentPage === 'invoice') ? 'active' : '' ?>" href="invoice">
                                <i class="bi bi-file-earmark-pdf"></i> <span>Invoice</span> 
                                <?php if (!empty($countPendingApplications) && $countPendingApplications > 0): ?>
                                    <span class="badge badge-sm bg-soft-danger text-danger rounded-pill ms-auto">
                                        <?= htmlspecialchars($countPendingApplications) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 d-flex align-items-center <?= ($currentPage === 'payment-proofs') ? 'active' : '' ?>" href="payment-proofs">
                                <i class="bi bi-cash-coin"></i> <span>Payment Proofs</span> 
                                <?php if (!empty($countPendingPaymentProofs) && $countPendingPaymentProofs > 0): ?>
                                    <span class="badge badge-sm bg-soft-danger text-danger rounded-pill ms-auto">
                                        <?= htmlspecialchars($countPendingPaymentProofs) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 d-flex align-items-center <?= ($currentPage === 'transactions') ? 'active' : '' ?>" href="transactions">
                                <i class="bi bi-arrow-down-up"></i> <span>Transactions</span>
                                <?php if (!empty($countApprovedTransactions) && $countApprovedTransactions > 0): ?>
                                    <span class="badge badge-sm bg-soft-danger text-danger rounded-pill ms-auto">
                                        <?= htmlspecialchars($countApprovedTransactions) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 <?= ($currentPage === 'users') ? 'active' : '' ?>" href="users">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                    </ul>

                    <hr class="navbar-divider my-4 opacity-70">

                    <ul class="navbar-nav">
                        <!-- <li>
                            <span class="nav-link text-xs font-semibold text-uppercase text-muted ls-wide">Internal Affairs</span>
                        </li> -->

                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 <?= ($currentPage === 'admins') ? 'active' : '' ?>" href="admins">
                                <i class="bi bi-person-workspace"></i> Staff
                            </a>
                        </li>


                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 <?= ($currentPage === 'profile') ? 'active' : '' ?>" href="profile">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>

                        <li class="nav-item mt-4">
                            <a class="nav-link py-2 <?= ($currentPage === 'security') ? 'active' : '' ?>" href="security">
                                <i class="bi bi-shield-check"></i> Security
                            </a>
                        </li>
                    </ul>

                    <div class="mt-auto"></div>
                    <div class="my-4 px-lg-6 position-relative">
                        <div class="dropup w-full">
                            <button class="btn-primary d-flex w-full py-3 ps-3 pe-4 align-items-center shadow shadow-3-hover rounded-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-3">
                                    <img alt="..." src="<?= $avatar ?>" class="avatar avatar-sm rounded-circle">
                                </span>
                                <span class="flex-fill text-start text-sm font-semibold"><?= $fullName ?></span>
                                <span><i class="bi bi-chevron-expand text-white text-opacity-70"></i></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end w-full">
                                <div class="dropdown-header">
                                    <span class="d-block text-sm text-muted mb-1">Signed in as</span>
                                    <span class="d-block text-heading font-semibold"><?= $fullName ?></span>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="profile"><i class="bi bi-person me-3"></i>Profile</a>
                                <a class="dropdown-item" href="security"><i class="bi bi-shield-check me-3"></i>Security</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout"><i class="bi bi-power me-3"></i>Log Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>