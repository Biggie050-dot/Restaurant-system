<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "customer") {
    header("Location: login.php");
    exit();
}

include "includes/db.php";

$categories = ["Pizza", "Burger", "Pasta", "Dranken", "Dessert"];
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Klantpagina</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-container">

    <div class="page-header">
        <h1>Restaurant Menu</h1>
        <a href="logout.php" class="logout-button">Uitloggen</a>
        <a href="order_status.php" class="logout-button">Bestelstatus bekijken</a>
    </div>

    <?php foreach ($categories as $category) { ?>

        <h2 class="category-title"><?php echo $category; ?></h2>

        <div class="menu-container">

            <?php
            $sql = "SELECT * FROM menu_items WHERE category = '$category' AND COALESCE(is_active, true) = true";
            $result = pg_query($conn, $sql);

            while ($item = pg_fetch_assoc($result)) {
            ?>

                <div class="menu-item">
                    <div class="product-image-box">
                        <?php if (!empty($item["image_path"])) { ?>
                            <img
                                src="<?php echo htmlspecialchars($item["image_path"]); ?>"
                                alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                class="product-image"
                            >
                        <?php } else { ?>
                            <div class="product-image-placeholder">Geen foto</div>
                        <?php } ?>
                    </div>

                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($item["name"]); ?></h3>

                        <p class="price">
                            €<?php echo number_format($item["price"], 2, ',', '.'); ?>
                        </p>
                    </div>

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

    <div class="cart-box">
        <h2>Winkelmandje</h2>

        <div id="cart-items">
            <p>Nog geen producten gekozen.</p>
        </div>

        <p class="cart-total">
            Totaal: €<span id="cart-total">0.00</span>
        </p>

        <input 
            type="number" 
            id="table-number" 
            placeholder="Tafelnummer"
            min="1"
        >

        <button id="place-order">
            Bestelling plaatsen
        </button>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/app.js"></script>

</body>
</html>