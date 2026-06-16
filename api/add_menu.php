<?php
require '../includes/db.php';

header('Content-Type: application/json');

$name = trim($_POST['name'] ?? '');
$price = $_POST['price'] ?? '';
$category = trim($_POST['category'] ?? '');
$image_path = null;

if ($name === '' || $price === '' || $category === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Naam, prijs en categorie zijn verplicht."]);
    exit();
}

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Afbeelding uploaden is mislukt."]);
        exit();
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Afbeelding mag maximaal 5 MB zijn."]);
        exit();
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif'
    ];

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
    finfo_close($fileInfo);

    if (!array_key_exists($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Alleen JPG, PNG, WEBP of GIF afbeeldingen zijn toegestaan."]);
        exit();
    }

    $uploadDir = __DIR__ . '/../uploads/menu/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = $allowedTypes[$mimeType];
    $fileName = uniqid('product_', true) . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Afbeelding kon niet worden opgeslagen."]);
        exit();
    }

    $image_path = 'uploads/menu/' . $fileName;
}

$result = pg_query_params($conn,
    "INSERT INTO menu_items (name, price, category, image_path, is_active) VALUES ($1, $2, $3, $4, true)",
    [$name, $price, $category, $image_path]
);

if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Gerecht kon niet worden toegevoegd."]);
    exit();
}

echo json_encode(["success" => true]);