<?php
require '../includes/db.php';

header('Content-Type: application/json');

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

$result = pg_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Bestellingen konden niet worden opgehaald.'
    ]);
    exit();
}

$orders = pg_fetch_all($result);

echo json_encode($orders ?: []);
