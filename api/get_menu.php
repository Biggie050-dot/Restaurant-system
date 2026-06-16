<?php
require '../includes/db.php';

header('Content-Type: application/json');

$result = pg_query($conn, "
    SELECT id, name, price, category, image_path
    FROM menu_items
    WHERE COALESCE(is_active, true) = true
    ORDER BY id ASC
");

$items = pg_fetch_all($result);

echo json_encode($items ?: []);