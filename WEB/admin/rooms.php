<?php

$pageTitle = 'Manage Rooms';
$activeNav = 'rooms';

require_once __DIR__ . '/../includes/admin_header.php';

?>
    

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>All Rooms</h3>
            <button class="btn btn-primary btn-sm" id="addRoomBtn">+ Add Room</button>
        </div>

    
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center;">
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab active" data-status="">All</button>
                <button class="filter-tab" data-status="Available">Available</button>
                <button class="filter-tab" data-status="Occupied">Occupied</button>
                <button class="filter-tab" data-status="Maintenance">Maintenance</button>
                <button class="filter-tab" data-status="Reserved">Reserved</button>
            </div>
            <input type="text" id="roomSearch" class="form-control"
                placeholder="Search room no. or type…" style="max-width:220px;margin-left:auto;">
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table" id="roomsTable">
                <thead>
                    <tr>
                        <th class="sortable" data-col="room_number">Room No. <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="type_name">Type <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="floor_number">Floor <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="capacity">Capacity <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-col="price_per_night">Rate/Night <span class="sort-icon">↕</span></th>
                        <th>Bed Type</th>
                        <th class="sortable" data-col="status">Status <span class="sort-icon">↕</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="roomsTbody">
                    <tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>


    
    <div class="modal-overlay" id="roomFormModal">
        <div class="modal" style="max-width:620px;">
            <div class="modal-header">
                <h3 id="roomFormTitle">Add New Room</h3>
                <button class="modal-close" id="closeRoomFormModal">&times;</button>
            </div>

            <div style="display:flex;border-bottom:1px solid var(--cream-dark);padding:0 1.5rem;">
                <button class="room-tab active" data-tab="details" style="padding:.65rem 1rem;border:none;background:none;cursor:pointer;font-size:.82rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--stone);border-bottom:2px solid transparent;margin-bottom:-1px;">
                    Room Details
                </button>
                <button class="room-tab" data-tab="images" id="imagesTabBtn" style="padding:.65rem 1rem;border:none;background:none;cursor:pointer;font-size:.82rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--stone);border-bottom:2px solid transparent;margin-bottom:-1px;">
                    Images
                </button>
            </div>

            <div class="modal-body">

        
                <div id="tabDetails">
                    <input type="hidden" id="editRoomId" value="0">
                
                    <input type="hidden" id="editRoomTypeId" value="0">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                        <div class="form-group">
                            <label for="rf_room_number">Room Number *</label>
                            <input type="text" id="rf_room_number" class="form-control" placeholder="e.g. 101" maxlength="20">
                        </div>
                        <div class="form-group">
                            <label for="rf_room_type">Room Type *</label>
                            <select id="rf_room_type" class="form-control">
                                <option value="">Loading…</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rf_floor">Floor</label>
                            <input type="number" id="rf_floor" class="form-control" placeholder="e.g. 1" min="0">
                        </div>
                        <div class="form-group">
                            <label for="rf_capacity">Capacity (guests)</label>
                            <input type="number" id="rf_capacity" class="form-control" placeholder="e.g. 2" min="1">
                        </div>
                        <div class="form-group">
                            <label for="rf_price">Price per Night (₱) *</label>
                            <input type="number" id="rf_price" class="form-control" placeholder="e.g. 3500" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="rf_status">Status *</label>
                            <select id="rf_status" class="form-control">
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Reserved">Reserved</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:.5rem;">
                        <label for="rf_description">Description</label>
                        <textarea id="rf_description" class="form-control" rows="3"
                                placeholder="Optional room description…"></textarea>
                    </div>
                    <div id="roomFormError" class="alert alert-error" style="display:none;"></div>
                </div>

               
                <div id="tabImages" style="display:none;">
                   
                    <div id="imagesTabNotice" class="alert alert-info" style="display:none;">
                        Save the room details first, then come back here to add images.
                    </div>

                  
                    <div id="imageUploadSection" style="display:none;">
                        <div style="background:var(--cream);border-radius:var(--radius);padding:1rem;margin-bottom:1.25rem;">
                            <div class="form-group" style="margin-bottom:.75rem;">
                                <label for="imgFile">Upload Image (JPG / PNG / WEBP, max 5 MB)</label>
                                <input type="file" id="imgFile" accept="image/jpeg,image/png,image/webp,image/gif"
                                    style="display:block;margin-top:.3rem;">
                            </div>

                       
                            <div id="imgPreviewWrap" style="display:none;margin-bottom:.75rem;">
                                <img id="imgPreview" src="" alt="Preview"
                                    style="max-width:100%;max-height:160px;border-radius:var(--radius);object-fit:cover;border:1px solid var(--cream-dark);">
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 100px;gap:.75rem;align-items:end;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label for="imgCaption">Caption (optional)</label>
                                    <input type="text" id="imgCaption" class="form-control" placeholder="e.g. Bedroom view">
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label for="imgOrder">Order</label>
                                    <input type="number" id="imgOrder" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div style="margin-top:.75rem;">
                                <button class="btn btn-primary btn-sm" id="uploadImageBtn">Upload Image</button>
                                <span id="uploadProgress" style="font-size:.82rem;color:var(--stone);margin-left:.75rem;display:none;">Uploading…</span>
                            </div>
                            <div id="uploadError" class="alert alert-error" style="display:none;margin-top:.75rem;"></div>
                        </div>

                     
                        <h4 style="font-size:.85rem;font-weight:700;margin-bottom:.75rem;">
                            Current Images
                            <span style="font-size:.75rem;font-weight:400;color:var(--stone);margin-left:.5rem;">
                                (★ = cover photo shown on room cards)
                            </span>
                        </h4>
                        <div id="currentImages" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.75rem;">
                            <p class="text-muted" style="font-size:.82rem;grid-column:1/-1;">Loading images…</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelRoomForm">Cancel</button>
              
                <button class="btn btn-primary btn-sm" id="saveRoomBtn">Save Room</button>
            </div>
        </div>
    </div>


    <div class="modal-overlay" id="roomStatusModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Update Room Status</h3>
                <button class="modal-close" id="closeRoomStatusModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:1rem;">Room <strong id="modalRoomNum"></strong></p>
                <div class="form-group">
                    <label for="newRoomStatus">New Status</label>
                    <select id="newRoomStatus" class="form-control">
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Reserved">Reserved</option>
                    </select>
                </div>
                <div id="roomStatusError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelRoomStatus">Cancel</button>
                <button class="btn btn-primary btn-sm" id="confirmRoomUpdate">Update</button>
            </div>
        </div>
    </div>


    <div class="modal-overlay" id="deleteRoomModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Delete Room</h3>
                <button class="modal-close" id="closeDeleteRoomModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Permanently delete room <strong id="deleteRoomNum"></strong>?</p>
                <p class="text-muted" style="font-size:.875rem;margin-top:.5rem;">
                    Rooms that have bookings cannot be deleted.
                </p>
                <div id="deleteRoomError" class="alert alert-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="cancelDeleteRoom">Cancel</button>
                <button class="btn btn-sm" style="background:#e53e3e;color:#fff;" id="confirmDeleteRoom">
                    Delete
                </button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>
