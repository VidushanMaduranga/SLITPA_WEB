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
    <link rel="stylesheet" href="assets/css/about.css">
</head>

<body style="padding-top: 0px;">
    <!-- Header Section with Navigation -->
    <?php include 'includes/header.php'; ?>
    <!-- Hero Section: Main banner with welcome message -->
    <section class="hero-section">
        <div class="hero" style="position: relative; height: 30vh; overflow: hidden;">
            <!-- Hero Background Image -->
            <img src="" alt="">
            <div class="hero-image-wrap">
                <img src="assets/images/hero.png" alt="SLITPA Hero Image" class="hero-image" height="40vh" width="100%">
            </div>
            <!-- Hero Content Overlay -->
            <div class="hero-text-wrap">
                <h1 class="hero-title">About Us</h1>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- First Content -->
        <div class="first-content-section">
            <p class="paragraph">
                <span>
                    Sri Lankan Information Technology (IT) Professionals
                </span>
                (as defined in article 2.1.5 of the
                Constitution)
                Association in the United Arab Emirates (SLITPA-UAE) is a non-profit association of Sri Lankan IT
                professionals working and living in the United Arab Emirates with an objective of helping and
                improving
                the presence of Sri Lankan IT professional's footprint in the UAE market.
                <br>
                <br>
                <span>
                    If you an IT professional working and living in the United Arab Emirates, please come and join
                    with us.
                </span>
            </p>
        </div>

        <!-- Second Content -->
        <div class="second-content-section">
            <img src="./assets/images/slitpa-about 2.jpg" alt="">
        </div>

        <!-- Vision Section -->
        <div class="vision-section">
            <h2 class="vision-title">Vision</h2>
            <p class="vision-paragraph">
                To be the leading IT Professionals Association in the UAE to support, train and uplift Sri Lankan IT
                Professionals in the UAE while supporting the business community in Sri Lanka.
            </p>
        </div>

        <!-- Mission Section -->
        <div class="mission-section">
            <h2 class="mission-title">Mission</h2>
            <p class="mission-paragraph">
                Support members to uplift their qualifications with the highest international IT professional standards
                for achieving the emerging demand in IT Industry in UAE and IT Industry in Sri Lanka by sharing the
                knowledge.
            </p>
        </div>

        <!-- Objectives Section -->
        <div class="objectives-section">
            <div class="objectives">
                <h2 class="objectives-title">Objectives</h2>
                <div class="objectives-list">
                    <h3>BUSINESS PROMOTION</h3>
                    <p class="objective-sub-title">Business Promotion, Business Opportunities and Outsource
                        Opportunities in
                        relation to the IT sector</p>

                    <ul>
                        <li>Help Sri Lankan IT companies to set-up and conduct their business in UAE. </li>
                        <li>Help Sri Lankan IT companies to outsource IT project from UAE companies </li>
                        <li>Help Sri Lankans to establish IT companies in UAE</li>
                    </ul>
                </div>
                <div class="objectives-list">
                    <h3>WELFARE OPPORTUNITIES</h3>
                    <p class="objective-sub-title">Welfare (help Members of the SLITPA-UAE)</p>
                    <ul>
                        <li>Sharing IT job opportunities among Members. </li>
                        <li>IT related knowledge sharing </li>
                        <li>Conduct CPD programs for the Members.</li>
                        <li>Sharing training and certification opportunities for the Members.</li>
                        <li>Arranging and conducting workshops for IT related sublect matters</li>
                        <li>Social gatherings for the Members.</li>
                    </ul>
                </div>

                <div class="objectives-list">
                    <h3>SUPPORT SRI LANKA</h3>
                    <p class="objective-sub-title">Support Our Motherland</p>
                    <ul>
                        <li>Helping Sri Lankan IT </li>
                        <li>Professionals who are based in Sri Lanka to find job opportunities in UAE. </li>
                        <li>Organize and conduct</li>
                        <li>Charity and Welfare projects related to the IT in Sri Lanka and UAE</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>