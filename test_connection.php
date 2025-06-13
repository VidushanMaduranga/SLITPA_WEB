<?php
$host = 'localhost';
$db   = 'slitpa-1-db'; 
$user = 'root';      
$pass = '';          
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}