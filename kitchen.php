<?php include "includes/db.php"; ?>
<div id="orders"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadOrders() {
    $.get("api/get_orders.php", function(data) {
        let orders = JSON.parse(data);
        let html = "";

        if (!orders) return;

        orders.forEach(o => {
            html += `
                <div class="order">
                    <h3>Order #${o.id}</h3>
                    <p>Status: ${o.status}</p>
                    <button onclick="updateStatus(${o.id}, 'preparing')">Preparing</button>
                    <button onclick="updateStatus(${o.id}, 'ready')">Ready</button>
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
