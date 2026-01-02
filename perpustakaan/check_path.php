<?php
/**
 * Path Checker - Helps find the correct URL path
 * Access this file to see your project structure
 */

echo "<!DOCTYPE html><html><head><title>Path Checker</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".info{background:white;padding:20px;border-radius:8px;margin:10px 0;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo "h1{color:#F875AA;} code{background:#f0f0f0;padding:2px 6px;border-radius:4px;}</style></head><body>";

echo "<h1>🔍 Path Checker</h1>";

echo "<div class='info'>";
echo "<h2>Current File Location:</h2>";
echo "<p><strong>Absolute Path:</strong><br><code>" . __DIR__ . "</code></p>";
echo "<p><strong>Document Root:</strong><br><code>" . $_SERVER['DOCUMENT_ROOT'] . "</code></p>";
echo "<p><strong>Script Name:</strong><br><code>" . $_SERVER['SCRIPT_NAME'] . "</code></p>";
echo "<p><strong>Request URI:</strong><br><code>" . $_SERVER['REQUEST_URI'] . "</code></p>";
echo "</div>";

echo "<div class='info'>";
echo "<h2>📁 Project Structure:</h2>";
echo "<pre style='background:#f0f0f0;padding:15px;border-radius:8px;overflow-x:auto;'>";

function listDirectory($dir, $prefix = '', $maxDepth = 3, $currentDepth = 0) {
    if ($currentDepth >= $maxDepth) return;
    
    $items = scandir($dir);
    $files = [];
    $dirs = [];
    
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $dirs[] = $item;
        } else {
            $files[] = $item;
        }
    }
    
    sort($dirs);
    sort($files);
    
    $all = array_merge($dirs, $files);
    $count = count($all);
    
    foreach ($all as $index => $item) {
        $isLast = ($index == $count - 1);
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if ($isLast) {
            echo $prefix . "└── " . $item;
        } else {
            echo $prefix . "├── " . $item;
        }
        
        if (is_dir($path)) {
            echo " [DIR]";
        } else {
            $size = filesize($path);
            echo " (" . ($size < 1024 ? $size . " B" : round($size/1024, 2) . " KB") . ")";
        }
        echo "\n";
        
        if (is_dir($path) && $currentDepth < $maxDepth - 1) {
            $newPrefix = $prefix . ($isLast ? "    " : "│   ");
            listDirectory($path, $newPrefix, $maxDepth, $currentDepth + 1);
        }
    }
}

listDirectory(__DIR__);
echo "</pre>";
echo "</div>";

echo "<div class='info'>";
echo "<h2>🔗 Try These URLs:</h2>";

$scriptPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
$scriptPath = str_replace('\\', '/', $scriptPath);
$scriptPath = ltrim($scriptPath, '/');

echo "<p><strong>Migration Runner:</strong></p>";
echo "<ul>";
echo "<li><code>http://localhost/" . $scriptPath . "/run_migration.php</code></li>";

// Try common variations
$variations = [
    $scriptPath,
    basename(dirname(__DIR__)) . '/' . basename(__DIR__),
    'perpustakaan',
    'perpustakaan/perpustakaan'
];

foreach ($variations as $var) {
    if ($var != $scriptPath) {
        echo "<li><code>http://localhost/" . $var . "/run_migration.php</code></li>";
    }
}

echo "</ul>";
echo "</div>";

echo "<div class='info'>";
echo "<h2>✅ Quick Test:</h2>";
echo "<p>If you can see this page, your server is working!</p>";
echo "<p>Now try accessing: <a href='run_migration.php'><strong>run_migration.php</strong></a></p>";
echo "</div>";

echo "</body></html>";
?>

