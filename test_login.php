<?php
// Simple test file to check if PHP and database are working
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br><br>";

// Test database connection
$dbHost = 'localhost';
$dbName = 'sunrise_hospital';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass
    );
    echo "✓ Database connection successful!<br>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists!<br>";
        
        // Count users
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "✓ Found {$count} user(s) in database<br>";
    } else {
        echo "✗ Users table does not exist. Please run database.sql<br>";
    }
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>";
    echo "Please check:<br>";
    echo "1. XAMPP MySQL is running<br>";
    echo "2. Database 'sunrise_hospital' exists<br>";
    echo "3. Database credentials in config.php are correct<br>";
}
?>

