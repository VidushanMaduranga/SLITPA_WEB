<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = trim($_POST['content']);
    $published_date = $_POST['published_date'];
    $created_by = 1; // TODO: Use session user id if available

    try {
        $stmt = $pdo->prepare("INSERT INTO news (title, slug, content, published_date, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $published_date, $created_by]);
        $news_id = $pdo->lastInsertId();

        // Handle multiple media uploads
        if (!empty($_FILES['media']['name'][0])) {
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
        $error = 'Error adding news: ' . $e->getMessage();
    }
}
?>
<div class="container py-5">
    <h2>Add News</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Slug (URL)</label>
            <input type="text" name="slug" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="6" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Media (Images and Videos)</label>
            <input type="file" name="media[]" class="form-control" multiple accept="image/*,video/mp4,video/webm,video/ogg">
            <small class="text-muted">You can select multiple images and videos. Allowed formats: JPG, PNG, GIF, WEBP, MP4, WebM, OGG</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Published Date</label>
            <input type="date" name="published_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add News</button>
        <a href="manage_news.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div> 