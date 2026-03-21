<div class="offcanvas offcanvas-end w-full w-lg-1/2" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasSellProduct">
    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
        <h5 class="offcanvas-title" id="offcanvasSellProductLabel">Sell Product</h5>
        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body vstack gap-5">

        <form id="sellProductForm">

            <!-- Hidden -->
            <input type="hidden" name="product_id" id="sellProductId">
            <input type="hidden" name="price" id="hiddenPrice">
            <input type="hidden" name="total_amount" id="hiddenTotal">
            <input type="hidden" name="admin_id" value="<?= $_SESSION['admin_id'] ?>">

            <!-- Product Image -->
            <div class="col-sm-12 text-center">
                <img id="sellProductImage" src="" style="max-width:120px; border-radius:8px; display:none;">
            </div>

            <!-- Product Name -->
            <div class="col-sm-12">
                <label class="form-label">Product</label>
                <input type="text" id="sellProductName" class="form-control" readonly>
            </div>

            <!-- Price -->
            <div class="col-sm-12">
                <label class="form-label">Price</label>
                <div class="input-group mb-3">
                    <span class="input-group-text">₦</span>
                    <input type="text" id="sellProductPrice" class="form-control" readonly>
                </div>
            </div>

            <!-- Customer -->
            <div class="col-sm-12">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">Select Customer</option>
                    <?php
                    $customers = $pdo->query("SELECT id, first_name, last_name FROM users ORDER BY first_name ASC");
                    foreach ($customers as $customer):
                    ?>
                        <option value="<?= $customer['id'] ?>">
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Quantity -->
            <div class="col-md-12">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" id="sellQuantity" class="form-control" min="1" value="1" required>
            </div>

            <!-- Total -->
            <div class="col-sm-12">
                <label class="form-label">Total Cost</label>
                <div class="input-group mb-3">
                    <span class="input-group-text">₦</span>
                    <input type="text" id="sellTotal" class="form-control text-end" readonly>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="col-md-12 mb-4">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

            <div class="col-sm-12">
                <button type="submit" class="btn btn-success w-full">
                    Complete Sale
                </button>
            </div>

        </form>

    </div>
</div>