const BASE = '<?= BASE_URL ?>';

let allRooms           = [];
let filteredRooms      = [];
let roomTypes          = [];
let currentRoomFilter  = '';
let sortCol            = 'room_number';
let sortDir            = 'asc';
let statusRoomId       = null;
let deleteRoomId       = null;

let activeImageTypeId  = 0;


function roomStatusBadge(status) 
{
    const map = {
        'Available':   'badge badge-confirmed',
        'Occupied':    'badge badge-checkedin',
        'Maintenance': 'badge badge-pending',
        'Reserved':    'badge badge-info',
    };
    return map[status] || 'badge badge-pending';
}

async function loadRoomTypes() 
{
    try 
    {
        roomTypes = await apiFetch(`${BASE}/api/admin.php?action=room_types`);
        const sel = document.getElementById('rf_room_type');
        sel.innerHTML = roomTypes.map(t =>
            `<option value="${t.room_type_id}">${t.type_name} – ₱${Number(t.base_price).toLocaleString()}</option>`
        ).join('');
    } catch (e) {  }
}

async function loadRooms() 
{
    try 
    {
        allRooms = await apiFetch(`${BASE}/api/admin.php?action=rooms`);
        applyFilters();
    } catch (err) 
    {
        document.getElementById('roomsTbody').innerHTML =
            `<tr><td colspan="8"><p class="alert alert-error">${err.message}</p></td></tr>`;
    }
}

