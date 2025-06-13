<?php
require_once __DIR__ . '/../config/config.php';
try {
    $pdo->exec("ALTER TABLE cpd_sessions ADD COLUMN location VARCHAR(255) AFTER organizer;");
    echo "Column 'location' added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'location' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
} 