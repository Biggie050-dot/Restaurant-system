<?php
// Verbind met de database
include "../includes/db.php";

// Geef aan dat deze API JSON terugstuurt
header("Content-Type: application/json");

// Lees de JSON-data die vanuit JavaScript/AJAX wordt verstuurd
$data = json_decode(file_get_contents("php://input"), true);

// Haal het tafelnummer en de gekozen producten uit de ontvangen data
$table_number = $data["table_number"];
$items = $data["items"];

// Voeg een nieuwe bestelling toe aan de tabel orders
// De bestelling krijgt als beginstatus 'in de wachtrij'
// RETURNING id geeft direct het nieuwe bestelnummer terug
$order_sql = "INSERT INTO orders (table_number, status)
              VALUES ($table_number, 'in de wachtrij')
              RETURNING id";

// Voer de query uit
$order_result = pg_query($conn, $order_sql);

// Controleer of het opslaan van de bestelling gelukt is
if (!$order_result) {
    echo json_encode([
        "success" => false,
        "message" => "Bestelling kon niet worden opgeslagen."
    ]);
    exit();
}

// Haal het aangemaakte bestelnummer op
$order = pg_fetch_assoc($order_result);
$order_id = $order["id"];

// Voeg alle producten uit het winkelmandje toe aan de tabel order_items
foreach ($items as $item) {

    // Product-id uit menu_items
    $menu_item_id = $item["id"];

    // Aantal keer dat het product is besteld
    $quantity = $item["quantity"];

    // Voeg één bestelregel toe aan de database
    $item_sql = "
        INSERT INTO order_items 
        (order_id, menu_item_id, quantity)
        VALUES 
        ($order_id, $menu_item_id, $quantity)
    ";

    // Voer de query uit
    pg_query($conn, $item_sql);
}

// Stuur een succesvolle response terug naar JavaScript
// Het order_id wordt gebruikt als bestelnummer voor de klant
echo json_encode([
    "success" => true,
    "order_id" => $order_id
]);
?>