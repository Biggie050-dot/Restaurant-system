<?php
// Databaseverbinding is vereist.
require '../includes/db.php';

// Ontvang de POST-parameters. Deze worden verzonden vanuit kitchen.php en cashier.php.
// We gebruiken $_POST omdat het een eenvoudige form-data POST is, versturen naar server
$id = $_POST['order_id'];
$status = $_POST['status'];

// Gebruik pg_query_params om SQL-injectie te voorkomen. Deze functie escaped
// de parameters automatisch. De placeholders $1 en $2 worden vervangen door
// de waarden uit de array.
pg_query_params($conn, // dit doet de update van de order status in de database, bv: van 'ready' naar 'betaald'
    "UPDATE orders SET status = $1 WHERE id = $2", //parameter array 
    [$status, $id]
);

// Stuur een succesmelding terug. De clients (kitchen/cashier) verwachten
// een JSON-object met 'success': true.
echo json_encode(["success" => true]);