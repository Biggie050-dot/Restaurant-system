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
            <p class="muted-text">Kies een foto uit de map <strong>foto's</strong> en voeg een product toe.</p>

            <form id="addItem" class="stacked-form">
                <label class="form-label" for="name">Naam gerecht</label>
                <input type="text" id="name" name="name" placeholder="Bijv. Pizza Margherita" required>

                <label class="form-label" for="price">Prijs</label>
                <input type="number" id="price" name="price" placeholder="Bijv. 12.50" step="0.01" min="0" required>

                <label class="form-label" for="category">Categorie</label>
                <select id="category" name="category" required>
                    <option value="">Kies categorie</option>
                    <option value="Pizza">Pizza</option>
                    <option value="Burger">Burger</option>
                    <option value="Pasta">Pasta</option>
                    <option value="Dranken">Dranken</option>
                    <option value="Dessert">Dessert</option>
                </select>

                <label class="form-label" for="imageSelect">Productfoto</label>
                <select id="imageSelect" name="image_path">
                    <option value="">Geen foto</option>
                </select>

                <div id="imagePreview" class="selected-image-preview">
                    Geen foto geselecteerd
                </div>

                <button type="submit">Toevoegen</button>
            </form>
        </section>

        <section class="panel-card wide-card">
            <div class="section-heading">
                <div>
                    <h2>Menu Items</h2>
                    <p class="muted-text">Overzicht van alle actieve gerechten.</p>
                </div>
            </div>
            <div id="menu" class="admin-products-grid"></div>
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function escapeHtml(value) {
    return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function loadImages() {
    $.get("api/list_images.php", function(images) {
        let options = '<option value="">Geen foto</option>';

        if (images && images.length > 0) {
            images.forEach(img => {
                options += `<option value="${escapeHtml(img.path)}">${escapeHtml(img.name)}</option>`;
            });
        }

        $("#imageSelect").html(options);
        updateImagePreview();
    }, "json");
}

function updateImagePreview() {
    let path = $("#imageSelect").val();

    if (!path) {
        $("#imagePreview").html("Geen foto geselecteerd").removeClass("has-image");
        return;
    }

    $("#imagePreview")
        .addClass("has-image")
        .html(`<img src="${escapeHtml(path)}" alt="Geselecteerde productfoto">`);
}

$("#imageSelect").on("change", updateImagePreview);

$("#addItem").submit(function(e){
    e.preventDefault();

    $.ajax({
        url: "api/add_menu.php",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            if (response.success) {
                alert("Gerecht toegevoegd!");
                $("#addItem")[0].reset();
                updateImagePreview();
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
                ? `<img src="${escapeHtml(i.image_path)}" alt="${escapeHtml(i.name)}" class="admin-product-image">`
                : `<div class="admin-product-image admin-product-placeholder">Geen foto</div>`;

            html += `
                <article class="admin-product-card">
                    ${image}
                    <div class="admin-product-content">
                        <span class="product-category">${escapeHtml(i.category || "Geen categorie")}</span>
                        <h3>${escapeHtml(i.name)}</h3>
                        <strong>€${parseFloat(i.price).toFixed(2)}</strong>
                    </div>
                    <button class="delete-product" data-id="${i.id}" data-name="${escapeHtml(i.name)}">Verwijderen</button>
                </article>
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

loadImages();
loadMenu();
</script>
</body>
</html>
