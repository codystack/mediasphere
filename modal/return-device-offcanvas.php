                <div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasReturnDevice" aria-labelledby="offcanvasReturnDeviceLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasReturnDeviceLabel">Return Assigned Device</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form id="returnDeviceForm">
                            <div class="row g-5">
                                

                                <div class="col-md-12" style="display: none;">
                                    <div>
                                        <label class="form-label">Device ID</label>
                                        <input type="text" class="form-control" name="assignment_id" id="return_device_id">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Condition at Return</label>
                                        <input type="text" class="form-control" name="returned_condition" placeholder="e.g., Good, Needs Repair" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Remarks (Optional)</label>
                                        <textarea class="form-control" name="remarks" rows="3" placeholder="Additional notes about the return..."></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-5">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <span class="pe-2"><i class="bi bi-box-arrow-in-left"></i> </span> Mark as Returned
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>