<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $member_id = (int)$_POST['id'];
    // Get user_id for this member
    $stmt = $pdo->prepare('SELECT user_id FROM members WHERE id = ?');
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    if ($member) {
        $user_id = $member['user_id'];
        // Delete member and user in a transaction
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM members WHERE id = ?')->execute([$member_id]);
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$user_id]);
            $pdo->commit();
            header('Location: manage_members.php?msg=Member+deleted+successfully');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: manage_members.php?msg=Error+deleting+member');
            exit;
        }
    } else {
        header('Location: manage_members.php?msg=Member+not+found');
        exit;
    }
}
header('Location: manage_members.php?msg=Invalid+request');
exit; 