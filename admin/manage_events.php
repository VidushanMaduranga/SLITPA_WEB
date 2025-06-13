<?php

require_once __DIR__ . '/../config/config.php'; 
include __DIR__ . '/../includes/admin_header.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Check if the connection was successful
if ($conn->connect_error) {
    // If the connection fails, display an error message and stop execution.
    die("Connection failed: " . $conn->connect_error);
}



$edit_event = null; //  Initialize variables to hold event data for editing
$event_media = [];   //  and media data.

// **Handle Edit Event Request**
//  Check if the user has clicked the "Edit" button for an event.
if (isset($_GET['edit'])) {
    $event_id = filter_input(INPUT_GET, 'edit', FILTER_SANITIZE_NUMBER_INT); // Get and sanitize the event ID
    if ($event_id) {
        //  We have an event ID, so let's fetch the event data from the database.
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_event = $result->fetch_assoc(); // Fetch the event data
        $stmt->close();

        if ($edit_event) {
            // We found the event, so now fetch its associated media.
            $stmt_media = $conn->prepare("SELECT id, file_path, media_type, is_featured FROM event_media WHERE event_id = ?");
            $stmt_media->bind_param("i", $event_id);
            $stmt_media->execute();
            $result_media = $stmt_media->get_result();
            $event_media = $result_media->fetch_all(MYSQLI_ASSOC); // Fetch all media
            $stmt_media->close();
        } else {
            //  Event not found.  Set an error message and redirect.
            $_SESSION['error'] = "Event not found.";
            header("Location: manage_events.php");
            exit();
        }
    }
}


if (isset($_POST['add_event'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $show_from = filter_input(INPUT_POST, 'show_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $show_until = filter_input(INPUT_POST, 'show_until', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($title) || empty($description) || empty($location) || empty($event_date)) {
        //  Check for required fields
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Generate and ensure unique slug
        $slug = generateSlug($title);
        $slug = ensureUniqueSlug($conn, $slug);
        //  All required fields are filled, so insert the event into the database.
        $stmt = $conn->prepare("INSERT INTO events (title, slug, description, location, event_date, end_date, show_from, show_until) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $slug, $description, $location, $event_date, $end_date, $show_from, $show_until);
        if ($stmt->execute()) {
            //  Event inserted successfully!
            $event_id = $conn->insert_id; // Get the ID of the newly inserted event.

            //  Handle media uploads (images and videos)
            if (!empty($_FILES['media']['name'][0])) {
                $uploadDir = '../uploads/events/'; //  Directory to store uploaded files
                 if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true); //create directory if not exist
                }
                $featured = true; // The first uploaded file will be the featured image/video
                foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
                    // Loop through each uploaded file.  $_FILES['media']['tmp_name'] is an array
                    //  because the "multiple" attribute was used in the HTML form.
                    $file_name = basename($_FILES['media']['name'][$key]);
                    $file_type = $_FILES['media']['type'][$key];
                    $file_size = $_FILES['media']['size'][$key];
                    $file_tmp = $_FILES['media']['tmp_name'][$key];
                    $file_error = $_FILES['media']['error'][$key];

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']; // Allowed file types
                    if ($file_error === UPLOAD_ERR_OK && in_array($file_type, $allowedTypes)) {
                        //  File uploaded successfully AND is an allowed type.
                        $new_file_name = uniqid() . '_' . $file_name; // Generate a unique filename
                        $destination = $uploadDir . $new_file_name;     //  Full path to save the file
                        if (move_uploaded_file($file_tmp, $destination)) {
                            //  File moved successfully.  Now, save the file information
                            //  to the database.
                            $media_type = strpos($file_type, 'image') === 0 ? 'image' : 'video'; // Determine media type
                            $is_featured = $featured ? 1 : 0;  //  Set featured flag
                            $file_path = 'uploads/events/' . $new_file_name;
                            $stmt_media = $conn->prepare("INSERT INTO event_media (event_id, file_path, media_type, is_featured) VALUES (?, ?, ?, ?)");
                            $stmt_media->bind_param("issi", $event_id, $file_path, $media_type, $is_featured);
                            $stmt_media->execute();
                            $stmt_media->close();
                            $featured = false; //  Only the first file is featured.
                        } else {
                            //  Error moving file.
                            $_SESSION['error'] .= "Error uploading file: " . $file_name . "<br>";
                        }
                    } elseif ($file_error === UPLOAD_ERR_OK) {
                         $_SESSION['error'] .= "Invalid file type for: " . $file_name . ". Allowed types: JPG, PNG, GIF, MP4<br>";
                    } elseif ($file_error !== UPLOAD_ERR_NO_FILE) {
                        //  File upload error.
                        $_SESSION['error'] .= "Upload error for: " . $file_name . ". Error code: " . $file_error . "<br>";
                    }
                     //  If file_error === UPLOAD_ERR_NO_FILE, it means no file was uploaded,
                    //  which is not an error in this case, so we don't add to the error message.
                }
            }

            $_SESSION['message'] = "Event added successfully!"; // Set success message
            header("Location: manage_events.php");             //  Redirect
            exit();
        } else {
            //  Error inserting event into database.
            $_SESSION['error'] = "Error adding event: " . $stmt->error;
        }
        $stmt->close(); //  Close the statement.
    }
}

