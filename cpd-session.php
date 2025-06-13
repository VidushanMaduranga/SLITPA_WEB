<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
$stmt = $pdo->query("SELECT * FROM cpd_sessions ORDER BY session_date DESC");
$sessions = $stmt->fetchAll();

// Fetch media for each session
$media_by_session = [];
foreach ($sessions as $session) {
    $stmt_media = $pdo->prepare("SELECT * FROM cpd_session_media WHERE session_id = ?");
    $stmt_media->execute([$session['id']]);
    $media_by_session[$session['id']] = $stmt_media->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPD Sessions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<!-- Hero Section -->
<section class="hero-section mb-4">
    <div class="hero">
        <div class="hero-image-wrap">
            <img src="assets/images/hero.png" alt="Hero" class="hero-image">
        </div>
        <div class="hero-text-wrap">
            <div class="hero-title">CPD Sessions</div>
            
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="list-group">
      
        <h1 class="mb-4">CPD Sessions</h1>      <?php foreach ($sessions as $session): ?>
            <a href="#" class="list-group-item list-group-item-action mb-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#sessionModal<?= $session['id'] ?>">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($session['title']) ?></h5>
                        <div class="mb-1 text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($session['location'] ?? '') ?></div>
                    </div>
                    <div class="text-md-end mt-2 mt-md-0">
                        <span class="badge bg-primary">Date: <?= htmlspecialchars($session['session_date']) ?></span>
                    </div>
                </div>
            </a>

            <!-- Modal for session details -->
            <div class="modal fade" id="sessionModal<?= $session['id'] ?>" tabindex="-1" aria-labelledby="sessionModalLabel<?= $session['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sessionModalLabel<?= $session['id'] ?>"><?= htmlspecialchars($session['title']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2"><strong>Organizer:</strong> <?= htmlspecialchars($session['organizer']) ?></div>
                            <div class="mb-2"><strong>Location:</strong> <?= htmlspecialchars($session['location'] ?? '') ?></div>
                            <div class="mb-2"><strong>Date:</strong> <?= htmlspecialchars($session['session_date']) ?></div>
                            <div class="mb-2"><strong>Added:</strong> <?= htmlspecialchars($session['created_at'] ?? '') ?></div>
                            <div class="mb-3">
                                <?php if (!empty($media_by_session[$session['id']])): ?>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php foreach ($media_by_session[$session['id']] as $media): ?>
                                            <div class="text-center" style="width: 180px;">
                                                <?php if ($media['media_type'] === 'image'): ?>
                                                    <img src="uploads/cpd_sessions/<?= htmlspecialchars($media['file_path']) ?>" class="img-thumbnail mb-2" style="max-width: 160px; max-height: 120px; object-fit: contain;">
                                                <?php else: ?>
                                                    <video src="uploads/cpd_sessions/<?= htmlspecialchars($media['file_path']) ?>" class="img-thumbnail mb-2" style="max-width: 160px; max-height: 120px; object-fit: contain;" controls></video>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                                        <span class="text-muted">No media available</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($sessions)): ?>
            <div class="alert alert-info">No CPD sessions found.</div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>