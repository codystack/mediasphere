                <div class="offcanvas offcanvas-end w-full w-lg-2/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNewInvoice" aria-labelledby="offcanvasAddNewInvoiceLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasAddNewInvoiceLabel">New Report</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form>
                            <div class="row g-5">
                                <div class="col-md-12" style="display: none;">
                                    <label class="form-label">Staff ID</label>
                                    <input type="text" class="form-control" value="<?= $_SESSION['admin_id']; ?>" name="admin_id" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" value="<?= $fullName; ?>" disabled>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Task</label>
                                    <textarea class="form-control" name="tasks_completed" id="tasks_completed" rows="6" placeholder="List your weekly tasks..."></textarea>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Notes / Challenges</label>
                                    <textarea class="form-control" name="issues_or_notes" id="issues_or_notes" rows="6" placeholder="Mention any challenges or additional notes..."></textarea>
                                </div>

                                <!-- Only visible to Super Admin -->
                                <div class="col-md-12" style="display: <?= $_SESSION['designation'] == 'super-admin' ? 'unset' : 'none' ?>;">
                                    <label class="form-label">Superior Remark</label>
                                    <textarea class="form-control" name="superior_remark" id="superior_remark" rows="6" placeholder="Write your review or feedback..."></textarea>
                                </div>

                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <span class="pe-2"><i class="bi bi-send"></i></span>
                                        <?= $_SESSION['designation'] == 'super-admin' ? 'Review Report' : 'Send Report' ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>