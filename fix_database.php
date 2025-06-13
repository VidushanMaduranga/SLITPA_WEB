<?php
require_once 'config/config.php';

try {
    // Check if members table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'members'");
    if ($stmt->rowCount() == 0) {
        // Create members table
        $pdo->exec("CREATE TABLE members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            country VARCHAR(100),
            position VARCHAR(255),
            passport_number VARCHAR(50),
            membership_status ENUM('active', 'expired', 'pending') NOT NULL DEFAULT 'pending',
            member_category ENUM('resident', 'non_resident') NOT NULL,
            profile_image VARCHAR(255),
            linkedin VARCHAR(255),
            visa_status VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Members table created successfully.\n";
    } else {
        // First, create a temporary table with the new structure
        $pdo->exec("CREATE TABLE members_new (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            country VARCHAR(100),
            position VARCHAR(255),
            passport_number VARCHAR(50),
            membership_status ENUM('active', 'expired', 'pending') NOT NULL DEFAULT 'pending',
            member_category ENUM('resident', 'non_resident') NOT NULL,
            profile_image VARCHAR(255),
            linkedin VARCHAR(255),
            visa_status VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Copy data from old table to new table
        $pdo->exec("INSERT INTO members_new (id, full_name, email, phone, country, position, passport_number, membership_status, profile_image, created_at, updated_at)
                   SELECT id, full_name, email, phone, country, position, passport_number, status, profile_image, created_at, updated_at
                   FROM members");

        // Drop the old table
        $pdo->exec("DROP TABLE members");

        // Rename the new table
        $pdo->exec("RENAME TABLE members_new TO members");

        // Add foreign key constraint
        $pdo->exec("ALTER TABLE members ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");

        echo "Members table structure updated successfully.\n";
    }

    echo "Database structure check completed.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 