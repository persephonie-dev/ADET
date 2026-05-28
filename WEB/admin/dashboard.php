<?php


    $pageTitle = 'Dashboard';
    $activeNav = 'dashboard';
    require_once __DIR__ . '/../includes/admin_header.php';

    ?>


    <div class="stat-grid" id="statGrid">
     
        <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="stat-card">
                <div class="skeleton" style="height:2rem;width:60%;margin-bottom:0.5rem;border-radius:4px;"></div>
                <div class="skeleton" style="height:0.8rem;width:80%;border-radius:4px;"></div>
            </div>
        <?php endfor; ?>
    </div>


    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>Recent Bookings</h3>
            <a href="bookings.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div id="recentBookingsWrap">
            <table class="admin-table" id="recentBookingsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="recentBookingsTbody">
                    <tr>
                        <td colspan="7" class="text-muted text-center" style="padding:2rem;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>

    async function loadDashboard() {
        try {
            const data = await apiFetch('<?= BASE_URL ?>/api/admin.php?action=dashboard');

       
            document.getElementById('statGrid').innerHTML = `
                <div class="stat-card">
                    <div class="stat-value">${data.bookings_today}</div>
                    <div class="stat-label">Bookings Today</div>
                </div>
                <div class="stat-card accent-green">
                    <div class="stat-value">${formatPHP(data.revenue_this_month)}</div>
                    <div class="stat-label">Revenue This Month</div>
                </div>
                <div class="stat-card accent-blue">
                    <div class="stat-value">${data.currently_checked_in}</div>
                    <div class="stat-label">Checked In Now</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${data.available_rooms}</div>
                    <div class="stat-label">Available Rooms</div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-value">${data.pending_reviews}</div>
                    <div class="stat-label">Pending Reviews</div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-value">${data.new_messages}</div>
                    <div class="stat-label">New Messages</div>
                </div>
            `;

  
            const tbody = document.getElementById('recentBookingsTbody');
            if (!data.recent_bookings.length) 
            {
                tbody.innerHTML = '<tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">No bookings yet.</td></tr>';
                return;
            }
            tbody.innerHTML = data.recent_bookings.map(b => `
                <tr>
                    <td>#${b.booking_id}</td>
                    <td>${b.guest_name}</td>
                    <td>${b.room_type} &mdash; ${b.room_number}</td>
                    <td>${formatDate(b.check_in_date)}</td>
                    <td>${formatDate(b.check_out_date)}</td>
                    <td>${formatPHP(b.total_amount)}</td>
                    <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                </tr>
            `).join('');

        } catch (err) 
        {
            document.getElementById('statGrid').innerHTML =
                `<p class="alert alert-error" style="grid-column:1/-1;">${err.message}</p>`;
        }
    }

    loadDashboard();
</script>