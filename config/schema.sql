/**
 * Event Media Table
 * Stores media files (images and videos) associated with events
 */
CREATE TABLE IF NOT EXISTS event_media (
    -- Primary key for media entries
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Reference to the associated event
    event_id INT NOT NULL,
    
    -- Path to the media file relative to the website root
    file_path VARCHAR(255) NOT NULL,
    
    -- Type of media (image or video)
    media_type ENUM('image', 'video') NOT NULL,
    
    -- When the media was uploaded
    upload_date DATETIME NOT NULL,
    
    -- Automatically delete media when associated event is deleted
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CPD Session Media Table
-- Stores media files (images and videos) associated with CPD sessions
CREATE TABLE IF NOT EXISTS cpd_session_media (
    -- Primary key for media entries
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Reference to the associated CPD session
    session_id INT NOT NULL,
    
    -- Path to the media file relative to the website root
    file_path VARCHAR(255) NOT NULL,
    
    -- Type of media (image or video)
    media_type ENUM('image', 'video') NOT NULL,
    
    -- When the media was uploaded
    upload_date DATETIME NOT NULL,
    
    -- Automatically delete media when associated session is deleted
    FOREIGN KEY (session_id) REFERENCES cpd_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 