<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_news.php');
    exit;
}
$news_id = (int)$_GET['id'];

// Fetch news to get image and video filename
$stmt = $pdo->prepare("SELECT image, video FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$news = $stmt->fetch();

if ($news) {
    // Delete image file if exists
    if ($news['image'] && file_exists(__DIR__ . '/../uploads/news/' . $news['image'])) {
        unlink(__DIR__ . '/../uploads/news/' . $news['image']);
    }
    // Delete video file if exists
    if ($news['video'] && file_exists(__DIR__ . '/../uploads/news/' . $news['video'])) {
        unlink(__DIR__ . '/../uploads/news/' . $news['video']);
    }
    // Delete news record
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$news_id]);
}

header('Location: manage_news.php');
exit; 