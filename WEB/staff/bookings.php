<?php


    $pageTitle = 'Bookings';
    $activeNav = 'bookings';
    require_once __DIR__ . '/../includes/staff_header.php';
    ?>


    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>All Bookings <span id="bookingCount" class="sidebar-badge" style="margin-left:.5rem;"></span></h3>
        </div>

        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center;">
        
            <div id="statusTabs" style="display:flex;gap:.4rem;flex-wrap:wrap;">
                <?php
                $tabs = ['', 'Pending', 'Confirmed', 'Checked In', 'Checked Out', 'Cancelled', 'No Show'];
                $labels = ['All', 'Pending', 'Confirmed', 'Checked In', 'Checked Out', 'Cancelled', 'No Show'];
                foreach ($tabs as $i => $tab):
                ?>
                    <button class="filter-tab <?= $i === 0 ? 'active' : '' ?>"
                            data-status="<?= htmlspecialchars($tab) ?>">
                        <?= $labels[$i] ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <input type="text" id="bookingSearch" class="form-control"
                placeholder="Search guest, email, room…"
                style="max-width:240px;margin-left:auto;">
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Nights</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="bookingsTbody">
                    <tr><td colspan="10" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="requestsModal">
        <div class="modal" style="max-width:480px;">
            <div class="modal-header">
                <h3>Special Requests</h3>
                <button class="modal-close" id="closeRequestsModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:.5rem;font-size:.8rem;color:var(--stone);">
                    Booking <strong id="reqBookingId"></strong> — <span id="reqGuestName"></span>
                </p>
                <p id="reqText" style="line-height:1.7;white-space:pre-wrap;"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelRequestsModal">Close</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="actionModal">
        <div class="modal" style="max-width:420px;">
            <div class="modal-header">
                <h3 id="actionModalTitle">Confirm Action</h3>
                <button class="modal-close" id="closeActionModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="actionModalMsg"></p>
                <div id="actionModalError" class="alert alert-error" style="display:none;margin-top:.75rem;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelAction">Cancel</button>
                <button class="btn btn-primary btn-sm" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/staff_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';

    let allBookings     = [];
    let activeStatus    = '';
    let pendingAction   = null;


    async function loadBookings() 
    {
        const search = document.getElementById('bookingSearch').value.trim();
        const params = new URLSearchParams({ action: 'bookings' });
        if (activeStatus) params.set('status', activeStatus);
        if (search)       params.set('search', search);

        try 
        {
            allBookings = await apiFetch(`${BASE}/api/staff.php?${params}`);
            renderBookings();
        } catch (err) {
            document.getElementById('bookingsTbody').innerHTML =
                `<tr><td colspan="10"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

    function renderBookings() 
    {
        document.getElementById('bookingCount').textContent =
            allBookings.length ? `${allBookings.length}` : '';

        const tbody = document.getElementById('bookingsTbody');
        if (!allBookings.length) 
        {
            tbody.innerHTML = '<tr><td colspan="10" class="text-muted text-center" style="padding:2rem;">No bookings found.</td></tr>';
            return;
        }

        tbody.innerHTML = allBookings.map(b => 
        {
            const nights = nightsBetween(b.check_in_date, b.check_out_date);
            const actions = buildActions(b);
            const hasRequests = b.special_requests && b.special_requests.trim() &&
                                b.special_requests.trim() !== 'No special requests.';

            return `<tr>
                <td>#${b.booking_id}</td>
                <td>
                    <strong>${b.guest_name || '—'}</strong><br>
                    <span class="text-muted" style="font-size:.78rem;">${b.guest_email || ''}</span>
                    ${b.guest_phone ? `<br><span class="text-muted" style="font-size:.78rem;">${b.guest_phone}</span>` : ''}
                </td>
                <td>
                    <strong>${b.room_number}</strong><br>
                    <span class="text-muted" style="font-size:.78rem;">${b.room_type}</span>
                </td>
                <td>${formatDate(b.check_in_date)}</td>
                <td>${formatDate(b.check_out_date)}</td>
                <td>${nights}</td>
                <td>${b.adults_count}${b.children_count > 0 ? ` + ${b.children_count}ch` : ''}</td>
                <td>${formatPHP(b.total_amount)}</td>
                <td>
                    <span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span>
                    ${hasRequests ? `<br><button class="btn btn-sm" style="margin-top:.3rem;font-size:.72rem;padding:.15rem .5rem;background:var(--cream);"
                        onclick="openRequests(${b.booking_id},'${escJ(b.guest_name)}','${escJ(b.special_requests)}')">
                        📋 Requests</button>` : ''}
                </td>
                <td style="display:flex;gap:.35rem;flex-wrap:wrap;">${actions}</td>
            </tr>`;
        }).join('');
    }

    
    function buildActions(b) 
    {
        const id   = b.booking_id;
        const name = escJ(b.guest_name || 'Guest');
        if (b.booking_status === 'Confirmed') 
        {
            return `<button class="btn btn-primary btn-sm"
                            onclick="openAction(${id},'Checked In','${name}')">Check In</button>`;
        }
        if (b.booking_status === 'Checked In') 
        {
            return `<button class="btn btn-outline btn-sm"
                            onclick="openAction(${id},'Checked Out','${name}')">Check Out</button>`;
        }
        return `<span class="text-muted" style="font-size:.8rem;">—</span>`;
    }

   
    function nightsBetween(cin, cout) 
    {
        const ms = new Date(cout) - new Date(cin);
        return Math.round(ms / 86400000);
    }

    function escJ(str) 
    {
        return (str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    document.getElementById('statusTabs').addEventListener('click', function(e) 
    {
        const tab = e.target.closest('.filter-tab');
        if (!tab) return;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        activeStatus = tab.dataset.status;
        loadBookings();
    });

  
    let searchTimer;
    document.getElementById('bookingSearch').addEventListener('input', function() 
    {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadBookings, 300);
    });

  
    function openRequests(id, guest, text) {
        document.getElementById('reqBookingId').textContent  = `#${id}`;
        document.getElementById('reqGuestName').textContent  = guest;
        document.getElementById('reqText').textContent       = text;
        document.getElementById('requestsModal').classList.add('open');
    }
    ['closeRequestsModal','cancelRequestsModal'].forEach(id =>
        document.getElementById(id).addEventListener('click',
            () => document.getElementById('requestsModal').classList.remove('open')));

    function openAction(bookingId, status, guestName) 
    {
        pendingAction = { bookingId, status, guestName };
        const verb = status === 'Checked In' ? 'Check in' : 'Check out';
        document.getElementById('actionModalTitle').textContent = `${verb} guest`;
        document.getElementById('actionModalMsg').textContent   =
            `${verb} ${guestName} (Booking #${bookingId})?`;
        document.getElementById('actionModalError').style.display = 'none';
        document.getElementById('actionModal').classList.add('open');
    }
    ['closeActionModal','cancelAction'].forEach(id =>
        document.getElementById(id).addEventListener('click',
            () => document.getElementById('actionModal').classList.remove('open')));

    document.getElementById('confirmAction').addEventListener('click', async function() 
    {
        if (!pendingAction) return;
        const errEl  = document.getElementById('actionModalError');
        errEl.style.display = 'none';
        this.disabled = true;
        try 
        {
            await apiFetch(`${BASE}/api/staff.php?action=update_booking`, {
                method: 'POST',
                body: { booking_id: pendingAction.bookingId, status: pendingAction.status }
            });
            document.getElementById('actionModal').classList.remove('open');
            showToast(`${pendingAction.guestName} — ${pendingAction.status}.`, 'success');
            loadBookings();
        } catch (err) 
        {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        } finally 
        {
            this.disabled = false;
        }
    });

    loadBookings();
</script>

<style>
    .filter-tab 
    {
        background: none;
        border: 1px solid var(--cream-dark);
        border-radius: 99px;
        padding: .25rem .75rem;
        font-size: .8rem;
        cursor: pointer;
        color: var(--stone);
        transition: background .15s, color .15s;
    }
    .filter-tab:hover  { background: var(--cream); }
    .filter-tab.active { background: var(--charcoal); color: var(--white); border-color: var(--charcoal); }
</style>