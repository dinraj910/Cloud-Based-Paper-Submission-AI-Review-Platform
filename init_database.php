<?php
/**
 * Database Initialization Script
 * Run this script to create all required tables for the Research Portal
 */

require_once 'config/db.php';

echo "🗄️ Research Portal Database Initialization\n";
echo "==========================================\n\n";

// Read and execute the schema file
$schemaFile = __DIR__ . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    die("❌ Error: schema.sql file not found at: $schemaFile\n");
}

$schema = file_get_contents($schemaFile);
if ($schema === false) {
    die("❌ Error: Could not read schema.sql file\n");
}

// Split the schema into individual statements
$statements = array_filter(array_map('trim', explode(';', $schema)), function($stmt) {
    return !empty($stmt) && !preg_match('/^--/', $stmt);
});

echo "🔄 Creating database tables...\n\n";

$success = true;
$createdTables = [];

foreach ($statements as $statement) {
    // Skip comments and empty statements
    if (preg_match('/^(--|\s*$)/', $statement)) {
        continue;
    }
    
    // Extract table name for CREATE TABLE statements
    if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
        $tableName = $matches[1];
        
        if (mysqli_query($conn, $statement)) {
            echo "✅ Created table: $tableName\n";
            $createdTables[] = $tableName;
        } else {
            echo "⚠️  Table $tableName: " . mysqli_error($conn) . "\n";
            if (strpos(mysqli_error($conn), 'already exists') === false) {
                $success = false;
            }
        }
    } else {
        // Execute other statements (USE, SELECT, etc.)
        if (mysqli_query($conn, $statement)) {
            if (preg_match('/USE\s+(\w+)/i', $statement, $matches)) {
                echo "📂 Using database: " . $matches[1] . "\n";
            }
        } else {
            $error = mysqli_error($conn);
            if (!empty($error) && strpos($error, 'already exists') === false) {
                echo "⚠️  Warning: $error\n";
            }
        }
    }
}

echo "\n==========================================\n";

if ($success) {
    echo "✅ Database initialization completed successfully!\n";
    
    if (!empty($createdTables)) {
        echo "\n📋 Tables created/verified:\n";
        foreach ($createdTables as $table) {
            echo "   • $table\n";
        }
    }
    
    // Verify tables exist
    echo "\n🔍 Verifying tables...\n";
    $result = mysqli_query($conn, "SHOW TABLES");
    $tables = [];
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
    
    echo "📊 Found " . count($tables) . " tables: " . implode(', ', $tables) . "\n";
    
    echo "\n🎉 Your Research Portal database is ready!\n";
    echo "You can now access your application at: http://localhost:8081\n";
    
} else {
    echo "❌ Database initialization failed. Please check the errors above.\n";
}

mysqli_close($conn);
?>