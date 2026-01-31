<?php
// Router script for PHP built-in server
// This file handles all requests and routes them to index.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Allow direct access to assets (CSS, JS, images, etc.)
$staticPaths = ['/assets/', '/uploads/', '/.well-known/'];
foreach ($staticPaths as $staticPath) {
    if (strpos($uri, $staticPath) === 0) {
        return false; // Let server handle static files
    }
}

// Check if the requested file exists and is a static file
$staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.doc', '.docx', '.xls', '.xlsx'];
foreach ($staticExtensions as $ext) {
    if (substr($uri, -strlen($ext)) === $ext) {
        // Check if file actually exists
        $filePath = __DIR__ . $uri;
        if (file_exists($filePath) && is_file($filePath)) {
            return false; // Let server serve the file
        }
        // If file doesn't exist, route to index.php (might be a route parameter)
        break;
    }
}

// Route all other requests to index.php
if (file_exists(__DIR__ . '/index.php')) {
    require __DIR__ . '/index.php';
    return true;
}

// If index.php doesn't exist, return 404
http_response_code(404);
echo "404 - File not found";
return true;

