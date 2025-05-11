<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/config/config.php';

// Verify connection
if (!isset($pdo)) {
    die("Database connection not initialized");
}


// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use absolute path for config
require_once __DIR__ . '/config/config.php';

// Get current date
$current_date = date('Y-m-d');

try {
    // Get the current featured event (next upcoming event)
    $featured_event = $pdo->prepare("
        SELECT e.*, em.file_path as featured_image 
        FROM events e
        LEFT JOIN event_media em ON e.id = em.event_id AND em.is_featured = 1
        WHERE e.end_date >= ? 
        ORDER BY e.event_date ASC
        LIMIT 1
    ");
    $featured_event->execute([$current_date]);
    $featured_event = $featured_event->fetch();

    // Get all media for featured event if exists
    $featured_media = [];
    if ($featured_event) {
        $stmt = $pdo->prepare("SELECT * FROM event_media WHERE event_id = ? ORDER BY is_featured DESC");
        $stmt->execute([$featured_event['id']]);
        $featured_media = $stmt->fetchAll();
    }

    // Get other upcoming events (excluding the featured one)
    $other_events = $pdo->prepare("
        SELECT e.*, em.file_path as featured_image 
        FROM events e
        LEFT JOIN event_media em ON e.id = em.event_id AND em.is_featured = 1
        WHERE e.end_date >= ? AND e.id != ?
        ORDER BY e.event_date ASC
        LIMIT 3
    ");
    $other_events->execute([$current_date, $featured_event ? $featured_event['id'] : 0]);
    $other_events = $other_events->fetchAll();
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLITPA Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>
    <!-- Header Section -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero">
            <div class="hero-image-wrap">
                <img src="<?= BASE_URL ?>/assets/images/hero.png" alt="SLITPA Hero Image" class="hero-image">
            </div>
            <div class="hero-text-wrap">
                <h1 class="hero-title">Become a Member Today!</h1>
                <h3 class="hero-sub-title">EMPOWERING SRI LANKAN IT PROFESSIONALS IN THE UAE</h3>
                <a href="<?= BASE_URL ?>/member/register.php">
                    <button class="apply-member">Apply Now</button>
                </a>
            </div>
        </div>
    </section>

    <!-- Next Events Section -->
    <?php if ($featured_event): ?>
    <section class="event-first-section">
        <div class="event-first">
            <div class="row g-0">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="event-first-text-wrap">
                        <h1 class="event-first-title"><?= htmlspecialchars($featured_event['title']) ?></h1>
                        <h3 class="event-first-sub-title">
                            <?= htmlspecialchars($featured_event['location']) ?>
                        </h3>
                        <p class="event-paragraph">
                            <?= htmlspecialchars($featured_event['description']) ?>
                        </p>
                        <div class="button-wrap">
                            <a href="<?= BASE_URL ?>/events/event_details.php?id=<?= $featured_event['id'] ?>">
                                <img src="<?= BASE_URL ?>/assets/images/arrow-right.png" alt="View Event">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-6">
                    <?php if (!empty($featured_media)): ?>
                    <div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($featured_media as $index => $media): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <?php if ($media['media_type'] === 'image'): ?>
                                    <img src="<?= htmlspecialchars($media['file_path']) ?>" class="d-block w-100" alt="Event Media">
                                <?php else: ?>
                                    <video controls class="d-block w-100">
                                        <source src="<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($featured_media) > 1): ?>
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
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Other Events Section -->
    <?php if (!empty($other_events)): ?>
    <section class="event-second-section">
        <div class="container">
            <div class="row">
                <?php foreach ($other_events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($event['featured_image']): ?>
                            <img src="<?= htmlspecialchars($event['featured_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($event['location']) ?></p>
                            <p class="card-text"><small class="text-muted"><?= date('M j, Y', strtotime($event['event_date'])) ?></small></p>
                            <a href="<?= BASE_URL ?>/events/event_details.php?id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
      <?php endif; ?>


    <!-- third section -->
    <section class="third-section">
        <div class=" container third-section-wrap">
            <div class="row g-4">
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="session-wrap">
                        <div class="session">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="session-text-wrap">
                                        <h1 class="session-title">CPD Sessions</h1>
                                        <h3 class="session-sub-title">VALUE-CENTRIC CAPABILITIES OF GENERATIVE
                                            ORGANIZATIONS</h3>
                                        <p class="organizer">By Ms. Janani Liyanage</p>
                                        <p class="session-date">25th Nov 2025</p>
                                        <a href="#">
                                            <button class="more">More Details</button>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div id="cpdCarousel" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 1">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 2">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 3">
                                            </div>
                                        </div>
                                        <button class="carousel-control-prev d-none" type="button"
                                            data-bs-target="#cpdCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next d-none" type="button"
                                            data-bs-target="#cpdCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="member-section-wrap">
                        <div class="member-section">
                            <div class="row g-0">
                                <div class="col-8">
                                    <div class="member-text-wrap">
                                        <div class="member-icon">
                                            <img src="./assets/images/member.png" alt="">
                                        </div>
                                        <div class="member-text">
                                            <h3 class="title">Member</h3>
                                            <p class="member-date">25th nov 2025</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="member-count">
                                        <h3 class="count-num">320</h3>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="session-wrap">
                        <div class="session">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="session-text-wrap">
                                        <h1 class="session-title">PARTNERS</h1>
                                        <h3 class="session-sub-title">TECHDEAL</h3>
                                        <p class="part-session-paragraph">BUY LATEST LAPTOPS AT BEST PRICING</p>
                                        <p class="contact">Contact : 055 111 1111</p>
                                        <a href="#">
                                            <button class="more">More Details</button>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div id="partCarousel" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 1">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 2">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 3">
                                            </div>
                                        </div>
                                        <button class="carousel-control-prev d-none" type="button"
                                            data-bs-target="#partCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next d-none" type="button"
                                            data-bs-target="#partCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- partner-logo section -->
                    <div class="partner-section-wrap">
                        <div class="partner-section">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="partner-text-wrap">
                                        <div class="partner-text">
                                            <h3 class="title">Platinum</h3>
                                            <p class="partner">Partner</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="partner-logo">
                                        <div class="logo-icon">
                                            <img class="" src="./assets/images/SLITPA2-logo.png" alt="">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- event section -->
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="session-wrap">
                        <div class="session">
                            <div class="row g-0">
                                <div class="col-6">
                                    <div class="session-text-wrap">
                                        <h1 class="session-title">EVENTS</h1>
                                        <h3 class="session-sub-title">FAMILY DESERT NIGHT</h3>
                                        <p class="even-date">25th Nov 2025</p>
                                        <p class="location">Dubai UAE</p>
                                        <a href="#">
                                            <button class="more">more details</button>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div id="evenCarousel" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 1">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 2">
                                            </div>
                                            <div class="carousel-item">
                                                <img src="./assets/images/event.jpg" class="d-block w-100"
                                                    alt="Event 3">
                                            </div>
                                        </div>
                                        <button class="carousel-control-prev d-none" type="button"
                                            data-bs-target="#evenCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next d-none" type="button"
                                            data-bs-target="#evenCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        
                        <!-- Upcomming Event Section -->
                        <div class="u-event-section-wrap">
                            <div class="u-event-section">
                                <div class="row g-0">
                                    <div class="col-6">
                                        <div class="u-event-text-wrap">
                                            <div class="u-event-text">
                                                <h3 class="title">Upcomming Events</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="u-event-name-wrap">
                                            <h3 class="event-name">Event Name</h3>
                                            <p class="event-date">25th nov 2025</p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="fourth-section">

                <div class="row g-4">
                    <div class="col-12 col-md-4 col-lg-4">
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="session-wrap">
                            <div class="session">
                                <div class="row g-0">
                                    <div class="col-6">
                                        <div class="session-text-wrap">
                                            <h1 class="session-title">PARTNERS</h1>
                                            <h3 class="session-sub-title">TECHDEAL</h3>
                                            <p class="part-session-paragraph">BUY LATEST LAPTOPS AT BEST PRICING</p>
                                            <p class="contact">Contact : 055 111 1111</p>
                                            <a href="#">
                                                <button class="more">More Details</button>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="partCarousel" class="carousel slide" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <img src="./assets/images/event.jpg" class="d-block w-100"
                                                        alt="Event 1">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="./assets/images/event.jpg" class="d-block w-100"
                                                        alt="Event 2">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="./assets/images/event.jpg" class="d-block w-100"
                                                        alt="Event 3">
                                                </div>
                                            </div>
                                            <button class="carousel-control-prev d-none" type="button"
                                                data-bs-target="#partCarousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next d-none" type="button"
                                                data-bs-target="#partCarousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <div class="session-wrap">
                            <div class="session">
                                <div class="row g-0">
                                    <div class="col-6">
                                        <div class="session-text-wrap">
                                            <h1 class="session-title">EVENTS</h1>
                                            <h3 class="session-sub-title">FAMILY DESERT NIGHT</h3>
                                            <p class="even-date">25th Nov 2025</p>
                                            <p class="location">Dubai UAE</p>
                                            <a href="#">
                                                <button class="more">more details</button>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="evenCarousel" class="carousel slide" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <img src="./assets/images/event.jpg" class="d-block w-100"
                                                        alt="Event 1">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="./assets/images/event.jpg" class="d-block w-100"
                                                        alt="Event 2">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="./assets/images/event.jpg" class="d-block w-100"
                                                        alt="Event 3">
                                                </div>
                                            </div>
                                            <button class="carousel-control-prev d-none" type="button"
                                                data-bs-target="#evenCarousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next d-none" type="button"
                                                data-bs-target="#evenCarousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> -->
        </div>

    </section>

    <!-- <section class="hero bg-light text-center py-5">
    <div class="container">
      <h1 class="display-4">Welcome to the SLITPA Portal</h1>
      <p class="lead">Manage members, partners, and events all in one place.</p>
      <a href="member/register.php" class="btn btn-primary btn-lg me-2">Register as Member</a>
      <a href="partner/register.php" class="btn btn-outline-secondary btn-lg">Register as Partner</a>
    </div>
  </section>

  <section class="features py-5">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Member Management</h5>
              <p class="card-text">Register and manage your account, track approval status, and more.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Partner Area</h5>
              <p class="card-text">Register as a partner, get approved and share posts with the community.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">CPD Sessions & Events</h5>
              <p class="card-text">Stay updated with the latest events and professional development opportunities.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section> -->


    <img class="img-fluid" src="./assets\images\footer-line.png" alt="">

    <footer class="bg-dark text-white text-center py-2">
        <p class="mb-0">&copy; <?php echo date("Y"); ?> SLITPA. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script> <!-- Optional -->
</body>

</html>