                <?php
                // Make sure the DB is connected
                require_once __DIR__ . '/../config/db.php';

                // Fetch admins list safely
                $admins = [];
                try {
                    $stmt = $pdo->query("SELECT id, first_name, last_name FROM admin ORDER BY first_name ASC");
                    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $admins = [];
                }
                ?>
                <div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAssignDevice" aria-labelledby="offcanvasAssignDeviceLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasAssignDeviceLabel">Aassign device</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form id="assignDeviceForm">
                            <div class="col-md-12" style="display: none;">
                                <div>
                                    <label class="form-label">Device ID</label>
                                    <input type="text" class="form-control" name="device_id" id="assign_device_id">
                                </div>
                            </div>

                            <div class="col-sm-12 mb-3">
                                <label class="form-label">Select Staff</label>
                                <select class="form-select" name="admin_id" required>
                                    <option value="">-- Select Staff --</option>
                                    <?php if (!empty($admins)): ?>
                                        <?php foreach ($admins as $admin): ?>
                                            <option value="<?= htmlspecialchars($admin['id']) ?>">
                                                <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No admins available</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-sm-12 mb-3">
                                <label class="form-label">Condition at Assignment</label>
                                <input type="text" class="form-control" name="assigned_condition" placeholder="e.g., Excellent" required>
                            </div>

                            <div class="col-sm-12 mb-3">
                                <label class="form-label">Remarks (Optional)</label>
                                <textarea class="form-control" name="remarks" rows="3" placeholder="Additional notes..."></textarea>
                            </div>

                            <div class="col-sm-12 mt-5">
                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="pe-2"><i class="bi bi-person-plus"></i> </span> Assign Device
                                </button>
                            </div>
                        </form>
                    </div>
                </div>