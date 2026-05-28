<?php

    $pageTitle = 'Check-In / Check-Out';
    $activeNav = 'checkin';
    require_once __DIR__ . '/../includes/staff_header.php';
    ?>

    <!--navigation-->
    <div class="filter-tabs" id="ciTabs" style="margin-bottom:1.5rem;">
        <button class="filter-tab active" data-tab="arrivals">Today's Arrivals</button>
        <button class="filter-tab" data-tab="departures">Today's Departures</button>
        <button class="filter-tab" data-tab="search">Search Booking</button>
    </div>


    <div id="tab-arrivals" class="ci-tab">
        <div class="admin-table-wrap">
            <div class="admin-table-header">
                <h3>Arrivals — <?= date('F j, Y') ?></h3>
                <button class="btn btn-outline btn-sm" onclick="loadArrivals()">Refresh</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Guest</th><th>Email</th>
                            <th>Room</th><th>Check-Out</th><th>Adults</th>
                            <th>Special Requests</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="arrivalsBody">
                        <tr><td colspan="9" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div id="tab-departures" class="ci-tab" style="display:none;">
        <div class="admin-table-wrap">
            <div class="admin-table-header">
                <h3>Departures — <?= date('F j, Y') ?></h3>
                <button class="btn btn-outline btn-sm" onclick="loadDepartures()">Refresh</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Guest</th><th>Room</th>
                            <th>Total Amount</th><th>Payment</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="departuresBody">
                        <tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div id="tab-search" class="ci-tab" style="display:none;">
        <div class="admin-table-wrap">
            <div class="admin-table-header">
                <h3>Search Booking</h3>
            </div>
            <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:flex-end;">
                <div class="form-group" style="margin:0;flex:1;min-width:220px;">
                    <label for="searchQuery">Guest name, email, or booking #</label>
                    <input type="text" id="searchQuery" class="form-control"
                        placeholder="e.g. Juan Dela Cruz or #123">
                </div>
                <button class="btn btn-primary btn-sm" onclick="searchBookings()" style="height:40px;">Search</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Guest</th><th>Room</th>
                            <th>Check-In</th><th>Check-Out</th><th>Total</th>
                            <th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="searchBody">
                        <tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">Enter a search query above.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="modal-overlay" id="ciModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="ciModalTitle">Confirm Action</h3>
                <button class="modal-close" id="closeCiModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="ciModalMsg"></p>
                <div class="form-group" id="ciNoteWrap" style="display:none;margin-top:.75rem;">
                    <label for="ciNote">Staff Note (optional)</label>
                    <textarea id="ciNote" class="form-control" rows="2"></textarea>
                </div>
                <div id="ciModalError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelCiModal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="confirmCiAction">Confirm</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/staff_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';
    let pendingAction   = null;   

   
    document.getElementById('ciTabs').addEventListener('click', function (e) 
    {
        const tab = e.target.closest('.filter-tab');
        if (!tab) return;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.querySelectorAll('.ci-tab').forEach(t => t.style.display = 'none');
        document.getElementById(`tab-${tab.dataset.tab}`).style.display = 'block';
    });


    async function loadArrivals() 
    {
        const body = document.getElementById('arrivalsBody');
        body.innerHTML = '<tr><td colspan="9" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>';
        try {
            const rows = await apiFetch(`${BASE}/api/staff.php?action=arrivals`);
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="9" class="text-muted text-center" style="padding:1.5rem;">No arrivals today.</td></tr>';
                return;
            }
            body.innerHTML = rows.map(b => `
                <tr>
                    <td>#${b.booking_id}</td>
                    <td><strong>${b.guest_name}</strong></td>
                    <td><a href="mailto:${b.guest_email}">${b.guest_email}</a></td>
                    <td>${b.room_number} — ${b.room_type}</td>
                    <td>${formatDate(b.check_out_date)}</td>
                    <td>${b.adults_count}${b.children_count ? ' + ' + b.children_count + ' child' : ''}</td>
                    <td>${b.special_requests ? `<em>${b.special_requests}</em>` : '--'}</td>
                    <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                    <td>
                        ${b.booking_status === 'Confirmed'
                            ? `<button class="btn btn-primary btn-sm"
                                    onclick="openAction(${b.booking_id},'Checked In','Check in ${b.guest_name}?')">
                                Check In
                            </button>`
                            : b.booking_status === 'Checked In'
                                ? '<span class="badge badge-checkedin">Already In</span>'
                                : `<span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span>`
                        }
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            body.innerHTML = `<tr><td colspan="9"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

   
    async function loadDepartures() 
    {
        const body = document.getElementById('departuresBody');
        body.innerHTML = '<tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>';
        try {
            const rows = await apiFetch(`${BASE}/api/staff.php?action=departures`);
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="7" class="text-muted text-center" style="padding:1.5rem;">No departures today.</td></tr>';
                return;
            }
            body.innerHTML = rows.map(b => `
                <tr>
                    <td>#${b.booking_id}</td>
                    <td><strong>${b.guest_name}</strong></td>
                    <td>${b.room_number}</td>
                    <td>${formatPHP(b.total_amount)}</td>
                    <td>${b.payment_status || 'N/A'}</td>
                    <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                    <td>
                        ${b.booking_status === 'Checked In'
                            ? `<button class="btn btn-outline btn-sm"
                                    onclick="openAction(${b.booking_id},'Checked Out','Check out ${b.guest_name}?')">
                                Check Out
                            </button>`
                            : `<span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span>`
                        }
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            body.innerHTML = `<tr><td colspan="7"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

    async function searchBookings() 
    {
        const q    = document.getElementById('searchQuery').value.trim();
        const body = document.getElementById('searchBody');
        if (!q) return;
        body.innerHTML = '<tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">Searching…</td></tr>';
        try 
        {
            const rows = await apiFetch(`${BASE}/api/staff.php?action=search_bookings&q=${encodeURIComponent(q)}`);
            if (!rows.length) 
            {
                body.innerHTML = '<tr><td colspan="8" class="text-muted text-center" style="padding:1.5rem;">No results found.</td></tr>';
                return;
            }
            body.innerHTML = rows.map(b => `
                <tr>
                    <td>#${b.booking_id}</td>
                    <td>${b.guest_name}</td>
                    <td>${b.room_number} — ${b.room_type}</td>
                    <td>${formatDate(b.check_in_date)}</td>
                    <td>${formatDate(b.check_out_date)}</td>
                    <td>${formatPHP(b.total_amount)}</td>
                    <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                    <td style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        ${b.booking_status === 'Confirmed'
                            ? `<button class="btn btn-primary btn-sm"
                                    onclick="openAction(${b.booking_id},'Checked In','Check in ${b.guest_name}?')">Check In</button>` : ''}
                        ${b.booking_status === 'Checked In'
                            ? `<button class="btn btn-outline btn-sm"
                                    onclick="openAction(${b.booking_id},'Checked Out','Check out ${b.guest_name}?')">Check Out</button>` : ''}
                        ${b.booking_status === 'Pending'
                            ? `<button class="btn btn-outline btn-sm"
                                    onclick="openAction(${b.booking_id},'Confirmed','Confirm booking #${b.booking_id}?')">Confirm</button>` : ''}
                    </td>
                </tr>
            `).join('');
        } catch (err) 
        {
            body.innerHTML = `<tr><td colspan="8"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

    document.getElementById('searchQuery').addEventListener('keydown', e => 
    {
        if (e.key === 'Enter') searchBookings();
    });


    function openAction(bookingId, newStatus, message) 
    {
        pendingAction = { booking_id: bookingId, new_status: newStatus };
        document.getElementById('ciModalTitle').textContent     = newStatus;
        document.getElementById('ciModalMsg').textContent       = message;
        document.getElementById('ciModalError').style.display   = 'none';
        document.getElementById('ciModal').classList.add('open');
    }

    document.getElementById('closeCiModal').addEventListener('click',
        () => document.getElementById('ciModal').classList.remove('open'));
    document.getElementById('cancelCiModal').addEventListener('click',
        () => document.getElementById('ciModal').classList.remove('open'));

    document.getElementById('confirmCiAction').addEventListener('click', async function () 
    {
        const errEl = document.getElementById('ciModalError');
        errEl.style.display = 'none';
        try {
            await apiFetch(`${BASE}/api/staff.php?action=update_booking`, 
            {
                method: 'POST',
                body:   { booking_id: pendingAction.booking_id, status: pendingAction.new_status }
            });
            document.getElementById('ciModal').classList.remove('open');
            showToast(`Booking updated to "${pendingAction.new_status}".`, 'success');
            loadArrivals();
            loadDepartures();
        } catch (err) 
        {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        }
    });

    loadArrivals();
    loadDepartures();
</script>