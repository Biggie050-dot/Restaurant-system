<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "kitchen") {
    header("Location: login.php");
    exit();
}

include "includes/db.php";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Keuken Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page-container">
    <div class="page-header">
        <div>
            <p class="eyebrow">Keuken</p>
            <h1>Keuken Dashboard</h1>
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

function statusClass(status) {
    if (status === "ready" || status === "gereed") return "status-ready";
    if (status === "preparing" || status === "in bereiding") return "status-preparing";
    return "status-waiting";
}

function loadOrders() {
    $.get("api/get_orders.php", function(data) {
        let orders = typeof data === "string" ? JSON.parse(data) : data;
        let html = "";

        if (!orders || orders.length === 0) {
            $("#orders").html(`
                <div class="empty-state full-width">
                    <h3>Geen openstaande bestellingen</h3>
                    <p>Betaalde bestellingen verdwijnen automatisch uit dit overzicht.</p>
                </div>
            `);
            return;
        }

        orders.forEach(o => {
            html += `
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <p class="eyebrow">Tafel ${o.table_number}</p>
                            <h3>Bestelling #${o.id}</h3>
                        </div>
                        <span class="status-pill ${statusClass(o.status)}">${o.status}</span>
                    </div>

                    <div class="order-items">
                        ${renderItems(o.items)}
                    </div>

                    <div class="order-actions">
                        <button onclick="updateStatus(${o.id}, 'preparing')">In bereiding</button>
                        <button onclick="updateStatus(${o.id}, 'ready')">Gereed</button>
                    </div>
                </div>
            `;
        });

        $("#orders").html(html);
    });
}

function updateStatus(id, status) {
    $.post("api/update_status.php", { order_id: id, status: status }, loadOrders);
}

setInterval(loadOrders, 2000);
loadOrders();
</script>
</body>
</html>
