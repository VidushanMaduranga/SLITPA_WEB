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
    // Fetch past events with their associated media
    $stmt = $pdo->prepare("
        SELECT e.*, GROUP_CONCAT(em.file_path, ':', em.media_type) as media_files 
        FROM events e
        LEFT JOIN event_media em ON e.id = em.event_id
        WHERE e.event_date < ?
        GROUP BY e.id
        ORDER BY e.event_date DESC
        LIMIT 3
    ");
    $stmt->execute([$current_date]);
    $past_events = $stmt->fetchAll();

    // Get total member count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM members");
    $member_count = $stmt->fetch()['count'];

    // Get active partner count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM partners WHERE status = 'active'");
    $partner_count = $stmt->fetch()['count'];

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $past_events = [];
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
                
                <img src="assets/images/hero.png" alt="SLITPA Hero Image" class="hero-image">
            </div>
            <!-- Hero Content Overlay -->
            <div class="hero-text-wrap">
                <h1 class="hero-title">Welcome to SLITPA</h1>
                <h3 class="hero-sub-title">Sri Lankan IT Professionals Association - UAE</h3>
                <a href="register.php" class="apply-member">Become a Member</a>
            </div>
        </div>
    </section>

    <!-- Events Section: Display past events -->
    <?php if (!empty($past_events)): ?>
    <section class="third-section">
        <div class="third-section-wrap">
            <!-- <h2 class="text-center mb-4">Past Events</h2> -->
            <!-- Main Event Carousel -->
            <div id="mainEventCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                <div class="carousel-inner">
                    <?php 
                    $firstEvent = true;
                    foreach ($past_events as $event): 
                    ?>
                    <div class="carousel-item <?= $firstEvent ? 'active' : '' ?>">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="session-wrap">
                                    <div class="session">
                                        <div class="row g-0">
                                            <!-- Event Description (Left Side) -->
                                            <div class="col-md-6">
                                                <div class="session-text-wrap">
                                                    <h1 class="session-title"><?= htmlspecialchars($event['title']) ?></h1>
                                                    <h3 class="session-sub-title"><?= htmlspecialchars($event['description']) ?></h3>
                                                    <p class="session-date"><?= date('d M Y', strtotime($event['event_date'])) ?></p>
                                                    <p class="location"><?= htmlspecialchars($event['location']) ?></p>
                                                    <a href="events/details.php?id=<?= $event['id'] ?>">
                                                        <button class="more">View Event Details</button>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <!-- Event Media Slider (Right Side) -->
                                            <div class="col-md-6">
                                                <?php if (!empty($event['media_files'])): ?>
                                                <div class="slide">
                                                    <div id="mediaCarousel<?= $event['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                                        <div class="carousel-inner">
                                                            <?php 
                                                            $media_array = explode(',', $event['media_files']);
                                                            $firstMedia = true;
                                                            foreach ($media_array as $media):
                                                                list($path, $type) = explode(':', $media);
                                                            ?>
                                                            <div class="carousel-item <?= $firstMedia ? 'active' : '' ?>">
                                                                <?php if ($type === 'image'): ?>
                                                                    <img src="<?= htmlspecialchars($path) ?>" class="d-block w-100" alt="Event Image">
                                                                <?php elseif ($type === 'video'): ?>
                                                                    <video class="d-block w-100" controls>
                                                                        <source src="<?= htmlspecialchars($path) ?>" type="video/mp4">
                                                                        Your browser does not support the video tag.
                                                                    </video>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php 
                                                            $firstMedia = false;
                                                            endforeach; 
                                                            ?>
                                                        </div>
                                                        <?php if (count($media_array) > 1): ?>
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#mediaCarousel<?= $event['id'] ?>" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Previous</span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#mediaCarousel<?= $event['id'] ?>" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                            <span class="visually-hidden">Next</span>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $firstEvent = false;
                    endforeach; 
                    ?>
                </div>
                
                <!-- Main Event Carousel Controls -->
                <?php if (count($past_events) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#mainEventCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#mainEventCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Add JavaScript for carousel coordination -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all media carousels
        const mediaCarousels = document.querySelectorAll('[id^="mediaCarousel"]');
        const mainEventCarousel = document.getElementById('mainEventCarousel');
        
        // Initialize all media carousels with different intervals
        mediaCarousels.forEach(carousel => {
            const bsCarousel = new bootstrap.Carousel(carousel, {
                interval: 3000, // 3 seconds per media item
                ride: 'carousel'
            });

            // When a media carousel ends, trigger the next event
            carousel.addEventListener('slid.bs.carousel', function(event) {
                const items = event.target.querySelectorAll('.carousel-item');
                const activeIndex = Array.from(items).findIndex(item => item.classList.contains('active'));
                
                // If we're at the last media item
                if (activeIndex === items.length - 1) {
                    // Wait for the last media item to be shown for its full duration
                    setTimeout(() => {
                        // Move to the next event
                        const mainCarousel = bootstrap.Carousel.getInstance(mainEventCarousel);
                        mainCarousel.next();
                    }, 3000); // Same as the interval above
                }
            });
        });

        // Initialize the main event carousel (initially paused)
        const mainCarousel = new bootstrap.Carousel(mainEventCarousel, {
            interval: false,
            ride: false
        });
    });
    </script>
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

    <!-- Event Carousel Section -->
    <section class="event-carousel-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Our Events</h2>
            <!-- Main Event Carousel -->
            <div id="eventMainCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                <div class="carousel-inner">
                    <?php 
                    // Get events with their media
                    $events_query = $pdo->query("
                        SELECT e.*, GROUP_CONCAT(em.file_path, ':', em.media_type) as media_files 
                        FROM events e 
                        LEFT JOIN event_media em ON e.id = em.event_id 
                        GROUP BY e.id 
                        ORDER BY e.event_date DESC 
                        LIMIT 10
                    ");
                    $events = $events_query->fetchAll();
                    
                    // Show two events per slide
                    $total_events = count($events);
                    $slides = array_chunk($events, 2);
                    
                    foreach ($slides as $index => $slide_events):
                    ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="row">
                            <?php foreach ($slide_events as $event): ?>
                            <div class="col-md-6">
                                <div class="event-carousel-item">
                                    <div class="row">
                                        <!-- Event Details (Left Side) -->
                                        <div class="col-md-6">
                                            <div class="event-details">
                                                <h3><?= htmlspecialchars($event['title']) ?></h3>
                                                <p class="event-date"><?= date('d M Y', strtotime($event['event_date'])) ?></p>
                                                <p class="event-location"><?= htmlspecialchars($event['location']) ?></p>
                                                <p class="event-description"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                                                <a href="events/event_details.php?id=<?= $event['id'] ?>" class="btn btn-primary">Learn More</a>
                                            </div>
                                        </div>
                                        <!-- Media Carousel (Right Side) -->
                                        <div class="col-md-6">
                                            <?php if (!empty($event['media_files'])): ?>
                                            <div id="mediaCarousel<?= $event['id'] ?>" class="carousel slide media-carousel" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    <?php 
                                                    $media_items = array_map(function($item) {
                                                        list($path, $type) = explode(':', $item);
                                                        return ['path' => $path, 'type' => $type];
                                                    }, explode(',', $event['media_files']));
                                                    
                                                    // Show two media items per slide
                                                    $media_slides = array_chunk($media_items, 2);
                                                    
                                                    foreach ($media_slides as $media_index => $media_group):
                                                    ?>
                                                    <div class="carousel-item <?= $media_index === 0 ? 'active' : '' ?>">
                                                        <div class="row">
                                                            <?php foreach ($media_group as $media): ?>
                                                            <div class="col-6">
                                                                <?php if ($media['type'] === 'image'): ?>
                                                                    <img src="<?= htmlspecialchars($media['path']) ?>" class="d-block w-100" alt="Event Image">
                                                                <?php else: ?>
                                                                    <video class="d-block w-100" muted>
                                                                        <source src="<?= htmlspecialchars($media['path']) ?>" type="video/mp4">
                                                                    </video>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php if (count($media_slides) > 1): ?>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#mediaCarousel<?= $event['id'] ?>" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#mediaCarousel<?= $event['id'] ?>" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($slides) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#eventMainCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#eventMainCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Add JavaScript for carousel coordination -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all media carousels
        const mediaCarousels = document.querySelectorAll('.media-carousel');
        const mainCarousel = document.getElementById('eventMainCarousel');
        const mainCarouselInstance = new bootstrap.Carousel(mainCarousel, {
            interval: false
        });

        // Initialize media carousels with auto-play
        mediaCarousels.forEach(carousel => {
            const mediaCarouselInstance = new bootstrap.Carousel(carousel, {
                interval: 3000,
                ride: 'carousel'
            });

            // Track media carousel progress
            let mediaItemsComplete = false;

            carousel.addEventListener('slid.bs.carousel', function(event) {
                const items = event.target.querySelectorAll('.carousel-item');
                const activeIndex = Array.from(items).findIndex(item => item.classList.contains('active'));
                
                // If we're at the last media item
                if (activeIndex === items.length - 1) {
                    mediaItemsComplete = true;
                    // Wait for the last item to be shown
                    setTimeout(() => {
                        // Reset flag and move main carousel
                        mediaItemsComplete = false;
                        mainCarouselInstance.next();
                    }, 3000);
                }
            });
        });

        // When main carousel slides, reset media carousels
        mainCarousel.addEventListener('slid.bs.carousel', function() {
            mediaCarousels.forEach(carousel => {
                const instance = bootstrap.Carousel.getInstance(carousel);
                if (instance) {
                    instance.to(0);
                }
            });
        });
    });
    </script>

    <!-- Footer Section -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>