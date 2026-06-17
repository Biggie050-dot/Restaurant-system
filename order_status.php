<?php
// Start de sessie zodat gecontroleerd kan worden of de gebruiker is ingelogd
session_start();

// Alleen klanten mogen deze pagina bekijken
// Als de gebruiker niet is ingelogd of geen klant is, wordt die teruggestuurd naar login.php
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "customer") {
    header("Location: login.php");
    exit();
}

// Verbind met de database
include "includes/db.php";

// Variabele waarin de gevonden bestelling wordt opgeslagen
$order = null;

// Variabele voor foutmeldingen
$error = "";

// Controleer of het formulier is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Haal het ingevulde bestelnummer op
    $order_id = $_POST["order_id"];

    // Zoek de bestelling in de database op basis van het bestelnummer
    $sql = "SELECT * FROM orders WHERE id = $order_id";
    $result = pg_query($conn, $sql);

    // Controleer of er precies één bestelling is gevonden
    if (pg_num_rows($result) == 1) {

        // Haal de gegevens van de bestelling op
        $order = pg_fetch_assoc($result);

    } else {
        // Toon een foutmelding als de bestelling niet bestaat
        $error = "Geen bestelling gevonden met dit nummer.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bestelstatus</title>

    <!-- Koppeling naar het algemene CSS-bestand -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Algemene container van de pagina -->
<div class="page-container">

    <!-- Bovenste gedeelte van de pagina -->
    <div class="page-header">
        <h1>Bestelstatus</h1>

        <!-- Link terug naar het menu -->
        <a href="customer.php" class="logout-button">Terug naar menu</a>
    </div>

    <!-- Box waarin de klant zijn bestelnummer kan invullen -->
    <div class="cart-box">

        <!-- Formulier om de status van een bestelling op te zoeken -->
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

        <!-- Toon foutmelding als er geen bestelling is gevonden -->
        <?php if ($error != "") { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <!-- Toon bestelling als deze gevonden is -->
        <?php if ($order) { ?>
            <h2>Bestelling #<?php echo $order["id"]; ?></h2>

            <!-- Toon het tafelnummer van de bestelling -->
            <p>Tafelnummer: <?php echo $order["table_number"]; ?></p>

            <!-- Toon de huidige status van de bestelling -->
            <p class="status-text">
                <strong>Status:</strong> 
                <?php echo $order["status"]; ?>
            </p>    
        <?php } ?>
    </div>

</div>

</body>
</html>