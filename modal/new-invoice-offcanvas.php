                <div class="offcanvas offcanvas-end w-full w-lg-1/2" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNewInvoice" aria-labelledby="offcanvasAddNewInvoiceLabel">
                    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
                        <h5 class="offcanvas-title" id="offcanvasAddNewInvoiceLabel">Create Invoice</h5>
                        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body vstack gap-5">
                        <form id="invoiceForm">
                            <div class="col-sm-12 mb-3">
                                <label>Customer</label>
                                <select name="customer_id" class="form-select" required>
                                    <?php if (!empty($customers)): ?>
                                        <?php foreach ($customers as $c): ?>
                                            <option value="<?= $c['id'] ?>">
                                                <?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No customers found</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div id="itemsContainer"></div>

                            <button type="button" id="addItemBtn" class="btn btn-sm btn-success mb-3">
                                + Add Item
                            </button>

                            <div class="col-sm-12 mb-3 text-end mt-3">
                                <h5>Subtotal: ₦ <span id="subTotalDisplay">0</span></h5>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label>Discount Type</label>
                                    <select id="discountType" name="discount_type" class="form-select">
                                        <option value="fixed">Fixed (₦)</option>
                                        <option value="percent">Percentage (%)</option>
                                    </select>
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label>Discount Value</label>
                                    <input type="text" id="discountValue" name="discount" class="form-control" value="0">
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label>Tax Type</label>
                                    <select id="taxType" name="tax_type" class="form-select">
                                        <option value="fixed">Fixed (₦)</option>
                                        <option value="percent">Percentage (%)</option>
                                    </select>
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label>Tax Value</label>
                                    <input type="text" id="taxValue" name="tax" class="form-control" value="0">
                                </div>
                            </div>

                            <div class="col-sm-12 mb-3 text-end mt-3">
                                <h5>Grand Total: ₦ <span id="grandTotalDisplay">0</span></h5>
                            </div>

                            <div class="col-sm-12 mb-5">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary w-full">
                                    <span class="pe-2"><i class="bi bi-send"></i></span>
                                    Create Invoice
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>