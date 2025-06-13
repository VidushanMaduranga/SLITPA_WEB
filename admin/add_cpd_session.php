<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

// Ensure uploads directory exists
$upload_dir = __DIR__ . '/../uploads/cpd_sessions/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['cpd_title'] ?? '');
    $organizer = trim($_POST['cpd_organizer'] ?? '');
    $session_date = $_POST['cpd_date'] ?? '';
    $location = trim($_POST['cpd_location'] ?? '');
    $photos = $_FILES['cpd_photo'] ?? null;

    if ($title && $organizer && $session_date && $location && $photos && $photos['error'][0] === UPLOAD_ERR_OK) {
        $stmt = $pdo->prepare("INSERT INTO cpd_sessions (title, organizer, location, session_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $organizer, $location, $session_date]);
        $session_id = $pdo->lastInsertId();

        foreach ($photos['tmp_name'] as $key => $tmp_name) {
            $ext = strtolower(pathinfo($photos['name'][$key], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('cpd_', true) . '.' . $ext;
                $target = $upload_dir . $filename;
                if (move_uploaded_file($tmp_name, $target)) {
                    $media_type = strpos($photos['type'][$key], 'image') === 0 ? 'image' : 'video';
                    $stmt = $pdo->prepare("INSERT INTO cpd_session_media (session_id, file_path, media_type, upload_date) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$session_id, $filename, $media_type]);
                }
            }
        }
        header('Location: dashboard.php?msg=cpd_added');
        exit;
    } else {
        $msg = 'All fields are required.';
    }
    header('Location: dashboard.php?msg=' . urlencode($msg));
    exit;
}
header('Location: dashboard.php');
exit; 