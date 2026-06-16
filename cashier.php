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
        <h1>Kassa Dashboard</h1>

        <a href="logout.php" class="logout-button">
            Uitloggen
        </a>
    </div>

    <div id="orders"></div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

function loadOrders() {

    $.get("api/get_orders.php", function(data) {

        let orders = JSON.parse(data);
        let html = "";

        if (!orders || orders.length === 0) {

            html = `
                <div class="order">
                    <h3>Geen bestellingen beschikbaar</h3>
                </div>
            `;

            $("#orders").html(html);
            return;
        }

        orders.forEach(o => {

            if (o.status === "onderweg") {

                html += `

                    <div class="order">

                        <h3>Bestelling #${o.id}</h3>

                        <p>
                            <strong>Tafel:</strong>
                            ${o.table_number}
                        </p>

                        <p>
                            <strong>Status:</strong>
                            ${o.status}
                        </p>

                        <button
                            class="ready-btn"
                            onclick="pay(${o.id})">

                            Afrekenen

                        </button>

                    </div>

                `;
            }
        });

        $("#orders").html(html);

    });

}

function pay(id) {

    $.post(
        "api/update_status.php",
        {
            order_id: id,
            status: "betaald"
        },
        function() {
            loadOrders();
        }
    );

}

setInterval(loadOrders, 2000);

loadOrders();

</script>

</body>
</html>