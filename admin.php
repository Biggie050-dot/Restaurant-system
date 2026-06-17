<?php
// Start de PHP-sessie om de ingelogde gebruiker te kunnen identificeren.
session_start();

// Controleer of de gebruiker is ingelogd en de rol 'admin' heeft.
// Zonder deze check zou iedereen deze pagina kunnen bekijken.
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    // Als niet admin, stuur door naar de inlogpagina.
    header("Location: login.php");
    exit(); // Stop de uitvoering om te voorkomen dat de rest van de pagina wordt getoond.
}

// Voeg de databaseverbinding toe (bevat $conn voor PostgreSQL).
// Deze wordt hier niet direct gebruikt, maar is wel nodig voor eventuele uitbreidingen.
include "includes/db.php";
?>
<!DOCTYPE html>
<!-- HTML5-doctype,  -->
<html lang="nl">
<head>
    <!-- UTF-8 is de standaard tekencodering  -->
    <meta charset="UTF-8">
    <!-- Paginatitel, zichtbaar in de browsertab -->
    <title>Admin Dashboard</title>
    <!-- Extern CSS-bestand voor de vormgeving; zo houden we de styling gescheiden van de structuur -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<!-- Hoofdcontainer voor de lay-out -->
<div class="page-container">
    <!-- Kopblok met titel en uitlogknop (consistent met andere dashboards) -->
    <div class="page-header">
        <div>
            <!-- Eyebrow (subtitel) voor extra context -->
            <p class="eyebrow">Beheer</p>
            <h1>Admin Dashboard</h1>
        </div>
        <!-- Link naar logout.php die de sessie vernietigt en doorverwijst naar login -->
        <a href="logout.php" class="logout-button">Uitloggen</a>
    </div>

    <!-- Grid-indeling voor de twee hoofdonderdelen: formulier en overzicht -->
    <div class="dashboard-grid">
        <!-- Sectie 1: Formulier om een nieuw gerecht toe te voegen -->
        <section class="panel-card">
            <h2>Gerecht toevoegen</h2>
            <!-- Uitleg dat je een foto kiest uit de bestaande map (geen upload meer) -->
            <p class="muted-text">Kies een foto uit de map <strong>foto's</strong> en voeg een product toe.</p>

            <!-- Formulier voor toevoegen. Geen enctype meer nodig omdat we geen bestand uploaden.
                 De gegevens worden via AJAX verstuurd met serialize(). -->
            <form id="addItem" class="stacked-form">
                <!-- Label voor de naam, gekoppeld aan input via 'for' -->
                <label class="form-label" for="name">Naam gerecht</label>
                <!-- Tekstveld voor de naam, verplicht -->
                <input type="text" id="name" name="name" placeholder="Bijv. Pizza Margherita" required>

                <label class="form-label" for="price">Prijs</label>
                <!-- Number-veld met stapgrootte 0.01 voor eurocenten, verplicht -->
                <input type="number" id="price" name="price" placeholder="Bijv. 12.50" step="0.01" min="0" required>

                <label class="form-label" for="category">Categorie</label>
                <!-- Dropdown voor categorie; de opties zijn hardcoded (komen niet uit de database) -->
                <select id="category" name="category" required>
                    <option value="">Kies categorie</option>
                    <option value="Pizza">Pizza</option>
                    <option value="Burger">Burger</option>
                    <option value="Pasta">Pasta</option>
                    <option value="Dranken">Dranken</option>
                    <option value="Dessert">Dessert</option>
                </select>

                <label class="form-label" for="imageSelect">Productfoto</label>
                <!-- Dropdown met afbeeldingen uit de map 'foto's'; wordt gevuld door JavaScript -->
                <select id="imageSelect" name="image_path">
                    <option value="">Geen foto</option>
                    <!-- Andere opties worden via JS toegevoegd -->
                </select>

                <!-- Voorbeeld van de geselecteerde afbeelding -->
                <div id="imagePreview" class="selected-image-preview">
                    Geen foto geselecteerd
                </div>

                <!-- Verzendknop -->
                <button type="submit">Toevoegen</button>
            </form>
        </section>

        <!-- Sectie 2: Overzicht van alle actieve menu-items -->
        <section class="panel-card wide-card">
            <div class="section-heading">
                <div>
                    <h2>Menu Items</h2>
                    <p class="muted-text">Overzicht van alle actieve gerechten.</p>
                </div>
            </div>
            <!-- Container waar de menu-items dynamisch worden ingeladen als kaarten (grid) -->
            <div id="menu" class="admin-products-grid"></div>
        </section>
    </div>
</div>

<!-- jQuery library wordt geladen; nodig voor AJAX en   jquery is dom staat --> 
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> 
<script>
// --- Functie: escapeHtml ---
// Doel: voorkomt XSS-aanvallen door speciale karakters om te zetten naar HTML-entiteiten.

