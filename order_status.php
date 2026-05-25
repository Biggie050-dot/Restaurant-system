<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "customer") {
    header("Location: login.php");
    exit();
}

include "includes/db.php";

$order = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST["order_id"];

    $sql = "SELECT * FROM orders WHERE id = $order_id";
    $result = pg_query($conn, $sql);

    if (pg_num_rows($result) == 1) {
        $order = pg_fetch_assoc($result);
    } else {
        $error = "Geen bestelling gevonden met dit nummer.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bestelstatus</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-container">

    <div class="page-header">
        <h1>Bestelstatus</h1>
        <a href="customer.php" class="logout-button">Terug naar menu</a>
    </div>

    <div class="cart-box">
        <form method="POST">
            <input 
                type="number" 
                name="order_id" 
                placeholder="Vul je bestelnummer in"
                required
            >

            <button type="submit">
                Status bekijken
            </button>
        </form>

        <?php if ($error != "") { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <?php if ($order) { ?>
            <h2>Bestelling #<?php echo $order["id"]; ?></h2>
            <p>Tafelnummer: <?php echo $order["table_number"]; ?></p>

            <p class="status-text">
            <strong>Status:</strong> 
            <?php echo $order["status"]; ?>
            </p>    
        <?php } ?>
    </div>

</div>

</body>
</html>