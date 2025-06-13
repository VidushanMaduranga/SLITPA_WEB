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
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <title>About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include custom styles -->
    <link rel="stylesheet" href="assets/css/committee.css">
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
                <h1 class="hero-title">executive Committee</h1>
                <p style="color: #fff; font-size: 1.2rem;">
                    <span class="committee-year-selector" data-year="2021" style="cursor:pointer;">2021</span> &nbsp;
                    <span class="committee-year-selector" data-year="2022" style="cursor:pointer;">2022</span> &nbsp;
                    <span class="committee-year-selector" data-year="2023" style="cursor:pointer;">2023</span> &nbsp;
                    <span class="committee-year-selector fw-bold" data-year="2024"
                        style="cursor:pointer;"><strong>2024</strong></span> &nbsp;
                    <span class="committee-year-selector" data-year="2025" style="cursor:pointer;">2025</span>
                </p>
            </div>
        </div>
    </section>
    <!-- Members Section 2021 -->
    <div class="container my-5 committee-members" id="committee-2021" style="display:none;">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            <!-- Repeat this card for each member -->
            <div class="col">
                <div class="card h-100 text-center">
                    <img src="https://via.placeholder.com/300x300" class="card-img-top" alt="Profile Image">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">Kuminda Liyanage</h6>
                        <p class="card-text">President</p>
                        <a href="#" class="linkedin-icon"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <!-- ... other 2021 members ... -->
        </div>
    </div>

    <!-- Members Section 2022 -->
    <div class="container my-5 committee-members" id="committee-2022" style="display:none;">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            <!-- Repeat this card for each member -->
            <div class="col">
                <div class="card h-100 text-center">
                    <img src="https://via.placeholder.com/300x300" class="card-img-top" alt="Profile Image">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">Kuminda Liyanage</h6>
                        <p class="card-text">President</p>
                        <a href="#" class="linkedin-icon"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <!-- ... other 2022 members ... -->
        </div>
    </div>
    <!-- Add similar containers for 2023, 2024, 2025 as needed -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show 2024 by default
        var defaultYear = '2024';
        var showCommittee = function(year) {
            // Hide all
            document.querySelectorAll('.committee-members').forEach(function(el) {
                el.style.display = 'none';
            });
            // Show selected
            var section = document.getElementById('committee-' + year);
            if (section) section.style.display = '';

            // Update bold style
            document.querySelectorAll('.committee-year-selector').forEach(function(el2) {
                el2.classList.remove('fw-bold');
                if (el2.querySelector('strong')) el2.innerHTML = el2.textContent;
            });
            var active = document.querySelector('.committee-year-selector[data-year="' + year + '"]');
            if (active) {
                active.classList.add('fw-bold');
                active.innerHTML = '<strong>' + active.textContent + '</strong>';
            }
        };

        // Initial display
        var defaultSection = document.getElementById('committee-' + defaultYear);
        if (defaultSection) {
            showCommittee(defaultYear);
        } else {
            var firstSection = document.querySelector('.committee-members');
            if (firstSection) {
                var firstYear = firstSection.id.replace('committee-', '');
                showCommittee(firstYear);
            }
        }

        // Year selector click
        document.querySelectorAll('.committee-year-selector').forEach(function(el) {
            el.addEventListener('click', function() {
                var year = this.getAttribute('data-year');
                showCommittee(year);
            });
        });
    });
    </script>
    <!-- Footer Section -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>