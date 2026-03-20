<div class="offcanvas offcanvas-end w-full w-lg-1/2" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNewExpenses" aria-labelledby="offcanvasAddNewExpensesLabel">
    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
        <h5 class="offcanvas-title" id="offcanvasAddNewExpensesLabel">New Expense</h5>
        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body vstack gap-5">
        <form id="expensesForm" method="POST" enctype="multipart/form-data">
            <div class="row g-5">

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
                    <input type="text" class="form-control" name="expense_title" required placeholder="Errand">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        <option selected disabled value="">Select Category</option>
                        <option value="Rent and Utilities">Rent and Utilities</option>
                        <option value="Office Supplies">Office Supplies</option>
                        <option value="Equipment and Technology">Equipment and Technology</option>
                        <option value="Personnel Costs">Personnel Costs</option>
                        <option value="Professional Services">Professional Services</option>
                        <option value="Travel and Transportation">Travel and Transportation</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Maintenance and Repairs">Maintenance and Repairs</option>
                        <option value="Miscellaneous / Other">Miscellaneous / Other</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Amount</label>
                    <input type="text" class="form-control" name="amount" id="amountInput" required placeholder="Enter amount">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="expense_date" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="6" placeholder="Description for expense..."></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Bill / Receipt</label>
                    <input type="file" class="form-control" name="receipt_path" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <!-- Only visible to Super Admin -->
                <div class="col-md-12" style="display: <?= $_SESSION['designation'] == 'super-admin' ? 'unset' : 'none' ?>;">
                    <label class="form-label">Superior Remark</label>
                    <textarea class="form-control" name="superior_remark" id="superior_remark" rows="6" placeholder="Write your review or feedback..."></textarea>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="pe-2"><i class="bi bi-send"></i></span>
                        <?= $_SESSION['designation'] == 'super-admin' ? 'Review Expense' : 'Send Expense' ?>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const amountInput = document.getElementById('amountInput');

        amountInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/,/g, ''); // Remove existing commas
            if (!isNaN(value) && value !== '') {
                // Format number with commas
                e.target.value = parseFloat(value).toLocaleString('en-US', {
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 0
                });
            } else {
                e.target.value = '';
            }
        });

        // Optional: remove commas before form submission
        const form = document.getElementById('expensesForm');
        form.addEventListener('submit', () => {
            amountInput.value = amountInput.value.replace(/,/g, '');
        });
    });
</script>
