<?php
// Verbind met de database
require '../includes/db.php';

// Geef aan dat deze API JSON terugstuurt
header('Content-Type: application/json');

// Haal het product-id op uit de POST-data
// Als er geen id is meegestuurd, wordt de waarde null
$id = $_POST['id'] ?? null;

// Controleer of er wel een product-id is meegegeven
if (!$id) {
    // Stuur HTTP-status 400 terug: fout verzoek
    http_response_code(400);

    // Geef een duidelijke foutmelding terug als JSON
    echo json_encode([
        "success" => false,
        "message" => "Geen product gekozen om te verwijderen."
    ]);

    // Stop de verdere uitvoering van het script
    exit();
}

// Soft delete:
// Het product wordt niet echt uit de database verwijderd.
// In plaats daarvan wordt is_active op false gezet.
// Hierdoor verdwijnt het product uit het menu,
// maar oude bestellingen met dit product blijven bewaard.
$result = pg_query_params(
    $conn,
    "UPDATE menu_items SET is_active = false WHERE id = $1",
    [$id]
);

// Controleer of de query goed is uitgevoerd
if (!$result) {
    // Stuur HTTP-status 500 terug: serverfout
    http_response_code(500);

    // Geef een foutmelding terug als JSON
    echo json_encode([
        "success" => false,
        "message" => "Product kon niet worden verwijderd."
    ]);

    // Stop het script
    exit();
}

// Als alles goed ging, stuur success true terug
echo json_encode([
    "success" => true
]);