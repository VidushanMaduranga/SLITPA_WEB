<?php
require_once 'config/config.php';
header('Content-Type: text/html; charset=UTF-8');

$partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : 0;
if (!$partner_id) {
    echo '<div class="text-danger">Invalid partner ID.</div>';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM partner_posts WHERE partner_id = ? ORDER BY created_at DESC');
$stmt->execute([$partner_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$posts) {
    echo '<div class="text-center text-muted">No posts found for this partner.</div>';
    exit;
}

$isAdmin = isset($_GET['admin']) && $_GET['admin'] == 1;
?>
<div id="partnerPostsCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <?php foreach ($posts as $i => $post):
      $mediaFiles = $post['media'] ? explode(',', $post['media']) : [];
      ?>
      <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-2"><?= htmlspecialchars($post['title']) ?></h5>
            <p class="card-text mb-3"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
              <?php foreach ($mediaFiles as $file):
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mediaUrl = '/slitpa-web/uploads/posts/' . htmlspecialchars($file);
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                  <a href="<?= $mediaUrl ?>" class="glightbox" data-gallery="partner-posts">
                    <img src="<?= $mediaUrl ?>" class="rounded border" style="max-width:220px;max-height:180px;object-fit:cover;" alt="Post Media">
                  </a>
                <?php elseif (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                  <a href="<?= $mediaUrl ?>" class="glightbox" data-gallery="partner-posts" data-type="video">
                    <video src="<?= $mediaUrl ?>" style="max-width:220px;max-height:180px;" class="rounded border"></video>
                  </a>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (count($posts) > 1): ?>
    <button class="carousel-control-prev" type="button" data-bs-target="#partnerPostsCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#partnerPostsCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  <?php endif; ?>
</div>

<div class="partner-modal-post-card">
    <div class="partner-modal-post-title"><?= htmlspecialchars($post['title']) ?></div>
    <div class="partner-modal-post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
    <?php if ($mediaFiles): ?>
    <div id="postMediaCarousel<?= $post['id'] ?>" class="carousel slide partner-modal-post-media" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($mediaFiles as $idx => $file):
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mediaUrl = '/slitpa-web/uploads/posts/' . htmlspecialchars($file);
            ?>
            <div class="carousel-item<?= $idx === 0 ? ' active' : '' ?>">
                <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                    <a href="<?= $mediaUrl ?>" class="glightbox" data-gallery="post-<?= $post['id'] ?>">
                        <img src="<?= $mediaUrl ?>" alt="Post Media">
                    </a>
                <?php elseif (in_array($ext, ['mp4', 'mov', 'avi'])): ?>
                    <a href="<?= $mediaUrl ?>" class="glightbox" data-gallery="post-<?= $post['id'] ?>" data-type="video">
                        <video src="<?= $mediaUrl ?>" controls></video>
                    </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($mediaFiles) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#postMediaCarousel<?= $post['id'] ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#postMediaCarousel<?= $post['id'] ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ($isAdmin): ?>
        <form method="post" onsubmit="return confirm('Delete this post?');">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <button type="submit" name="delete_partner_post" class="btn btn-danger btn-sm partner-modal-delete-btn">Delete Post</button>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
  if (window.glightboxInstance) window.glightboxInstance.destroy();
  window.glightboxInstance = GLightbox({
    selector: '.glightbox',
    touchNavigation: true
  });
</script> 