<?php
require_once __DIR__ . '/../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get member information
$stmt = $pdo->prepare("SELECT u.*, m.* FROM users u LEFT JOIN members m ON u.id = m.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$member = $stmt->fetch();

if (!$member) {
    die('Member not found');
}

// Handle profile photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('member_', true) . '.' . $ext;
            $target = __DIR__ . '/../uploads/members/' . $filename;
            if (!is_dir(__DIR__ . '/../uploads/members/')) {
                mkdir(__DIR__ . '/../uploads/members/', 0777, true);
            }
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Delete old profile image if exists
                if (!empty($member['profile_image'])) {
                    $old_file = __DIR__ . '/../uploads/members/' . $member['profile_image'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                // Update profile image in database
                $stmt = $pdo->prepare("UPDATE members SET profile_image = ? WHERE user_id = ?");
                $stmt->execute([$filename, $user_id]);
                $_SESSION['profile_image'] = $filename;
                $_SESSION['message'] = "Profile photo updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: profile.php");
                exit();
            }
        }
    }
    $_SESSION['message'] = "Failed to upload profile photo. Please try again.";
    $_SESSION['message_type'] = "danger";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Profile - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Member Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <?php if (!empty($member['profile_image'])): ?>
                                <img src="../uploads/members/<?= htmlspecialchars($member['profile_image']) ?>" alt="Profile Photo" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <img src="../assets/images/default-profile.png" alt="Default Profile" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data" class="mt-3">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Update Profile Photo</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Photo</button>
                            </form>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Personal Information</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">Full Name</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($member['full_name']) ?></dd>

                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($member['email']) ?></dd>

                                    <dt class="col-sm-4">Phone</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($member['phone']) ?></dd>

                                    <dt class="col-sm-4">Country</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($member['country']) ?></dd>

                                    <dt class="col-sm-4">Position</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($member['position']) ?></dd>

                                    <dt class="col-sm-4">Membership Status</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($member['membership_status']) ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 