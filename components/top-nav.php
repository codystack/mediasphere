        <nav class="navbar navbar-light position-lg-sticky top-lg-0 d-none d-lg-block overlap-10 flex-none bg-white border-bottom px-0 py-3" id="topbar">
            <div class="container-fluid">
                <div></div>
                <div class="navbar-user d-none d-sm-block">
                    <div class="hstack gap-3 ms-4">
                        <div class="dropdown">
                            <a class="d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                                <div>
                                    <div class="avatar avatar-sm bg-warning rounded-circle text-white">
                                        <img alt="avatar" src="<?= $avatar ?>">
                                    </div>
                                </div>
                                <div class="d-none d-sm-block ms-3">
                                    <span class="h6"><?= $firstName ?></span>
                                </div>
                                <div class="d-none d-md-block ms-md-2">
                                    <i class="bi bi-chevron-down text-muted text-xs"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="dropdown-header">
                                    <span class="d-block text-sm text-muted mb-1">Signed in as</span>
                                    <span class="d-block text-heading font-semibold"><?= $fullName ?></span></div>
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