function applyFilters() 
{
    const q = document.getElementById('roomSearch').value.toLowerCase();
    filteredRooms = allRooms.filter(r => {
        const statusOk = !currentRoomFilter || r.status === currentRoomFilter;
        const searchOk = !q || r.room_number.toLowerCase().includes(q)
                             || r.type_name.toLowerCase().includes(q);
        return statusOk && searchOk;
    });
    sortRooms();
    renderRooms();
}

function sortRooms() 
{
    filteredRooms.sort((a, b) => 
    {
        let va = a[sortCol] ?? '';
        let vb = b[sortCol] ?? '';
        if (['price_per_night','floor_number','capacity'].includes(sortCol)) { va = +va; vb = +vb; }
        if (va < vb) return sortDir === 'asc' ? -1 :  1;
        if (va > vb) return sortDir === 'asc' ?  1 : -1;
        return 0;
    });
}

function renderRooms() 
{
    const tbody = document.getElementById('roomsTbody');
    if (!filteredRooms.length) 
    {
        tbody.innerHTML = '<tr><td colspan="8" class="text-muted text-center" style="padding:2rem;">No rooms found.</td></tr>';
        return;
    }
    tbody.innerHTML = filteredRooms.map(r => `
        <tr>
            <td><strong>${r.room_number}</strong></td>
            <td>${r.type_name}</td>
            <td>${r.floor_number ?? '--'}</td>
            <td>${r.capacity ?? '--'}</td>
            <td>${formatPHP(r.price_per_night)}</td>
            <td>${r.bed_type || '--'}</td>
            <td><span class="${roomStatusBadge(r.status)}">${r.status}</span></td>
            <td style="display:flex;gap:.4rem;flex-wrap:wrap;">
                <button class="btn btn-outline btn-sm"
                        onclick="openStatusModal(${r.room_id},'${r.room_number}','${r.status}')">
                    Status
                </button>
                <button class="btn btn-outline btn-sm"
                        onclick="openEditRoom(${r.room_id})">
                    Edit
                </button>
                <button class="btn btn-sm" style="background:#e53e3e;color:#fff;"
                        onclick="openDeleteRoom(${r.room_id},'${r.room_number}')">
                    Delete
                </button>
            </td>
        </tr>
    `).join('');
    updateSortIcons();
}


document.getElementById('filterTabs').addEventListener('click', function (e) 
{
    const tab = e.target.closest('.filter-tab');
    if (!tab) return;
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    currentRoomFilter = tab.dataset.status;
    applyFilters();
});

document.getElementById('roomSearch').addEventListener('input', applyFilters);


document.querySelectorAll('.sortable').forEach(th => 
{
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => 
    {
        const col = th.dataset.col;
        if (sortCol === col) { sortDir = sortDir === 'asc' ? 'desc' : 'asc'; }
        else { sortCol = col; sortDir = 'asc'; }
        sortRooms();
        renderRooms();
    });
});
function updateSortIcons() {
    document.querySelectorAll('.sortable').forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (!icon) return;
        icon.textContent = th.dataset.col === sortCol ? (sortDir === 'asc' ? '↑' : '↓') : '↕';
    });
}


document.querySelectorAll('.room-tab').forEach(btn => 
{
    btn.addEventListener('click', function () {
        document.querySelectorAll('.room-tab').forEach(b =>
         {
            b.style.borderBottomColor = 'transparent';
            b.style.color = 'var(--stone)';
        });
        this.style.borderBottomColor = 'var(--gold)';
        this.style.color = 'var(--charcoal)';

        const tab = this.dataset.tab;
        document.getElementById('tabDetails').style.display = tab === 'details' ? '' : 'none';
        document.getElementById('tabImages').style.display  = tab === 'images'  ? '' : 'none';
        document.getElementById('saveRoomBtn').style.display = tab === 'details' ? '' : 'none';

        if (tab === 'images') 
            {
            prepareImagesTab();
        }
    });
});


