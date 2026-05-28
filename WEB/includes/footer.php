<?php

$hotelProfile = [];
if (!empty($pdo))
{
    $stmt = $pdo->query("SELECT * FROM hotel_profile LIMIT 1");
    $hotelProfile = $stmt->fetch() ?: [];
}
?>


<footer class="site-footer" style="background: var(--forest); color: var(--cream); border-top: 1px solid rgba(201,168,76,0.15); padding: 5rem 0 3rem; font-size: 0.88rem; font-weight: 300;">
    <div class="footer-inner container" style="display: grid; grid-template-columns: 2fr repeat(3, 1fr); gap: 4rem; margin-bottom: 4rem;">

    
        <div class="footer-col">
            <p class="footer-brand" style="font-family: var(--font-display); font-size: 2rem; color: var(--cream); margin-bottom: 1rem; font-weight: 400; letter-spacing: 0.05em;">
                The Pepperland Hotel
            </p>
            <p class="footer-tagline" style="color: rgba(248,244,236,0.6); max-width: 280px; font-size: 0.85rem; line-height: 1.6;">
                <?= h($hotelProfile['description'] ?? 'Curated hospitality tailored expertly against the scenic architecture of Albay province.') ?>
            </p>
        </div>

    
        <div class="footer-col">
            <h4 style="color: var(--gold-light); font-family: var(--font-body); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem; font-weight: 600;">Directory Navigation</h4>
            <ul style="display: flex; flex-direction: column; gap: 0.75rem;">
                <li><a href="<?= BASE_URL ?>/index.php" style="color: rgba(248,244,236,0.7); transition: var(--transition);">Portfolio Home</a></li>
                <li><a href="<?= BASE_URL ?>/rooms.php" style="color: rgba(248,244,236,0.7); transition: var(--transition);">Suites Listing</a></li>
                <li><a href="<?= BASE_URL ?>/contact.php" style="color: rgba(248,244,236,0.7); transition: var(--transition);">Concierge Access</a></li>
            </ul>
        </div>


        <div class="footer-col">
            <h4 style="color: var(--gold-light); font-family: var(--font-body); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem; font-weight: 600;">Location Desk</h4>
            <?php if ($hotelProfile): ?>
                <p style="color: rgba(248,244,236,0.7); line-height: 1.6; margin-bottom: 1rem;">
                    <?= h($hotelProfile['street_address']) ?><br>
                    <?= h($hotelProfile['city']) ?>, <?= h($hotelProfile['province']) ?>
                </p>
                <p style="margin: 0;"><a href="mailto:<?= h($hotelProfile['contact_email']) ?>" style="color: var(--gold); font-size: 0.8rem; font-weight: 500;"><?= h($hotelProfile['contact_email']) ?></a></p>
            <?php endif; ?>
        </div>

  
        <div class="footer-col">
            <h4 style="color: var(--gold-light); font-family: var(--font-body); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem; font-weight: 600;">Time Matrices</h4>
            <?php if ($hotelProfile): ?>
                <p style="color: rgba(248,244,236,0.7); margin-bottom: 0.5rem;">Arrival Check-In: <span style="color: var(--cream); font-weight: 400;"><?= h(date('g:i A', strtotime($hotelProfile['check_in_time']))) ?></span></p>
                <p style="color: rgba(248,244,236,0.7); margin-bottom: 1.5rem;">Check-Out: <span style="color: var(--cream); font-weight: 400;"><?= h(date('g:i A', strtotime($hotelProfile['check_out_time']))) ?></span></p>
            <?php endif; ?>
            <p style="color: var(--gold); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">
                <?php
                $stars = $hotelProfile['star_rating'] ?? 0;
                echo $stars ? h($stars) . ' Star Rated Hotel Property' : '';
                ?>
            </p>
        </div>

    </div>

    <div class="footer-bottom container" style="border-top: 1px solid rgba(248,244,236,0.1); padding-top: 2rem; display: flex; justify-content: space-between; font-size: 0.75rem; color: rgba(248,244,236,0.4); letter-spacing: 0.02em;">
        <p>&copy; <?= date('Y') ?> The Pepperland Hotel. Portfolio properties documented securely.</p>
        <p style="text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">Legazpi, Philippines</p>
    </div>
</footer>

<!-- main JS-->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>


</body>
</html>