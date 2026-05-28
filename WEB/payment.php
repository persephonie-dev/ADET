<?php
require_once __DIR__ . '/api/config.php';
require_login();

$bookingId = (int)($_GET['booking_id'] ?? 0);
if (!$bookingId) {
    header('Location: ' . BASE_URL . '/profile.php');
    exit;
}

$pageTitle = 'Complete Payment';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">

<div class="container section payment-container">
    <span class="section-label text-center-block">Secured Transaction</span>
    <h2 class="text-center payment-title">Finalize Allocation</h2>
    <p class="text-muted text-center payment-subtitle">Review details below to complete your billing submission.</p>

    <div id="loadingMsg" class="text-muted text-center payment-loading">Assembling account parameters...</div>
    
    <div id="bookingDetails" style="display: none;">
        <div class="payment-summary-card">
            <h3 class="payment-summary-title">Selected Plan</h3>
            <div id="summaryContent"></div>
        </div>

        <div class="payment-form-card">
            <h3 class="payment-summary-title">Settlement Channel</h3>

            <div id="paymentError" class="payment-error-banner"></div>

            <div class="qb-field payment-field-space">
                <label for="paymentMethod">Select Preference</label>
                <select id="paymentMethod">
                    <option value="">-- Choose verified channel --</option>
                </select>
            </div>
            
            <div class="qb-field payment-field-space large">
                <label for="transactionRef">Reference Code / Transaction ID</label>
                <input type="text" id="transactionRef" placeholder="E.g., Bank transaction string or transfer voucher...">
                <p class="payment-hint">
                    For front desk cash ledger registration, specify your voucher sequence number here.
                </p>
            </div>

            <button class="btn btn-gold payment-btn" id="payBtn">Authorize Remittance</button>
        </div>
    </div>

    <div id="errorMsg" class="payment-error-msg text-center-block text-muted"></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    const CONFIG = {
        baseUrl: '<?= BASE_URL ?>',
        bookingId: <?= $bookingId ?>
    };
</script>
<script src="<?= BASE_URL ?>/assets/js/payment.js" defer></script>