<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "cashier") {
    header("Location: login.php");
    exit();
}

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

    <div id="orders" class="orders-grid"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function formatMoney(value) {
    return parseFloat(value || 0).toFixed(2);
}

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

function loadOrders() {
    $.get("api/get_orders.php", function(data) {
        let orders = typeof data === "string" ? JSON.parse(data) : data;
        let html = "";

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

                    <button class="ready-btn" onclick="pay(${o.id})">Afrekenen</button>
                </div>
            `;
        });

        $("#orders").html(html);
    });
}

function pay(id) {
    $.post("api/update_status.php", { order_id: id, status: "betaald" }, function() {
        loadOrders();
    });
}

setInterval(loadOrders, 2000);
loadOrders();
</script>
</body>
</html>
