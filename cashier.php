<?php
// Sessie starten om gebruiker te authenticeren.
session_start();

// Rolcontrole: alleen gebruikers met de rol 'cashier' mogen deze pagina zien.
// Dit voorkomt dat bijvoorbeeld een klant per ongeluk de kassa-pagina opent.
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "cashier") {
    header("Location: login.php");
    exit();
}

// Databaseverbinding inladen voor eventueel gebruik (hoewel de API's de database
// zelf benaderen, hebben we deze hier nodig voor consistentie).
include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Kassa Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page-container">
    <div class="page-header">
        <div>
            <p class="eyebrow">Kassa</p>
            <h1>Kassa Dashboard</h1>
        </div>
        <a href="logout.php" class="logout-button">Uitloggen</a>
    </div>

    <!-- Container voor de te betalen bestellingen -->
    <div id="orders" class="orders-grid"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// Zelfde formatMoney-functie als in de keuken; consistentie is belangrijk.
function formatMoney(value) {
    return parseFloat(value || 0).toFixed(2);
}

// renderItems is identiek aan die in kitchen.php, omdat de opmaak van
// productregels hetzelfde is. 
function renderItems(items) {
    if (typeof items === "string") {
        items = JSON.parse(items || "[]");
    }
    if (!items || items.length === 0) {
        return '<p class="muted-text">Geen producten gevonden.</p>';
    }
    return items.map(item => `
        <div class="order-line">
            <span>${item.quantity}x ${item.name}</span>
            <strong>€${formatMoney(item.subtotal)}</strong>
        </div>
    `).join("");
}

// Hoofdfunctie om orders te laden.
function loadOrders() {
    // We gebruiken dezelfde API als de keuken: api/get_orders.php.
    // Dit is efficiënt omdat beide dashboards dezelfde gegevens nodig hebben,
    // maar elk filtert anders (keuken toont alles, kassa filtert op 'ready').
    $.get("api/get_orders.php", function(data) {
        let orders = typeof data === "string" ? JSON.parse(data) : data;
        let html = "";

        // **Client-side filtering**: we tonen alleen orders met status 'ready' of 'gereed'.
        // 
        // 
        let payableOrders = (orders || []).filter(o => o.status === "ready" || o.status === "gereed");

        if (payableOrders.length === 0) {
            $("#orders").html(`
                <div class="empty-state full-width">
                    <h3>Geen bestellingen om af te rekenen</h3>
                    <p>Bestellingen verschijnen hier zodra de keuken ze gereed meldt.</p>
                </div>
            `);
            return;
        }

        // Bouw voor elke betaalbare order een kaart.
        payableOrders.forEach(o => {
            html += `
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <p class="eyebrow">Tafel ${o.table_number}</p>
                            <h3>Bestelling #${o.id}</h3>
                        </div>
                        <span class="status-pill status-ready">${o.status}</span>
                    </div>

                    <div class="order-items">
                        ${renderItems(o.items)}
                    </div>

                    <div class="order-total">
                        <span>Totaal</span>
                        <strong>€${formatMoney(o.total)}</strong>
                    </div>

                    <!-- Afrekenknop: roept pay() aan met het order-id -->
                    <button class="ready-btn" onclick="pay(${o.id})">Afrekenen</button>
                </div>
            `;
        });

        $("#orders").html(html);
    });
}

// pay() wordt aangeroepen wanneer de kassier op "Afrekenen" klikt.
function pay(id) {
    // We sturen een POST naar update_status.php om de status op 'betaald' te zetten. 
    // Zodra de status 'betaald' is, wordt de order niet meer opgehaald door
    // get_orders.php (want die filtert 'betaald' uit). Hierdoor verdwijnt de
    // order automatisch uit de kassaweergave.
    $.post("api/update_status.php", { order_id: id, status: "betaald" }, function() {
        loadOrders(); // Herlaad de lijst om de betaalde order te verwijderen.
    });
}

// Automatisch verversen om nieuwe 'ready'-orders te tonen.
setInterval(loadOrders, 2000);
loadOrders();
</script>
</body>
</html>