// **Handle Update Event Form Submission**
//  Check if the user has submitted the "Update Event" form.  This is very
//  similar to the "Add Event" form handling.
if (isset($_POST['update_event'])) {
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $show_from = filter_input(INPUT_POST, 'show_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $show_until = filter_input(INPUT_POST, 'show_until', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($title) || empty($description) || empty($location) || empty($event_date)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Generate slug from title
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (empty($slug)) {
            $slug = 'event-' . time(); // Fallback if slug is empty
        }
        
        // Check for duplicate slugs
        $original_slug = $slug;
        $counter = 1;
        
        do {
            if ($counter > 1) {
                $slug = $original_slug . '-' . $counter;
            }
            
            $check_slug = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE slug = ? AND id != ?");
            $check_slug->bind_param("si", $slug, $event_id);
            $check_slug->execute();
            $result = $check_slug->get_result();
            $count = $result->fetch_assoc()['count'];
            $counter++;
        } while ($count > 0);

        // Now update the event with the unique slug
        $stmt = $conn->prepare("UPDATE events SET title = ?, slug = ?, description = ?, location = ?, event_date = ?, end_date = ?, show_from = ?, show_until = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi", $title, $slug, $description, $location, $event_date, $end_date, $show_from, $show_until, $event_id);
        if ($stmt->execute()) {
             if (!empty($_FILES['media']['name'][0])) {
                $uploadDir = '../uploads/events/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $featured = empty($event_media); // If no existing media, first upload is featured
                foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
                    $file_name = basename($_FILES['media']['name'][$key]);
                    $file_type = $_FILES['media']['type'][$key];
                    $file_size = $_FILES['media']['size'][$key];
                    $file_tmp = $_FILES['media']['tmp_name'][$key];
                    $file_error = $_FILES['media']['error'][$key];

                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4'];
                    if ($file_error === UPLOAD_ERR_OK && in_array($file_type, $allowedTypes)) {
                        $new_file_name = uniqid() . '_' . $file_name;
                        $destination = $uploadDir . $new_file_name;
                        if (move_uploaded_file($file_tmp, $destination)) {
                            $media_type = strpos($file_type, 'image') === 0 ? 'image' : 'video';
                            $is_featured = $featured ? 1 : 0;
                            $file_path = 'uploads/events/' . $new_file_name;
                            $stmt_media = $conn->prepare("INSERT INTO event_media (event_id, file_path, media_type, is_featured) VALUES (?, ?, ?, ?)");
                            $stmt_media->bind_param("issi", $event_id, $file_path, $media_type, $is_featured);
                            $stmt_media->execute();
                            $stmt_media->close();
                            $featured = false;
                        } else {
                            $_SESSION['error'] .= "Error uploading file: " . $file_name . "<br>";
                        }
                    } elseif ($file_error === UPLOAD_ERR_OK) {
                        $_SESSION['error'] .= "Invalid file type for: " . $file_name . ". Allowed types: JPG, PNG, GIF, MP4<br>";
                    } elseif ($file_error !== UPLOAD_ERR_NO_FILE) {
                        $_SESSION['error'] .= "Upload error for: " . $file_name . ". Error code: " . $file_error . "<br>";
                    }
                }
            }
            $_SESSION['message'] = "Event updated successfully!";
            header("Location: manage_events.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating event: " . $stmt->error;
        }
        $stmt->close();
    }
}

