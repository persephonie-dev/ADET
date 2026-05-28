(function () 
{
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    function updateNavbar() {
        if (window.scrollY > 60) 
        {
            navbar.classList.add('scrolled');
        } else 
        {
            navbar.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', updateNavbar, { passive: true });
    updateNavbar();
})();



  
window.showToast = function (message, type = 'success', duration = 4000) 
{
   
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => toast.classList.add('show'));
    });

   
    setTimeout(() =>
    {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
    }, duration);
};



    
(function () 
{
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);

    window.showLoading = () => overlay.classList.add('show');
    window.hideLoading = () => overlay.classList.remove('show');
})();



window.apiFetch = async function (url, options = {}) 
{
    const init = 
    {
        method:  options.method || 'GET',
        headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    };

    if (options.body) 
        {
        init.body = JSON.stringify(options.body);
    }

    const response = await fetch(url, init);

    
    const contentType = response.headers.get('Content-Type') || '';
    if (!contentType.includes('application/json')) 
    {
        const text = await response.text();
        console.error('Non-JSON response from', url, ':', text.slice(0, 200));
        throw new Error(`Server error: expected JSON but got ${contentType || 'unknown content type'} (HTTP ${response.status})`);
    }

    const data = await response.json();

    if (!response.ok) 
    {
        throw new Error(data.error || `HTTP ${response.status}`);
    }

    return data;
};



/**
 * @param {HTMLElement} input 
 * @param {string}      msg  
 */
window.setFieldError = function (input, msg) 
{
    const errorEl = input.parentElement.querySelector('.form-error');
    input.style.borderColor = 'var(--danger)';
    if (errorEl) 
    {
        errorEl.textContent = msg;
        errorEl.classList.add('visible');
    }
};

/**
 * clear all field errors inside a form.
 * @param {HTMLFormElement|HTMLElement} form
 */
window.clearFormErrors = function (form) {
    form.querySelectorAll('.form-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('visible');
    });
    form.querySelectorAll('.form-control').forEach(el => {
        el.style.borderColor = '';
    });
};



/**

 * @param {string} dateStr — ISO date string (YYYY-MM-DD)
 */
window.formatDate = function (dateStr) 
{
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00'); // force local time
    return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
};


window.todayISO = function () {
    return new Date().toISOString().slice(0, 10);
};


window.calcNights = function (checkIn, checkOut) 
{
    return Math.round((new Date(checkOut) - new Date(checkIn)) / 86400000);
};


window.formatPHP = function (amount) 
{
    return 'PHP ' + Number(amount).toLocaleString('en-PH', 
    {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};



window.statusBadgeClass = function (status) 
{
    const map = 
    {
        'Pending':     'badge-pending',
        'Confirmed':   'badge-confirmed',
        'Checked In':  'badge-checkedin',
        'Checked Out': 'badge-checkedout',
        'Cancelled':   'badge-cancelled',
        'No Show':     'badge-noshow',
    };
    return 'badge ' + (map[status] || 'badge-pending');
};