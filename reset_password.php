<?php
require_once 'config/config.php';

try {
    // Reset password for test account
    $email = 'john.smith@example.com';
    $new_password = 'Test@123';
    $hashed_password = hash_password($new_password);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashed_password, $email]);

    if ($result) {
        echo "Password has been reset successfully!\n";
        echo "New password hash: " . $hashed_password . "\n";
    } else {
        echo "Failed to reset password.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 