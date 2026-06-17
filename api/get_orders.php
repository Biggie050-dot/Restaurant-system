<?php
// We gebruiken require omdat het bestand met databaseverbinding essentieel is
// voor de werking van deze API. Als het ontbreekt, moet de uitvoering stoppen.
require '../includes/db.php';

// Zet de Content-Type header naar JSON, zodat de client weet dat het antwoord
// JSON is en deze automatisch kan parsen.
header('Content-Type: application/json');

// 
// json_agg en json_build_object
// om geneste JSON te maken. Dit is efficiënter dan meerdere queries of
// handmatig samenvoegen in PHP, omdat de database al het werk doet.
$sql = "
    SELECT
        o.id,
        o.table_number,
        o.status,
        o.created_at,
        COALESCE(
            json_agg(
                json_build_object(
                    'name', mi.name,
                    'price', mi.price,
                    'quantity', oi.quantity,
                    'subtotal', (mi.price * oi.quantity)
                )
                ORDER BY mi.name 
            ) FILTER (WHERE oi.id IS NOT NULL),
            '[]'
        ) AS items,
        COALESCE(SUM(mi.price * oi.quantity), 0) AS total
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id
    WHERE o.status NOT IN ('betaald', 'paid')
    GROUP BY o.id, o.table_number, o.status, o.created_at
    ORDER BY o.created_at ASC
";

// Voer de query uit met pg_query. We gebruiken geen parameterbinding omdat
// er geen gebruikersinvoer in de query zit; de filter op status is hardcoded.
$result = pg_query($conn, $sql);

// Controleer of de query is gelukt. Zo niet, stuur een 500-fout.
if (!$result) {
    http_response_code(500); //500 error is gewoon een server fout
    echo json_encode([
        'success' => false,
        'message' => 'Bestellingen konden niet worden opgehaald.'
    ]);
    exit();
}

// pg_fetch_all haalt alle rijen op als een array van associatieve arrays.
$orders = pg_fetch_all($result); 

// Als er geen orders zijn, stuur dan een lege array (geen null).
echo json_encode($orders ?: []);