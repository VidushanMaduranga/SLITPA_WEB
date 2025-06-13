<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';
if (!isset($_GET['id'])) {
    header('Location: cpd_sessions.php?msg=No+session+ID+provided');
    exit;
}
$id = (int)$_GET['id'];

// Fetch session
$stmt = $pdo->prepare('SELECT * FROM cpd_sessions WHERE id = ?');
$stmt->execute([$id]);
$session = $stmt->fetch();
if (!$session) {
    header('Location: cpd_sessions.php?msg=Session+not+found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $organizer = $_POST['organizer'] ?? '';
    $location = $_POST['location'] ?? '';
    $session_date = $_POST['session_date'] ?? '';
    $photo = $session['photo'];
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newPhoto = uniqid('cpd_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/cpd_sessions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newPhoto);
        // Delete old photo
        $oldPhotoPath = $uploadDir . $photo;
        if (is_file($oldPhotoPath)) unlink($oldPhotoPath);
        $photo = $newPhoto;
    }
    $stmt = $pdo->prepare('UPDATE cpd_sessions SET title=?, organizer=?, location=?, session_date=?, photo=? WHERE id=?');
    $stmt->execute([$title, $organizer, $location, $session_date, $photo, $id]);
    header('Location: cpd_sessions.php?msg=Session+updated');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit CPD Session</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .edit-card {
            max-width: 500px;
            margin: 56px auto;
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.12);
            border-radius: 18px;
            border: 1.5px solid #e3e6f0;
            background: #fff;
        }
        .edit-header {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5em;
        }
        .photo-preview {
            max-width: 180px;
            max-height: 110px;
            border-radius: 10px;
            border: 2px solid #b6d0f7;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .photo-preview:hover {
            transform: scale(1.04) rotate(-2deg);
            box-shadow: 0 6px 24px rgba(33, 150, 243, 0.18);
        }
        .form-label {
            font-weight: 600;
            color: #2563eb;
        }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb 0%, #13c1ac 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
        }
        .btn-primary i {
            margin-right: 6px;
        }
        .btn-outline-secondary {
            font-weight: 500;
        }
        @media (max-width: 600px) {
            .edit-card { margin: 16px auto; padding: 1.2rem; }
            .edit-header { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card edit-card p-4">
        <div class="edit-header mb-4"><i class="bi bi-pencil-square"></i> Edit CPD Session</div>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($session['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Organizer</label>
                <input type="text" name="organizer" class="form-control" value="<?= htmlspecialchars($session['organizer']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($session['location']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="session_date" class="form-control" value="<?= htmlspecialchars($session['session_date']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Photo</label><br>
                <img src="../uploads/cpd_sessions/<?= htmlspecialchars($session['photo']) ?>" alt="Current Photo" class="photo-preview">
                <input type="file" name="photo" class="form-control mt-2">
                <small class="text-muted">Leave blank to keep current photo.</small>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i>Update Session</button>
                <a href="cpd_sessions.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 