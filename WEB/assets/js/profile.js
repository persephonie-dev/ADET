async function loadBookings() {
    const wrap = document.getElementById('bookingsWrap');
    try {
        const bookings = await apiFetch(`${CONFIG.baseUrl}/api/bookings.php?action=my`);

        if (!bookings.length) {
            wrap.innerHTML = `
                <div class="profile-empty-state">
                    <p class="text-muted">No historical records tracked under this identity card context.</p>
                    <a href="${CONFIG.baseUrl}/rooms.php" class="btn btn-dark">Browse Premium Portfolio</a>
                </div>
            `;
            return;
        }

        wrap.innerHTML = `
            <div class="table-responsive">
                <table class="profile-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Suite Variant</th>
                            <th>Arrival</th>
                            <th>Departure</th>
                            <th>Nights</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Ledger Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bookings.map(b => `
                            <tr>
                                <td class="booking-id-cell">#${b.booking_id}</td>
                                <td><strong class="room-type-title">${b.room_type}</strong><br><span class="text-muted room-number-sub">Unit No. ${b.room_number}</span></td>
                                <td>${formatDate(b.check_in_date)}</td>
                                <td>${formatDate(b.check_out_date)}</td>
                                <td>${b.nights}</td>
                                <td class="total-amount-cell">${formatPHP(b.total_amount)}</td>
                                <td><span class="status-badge">${b.booking_status}</span></td>
                                <td>
                                    <div class="actions-flex">
                                        ${!b.payment_status && b.booking_status === 'Pending'
                                            ? `<a href="${CONFIG.baseUrl}/payment.php?booking_id=${b.booking_id}" class="btn btn-gold action-btn-sm">Settle Invoice</a>`
                                            : b.payment_status ? `<span class="status-paid">Paid</span>` : '<span class="text-muted">&mdash;</span>'
                                        }
                                        ${['Pending', 'Confirmed'].includes(b.booking_status)
                                            ? `<button class="btn btn-outline action-btn-void" onclick="cancelBooking(${b.booking_id}, this)">Void</button>`
                                            : ''}
                                        ${b.booking_status === 'Checked Out'
                                            ? `<button class="btn btn-dark action-btn-sm" onclick="openReviewModal(${b.booking_id})">Write Review</button>`
                                            : ''}
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (err) {
        wrap.innerHTML = `<p class="text-muted text-center" style="color:var(--danger);">${err.message}</p>`;
    }
}

async function cancelBooking(bookingId, btn) {
    if (!confirm('Are you absolutely certain about voiding reservation sequence #' + bookingId + '?')) return;
    btn.disabled = true;
    try {
        await apiFetch(`${CONFIG.baseUrl}/api/bookings.php?action=cancel&booking_id=${bookingId}`, {
            method: 'POST'
        });
        showToast('Reservation sequential parameters safely discarded.', 'success');
        loadBookings();
    } catch (err) {
        showToast(err.message, 'error');
        btn.disabled = false;
    }
}

let selectedRating = 0;
document.getElementById('starRating')?.addEventListener('click', function (e) {
    const star = e.target.closest('.star');
    if (!star) return;
    selectedRating = parseInt(star.dataset.value);
    document.getElementById('ratingValue').value = selectedRating;
    document.querySelectorAll('.star').forEach((s, i) => {
        s.style.color = i < selectedRating ? 'var(--gold)' : 'var(--cream-dark)';
    });
});

function openReviewModal(bookingId) {
    selectedRating = 0;
    document.getElementById('reviewBookingId').value = bookingId;
    document.getElementById('ratingValue').value    = 0;
    document.getElementById('reviewTitle').value    = '';
    document.getElementById('reviewComment').value  = '';
    document.getElementById('reviewError').style.display = 'none';
    document.querySelectorAll('.star').forEach(s => s.style.color = 'var(--cream-dark)');
    document.getElementById('reviewModal').style.display = 'flex';
}

document.getElementById('closeReviewModal')?.addEventListener('click',  () => document.getElementById('reviewModal').style.display = 'none');
document.getElementById('cancelReview')?.addEventListener('click',      () => document.getElementById('reviewModal').style.display = 'none');

document.getElementById('submitReview')?.addEventListener('click', async function () {
    const errEl     = document.getElementById('reviewError');
    errEl.style.display = 'none';
    const bookingId = parseInt(document.getElementById('reviewBookingId').value);
    const rating    = parseInt(document.getElementById('ratingValue').value);
    const title     = document.getElementById('reviewTitle').value.trim();
    const comment   = document.getElementById('reviewComment').value.trim();

    if (!rating) {
        errEl.textContent  = 'Kindly click to apply an evaluation metric.';
        errEl.style.display = 'block';
        return;
    }

    try {
        await apiFetch(`${CONFIG.baseUrl}/api/reviews.php?action=submit`, {
            method: 'POST',
            body: { booking_id: bookingId, rating, title, comment }
        });
        document.getElementById('reviewModal').style.display = 'none';
        showToast('Your perspective has been compiled and saved.', 'success');
    } catch (err) {
        errEl.textContent  = err.message;
        errEl.style.display = 'block';
    }
});

// Expose handlers globally to support dynamic HTML element target definitions securely
window.cancelBooking = cancelBooking;
window.openReviewModal = openReviewModal;

loadBookings();