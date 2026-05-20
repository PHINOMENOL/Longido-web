<?php
/**
 * Database Connection
 * Edit these credentials if your XAMPP MySQL setup is different
 */

$db_host = "localhost";
$db_user = "root";
$db_pass = "";  // Default XAMPP has no password
$db_name = "longido_lodge";

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . 
        "<br><br>Make sure XAMPP MySQL is running and you imported database.sql in phpMyAdmin.");
}

// Helper to sanitize output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Start session for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Flash message helpers
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
