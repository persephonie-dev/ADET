<?php

    $pageTitle = 'Room Status';
    $activeNav = 'housekeeping';
    require_once __DIR__ . '/../includes/staff_header.php';
    ?>

 
    <div class="stat-grid" id="hkStatGrid" style="margin-bottom:1.5rem;">
        <div class="stat-card skeleton-card"><div class="skeleton" style="height:1.8rem;width:50%;border-radius:4px;margin-bottom:.4rem;"></div><div class="skeleton" style="height:.8rem;width:70%;border-radius:4px;"></div></div>
        <div class="stat-card skeleton-card"><div class="skeleton" style="height:1.8rem;width:50%;border-radius:4px;margin-bottom:.4rem;"></div><div class="skeleton" style="height:.8rem;width:70%;border-radius:4px;"></div></div>
        <div class="stat-card skeleton-card"><div class="skeleton" style="height:1.8rem;width:50%;border-radius:4px;margin-bottom:.4rem;"></div><div class="skeleton" style="height:.8rem;width:70%;border-radius:4px;"></div></div>
        <div class="stat-card skeleton-card"><div class="skeleton" style="height:1.8rem;width:50%;border-radius:4px;margin-bottom:.4rem;"></div><div class="skeleton" style="height:.8rem;width:70%;border-radius:4px;"></div></div>
    </div>

 
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;">
        <div id="hkStatusTabs" style="display:flex;gap:.4rem;flex-wrap:wrap;">
            <button class="hk-tab active" data-status="">All Rooms</button>
            <button class="hk-tab" data-status="Available">Available</button>
            <button class="hk-tab" data-status="Occupied">Occupied</button>
            <button class="hk-tab" data-status="Maintenance">Maintenance</button>
            <button class="hk-tab" data-status="Reserved">Reserved</button>
        </div>
        <input type="text" id="hkSearch" class="form-control"
            placeholder="Search room no. or type…" style="max-width:220px;margin-left:auto;">
    </div>

    <!-- grouped by floor -->
    <div id="hkFloors"></div>

 
    <div class="modal-overlay" id="hkModal">
        <div class="modal" style="max-width:420px;">
            <div class="modal-header">
                <h3>Update Room Status</h3>
                <button class="modal-close" id="closeHkModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:1.25rem;">
                    Room <strong id="hkModalRoom"></strong>
                    <span class="text-muted" style="font-size:.85rem;" id="hkModalType"></span>
                </p>
                <div class="form-group">
                    <label for="hkNewStatus">New Status</label>
                    <select id="hkNewStatus" class="form-control">
                        <option value="Available">Available</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Reserved">Reserved</option>
                    </select>
                </div>
                <div id="hkModalNote" class="alert alert-info" style="margin-top:.75rem;font-size:.82rem;">
                    Note: rooms are set to <strong>Occupied</strong> automatically when a guest checks in.
                </div>
                <div id="hkModalError" class="alert alert-error" style="display:none;margin-top:.75rem;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelHkModal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="confirmHkUpdate">Update</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/staff_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';

    let allRooms      = [];
    let activeFilter  = '';
    let pendingRoomId = null;

    const STATUS_STYLES = 
    {
        'Available':   { badge: 'badge badge-confirmed',  dot: '#2e7d52' },
        'Occupied':    { badge: 'badge badge-checkedin',  dot: '#1a6091' },
        'Maintenance': { badge: 'badge badge-pending',    dot: '#b45309' },
        'Reserved':    { badge: 'badge badge-info',       dot: '#6b3fa0' },
    };

    function statusBadge(s) 
    {
        return STATUS_STYLES[s]?.badge || 'badge badge-pending';
    }

    
    async function loadRooms() 
    {
        try 
        {
            allRooms = await apiFetch(`${BASE}/api/staff.php?action=rooms`);
            renderStats();
            renderRooms();
        } catch (err) {
            document.getElementById('hkFloors').innerHTML =
                `<p class="alert alert-error">${err.message}</p>`;
        }
    }

   
    function renderStats() 
    {
        const counts = { Available: 0, Occupied: 0, Maintenance: 0, Reserved: 0 };
        allRooms.forEach(r => { if (counts[r.status] !== undefined) counts[r.status]++; });

        document.getElementById('hkStatGrid').innerHTML = `
            <div class="stat-card accent-green">
                <div class="stat-value">${counts.Available}</div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card accent-blue">
                <div class="stat-value">${counts.Occupied}</div>
                <div class="stat-label">Occupied</div>
            </div>
            <div class="stat-card accent-red">
                <div class="stat-value">${counts.Maintenance}</div>
                <div class="stat-label">Maintenance</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${counts.Reserved}</div>
                <div class="stat-label">Reserved</div>
            </div>
        `;
    }

  
    function renderRooms() 
    {
        const q = document.getElementById('hkSearch').value.toLowerCase().trim();

        let rooms = allRooms.filter(r => 
        {
            const statusOk = !activeFilter || r.status === activeFilter;
            const searchOk = !q || r.room_number.toLowerCase().includes(q)
                                || r.type_name.toLowerCase().includes(q);
            return statusOk && searchOk;
        });

        if (!rooms.length) 
            {
            document.getElementById('hkFloors').innerHTML =
                '<p class="text-muted" style="text-align:center;padding:2rem;">No rooms match the current filter.</p>';
            return;
        }

      
        const floors = {};
        rooms.forEach(r => 
        {
            const f = r.floor_number ?? 'Unassigned';
            if (!floors[f]) floors[f] = [];
            floors[f].push(r);
        });

        const sortedFloors = Object.keys(floors).sort((a, b) => Number(a) - Number(b));

        document.getElementById('hkFloors').innerHTML = sortedFloors.map(floor => `
            <div class="admin-table-wrap" style="margin-bottom:1.25rem;">
                <div class="admin-table-header">
                    <h3>Floor ${floor}
                        <span class="text-muted" style="font-size:.8rem;font-weight:400;margin-left:.5rem;">
                            ${floors[floor].length} room${floors[floor].length !== 1 ? 's' : ''}
                        </span>
                    </h3>
                </div>
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Type</th>
                                <th>Bed</th>
                                <th>Capacity</th>
                                <th>Rate / Night</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${floors[floor].map(r => roomRow(r)).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `).join('');
    }

    function roomRow(r) 
    {
        const canUpdate = r.status !== 'Occupied'; 
        return `<tr>
            <td><strong>${r.room_number}</strong></td>
            <td>${r.type_name}</td>
            <td>${r.bed_type || '—'}</td>
            <td>${r.capacity || '—'}</td>
            <td>${formatPHP(r.price_per_night)}</td>
            <td>
                <span class="${statusBadge(r.status)}">${r.status}</span>
                ${r.description ? `<br><span class="text-muted" style="font-size:.75rem;">${r.description}</span>` : ''}
            </td>
            <td>
                ${canUpdate
                    ? `<button class="btn btn-outline btn-sm"
                            onclick="openHkModal(${r.room_id},'${r.room_number}','${r.type_name.replace(/'/g,"\\'")}','${r.status}')">
                        Update Status
                    </button>`
                    : `<span class="text-muted" style="font-size:.8rem;">Occupied — use check-in/out</span>`
                }
            </td>
        </tr>`;
    }


    document.getElementById('hkStatusTabs').addEventListener('click', function(e) 
    {
        const tab = e.target.closest('.hk-tab');
        if (!tab) return;
        document.querySelectorAll('.hk-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        activeFilter = tab.dataset.status;
        renderRooms();
    });

    document.getElementById('hkSearch').addEventListener('input', () => renderRooms());

    function openHkModal(roomId, roomNum, roomType, currentStatus) 
    {
        pendingRoomId = roomId;
        document.getElementById('hkModalRoom').textContent   = roomNum;
        document.getElementById('hkModalType').textContent   = ` — ${roomType}`;
        document.getElementById('hkModalError').style.display = 'none';

   
        const sel = document.getElementById('hkNewStatus');
        const next = currentStatus === 'Maintenance' ? 'Available' : 'Maintenance';
        for (const opt of sel.options) 
        {
            opt.selected = opt.value === next;
        }

        document.getElementById('hkModal').classList.add('open');
    }

    ['closeHkModal','cancelHkModal'].forEach(id =>
        document.getElementById(id).addEventListener('click',
            () => document.getElementById('hkModal').classList.remove('open')));

    document.getElementById('confirmHkUpdate').addEventListener('click', async function() 
    {
        if (!pendingRoomId) return;
        const newStatus = document.getElementById('hkNewStatus').value;
        const errEl     = document.getElementById('hkModalError');
        errEl.style.display = 'none';
        this.disabled = true;
        try 
        {
            await apiFetch(`${BASE}/api/staff.php?action=update_room`, 
            {
                method: 'POST',
                body: { room_id: pendingRoomId, status: newStatus }
            });
            document.getElementById('hkModal').classList.remove('open');
            showToast(`Room updated to ${newStatus}.`, 'success');
            loadRooms();
        } catch (err) 
        {
            errEl.textContent   = err.message;
            errEl.style.display = 'block';
        } finally {
            this.disabled = false;
        }
    });

    
    loadRooms();
</script>

<style>
    .hk-tab {
        background: none;
        border: 1px solid var(--cream-dark);
        border-radius: 99px;
        padding: .25rem .75rem;
        font-size: .8rem;
        cursor: pointer;
        color: var(--stone);
        transition: background .15s, color .15s;
    }
    .hk-tab:hover  { background: var(--cream); }
    .hk-tab.active { background: var(--charcoal); color: var(--white); border-color: var(--charcoal); }
</style>