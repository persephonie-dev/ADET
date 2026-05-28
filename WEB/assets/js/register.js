document.getElementById('registerBtn')?.addEventListener('click', async function () {
    const btn       = this;
    const errBanner = document.getElementById('regError');
    errBanner.style.display = 'none';

    const firstName    = document.getElementById('firstName').value.trim();
    const middleName   = document.getElementById('middleName').value.trim();
    const lastName     = document.getElementById('lastName').value.trim();
    const dob          = document.getElementById('dob').value.trim();
    const email        = document.getElementById('email').value.trim();
    const phone        = document.getElementById('phone').value.trim();
    const streetAdr    = document.getElementById('streetAdr').value.trim();
    const city         = document.getElementById('city').value.trim();
    const region       = document.getElementById('region').value.trim();
    const password     = document.getElementById('password').value;
    const passConfirm  = document.getElementById('passwordConfirm').value;

    if (!firstName || !lastName || !dob || !email || !streetAdr || !city || !region || !password) 
    {
        errBanner.textContent  = 'All required fields must be filled in.';
        errBanner.style.display = 'block';
        return;
    }
    if (password !== passConfirm) {
        errBanner.textContent  = 'Passwords do not match.';
        errBanner.style.display = 'block';
        return;
    }
    if (password.length < 8) {
        errBanner.textContent  = 'Password must be at least 8 characters.';
        errBanner.style.display = 'block';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Creating account...';

    try {
        await apiFetch(`${CONFIG.baseUrl}/api/auth.php?action=register`, {
            method: 'POST',
            body: {
                first_name:   firstName,
                middle_name:  middleName,
                last_name:    lastName,
                DOB:          dob,
                street_adr:   streetAdr,
                city:         city,
                region:       region,
                email:        email,
                password:     password,
                phone_number: phone
            }
        });
        window.location.href = `${CONFIG.baseUrl}/profile.php`;
    } catch (err) {
        errBanner.textContent  = err.message;
        errBanner.style.display = 'block';
        btn.disabled    = false;
        btn.textContent = 'Create Account';
    }
});