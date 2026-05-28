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
            if (o.status === "ready") {
                html += `
                    <div class="order">
                        <h3>Order #${o.id}</h3>
                        <p>Status: ${o.status}</p>
                        <button onclick="pay(${o.id})">Mark as Paid</button>
                    </div>
                `;
            }
        });

        $("#orders").html(html);
    });
}

function pay(id) {
    $.post("api/update_status.php", { order_id: id, status: "paid" }, loadOrders);
}

setInterval(loadOrders, 2000);
loadOrders();
</script>
