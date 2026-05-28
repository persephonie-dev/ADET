<?php

    $pageTitle = 'Contact Us';
    require_once __DIR__ . '/includes/header.php';

    $stmt = $pdo->query("SELECT * FROM hotel_profile LIMIT 1");
    $hotel = $stmt->fetch() ?: [];
    ?>
    <!-- TBA: About Us section -->
    <!-- Page header -->
    <div style="background: linear-gradient(135deg, var(--forest) 0%, var(--forest-mid) 100%); padding: 5rem 0 4rem; text-align: center;">
        <div class="container">
            <span class="section-label" style="color: var(--gold-light);">About us</span>
            <h1 style="color: var(--white); font-weight: 300;">Connect with Us</h1>
            <div class="gold-line center"></div>
        </div>
    </div>

    <div class="container section" style="display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: start;">

        <!-- Left Column -->
        <div>
            <span class="section-label">Location Details</span>
            <h3 style="font-family: var(--font-display); font-size: 2.2rem; margin-bottom: 1.5rem; font-weight: 300;">The Destination</h3>
            
            <?php if ($hotel): ?>
                <p style="font-size: 1.05rem; margin-bottom: 2rem; font-weight: 300;">
                    <?= h($hotel['street_address']) ?><br>
                    <?= h($hotel['city']) ?>, <?= h($hotel['province']) ?><br>
                    <?= h($hotel['country']) ?>
                </p>
                
                <div style="margin-bottom: 1.5rem; font-size: 0.95rem;">
                    <strong style="display:block; text-transform:uppercase; font-size:0.75rem; color:var(--gold-dark); margin-bottom:0.25rem;">Direct Reception Line</strong>
                    <a href="tel:<?= h($hotel['contact_phone']) ?>" style="color: var(--forest); font-weight: 500;"><?= h($hotel['contact_phone']) ?></a>
                </div>
                
                <div style="margin-bottom: 1.5rem; font-size: 0.95rem;">
                    <strong style="display:block; text-transform:uppercase; font-size:0.75rem; color:var(--gold-dark); margin-bottom:0.25rem;">Concierge Email</strong>
                    <a href="mailto:<?= h($hotel['contact_email']) ?>" style="color: var(--forest); font-weight: 500;"><?= h($hotel['contact_email']) ?></a>
                </div>
                
                <div style="margin-bottom: 1.5rem; font-size: 0.95rem; border-top: 1px solid var(--cream-dark); padding-top: 1.5rem; display: flex; gap: 2rem;">
                    <div>
                        <strong style="display:block; text-transform:uppercase; font-size:0.75rem; color:var(--muted);">Check-In Frame</strong>
                        <span><?= h(date('g:i A', strtotime($hotel['check_in_time']))) ?></span>
                    </div>
                    <div>
                        <strong style="display:block; text-transform:uppercase; font-size:0.75rem; color:var(--muted);">Check-Out Departure</strong>
                        <span><?= h(date('g:i A', strtotime($hotel['check_out_time']))) ?></span>
                    </div>
                </div>

            <?php else: ?>
                <p class="text-muted" style="font-style: italic;">Run into a problem. Please try again later.</p>
            <?php endif; ?>

        </div>

        <!-- Right Column -->
        <div style="background: var(--white); padding: 3rem; border-radius: var(--radius); box-shadow: var(--shadow-md); border: 1px solid var(--cream-dark);">
            <h3 style="font-family: var(--font-display); font-size: 1.6rem; margin-bottom: 1.5rem;">Digital Dispatch</h3>

            <div id="formSuccess" class="text-muted" style="display:none; color: var(--success); margin-bottom: 1.5rem; font-style: italic;">
                Your information have been logged. A hospitality representative will follow up shortly.
            </div>
            <div id="formError" class="text-muted" style="display:none; color: var(--danger); margin-bottom: 1.5rem; padding-left: 1rem; border-left: 2px solid var(--danger);"></div>

            <div id="contactForm">
                <div class="qb-field" style="margin-bottom: 1.25rem;">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" placeholder="Name">
                </div>
                <div class="qb-field" style="margin-bottom: 1.25rem;">
                    <label for="email">Contact Email</label>
                    <input type="email" id="email" placeholder="you@email.com" required>
                </div>
                <div class="qb-field" style="margin-bottom: 1.25rem;">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" placeholder="Suite configurations or events mapping...">
                </div>
                <div class="qb-field" style="margin-bottom: 2rem;">
                    <label for="message">Message</label>
                    <textarea id="message" rows="4" style="width:100%; padding:0.8rem; border:1px solid var(--cream-dark); background:var(--cream); font-family:var(--font-body); border-radius:var(--radius); outline:none;" placeholder="Hello there!..."></textarea>
                </div>
                <button class="btn btn-gold" id="sendBtn" style="width: 100%; text-align: center; padding: 0.9rem;">Send Message</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    document.getElementById('sendBtn').addEventListener('click', async function () 
    {
        const btn    = this;
        const errEl  = document.getElementById('formError');
        const succEl = document.getElementById('formSuccess');
        errEl.style.display  = 'none';
        succEl.style.display = 'none';

        const name    = document.getElementById('name').value.trim();
        const email   = document.getElementById('email').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('message').value.trim();

        if (!email || !subject || !message) 
        {
            errEl.textContent  = 'Kindly update missing input slots before requesting context delivery.';
            errEl.style.display = 'block';
            return;
        }

        btn.disabled    = true;
        btn.textContent = 'Transmitting credentials...';

        try 
        {
            await apiFetch('<?= BASE_URL ?>/api/contact.php', 
            {
                method: 'POST',
                body: { name, email, subject, message }
            });
            document.getElementById('contactForm').style.display = 'none';
            succEl.style.display = 'block';
        } 
        catch (err) 
        {
            errEl.textContent  = err.message;
            errEl.style.display = 'block';
            btn.disabled    = false;
            btn.textContent = 'Transmit Dispatch';
        }
    });
</script>