document.getElementById('addRoomBtn').addEventListener('click', () => 
{
    document.getElementById('roomFormTitle').textContent = 'Add New Room';
    document.getElementById('editRoomId').value = '0';
    document.getElementById('editRoomTypeId').value = '0';
    document.getElementById('rf_room_number').value = '';
    document.getElementById('rf_floor').value = '';
    document.getElementById('rf_capacity').value = '';
    document.getElementById('rf_price').value = '';
    document.getElementById('rf_status').value = 'Available';
    document.getElementById('rf_description').value  = '';
    document.getElementById('roomFormError').style.display  = 'none';
    activeImageTypeId = 0;
  
    document.querySelector('.room-tab[data-tab="details"]').click();
    document.getElementById('roomFormModal').classList.add('open');
});


function openEditRoom(roomId) 
{
    const room = allRooms.find(r => r.room_id === roomId);
    if (!room) return;
    document.getElementById('roomFormTitle').textContent    = `Edit Room ${room.room_number}`;
    document.getElementById('editRoomId').value             = room.room_id;
    document.getElementById('editRoomTypeId').value         = room.room_type_id;
    document.getElementById('rf_room_number').value         = room.room_number;
    document.getElementById('rf_floor').value               = room.floor_number ?? '';
    document.getElementById('rf_capacity').value            = room.capacity ?? '';
    document.getElementById('rf_price').value               = room.price_per_night;
    document.getElementById('rf_status').value              = room.status;
    document.getElementById('rf_description').value         = room.description ?? '';
    const sel = document.getElementById('rf_room_type');
    for (const opt of sel.options)
    {
        if (opt.value == room.room_type_id) { opt.selected = true; break; }
    }
    document.getElementById('roomFormError').style.display  = 'none';
    activeImageTypeId = room.room_type_id;

    document.querySelector('.room-tab[data-tab="details"]').click();
    document.getElementById('roomFormModal').classList.add('open');
}


document.getElementById('saveRoomBtn').addEventListener('click', async function () {
    const errEl  = document.getElementById('roomFormError');
    errEl.style.display = 'none';
    const id     = parseInt(document.getElementById('editRoomId').value);
    const payload = 
    {
        room_id:          id,
        room_number:      document.getElementById('rf_room_number').value.trim(),
        room_type_id:     parseInt(document.getElementById('rf_room_type').value),
        floor_number:     document.getElementById('rf_floor').value || null,
        capacity:         document.getElementById('rf_capacity').value || null,
        price_per_night:  document.getElementById('rf_price').value,
        status:           document.getElementById('rf_status').value,
        description:      document.getElementById('rf_description').value.trim() || null,
    };
    if (!payload.room_number || !payload.room_type_id || !payload.price_per_night) {
        errEl.textContent   = 'Room number, type, and price are required.';
        errEl.style.display = 'block';
        return;
    }
    const action = id > 0 ? 'edit_room' : 'add_room';
    try 
    {
        const result = await apiFetch(`${BASE}/api/admin.php?action=${action}`, { method:'POST', body: payload });
        // After saving a new room, grab the room_type_id so the images tab works
        if (id === 0 && result.room_type_id) {
            document.getElementById('editRoomTypeId').value = result.room_type_id;
            activeImageTypeId = result.room_type_id;
            document.getElementById('editRoomId').value = result.room_id || 0;
        } else {
            activeImageTypeId = payload.room_type_id;
        }
        showToast(id > 0 ? 'Room updated.' : 'Room added. You can now upload images.', 'success');
        loadRooms();

        // If new room: stay in modal but switch to images tab automatically
        if (id === 0) {
            document.querySelector('.room-tab[data-tab="images"]').click();
        } else {
            document.getElementById('roomFormModal').classList.remove('open');
        }
    } catch (err) {
        errEl.textContent   = err.message;
        errEl.style.display = 'block';
    }
});

