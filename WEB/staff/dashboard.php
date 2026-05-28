<?php

    $pageTitle = 'Staff Dashboard';
    $activeNav = 'dashboard';
    require_once __DIR__ . '/../includes/staff_header.php';
    ?>


    <div class="stat-grid" id="statGrid">
        <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="stat-card">
                <div class="skeleton" style="height:2rem;width:60%;margin-bottom:.5rem;border-radius:4px;"></div>
                <div class="skeleton" style="height:.8rem;width:80%;border-radius:4px;"></div>
            </div>
        <?php endfor; ?>
    </div>


    <div class="admin-table-wrap" style="margin-top:1.5rem;">
        <div class="admin-table-header">
            <h3>Today's Arrivals
                <span id="arrivalsCount" class="sidebar-badge" style="margin-left:.5rem;"></span>
            </h3>
            <a href="checkin.php" class="btn btn-primary btn-sm">Check-In Guest</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th><th>Guest</th><th>Room</th>
                        <th>Check-In</th><th>Check-Out</th><th>Adults</th>
                        <th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody id="arrivalsBody">
                    <tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

   
    <div class="admin-table-wrap" style="margin-top:1.5rem;">
        <div class="admin-table-header">
            <h3>Today's Departures
                <span id="departuresCount" class="sidebar-badge" style="margin-left:.5rem;"></span>
            </h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th><th>Guest</th><th>Room</th>
                        <th>Check-Out Due</th><th>Total</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody id="departuresBody">
                    <tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>


    <div class="admin-table-wrap" style="margin-top:1.5rem;">
        <div class="admin-table-header">
            <h3>Room Status Overview</h3>
            <a href="housekeeping.php" class="btn btn-outline btn-sm">Manage Housekeeping</a>
        </div>
        <div id="roomSummaryGrid" class="stat-grid" style="margin:1rem 0 0;">
         
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/staff_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';

    async function loadDashboard() 
    {
        try 
        {
            const data = await apiFetch(`${BASE}/api/staff.php?action=dashboard`);

          
            document.getElementById('statGrid').innerHTML = `
                <div class="stat-card accent-blue">
                    <div class="stat-value">${data.today_arrivals}</div>
                    <div class="stat-label">Arrivals Today</div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-value">${data.today_departures}</div>
                    <div class="stat-label">Departures Today</div>
                </div>
                <div class="stat-card accent-green">
                    <div class="stat-value">${data.currently_checked_in}</div>
                    <div class="stat-label">Guests Checked In</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${data.available_rooms}</div>
                    <div class="stat-label">Available Rooms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${data.new_messages}</div>
                    <div class="stat-label">New Messages</div>
                </div>
            `;

            
            const arrivalsBody = document.getElementById('arrivalsBody');
            document.getElementById('arrivalsCount').textContent = data.today_arrivals || '';
            if (!data.arrivals.length) {
                arrivalsBody.innerHTML = '<tr><td colspan="8" class="text-muted text-center" style="padding:1.5rem;">No arrivals scheduled today.</td></tr>';
            } else {
                arrivalsBody.innerHTML = data.arrivals.map(b => `
                    <tr>
                        <td>#${b.booking_id}</td>
                        <td>${b.guest_name}</td>
                        <td><strong>${b.room_number}</strong> — ${b.room_type}</td>
                        <td>${formatDate(b.check_in_date)}</td>
                        <td>${formatDate(b.check_out_date)}</td>
                        <td>${b.adults_count}</td>
                        <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="quickCheckin(${b.booking_id}, '${b.guest_name}')">
                                Check In
                            </button>
                        </td>
                    </tr>
                `).join('');
            }

           
            const departuresBody = document.getElementById('departuresBody');
            document.getElementById('departuresCount').textContent = data.today_departures || '';
            if (!data.departures.length) {
                departuresBody.innerHTML = '<tr><td colspan="7" class="text-muted text-center" style="padding:1.5rem;">No departures due today.</td></tr>';
            } else {
                departuresBody.innerHTML = data.departures.map(b => `
                    <tr>
                        <td>#${b.booking_id}</td>
                        <td>${b.guest_name}</td>
                        <td><strong>${b.room_number}</strong></td>
                        <td>${formatDate(b.check_out_date)}</td>
                        <td>${formatPHP(b.total_amount)}</td>
                        <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                        <td>
                            <button class="btn btn-outline btn-sm"
                                    onclick="quickCheckout(${b.booking_id}, '${b.guest_name}')">
                                Check Out
                            </button>
                        </td>
                    </tr>
                `).join('');
            }

     
            document.getElementById('roomSummaryGrid').innerHTML = `
                <div class="stat-card accent-green">
                    <div class="stat-value">${data.rooms_available}</div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-card accent-blue">
                    <div class="stat-value">${data.rooms_occupied}</div>
                    <div class="stat-label">Occupied</div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-value">${data.rooms_maintenance}</div>
                    <div class="stat-label">Maintenance</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${data.rooms_reserved}</div>
                    <div class="stat-label">Reserved</div>
                </div>
            `;

        } catch (err) 
        {
            document.getElementById('statGrid').innerHTML =
                `<p class="alert alert-error" style="grid-column:1/-1;">${err.message}</p>`;
        }
    }

    async function quickCheckin(bookingId, name)
    {
        if (!confirm(`Check in ${name}?`)) return;
        try 
        {
            await apiFetch(`${BASE}/api/staff.php?action=update_booking`, 
            {
                method: 'POST',
                body: { booking_id: bookingId, status: 'Checked In' }
            });
            showToast(`${name} checked in.`, 'success');
            loadDashboard();
        } catch (err) 
        { showToast(err.message, 'error'); 

        }
    }

    async function quickCheckout(bookingId, name) 
    {
        if (!confirm(`Check out ${name}?`)) return;
        try {
            await apiFetch(`${BASE}/api/staff.php?action=update_booking`, 
            {
                method: 'POST',
                body: { booking_id: bookingId, status: 'Checked Out' }
            });
            showToast(`${name} checked out.`, 'success');
            loadDashboard();
        } catch (err) { showToast(err.message, 'error'); }
    }

    loadDashboard();
</script>