<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

// Handle approve partner (pending -> unpaid)
if (isset($_POST['approve_partner'])) {
    $partner_id = (int)$_POST['partner_id'];
    $stmt = $pdo->prepare("UPDATE partners SET status = 'unpaid' WHERE id = ?");
    $stmt->execute([$partner_id]);
    header('Location: manage_partners.php?type=unpaid');
    exit;
}
// Handle confirm payment (unpaid -> paid)
if (isset($_POST['confirm_payment'])) {
    $partner_id = (int)$_POST['partner_id'];
    $stmt = $pdo->prepare("UPDATE partners SET status = 'paid', payment_status = 'paid' WHERE id = ?");
    $stmt->execute([$partner_id]);
    header('Location: manage_partners.php?type=paid');
    exit;
}
// Add reject handler in PHP
if (isset($_POST['reject_partner'])) {
    $partner_id = (int)$_POST['partner_id'];
    $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
    $stmt->execute([$partner_id]);
    header('Location: manage_partners.php?type=pending');
    exit;
}
// Handle admin post deletion
if (isset($_POST['delete_partner_post'])) {
    $post_id = (int)$_POST['post_id'];
    // Get media files for this post
    $stmt = $pdo->prepare("SELECT media FROM partner_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $mediaStr = $stmt->fetchColumn();
    if ($mediaStr) {
        $mediaFiles = explode(',', $mediaStr);
        foreach ($mediaFiles as $file) {
            $file = trim($file);
            if ($file && file_exists(__DIR__ . '/../uploads/posts/' . $file)) {
                unlink(__DIR__ . '/../uploads/posts/' . $file);
            }
        }
    }
    // Delete the post
    $stmt = $pdo->prepare("DELETE FROM partner_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$type = $_GET['type'] ?? 'pending';

// Fetch partners by status
$pending_partners = $pdo->query("SELECT * FROM partners WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$unpaid_partners = $pdo->query("SELECT * FROM partners WHERE status = 'unpaid' ORDER BY created_at DESC")->fetchAll();
$paid_partners = $pdo->query("SELECT * FROM partners WHERE status = 'paid' ORDER BY created_at DESC")->fetchAll();
?>
<head>
    <link rel="stylesheet" href="../assets/css/partner-admin-modal.css">
</head>
<style>
.partner-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(44,62,80,0.10);
    padding: 1.5rem 1.2rem;
    margin-bottom: 2rem;
    transition: box-shadow 0.2s;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1.5rem;
}
.partner-card:hover {
    box-shadow: 0 8px 32px rgba(44,62,80,0.18);
}
.partner-logo {
    max-width: 64px;
    max-height: 64px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(44,62,80,0.10);
    background: #f8fafc;
    object-fit: cover;
}
.partner-info {
    flex: 1 1 0%;
    min-width: 0;
}
.partner-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
    color: #222;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.partner-meta {
    font-size: 0.98rem;
    color: #555;
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.badge-category {
    font-size: 0.95em;
    padding: 0.4em 0.8em;
    border-radius: 8px;
    color: #fff;
    margin-right: 0.5em;
}
.badge-category.silver { background: #adb5bd; }
.badge-category.gold { background: #ffd700; color: #333; }
.badge-category.platinum { background: #6c757d; }
.partner-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}
@media (max-width: 900px) {
    .partner-card { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .partner-actions { width: 100%; flex-direction: row; justify-content: flex-end; }
}
</style>
<div class="container py-5">
    <h2>Manage Partners</h2>
    <div class="mb-1" style="background-color: #0261e08c; border-radius: 10px;">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link<?= $type === 'pending' ? ' active' : '' ?>" href="manage_partners.php?type=pending">Pending Partners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $type === 'unpaid' ? ' active' : '' ?>" href="manage_partners.php?type=unpaid">Unpaid Partners</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $type === 'paid' ? ' active' : '' ?>" href="manage_partners.php?type=paid">Paid Partners</a>
            </li>
        </ul>
    </div>
    <?php if ($type === 'pending'): ?>
        <h3 class="mb-3">Pending Partners</h3>
        <div class="row">
        <?php foreach ($pending_partners as $partner): ?>
            <div class="col-lg-6 col-12">
                <div class="partner-card">
                    <?php if ($partner['logo']): ?>
                        <img src="../uploads/partners/<?= htmlspecialchars($partner['logo']) ?>" alt="Logo" class="partner-logo"/>
                    <?php endif; ?>
                    <div class="partner-info">
                        <div class="partner-title" title="<?= htmlspecialchars($partner['name']) ?>">
                            <?= htmlspecialchars($partner['name']) ?>
                        </div>
                        <div class="partner-meta" title="<?= htmlspecialchars($partner['company_name']) ?>">
                            <?= htmlspecialchars($partner['company_name']) ?>
                        </div>
                        <div class="partner-meta">
                            <span class="badge badge-category <?= htmlspecialchars(strtolower($partner['partner_category'])) ?>">
                                <?= htmlspecialchars(ucfirst($partner['partner_category'])) ?>
                            </span>
                            <span title="<?= htmlspecialchars($partner['partner_email']) ?>">
                                <?= htmlspecialchars($partner['partner_email']) ?>
                            </span>
                        </div>
                        <div class="partner-meta">
                            <span title="<?= htmlspecialchars($partner['company_email']) ?>">
                                <?= htmlspecialchars($partner['company_email']) ?>
                            </span>
                        </div>
                        <div class="partner-meta">
                            <span>Partner #: <?= htmlspecialchars($partner['partner_number']) ?></span> |
                            <span>Company #: <?= htmlspecialchars($partner['company_number']) ?></span>
                        </div>
                        <div class="partner-meta" style="white-space:normal;max-width:350px;">
                            <span><?= htmlspecialchars($partner['description']) ?></span>
                        </div>
                    </div>
                    <div class="partner-actions">
                        <form method="POST">
                            <input type="hidden" name="partner_id" value="<?= $partner['id'] ?>">
                            <button type="submit" name="approve_partner" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick='showPartnerModal({
                        id: "<?= $partner['id'] ?>",
                        logo: "<?= addslashes($partner['logo']) ?>",
                        name: "<?= addslashes($partner['name']) ?>",
                        company_name: "<?= addslashes($partner['company_name']) ?>",
                        partner_category: "<?= addslashes(ucfirst($partner['partner_category'])) ?>",
                        partner_email: "<?= addslashes($partner['partner_email']) ?>",
                        company_email: "<?= addslashes($partner['company_email']) ?>",
                        partner_number: "<?= addslashes($partner['partner_number']) ?>",
                        company_number: "<?= addslashes($partner['company_number']) ?>",
                        description: "<?= addslashes($partner['description']) ?>"
                    })'>View</button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php elseif ($type === 'unpaid'): ?>
        <h3 class="mb-3">Unpaid Partners</h3>
        <div class="row">
        <?php foreach ($unpaid_partners as $partner): ?>
            <div class="col-lg-6 col-12">
                <div class="partner-card">
                    <?php if ($partner['logo']): ?>
                        <img src="../uploads/partners/<?= htmlspecialchars($partner['logo']) ?>" alt="Logo" class="partner-logo"/>
                    <?php endif; ?>
                    <div class="partner-info">
                        <div class="partner-title" title="<?= htmlspecialchars($partner['name']) ?>">
                            <?= htmlspecialchars($partner['name']) ?>
                        </div>
                        <div class="partner-meta" title="<?= htmlspecialchars($partner['company_name']) ?>">
                            <?= htmlspecialchars($partner['company_name']) ?>
                        </div>
                        <div class="partner-meta">
                            <span class="badge badge-category <?= htmlspecialchars(strtolower($partner['partner_category'])) ?>">
                                <?= htmlspecialchars(ucfirst($partner['partner_category'])) ?>
                            </span>
                            <span title="<?= htmlspecialchars($partner['partner_email']) ?>">
                                <?= htmlspecialchars($partner['partner_email']) ?>
                            </span>
                        </div>
                        <div class="partner-meta">
                            <span title="<?= htmlspecialchars($partner['company_email']) ?>">
                                <?= htmlspecialchars($partner['company_email']) ?>
                            </span>
                        </div>
                        <div class="partner-meta">
                            <span>Partner #: <?= htmlspecialchars($partner['partner_number']) ?></span> |
                            <span>Company #: <?= htmlspecialchars($partner['company_number']) ?></span>
                        </div>
                        <div class="partner-meta" style="white-space:normal;max-width:350px;">
                            <span><?= htmlspecialchars($partner['description']) ?></span>
                        </div>
                    </div>
                    <div class="partner-actions">
                        <form method="POST">
                            <input type="hidden" name="partner_id" value="<?= $partner['id'] ?>">
                            <button type="submit" name="confirm_payment" class="btn btn-primary btn-sm">Confirm Payment</button>
                        </form>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick='showPartnerModal({
                        id: "<?= $partner['id'] ?>",
                        logo: "<?= addslashes($partner['logo']) ?>",
                        name: "<?= addslashes($partner['name']) ?>",
                        company_name: "<?= addslashes($partner['company_name']) ?>",
                        partner_category: "<?= addslashes(ucfirst($partner['partner_category'])) ?>",
                        partner_email: "<?= addslashes($partner['partner_email']) ?>",
                        company_email: "<?= addslashes($partner['company_email']) ?>",
                        partner_number: "<?= addslashes($partner['partner_number']) ?>",
                        company_number: "<?= addslashes($partner['company_number']) ?>",
                        description: "<?= addslashes($partner['description']) ?>"
                    })'>View</button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php elseif ($type === 'paid'): ?>
        <h3 class="mb-3">Paid Partners</h3>
        <div class="row">
        <?php foreach ($paid_partners as $partner): ?>
            <div class="col-lg-6 col-12">
                <div class="partner-card">
                    <?php if ($partner['logo']): ?>
                        <img src="../uploads/partners/<?= htmlspecialchars($partner['logo']) ?>" alt="Logo" class="partner-logo"/>
                    <?php endif; ?>
                    <div class="partner-info">
                        <div class="partner-title" title="<?= htmlspecialchars($partner['name']) ?>">
                            <?= htmlspecialchars($partner['name']) ?>
                        </div>
                        <div class="partner-meta" title="<?= htmlspecialchars($partner['company_name']) ?>">
                            <?= htmlspecialchars($partner['company_name']) ?>
                        </div>
                        <div class="partner-meta">
                            <span class="badge badge-category <?= htmlspecialchars(strtolower($partner['partner_category'])) ?>">
                                <?= htmlspecialchars(ucfirst($partner['partner_category'])) ?>
                            </span>
                            <span title="<?= htmlspecialchars($partner['partner_email']) ?>">
                                <?= htmlspecialchars($partner['partner_email']) ?>
                            </span>
                        </div>
                        <div class="partner-meta">
                            <span title="<?= htmlspecialchars($partner['company_email']) ?>">
                                <?= htmlspecialchars($partner['company_email']) ?>
                            </span>
                        </div>
                        <div class="partner-meta">
                            <span>Partner #: <?= htmlspecialchars($partner['partner_number']) ?></span> |
                            <span>Company #: <?= htmlspecialchars($partner['company_number']) ?></span>
                        </div>
                        <div class="partner-meta" style="white-space:normal;max-width:350px;">
                            <span><?= htmlspecialchars($partner['description']) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick='showPartnerModal({
                        id: "<?= $partner['id'] ?>",
                        logo: "<?= addslashes($partner['logo']) ?>",
                        name: "<?= addslashes($partner['name']) ?>",
                        company_name: "<?= addslashes($partner['company_name']) ?>",
                        partner_category: "<?= addslashes(ucfirst($partner['partner_category'])) ?>",
                        partner_email: "<?= addslashes($partner['partner_email']) ?>",
                        company_email: "<?= addslashes($partner['company_email']) ?>",
                        partner_number: "<?= addslashes($partner['partner_number']) ?>",
                        company_number: "<?= addslashes($partner['company_number']) ?>",
                        description: "<?= addslashes($partner['description']) ?>"
                    })'>View</button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<!-- Add modal HTML at the end of the file -->
