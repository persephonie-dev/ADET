<?php
require_once __DIR__ . '/api/config.php';
require_login();

$pageTitle = 'My Bookings';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">

<section class="section">
    <div class="container">
        <div class="profile-header-wrap">
            <div>
                <span class="section-label">Guest Profile</span>
                <h2 class="profile-title">Your Residences</h2>
                <p class="text-muted profile-subtitle">Welcome back, <?= h($_SESSION['first_name']) ?>. Track your luxury reservations below.</p>
            </div>
            <a href="<?= BASE_URL ?>/rooms.php" class="btn btn-gold">Plan Another Stay</a>
        </div>

        <div id="bookingsWrap">
            <p class="text-muted profile-subtitle" style="font-style: italic;">Accessing database registration layers...</p>
        </div>
    </div>
</section>

<div id="reviewModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Share Your Experience</h3>
            <button id="closeReviewModal" class="modal-close-btn">&times;</button>
        </div>
        
        <input type="hidden" id="reviewBookingId">
        
        <div class="qb-field auth-field-group">
            <label>Rating Evaluation</label>
            <div id="starRating" class="star-rating-container">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star" data-value="<?= $i ?>">&#9733;</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" id="ratingValue" value="0">
        </div>
        
        <div class="qb-field auth-field-group">
            <label for="reviewTitle">Summary Title (Optional)</label>
            <input type="text" id="reviewTitle" placeholder="Exceptional design and attention...">
        </div>
        
        <div class="qb-field auth-field-group last">
            <label for="reviewComment">Elaborate Commentary</label>
            <textarea id="reviewComment" rows="4" class="review-textarea" placeholder="Detail elements regarding staff hospitality or room environment..."></textarea>
        </div>
        
        <div id="reviewError" class="auth-error"></div>

        <div class="modal-actions">
            <button class="btn btn-outline modal-cancel-btn" id="cancelReview">Cancel</button>
            <button class="btn btn-dark" id="submitReview">Post Registry</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    const CONFIG = { baseUrl: '<?= BASE_URL ?>' };
</script>
<script src="<?= BASE_URL ?>/assets/js/profile.js" defer></script>