// **Handle Delete Event Request**
//  Check if the user has submitted the "Delete Event" form.
if (isset($_POST['delete_event'])) {
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    if ($event_id) {
        //  We have an event ID to delete.
        //  First, delete the associated media files from the file system
        $stmt_select_media = $conn->prepare("SELECT file_path FROM event_media WHERE event_id = ?");
        $stmt_select_media->bind_param("i", $event_id);
        $stmt_select_media->execute();
        $result_media_to_delete = $stmt_select_media->get_result();
        while ($media_to_delete = $result_media_to_delete->fetch_assoc()) {
            $file_to_delete = '../' . $media_to_delete['file_path']; // Path to the file
            if (file_exists($file_to_delete)) {
                //  Check if the file exists before attempting to delete it.
                unlink($file_to_delete); //  Delete the file
            }
        }
        $stmt_select_media->close();

        //  Now, delete the media records from the database
        $stmt_delete_media = $conn->prepare("DELETE FROM event_media WHERE event_id = ?");
        $stmt_delete_media->bind_param("i", $event_id);
        $stmt_delete_media->execute();
        $stmt_delete_media->close();

        //  Finally, delete the event record from the database
        $stmt_delete_event = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt_delete_event->bind_param("i", $event_id);
        if ($stmt_delete_event->execute()) {
            //  Event deleted successfully!
            $_SESSION['message'] = "Event deleted successfully!";
        } else {
            //  Error deleting event.
            $_SESSION['error'] = "Error deleting event: " . $stmt_delete_event->error;
        }
        $stmt_delete_event->close();
        header("Location: manage_events.php"); //  Redirect
        exit();
    }
}

