<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/slitpa-web/assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
</head>
<body>
        <!-- Header Section with Navigation -->
    <?php include 'includes/header.php'; ?>
    <!-- Hero Section (restored from original design) -->
    <section class="hero-section">
        <div class="hero">
            <div class="hero-image-wrap">
                <img src="assets/images/hero.png" alt="Events Hero" class="hero-image">
            </div>
            <div class="hero-text-wrap">
                <div class="hero-title">EVENTS</div>
                <div class="hero-sub-title">
                    <span id="upcoming-tab-link" class="me-3" style="cursor:pointer; font-weight:bold; text-decoration:underline;">UPCOMING EVENTS</span>
                    <span id="past-tab-link" style="cursor:pointer; font-weight:bold;">PAST EVENTS</span>
                </div>
            </div>
        </div>
    </section>
    <!-- Main Event Section -->
    <section class="py-5">
        <div class="container d-flex flex-column align-items-center">
            <div class="tab-content mt-4 w-100" style="max-width: 1100px;">
                <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                    <div class="card rounded-4 shadow p-4 mb-5 bg-white border-0">
                        <div id="upcoming-events-calendar"></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="past" role="tabpanel">
                    <div id="past-events-list" class="w-100"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" id="event-modal-content">
          <!-- Event details will be loaded here -->
        </div>
      </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/event.js"></script>
<script>
// Custom tab switching for hero section links
const upcomingTabLink = document.getElementById('upcoming-tab-link');
const pastTabLink = document.getElementById('past-tab-link');
const upcomingTab = document.getElementById('upcoming');
const pastTab = document.getElementById('past');

upcomingTabLink.addEventListener('click', function() {
    upcomingTab.classList.add('show', 'active');
    pastTab.classList.remove('show', 'active');
    upcomingTabLink.style.textDecoration = 'underline';
    pastTabLink.style.textDecoration = 'none';
});
pastTabLink.addEventListener('click', function() {
    pastTab.classList.add('show', 'active');
    upcomingTab.classList.remove('show', 'active');
    pastTabLink.style.textDecoration = 'underline';
    upcomingTabLink.style.textDecoration = 'none';
});
</script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
</body>
</html>
