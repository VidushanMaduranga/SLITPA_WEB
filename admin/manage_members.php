<?php
require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = get_db_connection();
if (!$conn) {
    die("Database connection failed");
}

// Handle member approval
if (isset($_POST['approve_member'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update user status
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Set membership dates
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 year'));
        
        // Update member record
        $stmt = $conn->prepare("UPDATE members SET membership_start_date = ?, membership_end_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $start_date, $end_date, $member_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $_SESSION['message'] = "Member approved successfully!";
        $_SESSION['message_type'] = 'success';
        
        // TODO: Send approval email to member
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error approving member: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: manage_members.php");
    exit();
}

// Handle member rejection
if (isset($_POST['reject_member'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update user status
        $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $_SESSION['message'] = "Member rejected successfully!";
        $_SESSION['message_type'] = 'success';
        
        // TODO: Send rejection email to member
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error rejecting member: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: manage_members.php");
    exit();
}

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $payment_date = date('Y-m-d');
    
    $stmt = $conn->prepare("UPDATE members SET payment_status = 'paid', payment_date = ? WHERE id = ?");
    $stmt->bind_param("si", $payment_date, $member_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Payment confirmed successfully!";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error confirming payment!";
        $_SESSION['message_type'] = 'danger';
    }
    $stmt->close();
    
    header("Location: manage_members.php");
    exit();
}

// Fetch all members with their user data
$query = "
    SELECT 
        m.*, 
        u.email, 
        u.status as user_status,
        u.id as user_id
    FROM members m
    JOIN users u ON m.user_id = u.id
    ORDER BY 
        CASE 
            WHEN u.status = 'pending' THEN 1
            WHEN m.payment_status = 'pending' THEN 2
            WHEN m.membership_end_date < CURDATE() THEN 3
            ELSE 4
        END,
        m.membership_end_date ASC
";

$result = $conn->query($query);
$members = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - SLITPA Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .table-actions {
            white-space: nowrap;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Manage Members</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Membership</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= $member['id'] ?></td>
                            <td><?= htmlspecialchars($member['full_name']) ?></td>
                            <td><?= htmlspecialchars($member['email']) ?></td>
                            <td><?= htmlspecialchars($member['country']) ?></td>
                            <td><?= htmlspecialchars($member['position']) ?></td>
                            <td>
                                <?php if ($member['user_status'] === 'pending'): ?>
                                    <span class="badge bg-warning status-badge">Pending Approval</span>
                                <?php elseif ($member['user_status'] === 'active'): ?>
                                    <span class="badge bg-success status-badge">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger status-badge">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($member['membership_end_date']): ?>
                                    <?php if (strtotime($member['membership_end_date']) < time()): ?>
                                        <span class="badge bg-danger status-badge">Expired</span>
                                    <?php else: ?>
                                        <span class="badge bg-success status-badge">
                                            Valid until <?= date('Y-m-d', strtotime($member['membership_end_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary status-badge">Not Set</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($member['payment_status'] === 'pending'): ?>
                                    <span class="badge bg-warning status-badge">Pending Payment</span>
                                <?php else: ?>
                                    <span class="badge bg-success status-badge">
                                        Paid on <?= date('Y-m-d', strtotime($member['payment_date'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="table-actions">
                                <?php if ($member['user_status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                        <input type="hidden" name="user_id" value="<?= $member['user_id'] ?>">
                                        <button type="submit" name="approve_member" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this member?')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button type="submit" name="reject_member" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this member?')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($member['payment_status'] === 'pending' && $member['user_status'] === 'active'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                        <button type="submit" name="confirm_payment" class="btn btn-primary btn-sm">
                                            <i class="bi bi-credit-card"></i> Confirm Payment
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="view_member.php?id=<?= $member['id'] ?>" class="btn btn-info btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 