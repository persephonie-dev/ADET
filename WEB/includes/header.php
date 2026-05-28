<?php

require_once __DIR__ . '/../api/config.php';

$pageTitle = $pageTitle ?? 'The Pepperland Hotel';
$bodyClass = $bodyClass ?? '';


$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($pageTitle) ?> | The Pepperland Hotel</title>

    
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

        <!-- Main stylesheet -->
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
        
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">

    </head>         
<body class="<?= h($bodyClass) ?>">


<nav class="navbar" style="position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; background: #101e14; border-bottom: 1px solid rgba(201, 168, 76, 0.15); padding: 1.25rem 0;">
    <div class="navbar-inner container" style="display: flex; align-items: center; justify-content: space-between;">
        <!-- Logo / brand -->
        <a href="<?= BASE_URL ?>/index.php" class="navbar-brand" style="font-family: var(--font-display); font-size: 1.8rem; letter-spacing: 0.05em; color: var(--cream);">
            <span style="color: var(--gold-light); font-weight: 300; font-style: italic;">The</span> Pepperland Hotel
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" style="background: transparent; border: none; cursor: pointer; display: none;">
            <span style="display: block; width: 20px; height: 1px; background: var(--cream); margin-bottom: 4px;"></span>
            <span style="display: block; width: 20px; height: 1px; background: var(--cream); margin-bottom: 4px;"></span>
            <span style="display: block; width: 20px; height: 1px; background: var(--cream);"></span>
        </button>

        <ul class="nav-links" id="navLinks" style="display: flex; align-items: center; gap: 2rem; font-size: 0.8rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.1em; list-style: none;">
            <li>
                <a href="<?= BASE_URL ?>/index.php" style="color: <?= $currentPage === 'index' ? 'var(--gold)' : 'rgba(248,244,236,0.8)' ?>; transition: var(--transition);">Home</a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/rooms.php" style="color: <?= $currentPage === 'rooms' ? 'var(--gold)' : 'rgba(248,244,236,0.8)' ?>; transition: var(--transition);">Rooms</a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/contact.php" style="color: <?= $currentPage === 'contact' ? 'var(--gold)' : 'rgba(248,244,236,0.8)' ?>; transition: var(--transition);">Contact</a>
            </li>

            <?php if (!empty($_SESSION['user_id'])): ?>
               
                <li>
                    <button id="sidebarToggleBtn" style="background: transparent; border: none; cursor: pointer; color: var(--gold); font-family: var(--font-body); font-size: 0.8rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.1em; transition: var(--transition);">
                        Account (<?= h($_SESSION['first_name']) ?>)
                    </button>
                </li>
            <?php else: ?>
          
                <li>
                    <a href="<?= BASE_URL ?>/login.php" style="color: <?= $currentPage === 'login' ? 'var(--gold)' : 'rgba(248,244,236,0.8)' ?>; transition: var(--transition);">Login</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-gold" style="padding: 0.4rem 1.2rem; font-size: 0.75rem;">
                        Register
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<?php if (!empty($_SESSION['user_id'])): ?>

<div id="userSidebar" class="user-sidebar">
    <div class="sidebar-header">
        <span class="sidebar-title">Menu</span>
        <button id="sidebarCloseBtn" class="sidebar-close" aria-label="Close menu">&times;</button>
    </div>
    
    <div class="sidebar-user-info">
        Logged in as: <strong><?= h($_SESSION['first_name']) ?></strong>
    </div>
    
    <ul class="sidebar-menu-links">
        <li>
           
            <a href="<?= BASE_URL ?>/update_profile.php">Update Information</a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/profile.php">Transaction History</a>
        </li>
        <?php if ((int)$_SESSION['role_id'] === 1): ?>
            <li>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" style="color: var(--gold-light);">Admin Panel</a>
            </li>
        <?php endif; ?>
        <?php if ((int)$_SESSION['role_id'] === 3): ?>
            <li>
                <a href="<?= BASE_URL ?>/staff/dashboard.php" style="color: var(--gold-light);">Staff Panel</a>
            </li>
        <?php endif; ?>
        <li class="sidebar-logout-wrapper">
            <a href="#" id="sidebarLogoutBtn" class="sidebar-logout-link">
                Logout
            </a>
        </li>
    </ul>
</div>
<?php endif; ?>

<script>

    document.getElementById('navToggle').addEventListener('click', function() {
        document.getElementById('navLinks').classList.toggle('open');
    });

    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const userSidebar = document.getElementById('userSidebar');

    if (sidebarToggleBtn && userSidebar) {
        sidebarToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userSidebar.classList.add('open');
        });
    }

    if (sidebarCloseBtn && userSidebar) {
        sidebarCloseBtn.addEventListener('click', function() {
            userSidebar.classList.remove('open');
        });
    }


    document.addEventListener('click', function(e) {
        if (userSidebar && userSidebar.classList.contains('open')) {
            if (!userSidebar.contains(e.target) && e.target !== sidebarToggleBtn) {
                userSidebar.classList.remove('open');
            }
        }
    });

   
    const registerLogoutHandler = (elementId) => {
        const btn = document.getElementById(elementId);
        if (btn) {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                await fetch('<?= BASE_URL ?>/api/auth.php?action=logout', { method: 'POST' });
                window.location.href = '<?= BASE_URL ?>/index.php';
            });
        }
    };

    registerLogoutHandler('sidebarLogoutBtn');
</script>