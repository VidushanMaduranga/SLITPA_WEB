<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
include __DIR__ . '/../includes/admin_header.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle member approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['member_id'])) {
    $member_id = (int)$_POST['member_id'];
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();
        
        if ($action === 'approve') {
            // Update member status
            $stmt = $pdo->prepare("UPDATE members SET membership_status = 'active', membership_expiry = DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR) WHERE id = ?");
            $stmt->execute([$member_id]);
            
            // Update user status
            $stmt = $pdo->prepare("UPDATE users u JOIN members m ON u.id = m.user_id SET u.status = 'active' WHERE m.id = ?");
            $stmt->execute([$member_id]);
            
            // Get member email for notification
            $stmt = $pdo->prepare("SELECT email, full_name FROM members WHERE id = ?");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch();
            
            // Send approval email
            $subject = "SLITPA Membership Approved";
            $message = "Dear {$member['full_name']},\n\n";
            $message .= "Your SLITPA membership has been approved. You can now log in to your account.\n\n";
            $message .= "Best regards,\nSLITPA Team";
            
            mail($member['email'], $subject, $message);
            
            $_SESSION['success_message'] = "Member approved successfully.";
        } elseif ($action === 'reject') {
            // Update member status
            $stmt = $pdo->prepare("UPDATE members SET membership_status = 'inactive' WHERE id = ?");
            $stmt->execute([$member_id]);
            
            // Update user status
            $stmt = $pdo->prepare("UPDATE users u JOIN members m ON u.id = m.user_id SET u.status = 'inactive' WHERE m.id = ?");
            $stmt->execute([$member_id]);
            
            $_SESSION['success_message'] = "Member rejected successfully.";
        }
        
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error processing request: " . $e->getMessage();
    }
    
    header("Location: pending-members.php");
    exit();
}

// Get pending members
$stmt = $pdo->query("
    SELECT m.*, u.created_at as registration_date 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.membership_status = 'pending' 
    ORDER BY u.created_at DESC
");
$pending_members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Members - SLITPA Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Pending Members</h1>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Country</th>
                                <th>Position</th>
                                <th>Phone</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo htmlspecialchars($member['country']); ?></td>
                                    <td><?php echo htmlspecialchars($member['position']); ?></td>
                                    <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($member['registration_date'])); ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 