<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/config/db.php';

// Define base path
define('BASE_PATH', '');

// Simple routing logic
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove base path from URI
$uri = str_replace(BASE_PATH, '', $requestUri);
$uri = trim($uri, '/');

// Map routes to files
$routes = [
    '' => 'public/index.php',
    'dashboard' => 'public/dashboard.php',
    'login' => 'public/login.php',
    'register' => 'public/register.php',
    'logout' => 'public/logout.php',
    'not_approved' => 'public/not_approved.php',
    'add_post' => 'public/add_post.php',
    'delete_post' => 'public/delete_post.php',
    'edit_post' => 'public/edit_post.php',
    'reject_user' => 'public/reject_user.php',
    'delete_user' => 'public/delete_user.php',
    'edit_user' => 'public/edit_user.php',
    'update_role' => 'public/update_role.php',
    'approve_user' => 'public/approve_user.php',
    'add_user' => 'public/add_user.php',
    'posts' => 'public/posts.php',
    'profile' => 'public/profile.php',
];

// Handle static assets
if (preg_match('/^assets\/(.+)$/', $uri, $matches)) {
    $filePath = __DIR__ . '/assets/' . $matches[1];
    if (file_exists($filePath)) {
        if (str_ends_with($filePath, '.css')) {
            header('Content-Type: text/css');
        } elseif (str_ends_with($filePath, '.js')) {
            header('Content-Type: application/javascript');
        }
        readfile($filePath);
        exit;
    }
}

// Handle Uploads directory
if (preg_match('/^Uploads\/(.+)$/', $uri, $matches)) {
    $filePath = __DIR__ . '/Uploads/' . $matches[1];
    if (file_exists($filePath)) {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            readfile($filePath);
            exit;
        }
    }
}

// Route handling
if (array_key_exists($uri, $routes)) {
    $file = __DIR__ . '/' . $routes[$uri];
    if (file_exists($file)) {
        include $file;
        exit;
    }
}

// Fallback for unmatched routes
if ($uri === '' || $uri === 'index.php') {
    if (isset($_SESSION['user_id'])) {
        header("Location: /dashboard.php");
        exit;
    }
    include __DIR__ . '/public/index.php';
    exit;
}

http_response_code(404);
echo "404 Not Found";
exit;