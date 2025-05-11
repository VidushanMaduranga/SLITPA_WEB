<?php
require_once '../config/config.php';
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        // Add new event
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $location = htmlspecialchars($_POST['location']);
        $event_date = $_POST['event_date'];
        $end_date = $_POST['end_date'] ?? $event_date;

        try {
            $stmt = $pdo->prepare("INSERT INTO events (title, description, location, event_date, end_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $location, $event_date, $end_date]);
            $event_id = $pdo->lastInsertId();

            // Handle file uploads
            if (!empty($_FILES['media']['name'][0])) {
                $uploadDir = '../assets/uploads/events/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                foreach ($_FILES['media']['name'] as $key => $name) {
                    $tmpName = $_FILES['media']['tmp_name'][$key];
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'];
                    
                    if (in_array($ext, $allowedExts)) {
                        $newName = uniqid() . '.' . $ext;
                        $filePath = $uploadDir . $newName;

                        if (move_uploaded_file($tmpName, $filePath)) {
                            $media_type = (strpos($_FILES['media']['type'][$key], 'image') !== false) ? 'image' : 'video';
                            $is_featured = ($key === 0) ? 1 : 0;

                            $stmt = $pdo->prepare("INSERT INTO event_media (event_id, file_path, media_type, is_featured) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$event_id, $filePath, $media_type, $is_featured]);
                        }
                    }
                }
            }

            $_SESSION['message'] = 'Event added successfully!';
            header('Location: manage_events.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error adding event: ' . $e->getMessage();
        }
    } 
    elseif (isset($_POST['update_event'])) {
        // Update existing event
        $event_id = $_POST['event_id'];
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $location = htmlspecialchars($_POST['location']);
        $event_date = $_POST['event_date'];
        $end_date = $_POST['end_date'] ?? $event_date;

        try {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, end_date = ? WHERE id = ?");
            $stmt->execute([$title, $description, $location, $event_date, $end_date, $event_id]);

            // Handle new file uploads
            if (!empty($_FILES['media']['name'][0])) {
                $uploadDir = '../assets/uploads/events/';
                
                foreach ($_FILES['media']['name'] as $key => $name) {
                    $tmpName = $_FILES['media']['tmp_name'][$key];
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov'];
                    
                    if (in_array($ext, $allowedExts)) {
                        $newName = uniqid() . '.' . $ext;
                        $filePath = $uploadDir . $newName;

                        if (move_uploaded_file($tmpName, $filePath)) {
                            $media_type = (strpos($_FILES['media']['type'][$key], 'image') !== false) ? 'image' : 'video';
                            $is_featured = 0; // Don't override existing featured image

                            $stmt = $pdo->prepare("INSERT INTO event_media (event_id, file_path, media_type, is_featured) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$event_id, $filePath, $media_type, $is_featured]);
                        }
                    }
                }
            }

            $_SESSION['message'] = 'Event updated successfully!';
            header('Location: manage_events.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error updating event: ' . $e->getMessage();
        }
    } 
    elseif (isset($_POST['delete_event'])) {
        // Delete event
        $event_id = $_POST['event_id'];
        
        try {
            // First delete media files
            $stmt = $pdo->prepare("SELECT file_path FROM event_media WHERE event_id = ?");
            $stmt->execute([$event_id]);
            $mediaFiles = $stmt->fetchAll();
            
            foreach ($mediaFiles as $file) {
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
            }
            
            // Then delete the event
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            
            $_SESSION['message'] = 'Event deleted successfully!';
            header('Location: manage_events.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error deleting event: ' . $e->getMessage();
        }
    }
}

// Get all events
$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();

// Get event details for editing
$edit_event = null;
$event_media = [];
if (isset($_GET['edit'])) {
    $event_id = $_GET['edit'];
    $edit_event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $edit_event->execute([$event_id]);
    $edit_event = $edit_event->fetch();
    
    $event_media = $pdo->prepare("SELECT * FROM event_media WHERE event_id = ? ORDER BY is_featured DESC");
    $event_media->execute([$event_id]);
    $event_media = $event_media->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - SLITPA Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .media-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .featured-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
        }
        .media-item {
            position: relative;
            display: inline-block;
        }
        .delete-media {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(255,0,0,0.7);
            color: white;
            border: none;
            border-radius: 3px;
            padding: 1px 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Events</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <?= $edit_event ? 'Update Event' : 'Add New Event' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="event_id" value="<?= $edit_event['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                    value="<?= $edit_event ? htmlspecialchars($edit_event['title']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?= $edit_event ? htmlspecialchars($edit_event['description']) : '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                    value="<?= $edit_event ? htmlspecialchars($edit_event['location']) : '' ?>" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="event_date" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" id="event_date" name="event_date" 
                                        value="<?= $edit_event ? $edit_event['event_date'] : '' ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date (optional)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                        value="<?= $edit_event ? $edit_event['end_date'] : '' ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="media" class="form-label">Event Media (Images/Videos)</label>
                                <input type="file" class="form-control" id="media" name="media[]" multiple <?= !$edit_event ? 'required' : '' ?>>
                                <small class="text-muted">First file will be used as featured image. Allowed: JPG, PNG, GIF, MP4</small>
                            </div>
                            
                            <?php if ($edit_event): ?>
                                <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
                                <a href="manage_events.php" class="btn btn-secondary">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <?php if ($edit_event && !empty($event_media)): ?>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Event Media
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            <?php foreach ($event_media as $media): ?>
                                <div class="media-item">
                                    <?php if ($media['media_type'] === 'image'): ?>
                                        <img src="<?= $media['file_path'] ?>" class="media-thumbnail" alt="Event Media">
                                    <?php else: ?>
                                        <video class="media-thumbnail">
                                            <source src="<?= $media['file_path'] ?>" type="video/mp4">
                                        </video>
                                    <?php endif; ?>
                                    <?php if ($media['is_featured']): ?>
                                        <span class="featured-badge">Featured</span>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="media_id" value="<?= $media['id'] ?>">
                                        <input type="hidden" name="file_path" value="<?= $media['file_path'] ?>">
                                        <button type="submit" name="delete_media" class="delete-media" 
                                            onclick="return confirm('Delete this media?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        All Events
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['title']) ?></td>
                                            <td><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
                                            <td><?= htmlspecialchars($event['location']) ?></td>
                                            <td>
                                                <a href="manage_events.php?edit=<?= $event['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                    <button type="submit" name="delete_event" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this event?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('media').addEventListener('change', function(e) {
            const files = e.target.files;
            const previewContainer = document.getElementById('media-preview');
            
            if (previewContainer) {
                previewContainer.innerHTML = '';
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'media-item';
                        div.style.display = 'inline-block';
                        div.style.marginRight = '10px';
                        
                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px';
                            img.style.height = '100px';
                            img.style.objectFit = 'cover';
                            div.appendChild(img);
                        } else if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = e.target.result;
                            video.controls = true;
                            video.style.width = '100px';
                            video.style.height = '100px';
                            video.style.objectFit = 'cover';
                            div.appendChild(video);
                        }
                        
                        previewContainer.appendChild(div);
                    }
                    
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>