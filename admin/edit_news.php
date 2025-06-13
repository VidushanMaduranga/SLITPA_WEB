<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container py-5"><div class="alert alert-danger">Invalid news ID.</div></div>';
    exit;
}
$news_id = (int)$_GET['id'];

// Fetch news item
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$news = $stmt->fetch();
if (!$news) {
    echo '<div class="container py-5"><div class="alert alert-danger">News not found.</div></div>';
    exit;
}

// Fetch all media for this news post
$stmtMedia = $pdo->prepare("SELECT * FROM news_media WHERE news_id = ? ORDER BY display_order, id");
$stmtMedia->execute([$news_id]);
$mediaList = $stmtMedia->fetchAll();

// Handle media deletion
if (isset($_POST['delete_media']) && isset($_POST['media_id']) && isset($_POST['file_path'])) {
    $media_id = (int)$_POST['media_id'];
    $file_path = $_POST['file_path'];
    $stmtDel = $pdo->prepare("DELETE FROM news_media WHERE id = ?");
    if ($stmtDel->execute([$media_id])) {
        $file_to_delete = '../' . $file_path;
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        header("Location: edit_news.php?id=$news_id");
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_media'])) {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = trim($_POST['content']);
    $published_date = $_POST['published_date'];
    $image = isset($news['image']) ? $news['image'] : '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('news_', true) . '.' . $ext;
            $target = __DIR__ . '/../uploads/news/' . $filename;
            if (!is_dir(__DIR__ . '/../uploads/news/')) mkdir(__DIR__ . '/../uploads/news/', 0777, true);
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Delete old image if exists
                if ($image && file_exists(__DIR__ . '/../uploads/news/' . $image)) {
                    unlink(__DIR__ . '/../uploads/news/' . $image);
                }
                $image = $filename;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE news SET title=?, slug=?, content=?, image=?, published_date=? WHERE id=?");
        $stmt->execute([$title, $slug, $content, $image, $published_date, $news_id]);

        // Handle new media uploads (optional)
        if (isset($_FILES['media']) && !empty($_FILES['media']['name'][0])) {
            $mediaNames = $_FILES['media']['name'];
            $mediaTmpNames = $_FILES['media']['tmp_name'];
            $mediaTypes = $_FILES['media']['type'];
            $mediaErrors = $_FILES['media']['error'];
            $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedVideos = ['mp4', 'webm', 'ogg'];
            foreach ($mediaNames as $i => $name) {
                if ($mediaErrors[$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $mediaType = in_array($ext, $allowedImages) ? 'image' : (in_array($ext, $allowedVideos) ? 'video' : null);
                    if ($mediaType) {
                        $filename = uniqid('news_media_', true) . '.' . $ext;
            $target = __DIR__ . '/../uploads/news/' . $filename;
            if (!is_dir(__DIR__ . '/../uploads/news/')) mkdir(__DIR__ . '/../uploads/news/', 0777, true);
                        if (move_uploaded_file($mediaTmpNames[$i], $target)) {
                            $stmtMedia = $pdo->prepare("INSERT INTO news_media (news_id, file_path, media_type, display_order) VALUES (?, ?, ?, ?)");
                            $stmtMedia->execute([$news_id, 'uploads/news/' . $filename, $mediaType, $i]);
                        }
                    }
                }
            }
        }
        header('Location: manage_news.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Error updating news: ' . $e->getMessage();
    }
}
?>
<head>
    <link rel="stylesheet" href="edit_news_style.css">
</head>
<div class="edit-news-card">
    <div class="edit-news-title">Edit News</div>
    <div class="edit-news-divider"></div>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($news['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Slug (URL)</label>
            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($news['slug']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($news['content']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Image</label>
            <?php if ($news['image']): ?>
                <div class="mb-2"><img src="../uploads/news/<?= htmlspecialchars($news['image']) ?>" alt="News Image" style="max-width:120px;"></div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Leave blank to keep current image.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Published Date</label>
            <input type="date" name="published_date" class="form-control" value="<?= htmlspecialchars($news['published_date']) ?>" required>
        </div>
        <div class="media-gallery">
            <?php foreach ($mediaList as $media): ?>
                <div class="media-item" style="position:relative;">
                    <?php if ($media['media_type'] === 'image'): ?>
                        <img src="../<?= htmlspecialchars($media['file_path']) ?>" alt="Media">
                    <?php else: ?>
                        <video src="../<?= htmlspecialchars($media['file_path']) ?>" controls></video>
                    <?php endif; ?>
                    <button type="button" class="delete-media-btn" onclick="deleteMedia(<?= $media['id'] ?>, '<?= htmlspecialchars($media['file_path']) ?>')">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Add More Media (Images and Videos)</label>
            <input type="file" name="media[]" class="form-control" multiple accept="image/*,video/mp4,video/webm,video/ogg">
            <small class="text-muted">You can select multiple images and videos. Allowed formats: JPG, PNG, GIF, WEBP, MP4, WebM, OGG</small>
        </div>
        <button style="margin: 20px 0" type="submit" class="btn btn-primary me-2">Update News</button>
        <a href="manage_news.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
    <!-- Hidden delete form -->
    <form id="deleteMediaForm" method="post" style="display:none;">
        <input type="hidden" name="media_id" id="deleteMediaId">
        <input type="hidden" name="file_path" id="deleteMediaPath">
        <input type="hidden" name="delete_media" value="1">
    </form>
    <script>
    function deleteMedia(id, path) {
        if (confirm('Delete this media?')) {
            document.getElementById('deleteMediaId').value = id;
            document.getElementById('deleteMediaPath').value = path;
            document.getElementById('deleteMediaForm').submit();
        }
    }
    </script>
</div> 