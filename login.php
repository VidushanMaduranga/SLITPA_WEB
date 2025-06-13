<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$member_error = '';
$partner_error = '';

// Handle member login form submission
if (isset($_POST['member_login'])) {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    try {
        $stmt = $pdo->prepare("SELECT u.*, m.full_name, m.membership_status, m.profile_image, m.member_category FROM users u LEFT JOIN members m ON u.id = m.user_id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $member_error = "No account found with this email address.";
        } else {
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['email'];
                $_SESSION['profile_image'] = $user['profile_image'];
                $_SESSION['member_category'] = $user['member_category'];
                $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$user['id']]);
                $_SESSION['message'] = "Welcome back, " . ($user['full_name'] ?? $user['email']) . "!";
                $_SESSION['message_type'] = "success";
                if ($user['user_type'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $member_error = "Invalid password. Please try again.";
            }
        }
    } catch (PDOException $e) {
        $member_error = "Database error: " . $e->getMessage();
    }
}
// Handle partner login form submission
if (isset($_POST['partner_login'])) {
    $partner_email = trim($_POST['partner_email']);
    $password = $_POST['partner_password'];
    $stmt = $pdo->prepare("SELECT * FROM partners WHERE partner_email = ? LIMIT 1");
    $stmt->execute([$partner_email]);
    $partner = $stmt->fetch();
    if ($partner && $password === $partner['password']) {
        $_SESSION['partner_id'] = $partner['id'];
        $_SESSION['partner_name'] = $partner['name'];
        header('Location: partners/profile.php');
        exit();
    } else {
        $partner_error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <div id="login-choice">
                            <button class="btn btn-primary w-100 mb-3" onclick="showMemberLogin()">Member Login</button>
                            <button class="btn btn-secondary w-100" onclick="showPartnerLogin()">Partner Login</button>
                        </div>
                        <form id="member-login-form" method="POST" action="" class="needs-validation" novalidate style="display:none;">
                            <?php if ($member_error): ?>
                                <div class="alert alert-danger"> <?= htmlspecialchars($member_error) ?> </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="member_login" class="btn btn-primary w-100">Login as Member</button>
                            <div class="text-center mt-3">
                                <a href="#" onclick="showLoginChoice();return false;">Back</a>
                            </div>
                        </form>
                        <form id="partner-login-form" method="POST" action="" class="needs-validation" novalidate style="display:none;">
                            <?php if ($partner_error): ?>
                                <div class="alert alert-danger"> <?= htmlspecialchars($partner_error) ?> </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="partner_email" class="form-label">Partner Email</label>
                                <input type="email" class="form-control" id="partner_email" name="partner_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="partner_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="partner_password" name="partner_password" required>
                            </div>
                            <button type="submit" name="partner_login" class="btn btn-secondary w-100">Login as Partner</button>
                            <div class="text-center mt-3">
                                <a href="#" onclick="showLoginChoice();return false;">Back</a>
                            </div>
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

        function showLoginChoice() {
            document.getElementById('login-choice').style.display = '';
            document.getElementById('member-login-form').style.display = 'none';
            document.getElementById('partner-login-form').style.display = 'none';
        }
        function showMemberLogin() {
            document.getElementById('login-choice').style.display = 'none';
            document.getElementById('member-login-form').style.display = '';
            document.getElementById('partner-login-form').style.display = 'none';
        }
        function showPartnerLogin() {
            document.getElementById('login-choice').style.display = 'none';
            document.getElementById('member-login-form').style.display = 'none';
            document.getElementById('partner-login-form').style.display = '';
        }
        // Show correct form based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            var params = new URLSearchParams(window.location.search);
            if (params.get('type') === 'partner') {
                showPartnerLogin();
            } else if (params.get('type') === 'member') {
                showMemberLogin();
            } else {
                showLoginChoice();
            }
        });
    </script>
</body>
</html> 