<?php
require_once __DIR__ . '/../config/config.php';

try {
    // Create the cpd_session_media table
    $sql = "CREATE TABLE IF NOT EXISTS cpd_session_media (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        media_type ENUM('image', 'video') NOT NULL,
        upload_date DATETIME NOT NULL,
        FOREIGN KEY (session_id) REFERENCES cpd_sessions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Table cpd_session_media created successfully";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 