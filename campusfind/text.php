<?php
echo "<h1>PHP is Working!</h1>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

// Check if files exist
echo "<h2>File Checks:</h2>";
$files = ['config/database.php', 'includes/functions.php', 'includes/auth.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file found<br>";
    } else {
        echo "❌ $file NOT found<br>";
    }
}

// Try to connect to database
echo "<h2>Database Test:</h2>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        echo "✅ Database connection successful!<br>";
        echo "Database: " . DB_NAME . "<br>";
        echo "Host: " . DB_HOST . "<br>";
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "<br>";
    }
}

// Check database tables
echo "<h2>Tables Check:</h2>";
try {
    $tables = ['users', 'lost_items', 'found_items', 'claims', 'match_log', 'notifications'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' NOT found<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}
?>