// **Handle Delete Media Request**
if (isset($_POST['delete_media'])) {
    $media_id = filter_input(INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT);
     $file_path = filter_input(INPUT_POST, 'file_path', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($media_id) {
        $stmt_delete = $conn->prepare("DELETE FROM event_media WHERE id = ?");
        $stmt_delete->bind_param("i", $media_id);
        if ($stmt_delete->execute()) {
             $file_to_delete = '../' . $file_path;
            if (file_exists($file_to_delete)) {
                if (unlink($file_to_delete)) {
                     $_SESSION['message'] = "Media deleted successfully!";
                } else {
                     $_SESSION['error'] = "Error deleting media file.";
                }
            } else {
                 $_SESSION['message'] = "Media record deleted, file not found.";
            }
           
        } else {
            $_SESSION['error'] = "Error deleting media record: " . $stmt_delete->error;
        }
        $stmt_delete->close();
        header("Location: manage_events.php?edit=" . ($edit_event ? $edit_event['id'] : ''));
        exit();
    }
}

// **Fetch All Events**
//  Get a list of all events from the database, ordered by event date.
$result_events = $conn->query("SELECT id, title, event_date, location FROM events ORDER BY event_date DESC");
$events = $result_events->fetch_all(MYSQLI_ASSOC); // Fetch all events as an associative array

$conn->close(); //  Close the database connection.  It's important to do this
              //  when you're finished with it.

// Add slug generation functions
function generateSlug($title) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function ensureUniqueSlug($conn, $slug, $id = null) {
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
        $types = "s";
        $params = [$slug];
        if ($id !== null) {
            $query .= " AND id != ?";
            $types .= "i";
            $params[] = $id;
        }
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();
        $counter++;
    } while ($count > 0);
    return $slug;
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
        /* Your existing styles */
        .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .event-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .event-date {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .event-location {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        .event-actions {
            display: flex;
            gap: 10px;
        }
        .media-preview {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .media-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .media-item {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 5px;
            width: 150px;
            text-align: center;
        }
        .media-item img, .media-item video {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .media-item .delete-media-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            padding: 2px 5px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .media-item:hover .delete-media-btn {
            opacity: 1;
        }

    </style>
</head>
<body>

    <div class="container py-5">
        <h1 class="mb-4"><?= $edit_event ? 'Edit Event' : 'Add New Event' ?></h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?= $edit_event ? $edit_event['id'] : '' ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= $edit_event ? $edit_event['title'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?= $edit_event ? $edit_event['description'] : '' ?></textarea>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= $edit_event ? $edit_event['location'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" value="<?= $edit_event ? $edit_event['event_date'] : '' ?>" required>
            </div>
             <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $edit_event ? $edit_event['end_date'] : '' ?>" >
            </div>
            <div class="mb-3">
                <label for="show_from" class="form-label">Show From</label>
                <input type="date" class="form-control" id="show_from" name="show_from" value="<?= $edit_event ? $edit_event['show_from'] : '' ?>">
            </div>
            <div class="mb-3">
                <label for="show_until" class="form-label">Show Until</label>
                <input type="date" class="form-control" id="show_until" name="show_until" value="<?= $edit_event ? $edit_event['show_until'] : '' ?>">
            </div>
            <div class="mb-3">
                <label for="media" class="form-label">Media (Images and Videos)</label>
                <input type="file" class="form-control" id="media" name="media[]" multiple accept="image/jpeg, image/png, image/gif, video/mp4">
                <small class="text-muted">You can select multiple files.  Allowed types: JPG, PNG, GIF, MP4</small>
            </div>

            <?php if ($edit_event): ?>
                <div class="media-container">
                    <?php foreach ($event_media as $media): ?>
                        <div class="media-item">
                            <?php if ($media['media_type'] === 'image'): ?>
                                <img src="../<?= $media['file_path'] ?>" alt="Event Media" class="media-preview">
                            <?php elseif ($media['media_type'] === 'video'): ?>
                                <video controls class="media-preview">
                                    <source src="../<?= $media['file_path'] ?>" type="<?= mime_content_type('../' . $media['file_path']) ?>">
                                        Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                            <p><?php echo ($media['is_featured'] == 1) ? "Featured" : ""; ?></p>
                            <form method="POST">
                                <input type="hidden" name="media_id" value="<?= $media['id'] ?>">
                                 <input type="hidden" name="file_path" value="<?= $media['file_path'] ?>">
                                <button type="submit" name="delete_media" class="delete-media-btn" onclick="return confirm('Are you sure you want to delete this media?')"><i class="bi bi-x-circle"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_event): ?>
                <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
            <?php else: ?>
                <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
            <?php endif; ?>
            <a href="manage_events.php" class="btn btn-secondary">Cancel</a>
        </form>

        <hr>

        <h2>Existing Events</h2>
        <div class="row">
            <?php if (empty($events)): ?>
                <div class="col-12">No events found.</div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6">
                        <div class="event-card">
                            <h3 class="event-title"><?= $event['title'] ?></h3>
                            <p class="event-date">Date: <?= $event['event_date'] ?></p>
                            <p class="event-location">Location: <?= $event['location'] ?></p>
                            <div class="event-actions">
                                <a href="manage_events.php?edit=<?= $event['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" name="delete_event" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Your existing JavaScript
        document.querySelectorAll('.delete-media-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                if (!confirm('Are you sure you want to delete this media?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>