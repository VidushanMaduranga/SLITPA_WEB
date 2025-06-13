<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
?>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Partners</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <!-- Include Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include custom styles -->
    <link rel="stylesheet" href="assets/css/partner.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
</head>

<body style="padding-top: 0px;">
    <!-- Header Section with Navigation -->
    <?php include 'includes/header.php'; ?>
    <!-- Hero Section: Main banner with welcome message -->
    <section class="hero-section">
        <div class="hero" style="position: relative;">
            <!-- Hero Background Image -->
            <div class="hero-image-wrap">
                <img src="/slitpa-web/assets/images/hero.png" alt="SLITPA Hero Image" class="hero-image">
            </div>
            <!-- Hero Content Overlay -->
            <div class="hero-text-wrap" >
                <h1 class="hero-title">Partners</h1>

                <div class="text-center mb-4">
                    <button class="btn filter-btn mx-1" data-category="all"
                        style="cursor:pointer; color: #fff; border:none;">All</button>
                    <button class="btn btn-outline-secondary mx-1 filter-btn" data-category="silver"
                        style="cursor:pointer; color: #fff;">Silver</button>
                    <button class="btn btn-outline-secondary mx-1 filter-btn" data-category="gold"
                        style="cursor:pointer; color: #fff;">Gold</button>
                    <button class="btn btn-outline-secondary mx-1 filter-btn" data-category="platinum"
                        style="cursor:pointer; color: #fff;">Platinum</button>
                </div>
            </div>
    </section>

    <!-- partners section -->
    <div class="container">
        <div class="partners-section">
            <!-- <h2 class="text-center mb-4">Our Partners</h2> -->
            <div class="row justify-content-center" id="partnersList">
                <?php
                $conn = new mysqli("localhost", "root", "", "slitpa_db");
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $sql = "SELECT id, name, company_name, partner_email, company_email, partner_number, company_number, logo, partner_category FROM partners WHERE status = 'paid'";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $cat = strtolower($row['partner_category']);
                        ?>
                <div class="col-12 mb-4 partner-card-row" data-category="<?= htmlspecialchars($cat) ?>">
                    <div class="card flex-row align-items-center shadow-sm p-3">
                        <?php if (!empty($row['logo'])): ?>
                        <img src="/slitpa-web/uploads/partners/<?php echo htmlspecialchars($row['logo']); ?>"
                            class="rounded-circle me-4" alt="<?php echo htmlspecialchars($row['name']); ?> Logo"
                            style="width:80px;height:80px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <h4 class="mb-1"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <div><strong>Company:</strong> <?php echo htmlspecialchars($row['company_name']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($row['partner_email']); ?></div>
                            <div><strong>Company Email:</strong> <?php echo htmlspecialchars($row['company_email']); ?>
                            </div>
                            <div><strong>Partner Number:</strong>
                                <?php echo htmlspecialchars($row['partner_number']); ?></div>
                            <div><strong>Company Number:</strong>
                                <?php echo htmlspecialchars($row['company_number']); ?></div>
                            <div><span
                                    class="badge bg-secondary text-capitalize"><?= htmlspecialchars($row['partner_category']) ?></span>
                            </div>
                        </div>
                        <button class="btn btn-primary ms-3 view-posts-btn" data-partner-id="<?php echo $row['id']; ?>"
                            data-partner-name="<?php echo htmlspecialchars($row['name']); ?>">View Posts</button>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="col-12 text-center"><p>No partners found.</p></div>';
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Partner Posts Modal -->
    <div class="modal fade" id="partnerPostsModal" tabindex="-1" aria-labelledby="partnerPostsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partnerPostsModalLabel">Partner Posts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="partnerPostsModalBody">
                    <!-- Posts will be loaded here -->
                    <div class="text-center text-muted">Loading posts...</div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Bootstrap 5 (no jQuery needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.view-posts-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('partnerPostsModalLabel').textContent = btn
                        .getAttribute('data-partner-name') + ' - Posts';
                    document.getElementById('partnerPostsModalBody').innerHTML =
                        '<div class="text-center text-muted">Loading posts...</div>';
                    var modal = new bootstrap.Modal(document.getElementById('partnerPostsModal'));
                    modal.show();
                    var partnerId = btn.getAttribute('data-partner-id');
                    fetch('get_partner_posts.php?partner_id=' + encodeURIComponent(partnerId))
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('partnerPostsModalBody').innerHTML = html;
                            if (window.glightboxInstance) window.glightboxInstance.destroy();
                            window.glightboxInstance = GLightbox({
                                selector: '.glightbox',
                                touchNavigation: true
                            });
                        })
                        .catch(() => {
                            document.getElementById('partnerPostsModalBody').innerHTML =
                                '<div class="text-danger">Failed to load posts.</div>';
                        });
                });
            });
        // Category filter
        document.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var cat = btn.getAttribute('data-category');
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove(
                    'active'));
                btn.classList.add('active');
                document.querySelectorAll('.partner-card-row').forEach(function(card) {
                    if (cat === 'all' || card.getAttribute('data-category') === cat) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
</body>

</html>