                <div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasEditStaff" aria-labelledby="offcanvasCreateLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasCreateLabel">Edit admin account</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form>
                            <div class="row g-5">
                                <div class="col-md-12" style="display: none;">
                                    <div>
                                        <label class="form-label">Admin ID</label>
                                        <input type="text" class="form-control" name="admin_id" id="admin_id" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">First name</label>
                                        <input type="text" class="form-control" placeholder="Enter first name" name="first_name" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div>
                                        <label class="form-label">Last name</label>
                                        <input type="text" class="form-control" placeholder="Enter last name" name="last_name" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="last_name">Email</label> 
                                    <input type="email" class="form-control" placeholder="Enter email" name="email" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="last_name">Phone</label> 
                                    <input type="tel" class="form-control" placeholder="Enter phone number" name="phone" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender" aria-label="Default select example" name="gender" required>
                                        <option selected disabled value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Designation</label>
                                    <select class="form-select" name="designation" aria-label="Default select example" required>
                                        <option selected disabled value="">Select Designation</option>
                                        <option value="super-admin">Super Admin</option>
                                        <option value="admin">Admin</option>
                                        <option value="manager">Manager</option>
                                        <option value="staff">Staff</option>
                                    </select>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary w-full">
                                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i> </span>Update Admin Account
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>