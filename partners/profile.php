<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['partner_id'])) {
    header('Location: ../login.php');
    exit();
}

$partner_id = $_SESSION['partner_id'];
$stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ?");
$stmt->execute([$partner_id]);
$partner = $stmt->fetch();

if (!$partner) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Create partner_posts table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS partner_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partner_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    media VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $company_name = trim($_POST['company_name']);
    $partner_email = trim($_POST['partner_email']);
    $company_email = trim($_POST['company_email']);
    $partner_number = trim($_POST['partner_number']);
    $company_number = trim($_POST['company_number']);
    $partner_category = $_POST['partner_category'];
    $description = trim($_POST['description']);
    $logo = $partner['logo'];

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('partner_', true) . '.' . $ext;
            $target = __DIR__ . '/../uploads/partners/' . $filename;
            if (!is_dir(__DIR__ . '/../uploads/partners/')) mkdir(__DIR__ . '/../uploads/partners/', 0777, true);
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $logo = $filename;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE partners SET name = ?, company_name = ?, partner_email = ?, company_email = ?, partner_number = ?, company_number = ?, partner_category = ?, description = ?, logo = ? WHERE id = ?");
        $stmt->execute([$name, $company_name, $partner_email, $company_email, $partner_number, $company_number, $partner_category, $description, $logo, $partner_id]);
        $success = 'Profile updated successfully.';
        // Re-fetch updated partner data
        $stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ?");
        $stmt->execute([$partner_id]);
        $partner = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Error updating profile: ' . $e->getMessage();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($current_password === $partner['password']) {
        if ($new_password === $confirm_password) {
            try {
                $stmt = $pdo->prepare("UPDATE partners SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $partner_id]);
                $success = 'Password changed successfully.';
            } catch (PDOException $e) {
                $error = 'Error changing password: ' . $e->getMessage();
            }
        } else {
            $error = 'New passwords do not match.';
        }
    } else {
        $error = 'Current password is incorrect.';
    }
}

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $title = trim($_POST['post_title']);
    $content = trim($_POST['post_content']);
    $media = null;

    if (isset($_FILES['post_media'])) {
        // Normalize file input to always be an array
        $mediaNames = $_FILES['post_media']['name'];
        $mediaTmpNames = $_FILES['post_media']['tmp_name'];
        if (!is_array($mediaNames)) {
            $mediaNames = [$mediaNames];
            $mediaTmpNames = [$mediaTmpNames];
        }
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi'];
        $media = [];
        foreach ($mediaNames as $index => $name) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = uniqid('post_', true) . '.' . $ext;
                $target = __DIR__ . '/../uploads/posts/' . $filename;
                if (!is_dir(__DIR__ . '/../uploads/posts/')) mkdir(__DIR__ . '/../uploads/posts/', 0777, true);
                if (move_uploaded_file($mediaTmpNames[$index], $target)) {
                    $media[] = $filename;
                }
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO partner_posts (partner_id, title, content, media) VALUES (?, ?, ?, ?)");
        $stmt->execute([$partner_id, $title, $content, implode(',', $media)]);
        $success = 'Post added successfully.';
    } catch (PDOException $e) {
        $error = 'Error adding post: ' . $e->getMessage();
    }
}

// Fetch posts
$stmt = $pdo->prepare("SELECT * FROM partner_posts WHERE partner_id = ? ORDER BY created_at DESC");
$stmt->execute([$partner_id]);
$posts = $stmt->fetchAll();

