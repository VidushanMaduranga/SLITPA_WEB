<?php
/**
 * Event Form Handler
 * This file provides the interface for creating and editing events with media uploads
 */

// Include required configuration and handlers
require_once '../config/config.php';
require_once '../includes/event_media_handler.php';
include __DIR__ . '/../includes/admin_header.php';

// Initialize variables
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$event = null;
$media = [];

// If editing an existing event, fetch its data
if ($event_id) {
    // Get event details
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    // Get associated media files
    $stmt = $pdo->prepare("SELECT * FROM event_media WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $media = $stmt->fetchAll();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction for data consistency
        $pdo->beginTransaction();

        // Get form data
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $location = $_POST['location'];

        if ($event_id) {
            // Update existing event
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE id = ?");
            $stmt->execute([$title, $description, $event_date, $location, $event_id]);
        } else {
            // Create new event
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $event_date, $location]);
            $event_id = $pdo->lastInsertId();
        }

        // Process media uploads if any files were submitted
        if (!empty($_FILES)) {
            $upload_result = handleMediaUpload($event_id, $_FILES);
            if (!$upload_result['success']) {
                throw new Exception(implode(", ", $upload_result['errors']));
            }
        }

        // Commit transaction and redirect
        $pdo->commit();
        header("Location: events.php");
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_id ? 'Edit' : 'Create' ?> Event - SLITPA Admin</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include admin header -->
    <?php include '../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <h2><?= $event_id ? 'Edit' : 'Create' ?> Event</h2>
        
        <!-- Display error messages if any -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Event form with file upload capability -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Event Title -->
            <div class="mb-3">
                <label for="title" class="form-label">Event Title</label>
                <input type="text" class="form-control" id="title" name="title" required 
                       value="<?= $event ? htmlspecialchars($event['title']) : '' ?>">
            </div>

            <!-- Event Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?= $event ? htmlspecialchars($event['description']) : '' ?></textarea>
            </div>

            <!-- Event Date -->
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" required
                       value="<?= $event ? $event['event_date'] : '' ?>">
            </div>

            <!-- Event Location -->
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" required
                       value="<?= $event ? htmlspecialchars($event['location']) : '' ?>">
            </div>

            <!-- Image Upload Field -->
            <div class="mb-3">
                <label for="image" class="form-label">Upload Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted">Supported formats: JPG, PNG, GIF (Max: 50MB)</small>
            </div>

            <!-- Video Upload Field -->
            <div class="mb-3">
                <label for="video" class="form-label">Upload Video</label>
                <input type="file" class="form-control" id="video" name="video" accept="video/*">
                <small class="text-muted">Supported formats: MP4, WebM, OGG (Max: 50MB)</small>
            </div>

            <!-- Display existing media if editing -->
            <?php if (!empty($media)): ?>
            <div class="mb-3">
                <h4>Current Media</h4>
                <div class="row">
                    <?php foreach ($media as $item): ?>
                        <div class="col-md-4 mb-3">
                            <?php if ($item['media_type'] === 'image'): ?>
                                <!-- Display image -->
                                <img src="../<?= htmlspecialchars($item['file_path']) ?>" class="img-fluid" alt="Event Image">
                            <?php else: ?>
                                <!-- Display video -->
                                <video class="img-fluid" controls>
                                    <source src="../<?= htmlspecialchars($item['file_path']) ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Form Buttons -->
            <button type="submit" class="btn btn-primary">Save Event</button>
            <a href="events.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 