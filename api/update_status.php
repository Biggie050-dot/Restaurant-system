<?php
require '../includes/db.php';

$id = $_POST['order_id'];
$status = $_POST['status'];

pg_query_params($conn,
    "UPDATE orders SET status = $1 WHERE id = $2",
    [$status, $id]
);

echo json_encode(["success" => true]);
