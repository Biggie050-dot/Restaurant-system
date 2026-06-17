<?php
// Start de sessie zodat we kunnen controleren wie is ingelogd
session_start();

// Alleen klanten mogen deze pagina bekijken
// Als iemand niet is ingelogd of geen klant is, wordt die teruggestuurd naar de loginpagina
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "customer") {
    header("Location: login.php");
    exit();
}

// Verbind met de database
include "includes/db.php";

// Categorieën die op het menu worden getoond
$categories = ["Pizza", "Burger", "Pasta", "Dranken", "Dessert"];
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Klantpagina</title>

    <!-- Koppeling naar het CSS-bestand voor de styling -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Algemene container van de klantpagina -->
<div class="page-container">

    <!-- Bovenste gedeelte van de pagina met titel en navigatieknoppen -->
    <div class="page-header">
        <h1>Restaurant Menu</h1>

        <!-- Knop om uit te loggen -->
        <a href="logout.php" class="logout-button">Uitloggen</a>

        <!-- Knop om de bestelstatus te bekijken -->
        <a href="order_status.php" class="logout-button">Bestelstatus bekijken</a>
    </div>

    <!-- Loop door alle categorieën heen -->
    <?php foreach ($categories as $category) { ?>

        <!-- Toon de naam van de categorie -->
        <h2 class="category-title"><?php echo $category; ?></h2>

        <!-- Container waarin de producten van deze categorie worden getoond -->
        <div class="menu-container">

            <?php
            // Haal alle actieve producten op uit de huidige categorie
            // COALESCE zorgt ervoor dat oude producten zonder is_active ook als actief worden gezien
            $sql = "SELECT * FROM menu_items WHERE category = '$category' AND COALESCE(is_active, true) = true";
            $result = pg_query($conn, $sql);

            // Loop door alle producten uit de database
            while ($item = pg_fetch_assoc($result)) {
            ?>

                <!-- Productkaart -->
                <div class="menu-item">

                    <!-- Afbeeldingsvak van het product -->
                    <div class="product-image-box">

                        <!-- Als er een foto is opgeslagen, toon deze foto -->
                        <?php if (!empty($item["image_path"])) { ?>
                            <img
                                src="<?php echo htmlspecialchars($item["image_path"]); ?>"
                                alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                class="product-image"
                            >

                        <!-- Als er geen foto is, toon een placeholder -->
                        <?php } else { ?>
                            <div class="product-image-placeholder">Geen foto</div>
                        <?php } ?>
                    </div>

                    <!-- Productinformatie -->
                    <div class="product-info">

                        <!-- Productnaam -->
                        <h3><?php echo htmlspecialchars($item["name"]); ?></h3>

                        <!-- Productprijs -->
                        <p class="price">
                            €<?php echo number_format($item["price"], 2, ',', '.'); ?>
                        </p>
                    </div>

                    <!-- Knop om product toe te voegen aan het winkelmandje -->
                    <!-- De data-attributen worden gebruikt door JavaScript -->
                    <button
                        class="add-to-cart"
                        data-id="<?php echo $item["id"]; ?>"
                        data-name="<?php echo htmlspecialchars($item["name"]); ?>"
                        data-price="<?php echo $item["price"]; ?>"
                    >
                        Toevoegen
                    </button>
                </div>

            <?php } ?>

        </div>

    <?php } ?>

    <!-- Winkelmandje -->
    <div class="cart-box">
        <h2>Winkelmandje</h2>

        <!-- Hier worden gekozen producten via JavaScript weergegeven -->
        <div id="cart-items">
            <p>Nog geen producten gekozen.</p>
        </div>

        <!-- Totaalbedrag van de bestelling -->
        <p class="cart-total">
            Totaal: €<span id="cart-total">0.00</span>
        </p>

        <!-- Invoer voor het tafelnummer -->
        <input 
            type="number" 
            id="table-number" 
            placeholder="Tafelnummer"
            min="1"
        >

        <!-- Knop om de bestelling te plaatsen -->
        <button id="place-order">
            Bestelling plaatsen
        </button>
    </div>

</div>

<!-- jQuery wordt gebruikt voor JavaScript-functionaliteit -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Eigen JavaScript-bestand voor winkelmandje en bestelling plaatsen -->
<script src="js/app.js"></script>

</body>
</html>