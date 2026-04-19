let currentUser = null;

// Register
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        try {
            const res = await fetch('register.php', { method: 'POST', body: formData });
            const data = await res.json();
            showMessage(data.message || data.errors?.[0], data.status);
            if (data.status === 'success') registerForm.reset();
        } catch {
            showMessage('Network error', 'error');
        }
    });
}

// Login
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        try {
            const res = await fetch('login.php', { method: 'POST', body: formData });
            const data = await res.json();
            showMessage(data.message, data.status);
            if (data.status === 'success') {
                currentUser = { id: data.user_id, name: data.message.split(', ')[1]?.replace('!', '') };
                showDashboard();
            }
        } catch {
            showMessage('Login failed', 'error');
        }
    });
}

// Update Profile
const updateForm = document.getElementById('updateForm');
if (updateForm) {
    updateForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(updateForm);
        try {
            const res = await fetch('update_profile.php', { method: 'POST', body: formData });
            const data = await res.json();
            showMessage(data.message, data.status);
        } catch {
            showMessage('Update failed', 'error');
        }
    });
}

// Delete Account
const deleteForm = document.getElementById('deleteForm');
if (deleteForm) {
    deleteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(deleteForm);
        try {
            const res = await fetch('delete_account.php', { method: 'POST', body: formData });
            const data = await res.json();
            showMessage(data.message, data.status);
        } catch {
            showMessage('Delete failed', 'error');
        }
    });
}

// Dashboard toggle
function showDashboard() {
    document.getElementById('dashboard').style.display = 'block';
    document.getElementById('user-id').textContent = currentUser.id;
    document.getElementById('user-name').textContent = currentUser.name;
}

// Message display
function showMessage(msg, status, target = 'global-message') {
    const el = document.getElementById(target);
    if (!el) return;
    el.textContent = msg;
    el.className = status === 'success' ? 'success' : 'error';
}