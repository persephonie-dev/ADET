<?php
    require_once __DIR__ . '/api/config.php';

    if (!empty($_SESSION['user_id'])) 
    {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    $pageTitle = 'Create Account';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">

    <div class="auth-container register">
        <div class="auth-card wide">
            <h2 class="auth-title">Account Registration</h2>
            <p class="text-muted auth-subtitle">Book a stay with us</p>

            <div id="regError" class="auth-error"></div>

            <div>
                <div class="grid-2col">
                    <div class="qb-field">
                        <label for="firstName">Given Name *</label>
                        <input type="text" id="firstName" placeholder="First Name" autocomplete="given-name">
                    </div>
                    <div class="qb-field">
                        <label for="lastName">Surname *</label>
                        <input type="text" id="lastName" placeholder="Surname" autocomplete="family-name">
                    </div>
                </div>

                <div class="qb-field auth-field-group">
                    <label for="middleName">Middle Name *</label>
                    <input type="text" id="middleName" placeholder="Middle Name" autocomplete="additional-name">
                </div>

                <div class="qb-field auth-field-group">
                    <label for="dob">Date of Birth *</label>
                    <input type="date" id="dob" autocomplete="bday">
                </div>

                <div class="qb-field auth-field-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" placeholder="you@email.com" autocomplete="email">
                </div>

                <div class="qb-field auth-field-group">
                    <label for="phone">Mobile Number (Optional)</label>
                    <input type="tel" id="phone" placeholder="+63 912 345 6789" autocomplete="tel">
                </div>

                <div class="grid-address">
                    <div class="qb-field">
                        <label for="streetAdr">Street Address *</label>
                        <input type="text" id="streetAdr" placeholder="123 Main St." autocomplete="street-address">
                    </div>
                    <div class="qb-field">
                        <label for="city">City *</label>
                        <input type="text" id="city" placeholder="Legazpi City" autocomplete="address-level2">
                    </div>
                    <div class="qb-field">
                        <label for="region">Region *</label>
                        <input type="text" id="region" placeholder="Bicol" autocomplete="address-level1">
                    </div>
                </div>

                <div class="qb-field auth-field-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" placeholder="Minimum 8 characters" autocomplete="new-password">
                </div>

                <div class="qb-field auth-field-group last">
                    <label for="passwordConfirm">Confirm Password *</label>
                    <input type="password" id="passwordConfirm" placeholder="Repeat password" autocomplete="new-password">
                </div>

                <button class="btn btn-dark auth-btn" id="registerBtn">Create Account</button>
            </div>

            <p class="auth-footer-text">
                Already have an account? <a href="<?= BASE_URL ?>/login.php">Sign In</a>
            </p>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    const CONFIG = { baseUrl: '<?= BASE_URL ?>' };
</script>
<script src="<?= BASE_URL ?>/assets/js/register.js" defer></script>