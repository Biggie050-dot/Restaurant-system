<?php
// Start de PHP-sessie. Dit is nodig om de ingelogde gebruiker te kunnen identificeren
// aan de hand van de sessievariabelen die bij login zijn vastgelegd.
session_start();

// Controleer of de gebruiker is ingelogd en de juiste rol ('kitchen') heeft.
// Zonder deze check zou iedereen deze pagina kunnen bekijken; dit is een
// beveiligingsmaatregel op basis van rolgebaseerde toegangscontrole (RBAC).
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "kitchen") {
    // Als niet aan de voorwaarde wordt voldaan, stuur de gebruiker dan terug naar
    // de inlogpagina. De functie header() stuurt een HTTP-redirect.
    // We gebruiken exit() om te voorkomen dat de rest van de pagina wordt uitgevoerd.
    header("Location: login.php");
    exit();
}

// Voeg het bestand met de databaseverbinding toe. Dit bestand bevat de
// variabele $conn (een PostgreSQL-verbindingsresource) die we nodig hebben
// voor database-operaties. Het wordt via include opgenomen, niet via require,
// omdat we bij een fout liever zelf een nette foutmelding tonen (hoewel we
// hier geen fang afhandelen, is include voldoende).
include "includes/db.php";
?>
<!DOCTYPE html>
<!-- HTML5-doctype, ingesteld op Nederlandse taal voor toegankelijkheid en SEO -->
<html lang="nl">
<head>
    <!-- UTF-8 is de standaard tekencodering voor moderne webapplicaties -->
    <meta charset="UTF-8">
    <!-- Paginatitel, zichtbaar in de browsertab en belangrijk voor SEO -->
    <title>Keuken Dashboard</title>
    <!-- Extern CSS-bestand voor de vormgeving. Door styling los te koppelen
         wordt de code onderhoudbaarder en kunnen we het uiterlijk centraal wijzigen. -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<!-- Hoofdcontainer; dient als wrapper voor de lay-out -->
<div class="page-container">
    <!-- Kopblok met titel en uitlogknop; dit is een consistent patroon op alle
         dashboards, wat de gebruikerservaring verbetert. -->
    <div class="page-header">
        <div>
            <!-- Eyebrow is een subtitel ter verduidelijking -->
            <p class="eyebrow">Keuken</p>
            <h1>Keuken Dashboard</h1>
        </div>
        <!-- Link naar logout.php die de sessie vernietigt en doorverwijst naar login -->
        <a href="logout.php" class="logout-button">Uitloggen</a>
    </div>

    <!-- Container waar de bestellingen dynamisch worden ingeladen.
         De id "orders" wordt gebruikt door jQuery om de HTML te vervangen. -->
    <div id="orders" class="orders-grid"></div>
</div>

<!-- jQuery wordt via een CDN ingeladen. We gebruiken jQuery vanwege zijn
     eenvoudige AJAX-API en DOM-manipulatie, wat cross-browser consistent werkt.
     Versie 3.7.1 is een stabiele release. -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// --- Functie: formatMoney ---
// Doel: een numerieke waarde opmaken met twee decimalen, zodat prijzen
// consistent worden weergegeven (bv. "5.00" i.p.v. "5").
function formatMoney(value) {
    // parseFloat converteert de waarde naar een getal; als de waarde
    // ongedefinieerd of geen getal is, gebruiken we 0 als fallback.
    // toFixed(2) rondt af op twee decimalen en retourneert een string.
    return parseFloat(value || 0).toFixed(2);
}

// --- Functie: renderItems ---
// Doel: de productregels van een bestelling omzetten naar HTML.
// Deze functie wordt zowel in kitchen.php als cashier.php gebruikt,
// wat duidt op herbruikbaarheid (hoewel de code gedupliceerd is).
function renderItems(items) {
    // Omdat de API JSON retourneert, kan 'items' een JSON-string zijn
    // (bijv. bij oude implementaties). We zorgen dat we altijd met een array werken.
    if (typeof items === "string") {
        items = JSON.parse(items || "[]");
    }

    // Als er geen items zijn, tonen we een neutrale melding.
    if (!items || items.length === 0) {
        return '<p class="muted-text">Geen producten gevonden.</p>';
    }

    // Gebruik Array.map om elk item om te zetten naar een HTML-string.
    // De backticks (template literals) maken het eenvoudig om variabelen in
    // de HTML te interpoleren. join('') voegt alle stukken samen zonder scheidingsteken.
    return items.map(item => `
        <div class="order-line">
            <span>${item.quantity}x ${item.name}</span>
            <strong>€${formatMoney(item.subtotal)}</strong>
        </div>
    `).join("");
}

