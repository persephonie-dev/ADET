<?php

    $pageTitle = 'Manage Reviews';
    $activeNav = 'reviews';
    require_once __DIR__ . '/../includes/admin_header.php';
    ?>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>Guest Reviews</h3>
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab active" data-status="Pending">Pending</button>
                <button class="filter-tab" data-status="Approved">Approved</button>
                <button class="filter-tab" data-status="Rejected">Rejected</button>
            </div>
        </div>
        <div id="reviewsWrap">
            <p class="text-muted text-center" style="padding:2rem;">Loading...</p>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';
    let currentFilter = 'Pending';

    async function loadReviews(status) 
    {
        const wrap = document.getElementById('reviewsWrap');
        wrap.innerHTML = '<p class="text-muted text-center" style="padding:2rem;">Loading...</p>';
        try {
            const reviews = await apiFetch(`${BASE}/api/admin.php?action=reviews&status=${encodeURIComponent(status)}`);

            if (!reviews.length) {
                wrap.innerHTML = `<p class="text-muted text-center" style="padding:2rem;">No ${status.toLowerCase()} reviews.</p>`;
                return;
            }

          
            wrap.innerHTML = reviews.map(rv => `
                <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--cream-dark);">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div style="flex:1;min-width:200px;">
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem;">
                                <strong>${rv.guest_name}</strong>
                                <span class="text-muted">&mdash; ${rv.room_type}</span>
                                <span style="color:var(--gold);">${'&#9733;'.repeat(rv.rating)}${'&#9734;'.repeat(5 - rv.rating)}</span>
                            </div>
                            ${rv.title ? `<p style="font-weight:700;margin-bottom:0.3rem;">${rv.title}</p>` : ''}
                            <p style="font-size:0.9rem;color:var(--stone);margin-bottom:0.5rem;">"${rv.comment || ''}"</p>
                            <span class="text-muted" style="font-size:0.78rem;">${formatDate(rv.review_date)}</span>
                        </div>
                        ${status === 'Pending' ? `
                            <div class="table-actions" style="flex-shrink:0;">
                                <button class="btn btn-primary btn-sm"
                                        onclick="updateReview(${rv.review_id}, 'Approved', this)">
                                    Approve
                                </button>
                                <button class="btn btn-danger btn-sm"
                                        onclick="updateReview(${rv.review_id}, 'Rejected', this)">
                                    Reject
                                </button>
                            </div>
                        ` : `<span class="${rv.review_status === 'Approved' ? 'badge badge-confirmed' : 'badge badge-cancelled'}">${rv.review_status}</span>`}
                    </div>
                </div>
            `).join('');
        } catch (err) {
            wrap.innerHTML = `<p class="alert alert-error" style="margin:1rem;">${err.message}</p>`;
        }
    }

    async function updateReview(reviewId, status, btn) 
    {
        btn.disabled = true;
        try 
        {
            await apiFetch(`${BASE}/api/admin.php?action=update_review`, 
            {
                method: 'POST',
                body: { review_id: reviewId, status }
            });
            showToast(`Review ${status.toLowerCase()}.`, 'success');
            loadReviews(currentFilter);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false;
        }
    }

    document.getElementById('filterTabs').addEventListener('click', function (e) 
    {
        const tab = e.target.closest('.filter-tab');
        if (!tab) return;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentFilter = tab.dataset.status;
        loadReviews(currentFilter);
    });

    loadReviews(currentFilter);
</script>