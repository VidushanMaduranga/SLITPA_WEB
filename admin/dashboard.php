<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
try {
    // Count total members
    $stmt = $pdo->query("SELECT COUNT(*) FROM members");
    $total_members = $stmt->fetchColumn();

    // Count total events
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $total_events = $stmt->fetchColumn();

    // Count total partners
    $stmt = $pdo->query("SELECT COUNT(*) FROM partners");
    $total_partners = $stmt->fetchColumn();

    // Count pending approvals (members + partners)
    $stmt = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM members m JOIN users u ON m.user_id = u.id WHERE u.status = 'pending') +
        (SELECT COUNT(*) FROM partners p JOIN users u ON p.user_id = u.id WHERE u.status = 'pending') as pending");
    $pending_approvals = $stmt->fetchColumn();

        } catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Set default values if query fails
    $total_members = 0;
    $total_events = 0;
    $total_partners = 0;
    $pending_approvals = 0;
}
?>

<h1 class="mb-4">Admin Dashboard</h1>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <p>Total Members</p>
            <h3><?= number_format($total_members) ?></h3>
        </div>
                    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p>Total Events</p>
            <h3><?= number_format($total_events) ?></h3>
                            </div>
                            </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p>Total Partners</p>
            <h3><?= number_format($total_partners) ?></h3>
                                </div>
                            </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p>Pending Approvals</p>
            <h3><?= number_format($pending_approvals) ?></h3>
                            </div>
                    </div>
                </div>
                
<!-- Action Cards -->
<div class="row">
    <!-- Event Management -->
    <div class="col-md-4">
        <div class="action-card">
            <i class="bi bi-calendar-event"></i>
            <h4>Event Management</h4>
            <p>Add, edit, or remove events. Manage event media and details.</p>
            <div class="mt-3">
                <a href="manage_events.php" class="btn btn-primary">Manage Events</a>
                <a href="add_event.php" class="btn btn-outline-primary">Add New Event</a>
                        </div>
                    </div>
                </div>

    <!-- Member Management -->
    <div class="col-md-4">
        <div class="action-card">
            <i class="bi bi-people"></i>
            <h4>Member Management</h4>
            <p>Review and manage member applications, update member status.</p>
            <div class="mt-3">
                <a href="manage_members.php" class="btn btn-primary">Manage Members</a>
                <a href="approve_members.php" class="btn btn-outline-primary">Pending Approvals</a>
            </div>
                        </div>
                    </div>

    <!-- Partner Management -->
    <div class="col-md-4">
        <div class="action-card">
            <i class="bi bi-building"></i>
            <h4>Partner Management</h4>
            <p>Manage partnership applications and existing partners.</p>
            <div class="mt-3">
                <a href="manage_partners.php" class="btn btn-primary">Manage Partners</a>
                <a href="add_partner.php" class="btn btn-outline-primary">Add New Partner</a>
                </div>
            </div>
        </div>
    </div>
    
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>