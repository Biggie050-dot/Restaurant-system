<?php
require '../includes/db.php';

$result = pg_query($conn, "SELECT * FROM menu_items ORDER BY id ASC");
$items = pg_fetch_all($result);

echo json_encode($items);
