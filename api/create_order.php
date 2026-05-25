<?php
include "../includes/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$table_number = $data["table_number"];
$items = $data["items"];

// Bestelling toevoegen met beginstatus
$order_sql = "INSERT INTO orders (table_number, status)
              VALUES ($table_number, 'in de wachtrij')
              RETURNING id";

$order_result = pg_query($conn, $order_sql);

if (!$order_result) {
    echo json_encode([
        "success" => false,
        "message" => "Bestelling kon niet worden opgeslagen."
    ]);
    exit();
}

$order = pg_fetch_assoc($order_result);
$order_id = $order["id"];

// Producten toevoegen aan bestelling
foreach ($items as $item) {
    $menu_item_id = $item["id"];
    $quantity = $item["quantity"];

    $item_sql = "
        INSERT INTO order_items 
        (order_id, menu_item_id, quantity)
        VALUES 
        ($order_id, $menu_item_id, $quantity)
    ";

    pg_query($conn, $item_sql);
}

echo json_encode([
    "success" => true,
    "order_id" => $order_id
]);
?>