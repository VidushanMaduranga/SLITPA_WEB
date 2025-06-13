<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';
$stmt = $pdo->query("SELECT * FROM cpd_sessions ORDER BY session_date DESC");
$sessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage CPD Sessions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/admin_header.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Manage CPD Sessions</h1>
    <a href="../admin/dashboard.php#cpd-sessions" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Title</th>
                    <th>Organizer</th>
                    <th>Date</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sessions as $session): ?>
                <tr>
                    <td><img src="../uploads/cpd_sessions/<?= htmlspecialchars($session['photo']) ?>" alt="" style="max-width:80px;max-height:60px;"></td>
                    <td><?= htmlspecialchars($session['title']) ?></td>
                    <td><?= htmlspecialchars($session['organizer']) ?></td>
                    <td><?= htmlspecialchars($session['session_date']) ?></td>
                    <td><?= htmlspecialchars($session['created_at']) ?></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-warning disabled">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger disabled">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($sessions)): ?>
            <tr><td colspan="6" class="text-center">No CPD sessions found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 