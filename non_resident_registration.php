<?php
require_once __DIR__ . '/config/config.php';
require_once 'includes/functions.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $linkedin = sanitize_input($_POST['linkedin']);
    $visa_status = sanitize_input($_POST['visa_status']);
    $country = sanitize_input($_POST['country']);
    $position = sanitize_input($_POST['position']);
    $passport_number = sanitize_input($_POST['passport_number']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password']; // Store password without hashing
    $profile_image = null;
    // Handle profile photo upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('member_', true) . '.' . $ext;
            $target = __DIR__ . '/uploads/members/' . $filename;
            if (!is_dir(__DIR__ . '/uploads/members/')) mkdir(__DIR__ . '/uploads/members/', 0777, true);
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $profile_image = $filename;
            }
        }
    }
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Email address is already registered.");
        }

        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type, status) VALUES (?, ?, ?, 'member', 'pending')");
        $stmt->execute([$username, $email, $password]);
        $user_id = $pdo->lastInsertId();

        if (!$user_id) {
            throw new Exception("Failed to create user account.");
        }

        // Insert into members table with member_category = 'non_resident'
        $stmt = $pdo->prepare("INSERT INTO members (user_id, full_name, email, country, position, passport_number, phone, membership_status, member_category, profile_image, linkedin, visa_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'non_resident', ?, ?, ?)");
        $full_name = $first_name . ' ' . $last_name;
        $stmt->execute([$user_id, $full_name, $email, $country, $position, $passport_number, $phone, $profile_image, $linkedin, $visa_status]);

        $pdo->commit();
        $_SESSION['success_message'] = "Registration successful! Please wait for admin approval.";
        header("Location: login.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non-Resident Member Registration - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Non-Resident Member Registration</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_message']; 
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="visa_status" class="form-label">Visa Status *</label>
                                <select class="form-select" id="visa_status" name="visa_status" required>
                                    <option value="">Select Visa Status</option>
                                    <option value="UAE Residence">UAE Residence</option>
                                    <option value="Visit Visa">Visit Visa</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or E-mail *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="linkedin" class="form-label">LinkedIn</label>
                                <input type="text" class="form-control" id="linkedin" name="linkedin">
                            </div>
                            <div class="mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <input type="text" class="form-control" id="country" name="country" required>
                            </div>
                            <div class="mb-3">
                                <label for="position" class="form-label">Position *</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                            </div>
                            <div class="mb-3">
                                <label for="passport_number" class="form-label">Passport Number *</label>
                                <input type="text" class="form-control" id="passport_number" name="passport_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Ensure Bootstrap dropdowns work (in case of dynamic content)
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>
</body>
</html> 