<div class="modal fade" id="partnerModal" tabindex="-1" aria-labelledby="partnerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="partnerModalLabel">Partner Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center" id="partnerModalBody">
        <!-- Content will be loaded by JS -->
      </div>
      <div class="modal-footer justify-content-center" id="partnerModalFooter">
        <!-- Buttons will be loaded by JS -->
      </div>
    </div>
  </div>
</div>
<script>
function showPartnerModal(partner) {
  let html = '';
  if (partner.logo) {
    html += `<img src="../uploads/partners/${partner.logo}" alt="Logo" class="partner-logo mb-3" style="max-width:80px;max-height:80px;">`;
  }
  html += `<div class='mb-2'><strong>Name:</strong> ${partner.name}</div>`;
  html += `<div class='mb-2'><strong>Company:</strong> ${partner.company_name}</div>`;
  html += `<div class='mb-2'><strong>Category:</strong> <span class='badge badge-category ${partner.partner_category.toLowerCase()}'>${partner.partner_category.charAt(0).toUpperCase() + partner.partner_category.slice(1)}</span></div>`;
  html += `<div class='mb-2'><strong>Email:</strong> ${partner.partner_email}</div>`;
  html += `<div class='mb-2'><strong>Company Email:</strong> ${partner.company_email}</div>`;
  html += `<div class='mb-2'><strong>Partner #:</strong> ${partner.partner_number}</div>`;
  html += `<div class='mb-2'><strong>Company #:</strong> ${partner.company_number}</div>`;
  html += `<div class='mb-2'><strong>Description:</strong> ${partner.description}</div>`;
  html += `<hr><h5>Posts</h5><div id='partnerPostsArea'>Loading posts...</div>`;
  document.getElementById('partnerModalBody').innerHTML = html;
  document.getElementById('partnerModalFooter').innerHTML = `
    <form method='POST' class='d-inline ms-2'>
      <input type='hidden' name='partner_id' value='${partner.id}'>
      <button type='submit' name='reject_partner' class='btn btn-danger'>Reject</button>
    </form>
  `;
  var modal = new bootstrap.Modal(document.getElementById('partnerModal'));
  modal.show();
  // Load posts via AJAX
  fetch('../get_partner_posts.php?partner_id=' + encodeURIComponent(partner.id) + '&admin=1')
    .then(response => response.text())
    .then(html => {
      document.getElementById('partnerPostsArea').innerHTML = html;
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 