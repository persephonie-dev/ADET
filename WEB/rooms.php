<?php
    $pageTitle = 'Rooms & Rates';
    require_once __DIR__ . '/includes/header.php';

    $checkIn  = $_GET['check_in']  ?? '';
    $checkOut = $_GET['check_out'] ?? '';
    $guests   = (int)($_GET['guests'] ?? 1);
    $isSearch = !empty($checkIn) && !empty($checkOut);
    ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pages.css">

    <div class="rooms-header">
        <div class="container">
            <span class="section-label" style="color: var(--gold-light);">Accommodation</span>
            <h1>Rooms &amp; Suites</h1>
            <div class="gold-line center"></div>
        </div>
    </div>

    <div class="filter-bar">
        <div class="container">
            <form id="searchForm" action="rooms.php" method="GET" class="filter-form">
                <div class="qb-field">
                    <label for="check_in">Check-in</label>
                    <input type="date" name="check_in" id="check_in" value="<?= h($checkIn) ?>" required>
                </div>
                <div class="qb-field">
                    <label for="check_out">Check-out</label>
                    <input type="date" name="check_out" id="check_out" value="<?= h($checkOut) ?>" required>
                </div>
                <div class="qb-field">
                    <label for="guests">Guests</label>
                    <select name="guests" id="guests">
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= $guests === $i ? 'selected' : '' ?>><?= $i ?> Guests</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark filter-search-btn">Update Search</button>
            </form>
        </div>
    </div>

    <section class="section">
        <div class="container">

            <?php if ($isSearch): ?>
                <p class="text-muted search-meta-text">
                    Showing configuration options available for 
                    <strong><?= h(date('M j, Y', strtotime($checkIn))) ?></strong> &mdash; 
                    <strong><?= h(date('M j, Y', strtotime($checkOut))) ?></strong> 
                    &middot; Unique for <?= $guests ?> guest<?= $guests > 1 ? 's' : '' ?>
                </p>
            <?php endif; ?>

            <div class="rooms-grid" id="roomsGrid">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="room-card">
                        <div class="room-card-img">
                            <div class="loader loader-dark"></div>
                        </div>
                        <div class="room-card-body">
                            <div class="skeleton skeleton-text-sm"></div>
                            <div class="skeleton skeleton-text-md"></div>
                            <div class="skeleton skeleton-text-lg"></div>
                            <div class="skeleton skeleton-text-btn"></div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <p id="noRoomsMsg" class="text-muted text-center no-rooms-banner">
                No suites correspond exactly with those dates.
            </p>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    const CONFIG = 
    {
        baseUrl: '<?= BASE_URL ?>',
        checkIn: '<?= h($checkIn) ?>',
        checkOut: '<?= h($checkOut) ?>',
        guests: <?= $guests ?>
    };
</script>
<script src="<?= BASE_URL ?>/assets/js/rooms.js" defer></script>