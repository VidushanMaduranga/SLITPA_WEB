<?php
require_once '../config/config.php';

// Get all upcoming events (events with end_date in future)
$current_date = date('Y-m-d');
$upcoming_events = $pdo->prepare("
    SELECT e.*, em.file_path as featured_image 
    FROM events e
    LEFT JOIN event_media em ON e.id = em.event_id AND em.is_featured = 1
    WHERE e.end_date >= ? 
    ORDER BY e.event_date ASC
");
$upcoming_events->execute([$current_date]);
$upcoming_events = $upcoming_events->fetchAll();

// Get past events grouped by year
$past_events_by_year = [];
$past_events = $pdo->query("
    SELECT e.*, em.file_path as featured_image, YEAR(e.event_date) as year 
    FROM events e
    LEFT JOIN event_media em ON e.id = em.event_id AND em.is_featured = 1
    WHERE e.end_date < ?
    ORDER BY e.event_date DESC
")->fetchAll();

foreach ($past_events as $event) {
    $year = $event['year'];
    if (!isset($past_events_by_year[$year])) {
        $past_events_by_year[$year] = [];
    }
    $past_events_by_year[$year][] = $event;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="py-5">
        <div class="container">
            <h1 class="mb-4">Upcoming Events</h1>
            
            <?php if (empty($upcoming_events)): ?>
                <div class="alert alert-info">No upcoming events scheduled. Check back later!</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <?php if ($event['featured_image']): ?>
                                    <img src="<?= $event['featured_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title']) ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <?= date('F j, Y', strtotime($event['event_date'])) ?>
                                            <?php if ($event['end_date'] && $event['end_date'] != $event['event_date']): ?>
                                                - <?= date('F j, Y', strtotime($event['end_date'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                    <p class="card-text"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?></p>
                                    <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h1 class="mb-4 mt-5">Past Events</h1>
            
            <?php if (empty($past_events_by_year)): ?>
                <div class="alert alert-info">No past events available.</div>
            <?php else: ?>
                <?php foreach ($past_events_by_year as $year => $events): ?>
                    <h2 class="mt-4 mb-3"><?= $year ?></h2>
                    <div class="row g-4">
                        <?php foreach ($events as $event): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <?php if ($event['featured_image']): ?>
                                        <img src="<?= $event['featured_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title']) ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <?= date('F j, Y', strtotime($event['event_date'])) ?>
                                                <?php if ($event['end_date'] && $event['end_date'] != $event['event_date']): ?>
                                                    - <?= date('F j, Y', strtotime($event['end_date'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        </p>
                                        <p class="card-text"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="event_details.php?id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>