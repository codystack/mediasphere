                <div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNotice" aria-labelledby="offcanvasAddNoticeLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasAddNoticeLabel">Create New Notice</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body vstack gap-5">
                        <form id="createNoticeForm">
                            <div class="row g-5">
                                <div class="col-md-12" style="display: none;">
                                    <label class="form-label">Admin ID</label>
                                    <input type="text" class="form-control" name="admin_id" value="<?php echo $_SESSION['admin_id']; ?>" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?= $fullName ?>" disabled>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Notice Title</label>
                                    <input type="text" class="form-control" name="title" placeholder="Enter notice title" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" name="message" id="message" rows="4" placeholder="Write your message..." required></textarea>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option selected disabled value="">Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-5">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="bi bi-megaphone"></i> Publish Notice
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>