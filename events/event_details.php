<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';

if (!isset($_GET['id'])) {
    header('Location: upcoming_events.php');
    exit;
}

$event_id = $_GET['id'];

// Get event details
$event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$event->execute([$event_id]);
$event = $event->fetch();

if (!$event) {
    header('Location: upcoming_events.php');
    exit;
}

// Get all media for this event
$media = $pdo->prepare("SELECT * FROM event_media WHERE event_id = ? ORDER BY is_featured DESC");
$media->execute([$event_id]);
$media = $media->fetchAll();

// Check if event is upcoming
$is_upcoming = strtotime($event['end_date']) >= strtotime(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="upcoming_events.php">Events</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($event['title']) ?></li>
                </ol>
            </nav>
            
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="mb-3"><?= htmlspecialchars($event['title']) ?></h1>
                    
                    <?php if (!empty($media)): ?>
                        <div id="eventCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($media as $index => $item): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <?php if ($item['media_type'] === 'image'): ?>
                                            <img src="<?= $item['file_path'] ?>" class="d-block w-100" alt="Event Media">
                                        <?php else: ?>
                                            <video controls class="d-block w-100">
                                                <source src="<?= $item['file_path'] ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($media) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <p><strong>Date:</strong> 
                            <?= date('F j, Y', strtotime($event['event_date'])) ?>
                            <?php if ($event['end_date'] && $event['end_date'] != $event['event_date']): ?>
                                - <?= date('F j, Y', strtotime($event['end_date'])) ?>
                            <?php endif; ?>
                        </p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                    </div>
                    
                    <div class="event-description">
                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Event Details</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>Status:</strong> 
                                    <?= $is_upcoming ? '<span class="badge bg-success">Upcoming</span>' : '<span class="badge bg-secondary">Past Event</span>' ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Date:</strong> 
                                    <?= date('F j, Y', strtotime($event['event_date'])) ?>
                                    <?php if ($event['end_date'] && $event['end_date'] != $event['event_date']): ?>
                                        - <?= date('F j, Y', strtotime($event['end_date'])) ?>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Time:</strong> 
                                    <?= date('g:i A', strtotime($event['event_date'])) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?>
                                </li>
                            </ul>
                            
                            <?php if ($is_upcoming): ?>
                                <div class="d-grid mt-3">
                                    <a href="#" class="btn btn-primary">Register for Event</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>