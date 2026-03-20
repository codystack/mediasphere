<div class="offcanvas offcanvas-end w-full w-lg-1/2" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasEditRequest" aria-labelledby="offcanvasEditRequestLabel">
    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
        <h5 class="offcanvas-title" id="offcanvasEditRequestLabel">Edit Request</h5>
        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body vstack gap-5">
        <form id="requestForm" method="POST" enctype="multipart/form-data">
            <div class="row g-5">
                <input type="hidden" name="request_id">

                <div class="col-md-12" style="display: none;">
                    <label class="form-label">Staff ID</label>
                    <input type="text" class="form-control" value="<?= $_SESSION['admin_id']; ?>" name="admin_id" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" value="<?= $fullName; ?>" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="request_title" required placeholder="Errand">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="request_type" required>
                        <option value="" disabled selected>Select a category</option>
                        <option value="IT & Technology">IT & Technology</option>
                        <option value="Facilities & Maintenance">Facilities & Maintenance</option>
                        <option value="Office Supplies">Office Supplies</option>
                        <option value="HR & Administrative">HR & Administrative</option>
                        <option value="Security & Access">Security & Access</option>
                        <option value="Meetings & Communication">Meetings & Communication</option>
                        <option value="Miscellaneous">Miscellaneous</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Priority</label>
                    <select class="form-select" name="priority" required>
                        <option value="" disabled selected>Select a priority</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="request_date" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="edit-description" rows="6" placeholder="Description for expense..."></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Attachment</label>
                    <input type="file" class="form-control" name="attachment_path" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <!-- Only visible to Super Admin -->
                <div class="col-md-12" style="display: <?= $_SESSION['designation'] == 'super-admin' ? 'unset' : 'none' ?>;">
                    <label class="form-label">Superior Remark</label>
                    <textarea class="form-control" name="superior_remark" id="edit-superior_remark" rows="6" placeholder="Write your review or feedback..."></textarea>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="pe-2"><i class="bi bi-send"></i></span>
                        <?= $_SESSION['designation'] == 'super-admin' ? 'Review Request' : 'Request' ?>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
