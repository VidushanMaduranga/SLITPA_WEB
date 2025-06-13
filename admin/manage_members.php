<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

$conn = get_db_connection();
if (!$conn) {
    die("Database connection failed");
}

// Handle member approval
if (isset($_POST['approve_member'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Update user status
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$user_id]);
        $stmt->closeCursor();
        
        // Set membership dates
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 year'));
        
        // Update member record
        $stmt = $conn->prepare("UPDATE members SET membership_start_date = ?, membership_end_date = ? WHERE id = ?");
        $stmt->execute([$start_date, $end_date, $member_id]);
        $stmt->closeCursor();
        
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
    $conn->beginTransaction();
    
    try {
        // Update user status
        $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$user_id]);
        $stmt->closeCursor();
        
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
    $stmt->execute([$payment_date, $member_id]);
    $stmt->closeCursor();
    
        $_SESSION['message'] = "Payment confirmed successfully!";
        $_SESSION['message_type'] = 'success';
    
    header("Location: manage_members.php");
    exit();
}

// Handle payment confirmation for unpaid members
if (isset($_POST['confirm_payment_unpaid'])) {
    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $payment_date = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE members SET payment_status = 'paid', payment_date = ? WHERE id = ?");
    $stmt->execute([$payment_date, $member_id]);
    header("Location: manage_members.php");
    exit();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Only show members whose payment_date is within the last year or is null (pending/unpaid)
$filter_condition = "";
if ($filter !== 'pending') {
    $filter_condition = " WHERE (m.payment_date IS NULL OR m.payment_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)) ";
}
if ($filter === 'pending') {
    $filter_condition = " WHERE u.status = 'pending' ";
}
$query = "
    SELECT 
        m.*, 
        u.email, 
        u.status as user_status,
        u.id as user_id
    FROM members m
    JOIN users u ON m.user_id = u.id
    $filter_condition
    ORDER BY 
        CASE 
            WHEN u.status = 'pending' THEN 1
            WHEN m.payment_status = 'pending' THEN 2
            WHEN m.payment_date < CURDATE() AND m.payment_status = 'paid' THEN 3
            ELSE 4
        END,
        m.payment_date DESC
";
$result = $conn->query($query);
$members = $result->fetchAll(PDO::FETCH_ASSOC);

$type = $_GET['type'] ?? 'pending';

// Fetch Paid Members
$paid_query = "SELECT m.*, u.email, u.status as user_status, u.id as user_id FROM members m JOIN users u ON m.user_id = u.id WHERE m.payment_status = 'paid' AND m.payment_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND u.status = 'active' ORDER BY m.payment_date DESC";
$paid_members = $conn->query($paid_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch Unpaid Members
$unpaid_query = "SELECT m.*, u.email, u.status as user_status, u.id as user_id FROM members m JOIN users u ON m.user_id = u.id WHERE (m.payment_status != 'paid' OR m.payment_status IS NULL) AND u.status = 'active' ORDER BY m.created_at DESC";
$unpaid_members = $conn->query($unpaid_query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pending Members
$pending_query = "SELECT m.*, u.email, u.status as user_status, u.id as user_id FROM members m JOIN users u ON m.user_id = u.id WHERE u.status = 'pending' ORDER BY m.created_at DESC";
$pending_members = $conn->query($pending_query)->fetchAll(PDO::FETCH_ASSOC);

$conn = null;
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
        .nav-pills {
            justify-content: center;
            margin-bottom: 2rem;
        }
        .nav-pills .nav-link {
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.7rem 1.5rem;
        }
        .card-table {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(44,62,80,0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 2.5rem;
        }
        .table thead th {
            font-weight: 700;
            font-size: 1.05rem;
            background: #f8fafc;
        }
        .table-hover tbody tr:hover {
            background: #f0f6ff;
        }
        @media (max-width: 600px) {
            .card-table { padding: 1rem 0.2rem; }
            .nav-pills .nav-link { font-size: 1rem; padding: 0.5rem 0.7rem; }
        }
    </style>
</head>
<body>

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

        <div class="d-flex justify-content-end mb-3">
            <a href="manage_members.php" class="btn btn-outline-primary me-2">All Members</a>
            <a href="manage_members.php?filter=pending" class="btn btn-outline-primary">Pending Approvals</a>
        </div>

        <div class="mb-1" style="background-color: #0261e08c;  border-radius: 10px;">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link<?= $type === 'pending' ? ' active' : '' ?>" href="manage_members.php?type=pending">Pending Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $type === 'unpaid' ? ' active' : '' ?>" href="manage_members.php?type=unpaid">Unpaid Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $type === 'paid' ? ' active' : '' ?>" href="manage_members.php?type=paid">Paid Members</a>
                </li>
            </ul>
        </div>

        <?php if ($type === 'paid'): ?>
            <h2 class="mb-3 text-center">Paid Members</h2>
            <div class="card-table">
                <div class="table-responsive mb-0">
                    <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                                <th>ID</th><th>Name</th><th>Email</th><th>Country</th><th>Position</th><th>Membership Start</th><th>Membership End</th><th>Payment Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($paid_members as $member): ?>
                        <tr>
                            <td><?= $member['id'] ?></td>
                            <td><?= htmlspecialchars($member['full_name']) ?></td>
                            <td><?= htmlspecialchars($member['email']) ?></td>
                            <td><?= htmlspecialchars($member['country']) ?></td>
                            <td><?= htmlspecialchars($member['position']) ?></td>
                                <td><?= htmlspecialchars($member['payment_date']) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($member['payment_date'] . ' +1 year'))) ?></td>
                                <td><?= htmlspecialchars($member['payment_date']) ?></td>
                                <td>
                                    <a href="view_member.php?id=<?= $member['id'] ?>" class="btn btn-info btn-sm">View</a>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($type === 'unpaid'): ?>
            <h2 class="mb-3 text-center">Unpaid Members</h2>
            <div class="card-table">
                <div class="table-responsive mb-0">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th><th>Name</th><th>Email</th><th>Country</th><th>Position</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($unpaid_members as $member): ?>
                            <tr>
                                <td><?= $member['id'] ?></td>
                                <td><?= htmlspecialchars($member['full_name']) ?></td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td><?= htmlspecialchars($member['country']) ?></td>
                                <td><?= htmlspecialchars($member['position']) ?></td>
                                <td><span class="badge bg-warning">Unpaid</span></td>
                                <td>
                                    <a href="view_member.php?id=<?= $member['id'] ?>" class="btn btn-info btn-sm">View</a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                        <button type="submit" name="confirm_payment_unpaid" class="btn btn-primary btn-sm" onclick="return confirm('Confirm payment for this member?')">Confirm Payment</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($type === 'pending'): ?>
            <h2 class="mb-3 text-center">Pending Members</h2>
            <div class="card-table">
                <div class="table-responsive mb-0">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th><th>Name</th><th>Email</th><th>Country</th><th>Position</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pending_members as $member): ?>
                            <tr>
                                <td><?= $member['id'] ?></td>
                                <td><?= htmlspecialchars($member['full_name']) ?></td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td><?= htmlspecialchars($member['country']) ?></td>
                                <td><?= htmlspecialchars($member['position']) ?></td>
                                <td><span class="badge bg-secondary">Pending</span></td>
                                <td>
                                    <a href="view_member.php?id=<?= $member['id'] ?>" class="btn btn-info btn-sm">View</a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                        <input type="hidden" name="user_id" value="<?= $member['user_id'] ?>">
                                        <button type="submit" name="approve_member" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="reject_member" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 