// --- Payment Expiry Logic ---
$paid_until = null;
if ($partner['status'] === 'paid' && !empty($partner['payment_confirmed_at'])) {
    $paid_until = date('Y-m-d', strtotime($partner['payment_confirmed_at'] . ' +1 year'));
    if (strtotime($paid_until) < time()) {
        // Expired, move to unpaid
        $stmt = $pdo->prepare("UPDATE partners SET status = 'unpaid' WHERE id = ?");
        $stmt->execute([$partner_id]);
        $partner['status'] = 'unpaid';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Partner Profile - SLITPA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4">Partner Profile</h2>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Partner Details</h5>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong>ID:</strong> <?= htmlspecialchars($partner['id'] ?? '') ?></p>
                    <p><strong>Partner Name:</strong> <?= htmlspecialchars($partner['name'] ?? '') ?></p>
                    <p><strong>Company Name:</strong> <?= htmlspecialchars($partner['company_name'] ?? '') ?></p>
                    <p><strong>Partner Email:</strong> <?= htmlspecialchars($partner['partner_email'] ?? '') ?></p>
                    <p><strong>Company Email:</strong> <?= htmlspecialchars($partner['company_email'] ?? '') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Partner Number:</strong> <?= htmlspecialchars($partner['partner_number'] ?? '') ?></p>
                    <p><strong>Company Number:</strong> <?= htmlspecialchars($partner['company_number'] ?? '') ?></p>
                    <p><strong>Partner Category:</strong> <?= htmlspecialchars($partner['partner_category'] ?? '') ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($partner['description'] ?? '') ?></p>
                </div>
            </div>
            <hr>
            <div class="mb-2">
                <strong>Status:</strong> <span class="badge bg-<?= $partner['status']==='paid'?'success':'secondary' ?> text-uppercase"><?= htmlspecialchars($partner['status'] ?? '') ?></span>
                <?php if ($partner['status'] === 'paid' && $paid_until): ?>
                    <span class="ms-2">(Paid until: <strong><?= htmlspecialchars($paid_until) ?></strong>)</span>
                <?php elseif ($partner['status'] === 'unpaid'): ?>
                    <span class="ms-2 text-danger">(Payment expired or not confirmed)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="post" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Partner Name</label>
                  <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($partner['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Company Name</label>
                  <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($partner['company_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Partner Email</label>
                  <input type="email" name="partner_email" class="form-control" value="<?= htmlspecialchars($partner['partner_email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Company Email</label>
                  <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($partner['company_email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Partner Number</label>
                  <input type="text" name="partner_number" class="form-control" value="<?= htmlspecialchars($partner['partner_number'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Company Number</label>
                  <input type="text" name="company_number" class="form-control" value="<?= htmlspecialchars($partner['company_number'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Partner Category</label>
                  <select name="partner_category" class="form-select">
                    <option value="silver" <?= ($partner['partner_category'] ?? '')==='silver'?'selected':'' ?>>Silver</option>
                    <option value="gold" <?= ($partner['partner_category'] ?? '')==='gold'?'selected':'' ?>>Gold</option>
                    <option value="platinum" <?= ($partner['partner_category'] ?? '')==='platinum'?'selected':'' ?>>Platinum</option>
                  </select>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Description</label>
                  <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($partner['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Logo (optional)</label>
                  <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Password Change Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Change Password</h5>
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="password" name="current_password" class="form-control" placeholder="Current Password" required>
                    </div>
                    <div class="col-md-4">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                    </div>
                    <div class="col-md-4">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary mt-3">Change Password</button>
            </form>
        </div>
    </div>

    <h3>Add Post</h3>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Post Title</label>
            <input type="text" name="post_title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Post Content</label>
            <textarea name="post_content" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Media (Multiple files allowed)</label>
            <input type="file" name="post_media[]" class="form-control" multiple accept="image/*,video/*">
        </div>
        <button type="submit" name="add_post" class="btn btn-primary">Add Post</button>
    </form>

    <h3>Your Posts</h3>
    <?php foreach ($posts as $post): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($post['content']) ?></p>
                <?php if ($post['media']): ?>
                    <img src="<?= htmlspecialchars($post['media']) ?>" class="img-fluid mb-2" alt="Post Media">
                <?php endif; ?>
                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-warning">Edit Post</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_post_id" value="<?= $post['id'] ?>">
                    <button type="submit" name="delete_post" class="btn btn-danger">Delete Post</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html> 