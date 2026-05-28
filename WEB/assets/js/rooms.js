
(function () 
{
    if (document.getElementById('rc-carousel-css')) return;
    const s = document.createElement('style');
    s.id = 'rc-carousel-css';
    s.textContent = `
        /* Card image area */
        .room-card-img { position: relative; overflow: hidden; }

        /* Slide buttons */
        .slide-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(0,0,0,.45); color: #fff; border: none;
            font-size: 1.5rem; line-height: 1; padding: .2rem .55rem;
            border-radius: 4px; cursor: pointer; z-index: 3;
            opacity: 0; transition: opacity .18s;
            -webkit-user-select: none; user-select: none;
        }
        .room-card-img:hover .slide-btn { opacity: 1; }
        .slide-btn:hover { background: rgba(0,0,0,.7); }
        .slide-prev { left: .4rem; }
        .slide-next { right: .4rem; }

        /* Dot strip */
        .slide-dots {
            position: absolute; bottom: .55rem; left: 50%;
            transform: translateX(-50%);
            display: flex; gap: .3rem; z-index: 3;
        }
        .slide-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: rgba(255,255,255,.5);
            transition: background .15s, transform .15s;
            cursor: pointer;
        }
        .slide-dot.active {
            background: #fff;
            transform: scale(1.25);
        }

        /* Counter badge */
        .slide-counter {
            position: absolute; top: .5rem; right: .5rem;
            background: rgba(0,0,0,.5); color: #fff;
            font-size: .68rem; border-radius: 3px;
            padding: .15rem .4rem; z-index: 3; pointer-events: none;
        }

        /* Main slide image */
        .card-slide-img {
            width: 100%; height: 100%; object-fit: cover;
            transition: opacity .14s; display: block;
        }
    `;
    document.head.appendChild(s);
}());


const _rcImages = {};   
let   _rcCount  = 0;


function buildCardImageHtml(images, price, altText) 
{
    const badge = `<span class="room-card-badge">From ${formatPHP(price)}</span>`;

    if (!images || !images.length) 
        {
        return `<div class="no-img">Awaiting visual selection</div>${badge}`;
    }

    const id    = 'rc-' + (_rcCount++);
    _rcImages[id] = images;
    const multi = images.length > 1;

    const dots = multi
        ? `<div class="slide-dots">
               ${images.map((_, i) =>
                   `<span class="slide-dot${i === 0 ? ' active' : ''}"
                          onclick="event.preventDefault();event.stopPropagation();rcDot('${id}',${i})"></span>`
               ).join('')}
           </div>`
        : '';

    const arrows = multi
        ? `<button class="slide-btn slide-prev"
                   onclick="event.preventDefault();event.stopPropagation();rcSlide('${id}',-1)">&#8249;</button>
           <button class="slide-btn slide-next"
                   onclick="event.preventDefault();event.stopPropagation();rcSlide('${id}',1)">&#8250;</button>`
        : '';

    const counter = multi
        ? `<span class="slide-counter">1 / ${images.length}</span>`
        : '';

    return `
        <img id="${id}"
             class="card-slide-img"
             src="${images[0].url}"
             alt="${images[0].caption || altText}"
             loading="lazy"
             data-idx="0">
        ${arrows}
        ${dots}
        ${counter}
        ${badge}
    `;
}


function rcGoTo(id, idx) 
{
    const imgs = _rcImages[id];
    if (!imgs) return;
    idx = ((idx % imgs.length) + imgs.length) % imgs.length;

    const img = document.getElementById(id);
    if (!img) return;
    img.dataset.idx  = idx;
    img.style.opacity = '0';
    setTimeout(() => {
        img.src          = imgs[idx].url;
        img.alt          = imgs[idx].caption || '';
        img.style.opacity = '1';
    }, 130);


    const wrap = img.closest('.room-card-img');
    if (wrap) {
        wrap.querySelectorAll('.slide-dot').forEach((d, i) =>
            d.classList.toggle('active', i === idx));
        const ctr = wrap.querySelector('.slide-counter');
        if (ctr) ctr.textContent = (idx + 1) + ' / ' + imgs.length;
    }
}

function rcSlide(id, dir) {
    const img = document.getElementById(id);
    const cur = parseInt(img?.dataset.idx || '0');
    rcGoTo(id, cur + dir);
}

function rcDot(id, idx) {
    rcGoTo(id, idx);
}


const checkInEl  = document.getElementById('check_in');
const checkOutEl = document.getElementById('check_out');

if (checkInEl && checkOutEl) {
    checkInEl.min  = todayISO();
    checkOutEl.min = todayISO();
    checkInEl.addEventListener('change', function () {
        const next = new Date(this.value);
        next.setDate(next.getDate() + 1);
        checkOutEl.min   = next.toISOString().slice(0, 10);
        checkOutEl.value = '';
    });
}

async function loadRooms() 
{
    const grid = document.getElementById('roomsGrid');
    let rooms;

    try 
    {
        if (CONFIG.checkIn && CONFIG.checkOut) 
        {
            rooms = await apiFetch(
                `${CONFIG.baseUrl}/api/rooms.php?action=available` +
                `&check_in=${CONFIG.checkIn}&check_out=${CONFIG.checkOut}&guests=${CONFIG.guests}`
            );
        } else {
            rooms = await apiFetch(`${CONFIG.baseUrl}/api/rooms.php`);
        }
    } catch (err) 
    {
        grid.innerHTML = `<p class="text-muted text-center" style="grid-column:1/-1;">${err.message}</p>`;
        return;
    }

    if (!rooms.length) 
    {
        grid.innerHTML = '';
        const msg = document.getElementById('noRoomsMsg');
        if (msg) msg.style.display = 'block';
        return;
    }

    function bookingUrl(id) 
    {
        let url = `booking.php?room_id=${id}`;
        if (CONFIG.checkIn && CONFIG.checkOut) {
            url += `&check_in=${CONFIG.checkIn}&check_out=${CONFIG.checkOut}&guests=${CONFIG.guests}`;
        }
        return url;
    }

    grid.innerHTML = rooms.map(room => 
    {
        const isRoom   = !!room.room_id;
        const id       = isRoom ? room.room_id : room.room_type_id;
        const name     = room.type_name;
        const price    = isRoom ? room.price_per_night : (room.from_price || room.base_price);
        const capacity = room.max_capacity;
        const bed      = room.bed_type || '';
        const desc     = room.description || 'Thoughtfully curated interior details optimised for a completely tailored hospitality retreat.';
        const images   = room.images || (room.thumbnail ? [{ url: room.thumbnail, caption: '' }] : []);

        return `
            <div class="room-card">
                <div class="room-card-img">
                    ${buildCardImageHtml(images, price, name)}
                </div>
                <div class="room-card-body">
                    <div class="room-card-type">${bed} &middot; Space for ${capacity}</div>
                    <h3 class="room-card-name">${name}${isRoom ? ' &mdash; Room ' + room.room_number : ''}</h3>
                    <p class="room-card-desc">${desc}</p>
                    <div class="room-card-price room-card-price-container">
                        <span class="price-amount">${formatPHP(price)}</span>
                        <span class="price-unit">/ night</span>
                    </div>
                    <a href="${bookingUrl(id)}" class="btn btn-dark btn-block room-card-action-btn">
                        ${CONFIG.checkIn ? 'Reserve Suite' : 'Check Open Dates'}
                    </a>
                </div>
            </div>
        `;
    }).join('');
}

loadRooms();