<?php
require '../includes/db.php';

$result = pg_query($conn, "SELECT * FROM orders WHERE status != 'paid' ORDER BY created_at ASC");

$orders = pg_fetch_all($result);

echo json_encode($orders);
