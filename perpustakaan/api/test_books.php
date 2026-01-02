<?php
// test_books.php - Letakkan di folder api/
// Buka: http://localhost/perpustakaan/api/test_books.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Test Database Connection</h2>";
echo "<hr>";

// Test 1: Include config
echo "<h3>1. Test Config</h3>";
try {
    require_once __DIR__ . '/../includes/config.php';
    echo "✅ Config loaded<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Database connection
echo "<h3>2. Test Database</h3>";
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
    exit;
} else {
    echo "✅ Database connected<br>";
}

// Test 3: Session
echo "<h3>3. Test Session</h3>";
echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Active" : "❌ Inactive") . "<br>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . $_SESSION['username'] . "<br>";
    echo "Role: " . $_SESSION['role'] . "<br>";
} else {
    echo "⚠️ Not logged in<br>";
}

// Test 4: Count books
echo "<h3>4. Test Books Table</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM books");
if ($result) {
    $count = $result->fetch_assoc()['total'];
    echo "✅ Books table exists<br>";
    echo "Total books: <strong>" . $count . "</strong><br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 5: Get sample data
echo "<h3>5. Sample Books</h3>";
$result = $conn->query("SELECT id, title, author FROM books LIMIT 3");
if ($result && $result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: {$row['id']} - {$row['title']} by {$row['author']}</li>";
    }
    echo "</ul>";
} else {
    echo "⚠️ No books found<br>";
}

// Test 6: Test JSON output
echo "<h3>6. Test JSON Output</h3>";
$testData = [
    'success' => true,
    'message' => 'Test berhasil',
    'count' => 3
];
echo "JSON: <pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

echo "<hr>";
echo "<p style='color: green;'><strong>Jika semua ✅, maka database OK!</strong></p>";
echo "<p><a href='../pages/bibliografi.php'>← Kembali ke Bibliografi</a></p>";

$conn->close();
?>