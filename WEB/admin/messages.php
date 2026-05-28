<?php


    $pageTitle = 'Contact Messages';
    $activeNav = 'messages';
    require_once __DIR__ . '/../includes/admin_header.php';
    ?>

    <div class="admin-table-wrap">
        <div class="admin-table-header">
            <h3>Contact Messages</h3>
        </div>
        <div id="messagesWrap">
            <p class="text-muted text-center" style="padding:2rem;">Loading...</p>
        </div>
    </div>


    <div class="modal-overlay" id="msgModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="msgModalSubject">Message</h3>
                <button class="modal-close" id="closeMsgModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.82rem;color:var(--stone);margin-bottom:1rem;">
                    From: <strong id="msgFrom"></strong> &lt;<a id="msgEmail" href="#"></a>&gt;
                    &mdash; <span id="msgDate"></span>
                </p>
                <p id="msgBody" style="white-space:pre-wrap;line-height:1.7;"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline btn-sm" id="markReplied">Mark as Replied</button>
                <button class="btn btn-dark btn-sm" id="markRead">Mark as Read</button>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<script>
    const BASE = '<?= BASE_URL ?>';
    let currentMsgId = null;

    function msgStatusBadge(status) 
    {
        if (status === 'New')     return 'badge badge-pending';
        if (status === 'Read')    return 'badge badge-checkedout';
        if (status === 'Replied') return 'badge badge-confirmed';
        return 'badge badge-pending';
    }

    async function loadMessages() 
    {
        const wrap = document.getElementById('messagesWrap');
        try {
            const messages = await apiFetch(`${BASE}/api/admin.php?action=messages`);
            if (!messages.length) 
            {
                wrap.innerHTML = '<p class="text-muted text-center" style="padding:2rem;">No messages yet.</p>';
                return;
            }
            wrap.innerHTML = `
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>From</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Received</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${messages.map(m => `
                            <tr style="${m.message_status === 'New' ? 'font-weight:700;' : ''}">
                                <td>#${m.message_id}</td>
                                <td>${m.name || 'Anonymous'}</td>
                                <td><a href="mailto:${m.email}">${m.email}</a></td>
                                <td>${m.subject}</td>
                                <td>${m.created_at ? formatDate(m.created_at.split(' ')[0]) : 'N/A'}</td>
                                <td><span class="${msgStatusBadge(m.message_status)}">${m.message_status}</span></td>
                                <td>
                                    <button class="btn btn-outline btn-sm"
                                            onclick="openMsg(${m.message_id}, \`${(m.subject||'').replace(/`/g,"'")}\`, \`${(m.name||'Anonymous').replace(/`/g,"'")}\`, '${m.email}', \`${(m.message||'').replace(/`/g,"'")}\`, '${m.created_at}')">
                                        View
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } catch (err) 
        {
            wrap.innerHTML = `<p class="alert alert-error" style="margin:1rem;">${err.message}</p>`;
        }
    }

    function openMsg(id, subject, name, email, body, date) {
        currentMsgId = id;
        document.getElementById('msgModalSubject').textContent = subject;
        document.getElementById('msgFrom').textContent         = name;
        
        const emailLink = document.getElementById('msgEmail');
        emailLink.href        = 'mailto:' + email;
        emailLink.textContent = email;
        

        document.getElementById('msgDate').textContent = date ? formatDate(date.split(' ')[0]) : 'N/A';
        
        document.getElementById('msgBody').textContent = body;
        document.getElementById('msgModal').classList.add('open');

    
        updateMsg(id, 'Read', false);
    }

    async function updateMsg(msgId, status, reload = true) {
        try {
            await apiFetch(`${BASE}/api/admin.php?action=update_message`, {
                method: 'POST',
                body: { message_id: msgId, status }
            });
            if (reload) {
                document.getElementById('msgModal').classList.remove('open');
                showToast('Message marked as ' + status.toLowerCase() + '.', 'success');
                loadMessages();
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    document.getElementById('closeMsgModal').addEventListener('click', () => document.getElementById('msgModal').classList.remove('open'));
    document.getElementById('markRead').addEventListener('click',    () => updateMsg(currentMsgId, 'Read'));
    document.getElementById('markReplied').addEventListener('click', () => updateMsg(currentMsgId, 'Replied'));

    loadMessages();
</script>