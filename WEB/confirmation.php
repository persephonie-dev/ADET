<?php

    require_once __DIR__ . '/api/config.php';
    require_login();

    $bookingId = (int)($_GET['booking_id'] ?? 0);
    if (!$bookingId) {
        header('Location: ' . BASE_URL . '/profile.php');
        exit;
    }

    $pageTitle = 'Booking Confirmed';
    require_once __DIR__ . '/includes/header.php';
    ?>

    <div class="container section" style="max-width: 600px; text-align: center;">

        
        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--forest); display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 1px solid var(--gold);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--gold-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <span class="section-label">Complete</span>
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Stay Confirmed</h2>
        <p class="text-muted" style="margin-bottom: 3rem; font-size: 0.95rem; max-width: 480px; margin-left: auto; margin-right: auto;">
            Successfully Booked.
        </p>

        <div id="confirmDetails" style="text-align: left; margin-bottom: 3rem;">
            <p class="text-muted text-center" style="font-style: italic;">Loading details...</p>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="<?= BASE_URL ?>/profile.php" class="btn btn-dark">Manage Bookings</a>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline" style="color: var(--forest); border-color: var(--forest-mid);">Return Home</a>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
const BASE       = '<?= BASE_URL ?>';
const BOOKING_ID = <?= $bookingId ?>;

async function loadConfirmation() 
{
    try 
    {
        const b = await apiFetch(`${BASE}/api/bookings.php?action=detail&booking_id=${BOOKING_ID}`);
        const payment = b.payments && b.payments[0] ? b.payments[0] : null;

        document.getElementById('confirmDetails').innerHTML = `
            <div style="background: var(--white); padding: 2.5rem; border-radius: var(--radius); box-shadow: var(--shadow-sm); border-top: 1px solid var(--cream-dark);">
                <h3 style="margin-bottom: 1.5rem; font-family: var(--font-display); font-size: 1.4rem; color: var(--gold-dark);">Reservation Ledger Reference #${b.booking_id}</h3>
                
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.9rem; border-bottom:1px dashed var(--cream-dark); padding-bottom:0.75rem;">
                    <span class="text-muted">Allocation</span><span>${b.type_name} (Suite ${b.room_number})</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.9rem; border-bottom:1px dashed var(--cream-dark); padding-bottom:0.75rem;">
                    <span class="text-muted">Check-in</span><span>${formatDate(b.check_in_date)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.9rem; border-bottom:1px dashed var(--cream-dark); padding-bottom:0.75rem;">
                    <span class="text-muted">Check-out</span><span>${formatDate(b.check_out_date)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.9rem; border-bottom:1px dashed var(--cream-dark); padding-bottom:0.75rem;">
                    <span class="text-muted">Registered Unit Stay</span><span>${b.nights} Nights</span>
                </div>
                ${payment ? `
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.9rem; border-bottom:1px dashed var(--cream-dark); padding-bottom:0.75rem;">
                    <span class="text-muted">Channel Protocol</span><span>${payment.method_name}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; font-size:0.9rem; border-bottom:1px dashed var(--cream-dark); padding-bottom:0.75rem;">
                    <span class="text-muted">Internal ID Key</span><span style="font-family:monospace; font-size:0.85rem;">${payment.transaction_reference}</span>
                </div>
                ` : ''}
                <div style="display:flex; justify-content:space-between; margin-top: 1.5rem;">
                    <span style="font-family:var(--font-display); font-size:1.2rem; font-weight:500;">Total Documented</span>
                    <span style="font-family:var(--font-display); font-size:1.4rem; font-weight:600; color:var(--forest);">${formatPHP(b.total_amount)}</span>
                </div>
            </div>
        `;
    } 
    catch (err) 
    {
        document.getElementById('confirmDetails').innerHTML =
            `<p class="text-muted text-center" style="color:var(--danger);">${err.message}</p>`;
    }
}

loadConfirmation();
</script>