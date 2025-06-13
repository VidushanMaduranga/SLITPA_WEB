<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';
?>
<?php include '../includes/header.php'; ?>
<?php

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $company_name = trim($_POST['company_name']);
    $partner_email = trim($_POST['partner_email']);
    $company_email = trim($_POST['company_email']);
    $partner_number = trim($_POST['partner_number']);
    $company_number = trim($_POST['company_number']);
    $partner_category = $_POST['partner_category'];
    $description = trim($_POST['description']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // Check for duplicate company name
    $stmt = $pdo->prepare("SELECT id FROM partners WHERE company_name = ?");
    $stmt->execute([$company_name]);
    if ($stmt->fetch()) {
        $error = 'A partner with this company name already exists. Please use a different company name.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $logo = null;
        // Handle logo upload if needed
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('partner_logo_', true) . '.' . $ext;
                $target = __DIR__ . '/../uploads/partners/' . $filename;
                if (!is_dir(__DIR__ . '/../uploads/partners/')) mkdir(__DIR__ . '/../uploads/partners/', 0777, true);
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                    $logo = $filename;
                }
            }
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO partners (name, company_name, partner_email, company_email, partner_number, company_number, partner_category, description, password, logo, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
            $stmt->execute([$name, $company_name, $partner_email, $company_email, $partner_number, $company_number, $partner_category, $description, $password, $logo]);
            $success = 'Registration successful! Your application is pending admin approval.';
        } catch (PDOException $e) {
            $error = 'Error adding partner: ' . $e->getMessage();
        }
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">Partner Registration</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"> <?= htmlspecialchars($success) ?> </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Partner Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="partner_email" class="form-label">Partner Email</label>
                            <input type="email" class="form-control" id="partner_email" name="partner_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_email" class="form-label">Company Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="partner_number" class="form-label">Partner Number</label>
                            <input type="text" class="form-control" id="partner_number" name="partner_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_number" class="form-label">Company Number</label>
                            <input type="text" class="form-control" id="company_number" name="company_number">
                        </div>
                        <div class="mb-3">
                            <label for="partner_category" class="form-label">Partner Category</label>
                            <select class="form-select" id="partner_category" name="partner_category" required>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="logo" class="form-label">Partner Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Ensure Bootstrap dropdowns work (in case of dynamic content)
    document.addEventListener('DOMContentLoaded', function() {
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
</script>