<?php

    require_once __DIR__ . '/../api/config.php';


    require_admin();

    $pageTitle = $pageTitle ?? 'Admin Panel';
    $activeNav = $activeNav ?? '';

    $pendingReviews  = 0;
    $newMessages     = 0;
    $pendingBookings = 0;
    try 
    {
        $pendingReviews  = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE review_status = 'Pending'")->fetchColumn();
        $newMessages     = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE message_status = 'New'")->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id WHERE bs.status_name = 'Pending'");
        $stmt->execute();
        $pendingBookings = (int)$stmt->fetchColumn();
    } catch (Exception $e) 
    {
       
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($pageTitle) ?> | Pepperland Admin</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    </head>
    <body class="admin-body">

    <div class="admin-shell">

   
        <aside class="admin-sidebar" id="adminSidebar">

            <div class="sidebar-brand">
                <a href="<?= BASE_URL ?>/admin/dashboard.php">
                    <span class="brand-pepper">Pepper</span><span class="brand-land">land</span>
                </a>
                <small>Admin Panel</small>
            </div>

            <nav class="sidebar-nav">
                <span class="sidebar-section-label">Overview</span>
                <a href="<?= BASE_URL ?>/admin/dashboard.php"
                class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                    Dashboard
                </a>

                <span class="sidebar-section-label">Manage</span>
                <a href="<?= BASE_URL ?>/admin/bookings.php"
                class="<?= $activeNav === 'bookings' ? 'active' : '' ?>">
                    Bookings
                    <?php if ($pendingBookings): ?>
                        <span class="sidebar-badge"><?= $pendingBookings ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/rooms.php"
                class="<?= $activeNav === 'rooms' ? 'active' : '' ?>">
                    Rooms
                </a>
                <a href="<?= BASE_URL ?>/admin/staff_management.php"
                class="<?= $activeNav === 'staff_management' ? 'active' : '' ?>">
                    Staff Management
                </a>
                <a href="<?= BASE_URL ?>/admin/users.php"
                class="<?= $activeNav === 'users' ? 'active' : '' ?>">
                    Guests / Users
                </a>
                <a href="<?= BASE_URL ?>/admin/promotions.php"
                class="<?= $activeNav === 'promotions' ? 'active' : '' ?>">
                    Promotions
                </a>

                <span class="sidebar-section-label">Content</span>
                <a href="<?= BASE_URL ?>/admin/reviews.php"
                class="<?= $activeNav === 'reviews' ? 'active' : '' ?>">
                    Reviews
                    <?php if ($pendingReviews): ?>
                        <span class="sidebar-badge"><?= $pendingReviews ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/messages.php"
                class="<?= $activeNav === 'messages' ? 'active' : '' ?>">
                    Messages
                    <?php if ($newMessages): ?>
                        <span class="sidebar-badge"><?= $newMessages ?></span>
                    <?php endif; ?>
                </a>
            </nav>

            <div class="sidebar-footer">
                Logged in as <strong><?= h($_SESSION['first_name']) ?></strong><br>
                <a href="<?= BASE_URL ?>/index.php">View Site</a> &nbsp;&middot;&nbsp;
                <a href="#" id="sidebarLogout">Logout</a>
            </div>
        </aside>

   
        <div class="admin-main">


            <div class="admin-topbar">
        
                <button class="nav-toggle" id="sidebarToggle" style="display:none;">
                    <span></span><span></span><span></span>
                </button>
                <h1><?= h($pageTitle) ?></h1>
                <div class="topbar-user">
                    <span>Welcome, <strong><?= h($_SESSION['first_name']) ?></strong></span>
                    <a href="#" id="topbarLogout">Logout</a>
                </div>
            </div>


            <div class="admin-content">

<script>

    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar  = document.getElementById('adminSidebar');
    if (sidebarToggle) 
        {
        sidebarToggle.style.display = 'flex';
        sidebarToggle.addEventListener('click', () => adminSidebar.classList.toggle('open'));
    }

    
    async function doAdminLogout() 
    {
        await fetch('<?= BASE_URL ?>/api/auth.php?action=logout', { method: 'POST' });
        window.location.href = '<?= BASE_URL ?>/login.php';
    }
    document.getElementById('sidebarLogout').addEventListener('click', e => { e.preventDefault(); doAdminLogout(); });
    document.getElementById('topbarLogout').addEventListener('click',  e => { e.preventDefault(); doAdminLogout(); });
</script>