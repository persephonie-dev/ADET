<?php

    $pageTitle = 'Staff Management';
    $activeNav = 'staff_management';
    require_once __DIR__ . '/../includes/admin_header.php';
    ?>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>Hotel Staff</h3>
            <button class="btn btn-primary btn-sm" id="addStaffBtn">+ Add Staff</button>
        </div>

    
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center;">
            <input type="text" id="staffSearch" class="form-control"
                placeholder="Search name or email…" style="max-width:260px;">
            <select id="staffRoleFilter" class="form-control" style="max-width:200px;">
                <option value="">All Roles</option>
            </select>
            <select id="staffStatusFilter" class="form-control" style="max-width:160px;">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
            <span id="staffCount" class="text-muted" style="font-size:.875rem;margin-left:auto;"></span>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role / Department</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hire Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="staffTbody">
                    <tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="staffFormModal">
        <div class="modal" style="max-width:520px;">
            <div class="modal-header">
                <h3 id="staffFormTitle">Add Staff Member</h3>
                <button class="modal-close" id="closeStaffForm">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editStaffId" value="0">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <div class="form-group">
                        <label for="sf_first_name">First Name *</label>
                        <input type="text" id="sf_first_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="sf_last_name">Last Name *</label>
                        <input type="text" id="sf_last_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="sf_email">Email *</label>
                        <input type="email" id="sf_email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="sf_phone">Phone *</label>
                        <input type="text" id="sf_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="sf_role">Staff Role *</label>
                        <select id="sf_role" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="sf_hire_date">Hire Date</label>
                        <input type="date" id="sf_hire_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="sf_status">Status *</label>
                        <select id="sf_status" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div id="staffFormError" class="alert alert-error" style="display:none;margin-top:.75rem;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelStaffForm">Cancel</button>
                <button class="btn btn-primary btn-sm" id="saveStaffBtn">Save</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="deleteStaffModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Remove Staff</h3>
                <button class="modal-close" id="closeDeleteStaff">&times;</button>
            </div>
            <div class="modal-body">
                <p>Remove <strong id="deleteStaffName"></strong> from staff?</p>
                <p class="text-muted" style="font-size:.875rem;margin-top:.5rem;">
                    This will permanently delete the staff record.
                </p>
                <div id="deleteStaffError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelDeleteStaff">Cancel</button>
                <button class="btn btn-sm" style="background:#e53e3e;color:#fff;" id="confirmDeleteStaff">Remove</button>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

    <script>
    const BASE = '<?= BASE_URL ?>';
    let allStaff       = [];
    let staffRoles     = [];
    let deleteStaffId  = null;

    async function loadStaffRoles() {
        try {
            staffRoles = await apiFetch(`${BASE}/api/admin.php?action=staff_roles`);
            const opts = staffRoles.map(r => `<option value="${r.staff_role_id}">${r.role_name}</option>`).join('');
            document.getElementById('sf_role').innerHTML = opts;
            document.getElementById('staffRoleFilter').innerHTML =
                '<option value="">All Roles</option>' +
                staffRoles.map(r => `<option value="${r.role_name}">${r.role_name}</option>`).join('');
        } catch (e) { /* ignore */ }
    }

    async function loadStaff() 
    {
        try {
            allStaff = await apiFetch(`${BASE}/api/admin.php?action=staff`);
            renderStaff();
        } catch (err) {
            document.getElementById('staffTbody').innerHTML =
                `<tr><td colspan="8"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

    function renderStaff() 
    {
        const q      = document.getElementById('staffSearch').value.toLowerCase();
        const role   = document.getElementById('staffRoleFilter').value;
        const status = document.getElementById('staffStatusFilter').value;

        const filtered = allStaff.filter(s => {
            const nameMatch = (`${s.first_name} ${s.last_name} ${s.email}`).toLowerCase().includes(q);
            const roleMatch = !role   || s.role_name    === role;
            const statMatch = !status || s.staff_status === status;
            return nameMatch && roleMatch && statMatch;
        });

        document.getElementById('staffCount').textContent =
            `${filtered.length} staff member${filtered.length !== 1 ? 's' : ''}`;

        const tbody = document.getElementById('staffTbody');
        if (!filtered.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">No staff found.</td></tr>';
            return;
        }
        tbody.innerHTML = filtered.map(s => `
            <tr>
                <td>#${s.staff_id}</td>
                <td>${s.first_name} ${s.last_name}</td>
                <td>${s.role_name}</td>
                <td><a href="mailto:${s.email}">${s.email}</a></td>
                <td>${s.phone_number}</td>
                <td>${s.hire_date ? new Date(s.hire_date).toLocaleDateString() : '--'}</td>
                <td><span class="badge ${s.staff_status === 'Active' ? 'badge-confirmed' : 'badge-pending'}">
                    ${s.staff_status}</span></td>
                <td style="display:flex;gap:.4rem;">
                    <button class="btn btn-outline btn-sm" onclick="openEditStaff(${s.staff_id})">Edit</button>
                    <button class="btn btn-sm" style="background:#e53e3e;color:#fff;"
                            onclick="openDeleteStaff(${s.staff_id},'${s.first_name} ${s.last_name}')">Remove</button>
                </td>
            </tr>
        `).join('');
    }

    ['staffSearch','staffRoleFilter','staffStatusFilter'].forEach(id => 
    {
        document.getElementById(id).addEventListener('input', renderStaff);
        document.getElementById(id).addEventListener('change', renderStaff);
    });


    document.getElementById('addStaffBtn').addEventListener('click', () => {
        document.getElementById('staffFormTitle').textContent = 'Add Staff Member';
        document.getElementById('editStaffId').value = '0';
        ['sf_first_name','sf_last_name','sf_email','sf_phone','sf_hire_date'].forEach(id =>
            document.getElementById(id).value = '');
        document.getElementById('sf_status').value = 'Active';
        document.getElementById('staffFormError').style.display = 'none';
        document.getElementById('staffFormModal').classList.add('open');
    });

    
    function openEditStaff(staffId) 
    {
        const s = allStaff.find(x => x.staff_id === staffId);
        if (!s) return;
        document.getElementById('staffFormTitle').textContent  = `Edit ${s.first_name} ${s.last_name}`;
        document.getElementById('editStaffId').value           = s.staff_id;
        document.getElementById('sf_first_name').value         = s.first_name;
        document.getElementById('sf_last_name').value          = s.last_name;
        document.getElementById('sf_email').value              = s.email;
        document.getElementById('sf_phone').value              = s.phone_number;
        document.getElementById('sf_hire_date').value          = s.hire_date || '';
        document.getElementById('sf_status').value             = s.staff_status || 'Active';
        
        const sel = document.getElementById('sf_role');
        for (const opt of sel.options) {
            if (opt.value == s.staff_role_id) { opt.selected = true; break; }
        }
        document.getElementById('staffFormError').style.display = 'none';
        document.getElementById('staffFormModal').classList.add('open');
    }


    document.getElementById('saveStaffBtn').addEventListener('click', async function () 
    {
        const errEl = document.getElementById('staffFormError');
        errEl.style.display = 'none';
        const id = parseInt(document.getElementById('editStaffId').value);
        const payload = {
            staff_id:      id,
            first_name:    document.getElementById('sf_first_name').value.trim(),
            last_name:     document.getElementById('sf_last_name').value.trim(),
            email:         document.getElementById('sf_email').value.trim(),
            phone_number:  document.getElementById('sf_phone').value.trim(),
            staff_role_id: parseInt(document.getElementById('sf_role').value),
            hire_date:     document.getElementById('sf_hire_date').value || null,
            staff_status:  document.getElementById('sf_status').value,
        };
        if (!payload.first_name || !payload.last_name || !payload.email || !payload.phone_number) 
            {
            errEl.textContent   = 'Name, email and phone are required.';
            errEl.style.display = 'block';
            return;
        }
        const action = id > 0 ? 'edit_staff' : 'add_staff';
        try {
            await apiFetch(`${BASE}/api/admin.php?action=${action}`, { method:'POST', body: payload });
            document.getElementById('staffFormModal').classList.remove('open');
            showToast(id > 0 ? 'Staff updated.' : 'Staff member added.', 'success');
            loadStaff();
        } catch (err) 
        {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        }
    });

    ['closeStaffForm','cancelStaffForm'].forEach(id =>
        document.getElementById(id).addEventListener('click',
            () => document.getElementById('staffFormModal').classList.remove('open')));

    function openDeleteStaff(staffId, name) 
    {
        deleteStaffId = staffId;
        document.getElementById('deleteStaffName').textContent     = name;
        document.getElementById('deleteStaffError').style.display  = 'none';
        document.getElementById('deleteStaffModal').classList.add('open');
    }
    ['closeDeleteStaff','cancelDeleteStaff'].forEach(id =>
        document.getElementById(id).addEventListener('click',
            () => document.getElementById('deleteStaffModal').classList.remove('open')));

    document.getElementById('confirmDeleteStaff').addEventListener('click', async function () {
        const errEl = document.getElementById('deleteStaffError');
        errEl.style.display = 'none';
        try {
            await apiFetch(`${BASE}/api/admin.php?action=delete_staff`, {
                method:'POST', body:{ staff_id: deleteStaffId }
            });
            document.getElementById('deleteStaffModal').classList.remove('open');
            showToast('Staff member removed.', 'success');
            loadStaff();
        } catch (err) {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        }
    });

 
    
    loadStaffRoles();
    loadStaff();
</script>