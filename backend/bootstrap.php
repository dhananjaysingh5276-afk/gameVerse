<?php
/**
 * bootstrap.php - Central setup file for all backend PHP files
 */

// ============================================
// 1. DEFINE PATHS
// ============================================

// Define root path
define('ROOT_PATH', realpath(__DIR__ . '/..'));

// Configuration directory
define('CONFIG_PATH', ROOT_PATH . '/config');

// Database configuration file
define('DB_CONFIG_FILE', CONFIG_PATH . '/database.php');

// ============================================
// 2. LOAD DATABASE CONFIGURATION
// ============================================

// Check if database config exists
if (!file_exists(DB_CONFIG_FILE)) {
    die('❌ Database configuration not found at: ' . DB_CONFIG_FILE);
}

// Include database configuration
require_once DB_CONFIG_FILE;

// ============================================
// 3. SESSION MANAGEMENT
// ============================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// 4. ERROR REPORTING (Development Only)
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ============================================
// 5. HELPER FUNCTIONS
// ============================================

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
}

/**
 * Get current username
 * @return string|null
 */
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Redirect user to a page
 * @param string $url
 * @param int $statusCode
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * Display success message
 * @param string $message
 */
function showSuccess($message) {
    $_SESSION['success'] = $message;
}

/**
 * Display error message
 * @param string $message
 */
function showError($message) {
    $_SESSION['error'] = $message;
}

/**
 * Get and clear flash messages
 * @return array
 */
function getFlashMessages() {
    $messages = [
        'success' => isset($_SESSION['success']) ? $_SESSION['success'] : null,
        'error' => isset($_SESSION['error']) ? $_SESSION['error'] : null
    ];
    
    // Clear messages after reading
    unset($_SESSION['success']);
    unset($_SESSION['error']);
    
    return $messages;
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if request is POST
 * @return bool
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 * @return bool
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Get POST data safely
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getPost($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Get GET data safely
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getGet($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

// ============================================
// 6. INITIALIZATION COMPLETE
// ============================================

// Optional: Log that bootstrap loaded successfully
// error_log("Bootstrap loaded successfully");
?>