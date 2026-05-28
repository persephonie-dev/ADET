<?php

    $pageTitle = 'Promotions';
    $activeNav = 'promotions';
    require_once __DIR__ . '/../includes/admin_header.php';
    ?>

    <div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
        <button class="btn btn-primary btn-sm" id="newPromoBtn">+ New Promotion</button>
    </div>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>All Promotions</h3>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Discount</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="promosTbody">
                    <tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>


    <div class="modal-overlay" id="promoModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="promoModalTitle">New Promotion</h3>
                <button class="modal-close" id="closePromoModal">&times;</button>
            </div>
            <div class="modal-body">
            
                <input type="hidden" id="promoId" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label for="promoCode">Promo Code</label>
                        <input type="text" id="promoCode" class="form-control" placeholder="SUMMER20">
                    </div>
                    <div class="form-group">
                        <label for="promoName">Promo Name</label>
                        <input type="text" id="promoName" class="form-control" placeholder="Summer Special">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="discountType">Discount Type</label>
                        <select id="discountType" class="form-control">
                            <option value="Percentage">Percentage (%)</option>
                            <option value="Fixed">Fixed Amount (PHP)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discountValue">Discount Value</label>
                        <input type="number" id="discountValue" class="form-control" min="0" step="0.01" placeholder="20">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" id="startDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input type="date" id="endDate" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="isActive" checked style="margin-right:0.4rem;">
                        Active (guests can use this code immediately)
                    </label>
                </div>
                <div id="promoModalError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelPromoModal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="savePromo">Save Promotion</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';

    async function loadPromos() 
    {
        try {
            const promos = await apiFetch(`${BASE}/api/admin.php?action=promos`);
            const tbody  = document.getElementById('promosTbody');
            if (!promos.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">No promotions yet.</td></tr>';
                return;
            }
            tbody.innerHTML = promos.map(p => `
                <tr>
                    <td><strong>${p.promo_code}</strong></td>
                    <td>${p.promo_name}</td>
                    <td>${p.discount_type === 'Percentage' ? p.discount_value + '%' : formatPHP(p.discount_value) + ' off'}</td>
                    <td>${formatDate(p.start_date)}</td>
                    <td>${formatDate(p.end_date)}</td>
                    <td>
                        <span class="${p.is_active ? 'badge badge-confirmed' : 'badge badge-cancelled'}">
                            ${p.is_active ? 'Yes' : 'No'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick="editPromo(${p.promotion_id})">Edit</button>
                    </td>
                </tr>
            `).join('');
        
            window._promos = promos;
        } catch (err) {
            document.getElementById('promosTbody').innerHTML =
                `<tr><td colspan="7"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

    function openModal(title) 
    {
        document.getElementById('promoModalTitle').textContent = title;
        document.getElementById('promoModalError').style.display = 'none';
        document.getElementById('promoModal').classList.add('open');
    }

  
    document.getElementById('newPromoBtn').addEventListener('click', function () {
        document.getElementById('promoId').value       = '0';
        document.getElementById('promoCode').value     = '';
        document.getElementById('promoName').value     = '';
        document.getElementById('discountType').value  = 'Percentage';
        document.getElementById('discountValue').value = '';
        document.getElementById('startDate').value     = '';
        document.getElementById('endDate').value       = '';
        document.getElementById('isActive').checked    = true;
        openModal('New Promotion');
    });

  
    function editPromo(promoId) {
        const p = (window._promos || []).find(x => x.promotion_id == promoId);
        if (!p) return;
        document.getElementById('promoId').value       = p.promotion_id;
        document.getElementById('promoCode').value     = p.promo_code;
        document.getElementById('promoName').value     = p.promo_name;
        document.getElementById('discountType').value  = p.discount_type;
        document.getElementById('discountValue').value = p.discount_value;
        document.getElementById('startDate').value     = p.start_date;
        document.getElementById('endDate').value       = p.end_date;
        document.getElementById('isActive').checked    = !!parseInt(p.is_active);
        openModal('Edit Promotion');
    }


    document.getElementById('closePromoModal').addEventListener('click',  () => document.getElementById('promoModal').classList.remove('open'));
    document.getElementById('cancelPromoModal').addEventListener('click', () => document.getElementById('promoModal').classList.remove('open'));

   
    document.getElementById('savePromo').addEventListener('click', async function () 
    {
        const errEl = document.getElementById('promoModalError');
        errEl.style.display = 'none';
        try {
            await apiFetch(`${BASE}/api/admin.php?action=save_promo`, {
                method: 'POST',
                body: {
                    promotion_id:   parseInt(document.getElementById('promoId').value),
                    promo_code:     document.getElementById('promoCode').value.trim().toUpperCase(),
                    promo_name:     document.getElementById('promoName').value.trim(),
                    discount_type:  document.getElementById('discountType').value,
                    discount_value: parseFloat(document.getElementById('discountValue').value),
                    start_date:     document.getElementById('startDate').value,
                    end_date:       document.getElementById('endDate').value,
                    is_active:      document.getElementById('isActive').checked ? 1 : 0,
                }
            });
            document.getElementById('promoModal').classList.remove('open');
            showToast('Promotion saved.', 'success');
            loadPromos();
        } catch (err) 
        {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        }
    });

    loadPromos();
</script>