<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Invalid member ID.</div></div>';
    exit;
}
$member_id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT m.*, u.email as user_email, u.status as user_status FROM members m JOIN users u ON m.user_id = u.id WHERE m.id = ?');
$stmt->execute([$member_id]);
$member = $stmt->fetch();

if (!$member) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Member not found.</div></div>';
    exit;
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Member Details</h4>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Profile Photo</dt>
                        <dd class="col-sm-8">
                            <?php if (!empty($member['profile_image']) && file_exists(__DIR__ . '/../uploads/members/' . $member['profile_image'])): ?>
                                <img src="../uploads/members/<?= htmlspecialchars($member['profile_image']) ?>" alt="Profile Photo" style="max-width:80px;max-height:80px;border-radius:8px;">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['full_name']) ?></dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['user_email']) ?></dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['user_status']) ?></dd>
                        <dt class="col-sm-4">Country</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['country']) ?></dd>
                        <dt class="col-sm-4">Position</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['position']) ?></dd>
                        <dt class="col-sm-4">Membership Type</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['membership_type']) ?></dd>
                        <dt class="col-sm-4">Membership Status</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['membership_status']) ?></dd>
                        <dt class="col-sm-4">Membership Start</dt>
                        <dd class="col-sm-8">
                            <?php if ($member['payment_status'] === 'paid' && $member['payment_date']): ?>
                                <?= htmlspecialchars($member['payment_date']) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Membership End</dt>
                        <dd class="col-sm-8">
                            <?php if ($member['payment_status'] === 'paid' && $member['payment_date']): ?>
                                <?= htmlspecialchars(date('Y-m-d', strtotime($member['payment_date'] . ' +1 year'))) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-4">Payment Status</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['payment_status'] ?? '-') ?: '-' ?></dd>
                        <dt class="col-sm-4">Payment Date</dt>
                        <dd class="col-sm-8"><?= isset($member['payment_date']) && $member['payment_date'] ? htmlspecialchars($member['payment_date']) : '-' ?></dd>
                        <dt class="col-sm-4">LinkedIn</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['linkedin'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Visa Status</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['visa_status'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['phone'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Passport Number</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['passport_number'] ?? '-') ?></dd>
                        <dt class="col-sm-4">Member Category</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($member['member_category'] ?? '-') ?></dd>
                    </dl>
                </div>
                <div class="card-footer text-end">
                    <a href="manage_members.php" class="btn btn-secondary">Back to Members</a>
                    <form method="post" action="delete_member.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this member? This cannot be undone.');">
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete Member</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 