<?php
// Verbind met de database
require '../includes/db.php';

// Geef aan dat deze API JSON terugstuurt
header('Content-Type: application/json');

// Haal alle actieve menu-items op uit de database
// COALESCE zorgt ervoor dat oude producten zonder is_active alsnog zichtbaar blijven
$result = pg_query($conn, "
    SELECT id, name, price, category, image_path
    FROM menu_items
    WHERE COALESCE(is_active, true) = true
    ORDER BY id ASC
");

// Zet alle gevonden producten om naar een array
$items = pg_fetch_all($result);

// Stuur de producten terug als JSON
// Als er geen producten zijn, stuur dan een lege array terug
echo json_encode($items ?: []);