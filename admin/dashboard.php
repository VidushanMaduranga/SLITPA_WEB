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

// Get statistics
$stats = [
    'total_members' => 0,
    'pending_members' => 0,
    'active_members' => 0,
    'total_partners' => 0,
    'pending_partners' => 0,
    'total_events' => 0,
    'upcoming_events' => 0
];

// Get member statistics
$query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN u.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active
FROM members m
JOIN users u ON m.user_id = u.id";
$result = $conn->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['total_members'] = $row['total'];
    $stats['pending_members'] = $row['pending'];
    $stats['active_members'] = $row['active'];
}

// Get partner statistics
$query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
FROM partners";
$result = $conn->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['total_partners'] = $row['total'];
    $stats['pending_partners'] = $row['pending'];
}

// Get event statistics
$query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming
FROM events";
$result = $conn->query($query);
if ($row = $result->fetch_assoc()) {
    $stats['total_events'] = $row['total'];
    $stats['upcoming_events'] = $row['upcoming'];
}

// Get recent members
$query = "SELECT m.*, u.email, u.status 
FROM members m 
JOIN users u ON m.user_id = u.id 
ORDER BY m.id DESC LIMIT 5";
$recent_members = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get recent partners
$query = "SELECT * FROM partners ORDER BY id DESC LIMIT 5";
$recent_partners = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .quick-link {
            text-decoration: none;
            color: inherit;
        }
        .quick-link:hover .stat-card {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <div>
                <a href="manage_members.php" class="btn btn-primary me-2">
                    <i class="bi bi-person-plus"></i> Manage Members
                </a>
                <a href="manage_partners.php" class="btn btn-primary me-2">
                    <i class="bi bi-building"></i> Manage Partners
                </a>
                <a href="manage_events.php" class="btn btn-primary">
                    <i class="bi bi-calendar-event"></i> Manage Events
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <a href="manage_members.php" class="quick-link">
                    <div class="card stat-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Members</h6>
                                    <div class="stat-number"><?= $stats['total_members'] ?></div>
                                </div>
                                <div class="stat-icon text-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                            <small class="text-warning"><?= $stats['pending_members'] ?> pending approval</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage_partners.php" class="quick-link">
                    <div class="card stat-card mb-3">
                    <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Partners</h6>
                                    <div class="stat-number"><?= $stats['total_partners'] ?></div>
                                </div>
                                <div class="stat-icon text-success">
                                    <i class="bi bi-building"></i>
                                </div>
                            </div>
                            <small class="text-warning"><?= $stats['pending_partners'] ?> pending approval</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage_events.php" class="quick-link">
                    <div class="card stat-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total Events</h6>
                                    <div class="stat-number"><?= $stats['total_events'] ?></div>
                                </div>
                                <div class="stat-icon text-info">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                            </div>
                            <small class="text-info"><?= $stats['upcoming_events'] ?> upcoming events</small>
                        </div>
                    </div>
                </a>
                            </div>
            <div class="col-md-3">
                <a href="manage_members.php" class="quick-link">
                    <div class="card stat-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Active Members</h6>
                                    <div class="stat-number"><?= $stats['active_members'] ?></div>
                                </div>
                                <div class="stat-icon text-success">
                                    <i class="bi bi-person-check"></i>
                                </div>
                            </div>
                            </div>
                    </div>
                </a>
                    </div>
                </div>
                
        <!-- Recent Activity -->
        <div class="row">
            <!-- Recent Members -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Members</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_members as $member): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($member['full_name']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($member['email']) ?></small>
                                        </div>
                                        <span class="badge bg-<?= $member['status'] === 'pending' ? 'warning' : 'success' ?>">
                                            <?= ucfirst($member['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Partners -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Partners</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_partners as $partner): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($partner['company_name']) ?></h6>
                                            <small class="text-muted"><?= ucfirst($partner['partner_type']) ?> Partner</small>
                                        </div>
                                        <span class="badge bg-<?= $partner['status'] === 'pending' ? 'warning' : 'success' ?>">
                                            <?= ucfirst($partner['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                    <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>