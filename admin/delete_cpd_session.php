<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    // Get photo filename
    $stmt = $pdo->prepare("SELECT photo FROM cpd_sessions WHERE id = ?");
    $stmt->execute([$id]);
    $session = $stmt->fetch();
    if ($session) {
        $photo = $session['photo'];
        // Delete DB row
        $pdo->prepare("DELETE FROM cpd_sessions WHERE id = ?")->execute([$id]);
        // Delete photo file
        $photo_path = __DIR__ . '/../uploads/cpd_sessions/' . $photo;
        if (is_file($photo_path)) {
            unlink($photo_path);
        }
        header('Location: cpd_sessions.php?msg=Session+deleted');
        exit;
    } else {
        header('Location: cpd_sessions.php?msg=Session+not+found');
        exit;
    }
}
header('Location: cpd_sessions.php?msg=Invalid+request');
exit; 