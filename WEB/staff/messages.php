<?php


    $pageTitle = 'Messages';
    $activeNav = 'messages';
    require_once __DIR__ . '/../includes/staff_header.php';
    ?>


    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>Contact Messages
                <span id="msgCount" class="sidebar-badge" style="margin-left:.5rem;"></span>
            </h3>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <select id="filterStatus" class="form-control" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="New">New</option>
                    <option value="Read">Read</option>
                    <option value="Replied">Replied</option>
                </select>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="msgBody">
                    <tr><td colspan="7" class="text-muted text-center" style="padding:2rem;">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>


    <div id="msgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:2rem;max-width:580px;width:90%;max-height:90vh;overflow-y:auto;position:relative;">
            <button onclick="closeModal()" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.4rem;cursor:pointer;">&times;</button>
            <h3 id="modalSubject" style="margin-bottom:.25rem;"></h3>
            <p style="color:#666;font-size:.85rem;margin-bottom:1rem;">
                From: <strong id="modalName"></strong> &lt;<span id="modalEmail"></span>&gt;
                &nbsp;·&nbsp; <span id="modalDate"></span>
            </p>
            <div id="modalMessage" style="background:#f8f8f8;padding:1rem;border-radius:6px;white-space:pre-wrap;line-height:1.6;"></div>
            <div style="margin-top:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;">
                <button id="modalMarkRead"    class="btn btn-outline btn-sm"   onclick="updateStatus(currentMsgId,'Read')">Mark as Read</button>
                <button id="modalMarkReplied" class="btn btn-primary btn-sm"   onclick="updateStatus(currentMsgId,'Replied')">Mark as Replied</button>
                <a      id="modalReplyLink"   class="btn btn-outline btn-sm"   href="#" target="_blank">Reply via Email</a>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/staff_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';
    let allMessages  = [];
    let currentMsgId = null;

    async function loadMessages() 
    {
        try 
        {
            allMessages = await apiFetch(`${BASE}/api/staff.php?action=messages`);
            renderTable(allMessages);
        } catch (err) {
            document.getElementById('msgBody').innerHTML =
                `<tr><td colspan="7" class="alert alert-error">${err.message}</td></tr>`;
        }
    }

    function renderTable(rows) 
    {
        const filter = document.getElementById('filterStatus').value;
        const list   = filter ? rows.filter(m => m.message_status === filter) : rows;

      
        const newCount = rows.filter(m => m.message_status === 'New').length;
        document.getElementById('msgCount').textContent = newCount || '';

        if (!list.length) 
        {
            document.getElementById('msgBody').innerHTML =
                `<tr><td colspan="7" class="text-muted text-center" style="padding:1.5rem;">No messages found.</td></tr>`;
            return;
        }

        document.getElementById('msgBody').innerHTML = list.map(m => `
            <tr style="${m.message_status === 'New' ? 'font-weight:600;' : ''}">
                <td>${m.message_id}</td>
                <td>${escHtml(m.name || '—')}</td>
                <td>${escHtml(m.email)}</td>
                <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    title="${escHtml(m.subject)}">${escHtml(m.subject)}</td>
                <td>${formatDate(m.created_at)}</td>
                <td><span class="${msgBadgeClass(m.message_status)}">${m.message_status}</span></td>
                <td>
                    <button class="btn btn-outline btn-sm" onclick="openModal(${m.message_id})">View</button>
                </td>
            </tr>
        `).join('');
    }

    function openModal(id) 
    {
        const m = allMessages.find(x => x.message_id == id);
        if (!m) return;
        currentMsgId = id;

        document.getElementById('modalSubject').textContent  = m.subject;
        document.getElementById('modalName').textContent     = m.name || '(no name)';
        document.getElementById('modalEmail').textContent    = m.email;
        document.getElementById('modalDate').textContent     = formatDate(m.created_at);
        document.getElementById('modalMessage').textContent  = m.message;
        document.getElementById('modalReplyLink').href       = `mailto:${m.email}?subject=Re: ${encodeURIComponent(m.subject)}`;

        document.getElementById('modalMarkRead').style.display    = m.message_status !== 'Read'    ? '' : 'none';
        document.getElementById('modalMarkReplied').style.display = m.message_status !== 'Replied' ? '' : 'none';

     
        if (m.message_status === 'New') updateStatus(id, 'Read', false);

        document.getElementById('msgModal').style.display = 'flex';
    }

    function closeModal() 
    {
        document.getElementById('msgModal').style.display = 'none';
        currentMsgId = null;
    }

    async function updateStatus(id, status, showToast = true)
    {
        try 
        {
            await apiFetch(`${BASE}/api/staff.php?action=update_message`, 
            {
                method: 'POST',
                body: { message_id: id, status }
            });
         
            const m = allMessages.find(x => x.message_id == id);
            if (m) m.message_status = status;
            renderTable(allMessages);
            if (showToast) 
            {
                showToast(`Message marked as ${status}.`, 'success');
                closeModal();
            }
        } catch (err)
        {
            showToast(err.message, 'error');
        }
    }


    function msgBadgeClass(s)
    {
        if (s === 'New')     return 'badge badge-warning';
        if (s === 'Replied') return 'badge badge-success';
        return 'badge badge-secondary';
    }
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function formatDate(d) {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
    }

    document.getElementById('filterStatus').addEventListener('change', () => renderTable(allMessages));

    document.getElementById('msgModal').addEventListener('click', e => 
    {
        if (e.target === document.getElementById('msgModal')) closeModal();
    });

    loadMessages();
</script>