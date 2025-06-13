<?php
require_once 'config/config.php';

try {
    $email = 'admin@slitpa.org';
    $new_password = 'admin123';
    $hashed_password = hash_password($new_password);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND user_type = 'admin'");
    $result = $stmt->execute([$hashed_password, $email]);
    if ($result) {
        echo "Admin password has been reset to 'admin123'.\n";
    } else {
        echo "Failed to reset admin password.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 