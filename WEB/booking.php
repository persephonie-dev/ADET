<?php

require_once __DIR__ . '/api/config.php';
require_login();

    $roomId   = (int)($_GET['room_id']    ?? 0);
    $checkIn  = $_GET['check_in']         ?? '';
    $checkOut = $_GET['check_out']        ?? '';
    $guests   = (int)($_GET['guests']     ?? 1);

    if (!$roomId) 
    {
        header('Location: ' . BASE_URL . '/rooms.php');
        exit;
    }
    //room  type annd information
    $stmt = $pdo->prepare("
        SELECT r.room_id, r.room_number, r.price_per_night,
            r.capacity, r.description,
            rt.room_type_id, rt.type_name, rt.max_capacity, rt.bed_type, rt.description AS type_description
        FROM  rooms r
        JOIN  room_types rt ON r.room_type_id = rt.room_type_id
        WHERE r.room_id = ?
    ");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        header('Location: ' . BASE_URL . '/rooms.php');
        exit;
    }


    $imgStmt = $pdo->prepare("
        SELECT room_image_id, image_url, caption
        FROM   room_images
        WHERE  room_type_id = ?
        ORDER  BY display_order ASC, room_image_id ASC
    ");
    $imgStmt->execute([$room['room_type_id']]);
    $images = $imgStmt->fetchAll();


    foreach ($images as &$img) 
    {
        $img['full_url'] = preg_match('/^https?:\/\//', $img['image_url'])
            ? $img['image_url']
            : rtrim(BASE_URL, '/') . '/' . ltrim($img['image_url'], '/');
    }
    unset($img);


    $amenStmt = $pdo->prepare("
        SELECT a.amenity_name, a.description
        FROM   room_amenities ra
        JOIN   amenities      a ON ra.amenity_id = a.amenity_id
        WHERE  ra.room_id = ?
        ORDER  BY a.amenity_name ASC
    ");
    $amenStmt->execute([$roomId]);
    $amenities = $amenStmt->fetchAll();

    $pageTitle = 'Book ' . $room['type_name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section" style="display:grid; grid-template-columns:1fr 400px; gap:4rem; align-items:start; margin-top: 4rem; margin-bottom: 4rem;">

    <div>
        <span class="section-label">Reservation Details</span>
        <h2 style="font-size:2.5rem; margin-bottom:0.5rem;"><?= h($room['type_name']) ?></h2>
        <p class="text-muted" style="margin-bottom:3rem; font-size:0.9rem; letter-spacing:0.05em; text-transform:uppercase;">
            Suite <?= h($room['room_number']) ?> &middot; <?= h($room['bed_type']) ?> &middot; Maximum <?= (int)$room['max_capacity'] ?> Guests
        </p>

        <div id="bookingError" class="text-muted"
             style="display:none; color:var(--danger); margin-bottom:2rem;
                    border-left:2px solid var(--danger); padding-left:1rem;"></div>

        <!-- Dates -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <div class="qb-field">
                <label for="checkIn">Check-in Date</label>
                <input type="date" id="checkIn" value="<?= h($checkIn) ?>" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="qb-field">
                <label for="checkOut">Check-out Date</label>
                <input type="date" id="checkOut" value="<?= h($checkOut) ?>" required>
            </div>
        </div>

        <!-- Guests -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <div class="qb-field">
                <label for="adults">Adults</label>
                <select id="adults">
                    <?php for ($i = 1; $i <= (int)$room['max_capacity']; $i++): ?>
                        <option value="<?= $i ?>" <?= $guests === $i ? 'selected' : '' ?>><?= $i ?> Adults</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="qb-field">
                <label for="children">Children</label>
                <select id="children">
                    <?php for ($i = 0; $i <= 4; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> Children</option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <!-- Special requests -->
        <div class="qb-field" style="margin-bottom:1.5rem;">
            <label for="specialRequests">Special Considerations (Optional)</label>
            <textarea id="specialRequests" rows="3"
                      style="width:100%; padding:0.8rem; border:1px solid var(--cream-dark);
                             background:var(--white); font-family:var(--font-body);
                             border-radius:var(--radius); outline:none;"
                      placeholder=" early arrival requests, dietary settings, or pillow preferences..."></textarea>
        </div>

        <!-- Promo code -->
        <div class="qb-field" style="margin-bottom:3rem;">
            <label for="promoCode">Promo Code</label>
            <div style="display:flex; gap:1rem;">
                <input type="text" id="promoCode" placeholder="Enter code" style="flex:1;">
                <button class="btn btn-outline" id="applyPromoBtn" type="button"
                        style="color:var(--forest); border-color:var(--forest-mid);">Apply</button>
            </div>
            <p id="promoMsg" style="font-size:0.85rem; margin-top:0.5rem; display:none; font-weight:500;"></p>
        </div>

        <button class="btn btn-gold" id="bookBtn" style="padding:1rem 3rem;">
            Confirm Suite Selection
        </button>
    </div>

 
    <div>

      
        <?php if (!empty($images)): ?>
        <div class="room-gallery" id="roomGallery">

            <!-- main image -->
            <div class="gallery-main" id="galleryMain"
                 style="position:relative; border-radius:var(--radius); overflow:hidden;
                        background:#111; cursor:zoom-in; margin-bottom:0.6rem;"
                 onclick="openLightbox(currentImg)">
                <img id="galleryMainImg"
                     src="<?= h($images[0]['full_url']) ?>"
                     alt="<?= h($images[0]['caption'] ?: $room['type_name']) ?>"
                     style="width:100%; height:260px; object-fit:cover; display:block;
                            transition:opacity .25s;">
                <!-- zoom hint -->
                <div style="position:absolute; top:.6rem; right:.6rem;
                            background:rgba(0,0,0,.55); color:#fff; border-radius:4px;
                            padding:.25rem .5rem; font-size:.7rem; pointer-events:none;">
                     Click to expand
                </div>
                <!-- image counter -->
                <div id="galleryCounter"
                     style="position:absolute; bottom:.6rem; right:.6rem;
                            background:rgba(0,0,0,.55); color:#fff; border-radius:4px;
                            padding:.25rem .5rem; font-size:.72rem; pointer-events:none;">
                    1 / <?= count($images) ?>
                </div>
            </div>

            <?php if (count($images) > 1): ?>
            
            <div style="display:flex; gap:.4rem; overflow-x:auto; padding-bottom:.25rem;">
                <?php foreach ($images as $i => $img): ?>
                <img src="<?= h($img['full_url']) ?>"
                     alt="<?= h($img['caption'] ?: ($room['type_name'] . ' ' . ($i + 1))) ?>"
                     data-index="<?= $i ?>"
                     onclick="setMainImage(<?= $i ?>)"
                     title="<?= h($img['caption'] ?: '') ?>"
                     class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                     style="width:64px; height:52px; object-fit:cover; border-radius:4px;
                            cursor:pointer; flex-shrink:0; border:2px solid transparent;
                            transition:border-color .15s, opacity .15s; opacity:<?= $i === 0 ? '1' : '.65' ?>;">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!--price calc -->
        <div style="background:var(--white); padding:2rem; border-radius:var(--radius);
                    box-shadow:var(--shadow-md); border-top:3px solid var(--gold);
                    margin-top:1.25rem;">

            <h3 style="margin-bottom:1.25rem; font-family:var(--font-display); font-size:1.5rem;">
                Price Breakdown
            </h3>

            <div style="display:flex; justify-content:space-between; margin-bottom:1rem; font-size:0.9rem;">
                <span class="text-muted">Standard Rate</span>
                <span style="font-weight:500; color:var(--forest);">
                    <?= h(number_format((float)$room['price_per_night'], 2)) ?> / night
                </span>
            </div>

            <div id="summaryNights"   style="display:none; justify-content:space-between; margin-bottom:1rem; font-size:0.9rem;">
                <span id="summaryNightsLabel" class="text-muted">Nights</span>
                <span id="summaryNightsValue" style="font-weight:500;"></span>
            </div>
            <div id="summarySubtotal" style="display:none; justify-content:space-between; margin-bottom:1rem; font-size:0.9rem; border-top:1px solid var(--cream-dark); padding-top:1rem;">
                <span class="text-muted">Initial Subtotal</span>
                <span id="summarySubtotalValue" style="font-weight:500;"></span>
            </div>
            <div id="summaryDiscount" style="display:none; justify-content:space-between; margin-bottom:1rem; font-size:0.9rem; color:var(--success);">
                <span id="summaryDiscountLabel">Applied Discount</span>
                <span id="summaryDiscountValue" style="font-weight:600;"></span>
            </div>
            <div id="summaryTotal"    style="display:none; justify-content:space-between; margin-top:1.5rem; border-top:1px solid var(--forest); padding-top:1.5rem;">
                <span style="font-family:var(--font-display); font-size:1.3rem; color:var(--forest);">Total Value</span>
                <span id="summaryTotalValue" style="font-family:var(--font-display); font-size:1.6rem; font-weight:600; color:var(--gold-dark);"></span>
            </div>

            <p id="summaryPlaceholder" class="text-muted text-center"
               style="font-size:0.85rem; margin-top:1rem; font-style:italic;">
                Set check-in date above to compute totals.
            </p>
        </div>

  
        <?php if (!empty($amenities)): ?>
        <div style="background:var(--white); padding:2rem; border-radius:var(--radius);
                    box-shadow:var(--shadow-md); margin-top:1.25rem;">

            <h3 style="margin-bottom:1.25rem; font-family:var(--font-display); font-size:1.4rem;">
                Room Amenities
            </h3>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.55rem;">
                <?php foreach ($amenities as $amenity): ?>
                <div title="<?= h($amenity['description'] ?? '') ?>"
                     style="display:flex; align-items:center; gap:.5rem;
                            font-size:.875rem; color:var(--forest-dark);">
                    <span style="color:var(--gold-dark); font-size:1rem; flex-shrink:0;">✓</span>
                    <?= h($amenity['amenity_name']) ?>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
        <?php elseif ($room['type_description']): ?>
        <!-- show room type description if no individual amenities are set -->
        <div style="background:var(--white); padding:2rem; border-radius:var(--radius);
                    box-shadow:var(--shadow-md); margin-top:1.25rem;">
            <h3 style="margin-bottom:.75rem; font-family:var(--font-display); font-size:1.4rem;">
                About This Room
            </h3>
            <p style="font-size:.875rem; color:var(--forest-dark); line-height:1.7;">
                <?= nl2br(h($room['type_description'])) ?>
            </p>
        </div>
        <?php endif; ?>

    </div>
</div>


<div id="lightbox"
     style="display:none; position:fixed; inset:0; z-index:9999;
            background:rgba(0,0,0,.92); align-items:center; justify-content:center;">


    <button onclick="closeLightbox()"
            style="position:absolute; top:1.25rem; right:1.5rem; background:none; border:none;
                   color:#fff; font-size:2rem; cursor:pointer; line-height:1;">&times;</button>

   
    <button id="lbPrev" onclick="lbNavigate(-1)"
            style="position:absolute; left:1rem; top:50%; transform:translateY(-50%);
                   background:rgba(255,255,255,.12); border:none; color:#fff;
                   font-size:2rem; padding:.4rem .9rem; border-radius:4px; cursor:pointer;">
        &#8249;
    </button>

   
    <div style="max-width:90vw; max-height:90vh; display:flex; flex-direction:column;
                align-items:center; gap:.75rem;">
        <img id="lbImg" src="" alt=""
             style="max-width:90vw; max-height:80vh; object-fit:contain; border-radius:4px;">
        <p id="lbCaption" style="color:rgba(255,255,255,.7); font-size:.875rem; margin:0;
                                  text-align:center;"></p>
        <p id="lbCounter" style="color:rgba(255,255,255,.45); font-size:.75rem; margin:0;"></p>
    </div>


    <button id="lbNext" onclick="lbNavigate(1)"
            style="position:absolute; right:1rem; top:50%; transform:translateY(-50%);
                   background:rgba(255,255,255,.12); border:none; color:#fff;
                   font-size:2rem; padding:.4rem .9rem; border-radius:4px; cursor:pointer;">
        &#8250;
    </button>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    const ROOM_ID = <?= $roomId ?>;
    const BASE    = '<?= BASE_URL ?>';

    const IMAGES = <?= json_encode(array_values(array_map(function ($img) {
        return ['url' => $img['full_url'], 'caption' => $img['caption'] ?? ''];
    }, $images))) ?>;

    let currentImg = 0; 

    
    function setMainImage(index) 
    {
        if (index < 0 || index >= IMAGES.length) return;
        currentImg = index;

        const mainEl = document.getElementById('galleryMainImg');
        mainEl.style.opacity = '0';
        setTimeout(() => 
        {
            mainEl.src = IMAGES[index].url;
            mainEl.alt = IMAGES[index].caption || '';
            mainEl.style.opacity = '1';
        }, 150);

        document.getElementById('galleryCounter').textContent =
            (index + 1) + ' / ' + IMAGES.length;

       
        document.querySelectorAll('.gallery-thumb').forEach((el, i) => 
        {
            const active = i === index;
            el.style.borderColor = active ? 'var(--gold)' : 'transparent';
            el.style.opacity     = active ? '1'           : '0.65';
        });
    }

    
    function openLightbox(index) 
    {
        if (!IMAGES.length) return;
        currentImg = index;
        _renderLb();
        const lb = document.getElementById('lightbox');
        lb.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() 
    {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    }

    function lbNavigate(dir) 
    {
        currentImg = (currentImg + dir + IMAGES.length) % IMAGES.length;
        _renderLb();
       
        setMainImage(currentImg);
    }

    function _renderLb() 
    {
        const img  = IMAGES[currentImg];
        document.getElementById('lbImg').src = img.url;
        document.getElementById('lbImg').alt = img.caption || '';
        document.getElementById('lbCaption').textContent = img.caption || '';
        document.getElementById('lbCounter').textContent =
            (currentImg + 1) + ' / ' + IMAGES.length;

        // Hide nav arrows if only one image
        const show = IMAGES.length > 1 ? 'block' : 'none';
        document.getElementById('lbPrev').style.display = show;
        document.getElementById('lbNext').style.display = show;
    }

    
    document.getElementById('lightbox').addEventListener('click', function (e) {
        if (e.target === this) closeLightbox();
    });

    
    document.addEventListener('keydown', function (e) 
    {
        const lb = document.getElementById('lightbox');
        if (lb.style.display === 'none') return;
        if (e.key === 'Escape')     closeLightbox();
        if (e.key === 'ArrowLeft')  lbNavigate(-1);
        if (e.key === 'ArrowRight') lbNavigate(1);
    });

    let appliedPromo = null;

    document.getElementById('checkIn').addEventListener('change', function () 
    {
        const next = new Date(this.value);
        next.setDate(next.getDate() + 1);
        document.getElementById('checkOut').min   = next.toISOString().slice(0, 10);
        document.getElementById('checkOut').value = '';
        updateSummary();
    });
    document.getElementById('checkOut').addEventListener('change', updateSummary);

    async function updateSummary() 
    {
        const checkIn  = document.getElementById('checkIn').value;
        const checkOut = document.getElementById('checkOut').value;
        if (!checkIn || !checkOut || checkOut <= checkIn) {
            document.getElementById('summaryPlaceholder').style.display = 'block';
            ['summaryNights','summarySubtotal','summaryDiscount','summaryTotal']
                .forEach(id => document.getElementById(id).style.display = 'none');
            return;
        }
        const promoCode = appliedPromo
            ? document.getElementById('promoCode').value.trim()
            : '';
        try 
        {
            const data = await apiFetch(
                `${BASE}/api/bookings.php?action=preview&room_id=${ROOM_ID}` +
                `&check_in_date=${checkIn}&check_out_date=${checkOut}` +
                `&promo_code=${encodeURIComponent(promoCode)}`
            );
            document.getElementById('summaryPlaceholder').style.display = 'none';
            document.getElementById('summaryNights').style.display      = 'flex';
            document.getElementById('summarySubtotal').style.display     = 'flex';
            document.getElementById('summaryTotal').style.display        = 'flex';

            document.getElementById('summaryNightsLabel').textContent =
                data.nights + ' night' + (data.nights > 1 ? 's' : '');
            document.getElementById('summaryNightsValue').textContent =
                formatPHP(data.price_per_night) + ' x ' + data.nights;
            document.getElementById('summarySubtotalValue').textContent = formatPHP(data.subtotal);
            document.getElementById('summaryTotalValue').textContent    = formatPHP(data.total);

            if (data.discount > 0) {
                document.getElementById('summaryDiscount').style.display        = 'flex';
                document.getElementById('summaryDiscountLabel').textContent     = data.promo_name || 'Promo discount';
                document.getElementById('summaryDiscountValue').textContent     = '- ' + formatPHP(data.discount);
            } else {
                document.getElementById('summaryDiscount').style.display = 'none';
            }
        } catch (err) { /* forhgot to add error message */ }
    }

    document.getElementById('applyPromoBtn').addEventListener('click', async function () {
        const code  = document.getElementById('promoCode').value.trim();
        const msgEl = document.getElementById('promoMsg');
        if (!code) return;

        const checkIn  = document.getElementById('checkIn').value;
        const checkOut = document.getElementById('checkOut').value;

        try {
            const data = await apiFetch(
                `${BASE}/api/bookings.php?action=preview&room_id=${ROOM_ID}` +
                `&check_in_date=${checkIn}&check_out_date=${checkOut}` +
                `&promo_code=${encodeURIComponent(code)}`
            );
            if (data.discount > 0) 
            {
                appliedPromo             = code;
                msgEl.textContent        = 'Code matched: ' + (data.promo_name || code) + ' (- ' + formatPHP(data.discount) + ')';
                msgEl.style.color        = 'var(--success)';
                msgEl.style.display      = 'block';
                updateSummary();
            } else 
            {
                msgEl.textContent   = 'Code parameters do not match requested stay elements.';
                msgEl.style.color   = 'var(--danger)';
                msgEl.style.display = 'block';
            }
        } catch (err) 
        {
            msgEl.textContent   = err.message;
            msgEl.style.color   = 'var(--danger)';
            msgEl.style.display = 'block';
        }
    });

    document.getElementById('bookBtn').addEventListener('click', async function () 
    {
        const btn       = this;
        const errBanner = document.getElementById('bookingError');
        errBanner.style.display = 'none';

        const checkIn   = document.getElementById('checkIn').value;
        const checkOut  = document.getElementById('checkOut').value;
        const adults    = parseInt(document.getElementById('adults').value);
        const children  = parseInt(document.getElementById('children').value);
        const special   = document.getElementById('specialRequests').value.trim();
        const promoCode = document.getElementById('promoCode').value.trim();

        if (!checkIn || !checkOut) 
        {
            errBanner.textContent  = 'Please specify formal check-in and check-out dates.';
            errBanner.style.display = 'block';
            return;
        }

        btn.disabled    = true;
        btn.textContent = 'Processing...';

        try 
        {
            const data = await apiFetch(`${BASE}/api/bookings.php?action=create`, {
                method: 'POST',
                body: 
                {
                    room_id:          ROOM_ID,
                    check_in_date:    checkIn,
                    check_out_date:   checkOut,
                    adults_count:     adults,
                    children_count:   children,
                    special_requests: special,
                    promo_code:       promoCode,
                }
            });
            window.location.href = `${BASE}/payment.php?booking_id=${data.booking_id}`;
        } catch (err) 
        {
            errBanner.textContent   = err.message;
            errBanner.style.display = 'block';
            btn.disabled    = false;
            btn.textContent = 'Confirm Suite Selection';
        }
    });

    updateSummary();
    </script>

<style>

.room-gallery > div:last-child::-webkit-scrollbar { height: 4px; }
.room-gallery > div:last-child::-webkit-scrollbar-track { background: transparent; }
.room-gallery > div:last-child::-webkit-scrollbar-thumb { background: var(--cream-dark, #ddd); border-radius: 2px; }


#galleryMain:hover img { filter: brightness(.92); }


@media (max-width: 860px) {
    .container.section[style*="grid-template-columns"]
    {
        grid-template-columns: 1fr !important;
    }
}
</style>