['closeRoomFormModal','cancelRoomForm'].forEach(id =>
    document.getElementById(id).addEventListener('click',
        () => document.getElementById('roomFormModal').classList.remove('open')));


function prepareImagesTab()
{
    const notice  = document.getElementById('imagesTabNotice');
    const section = document.getElementById('imageUploadSection');


    if (!activeImageTypeId) 
    {
        notice.style.display  = 'block';
        section.style.display = 'none';
        return;
    }
    notice.style.display  = 'none';
    section.style.display = 'block';
    loadCurrentImages(activeImageTypeId);
}

async function loadCurrentImages(roomTypeId) {
    const grid = document.getElementById('currentImages');
    grid.innerHTML = '<p class="text-muted" style="font-size:.82rem;grid-column:1/-1;">Loading images…</p>';
    try 
    {
     
        const images = await apiFetch(`${BASE}/api/room_images.php?action=list&room_type_id=${roomTypeId}`);
        if (!images.length) 
        {
            grid.innerHTML = '<p class="text-muted" style="font-size:.82rem;grid-column:1/-1;">No images yet. Upload one above.</p>';
            return;
        }
        grid.innerHTML = images.map((img, idx) => `
            <div style="position:relative;border-radius:var(--radius);overflow:hidden;border:2px solid ${img.display_order == 1 ? 'var(--gold)' : 'var(--cream-dark)'};" title="${img.display_order == 1 ? '★ Cover photo' : ''}">
                <img src="${img.full_url}" alt="${img.caption || ''}"
                     style="width:100%;height:90px;object-fit:cover;display:block;"
                     onerror="this.style.background='var(--cream)';this.style.height='90px';">
                <div style="padding:.35rem .5rem;font-size:.72rem;color:var(--stone);background:var(--white);">
                    ${img.display_order == 1 ? '<strong style="color:var(--gold);">★ Cover</strong> · ' : ''}${img.caption || '<em>No caption</em>'}<br>
                    <span style="color:var(--stone-light);">Order: ${img.display_order}</span>
                </div>
                <!-- Delete button -->
                <button onclick="deleteImage(${img.room_image_id})"
                        style="position:absolute;top:4px;right:4px;background:rgba(229,62,62,.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:13px;line-height:1;cursor:pointer;"
                        title="Delete image">&times;</button>
                <!-- Set as cover button (only on non-thumbnail images) -->
                ${img.display_order != 1 ? `
                <button onclick="setCover(${img.room_image_id}, ${roomTypeId})"
                        style="position:absolute;top:4px;left:4px;background:rgba(184,142,60,.9);color:#fff;border:none;border-radius:3px;padding:2px 5px;font-size:10px;font-weight:700;cursor:pointer;line-height:1.4;"
                        title="Set as cover photo">★ Cover</button>
                ` : ''}
            </div>
        `).join('');
    } catch (err) {
        grid.innerHTML = `<p class="alert alert-error" style="grid-column:1/-1;">${err.message}</p>`;
    }
}

async function deleteImage(roomImageId) {
    if (!confirm('Delete this image?')) return;
    try {
        await apiFetch(`${BASE}/api/room_images.php?action=delete`, {
            method: 'POST',
            body:   { room_image_id: roomImageId }
        });
        showToast('Image deleted.', 'success');
        loadCurrentImages(activeImageTypeId);
    } catch (err) {
        showToast(err.message, 'error');
    }
}

async function setCover(roomImageId, roomTypeId) 
{
    try {
        await apiFetch(`${BASE}/api/room_images.php?action=set_thumbnail`, {
            method: 'POST',
            body:   { room_image_id: roomImageId, room_type_id: roomTypeId }
        });
        showToast('Cover photo updated.', 'success');
        loadCurrentImages(activeImageTypeId);
    } catch (err) {
        showToast(err.message, 'error');
    }
}


document.getElementById('imgFile').addEventListener('change', function () 
{
    const previewWrap = document.getElementById('imgPreviewWrap');
    const preview     = document.getElementById('imgPreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            previewWrap.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    } else {
        previewWrap.style.display = 'none';
        preview.src = '';
    }
});


