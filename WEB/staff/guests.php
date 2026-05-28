<?php


    $pageTitle = 'Guest Lookup';
    $activeNav = 'guests';
    require_once __DIR__ . '/../includes/staff_header.php';
    ?>

    
    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>Guest Lookup</h3>
        </div>

        <div style="padding:1rem 1.25rem;border-bottom:1px solid #eee;">
            <div style="display:flex;gap:.75rem;max-width:520px;">
                <input  id="searchInput"
                        type="text"
                        class="form-control"
                        placeholder="Search by name, email or phone…"
                        autocomplete="off">
                <button id="searchBtn" class="btn btn-primary">Search</button>
            </div>
            <p style="color:#888;font-size:.8rem;margin-top:.4rem;">Minimum 2 characters.</p>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="guestBody">
                    <tr>
                        <td colspan="8" class="text-muted text-center" style="padding:2rem;">
                            Enter a name, email, or phone number above to find a guest.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- guest pannel-->
    <div id="historyWrap" class="admin-table-wrap" style="margin-top:1.5rem;display:none;">
        <div class="admin-table-header">
            <h3>Booking History — <span id="historyGuestName"></span></h3>
            <button class="btn btn-outline btn-sm" onclick="closeHistory()">Close</button>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Adults</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <tr><td colspan="7" class="text-muted text-center" style="padding:1.5rem;">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/staff_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';

    async function searchGuests() 
    {
        const q = document.getElementById('searchInput').value.trim();
        if (q.length < 2) {
            showToast('Please enter at least 2 characters.', 'error');
            return;
        }

        document.getElementById('guestBody').innerHTML =
            `<tr><td colspan="8" class="text-muted text-center" style="padding:1.5rem;">Searching…</td></tr>`;

        try 
        {
            const guests = await apiFetch(`${BASE}/api/staff.php?action=guests&search=${encodeURIComponent(q)}`);

            if (!guests.length) 
            {
                document.getElementById('guestBody').innerHTML =
                    `<tr><td colspan="8" class="text-muted text-center" style="padding:1.5rem;">No guests found for "${escHtml(q)}".</td></tr>`;
                return;
            }

            document.getElementById('guestBody').innerHTML = guests.map(g => `
                <tr>
                    <td>${g.user_id}</td>
                    <td><strong>${escHtml(g.full_name)}</strong></td>
                    <td>${escHtml(g.email)}</td>
                    <td>${escHtml(g.phone_number || '—')}</td>
                    <td>${escHtml(g.city || '—')}</td>
                    <td>${g.total_bookings}</td>
                    <td><span class="${g.user_status === 'Active' ? 'badge badge-success' : 'badge badge-secondary'}">${g.user_status}</span></td>
                    <td>
                        <button class="btn btn-outline btn-sm"
                                onclick="loadHistory(${g.user_id}, '${escHtml(g.full_name)}')">
                            History
                        </button>
                    </td>
                </tr>
            `).join('');

        } catch (err) 
        {
            document.getElementById('guestBody').innerHTML =
                `<tr><td colspan="8" class="alert alert-error">${err.message}</td></tr>`;
        }
    }

    async function loadHistory(userId, name)
    {
        document.getElementById('historyGuestName').textContent = name;
        document.getElementById('historyWrap').style.display    = '';
        document.getElementById('historyBody').innerHTML =
            `<tr><td colspan="7" class="text-muted text-center" style="padding:1.5rem;">Loading…</td></tr>`;

     
        document.getElementById('historyWrap').scrollIntoView({ behavior: 'smooth' });

        try
        {
            
            const bookings = await apiFetch(`${BASE}/api/staff.php?action=bookings&search=${encodeURIComponent(name)}`);

          
            const mine = bookings.filter(b => {
              
                return true;
            });

            if (!mine.length) 
            {
                document.getElementById('historyBody').innerHTML =
                    `<tr><td colspan="7" class="text-muted text-center" style="padding:1.5rem;">No bookings found for this guest.</td></tr>`;
                return;
            }

            document.getElementById('historyBody').innerHTML = mine.map(b => `
                <tr>
                    <td>#${b.booking_id}</td>
                    <td><strong>${escHtml(b.room_number)}</strong> · ${escHtml(b.room_type)}</td>
                    <td>${formatDate(b.check_in_date)}</td>
                    <td>${formatDate(b.check_out_date)}</td>
                    <td>${b.adults_count}</td>
                    <td>${formatPHP(b.total_amount)}</td>
                    <td><span class="${statusBadgeClass(b.booking_status)}">${b.booking_status}</span></td>
                </tr>
            `).join('');

        } catch (err)
        {
            document.getElementById('historyBody').innerHTML =
                `<tr><td colspan="7" class="alert alert-error">${err.message}</td></tr>`;
        }
    }

    function closeHistory() 
    {
        document.getElementById('historyWrap').style.display = 'none';
    }


    function escHtml(str) 
    {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function formatDate(d) 
    {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
    }


    document.getElementById('searchBtn').addEventListener('click', searchGuests);
    document.getElementById('searchInput').addEventListener('keydown', e => 
    {
        if (e.key === 'Enter') searchGuests();
    });
</script>