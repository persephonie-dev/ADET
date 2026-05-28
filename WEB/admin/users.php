<?php


    $pageTitle = 'Guests & Users';
    $activeNav = 'users';
    require_once __DIR__ . '/../includes/admin_header.php';
    ?>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>All Users</h3>
            <button class="btn btn-primary btn-sm" id="exportUsersBtn">Export CSV</button>
        </div>


        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center;">
            <input type="text" id="userSearch" class="form-control" placeholder="Search name or email…"
                style="max-width:260px;">
            <select id="roleFilter" class="form-control" style="max-width:160px;">
                <option value="">All Roles</option>
                <option value="Admin">Admin</option>
                <option value="Guest">Guest</option>
                <option value="Staff">Staff</option>
            </select>
            <select id="statusFilter" class="form-control" style="max-width:160px;">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Suspended">Suspended</option>
                <option value="Inactive">Inactive</option>
            </select>
            <span id="userCount" class="text-muted" style="font-size:.875rem;margin-left:auto;"></span>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table" id="usersTable">
                <thead>
                    <tr>
                        <th class="sortable" data-col="user_id">ID <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="name">Name <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="email">Email <span class="sort-icon">↕</span></th>
                        <th>Phone</th>
                        <th class="sortable" data-col="role_name">Role <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="total_bookings">Bookings <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="created_at">Registered <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="user_status">Status <span class="sort-icon">↕</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTbody">
                    <tr><td colspan="9" class="text-muted text-center" style="padding:2rem;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div id="usersPagination" style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;flex-wrap:wrap;"></div>
    </div>


    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="userModalTitle">Update User</h3>
                <button class="modal-close" id="closeUserModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:1rem;">User: <strong id="modalUserName"></strong></p>
                <div class="form-group">
                    <label for="newUserStatus">Status</label>
                    <select id="newUserStatus" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Suspended">Suspended</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div id="userModalError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelUserModal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="confirmUserUpdate">Update Status</button>
            </div>
        </div>
    </div>


    <div class="modal-overlay" id="deleteUserModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Delete User</h3>
                <button class="modal-close" id="closeDeleteUserModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete <strong id="deleteUserName"></strong>?</p>
                <p class="text-muted" style="font-size:.875rem;margin-top:.5rem;">
                    This action cannot be undone. Users with existing bookings cannot be deleted.
                </p>
                <div id="deleteUserError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelDeleteUser">Cancel</button>
                <button class="btn btn-sm" id="confirmDeleteUser"
                        style="background:#e53e3e;color:#fff;">Delete</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';
    let allUsers        = [];
    let filteredUsers   = [];
    let sortCol         = 'created_at';
    let sortDir         = 'desc';
    let currentUserId   = null;
    let deleteUserId    = null;
    const PAGE_SIZE     = 20;
    let currentPage     = 1;


    function userStatusBadge(status) {
        if (status === 'Active')    return 'badge badge-confirmed';
        if (status === 'Suspended') return 'badge badge-cancelled';
        return 'badge badge-pending';
    }

    async function loadUsers() 
    {
        try {
            allUsers = await apiFetch(`${BASE}/api/admin.php?action=users`);
            applyFilters();
        } catch (err) {
            document.getElementById('usersTbody').innerHTML =
                `<tr><td colspan="9"><p class="alert alert-error">${err.message}</p></td></tr>`;
        }
    }

    function applyFilters() 
    {
        const q      = document.getElementById('userSearch').value.toLowerCase();
        const role   = document.getElementById('roleFilter').value;
        const status = document.getElementById('statusFilter').value;

        filteredUsers = allUsers.filter(u => {
            const nameMatch  = (`${u.first_name} ${u.last_name}`).toLowerCase().includes(q)
                            || u.email.toLowerCase().includes(q);
            const roleMatch  = !role   || u.role_name   === role;
            const statMatch  = !status || u.user_status === status;
            return nameMatch && roleMatch && statMatch;
        });

        sortUsers();
        currentPage = 1;
        renderUsers();
    }

    function sortUsers() 
    {
        filteredUsers.sort((a, b) => {
            let va = a[sortCol] ?? '';
            let vb = b[sortCol] ?? '';
            if (sortCol === 'name') { va = `${a.first_name} ${a.last_name}`; vb = `${b.first_name} ${b.last_name}`; }
            if (sortCol === 'user_id' || sortCol === 'total_bookings') { va = +va; vb = +vb; }
            if (va < vb) return sortDir === 'asc' ? -1 :  1;
            if (va > vb) return sortDir === 'asc' ?  1 : -1;
            return 0;
        });
    }

    function renderUsers() 
    {
        const start   = (currentPage - 1) * PAGE_SIZE;
        const page    = filteredUsers.slice(start, start + PAGE_SIZE);
        const tbody   = document.getElementById('usersTbody');
        document.getElementById('userCount').textContent =
            `${filteredUsers.length} user${filteredUsers.length !== 1 ? 's' : ''}`;

        if (!page.length) 
        {
            tbody.innerHTML = '<tr><td colspan="9" class="text-muted text-center" style="padding:2rem;">No users found.</td></tr>';
            document.getElementById('usersPagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = page.map(u => `
            <tr>
                <td>#${u.user_id}</td>
                <td>${u.first_name} ${u.last_name}</td>
                <td><a href="mailto:${u.email}">${u.email}</a></td>
                <td>${u.phone_number || '--'}</td>
                <td>${u.role_name}</td>
                <td>${u.total_bookings}</td>
                <td>${u.created_at ? new Date(u.created_at.replace(' ','T')).toLocaleDateString() : 'N/A'}</td>
                <td><span class="${userStatusBadge(u.user_status)}">${u.user_status}</span></td>
                <td style="display:flex;gap:.4rem;flex-wrap:wrap;">
                    <button class="btn btn-outline btn-sm"
                            onclick="openUserModal(${u.user_id},'${u.first_name} ${u.last_name}','${u.user_status}')">
                        Edit Status
                    </button>
                    <button class="btn btn-sm" style="background:#e53e3e;color:#fff;"
                            onclick="openDeleteUser(${u.user_id},'${u.first_name} ${u.last_name}')">
                        Delete
                    </button>
                </td>
            </tr>
        `).join('');

        renderPagination();
        updateSortIcons();
    }

    function renderPagination() 
    {
        const total = Math.ceil(filteredUsers.length / PAGE_SIZE);
        if (total <= 1) { document.getElementById('usersPagination').innerHTML = ''; return; }
        let html = '';
        for (let i = 1; i <= total; i++) 
        {
            html += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline'}"
                            onclick="goPage(${i})">${i}</button>`;
        }
        document.getElementById('usersPagination').innerHTML = html;
    }
    function goPage(p) { currentPage = p; renderUsers(); }


    document.querySelectorAll('.sortable').forEach(th => 
    {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            if (sortCol === col) { sortDir = sortDir === 'asc' ? 'desc' : 'asc'; }
            else { sortCol = col; sortDir = 'asc'; }
            sortUsers();
            currentPage = 1;
            renderUsers();
        });
    });

    function updateSortIcons() 
    {
        document.querySelectorAll('.sortable').forEach(th => 
        {
            const icon = th.querySelector('.sort-icon');
            if (th.dataset.col === sortCol) {
                icon.textContent = sortDir === 'asc' ? '↑' : '↓';
            } else {
                icon.textContent = '↕';
            }
        });
    }


    ['userSearch','roleFilter','statusFilter'].forEach(id => 
    {
        document.getElementById(id).addEventListener('input', applyFilters);
        document.getElementById(id).addEventListener('change', applyFilters);
    });


    function openUserModal(userId, name, currentStatus) 
    {
        currentUserId = userId;
        document.getElementById('modalUserName').textContent  = name;
        document.getElementById('newUserStatus').value        = currentStatus;
        document.getElementById('userModalError').style.display = 'none';
        document.getElementById('userModal').classList.add('open');
    }
    document.getElementById('closeUserModal').addEventListener('click',
        () => document.getElementById('userModal').classList.remove('open'));
    document.getElementById('cancelUserModal').addEventListener('click',
        () => document.getElementById('userModal').classList.remove('open'));

    document.getElementById('confirmUserUpdate').addEventListener('click', async function () {
        const newStatus = document.getElementById('newUserStatus').value;
        const errEl     = document.getElementById('userModalError');
        errEl.style.display = 'none';
        try 
        {
            await apiFetch(`${BASE}/api/admin.php?action=update_user`, {
                method: 'POST',
                body: { user_id: currentUserId, status: newStatus }
            });
            document.getElementById('userModal').classList.remove('open');
            showToast('User status updated.', 'success');
            loadUsers();
        } catch (err) {
            errEl.textContent    = err.message;
            errEl.style.display  = 'block';
        }
    });


    function openDeleteUser(userId, name) {
        deleteUserId = userId;
        document.getElementById('deleteUserName').textContent    = name;
        document.getElementById('deleteUserError').style.display = 'none';
        document.getElementById('deleteUserModal').classList.add('open');
    }
    document.getElementById('closeDeleteUserModal').addEventListener('click',
        () => document.getElementById('deleteUserModal').classList.remove('open'));
    document.getElementById('cancelDeleteUser').addEventListener('click',
        () => document.getElementById('deleteUserModal').classList.remove('open'));

    document.getElementById('confirmDeleteUser').addEventListener('click', async function () {
        const errEl = document.getElementById('deleteUserError');
        errEl.style.display = 'none';
        try 
        {
            await apiFetch(`${BASE}/api/admin.php?action=delete_user`, {
                method: 'POST',
                body: { user_id: deleteUserId }
            });
            document.getElementById('deleteUserModal').classList.remove('open');
            showToast('User deleted.', 'success');
            loadUsers();
        } catch (err) {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        }
    });

  
    document.getElementById('exportUsersBtn').addEventListener('click', () => 
    {
        if (!filteredUsers.length) return;
        const headers = ['ID','First Name','Last Name','Email','Phone','Role','Bookings','Registered','Status'];
        const rows = filteredUsers.map(u => [
            u.user_id, u.first_name, u.last_name, u.email,
            u.phone_number || '', u.role_name, u.total_bookings,
            u.created_at || '', u.user_status
        ]);
        const csv = [headers, ...rows].map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type:'text/csv' });
        const a    = Object.assign(document.createElement('a'), {
            href: URL.createObjectURL(blob),
            download: `users_${new Date().toISOString().slice(0,10)}.csv`
        });
        a.click();
    });

    loadUsers();
</script>

<style>
.sortable:hover { background: rgba(0,0,0,.04); }
.sort-icon { opacity:.5; font-size:.8em; }
</style>