<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT u.*, m.full_name, m.membership_status, m.profile_image FROM users u LEFT JOIN members m ON u.id = m.user_id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}
?>
<header class="text-white" style="background-color: #343a40bf; position: fixed; top: 0; left: 0; width: 100%; z-index: 1030;">
        <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="/slitpa-web/index.php">
            <img src="/slitpa-web/assets/images/logo-2.png" alt="SLITPA Logo" class="img-fluid" style="max-height: 50px;">
            </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/about_us.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/comitee.php">Committee</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/partner.php">Partners</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/event.php">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/cpd-session.php">CPD Session</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/member-page.php">Membership</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/news.php">History</a></li>
                <li class="nav-item"><a class="nav-link" href="/slitpa-web/contact_us.php">Contact</a></li>
                </ul>
            <ul class="navbar-nav ms-4">
                <?php if (isset($_SESSION['partner_id'])): ?>
                    <?php
                    $stmt = $pdo->prepare("SELECT name, logo, status FROM partners WHERE id = ?");
                    $stmt->execute([$_SESSION['partner_id']]);
                    $partner = $stmt->fetch();
                    $first_name = explode(' ', trim($partner['name'] ?? ''))[0];
                    ?>
                    <?php if ($partner && $partner['status'] === 'pending'): ?>
                        <li class="nav-item"><span class="nav-link text-warning">Pending Approval</span></li>
                    <?php elseif ($partner && $partner['status'] === 'unpaid'): ?>
                        <li class="nav-item"><span class="nav-link text-warning">Pending Payment</span></li>
                    <?php elseif ($partner && $partner['status'] === 'paid'): ?>
                        <li class="nav-item dropdown d-flex align-items-center">
                            <?php if (!empty($partner['logo'])): ?>
                                <img src="/slitpa-web/uploads/partners/<?= htmlspecialchars($partner['logo']) ?>" alt="Logo" style="height:32px;width:32px;object-fit:cover;border-radius:50%;margin-right:8px;">
                            <?php endif; ?>
                            <a class="nav-link dropdown-toggle" href="#" id="partnerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= htmlspecialchars($first_name) ?>
                            </a>
                            <ul class="dropdown-menu " aria-labelledby="partnerDropdown" style="margin-left: 0;">
                                <li><a class="dropdown-item" href="/slitpa-web/partners/profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="/slitpa-web/partners/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $first_name = '';
                    if ($current_user && !empty($current_user['full_name'])) {
                        $first_name = explode(' ', trim($current_user['full_name']))[0];
                    } elseif ($current_user && !empty($current_user['email'])) {
                        $first_name = $current_user['email'];
                    }
                    ?>
                    <li class="nav-item dropdown d-flex align-items-center">
                        <?php if (!empty($current_user['profile_image'])): ?>
                            <img src="/slitpa-web/uploads/members/<?= htmlspecialchars($current_user['profile_image']) ?>" alt="Profile" style="height:32px;width:32px;object-fit:cover;border-radius:50%;margin-right:8px;">
                        <?php endif; ?>
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($first_name) ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/slitpa-web/member/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/slitpa-web/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['partner_id']) && !isset($_SESSION['user_id'])): ?>
                <!-- Only show Login and Register if NOT logged in as partner or member -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Login</a>
                    <ul class="dropdown-menu" aria-labelledby="loginDropdown">
                        <li><a class="dropdown-item" href="/slitpa-web/login.php?type=partner">Partner Login</a></li>
                        <li><a class="dropdown-item" href="/slitpa-web/login.php?type=member">Member Login</a></li>
                    </ul>
                </li>
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Register</a>
                    <ul class="dropdown-menu" aria-labelledby="registerDropdown">
                        <li><a class="dropdown-item" href="/slitpa-web/partners/register.php">Partner Registration</a></li>
                        <li><a class="dropdown-item" href="/slitpa-web/member-registration.php">Resident Member Registration</a></li>
                        <li><a class="dropdown-item" href="/slitpa-web/non_resident_registration.php">Non-Resident Member Registration</a></li>
                    </ul>
                </li> -->
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    </div>
</header>

<!-- Add padding to body to prevent content from being hidden under the fixed header -->
<style>
    body { 
        /* padding-top: 80px;  */
    }
    @media (max-width: 576px) {
        body { 
            padding-top: 120px; 
        }
        .navbar-brand img {
            max-height: 40px;
        }
    }
    @media (max-width: 768px) {
        .navbar-collapse {
            background-color: #343a40bf;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
        }
        .dropdown-menu {
            background-color: transparent;
            border: none;
            padding-left: 1rem;
        }
        .dropdown-item {
            color: #fff !important;
        }
        .dropdown-item:hover {
            background-color: rgba(255,255,255,0.1);
        }
    }
</style>

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

<!-- Add Bootstrap JS to ensure dropdowns work on every page -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>