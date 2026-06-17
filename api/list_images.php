<?php
header('Content-Type: application/json');

$photoDir = __DIR__ . "/../foto's/";
$photoWebPath = "foto's/";
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$images = [];

if (is_dir($photoDir)) {
    $files = scandir($photoDir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $fullPath = $photoDir . $file;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (is_file($fullPath) && in_array($extension, $allowedExtensions, true)) {
            $images[] = [
                'name' => $file,
                'path' => $photoWebPath . $file
            ];
        }
    }
}

usort($images, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

echo json_encode($images);
