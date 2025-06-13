<?php
/**
 * SLITPA Website Homepage
 * Main landing page displaying events, member counts, and partner information
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load database configuration
require_once __DIR__ . '/config/config.php';

// Dynamic base URL for media paths
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($baseUrl === '/' || $baseUrl === '\\') $baseUrl = '';

// Verify database connection
if (!isset($pdo)) {
    die("Database connection not initialized");
}

// Get current date for event filtering
$current_date = date('Y-m-d');

$last_registered_date_fmt = $last_registered_date_fmt ?? '';
$paid_member_count = $paid_member_count ?? 0;

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

// Fetch latest news and their media for the homepage news carousel
$news_stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC, created_at DESC LIMIT 5");
$news_list = $news_stmt->fetchAll();
$news_ids = array_column($news_list, 'id');
$media_by_news = [];
if ($news_ids) {
    $in = str_repeat('?,', count($news_ids) - 1) . '?';
    $stmtMedia = $pdo->prepare("SELECT * FROM news_media WHERE news_id IN ($in) ORDER BY display_order, id");
    $stmtMedia->execute($news_ids);
    foreach ($stmtMedia->fetchAll() as $media) {
        $media_by_news[$media['news_id']][] = $media;
    }
}

// Fetch all paid partners
$partner_stmt = $pdo->query("SELECT * FROM partners WHERE status = 'paid' ORDER BY id DESC");
$partners = $partner_stmt->fetchAll();
if (empty($partners)) {
    echo "<div class='alert alert-warning'>No partners found.</div>";
}

// Fetch all media for partners' posts
$partnerMedia = [];
foreach ($partners as $partner) {
    $stmt = $pdo->prepare("SELECT media FROM partner_posts WHERE partner_id = ?");
    $stmt->execute([$partner['id']]);
    $mediaFiles = [];
    while ($row = $stmt->fetch()) {
        if (!empty($row['media'])) {
            $mediaArr = explode(',', $row['media']);
            foreach ($mediaArr as $media) {
                $media = trim($media);
                if ($media) $mediaFiles[] = $media;
            }
        }
    }
    $partnerMedia[$partner['id']] = $mediaFiles;
}

// Fetch latest CPD session and its media
$cpdSession = $pdo->query("SELECT * FROM cpd_sessions ORDER BY session_date DESC LIMIT 1")->fetch();
$cpdMediaList = [];
if ($cpdSession) {
    $stmt = $pdo->prepare("SELECT * FROM cpd_session_media WHERE session_id = ?");
    $stmt->execute([$cpdSession['id']]);
    $cpdMediaList = $stmt->fetchAll();
}

// Fetch the first upcoming event (event_date >= today)
$upcoming_event = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC LIMIT 1");
    $stmt->execute([$current_date]);
    $upcoming_event = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Database error (upcoming event): " . $e->getMessage());
    $upcoming_event = null;
}
?>
<?php
// Get paid member count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM members WHERE payment_status = 'paid'");
$paid_member_count = $stmt->fetch()['count'] ?? 0;

// Get last registered member date
$stmt = $pdo->query("SELECT created_at FROM members ORDER BY created_at DESC LIMIT 1");
$last_registered_date = $stmt->fetch()['created_at'] ?? null;
$last_registered_date_fmt = $last_registered_date ? date('d-F-Y', strtotime($last_registered_date)) : '-';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLITPA - Sri Lankan IT Professionals Association</title>
    <!-- Include Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include custom styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body style="padding-top: 0;">
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
                <!-- <a href="register.php" class="apply-member">Become a Member</a> -->
            </div>
        </div>
    </section>



    <!-- SLITPA NEWS -->
    <section class="slitpa-news text-white">
        <div class="container-fluid">
            <?php if (empty($news_list)): ?>
            <div class="alert alert-info">No news available at this time.</div>
            <?php else: 
                // Get the latest news item
                $news = $news_list[0];
                $mediaList = $media_by_news[$news['id']] ?? [];
            ?>
            <div class="row g-4">
                <!-- Left: News details -->
                <div class="col-12 col-md-6">
                    <h4><?= htmlspecialchars($news['title']) ?></h4>
                    <div class="date-news">
                        <?= date('F j, Y', strtotime($news['published_date'])) ?>
                    </div>
                    <p class="news-description-clamp" style="">
                        <?= htmlspecialchars(strip_tags($news['content'])) ?>
                    </p>
                    <div class="buton-wrap">
                        <button class="btn btn-link p-0 mt-2" data-bs-toggle="modal"
                            data-bs-target="#newsModal<?= $news['id'] ?>"><img width="40px"
                                src="assets\images\arrow-right.png" alt=""></button>
                    </div>

                </div>



                <!-- Modal for full details and gallery -->
                <div class="modal fade fade-wrap" id="newsModal<?= $news['id'] ?>" tabindex="-1"
                    aria-labelledby="newsModalLabel<?= $news['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog-wrap modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="newsModalLabel<?= $news['id'] ?>">
                                    <?= htmlspecialchars($news['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark">
                                <div class="mb-3 text-muted">Published:
                                    <?= date('F j, Y', strtotime($news['published_date'])) ?></div>
                                <div class="mb-3"><?= nl2br(htmlspecialchars($news['content'])) ?></div>
                                <div class="row">
                                    <?php foreach ($mediaList as $media): ?>
                                    <div class="col-md-4 mb-3">
                                        <?php if ($media['media_type'] === 'image'): ?>
                                        <img src="<?= $baseUrl ?>/<?= htmlspecialchars($media['file_path']) ?>"
                                            class="img-fluid rounded" alt="News Image">
                                        <?php elseif ($media['media_type'] === 'video'): ?>
                                        <video src="<?= $baseUrl ?>/<?= htmlspecialchars($media['file_path']) ?>"
                                            class="img-fluid rounded" controls></video>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Right: Slideshow of images and videos -->
                <div class="col-12 col-md-6">
                    <div id="newsGallery<?= $news['id'] ?>" class="news-gallery" style="">
                        <?php if (empty($mediaList)): ?>
                        <span style="color: #888; font-size: 1.1em;">No images or videos for this news item.</span>
                        <?php else: ?>
                        <?php foreach ($mediaList as $idx => $media): ?>
                        <div class="gallery-media" style="display: <?= $idx === 0 ? 'block' : 'none' ?>; width: 100%;">
                            <?php if ($media['media_type'] === 'image'): ?>
                            <img src="<?= $baseUrl ?>/<?= htmlspecialchars($media['file_path']) ?>"
                                class="img-fluid rounded shadow" alt="News Image"
                                style="width: 100%; height: 35vh; object-fit: contain;">
                            <?php elseif ($media['media_type'] === 'video'): ?>
                            <video src="<?= $baseUrl ?>/<?= htmlspecialchars($media['file_path']) ?>"
                                class="img-fluid rounded shadow" controls
                                style="width: 100%; max-height: 32vh; object-fit: contain;"></video>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- third section -->
    <section class="third-section">
        <div class="container-fluid third-content">
            <div class="row g-4">
                <!-- CPD SESSION SECTION -->
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="cpd-wrap text-white">
                        <div class="row g-0">
                            <div class="col-12 col-md-6">
                                <p>CPD Sessions</p>
                                <?php if ($cpdSession): ?>
                                <h4><?= htmlspecialchars($cpdSession['title']) ?></h4>
                                <div class="date-news">
                                    <?= htmlspecialchars($cpdSession['session_date']) ?>
                                </div>
                                <div class="location-session">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($cpdSession['location'] ?? '') ?>
                                </div>
                                <div class="location-session">
                                    <?= htmlspecialchars($cpdSession['organizer']) ?></div>
                                <div class="buton-wrap">
                                    <button class="btn btn-link p-0 mt-2" data-bs-toggle="modal"
                                        data-bs-target="#cpdModal<?= $cpdSession['id'] ?>">
                                        more details
                                    </button>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">No CPD sessions found.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <div id="cpdGallery<?= $cpdSession['id'] ?? '' ?>" class="cpd-session-gallery">
                                    <?php if (empty($cpdMediaList)): ?>
                                    <span style="color: #888; font-size: 1.1em;">No images or videos for this
                                        session.</span>
                                    <?php else: ?>
                                    <?php foreach ($cpdMediaList as $idx => $media): ?>
                                    <div class="gallery-media"
                                        style="display: <?= $idx === 0 ? 'block' : 'none' ?>; width: 100%;">
                                        <?php if ($media['media_type'] === 'image'): ?>
                                        <img src="<?= $baseUrl ?>/uploads/cpd_sessions/<?= htmlspecialchars($media['file_path']) ?>"
                                            class="img-fluid rounded shadow" alt="Session Media"
                                            style="width: 100%; height: 30vh; object-fit: cover; border-radius: 20px;">
                                        <?php elseif ($media['media_type'] === 'video'): ?>
                                        <video
                                            src="<?= $baseUrl ?>/uploads/cpd_sessions/<?= htmlspecialchars($media['file_path']) ?>"
                                            class="img-fluid rounded shadow" controls
                                            style="width: 100%; max-height: 30vh; object-fit: cover; border-redius:20px;"></video>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($cpdSession): ?>
                    <!-- Modal for full details and gallery -->
                    <div class="modal fade fade-wrap" id="cpdModal<?= $cpdSession['id'] ?>" tabindex="-1"
                        aria-labelledby="cpdModalLabel<?= $cpdSession['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog-wrap modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cpdModalLabel<?= $cpdSession['id'] ?>">
                                        <?= htmlspecialchars($cpdSession['title']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-dark">
                                    <div class="mb-3 text-muted">Date:
                                        <?= htmlspecialchars($cpdSession['session_date']) ?></div>
                                    <div class="mb-3"><strong>Location:</strong>
                                        <?= htmlspecialchars($cpdSession['location'] ?? '') ?></div>
                                    <div class="mb-3"><strong>Organizer:</strong>
                                        <?= htmlspecialchars($cpdSession['organizer']) ?></div>
                                    <div class="mb-3"><strong>Added:</strong>
                                        <?= htmlspecialchars($cpdSession['created_at'] ?? '') ?></div>
                                    <div class="row">
                                        <?php foreach ($cpdMediaList as $media): ?>
                                        <div class="col-md-4 mb-3">
                                            <?php if ($media['media_type'] === 'image'): ?>
                                            <img src="<?= $baseUrl ?>/uploads/cpd_sessions/<?= htmlspecialchars($media['file_path']) ?>"
                                                class="img-fluid rounded" alt="Session Image">
                                            <?php elseif ($media['media_type'] === 'video'): ?>
                                            <video
                                                src="<?= $baseUrl ?>/uploads/cpd_sessions/<?= htmlspecialchars($media['file_path']) ?>"
                                                class="img-fluid rounded" controls></video>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="member">
                        <div class="row g-0">
                            <div class="col-7">
                                <div class="member-left-wrap">
                                    <div class="member-left">
                                        <div class="member-icon m-2">
                                            <img src="./assets/images/member.png" alt="" class="member-logo">
                                        </div>
                                        <div class="meber-text-wrap m-2">
                                            <h5 class="member-title">MEMBERS</h5>
                                            <p class="date">
                                                <?= htmlspecialchars($last_registered_date_fmt ?? '') ?>
                                                <!-- Last registered member date -->
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="member-count-wrap">
                                    <h1 class="member-count">
                                        <?= htmlspecialchars($paid_member_count ?? '') ?>
                                        <!-- Paid member count -->
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PARTNER SECTION -->
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="partner-wrap">
                        <?php if (empty($partners)): ?>
                        <div class="alert alert-info">No partners available at this time.</div>
                        <?php else: ?>
                        <div id="partnersCarousel" class="partners-carousel">
                            <?php foreach ($partners as $index => $partner): ?>
                            <div class="partner-slide" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
                                <div class="partner-slider-wrap">
                                    <div class="row">
                                        <!-- Left: Partner details -->
                                        <div class="col-6 ">
                                            <p>partners</p>
                                            <h4><?= htmlspecialchars($partner['name']) ?></h4>
                                            <div class="mb-2" style="color: #fff; padding: 0 10px;">
                                                <?= htmlspecialchars($partner['company_name']) ?>
                                            </div>
                                            <div class="mb-2" style="color: #fff; padding: 0 10px;">
                                                <?= htmlspecialchars($partner['partner_number']) ?><br>
                                            </div>
                                            <!-- More Details Button -->
                                            <button class="more" 
                                                style="text-decoration: none;
                                                    border: solid 1px #ffffff;
                                                    background-color: transparent;
                                                    width: calc(100% - 40px);
                                                    max-width: 300px;
                                                    min-width: 120px;
                                                    border-radius: 20px;
                                                    transition: background 0.3s, color 0.3s, box-shadow 0.3s;
                                                    margin-left: 20px;
                                                    color: #ffffff;
                                                    text-t"
                                                data-bs-toggle="modal" data-bs-target="#partnerModal<?= $partner['id'] ?>">
                                                more details
                                            </button>

                                            <!-- Partner Modal -->
                                            <div class="modal fade fade-wrap" id="partnerModal<?= $partner['id'] ?>" tabindex="-1"
                                                aria-labelledby="partnerModalLabel<?= $partner['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog-wrap modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="partnerModalLabel<?= $partner['id'] ?>">
                                                                <?= htmlspecialchars($partner['name']) ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-dark">
                                                            <div class="mb-2"><strong>Company Name:</strong> <?= htmlspecialchars($partner['company_name']) ?></div>
                                                            <div class="mb-2"><strong>Partner Number:</strong> <?= htmlspecialchars($partner['partner_number']) ?></div>
                                                            <div class="mb-2"><strong>Category:</strong> <?= htmlspecialchars($partner['partner_category']) ?></div>
                                                            <div class="mb-2"><strong>Status:</strong> <?= htmlspecialchars($partner['status']) ?></div>
                                                            <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($partner['email'] ?? '-') ?></div>
                                                            <div class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($partner['phone'] ?? '-') ?></div>
                                                            <div class="mb-2"><strong>Description:</strong><br>
                                                                <?= nl2br(htmlspecialchars($partner['description'] ?? '-')) ?>
                                                            </div>
                                                            <hr>
                                                            <h6>Posts</h6>
                                                            <?php
                                                            $stmt = $pdo->prepare("SELECT * FROM partner_posts WHERE partner_id = ? ORDER BY created_at DESC");
                                                            $stmt->execute([$partner['id']]);
                                                            $posts = $stmt->fetchAll();
                                                            ?>
                                                            <?php if (empty($posts)): ?>
                                                                <div class="alert alert-info">No posts available for this partner.</div>
                                                            <?php else: ?>
                                                                <?php foreach ($posts as $post): ?>
                                                                    <div class="mb-4 border rounded p-2">
                                                                        <div class="mb-1"><strong><?= htmlspecialchars($post['title'] ?? 'Untitled Post') ?></strong></div>
                                                                        <div class="mb-1 text-muted" style="font-size:0.95em;">
                                                                            <?= date('d M Y', strtotime($post['created_at'])) ?>
                                                                        </div>
                                                                        <div class="mb-2"><?= nl2br(htmlspecialchars($post['content'] ?? '')) ?></div>
                                                                        <?php
                                                                        $mediaArr = [];
                                                                        if (!empty($post['media'])) {
                                                                            $mediaArr = array_filter(array_map('trim', explode(',', $post['media'])));
                                                                        }
                                                                        ?>
                                                                        <?php if (!empty($mediaArr)): ?>
                                                                            <div class="row g-2">
                                                                                <?php foreach ($mediaArr as $mediaFile): 
                                                                                    $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));
                                                                                ?>
                                                                                    <div class="col-6 col-md-4">
                                                                                        <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                                                                            <img src="/slitpa-web/uploads/posts/<?= htmlspecialchars($mediaFile) ?>"
                                                                                                class="img-fluid rounded shadow mb-2" alt="Post Media"
                                                                                                style="width:100%;height:150px;object-fit:cover;">
                                                                                        <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                                                                                            <video src="/slitpa-web/uploads/posts/<?= htmlspecialchars($mediaFile) ?>" controls
                                                                                                class="img-fluid rounded shadow mb-2"
                                                                                                style="width:100%;height:150px;object-fit:cover;"></video>
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Right: Partner image FULL WIDTH -->
                                        <div class="col-6" style="height:30vh;">
                                            <div id="partnerImagesCarousel<?= $partner['id'] ?>" class="partner-images-carousel w-100 h-100" style="border-radius: 0 20px 20px 0; overflow: hidden;">
                                                <?php 
                                                $media = $partnerMedia[$partner['id']] ?? [];
                                                foreach ($media as $index => $file): 
                                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                                ?>
                                                <div class="partner-image-item" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
                                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                                    <img src="/slitpa-web/uploads/posts/<?= htmlspecialchars($file) ?>"
                                                        class="img-fluid shadow"
                                                        style="width:100%;height:30vh;object-fit:cover;display:block;">
                                                    <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                                                    <video src="/slitpa-web/uploads/posts/<?= htmlspecialchars($file) ?>" controls
                                                        style="width:100%;height:100%;object-fit:cover;display:block;"></video>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="partner-down-wrap">
                                    <div class="row g-0">
                                        <div class="col-6">
                                            <div class="partner-left-wrap">
                                                <?php if (!empty($partner['partner_category'])): ?>
                                                <span class="badge badge-category <?= htmlspecialchars(strtolower($partner['partner_category'])) ?>">
                                                    <?= htmlspecialchars(ucfirst($partner['partner_category'])) ?>
                                                </span>
                                                <?php endif; ?>
                                                <h5 class="partner-title">PARTNERS</h5>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="partner-logo-wrap">
                                                <?php if (!empty($partner['logo'])): ?>
                                                <img src="/slitpa-web/uploads/partners/<?= htmlspecialchars($partner['logo']) ?>"
                                                    alt="Partner Logo" class="partner-logo"
                                                    style="">
                                                <?php else: ?>
                                                <span style="color:#888;">No logo available.</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- EVENT SECTION -->
                <div class="col-12 col-md-4 col-lg-4">
                    <div class="event-wrap">
                        <div class="uc-event">
                            <!-- Events Section: Display past events -->
                            <?php if (!empty($past_events)): ?>
                            <section class="p-event-section">
                                <div class="p-event-section-wrap">
                                    <div id="mainEventCarousel" class="carousel slide" data-bs-ride="carousel"
                                        data-bs-interval="40000">
                                        <div class="carousel-inner">
                                            <?php 
                                                $firstEvent = true;
                                                foreach ($past_events as $event): 
                                                    $media_array = explode(',', $event['media_files']);
                                                    $images = [];
                                                    foreach ($media_array as $media) {
                                                        $parts = explode(':', $media, 2);
                                                        $path = $parts[0] ?? '';
                                                        $type = $parts[1] ?? '';
                                                        if ($type === 'image' && $path) {
                                                            $images[] = $path;
                                                        }
                                                    }
                                                ?>
                                            <div class="carousel-item <?= $firstEvent ? 'active' : '' ?>">
                                                <div class="row g-0">
                                                    <div class="col-md-6">
                                                        <div class="uc-event-text-wrap">
                                                            <h5 class="uc-event-title">EVENTS</h5>
                                                            <h5 class="uc-event-sub-title">
                                                                <?= htmlspecialchars($event['title']) ?></h5>
                                                            <p class="uc-event-date">
                                                                <?= date('d M Y', strtotime($event['event_date'])) ?>
                                                            </p>
                                                            <p class="uc-event-location">
                                                                <?= htmlspecialchars($event['location']) ?></p>
                                                            
                                                            <button class="more" data-bs-toggle="modal" data-bs-target="#eventModal<?= $event['id'] ?>">More Details</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 text-center">
                                                        <?php if (!empty($images)): ?>
                                                            <div id="eventImagesCarousel<?= $event['id'] ?>" class="event-images-carousel">
                                                                <?php foreach ($images as $index => $image): ?>
                                                                    <div class="event-image-item" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
                                                                        <img src="<?= $baseUrl ?>/<?= htmlspecialchars($image) ?>"
                                                                            alt="" class="rounded shadow" 
                                                                            style="width: 100%; height: 30vh; object-fit: cover; border-radius: 20px;">
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $firstEvent = false;
                                            endforeach; ?>
                                        </div>
                                        <?php if (count($past_events) > 1): ?>
                                        <!-- <button class="carousel-control-prev" type="button"
                                            data-bs-target="#mainEventCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button> -->
                                        <!-- <button class="carousel-control-next" type="button"
                                            data-bs-target="#mainEventCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button> -->
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </section>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="upcomming-event-section">
                        <div class="row g-0">
                            <div class="col-6">
                                <div class="upcomming-event-wrp">
                                    <h5 class="upcomming-event">
                                        UPCOMMING EVENTS
                                    </h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="right-wrap-ue">
                                    <h3 class="event-name">
                                        <?= $upcoming_event ? htmlspecialchars($upcoming_event['title']) : 'No Upcoming Event' ?>
                                    </h3>
                                    <p class="date">
                                        <?= $upcoming_event ? date('d-M-Y', strtotime($upcoming_event['event_date'])) : '' ?>
                                    </p>
                                </div>
                            </div>
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


    <!-- Remove any previous slideshow script for this gallery -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const gallery = document.getElementById('newsGallery<?= $news['id'] ?>');
        if (!gallery) return;
        const items = gallery.querySelectorAll('.gallery-media');
        if (items.length < 2) return;

        let current = 0;
        const maxLoops = items.length - 1;

        function loop(nextCount = 0) {
            if (nextCount > maxLoops) return;
            items[current].style.display = 'none';
            current = (current + 1) % items.length;
            items[current].style.display = 'block';

            if (nextCount < maxLoops) {
                setTimeout(() => loop(nextCount + 1), 3000);
            }
        }

        setTimeout(() => loop(1), 3000);
    });
    </script>

    <script src="assets/js/cpd-session.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Partner carousel (slide partners every 40s)
        const partnersCarousel = document.getElementById('partnersCarousel');
        if (partnersCarousel) {
            const slides = partnersCarousel.querySelectorAll('.partner-slide');
            let currentPartnerIndex = 0;
            function showPartnerSlide(index) {
                slides.forEach((slide, i) => {
                    slide.style.display = (i === index) ? 'block' : 'none';
                });
            }
            function nextPartner() {
                currentPartnerIndex = (currentPartnerIndex + 1) % slides.length;
                showPartnerSlide(currentPartnerIndex);
            }
            if (slides.length > 1) {
                setInterval(nextPartner, 40000); // 40 seconds
            }
        }
        // Partner image carousels (3s)
        <?php foreach ($partners as $partner): ?>
        (function() {
            const carouselId = 'partnerImagesCarousel<?= $partner['id'] ?>';
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;
            const items = carousel.querySelectorAll('.partner-image-item');
            if (items.length < 2) return;
            let current = 0;
            function showNextImage() {
                items[current].style.display = 'none';
                current = (current + 1) % items.length;
                items[current].style.display = 'block';
            }
            setInterval(showNextImage, 3000);
        })();
        <?php endforeach; ?>
        // Event image carousels (4s)
        <?php foreach ($past_events as $event): ?>
        (function() {
            const carouselId = 'eventImagesCarousel<?= $event['id'] ?>';
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;
            const items = carousel.querySelectorAll('.event-image-item');
            if (items.length < 2) return;
            let current = 0;
            function showNextImage() {
                items[current].style.display = 'none';
                current = (current + 1) % items.length;
                items[current].style.display = 'block';
            }
            setInterval(showNextImage, 4000);
        })();
        <?php endforeach; ?>
    });
    </script>

    <!-- Event Modals -->
    <?php foreach ($past_events as $event): ?>
    <div class="modal fade" id="eventModal<?= $event['id'] ?>" tabindex="-1" aria-labelledby="eventModalLabel<?= $event['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="event-details">
                                <p><strong>Date:</strong> <?= date('d M Y', strtotime($event['event_date'])) ?></p>
                                <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                                <?php if (!empty($event['description'])): ?>
                                    <p><strong>Description:</strong></p>
                                    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($images)): ?>
                        <div class="col-md-12">
                            <div class="event-gallery">
                                <h6>Event Gallery</h6>
                                <div class="row g-3">
                                    <?php foreach ($images as $image): ?>
                                    <div class="col-md-4">
                                        <img src="<?= $baseUrl ?>/<?= htmlspecialchars($image) ?>"
                                            alt="" class="img-fluid rounded shadow event-gallery-img"
                                            style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                            data-bs-toggle="modal" data-bs-target="#fullscreenImageModal"
                                            data-img-src="<?= $baseUrl ?>/<?= htmlspecialchars($image) ?>">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Fullscreen Image Modal -->
    <div class="modal fade" id="fullscreenImageModal" tabindex="-1" aria-labelledby="fullscreenImageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body p-0 text-center position-relative">
            <img id="fullscreenModalImg" src="" alt="Full Image" style="max-width: 100%; max-height: 90vh; border-radius: 10px; box-shadow: 0 0 20px #000; background: #fff;" />
            <!-- Image Close Button -->
            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 fullscreen-img-close-btn" style="z-index:13; border-radius:50%; width:2.2rem; height:2.2rem; font-size:1.3rem; display:flex; align-items:center; justify-content:center; opacity:0.85;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            <!-- Next Button -->
            <button id="fullscreenNextBtn" type="button" class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-3" style="z-index:11; font-size:2rem; opacity:0.8;">
              &rarr;
            </button>
          </div>
          <!-- Close Button -->
          <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index:12;"></button>
        </div>
      </div>
    </div>

    <script>
    (function() {
      let currentGallery = [];
      let currentIndex = 0;

      // Helper to update modal image
      function showImage(idx) {
        const modalImg = document.getElementById('fullscreenModalImg');
        if (modalImg && currentGallery.length > 0) {
          modalImg.src = currentGallery[idx];
        }
      }

      document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('event-gallery-img')) {
          // Find all images in the same gallery row
          const galleryRow = e.target.closest('.event-gallery');
          let images;
          if (galleryRow) {
            images = Array.from(galleryRow.querySelectorAll('.event-gallery-img'));
          } else {
            // fallback: all images with this class
            images = Array.from(document.querySelectorAll('.event-gallery-img'));
          }
          currentGallery = images.map(img => img.getAttribute('data-img-src'));
          currentIndex = images.indexOf(e.target);
          showImage(currentIndex);
        }
      });

      // Next button handler
      document.getElementById('fullscreenNextBtn').addEventListener('click', function(e) {
        if (currentGallery.length > 0) {
          currentIndex = (currentIndex + 1) % currentGallery.length;
          showImage(currentIndex);
        }
      });

      // Optional: Reset modal on close
      document.getElementById('fullscreenImageModal').addEventListener('hidden.bs.modal', function() {
        currentGallery = [];
        currentIndex = 0;
        document.getElementById('fullscreenModalImg').src = '';
      });
    })();
    </script>

</body>

</html>