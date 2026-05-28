document.getElementById('check_in').min  = todayISO();
document.getElementById('check_out').min = todayISO();


document.getElementById('check_in').addEventListener('change', function () 
{
    const nextDay = new Date(this.value);
    nextDay.setDate(nextDay.getDate() + 1);
    document.getElementById('check_out').min   = nextDay.toISOString().slice(0, 10);
    document.getElementById('check_out').value = '';
});

const _rcImages = {};
let   _rcCount  = 0;

function buildCardImageHtml(images, price, altText) {
    const badge = `<span class="room-card-badge">From ${formatPHP(price)}</span>`;
    if (!images || !images.length) {
        return `<div class="no-img">Photo coming soon</div>${badge}`;
    }
    const id    = 'rc-' + (_rcCount++);
    _rcImages[id] = images;
    const multi = images.length > 1;

    const arrows = multi ? `
        <button class="slide-btn slide-prev"
                onclick="event.preventDefault();event.stopPropagation();rcSlide('${id}',-1)">&#8249;</button>
        <button class="slide-btn slide-next"
                onclick="event.preventDefault();event.stopPropagation();rcSlide('${id}',1)">&#8250;</button>` : '';

    const dots = multi ? `
        <div class="slide-dots">
            ${images.map((_, i) =>
                `<span class="slide-dot${i === 0 ? ' active' : ''}"
                       onclick="event.preventDefault();event.stopPropagation();rcGoTo('${id}',${i})"></span>`
            ).join('')}
        </div>` : '';

    const counter = multi ? `<span class="slide-counter">1 / ${images.length}</span>` : '';

    return `
        <img id="${id}" class="card-slide-img"
             src="${images[0].url}" alt="${images[0].caption || altText}"
             loading="lazy" data-idx="0">
        ${arrows}${dots}${counter}${badge}
    `;
}

function rcGoTo(id, idx) 
{
    const imgs = _rcImages[id];
    if (!imgs) return;
    idx = ((idx % imgs.length) + imgs.length) % imgs.length;
    const img = document.getElementById(id);
    if (!img) return;
    img.dataset.idx   = idx;
    img.style.opacity = '0';
    setTimeout(() => { img.src = imgs[idx].url; img.alt = imgs[idx].caption || ''; img.style.opacity = '1'; }, 130);
    const wrap = img.closest('.room-card-img');

    if (wrap)
         {
        wrap.querySelectorAll('.slide-dot').forEach((d, i) => d.classList.toggle('active', i === idx));
        const ctr = wrap.querySelector('.slide-counter');
        if (ctr) ctr.textContent = (idx + 1) + ' / ' + imgs.length;
    }
}
function rcSlide(id, dir) 
{
    const img = document.getElementById(id);
    rcGoTo(id, parseInt(img?.dataset.idx || '0') + dir);
}


async function loadRooms() 
{
    try 
    {
        const rooms = await apiFetch('<?= BASE_URL ?>/api/rooms.php');
        const grid  = document.getElementById('roomsGrid');

        if (!rooms.length) 
            {
            grid.innerHTML = '<p class="text-muted text-center" style="grid-column:1/-1;">No rooms available right now.</p>';
            return;
        }

        grid.innerHTML = rooms.slice(0, 3).map(room => {
            const images = room.images || (room.thumbnail ? [{ url: room.thumbnail, caption: '' }] : []);
            const price  = room.from_price || room.base_price;
            return `
                <div class="room-card">
                    <div class="room-card-img">
                        ${buildCardImageHtml(images, price, room.type_name)}
                    </div>
                    <div class="room-card-body">
                        <div class="room-card-type">${room.bed_type || 'Standard Suite'}</div>
                        <h3 class="room-card-name">${room.type_name}</h3>
                        <p class="room-card-desc">Experience refined relaxation combined with premium architectural design elements.</p>
                        <div class="room-card-meta">
                            <span>Capacity: Up to ${room.max_capacity} guests</span>
                        </div>
                        <div class="room-card-price">
                            <span class="price-amount">${formatPHP(price)}</span>
                            <span class="price-unit">/ night</span>
                        </div>
                        <a href="rooms.php?type=${room.room_type_id}" class="btn btn-dark btn-block mt-3"
                           style="width:100%; text-align:center; margin-top:1rem;">View Details</a>
                    </div>
                </div>
            `;
        }).join('');

    } catch (err) 
    {
        document.getElementById('roomsGrid').innerHTML =
            '<p class="text-muted text-center" style="grid-column:1/-1;">Could not load rooms.</p>';
    }
}


async function loadReviews() 
{
    try {
        const reviews = await apiFetch('<?= BASE_URL ?>/api/reviews.php');
        const grid    = document.getElementById('reviewsGrid');

        document.getElementById('reviewCount').textContent = reviews.length || '--';

        if (!reviews.length)
        {
            grid.innerHTML = '<p class="text-muted text-center" style="grid-column:1/-1;">No reviews yet.</p>';
            return;
        }

        grid.innerHTML = reviews.slice(0, 3).map(rv => `
            <div class="review-card">
                <div class="review-stars">
                    ${'★'.repeat(rv.rating)}${'☆'.repeat(5 - rv.rating)}
                </div>
                ${rv.title ? `<h4 style="margin-bottom:0.4rem; font-family:var(--font-display); font-size:1.25rem;">${rv.title}</h4>` : ''}
                <p class="review-comment">"${rv.comment || ''}"</p>
                <div class="review-author">
                    <strong>${rv.guest_name}</strong> &mdash; ${rv.room_type}
                </div>
            </div>
        `).join('');

    } catch (err) {
        document.getElementById('reviewsGrid').innerHTML =
            '<p class="text-muted text-center" style="grid-column:1/-1;">Could not load reviews.</p>';
    }
}

loadRooms();
loadReviews();
