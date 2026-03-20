<div class="offcanvas offcanvas-end w-full w-lg-1/2" data-bs-scroll="true" data-bs-backdrop="true"
    tabindex="-1" id="offcanvasEditExpense" aria-labelledby="offcanvasEditExpenseLabel">
    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
        <h5 class="offcanvas-title" id="offcanvasEditExpenseLabel">Edit Expense</h5>
        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>
    <div class="offcanvas-body vstack gap-5">
        <form id="editExpensesForm" method="POST" enctype="multipart/form-data">
            <div class="row g-5">

                <!-- Hidden IDs -->
                <input type="hidden" name="expense_id">
                <input type="hidden" name="admin_id" value="<?= $_SESSION['admin_id']; ?>">

                <div class="col-md-12">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($fullName); ?>" disabled>
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
                    <input type="text" class="form-control" name="amount" id="editAmountInput" required placeholder="Enter amount">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="expense_date" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="edit-description" rows="6" placeholder="Description for expense..."></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Bill / Receipt (optional)</label>
                    <input type="file" class="form-control" name="receipt_path" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <!-- Only visible to Super Admin -->
                <div class="col-md-12"
                    style="display: <?= $_SESSION['designation'] == 'super-admin' ? 'unset' : 'none' ?>;">
                    <label class="form-label">Superior Remark</label>
                    <textarea class="form-control" name="superior_remark" id="edit-superior_remark" rows="6" placeholder="Write your review or feedback..."></textarea>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="pe-2"><i class="bi bi-save"></i></span>
                        Update Expense
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const notyf = new Notyf();
    const editForm = document.getElementById('editExpensesForm');
    const offcanvas = document.getElementById('offcanvasEditExpenses');
    const amountInput = document.getElementById('editAmountInput');

    // Format amount with commas
    amountInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/,/g, '');
        if (!isNaN(value) && value !== '') {
            e.target.value = parseFloat(value).toLocaleString('en-US', {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            });
        } else e.target.value = '';
    });

    // Remove commas before submit
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        amountInput.value = amountInput.value.replace(/,/g, '');

        const submitBtn = editForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;

        const formData = new FormData(editForm);

        try {
            const response = await fetch('./auth/update_expense_auth.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                notyf.success(data.message);
                setTimeout(() => {
                    offcanvas.classList.remove('show');
                    offcanvas.style.display = 'none';
                    document.body.style.overflow = '';
                    window.location.reload();
                }, 1000);
            } else {
                notyf.error(data.message || 'Update failed.');
            }
        } catch (error) {
            console.error(error);
            notyf.error('Network or server error.');
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span class="pe-2"><i class="bi bi-save"></i></span>Update Expense`;
    });
});
</script>
