<?php
require '../includes/db.php';

header('Content-Type: application/json');

$name = $_POST['name'];
$price = $_POST['price'];
$category = $_POST['category'];

$result = pg_query_params($conn,
    "INSERT INTO menu_items (name, price, category) VALUES ($1, $2, $3)",
    [$name, $price, $category]
);

if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Gerecht kon niet worden toegevoegd."]);
    exit();
}

echo json_encode(["success" => true]);
