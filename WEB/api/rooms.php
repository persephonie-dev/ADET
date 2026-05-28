<?php
//
//  api/rooms.php  — PUBLIC endpoint (no login required)
//
//  GET  /api/rooms.php                   → all room types with images[] + lowest price
//  GET  /api/rooms.php?action=available  → available rooms with images[]
//  GET  /api/rooms.php?room_id=N         → single room with images[] + amenities[]
//

require_once __DIR__ . '/config.php';

$action = $_GET['action']  ?? '';
$roomId = (int)($_GET['room_id'] ?? 0);

// ── Helper: resolve image URL ─────────────────────────────────────────────────
function resolveUrl(string $url): string {
    return preg_match('/^https?:\/\//', $url)
        ? $url
        : rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
}

// ── Helper: fetch all images for a set of room_type_ids in ONE query ──────────
// Returns [ room_type_id => [ ['url'=>..., 'caption'=>...], ... ], ... ]
function fetchImagesByTypeIds(PDO $pdo, array $typeIds): array {
    if (!$typeIds) return [];
    $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
    $stmt = $pdo->prepare("
        SELECT room_type_id, image_url, caption
        FROM   room_images
        WHERE  room_type_id IN ($placeholders)
        ORDER  BY display_order ASC, room_image_id ASC
    ");
    $stmt->execute(array_values($typeIds));
    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $tid = (int)$row['room_type_id'];
        $map[$tid][] = [
            'url'     => resolveUrl($row['image_url']),
            'caption' => $row['caption'] ?? '',
        ];
    }
    return $map;
}

// ════════════════════════════════════════════════════════════════════════════
//  SINGLE ROOM DETAIL
// ════════════════════════════════════════════════════════════════════════════
if ($roomId > 0) {
    $stmt = $pdo->prepare("
        SELECT r.room_id, r.room_number, r.price_per_night,
               r.capacity, r.description,
               rt.room_type_id, rt.type_name, rt.max_capacity,
               rt.bed_type,     rt.description AS type_description,
               rs.status_name AS status
        FROM  rooms r
        JOIN  room_types  rt ON r.room_type_id   = rt.room_type_id
        JOIN  room_status rs ON r.room_status_id = rs.room_status_id
        WHERE r.room_id = ?
    ");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) { json_response(['error' => 'Room not found.'], 404); }

    // All images for this room type
    $imgMap          = fetchImagesByTypeIds($pdo, [$room['room_type_id']]);
    $room['images']  = $imgMap[$room['room_type_id']] ?? [];
    $room['thumbnail'] = $room['images'][0]['url'] ?? null;

    // Amenities (per individual room)
    $stmt = $pdo->prepare("
        SELECT a.amenity_id, a.amenity_name, a.description
        FROM   room_amenities ra
        JOIN   amenities a ON ra.amenity_id = a.amenity_id
        WHERE  ra.room_id = ?
        ORDER  BY a.amenity_name
    ");
    $stmt->execute([$roomId]);
    $room['amenities'] = $stmt->fetchAll();

    json_response($room);
}

// ════════════════════════════════════════════════════════════════════════════
//  AVAILABLE ROOMS (date search)
// ════════════════════════════════════════════════════════════════════════════
if ($action === 'available') {
    $checkIn  = $_GET['check_in']  ?? '';
    $checkOut = $_GET['check_out'] ?? '';
    $guests   = (int)($_GET['guests'] ?? 1);

    if (!$checkIn || !$checkOut || $checkOut <= $checkIn) {
        json_response(['error' => 'Valid check_in and check_out dates are required.'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT r.room_id, r.room_number, r.price_per_night,
               r.capacity, r.description,
               rt.room_type_id, rt.type_name, rt.max_capacity, rt.bed_type
        FROM  rooms        r
        JOIN  room_types   rt ON r.room_type_id    = rt.room_type_id
        JOIN  room_status  rs ON r.room_status_id  = rs.room_status_id
        WHERE rs.status_name = 'Available'
          AND r.capacity     >= ?
          AND r.room_id NOT IN (
              SELECT br.room_id
              FROM   booking_rooms   br
              JOIN   bookings        b  ON br.booking_id        = b.booking_id
              JOIN   booking_status  bs ON b.booking_status_id  = bs.booking_status_id
              WHERE  bs.status_name NOT IN ('Cancelled','No Show')
                AND  br.check_in_date  < ?
                AND  br.check_out_date > ?
          )
        ORDER BY r.price_per_night
    ");
    $stmt->execute([$guests, $checkOut, $checkIn]);
    $rooms = $stmt->fetchAll();

    if ($rooms) {
        $typeIds = array_unique(array_column($rooms, 'room_type_id'));
        $imgMap  = fetchImagesByTypeIds($pdo, $typeIds);
        foreach ($rooms as &$room) {
            $imgs              = $imgMap[(int)$room['room_type_id']] ?? [];
            $room['images']    = $imgs;
            $room['thumbnail'] = $imgs[0]['url'] ?? null;
        }
        unset($room);
    }

    json_response($rooms);
}

// ════════════════════════════════════════════════════════════════════════════
//  DEFAULT: all room types
// ════════════════════════════════════════════════════════════════════════════
$stmt = $pdo->query("
    SELECT rt.room_type_id, rt.type_name, rt.base_price,
           rt.max_capacity, rt.bed_type, rt.description,
           (
               SELECT   MIN(r2.price_per_night)
               FROM     rooms       r2
               JOIN     room_status rs2 ON r2.room_status_id = rs2.room_status_id
               WHERE    r2.room_type_id = rt.room_type_id
                 AND    rs2.status_name = 'Available'
           ) AS from_price
    FROM  room_types rt
    ORDER BY rt.base_price
");
$types = $stmt->fetchAll();

if ($types) {
    $typeIds = array_column($types, 'room_type_id');
    $imgMap  = fetchImagesByTypeIds($pdo, $typeIds);
    foreach ($types as &$type) {
        $imgs              = $imgMap[(int)$type['room_type_id']] ?? [];
        $type['images']    = $imgs;
        $type['thumbnail'] = $imgs[0]['url'] ?? null;
        if ($type['from_price'] === null) {
            $type['from_price'] = $type['base_price'];
        }
    }
    unset($type);
}

json_response($types);