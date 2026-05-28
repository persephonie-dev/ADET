<?php

    $pageTitle = 'Manage Bookings';
    $activeNav = 'bookings';
    require_once __DIR__ . '/../includes/admin_header.php';

?>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>All Bookings</h3>
        
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab active" data-status="">All</button>
                <button class="filter-tab" data-status="Pending">Pending</button>
                <button class="filter-tab" data-status="Confirmed">Confirmed</button>
                <button class="filter-tab" data-status="Checked In">Checked In</button>
                <button class="filter-tab" data-status="Checked Out">Checked Out</button>
                <button class="filter-tab" data-status="Cancelled">Cancelled</button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Email</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total</th>
                        <th>Booked</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTbody">
                    <tr><td colspan="10" class="text-muted text-center" style="padding:2rem;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="statusModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Update Booking Status</h3>
                <button class="modal-close" id="closeStatusModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:1rem;">
                    Booking <strong id="modalBookingId"></strong> &mdash; <span id="modalGuestName"></span>
                </p>
                <div class="form-group">
                    <label for="newStatus">New Status</label>
                    <select id="newStatus" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Checked In">Checked In</option>
                        <option value="Checked Out">Checked Out</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="No Show">No Show</option>
                    </select>
                </div>
                <div id="statusModalError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelStatusModal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="confirmStatusUpdate">Update Status</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>

    const BASE = '<?= BASE_URL ?>';

    let currentBookingId   = null;
    let currentStatusFilter = '';


    async function loadBookings(statusFilter = '') 
    {
        const tbody = document.getElementById('bookingsTbody');
        tbody.innerHTML = '<tr><td colspan="10" class="text-muted text-center" style="padding:2rem;">Loading...</td></tr>';
        
        try 
        {
            const url  = `${BASE}/api/admin.php?action=bookings${statusFilter ? '&status=' + encodeURIComponent(statusFilter) : ''}`;
            
            const rows = await apiFetch(url);
            console.log("Sample Booking Object:", rows[0]);
            if (!rows.length) 
            {
                tbody.innerHTML = '<tr><td colspan="10" class="text-muted text-center" style="padding:2rem;">No bookings found.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(b => `
                <tr>
                    <td>#${b.booking_id}</td>
                    <td>${b.guest_name}</td>
                    <td><a href="mailto:${b.guest_email}">${b.guest_email}</a></td>
                    <td>${b.room_type} &mdash; ${b.room_number}</td>
                    <td>${formatDate(b.check_in_date)}</td>
                    <td>${formatDate(b.check_out_date)}</td>
                    <td>${formatPHP(b.total_amount)}</td>
                    
                    <td>${b.booking_date ? formatDate(b.booking_date.split(' ')[0]) : 'N/A'}</td>
                    <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-outline btn-sm"
                                    onclick="openStatusModal(${b.booking_id}, '${b.guest_name}', '${b.booking_status}')">
                                Change Status
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

        } catch (err) 
        {
            tbody.innerHTML = `<tr><td colspan="10"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }


    document.getElementById('filterTabs').addEventListener('click', function (e) 
    {
        const tab = e.target.closest('.filter-tab');
        if (!tab) return;

        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentStatusFilter = tab.dataset.status;
        loadBookings(currentStatusFilter);
    });


    function openStatusModal(bookingId, guestName, currentStatus) 
    {
        currentBookingId = bookingId;
        document.getElementById('modalBookingId').textContent = '#' + bookingId;
        document.getElementById('modalGuestName').textContent = guestName;
        document.getElementById('newStatus').value = currentStatus;
        document.getElementById('statusModalError').style.display = 'none';
        document.getElementById('statusModal').classList.add('open');
    }

    document.getElementById('closeStatusModal').addEventListener('click',  () => document.getElementById('statusModal').classList.remove('open'));
    document.getElementById('cancelStatusModal').addEventListener('click', () => document.getElementById('statusModal').classList.remove('open'));

    document.getElementById('confirmStatusUpdate').addEventListener('click', async function () {
        const newStatus = document.getElementById('newStatus').value;
        const errEl     = document.getElementById('statusModalError');
        errEl.style.display = 'none';

        try 
        {
            await apiFetch(`${BASE}/api/admin.php?action=update_booking`, 
            {
                method: 'POST',
                body:   { booking_id: currentBookingId, status: newStatus }
            });
            document.getElementById('statusModal').classList.remove('open');
            showToast('Booking status updated.', 'success');
            loadBookings(currentStatusFilter);
        } catch (err) 
        {
            errEl.textContent  = err.message;
            errEl.style.display = 'block';
        }
    });

    loadBookings();
</script>