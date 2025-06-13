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
    <title>Contact US</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include custom styles -->
    <link rel="stylesheet" href="assets/css/contact-us.css">
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
                <h1 class="hero-title">contact Us</h1>
            </div>
        </div>
    </section>
    <div class="container">
        <!-- Contact Form Section -->
        <div class="contact-form-section">
            <h2>Get in Touch</h2>
            <form action="process_contact.php" method="POST" class="contact-form">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>

        <!-- Contact Information Section -->
        <div class="contact-info-section">
            <h2>Contact Information</h2>
            <p>Email: <a href="mailto:vidusanmaduranga0@gmail.com">vidushanmaduranga0@gmail.com</a></p>
            <p>Phone: <a href="tel:+97112345678">+971 123 456 78</a></p>

        </div>

    </div>
            <!-- Map Section -->
        <div class="map-section">
            <h2>Our Location</h2>
            <div class="ratio ratio-16x9">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.1234567890123!2d79.8612433153345!3d6.927078795000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2595b1234567%3A0xabcdef1234567890!2sColombo%2C%20Sri%20Lanka!5e0!3m2!1sen!2slk!4v1680000000000!5m2!1sen!2slk"
                    width="100%"
                    height="20vh"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Google Map Location"></iframe>
            </div>
        </div>
    <!-- Footer Section -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    </script>
</body>

</html>