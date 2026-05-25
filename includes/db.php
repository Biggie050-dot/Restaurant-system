<?php

$host = "localhost";
$port = "5432";
$dbname = "Restaurant_System";
$user = "postgres";
$password = "Ensar2006";

$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

if (!$conn) {
    die("Verbinding mislukt");
}

?>