<?php
require_once 'config/config.php';

// Test password
$test_password = 'Test@123';

// Get the stored password from database
$stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
$stmt->execute(['john.smith@example.com']);
$stored_hash = $stmt->fetchColumn();

echo "Testing password verification:\n";
echo "Test password: " . $test_password . "\n";
echo "Stored hash: " . $stored_hash . "\n";

// Test direct verification
$direct_verify = password_verify($test_password . PASSWORD_PEPPER, $stored_hash);
echo "Direct verification result: " . ($direct_verify ? "true" : "false") . "\n";

// Test using verify_password function
$function_verify = verify_password($test_password, $stored_hash);
echo "Function verification result: " . ($function_verify ? "true" : "false") . "\n";

// Generate new hash for comparison
$new_hash = hash_password($test_password);
echo "New hash generated: " . $new_hash . "\n";

// Test verification with new hash
$new_verify = verify_password($test_password, $new_hash);
echo "New hash verification result: " . ($new_verify ? "true" : "false") . "\n";
?> 