document.getElementById('uploadImageBtn').addEventListener('click', async function () 
{
    const fileInput = document.getElementById('imgFile');
    const caption   = document.getElementById('imgCaption').value.trim();
    const order     = document.getElementById('imgOrder').value;
    const errEl     = document.getElementById('uploadError');
    const progress  = document.getElementById('uploadProgress');
    errEl.style.display = 'none';

    if (!fileInput.files.length) 
        {
        errEl.textContent   = 'Please select an image file.';
        errEl.style.display = 'block';
        return;
    }

    const formData = new FormData();
    formData.append('room_type_id',  activeImageTypeId);
    formData.append('image',         fileInput.files[0]);
    formData.append('caption',       caption);
    formData.append('display_order', order || '0');

    this.disabled            = true;
    progress.style.display   = 'inline';

    try 
    {
       
        const res  = await fetch(`${BASE}/api/room_images.php?action=upload`, {
            method: 'POST',
            body:   formData,
    
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Upload failed.');

        showToast('Image uploaded.', 'success');
        fileInput.value          = '';
        document.getElementById('imgPreviewWrap').style.display = 'none';
        document.getElementById('imgPreview').src               = '';
        document.getElementById('imgCaption').value = '';
        document.getElementById('imgOrder').value   = '0';
        loadCurrentImages(activeImageTypeId);
    } catch (err) {
        errEl.textContent   = err.message;
        errEl.style.display = 'block';
    } finally {
        this.disabled           = false;
        progress.style.display  = 'none';
    }
});


function openStatusModal(roomId, roomNum, currentStatus) 
{
    statusRoomId = roomId;
    document.getElementById('modalRoomNum').textContent      = roomNum;
    document.getElementById('newRoomStatus').value           = currentStatus;
    document.getElementById('roomStatusError').style.display = 'none';
    document.getElementById('roomStatusModal').classList.add('open');
}
['closeRoomStatusModal','cancelRoomStatus'].forEach(id =>
    document.getElementById(id).addEventListener('click',
        () => document.getElementById('roomStatusModal').classList.remove('open')));

document.getElementById('confirmRoomUpdate').addEventListener('click', async function () {
    const newStatus = document.getElementById('newRoomStatus').value;
    const errEl     = document.getElementById('roomStatusError');
    errEl.style.display = 'none';
    try {
        await apiFetch(`${BASE}/api/admin.php?action=update_room`, {
            method:'POST', body:{ room_id: statusRoomId, status: newStatus }
        });
        document.getElementById('roomStatusModal').classList.remove('open');
        showToast('Room status updated.', 'success');
        loadRooms();
    } catch (err) {
        errEl.textContent   = err.message;
        errEl.style.display = 'block';
    }
});


function openDeleteRoom(roomId, roomNum) 
{
    deleteRoomId = roomId;
    document.getElementById('deleteRoomNum').textContent      = roomNum;
    document.getElementById('deleteRoomError').style.display  = 'none';
    document.getElementById('deleteRoomModal').classList.add('open');
}
['closeDeleteRoomModal','cancelDeleteRoom'].forEach(id =>
    document.getElementById(id).addEventListener('click',
        () => document.getElementById('deleteRoomModal').classList.remove('open')));

document.getElementById('confirmDeleteRoom').addEventListener('click', async function () 
{
    const errEl = document.getElementById('deleteRoomError');
    errEl.style.display = 'none';
    try {
        await apiFetch(`${BASE}/api/admin.php?action=delete_room`, {
            method:'POST', body:{ room_id: deleteRoomId }
        });
        document.getElementById('deleteRoomModal').classList.remove('open');
        showToast('Room deleted.', 'success');
        loadRooms();
    } catch (err) {
        errEl.textContent   = err.message;
        errEl.style.display = 'block';
    }
});

loadRoomTypes();
loadRooms();
</script>

<style>
.sortable:hover { background:rgba(0,0,0,.04); }
.sort-icon { opacity:.5; font-size:.8em; }
.room-tab:hover { color: var(--charcoal) !important; }
.room-tab.active { border-bottom-color: var(--gold) !important; color: var(--charcoal) !important; }
</style>