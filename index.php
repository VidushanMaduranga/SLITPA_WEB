<?php
/**
 * SLITPA Website Homepage
 * Main landing page displaying events, member counts, and partner information
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load database configuration
require_once __DIR__ . '/config/config.php';

// Verify database connection
if (!isset($pdo)) {
    die("Database connection not initialized");
}

// Get current date for event filtering
$current_date = date('Y-m-d');

try {
    // Fetch upcoming events with their associated media
    $stmt = $pdo->prepare("
        SELECT e.*, GROUP_CONCAT(em.file_path, ':', em.media_type) as media_files 
        FROM events e
        LEFT JOIN event_media em ON e.id = em.event_id
        WHERE e.event_date >= ?
        GROUP BY e.id
        ORDER BY e.event_date ASC
        LIMIT 3
    ");
    $stmt->execute([$current_date]);
    $upcoming_events = $stmt->fetchAll();

    // Get total member count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM members");
    $member_count = $stmt->fetch()['count'];

    // Get active partner count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM partners WHERE status = 'active'");
    $partner_count = $stmt->fetch()['count'];

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $upcoming_events = [];
    $member_count = 0;
    $partner_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLITPA - Sri Lankan IT Professionals Association</title>
    <!-- Include Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include custom styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Header Section with Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section: Main banner with welcome message -->
    <section class="hero-section">
        <div class="hero">
            <!-- Hero Background Image -->
            <div class="hero-image-wrap">
                <img src="assets/images/hero.jpg" alt="SLITPA Hero Image" class="hero-image">
            </div>
            <!-- Hero Content Overlay -->
            <div class="hero-text-wrap">
                <h1 class="hero-title">Welcome to SLITPA</h1>
                <h3 class="hero-sub-title">Sri Lankan IT Professionals Association - UAE</h3>
                <a href="register.php" class="apply-member">Become a Member</a>
            </div>
        </div>
    </section>

    <!-- Events Section: Display upcoming events -->
    <?php if (!empty($upcoming_events)): ?>
    <section class="third-section">
        <div class="container third-section-wrap">
            <div class="row g-4">
                <?php foreach ($upcoming_events as $event): ?>
                <div class="col-12 col-md-4">
                    <div class="session-wrap">
                        <div class="session">
                            <div class="row g-0">
                                <div class="col-12">
                                    <!-- Event Media Display -->
                                    <?php
                                    if (!empty($event['media_files'])) {
                                        $media_array = explode(',', $event['media_files']);
                                        // Display first image if available
                                        foreach ($media_array as $media) {
                                            list($path, $type) = explode(':', $media);
                                            if ($type === 'image') {
                                                echo '<img src="' . htmlspecialchars($path) . '" class="img-fluid mb-3" alt="Event Image">';
                                                break;
                                            }
                                        }
                                        
                                        // Display first video if available
                                        foreach ($media_array as $media) {
                                            list($path, $type) = explode(':', $media);
                                            if ($type === 'video') {
                                                echo '<video class="img-fluid mb-3" controls>
                                                        <source src="' . htmlspecialchars($path) . '" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                      </video>';
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <!-- Event Information -->
                                    <div class="session-text-wrap">
                                        <h1 class="session-title"><?= htmlspecialchars($event['title']) ?></h1>
                                        <h3 class="session-sub-title"><?= htmlspecialchars($event['description']) ?></h3>
                                        <p class="session-date"><?= date('d M Y', strtotime($event['event_date'])) ?></p>
                                        <p class="location"><?= htmlspecialchars($event['location']) ?></p>
                                        <a href="events/details.php?id=<?= $event['id'] ?>">
                                            <button class="more">More Details</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics Section: Display member and partner counts -->
    <section class="third-section">
        <div class="container third-section-wrap">
            <div class="row">
                <!-- Member Count Display -->
                <div class="col-md-6">
                    <div class="member-section-wrap">
                        <div class="member-section">
                            <div class="member-text-wrap">
                                <div class="member-icon">
                                    <img src="assets/images/member-icon.png" alt="Members">
                                </div>
                                <div class="member-text">
                                    <h2 class="title">Total Members</h2>
                                    <p class="member-date">As of <?= date('M Y') ?></p>
                                </div>
                            </div>
                            <div class="member-count">
                                <div class="count-num"><?= $member_count ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Partner Count Display -->
                <div class="col-md-6">
                    <div class="member-section-wrap">
                        <div class="member-section">
                            <div class="member-text-wrap">
                                <div class="member-icon">
                                    <img src="assets/images/partner-icon.png" alt="Partners">
                                </div>
                                <div class="member-text">
                                    <h2 class="title">Active Partners</h2>
                                    <p class="member-date">Corporate Partners</p>
                                </div>
                            </div>
                            <div class="member-count">
                                <div class="count-num"><?= $partner_count ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>