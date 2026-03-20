                <div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNewLeave" aria-labelledby="offcanvasAddNewLeaveLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasAddNewLeaveLabel">New leave</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form>
                            <div class="row g-5">
                                <div class="col-md-12" style="display: none;">
                                    <div>
                                        <label class="form-label">Staff ID</label>
                                        <input type="text" class="form-control" value="<?= $_SESSION['admin_id']; ?>" name="admin_id" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" value="<?= $fullName; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Leave Type</label>
                                    <select class="form-select" name="leave_type" aria-label="Default select example" required>
                                        <option selected disabled value="">Select Leave Type</option>
                                        <option value="Casual">Casual</option>
                                        <option value="Sick">Sick</option>
                                        <option value="Earned">Earned</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Duration</label>
                                    <select class="form-select" name="duration" aria-label="Default select example" required>
                                        <option selected disabled value="">Select Duration</option>
                                        <option value="Full Day">Full Day</option>
                                        <option value="One Week">One Week</option>
                                        <option value="Two Weeks">Two Weeks</option>
                                        <option value="One Month">One Month</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Reason for absence</label>
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Write your reason..." required></textarea>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>Apply for Leave
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>