// --- Functie: statusClass ---
// Doel: de CSS-klasse bepalen voor de status-pill, zodat we verschillende
// kleuren kunnen tonen (bijv. groen voor gereed, oranje voor in bereiding).
function statusClass(status) {
    if (status === "ready" || status === "gereed") return "status-ready";
    if (status === "preparing" || status === "in bereiding") return "status-preparing";
    return "status-waiting"; // standaard voor 'in de wachtrij'
}

// --- Functie: loadOrders ---
// Doel: de actieve bestellingen ophalen van de server en de DOM bijwerken.
// Deze functie wordt zowel bij het laden van de pagina als periodiek aangeroepen.
function loadOrders() {
    // jQuery's $.get stuurt een asynchrone HTTP GET-request naar de opgegeven URL.
    // Dit voorkomt dat de pagina herladen wordt, wat de gebruikerservaring
    // verbetert (single-page-achtig gedrag).
    $.get("api/get_orders.php", function(data) {
        // De callback ontvangt de response. Omdat de API JSON retourneert,
        // zetten we de data om naar een JavaScript-object als het nog een string is.
        let orders = typeof data === "string" ? JSON.parse(data) : data;
        let html = ""; // Hier bouwen we de uiteindelijke HTML voor alle orders.

        // Als er geen orders zijn, tonen we een duidelijke melding in de container.
        if (!orders || orders.length === 0) {
            $("#orders").html(`
                <div class="empty-state full-width">
                    <h3>Geen openstaande bestellingen</h3>
                    <p>Betaalde bestellingen verdwijnen automatisch uit dit overzicht.</p>
                </div>
            `);
            return; // Stop de uitvoering van de functie.
        }

        // Loop door elke order en bouw een HTML-card.
        orders.forEach(o => {
            html += `
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <!-- Eyebrow toont het tafelnummer -->
                            <p class="eyebrow">Tafel ${o.table_number}</p>
                            <h3>Bestelling #${o.id}</h3>
                        </div>
                        <!-- Status-pill met dynamische klasse -->
                        <span class="status-pill ${statusClass(o.status)}">${o.status}</span>
                    </div>

                    <div class="order-items">
                        ${renderItems(o.items)}
                    </div>

                    <div class="order-actions">
                        <!-- Knoppen om de status te wijzigen. De onclick-attributen
                             roepen de JavaScript-functie updateStatus aan met het
                             order-id en de gewenste nieuwe status. -->
                        <button onclick="updateStatus(${o.id}, 'preparing')">In bereiding</button>
                        <button onclick="updateStatus(${o.id}, 'ready')">Gereed</button>
                    </div>
                </div>
            `;
        });

        // Vervang de inhoud van de #orders-container met de gegenereerde HTML.
        $("#orders").html(html);
    });
}

// --- Functie: updateStatus ---
// Doel: de status van een specifieke order wijzigen via de API.
function updateStatus(id, status) {
    // $.post stuurt een POST-request met de gegevens als form-data.
    // Na een succesvolle response wordt de callback loadOrders uitgevoerd,
    // zodat de lijst automatisch wordt ververst zonder dat de gebruiker iets hoeft te doen.
    $.post("api/update_status.php", { order_id: id, status: status }, loadOrders);
}

// --- Periodiek herladen ---
// setInterval zorgt ervoor dat loadOrders elke 2000 milliseconden (2 seconden)
// wordt aangeroepen. Dit geeft een real-time effect: zodra een kok een status
// wijzigt, zien alle andere keukenmedewerkers dat binnen enkele seconden.
// We gebruiken een interval in plaats van WebSockets omdat dit eenvoudiger te
// implementeren is en voldoende is voor dit systeem met beperkte gelijktijdige gebruikers.
setInterval(loadOrders, 2000);

// Laad de bestellingen direct bij het laden van de pagina.
loadOrders();
</script>
</body>
</html>