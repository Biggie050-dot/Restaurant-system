<?php
require '../includes/db.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Geen product gekozen om te verwijderen."]);
    exit();
}

// Soft delete: het product verdwijnt uit het menu, maar oude bestellingen blijven bewaard.
$result = pg_query_params($conn,
    "UPDATE menu_items SET is_active = false WHERE id = $1",
    [$id]
);

if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Product kon niet worden verwijderd."]);
    exit();
}

echo json_encode(["success" => true]);
