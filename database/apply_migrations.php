<?php
/**
 * Migration Runner
 * Automatically applies pending SQL migrations to the database
 */

require_once __DIR__ . '/../backend/config.php';

echo "Starting migrations...\n";

$database = new Database();
$db = $database->getConnection();

$migrations = [
    'add_profile_image_to_users.sql',
    'add-customer-id-migration.sql',
    'create_reviews_table.sql'
];

foreach ($migrations as $file) {
    echo "Applying $file... ";
    
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        echo "FAILED (File not found)\n";
        continue;
    }
    
    $sql = file_get_contents($path);
    
    // Remove USE statements if present to avoid conflicts with established connection
    $sql = preg_replace('/USE\s+[^;]+;/i', '', $sql);
    
    // Split into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = true;
    foreach ($queries as $query) {
        if (empty($query)) continue;
        
        try {
            if (!$db->query($query)) {
                $error = $db->error;
                if ($error && strpos($error, 'Duplicate column name') === false && 
                    strpos($error, 'already exists') === false) {
                    echo "ERROR in query: " . $query . "\n";
                    echo "Error message: " . $error . "\n";
                    $success = false;
                    break;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $error = $e->getMessage();
            if (strpos($error, 'Duplicate column name') === false && 
                strpos($error, 'already exists') === false &&
                strpos($error, 'Duplicate key name') === false) {
                echo "ERROR in query: " . $query . "\n";
                echo "Error message: " . $error . "\n";
                $success = false;
                break;
            }
        }
    }
    
    if ($success) {
        echo "DONE\n";
    } else {
        echo "FAILED\n";
    }
}

echo "\nAll migrations processed!\n";
?>
