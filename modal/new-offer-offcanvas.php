<?php
// Ensure DB connection
require_once __DIR__ . '/../config/db.php';

// Fetch approved applicants from pof_application
$applicants = [];
try {
    $stmt = $pdo->query("
        SELECT 
            id, 
            user_id,
            first_name, 
            last_name, 
            loan_duration_months AS duration, 
            loan_amount AS amount
        FROM pof_application 
        WHERE status = 'approved'
        ORDER BY first_name ASC
    ");
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $applicants = [];
}
?>

<div class="offcanvas offcanvas-end w-full w-lg-1/3" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1" id="offcanvasAddNewOffer" aria-labelledby="offcanvasAddNewOfferLabel">
    <div class="offcanvas-header border-bottom py-4 bg-surface-secondary">
        <h5 class="offcanvas-title" id="offcanvasAddNewOfferLabel">Create a New Offer</h5>
        <button type="button" class="btn-close text-reset text-xs" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body vstack gap-5">
        <form id="createOfferForm" method="POST">
            <div class="row g-5">
                
                <div class="col-md-12">
                    <label class="form-label">Applicant</label>
                    <select class="form-select" name="user_id" id="user_id" required>
                        <option selected disabled value="">Select Applicant</option>
                        <?php if (!empty($applicants)): ?>
                            <?php foreach ($applicants as $app): ?>
                                <option 
                                    value="<?= htmlspecialchars($app['user_id']) ?>" 
                                    data-duration="<?= htmlspecialchars($app['duration']) ?>"
                                    data-amount="<?= htmlspecialchars($app['amount']) ?>">
                                    <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option disabled>No applicants available</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Loan Duration</label>
                    <select class="form-select" name="duration" id="duration" required>
                        <option selected disabled value="">Select Duration</option>
                        <?php for ($i = 1; $i <= 18; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Month' : 'Months' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Loan Amount</label>
                    <input type="text" class="form-control" name="amount" id="amount" placeholder="Loan amount" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Percentage</label>
                    <input type="text" class="form-control" name="percentage" id="percentage" placeholder="Enter percentage" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Transaction Fee</label>
                    <input type="text" class="form-control" name="fee" id="fee" placeholder="Fee will auto-calculate" required readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Transaction Type</label>
                    <select class="form-select" name="transaction_type" id="transaction_type" required>
                        <option selected disabled value="">Select Transaction Type</option>
                        <option value="Personal Proof of Fund">Personal Proof of Fund</option>
                        <option value="Business Proof of Fund">Business Proof of Fund</option>
                        <option value="Employment Salary History">Employment Salary History</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" rows="3" placeholder="Write your message..." required></textarea>
                </div>

                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary w-full">
                        <span class="pe-2"><i class="bi bi-plus-square-dotted"></i></span>
                        Create Offer
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
function formatNumber(num) {
    if (!num) return '';
    return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const userSelect = document.getElementById('user_id');
const durationSelect = document.getElementById('duration');
const amountInput = document.getElementById('amount');
const percentageInput = document.getElementById('percentage');
const feeInput = document.getElementById('fee');

userSelect?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const duration = selected.getAttribute('data-duration');
    const amount = selected.getAttribute('data-amount');

    // Set duration dropdown
    let found = false;
    for (const opt of durationSelect.options) {
        if (opt.value === duration) {
            opt.selected = true;
            found = true;
            break;
        }
    }
    if (!found) durationSelect.value = '';

    // Set loan amount with commas
    amountInput.value = formatNumber(amount);
    calculateFee();
});

percentageInput?.addEventListener('input', calculateFee);
amountInput?.addEventListener('input', calculateFee);

function calculateFee() {
    // Remove commas for calculation
    const amount = parseFloat(amountInput.value.replace(/,/g, '')) || 0;
    const percentage = parseFloat(percentageInput.value) || 0;
    const fee = (amount * percentage) / 100;

    feeInput.value = formatNumber(fee);
    amountInput.value = formatNumber(amount); // maintain comma formatting while typing
}
</script>