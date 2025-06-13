<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';

// Fetch all news
$stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC, created_at DESC");
$news_list = $stmt->fetchAll();

// Fetch all media for all news posts
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>News - SLITPA</title>
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
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
                <h1 class="hero-title">News</h1>
            </div>
        </div>
    </section>
<div class="container py-5">
    <h1 class="mb-4">Latest News</h1>
    <?php if (empty($news_list)): ?>
        <div class="alert alert-info">No news available at this time.</div>
    <?php endif; ?>
    <?php foreach ($news_list as $news): ?>
        <div class="row align-items-center mb-4 shadow-sm bg-white rounded p-3 flex-nowrap" style="min-height:140px;">
            <div class="col-auto d-flex align-items-center justify-content-center" style="min-width:120px;">
                <?php if (isset($news['image']) && $news['image']): ?>
                    <img src="uploads/news/<?= htmlspecialchars($news['image']) ?>" class="rounded-circle" alt="News Image" style="width:100px;height:100px;object-fit:cover;">
                <?php elseif (isset($news['video']) && $news['video']): ?>
                    <video src="uploads/news/<?= htmlspecialchars($news['video']) ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover;" controls></video>
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:100px;height:100px;"><span class="text-white">No Image</span></div>
                <?php endif; ?>
                </div>
            <div class="col ps-4">
                <h4 class="mb-1"><?= htmlspecialchars($news['title']) ?></h4>
                <div class="mb-1 text-muted" style="font-size:0.95em;">
                    <?= date('F j, Y', strtotime($news['published_date'])) ?>
                </div>
                <div class="mb-2">
                    <?= htmlspecialchars(mb_strimwidth(strip_tags($news['content']), 0, 220, '...')) ?>
                </div>
            </div>
            <div class="col-auto d-flex align-items-center justify-content-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newsModal<?= $news['id'] ?>">
                    View More
                </button>
            </div>
        </div>
        <!-- Modal for full news details -->
        <div class="modal fade" id="newsModal<?= $news['id'] ?>" tabindex="-1" aria-labelledby="newsModalLabel<?= $news['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newsModalLabel<?= $news['id'] ?>"><?= htmlspecialchars($news['title']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2 text-muted">Published: <?= date('F j, Y', strtotime($news['published_date'])) ?></div>
                        <div class="row">
                        <?php if (!empty($media_by_news[$news['id']])): ?>
                            <?php foreach ($media_by_news[$news['id']] as $media): ?>
                                <div class="col-md-4 mb-3">
                                    <?php if ($media['media_type'] === 'image'): ?>
                                        <img src="<?= htmlspecialchars($media['file_path']) ?>" class="img-fluid rounded" alt="News Image">
                                    <?php elseif ($media['media_type'] === 'video'): ?>
                                        <video src="<?= htmlspecialchars($media['file_path']) ?>" class="img-fluid rounded" controls></video>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </div>
                        <div><?= nl2br(htmlspecialchars($news['content'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
