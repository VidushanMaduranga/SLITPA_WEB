<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config/config.php';

if (!isset($_SESSION['partner_id'])) {
    header('Location: login.php');
    exit;
}
$partner_id = $_SESSION['partner_id'];

// Get post ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo 'Invalid post ID.';
    exit;
}
$post_id = (int)$_GET['id'];

// Fetch the post
$stmt = $pdo->prepare('SELECT * FROM partner_posts WHERE id = ? AND partner_id = ?');
$stmt->execute([$post_id, $partner_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    echo 'Post not found or you do not have permission to edit this post.';
    exit;
}

$error = $success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post'])) {
    error_log('POST: ' . print_r($_POST, true));
    $title = trim($_POST['post_title'] ?? '');
    $content = trim($_POST['post_content'] ?? '');
    $media = $post['media']; // Keep existing media by default

    // Handle new media upload (optional)
    if (isset($_FILES['post_media']) && $_FILES['post_media']['name'][0] !== '') {
        $mediaNames = $_FILES['post_media']['name'];
        $mediaTmpNames = $_FILES['post_media']['tmp_name'];
        if (!is_array($mediaNames)) {
            $mediaNames = [$mediaNames];
            $mediaTmpNames = [$mediaTmpNames];
        }
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi'];
        $mediaArr = [];
        foreach ($mediaNames as $index => $name) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = uniqid('post_', true) . '.' . $ext;
                $target = __DIR__ . '/../uploads/posts/' . $filename;
                if (!is_dir(__DIR__ . '/../uploads/posts/')) mkdir(__DIR__ . '/../uploads/posts/', 0777, true);
                if (move_uploaded_file($mediaTmpNames[$index], $target)) {
                    $mediaArr[] = $filename;
                }
            }
        }
        if ($mediaArr) {
            $existingMedia = $post['media'] ? explode(',', $post['media']) : [];
            $media = implode(',', array_merge($existingMedia, $mediaArr));
        }
    }

    // Update the post
    $stmt = $pdo->prepare('UPDATE partner_posts SET title = ?, content = ?, media = ? WHERE id = ? AND partner_id = ?');
    if ($stmt->execute([$title, $content, $media, $post_id, $partner_id])) {
        $success = 'Post updated successfully.';
        // Refresh post data
        $stmt = $pdo->prepare('SELECT * FROM partner_posts WHERE id = ? AND partner_id = ?');
        $stmt->execute([$post_id, $partner_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $errorInfo = $stmt->errorInfo();
        $error = 'Failed to update post: ' . htmlspecialchars($errorInfo[2]);
    }
}

// Handle media deletion
if (isset($_POST['delete_media']) && isset($_POST['media_file'])) {
    $mediaToDelete = $_POST['media_file'];
    $mediaFiles = $post['media'] ? explode(',', $post['media']) : [];
    $mediaFiles = array_filter($mediaFiles, function($file) use ($mediaToDelete) {
        return $file !== $mediaToDelete;
    });
    // Delete file from server
    $filePath = __DIR__ . '/../uploads/posts/' . $mediaToDelete;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    // Update database
    $stmt = $pdo->prepare('UPDATE partner_posts SET media = ? WHERE id = ? AND partner_id = ?');
    $stmt->execute([implode(',', $mediaFiles), $post_id, $partner_id]);
    // Redirect to refresh post data and avoid resubmission
    header("Location: edit_post.php?id=" . $post_id . "&deleted=1");
    exit;
}

// Show success message after redirect
if (isset($_GET['deleted'])) {
    $success = 'Media deleted successfully.';
    echo '<script>if (window.history.replaceState) { window.history.replaceState(null, null, window.location.pathname + window.location.search.replace(/([&?])deleted=1(&|$)/, function(m,a,b){return a=="?"&&b?"?":a=="?"?"":a=="&"&&b?"&":"";}) ); }</script>';
}

// Prepare media for display
$mediaFiles = $post['media'] ? explode(',', $post['media']) : [];

?>
<?php include '../includes/header.php'; ?>
<!-- Add Bootstrap CSS if not already included -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Hero Section -->
<section class="hero-section mb-4">
    <div class="hero" style="position: relative;">
        <div class="hero-image-wrap">
            <img src="/slitpa-web/assets/images/hero.png" alt="SLITPA Hero Image" class="hero-image img-fluid">
        </div>
        <div class="hero-text-wrap">
            <!-- <h1 class="hero-title">Edit Post</h1> -->
        </div>
    </div>
</section>
<!-- Modern Card Layout -->
<div class="container" style="max-width: 700px;">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0" style="border-radius: 18px; background: #f7fafd;">
                <div class="card-body p-5">
                    <h2 class="card-title mb-4 text-center" style="font-weight:700;">Edit Post</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"> <?= htmlspecialchars($success) ?> </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Post Title</label>
                            <input type="text" name="post_title" class="form-control form-control-lg" value="<?= htmlspecialchars($post['title']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Post Content</label>
                            <textarea name="post_content" class="form-control form-control-lg" rows="4" required><?= htmlspecialchars($post['content']) ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Current Media</label><br>
                            <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($mediaFiles as $file): ?>
                                <?php $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); ?>
                                <div class="position-relative" style="width:140px;">
                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                        <img src="../uploads/posts/<?= htmlspecialchars($file) ?>" alt="Media" class="img-thumbnail" style="max-width:140px; border-radius:12px; box-shadow:0 2px 12px rgba(44,62,80,0.08); transition:transform 0.2s; cursor:pointer;" onmouseover="this.style.transform='scale(1.07)'" onmouseout="this.style.transform='scale(1)'">
                                    <?php elseif (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                                        <video src="../uploads/posts/<?= htmlspecialchars($file) ?>" controls class="img-thumbnail" style="max-width:140px; border-radius:12px; box-shadow:0 2px 12px rgba(44,62,80,0.08); transition:transform 0.2s; cursor:pointer;" onmouseover="this.style.transform='scale(1.07)'" onmouseout="this.style.transform='scale(1)'"></video>
                                    <?php endif; ?>
                                    <form method="post" class="position-absolute top-0 end-0" style="z-index:2;">
                                        <input type="hidden" name="media_file" value="<?= htmlspecialchars($file) ?>">
                                        <button type="submit" name="delete_media" class="btn btn-sm btn-danger" style="border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center;" onclick="return confirm('Delete this media?');">&times;</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload New Media (optional, multiple allowed)</label>
                            <input type="file" name="post_media[]" class="form-control form-control-lg" multiple accept="image/*,video/*">
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" name="update_post" class="btn btn-primary btn-lg px-4">Update Post</button>
                            <a href="profile.php" class="btn btn-outline-secondary btn-lg px-4">Back to Profile</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add Bootstrap JS if not already included -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/footer.php'; ?> 