<?php
// 
//  api/room_images.php
//  Handles image uploads and deletions for room types.
//  All actions require Admin role.
//
//  POST ?action=upload
//       Multipart form-data: room_type_id, image (file),
//                            caption (optional), display_order (optional)
//       Saves the file to /assets/img/rooms/ and inserts a
//       row into room_images.
//
//  POST ?action=delete
//       JSON body: room_image_id
//       Deletes the DB record AND the physical file (if local).
//
//  POST ?action=set_thumbnail
//       JSON body: room_image_id, room_type_id
//       Sets display_order=1 for this image and bumps all
//       others for the same room_type to order+1 (QoL: easy
//       "make this the cover photo" button in the admin UI).
//
//  GET  ?action=list&room_type_id=N
//       Returns all images for a given room type.
// 

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
require_admin();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'upload':        handle_upload($pdo);        break;
    case 'delete':        handle_delete($pdo);        break;
    case 'set_thumbnail': handle_set_thumbnail($pdo); break;
    case 'list':          handle_list($pdo);           break;
    default:
        json_response(['error' => 'Unknown action.'], 400);
}

// 
//  UPLOAD
// 
function handle_upload(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $roomTypeId   = (int)($_POST['room_type_id']  ?? 0);
    $caption      = trim($_POST['caption']         ?? '');
    $displayOrder = (int)($_POST['display_order']  ?? 0);

    if (!$roomTypeId) {
        json_response(['error' => 'room_type_id is required.'], 422);
    }

    // Confirm the room type exists
    $stmt = $pdo->prepare("SELECT room_type_id FROM room_types WHERE room_type_id = ?");
    $stmt->execute([$roomTypeId]);
    if (!$stmt->fetch()) {
        json_response(['error' => 'Room type not found.'], 404);
    }

    // Validate the uploaded file
    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errCodes = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        ];
        $code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
        json_response(['error' => $errCodes[$code] ?? 'Upload error.'], 422);
    }

    $file     = $_FILES['image'];
    $maxBytes = 5 * 1024 * 1024; // 5 MB hard limit

    if ($file['size'] > $maxBytes) {
        json_response(['error' => 'Image must be under 5 MB.'], 422);
    }

    // Validate MIME type by reading the file header, not trusting $_FILES
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mimeType, $allowedMimes)) {
        json_response(['error' => 'Only JPG, PNG, WEBP, and GIF images are allowed.'], 422);
    }

    $extMap    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $extension = $extMap[$mimeType];

    // Build the upload directory path relative to the project root.
    // __DIR__ is /WEB/api, so go up one level then into assets/img/rooms/
    $uploadDir = __DIR__ . '/../assets/img/rooms/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a unique filename — roomtype ID + timestamp + random bytes
    $filename  = 'room_' . $roomTypeId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destPath  = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        json_response(['error' => 'Could not save the image. Check folder permissions.'], 500);
    }

    // Store a web-accessible relative URL in the DB.
    // The front-end will prepend BASE_URL.
    $imageUrl = 'assets/img/rooms/' . $filename;

    // If this is the first image for the type, force it as thumbnail (order 1)
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM room_images WHERE room_type_id = ?");
    $countStmt->execute([$roomTypeId]);
    if ((int)$countStmt->fetchColumn() === 0) {
        $displayOrder = 1;
    }

    $stmt = $pdo->prepare("
        INSERT INTO room_images (room_type_id, image_url, caption, display_order)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $roomTypeId,
        $imageUrl,
        $caption ?: null,
        $displayOrder ?: 0,
    ]);

    json_response([
        'success'       => true,
        'room_image_id' => (int)$pdo->lastInsertId(),
        'image_url'     => $imageUrl,
        'thumbnail_url' => BASE_URL . '/' . $imageUrl,
    ], 201);
}

// 
//  DELETE
// 
function handle_delete(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body        = json_decode(file_get_contents('php://input'), true);
    $roomImageId = (int)($body['room_image_id'] ?? 0);

    if (!$roomImageId) {
        json_response(['error' => 'room_image_id is required.'], 422);
    }

    // Fetch the record so we can delete the physical file too
    $stmt = $pdo->prepare("SELECT image_url FROM room_images WHERE room_image_id = ?");
    $stmt->execute([$roomImageId]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response(['error' => 'Image not found.'], 404);
    }

    // Delete the physical file only if it's a local path (not an external URL like Google Drive)
    if (!preg_match('/^https?:\/\//', $row['image_url'])) {
        $filePath = __DIR__ . '/../' . $row['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete the DB record
    $stmt = $pdo->prepare("DELETE FROM room_images WHERE room_image_id = ?");
    $stmt->execute([$roomImageId]);

    json_response(['success' => true]);
}

// 
//  SET THUMBNAIL  (QoL: promote an image to display_order = 1)
// 
function handle_set_thumbnail(PDO $pdo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'POST required.'], 405);
    }

    $body        = json_decode(file_get_contents('php://input'), true);
    $roomImageId = (int)($body['room_image_id'] ?? 0);
    $roomTypeId  = (int)($body['room_type_id']  ?? 0);

    if (!$roomImageId || !$roomTypeId) {
        json_response(['error' => 'room_image_id and room_type_id are required.'], 422);
    }

    // Bump all existing images for this type to order >= 2
    $pdo->prepare("
        UPDATE room_images
        SET display_order = display_order + 10
        WHERE room_type_id = ?
    ")->execute([$roomTypeId]);

    // Set the chosen image to order 1
    $pdo->prepare("
        UPDATE room_images
        SET display_order = 1
        WHERE room_image_id = ?
    ")->execute([$roomImageId]);

    // Re-compact display_order values to keep them tidy (1, 2, 3 …)
    $rows = $pdo->prepare("
        SELECT room_image_id
        FROM room_images
        WHERE room_type_id = ?
        ORDER BY display_order ASC, room_image_id ASC
    ");
    $rows->execute([$roomTypeId]);
    $upd = $pdo->prepare("UPDATE room_images SET display_order = ? WHERE room_image_id = ?");
    $i   = 1;
    foreach ($rows->fetchAll() as $r) {
        $upd->execute([$i++, $r['room_image_id']]);
    }

    json_response(['success' => true]);
}

// 
//  LIST
// 
function handle_list(PDO $pdo): void
{
    $roomTypeId = (int)($_GET['room_type_id'] ?? 0);
    if (!$roomTypeId) {
        json_response(['error' => 'room_type_id is required.'], 422);
    }

    $stmt = $pdo->prepare("
        SELECT room_image_id, image_url, caption, display_order
        FROM room_images
        WHERE room_type_id = ?
        ORDER BY display_order ASC, room_image_id ASC
    ");
    $stmt->execute([$roomTypeId]);
    $rows = $stmt->fetchAll();

    // Resolve full URLs for the frontend: local paths get BASE_URL prepended,
    // external URLs (Google Drive seeds) are returned as-is.
    foreach ($rows as &$row) {
        if (!preg_match('/^https?:\/\//', $row['image_url'])) {
            $row['full_url'] = BASE_URL . '/' . $row['image_url'];
        } else {
            $row['full_url'] = $row['image_url'];
        }
    }
    unset($row);

    json_response($rows);
}