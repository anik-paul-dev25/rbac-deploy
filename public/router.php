<?php
// public/router.php

$rootDir = dirname(__DIR__); // Get the project root directory
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestFile = $rootDir . $requestUri;

// Serve static files from assets/ and Uploads/ directories
if (preg_match('#^/(assets|Uploads)/#', $requestUri) && is_file($requestFile)) {
    $extension = strtolower(pathinfo($requestFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    header('Content-Type: ' . $mimeType);
    readfile($requestFile);
    exit;
}

// Check if the request is for a PHP file that exists in the public/ directory
$publicFile = __DIR__ . $requestUri;
if (is_file($publicFile) && pathinfo($publicFile, PATHINFO_EXTENSION) === 'php') {
    // Include the PHP file directly to avoid redirects
    return false; // Let PHP server process the file
}

// If the request is not for a static file or existing PHP file, route to index.php
// Avoid including index.php directly to prevent session-related redirect loops
if ($requestUri === '/' || $requestUri === '/index.php') {
    return false; // Let PHP server handle index.php
}

// For any other non-existent files, return a 404
http_response_code(404);
echo '404 Not Found';
exit;
?>