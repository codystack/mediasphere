                <div class="offcanvas offcanvas-end w-full w-lg-1/3" tabindex="-1" id="offcanvasEditNotice" aria-labelledby="offcanvasEditNoticeLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasEditNoticeLabel">Edit Notice</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-4">
                        <form id="editNoticeForm">
                            <div class="col-md-12" style="display:none;">
                                <label class="form-label">Notice ID</label>
                                <input type="text" class="form-control" name="id" id="editNoticeId">
                            </div>

                            <div class="col-md-12" style="display:none;">
                                <label class="form-label">Admin ID</label>
                                <input type="text" class="form-control" name="admin_id" value="<?php echo $_SESSION['admin_id']; ?>" required>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label">Admin Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($fullName ?? '') ?>" disabled>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label">Notice Title</label>
                                <input type="text" class="form-control" name="title" id="editTitle" placeholder="Enter notice title" required>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" id="editMessage" rows="4" placeholder="Write the message..." required></textarea>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="editStatus" required>
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>

                            <div class="col-md-12 mt-4">
                                <button type="submit" class="btn btn-primary w-full">
                                    Update Notice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>