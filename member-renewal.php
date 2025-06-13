<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a member
if (!is_logged_in() || !is_member()) {
    header("Location: login.php");
    exit();
}

// Get member information
$stmt = $pdo->prepare("
    SELECT m.*, u.email 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

// Handle renewal submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update membership expiry date
        $stmt = $pdo->prepare("
            UPDATE members 
            SET membership_status = 'active',
                membership_expiry = DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR),
                last_renewal_date = CURRENT_DATE
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Log the renewal
        $stmt = $pdo->prepare("
            INSERT INTO membership_renewals (member_id, renewal_date, payment_date) 
            VALUES (?, CURRENT_DATE, CURRENT_DATE)
        ");
        $stmt->execute([$member['id']]);
        
        $pdo->commit();
        
        // Send confirmation email
        $subject = "SLITPA Membership Renewed";
        $message = "Dear {$member['full_name']},\n\n";
        $message .= "Your SLITPA membership has been successfully renewed.\n";
        $message .= "Your new membership expiry date is: " . date('F j, Y', strtotime('+1 year')) . "\n\n";
        $message .= "Thank you for your continued support.\n\n";
        $message .= "Best regards,\nSLITPA Team";
        
        mail($member['email'], $subject, $message);
        
        $_SESSION['success_message'] = "Membership renewed successfully!";
        header("Location: member-dashboard.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error processing renewal. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Renewal - SLITPA</title>
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
                        <h3 class="text-center">Membership Renewal</h3>
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

                        <div class="mb-4">
                            <h4>Current Membership Status</h4>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($member['full_name']); ?></p>
                            <p><strong>Membership Status:</strong> 
                                <span class="badge bg-<?php echo $member['membership_status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($member['membership_status']); ?>
                                </span>
                            </p>
                            <p><strong>Expiry Date:</strong> <?php echo date('F j, Y', strtotime($member['membership_expiry'])); ?></p>
                        </div>

                        <?php if ($member['membership_status'] === 'active'): ?>
                            <div class="alert alert-info">
                                Your membership is currently active. You can renew it before the expiry date.
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Renewal Period</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="renewal_period" id="one_year" value="1" checked>
                                    <label class="form-check-label" for="one_year">
                                        1 Year ($100)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" checked>
                                    <label class="form-check-label" for="bank_transfer">
                                        Bank Transfer
                                    </label>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h5>Bank Account Details:</h5>
                                <p>Bank: [Your Bank Name]<br>
                                Account Name: SLITPA<br>
                                Account Number: [Your Account Number]<br>
                                Swift Code: [Your Swift Code]</p>
                            </div>

                            <div class="mb-3">
                                <label for="payment_reference" class="form-label">Payment Reference Number</label>
                                <input type="text" class="form-control" id="payment_reference" name="payment_reference" required>
                                <div class="form-text">Please enter the reference number from your bank transfer.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Submit Renewal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 