<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page-container">
    <div class="page-header">
        <div>
            <p class="eyebrow">Beheer</p>
            <h1>Admin Dashboard</h1>
        </div>
        <a href="logout.php" class="logout-button">Uitloggen</a>
    </div>

    <div class="dashboard-grid">
        <section class="panel-card">
            <h2>Gerecht toevoegen</h2>
            <p class="muted-text">Voeg snel een nieuw menu-item toe.</p>

            <form id="addItem" class="stacked-form">
                <input type="text" name="name" placeholder="Naam van gerecht" required>
                <input type="number" name="price" placeholder="Prijs" step="0.01" min="0" required>
                <select name="category" required>
                    <option value="">Kies categorie</option>
                    <option value="Pizza">Pizza</option>
                    <option value="Burger">Burger</option>
                    <option value="Pasta">Pasta</option>
                    <option value="Dranken">Dranken</option>
                    <option value="Dessert">Dessert</option>
                </select>
                <button type="submit">Toevoegen</button>
            </form>
        </section>

        <section class="panel-card wide-card">
            <div class="section-heading">
                <div>
                    <h2>Menu Items</h2>
                    <p class="muted-text">Overzicht van alle gerechten.</p>
                </div>
            </div>
            <div id="menu" class="menu-list"></div>
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$("#addItem").submit(function(e){
    e.preventDefault();

    $.post("api/add_menu.php", $(this).serialize(), function(){
        alert("Gerecht toegevoegd!");
        $("#addItem")[0].reset();
        loadMenu();
    });
});

function loadMenu() {
    $.get("api/get_menu.php", function(data){
        let items = typeof data === "string" ? JSON.parse(data) : data;
        let html = "";

        if (!items || items.length === 0) {
            $("#menu").html('<div class="empty-state">Nog geen menu-items gevonden.</div>');
            return;
        }

        items.forEach(i => {
            html += `
                <div class="list-row">
                    <span>${i.name}</span>
                    <span class="row-meta">${i.category || "Geen categorie"}</span>
                    <strong>€${parseFloat(i.price).toFixed(2)}</strong>
                </div>
            `;
        });

        $("#menu").html(html);
    });
}

loadMenu();
</script>
</body>
</html>
