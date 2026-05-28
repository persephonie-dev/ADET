<?php

    $pageTitle = 'Welcome';
    $bodyClass = 'has-hero'; 

    require_once __DIR__ . '/includes/header.php';
    ?>

    <section class="hero">
    <!--Fix this 
        <div class="hero-bg" style="/* background-image: url('assets/img/hero.jpg'); background-size: cover; background-position: center; */"></div>-->
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="section-label">Legazpi City, Albay, Philippines</span>
            <h1 class="hero-title">Where <em>Every Stay</em> Becomes a Memory</h1>
            <p class="hero-subtitle">Experience refined hospitality at the foot of Mayon Volcano. Relax, explore, and feel at home at The Pepperland Hotel.</p>
            <div class="hero-cta">
                <a href="#availability" class="btn btn-gold">Check Availability</a>
                <a href="rooms.php" class="btn btn-outline">View Rooms</a>
            </div>
        </div>
    </section>

<!--Quickbook-->
    <div class="container" id="availability">

        <form class="quick-book" id="availabilityForm" action="rooms.php" method="GET">
            <div class="qb-field">
                <label for="check_in">Check-in</label>
                <input type="date" name="check_in" id="check_in" required>
            </div>
            <div class="qb-field">
                <label for="check_out">Check-out</label>
                <input type="date" name="check_out" id="check_out" required>
            </div>

            <div class="qb-field">
                <label for="guests">Guests</label>
                <select name="guests" id="guests">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> Guests</option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-gold">Search Rooms</button>
        </form>
    </div>

   
    <section class="section">
        <div class="container">
            <div class="center mb-4">
                <span class="section-label">Accommodation</span>
                <h2>Our Rooms &amp; Suites</h2>
                <div class="gold-line center"></div>
                <p class="text-muted">Each room is thoughtfully designed to blend comfort with the warmth of Filipino hospitality.</p>
            </div>

           
            <div class="rooms-grid" id="roomsGrid">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="room-card">
                        <div class="room-card-img">
                            <div class="loader loader-dark"></div>
                        </div>
                        <div class="room-card-body">
                            <div class="skeleton" style="height:1rem; width:40%; margin-bottom:0.5rem;"></div>
                            <div class="skeleton" style="height:1.5rem; width:70%; margin-bottom:0.5rem;"></div>
                            <div class="skeleton" style="height:3rem; width:100%; margin-bottom:1rem;"></div>
                            <div class="skeleton" style="height:2rem; width:40%;"></div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="text-center" style="margin-top: 3rem;">
                <a href="rooms.php" class="btn btn-dark">View All Rooms</a>
            </div>

        </div>
    </section>

   
    <section class="section" style="background: var(--forest); color: var(--cream);">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 3rem; text-align: center;">
                <div>
                    <h3 style="color: var(--gold); font-size: 3rem; font-family: var(--font-display);">4.5</h3>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em;">Star Rated Hotel</p>
                </div>
                <div>
                    <h3 style="color: var(--gold); font-size: 3rem; font-family: var(--font-display);" id="reviewCount">--</h3>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em;">Guest Reviews</p>
                </div>
                <div>
                    <h3 style="color: var(--gold); font-size: 3rem; font-family: var(--font-display);">24/7</h3>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em;">Front Desk Service</p>
                </div>
            </div>
        </div>
    </section>


    <section class="section section-alt">
        <div class="container">
            <div class="center mb-4">
                <span class="section-label">Guest Feedback</span>
                <h2>What Our Guests Say</h2>
                <div class="gold-line center"></div>
            </div>
            <div class="reviews-grid" id="reviewsGrid">
                <p class="text-muted text-center" style="grid-column: 1/-1;">Loading reviews...</p>
            </div>
        </div>
    </section>

    <section class="section" style="background: var(--forest-mid); color: var(--white); text-align: center;">
        <div class="container">
            <span class="section-label" style="color: var(--gold-light);">Limited Availability</span>
            <h2 style="color: var(--white); margin-bottom: 1rem;">Ready to Experience Pepperland?</h2>
            <p class="text-muted" style="max-width: 480px; margin: 0.75rem auto 2rem; color: rgba(255,255,255,0.7);">
                Book directly with us for the best rates and personalized service.
            </p>
            <a href="rooms.php" class="btn btn-gold">Book Your Stay</a>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/index.css">


<script>
// date set to present date as default minimum value
document.getElementById('check_in').min  = todayISO();
document.getElementById('check_out').min = todayISO();

// date is set to next day as default 
document.getElementById('check_in').addEventListener('change', function () 
{
    const nextDay = new Date(this.value);
    nextDay.setDate(nextDay.getDate() + 1);
    document.getElementById('check_out').min   = nextDay.toISOString().slice(0, 10);
    document.getElementById('check_out').value = '';
});

const _rcImages = {};
let   _rcCount  = 0;

function buildCardImageHtml(images, price, altText) 
{
    const badge = `<span class="room-card-badge">From ${formatPHP(price)}</span>`;
    if (!images || !images.length) 
    {
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
    if (wrap) {
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
    try {
        const rooms = await apiFetch('<?= BASE_URL ?>/api/rooms.php');
        const grid  = document.getElementById('roomsGrid');

        if (!rooms.length) {
            grid.innerHTML = '<p class="text-muted text-center" style="grid-column:1/-1;">No rooms available right now.</p>';
            return;
        }

        grid.innerHTML = rooms.slice(0, 3).map(room => 
        {
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
    try 
    {
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

    } catch (err) 
    {
        document.getElementById('reviewsGrid').innerHTML =
            '<p class="text-muted text-center" style="grid-column:1/-1;">Could not load reviews.</p>';
    }
}

loadRooms();
loadReviews();
</script>