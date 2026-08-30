<?php
// Vercel Serverless Entry Point for PHP Multi-page App
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = urldecode($path);

// Root path -> index.php
if ($path === '/' || $path === '') {
    chdir(__DIR__ . '/..');
    require_once __DIR__ . '/../index.php';
    exit();
}

$root_dir = realpath(__DIR__ . '/..');
$target_file = realpath($root_dir . $path);

// Check if exact file exists and is within project root
if ($target_file && strpos($target_file, $root_dir) === 0 && is_file($target_file)) {
    $extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($extension === 'php') {
        // Execute PHP file in its own directory context
        chdir(dirname($target_file));
        require $target_file;
        exit();
    } else {
        // Static file requested via fallback
        $mimes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'svg'  => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico'  => 'image/x-icon',
            'json' => 'application/json',
            'pdf'  => 'application/pdf'
        ];
        if (isset($mimes[$extension])) {
            header("Content-Type: " . $mimes[$extension]);
        }
        readfile($target_file);
        exit();
    }
}

// Try appending .php if requested without extension (e.g., /equipment or /farmer/bookings)
$target_php = realpath($root_dir . $path . '.php');
if ($target_php && strpos($target_php, $root_dir) === 0 && is_file($target_php)) {
    chdir(dirname($target_php));
    require $target_php;
    exit();
}

// Fallback to index.php if not found
chdir(__DIR__ . '/..');
http_response_code(404);
require_once __DIR__ . '/../index.php';
