const loginBtn    = document.getElementById('loginBtn');
const errorBanner = document.getElementById('loginError');

async function doLogin() 
{
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    errorBanner.style.display = 'none';

    if (!email || !password) {
        errorBanner.textContent  = 'Please map active keys inside each field before submission.';
        errorBanner.style.display = 'block';
        return;
    }

    loginBtn.disabled    = true;
    loginBtn.textContent = 'Authenticating tokens...';

    try {
        const data = await apiFetch(`${CONFIG.baseUrl}/api/auth.php?action=login`, {
            method: 'POST',
            body: { email, password }
        });

        if (data.role_id === 1) 
        {
            window.location.href = `${CONFIG.baseUrl}/admin/dashboard.php`;
        } 
        else if (data.role_id === 3) 
        {
            window.location.href = `${CONFIG.baseUrl}/staff/dashboard.php`;
        } else {
            window.location.href = `${CONFIG.baseUrl}/profile.php`;
        }
    } catch (err) {
        errorBanner.textContent  = err.message;
        errorBanner.style.display = 'block';
        loginBtn.disabled    = false;
        loginBtn.textContent = 'Access Profile';
    }
}

if (loginBtn) {
    loginBtn.addEventListener('click', doLogin);
}
document.getElementById('email')?.addEventListener('keydown', e => e.key === 'Enter' && doLogin());
document.getElementById('password')?.addEventListener('keydown', e => e.key === 'Enter' && doLogin());