<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}
?>
<header class="header-section">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="/assets/images/logo.png" alt="SLITPA Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/partners">Partners</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if ($current_user): ?>
                        <?php if ($current_user['user_type'] === 'admin'): ?>
                            <a href="/admin/dashboard.php" class="btn btn-outline-light me-2">Admin Dashboard</a>
                        <?php else: ?>
                            <a href="/member/profile.php" class="btn btn-outline-light me-2">My Profile</a>
                        <?php endif; ?>
                        <a href="/logout.php" class="btn btn-light">Logout</a>
                    <?php else: ?>
                        <a href="/register.php" class="btn btn-outline-light me-2">Register</a>
                        <a href="/login.php" class="btn btn-light">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
    <?= $_SESSION['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
endif;
?>