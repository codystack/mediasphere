                <!-- Change admin status -->
                <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-3">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-semibold" id="deleteAdminModalLabel">Confirm Action</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="align-items-center mb-10">
                                    <div class="text-center">
                                        <div class="icon icon-shape icon-xl rounded-circle bg-soft-danger text-danger text-2xl mb-4">
                                            <i class="bi bi-shield-exclamation"></i>
                                        </div>
                                        <p id="confirmActionMessage" class="fs-6 mb-4 text-center"></p>
                                        <button type="button" class="btn btn-danger" id="confirmActionButton"></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="modalExport" tabindex="-1" aria-labelledby="modalExport" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-3">
                            <div class="modal-header">
                                <div class="icon icon-shape rounded-3 bg-soft-primary text-primary text-lg me-4"><i class="bi bi-globe"></i></div>
                                <div>
                                    <h5 class="mb-1">Share to web</h5><small class="d-block text-xs text-muted">Publish and share link with anyone</small></div>
                                <div class="ms-auto">
                                    <div class="form-check form-switch me-n2">
                                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked="checked"> 
                                        <label class="form-check-label" for="flexSwitchCheckChecked"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-center mb-5">
                                    <div>
                                        <p class="text-sm">Anyone with this link <span class="font-bold text-heading">can view</span></p>
                                    </div>
                                    <div class="ms-auto">
                                        <a href="#" class="text-sm font-semibold">Settings</a>
                                    </div>
                                </div>
                                <div>
                                    <div class="input-group input-group-inline"><input type="email" class="form-control" placeholder="username" value="https://webpixels.io/your-amazing-link"> <span class="input-group-text"><i class="bi bi-clipboard"></i></span></div><span class="mt-2 valid-feedback">Looks good!</span></div>
                            </div>
                            <div class="modal-footer">
                                <div class="me-auto"><a href="#" class="text-sm font-semibold"><i class="bi bi-clipboard me-2"></i>Copy link</a></div><button type="button" class="btn btn-sm btn-neutral" data-bs-dismiss="modal">Close</button> <button type="button" class="btn btn-sm btn-success">Share file</button></div>
                        </div>
                    </div>
                </div>