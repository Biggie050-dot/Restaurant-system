<?php
require '../includes/db.php';

$name = $_POST['name'];
$price = $_POST['price'];

pg_query_params($conn,
    "INSERT INTO menu_items (name, price) VALUES ($1, $2)",
    [$name, $price]
);

echo json_encode(["success" => true]);
