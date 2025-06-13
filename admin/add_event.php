<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/admin_header.php';

$error = '';
$success = '';

// Function to generate slug
function generateSlug($title) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Function to ensure unique slug
function ensureUniqueSlug($pdo, $slug, $id = null) {
    if (empty($slug)) {
        $slug = 'event-' . time();
    }
    
    $counter = 1;
    $originalSlug = $slug;
    
    do {
        if ($counter > 1) {
            $slug = $originalSlug . '-' . $counter;
        }
        
        $query = "SELECT COUNT(*) as count FROM events WHERE slug = ?";
        $params = [$slug];
        
        if ($id !== null) {
            $query .= " AND id != ?";
            $params[] = $id;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();
        $counter++;
    } while ($count > 0);
    
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['title', 'description', 'location', 'event_date'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = ucfirst(str_replace('_', ' ', $field));
            }
        }
        
        if (!empty($missing_fields)) {
            $error = "Please fill in the following fields: " . implode(", ", $missing_fields);
        } else {
            // Generate and ensure unique slug
            $slug = generateSlug($_POST['title']);
            $slug = ensureUniqueSlug($pdo, $slug);
            
            // Prepare the SQL statement
            $stmt = $pdo->prepare("INSERT INTO events (
                title, slug, description, location, 
                event_date, end_date, show_from, show_until, created_by
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?, ?
            )");
            
            // Execute with parameters
            $stmt->execute([
                $_POST['title'],
                $slug,
                $_POST['description'],
                $_POST['location'],
                $_POST['event_date'],
                !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                !empty($_POST['show_from']) ? $_POST['show_from'] : null,
                !empty($_POST['show_until']) ? $_POST['show_until'] : null,
                $_SESSION['user_id']
            ]);
            
            $event_id = $pdo->lastInsertId();
            
            // Handle file uploads
            if (!empty($_FILES['media']['name'][0])) {
                $uploadDir = __DIR__ . '/../uploads/events/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['media']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = $_FILES['media']['name'][$key];
                        $fileType = $_FILES['media']['type'][$key];
                        
                        // Generate unique filename
                        $uniqueName = uniqid() . '_' . $fileName;
                        $filePath = $uploadDir . $uniqueName;
                        
                        // Determine media type
                        $mediaType = strpos($fileType, 'image/') === 0 ? 'image' : 
                                   (strpos($fileType, 'video/') === 0 ? 'video' : 'document');
                        
                        if (move_uploaded_file($tmp_name, $filePath)) {
                            // Save media info to database
                            $stmt = $pdo->prepare("INSERT INTO event_media (
                                event_id, file_path, media_type, is_featured
                            ) VALUES (?, ?, ?, ?)");
                            
                            $relativePath = 'uploads/events/' . $uniqueName;
                            $isFeatured = ($key === 0) ? 1 : 0; // First upload is featured
                            
                            $stmt->execute([$event_id, $relativePath, $mediaType, $isFeatured]);
                        }
                    }
                }
            }
            
            $success = "Event created successfully!";
            // Redirect to manage events page
            header("Location: manage_events.php?success=created");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error creating event: " . $e->getMessage());
        $error = "An error occurred while creating the event. Please try again.";
    }
}
?>

<div class="container py-4">
    <h1 class="mb-4">Add New Event</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
        </div>
        
        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" required>
        </div>
        
        <div class="mb-3">
            <label for="event_date" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="event_date" name="event_date" required>
        </div>
        
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date">
        </div>
        
        <div class="mb-3">
            <label for="show_from" class="form-label">Show From</label>
            <input type="date" class="form-control" id="show_from" name="show_from">
        </div>
        <div class="mb-3">
            <label for="show_until" class="form-label">Show Until</label>
            <input type="date" class="form-control" id="show_until" name="show_until">
        </div>
        
        <div class="mb-3">
            <label for="media" class="form-label">Media (Images and Videos)</label>
            <input type="file" class="form-control" id="media" name="media[]" multiple accept="image/jpeg,image/png,image/gif,video/mp4">
            <div class="form-text">You can select multiple files. Allowed types: JPG, PNG, GIF, MP4</div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Add Event</button>
            <a href="manage_events.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Date validation
document.getElementById('end_date').addEventListener('change', function() {
    var startDate = document.getElementById('event_date').value;
    var endDate = this.value;
    
    if (startDate && endDate && endDate < startDate) {
        this.setCustomValidity('End date must be after start date');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('event_date').addEventListener('change', function() {
    var endDate = document.getElementById('end_date');
    if (endDate.value) {
        if (endDate.value < this.value) {
            endDate.setCustomValidity('End date must be after start date');
        } else {
            endDate.setCustomValidity('');
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?> 