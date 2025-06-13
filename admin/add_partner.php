<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $company_name = trim($_POST['company_name']);
    $partner_email = trim($_POST['partner_email']);
    $company_email = trim($_POST['company_email']);
    $partner_number = trim($_POST['partner_number']);
    $company_number = trim($_POST['company_number']);
    $partner_category = $_POST['partner_category'];
    $description = trim($_POST['description']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $logo = null;

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

    // Password validation
    if (empty($password) || empty($confirm_password)) {
        $error = 'Password and Confirm Password are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO partners (logo, name, company_name, partner_email, company_email, partner_number, company_number, partner_category, description, password, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
            $stmt->execute([$logo, $name, $company_name, $partner_email, $company_email, $partner_number, $company_number, $partner_category, $description, $password]);
            header('Location: manage_partners.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error adding partner: ' . $e->getMessage();
        }
    }
}
?>
<div class="container py-5">
    <h2>Add Partner</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="card p-4">
        <div class="mb-3">
            <label class="form-label">Partner Logo</label>
            <input type="file" name="logo" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Partner Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Partner Email</label>
            <input type="email" name="partner_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Company Email</label>
            <input type="email" name="company_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Partner Number</label>
            <input type="text" name="partner_number" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Company Number</label>
            <input type="text" name="company_number" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Partner Category</label>
            <select name="partner_category" class="form-select" required>
                <option value="">Select Category</option>
                <option value="silver">Silver</option>
                <option value="gold">Gold</option>
                <option value="platinum">Platinum</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Partner</button>
        <a href="manage_partners.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div> 