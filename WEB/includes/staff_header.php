<?php


    require_once __DIR__ . '/../api/config.php';


    if (empty($_SESSION['user_id'])) 
    {
        header('Location: ' . BASE_URL . '/login.php'); exit;
    }
    $userRole = (int)($_SESSION['role_id'] ?? 0);
    if ($userRole !== 3 && $userRole !== 1) 
    {          // 1=Admin, 3=Staff
        header('Location: ' . BASE_URL . '/index.php'); exit;
    }

    $pageTitle = $pageTitle ?? 'Staff Panel';
    $activeNav = $activeNav ?? '';


    $todayArrivals   = 0;
    $todayDepartures = 0;
    $newMessages     = 0;
    try 
    {
        $todayArrivals = (int)$pdo->query("
            SELECT COUNT(*) FROM bookings b
            JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
            WHERE b.check_in_date = CURDATE()
            AND bs.status_name  = 'Confirmed'
        ")->fetchColumn();

        $todayDepartures = (int)$pdo->query("
            SELECT COUNT(*) FROM bookings b
            JOIN booking_status bs ON b.booking_status_id = bs.booking_status_id
            WHERE b.check_out_date = CURDATE()
            AND bs.status_name   = 'Checked In'
        ")->fetchColumn();

        $newMessages = (int)$pdo->query(
            "SELECT COUNT(*) FROM contact_messages WHERE message_status = 'New'"
        )->fetchColumn();
    } catch (Exception $e) {  }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($pageTitle) ?> | Pepperland Staff</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
        <style>
         
            .admin-sidebar          { background: #1a3a4a; }
            .sidebar-brand a        { color: #f6c90e; }
            .brand-pepper           { color: #f6c90e; }
            .brand-land             { color: #fff; }
            .sidebar-nav a.active   { background: rgba(246,201,14,.15); border-left-color: #f6c90e; color: #f6c90e; }
            .sidebar-section-label  { color: rgba(255,255,255,.4); }
            .admin-sidebar small    { color: rgba(255,255,255,.55); }
            .staff-badge            { background: #f6c90e; color: #1a3a4a; font-size:.7rem; padding:.15rem .45rem;
                                    border-radius:999px; font-weight:700; margin-left:.35rem; }
        </style>
    </head>
    <body class="admin-body">

    <div class="admin-shell">

   
        <aside class="admin-sidebar" id="staffSidebar">

            <div class="sidebar-brand">
                <a href="<?= BASE_URL ?>/staff/dashboard.php">
                    <span class="brand-pepper">Pepper</span><span class="brand-land">land</span>
                </a>
                <small>Staff Panel</small>
            </div>

            <nav class="sidebar-nav">
                <span class="sidebar-section-label">Overview</span>
                <a href="<?= BASE_URL ?>/staff/dashboard.php"
                class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                    Dashboard
                </a>

                <span class="sidebar-section-label">Front Desk</span>
                <a href="<?= BASE_URL ?>/staff/checkin.php"
                class="<?= $activeNav === 'checkin' ? 'active' : '' ?>">
                    Check-In / Check-Out
                    <?php if ($todayArrivals): ?>
                        <span class="staff-badge"><?= $todayArrivals ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/staff/bookings.php"
                class="<?= $activeNav === 'bookings' ? 'active' : '' ?>">
                    Bookings
                </a>

                <span class="sidebar-section-label">Housekeeping</span>
                <a href="<?= BASE_URL ?>/staff/housekeeping.php"
                class="<?= $activeNav === 'housekeeping' ? 'active' : '' ?>">
                    Room Status
                </a>

                <span class="sidebar-section-label">Guest Services</span>
                <a href="<?= BASE_URL ?>/staff/messages.php"
                class="<?= $activeNav === 'messages' ? 'active' : '' ?>">
                    Messages
                    <?php if ($newMessages): ?>
                        <span class="staff-badge"><?= $newMessages ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/staff/guests.php"
                class="<?= $activeNav === 'guests' ? 'active' : '' ?>">
                    Guest Lookup
                </a>
            </nav>

            <div class="sidebar-footer">
                Logged in as <strong><?= h($_SESSION['first_name']) ?></strong><br>
                <a href="<?= BASE_URL ?>/index.php">View Site</a> &nbsp;&middot;&nbsp;
                <?php if ($userRole === 1): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php">Admin Panel</a> &nbsp;&middot;&nbsp;
                <?php endif; ?>
                <a href="#" id="staffLogout">Logout</a>
            </div>
        </aside>

       
        <div class="admin-main">

           
            <div class="admin-topbar">
                <button class="nav-toggle" id="staffSidebarToggle" style="display:none;">
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
   
    const staffSidebarToggle = document.getElementById('staffSidebarToggle');
    const staffSidebar       = document.getElementById('staffSidebar');
    if (staffSidebarToggle) 
    {
        staffSidebarToggle.style.display = 'flex';
        staffSidebarToggle.addEventListener('click', () => staffSidebar.classList.toggle('open'));
    }

    async function doStaffLogout() 
    {
        await fetch('<?= BASE_URL ?>/api/auth.php?action=logout', { method: 'POST' });
        window.location.href = '<?= BASE_URL ?>/login.php';
    }
    document.getElementById('staffLogout').addEventListener('click',   e => { e.preventDefault(); doStaffLogout(); });
    document.getElementById('topbarLogout').addEventListener('click',  e => { e.preventDefault(); doStaffLogout(); });
</script>