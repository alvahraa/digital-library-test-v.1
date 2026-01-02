<?php
/**
 * Database Migration Runner
 * Run this file in your browser to execute the migration
 * URL: http://localhost/perpustakaan/run_migration.php
 */

require_once __DIR__ . '/includes/config.php';

// Security check - only allow in development
$allowed_hosts = ['localhost', '127.0.0.1'];
$host = $_SERVER['HTTP_HOST'] ?? '';

if (!in_array($host, $allowed_hosts) && strpos($host, 'localhost') === false) {
    die('Migration can only be run on localhost for security reasons.');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Add copy_id to transactions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #F875AA 0%, #ff4d9f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #1a1a1a;
            margin-bottom: 10px;
            font-size: 32px;
            background: linear-gradient(135deg, #F875AA 0%, #ff4d9f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.8;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .status.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .status.info {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            color: #0c5460;
        }
        .btn {
            padding: 14px 32px;
            background: linear-gradient(135deg, #F875AA 0%, #ff4d9f 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 14px rgba(248, 117, 170, 0.25);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(248, 117, 170, 0.35);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .step {
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #F875AA;
        }
        .step-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .step-desc {
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Migration</h1>
        <p class="subtitle">Add copy_id column to transactions table</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
            echo '<div class="status info">';
            echo "Starting migration...\n";
            echo "Database: " . DB_NAME . "\n";
            echo "Host: " . DB_HOST . "\n";
            echo "========================================\n\n";
            
            $errors = [];
            $success = [];
            
            // Read migration file
            $migration_file = __DIR__ . '/database/migration_add_copy_id.sql';
            
            if (!file_exists($migration_file)) {
                $errors[] = "Migration file not found: $migration_file";
            } else {
                $sql = file_get_contents($migration_file);
                
                // Split SQL into individual statements
                // Remove comments first
                $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
                $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
                
                // Split by semicolon
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt);
                    }
                );
                
                // Execute each statement
                foreach ($statements as $index => $statement) {
                    if (empty(trim($statement))) continue;
                    
                    // Skip comments
                    if (preg_match('/^--/', $statement)) continue;
                    
                    $statement = trim($statement);
                    if (empty($statement)) continue;
                    
                    echo "Executing statement " . ($index + 1) . "...\n";
                    
                    // Check if column already exists
                    if (stripos($statement, 'ADD COLUMN `copy_id`') !== false) {
                        $check_query = "SHOW COLUMNS FROM transactions LIKE 'copy_id'";
                        $check_result = $conn->query($check_query);
                        
                        if ($check_result && $check_result->num_rows > 0) {
                            echo "⚠ Column 'copy_id' already exists. Skipping...\n";
                            $success[] = "Column already exists - skipped";
                            continue;
                        }
                    }
                    
                    // Check if index already exists
                    if (stripos($statement, 'ADD KEY `idx_copy_id`') !== false) {
                        $check_query = "SHOW INDEX FROM transactions WHERE Key_name = 'idx_copy_id'";
                        $check_result = $conn->query($check_query);
                        
                        if ($check_result && $check_result->num_rows > 0) {
                            echo "⚠ Index 'idx_copy_id' already exists. Skipping...\n";
                            $success[] = "Index already exists - skipped";
                            continue;
                        }
                    }
                    
                    // Check if foreign key already exists
                    if (stripos($statement, 'ADD CONSTRAINT `fk_transactions_copy`') !== false) {
                        $check_query = "SELECT CONSTRAINT_NAME 
                                       FROM information_schema.TABLE_CONSTRAINTS 
                                       WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                                       AND TABLE_NAME = 'transactions' 
                                       AND CONSTRAINT_NAME = 'fk_transactions_copy'";
                        $check_result = $conn->query($check_query);
                        
                        if ($check_result && $check_result->num_rows > 0) {
                            echo "⚠ Foreign key 'fk_transactions_copy' already exists. Skipping...\n";
                            $success[] = "Foreign key already exists - skipped";
                            continue;
                        }
                    }
                    
                    // Execute statement
                    if ($conn->query($statement)) {
                        echo "✓ Statement executed successfully\n";
                        $success[] = "Statement " . ($index + 1) . " executed";
                    } else {
                        $error_msg = $conn->error;
                        // Check if it's a "duplicate" error (column/index already exists)
                        if (stripos($error_msg, 'Duplicate column') !== false || 
                            stripos($error_msg, 'Duplicate key') !== false ||
                            stripos($error_msg, 'already exists') !== false) {
                            echo "⚠ " . $error_msg . " (Skipping...)\n";
                            $success[] = "Statement " . ($index + 1) . " - already exists (skipped)";
                        } else {
                            echo "✗ Error: $error_msg\n";
                            $errors[] = "Statement " . ($index + 1) . ": $error_msg";
                        }
                    }
                    
                    echo "\n";
                }
            }
            
            echo "========================================\n";
            
            if (empty($errors)) {
                echo "\n✅ Migration completed successfully!\n";
                echo "\nYou can now test the book copies integration in the circulation system.\n";
            } else {
                echo "\n⚠ Migration completed with some errors:\n";
                foreach ($errors as $error) {
                    echo "- $error\n";
                }
            }
            
            echo '</div>';
            
            if (empty($errors)) {
                echo '<div class="status success">';
                echo "✅ All migration steps completed successfully!\n\n";
                echo "Next steps:\n";
                echo "1. Go to Bibliografi page and manage book copies\n";
                echo "2. Test borrowing books with copy tracking\n";
                echo "3. Test returning books and verify copy status updates\n";
                echo '</div>';
            }
            
        } else {
            ?>
            <div class="step">
                <div class="step-title">Step 1: Check Current Database Structure</div>
                <div class="step-desc">Verify if copy_id column exists in transactions table</div>
            </div>
            
            <?php
            // Check current structure
            $check_query = "SHOW COLUMNS FROM transactions LIKE 'copy_id'";
            $result = $conn->query($check_query);
            
            if ($result && $result->num_rows > 0) {
                echo '<div class="status info">';
                echo "ℹ Column 'copy_id' already exists in transactions table.\n";
                echo "Migration may have already been run.\n";
                echo '</div>';
            } else {
                echo '<div class="status info">';
                echo "ℹ Column 'copy_id' does not exist yet.\n";
                echo "Ready to run migration.\n";
                echo '</div>';
            }
            ?>
            
            <div class="step">
                <div class="step-title">Step 2: Run Migration</div>
                <div class="step-desc">Click the button below to execute the migration</div>
            </div>
            
            <form method="POST" style="margin-top: 20px;">
                <button type="submit" name="run_migration" class="btn">
                    Run Migration Now
                </button>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 12px; border-left: 4px solid #ffc107;">
                <strong>⚠ Important Notes:</strong>
                <ul style="margin-top: 10px; margin-left: 20px; color: #856404;">
                    <li>Make sure you have a backup of your database</li>
                    <li>This migration will add a new column to the transactions table</li>
                    <li>The migration is safe to run multiple times (it checks for existing columns)</li>
                    <li>After migration, test the circulation system thoroughly</li>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>