function escapeHtml(value) { // ontsmetten van de input zodat er geen code kan worden uitgevoerd in de browser.
    // Zet de waarde om naar een string (of lege string bij null/undefined) zorgt er ook voor dat <, >, or " er niet voor zorgt dat het crashed
    return String(value || "") 
        .replace(/&/g, "&amp;")   // & wordt &amp;
        .replace(/</g, "&lt;")    // < wordt &lt;
        .replace(/>/g, "&gt;")    // > wordt &gt;
        .replace(/"/g, "&quot;")  // " wordt &quot;
        .replace(/'/g, "&#039;"); // ' wordt &#039;
}

// --- Functie: loadImages ---
// Doel: haalt de lijst met beschikbare afbeeldingen op uit de map 'foto's'
// via de API en vult daarmee de dropdown (#imageSelect).
function loadImages() {
    // AJAX GET-request naar api/list_images.php, verwacht een JSON-array.
    $.get("api/list_images.php", function(images) {
        // Begin met de lege optie (geen foto)
        let options = '<option value="">Geen foto</option>';

        // Als er afbeeldingen zijn, voeg dan voor elke afbeelding een <option> toe.
        if (images && images.length > 0) {
            images.forEach(img => {
                // Gebruik escapeHtml om de bestandsnaam en het pad veilig te tonen.
                options += `<option value="${escapeHtml(img.path)}">${escapeHtml(img.name)}</option>`; // escapehtml is nodig voor speciale tekens te vermijden 
            });
        }

        // Vervang de inhoud van de dropdown met de gegenereerde opties.
        $("#imageSelect").html(options);
        // Werk de preview bij (toon de standaardwaarde of 'Geen foto').
        updateImagePreview();
    }, "json");
}

// --- Functie: updateImagePreview ---
// Doel: toont een voorbeeld van de geselecteerde afbeelding in de preview-div.
function updateImagePreview() {
    // Lees de gekozen waarde uit de dropdown.
    let path = $("#imageSelect").val();

    // Als er geen pad is geselecteerd, toon dan 'Geen foto geselecteerd'.
    if (!path) {
        $("#imagePreview")
            .html("Geen foto geselecteerd")
            .removeClass("has-image"); // CSS-klasse voor eventuele stijl verwijderen
        return;
    }

    // Anders: toon de afbeelding in een <img>-tag en voeg de CSS-klasse 'has-image' toe.
    $("#imagePreview")
        .addClass("has-image")
        .html(`<img src="${escapeHtml(path)}" alt="Geselecteerde productfoto">`);
}

// --- Event: bij wijziging van de dropdown ---
// Roep updateImagePreview aan zodat de preview direct bijwerkt.
$("#imageSelect").on("change", updateImagePreview);

// --- Formulier voor toevoegen ---
// Vang het submit-event af om een AJAX-request te doen (geen page reload).
$("#addItem").submit(function(e){
    e.preventDefault(); // Voorkom de standaard POST-actie.

    // Stuur een AJAX POST-request naar api/add_menu.php. post staat voor het versturen van data naar de server.
    // In plaats van FormData gebruiken we nu serialize() omdat we geen bestand uploaden.
    // serialize() zet de formuliervelden om naar een query string.
    $.ajax({
        url: "api/add_menu.php",
        type: "POST",
        data: $(this).serialize(), // Verstuur alle velden (name, price, category, image_path)
        dataType: "json",          // Verwacht JSON-antwoord.
        success: function(response) {
            if (response.success) {
                alert("Gerecht toegevoegd!");   // Feedback aan de admin.
                $("#addItem")[0].reset();       // Maak het formulier leeg.
                updateImagePreview();           // Zet de preview terug naar 'Geen foto'.
                loadMenu();                     // Herlaad de menulijst.
            } else {
                alert(response.message || "Gerecht kon niet worden toegevoegd.");
            }
        },
        error: function() {
            // Algemene foutafhandeling bij netwerk- of serverproblemen.
            alert("Gerecht kon niet worden toegevoegd.");
        }
    });
});

// --- Functie: loadMenu ---
// Doel: haalt de actieve menu-items op via api/get_menu.php en toont ze als kaarten.
function loadMenu() {
    $.get("api/get_menu.php", function(data){
        // Zet de response om naar een array (als het een string is).
        let items = typeof data === "string" ? JSON.parse(data) : data;
        let html = "";

        // Als er geen items zijn, toon een melding.
        if (!items || items.length === 0) {
            $("#menu").html('<div class="empty-state">Nog geen menu-items gevonden.</div>');
            return;
        }

        // Loop door elk item en bouw een HTML-card.
        items.forEach(i => {
            // Bepaal de afbeelding: als die bestaat, toon <img>, anders een placeholder.
            let image = i.image_path
                ? `<img src="${escapeHtml(i.image_path)}" alt="${escapeHtml(i.name)}" class="admin-product-image">`
                : `<div class="admin-product-image admin-product-placeholder">Geen foto</div>`;

            // Bouw de kaart met escapeHtml voor veiligheid.
            html += `
                <article class="admin-product-card">
                    ${image}
                    <div class="admin-product-content">
                        <span class="product-category">${escapeHtml(i.category || "Geen categorie")}</span>
                        <h3>${escapeHtml(i.name)}</h3>
                        <strong>€${parseFloat(i.price).toFixed(2)}</strong>
                    </div>
                    <!-- Verwijderknop met data-attributen voor id en naam -->
                    <button class="delete-product" data-id="${i.id}" data-name="${escapeHtml(i.name)}">Verwijderen</button>
                </article>
            `;
        });

        // Plaats de HTML in de container.
        $("#menu").html(html);
    });
}

// --- Event: verwijderen van een product (soft delete)  soft delete markeert het als inactief in plaats van echt verwijderen uit de database ---
// Gebruik event-delegatie zodat ook dynamisch toegevoegde knoppen werken.
$(document).on("click", ".delete-product", function(){
    let id = $(this).data("id");
    let name = $(this).data("name");

    // Vraag bevestiging voordat je verwijdert.
    if (!confirm(`Weet je zeker dat je '${name}' wilt verwijderen?`)) {
        return;
    }

    // POST naar api/delete_menu.php met het id.
    $.post("api/delete_menu.php", { id: id }, function(response){
        if (response.success) {
            loadMenu(); // Herlaad de lijst (het item is nu 'is_active=false').
        } else {
            alert(response.message || "Product kon niet worden verwijderd.");
        }
    }, "json");
});

// --- Initialisatie bij het laden van de pagina ---
loadImages(); // Laad de afbeeldingenlijst in de dropdown.
loadMenu();   // Laad de menu-items.
</script>
</body>
</html>