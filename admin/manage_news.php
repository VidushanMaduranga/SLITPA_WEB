<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

// Fetch all news
$stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC, created_at DESC");
$news_list = $stmt->fetchAll();
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage News</h2>
        <a href="add_news.php" class="btn btn-success">Add News</a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Published Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news_list as $news): ?>
                        <tr>
                            <td><?= htmlspecialchars($news['title']) ?></td>
                            <td><?= htmlspecialchars($news['published_date']) ?></td>
                            <td>
                                <a href="edit_news.php?id=<?= $news['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_news.php?id=<?= $news['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this news item?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 