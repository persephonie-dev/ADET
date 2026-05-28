<?php
require_once __DIR__ . '/api/config.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">

<div class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Welcome Back</h2>
        <p class="text-muted auth-subtitle">Ready to start a new experience?</p>

        <div id="loginError" class="auth-error"></div>

        <div>
            <div class="qb-field auth-field-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="you@email.com" autocomplete="email">
            </div>
            <div class="qb-field auth-field-group last">
                <label for="password">Password </label>
                <input type="password" id="password" placeholder="Account password" autocomplete="current-password">
            </div>

            <button class="btn btn-dark auth-btn" id="loginBtn">Login</button>
        </div>

        <p class="auth-footer-text">
            First time booking with us?<a href="<?= BASE_URL ?>/register.php"> Register a new account</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    const CONFIG = { baseUrl: '<?= BASE_URL ?>' };
</script>
<script src="<?= BASE_URL ?>/assets/js/login.js" defer></script>