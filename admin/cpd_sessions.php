<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';
// Handle messages
$msg = $_GET['msg'] ?? '';
$stmt = $pdo->query("SELECT * FROM cpd_sessions ORDER BY session_date DESC");
$cpd_sessions = $stmt->fetchAll();
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
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="card mb-4 p-4">
        <form action="add_cpd_session.php" method="POST" enctype="multipart/form-data">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="cpd_photo" class="form-label">Session Media</label>
                    <input type="file" class="form-control" id="cpd_photo" name="cpd_photo[]" accept="image/*,video/*" multiple required>
                </div>
                <div class="col-md-3">
                    <label for="cpd_title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="cpd_title" name="cpd_title" required>
                </div>
                <div class="col-md-3">
                    <label for="cpd_organizer" class="form-label">Organizer</label>
                    <input type="text" class="form-control" id="cpd_organizer" name="cpd_organizer" required>
                </div>
                <div class="col-md-3">
                    <label for="cpd_location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="cpd_location" name="cpd_location" required>
                </div>
                <div class="col-md-2">
                    <label for="cpd_date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="cpd_date" name="cpd_date" required>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row g-4">
        <?php foreach ($cpd_sessions as $session): ?>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <img src="../uploads/cpd_sessions/<?= htmlspecialchars($session['photo']) ?>" class="card-img-top" alt="CPD Session Photo">
                <div class="card-body">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($session['title']) ?></h5>
                    <div class="mb-1"><strong>Organizer:</strong> <?= htmlspecialchars($session['organizer']) ?></div>
                    <div class="mb-1"><strong><i class="bi bi-geo-alt"></i> Location:</strong> <?= htmlspecialchars($session['location'] ?? '') ?></div>
                    <div class="mb-1"><strong>Date:</strong> <?= htmlspecialchars($session['session_date']) ?></div>
                    <div class="mb-1"><strong>Added:</strong> <?= htmlspecialchars($session['created_at']) ?></div>
                    <div class="mt-3 d-flex gap-2">
                        <a href="edit_cpd_session.php?id=<?= $session['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form action="delete_cpd_session.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this session?');">
                            <input type="hidden" name="id" value="<?= $session['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($cpd_sessions)): ?>
        <div class="col-12"><div class="alert alert-info">No CPD sessions found.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 