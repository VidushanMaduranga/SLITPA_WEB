<?php
/**
 * Event Media Handler
 * This file handles the upload and storage of media files (images and videos) for events
 */

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/../config/config.php';

/**
 * Handles the upload of media files for events
 * 
 * @param int $event_id The ID of the event to associate media with
 * @param array $files The $_FILES array containing uploaded files
 * @return array Array containing upload status, success messages, and errors
 */
function handleMediaUpload($event_id, $files) {
    global $pdo;
    
    // Define allowed file types and size limits
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $max_file_size = 50 * 1024 * 1024; // 50MB size limit
    
    // Set up upload directory and initialize result arrays
    $upload_path = __DIR__ . '/../uploads/events/';
    $successful_uploads = [];
    $errors = [];

    // Handle image upload if present
    if (isset($files['image']) && $files['image']['error'] === 0) {
        $image = $files['image'];
        
        // Validate image type
        if (!in_array($image['type'], $allowed_image_types)) {
            $errors[] = "Invalid image type. Allowed types: JPG, PNG, GIF";
        }
        // Validate image size
        elseif ($image['size'] > $max_file_size) {
            $errors[] = "Image file is too large. Maximum size: 50MB";
        }
        else {
            // Generate unique filename
            $image_filename = 'event_' . $event_id . '_' . time() . '_' . basename($image['name']);
            $image_path = $upload_path . $image_filename;
            
            // Attempt to move uploaded file to destination
            if (move_uploaded_file($image['tmp_name'], $image_path)) {
                // Save image record to database
                $stmt = $pdo->prepare("INSERT INTO event_media (event_id, file_path, media_type, upload_date) VALUES (?, ?, 'image', NOW())");
                $stmt->execute([$event_id, 'uploads/events/' . $image_filename]);
                $successful_uploads[] = "Image uploaded successfully";
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }

    // Handle video upload if present
    if (isset($files['video']) && $files['video']['error'] === 0) {
        $video = $files['video'];
        
        // Validate video type
        if (!in_array($video['type'], $allowed_video_types)) {
            $errors[] = "Invalid video type. Allowed types: MP4, WebM, OGG";
        }
        // Validate video size
        elseif ($video['size'] > $max_file_size) {
            $errors[] = "Video file is too large. Maximum size: 50MB";
        }
        else {
            // Generate unique filename
            $video_filename = 'event_' . $event_id . '_' . time() . '_' . basename($video['name']);
            $video_path = $upload_path . $video_filename;
            
            // Attempt to move uploaded file to destination
            if (move_uploaded_file($video['tmp_name'], $video_path)) {
                // Save video record to database
                $stmt = $pdo->prepare("INSERT INTO event_media (event_id, file_path, media_type, upload_date) VALUES (?, ?, 'video', NOW())");
                $stmt->execute([$event_id, 'uploads/events/' . $video_filename]);
                $successful_uploads[] = "Video uploaded successfully";
            } else {
                $errors[] = "Failed to upload video";
            }
        }
    }

    // Return upload results
    return [
        'success' => count($successful_uploads) > 0,
        'messages' => $successful_uploads,
        'errors' => $errors
    ];
} 