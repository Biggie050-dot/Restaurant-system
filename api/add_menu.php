<?php
require '../includes/db.php';

header('Content-Type: application/json');

$name = trim($_POST['name'] ?? '');
$price = $_POST['price'] ?? '';
$category = trim($_POST['category'] ?? '');
$selectedImage = trim($_POST['image_path'] ?? '');
$imagePath = null;

if ($name === '' || $price === '' || $category === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Naam, prijs en categorie zijn verplicht."]);
    exit();
}

if ($selectedImage !== '') {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extension = strtolower(pathinfo($selectedImage, PATHINFO_EXTENSION));

    if (substr($selectedImage, 0, 7) !== "foto's/" || !in_array($extension, $allowedExtensions, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ongeldige foto gekozen."]);
        exit();
    }

    $fileName = basename($selectedImage);
    $realPhotoDir = realpath(__DIR__ . "/../foto's/");
    $realImagePath = realpath(__DIR__ . "/../foto's/" . $fileName);

    if ($realPhotoDir === false || $realImagePath === false || strpos($realImagePath, $realPhotoDir) !== 0 || !is_file($realImagePath)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "De gekozen foto bestaat niet in de map foto's."]);
        exit();
    }

    $imagePath = "foto's/" . $fileName;
}

$result = pg_query_params($conn,
    "INSERT INTO menu_items (name, price, category, image_path, is_active) VALUES ($1, $2, $3, $4, true)",
    [$name, $price, $category, $imagePath]
);

if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Gerecht kon niet worden toegevoegd."]);
    exit();
}

echo json_encode(["success" => true]);
