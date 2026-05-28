async function loadPage() {
    try {
        const [booking, methods] = await Promise.all([
            apiFetch(`${CONFIG.baseUrl}/api/bookings.php?action=detail&booking_id=${CONFIG.bookingId}`),
            apiFetch(`${CONFIG.baseUrl}/api/payments.php?action=methods`)
        ]);

        if (booking.booking_status !== 'Pending') {
            document.getElementById('loadingMsg').style.display = 'none';
            document.getElementById('errorMsg').textContent     =
                `This ledger listing is already marked "${booking.booking_status}". Action cancelled.`;
            document.getElementById('errorMsg').style.display   = 'block';
            return;
        }

        document.getElementById('summaryContent').innerHTML = `
            <div class="summary-row">
                <span class="text-muted summary-label">Accommodation:</span>
                <span class="summary-val-highlight">${booking.type_name} &mdash; Suite ${booking.room_number}</span>
            </div>
            <div class="summary-row">
                <span class="text-muted summary-label">Duration:</span>
                <span>${formatDate(booking.check_in_date)} to ${formatDate(booking.check_out_date)} (${booking.nights} nights)</span>
            </div>
            <div class="summary-row">
                <span class="text-muted summary-label">Occupancy:</span>
                <span>${booking.adults_count} Adult${booking.adults_count > 1 ? 's' : ''} ${booking.children_count > 0 ? ', ' + booking.children_count + ' Child' : ''}</span>
            </div>
            <div class="summary-total-row">
                <span class="summary-total-label">Total Billing:</span>
                <span class="summary-total-amount">${formatPHP(booking.total_amount)}</span>
            </div>
        `;

        const select = document.getElementById('paymentMethod');
        methods.forEach(m => {
            const option = document.createElement('option');
            option.value       = m.payment_method_id;
            option.textContent = m.method_name;
            select.appendChild(option);
        });

        document.getElementById('loadingMsg').style.display = 'none';
        document.getElementById('bookingDetails').style.display = 'block';

    } catch (err) {
        document.getElementById('loadingMsg').style.display = 'none';
        document.getElementById('errorMsg').textContent     = err.message;
        document.getElementById('errorMsg').style.display   = 'block';
    }
}

document.getElementById('payBtn')?.addEventListener('click', async function () {
    const btn       = this;
    const errEl     = document.getElementById('paymentError');
    errEl.style.display = 'none';

    const methodId = document.getElementById('paymentMethod').value;
    const ref      = document.getElementById('transactionRef').value.trim();

    if (!methodId || !ref) {
        errEl.textContent  = 'Please specify both verified channel selection and operational tracking keys.';
        errEl.style.display = 'block';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Processing validation...';

    try {
        const booking = await apiFetch(`${CONFIG.baseUrl}/api/bookings.php?action=detail&booking_id=${CONFIG.bookingId}`);

        await apiFetch(`${CONFIG.baseUrl}/api/payments.php?action=pay`, {
            method: 'POST',
            body: {
                booking_id:          CONFIG.bookingId,
                payment_method_id:   parseInt(methodId),
                amount_paid:         parseFloat(booking.total_amount),
                transaction_reference: ref,
            }
        });

        window.location.href = `${CONFIG.baseUrl}/confirmation.php?booking_id=${CONFIG.bookingId}`;

    } catch (err) {
        errEl.textContent  = err.message;
        errEl.style.display = 'block';
        btn.disabled    = false;
        btn.textContent = 'Authorize Remittance';
    }
});

loadPage();