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

            <form id="addItem" class="stacked-form" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Naam van gerecht" required>
                <input type="number" name="price" placeholder="Prijs" step="0.01" min="0" required>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
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

    let formData = new FormData(this);

    $.ajax({
        url: "api/add_menu.php",
        type: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert("Gerecht toegevoegd!");
                $("#addItem")[0].reset();
                loadMenu();
            } else {
                alert(response.message || "Gerecht kon niet worden toegevoegd.");
            }
        },
        error: function() {
            alert("Gerecht kon niet worden toegevoegd.");
        }
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
            let image = i.image_path
                ? `<img src="${i.image_path}" alt="${i.name}" class="admin-product-thumb">`
                : `<div class="admin-product-thumb placeholder-thumb">Geen foto</div>`;

            html += `
                <div class="list-row product-row">
                    ${image}
                    <div class="product-row-info">
                        <strong class="product-name">${i.name}</strong>
                        <span class="row-meta">${i.category || "Geen categorie"}</span>
                    </div>
                    <strong>€${parseFloat(i.price).toFixed(2)}</strong>
                    <button class="delete-product" data-id="${i.id}" data-name="${i.name}">Verwijderen</button>
                </div>
            `;
        });

        $("#menu").html(html);
    });
}

$(document).on("click", ".delete-product", function(){
    let id = $(this).data("id");
    let name = $(this).data("name");

    if (!confirm(`Weet je zeker dat je '${name}' wilt verwijderen?`)) {
        return;
    }

    $.post("api/delete_menu.php", { id: id }, function(response){
        if (response.success) {
            loadMenu();
        } else {
            alert(response.message || "Product kon niet worden verwijderd.");
        }
    }, "json");
});

loadMenu();
</script>
</body>
</html>