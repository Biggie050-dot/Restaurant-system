let cart = [];

$(document).on("click", ".add-to-cart", function () {
    let id = $(this).data("id");
    let name = $(this).data("name");
    let price = parseFloat($(this).data("price"));

    let existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });
    }

    updateCart();
});

function updateCart() {
    let cartHtml = "";
    let total = 0;

    if (cart.length === 0) {
        $("#cart-items").html("<p>Nog geen producten gekozen.</p>");
        $("#cart-total").text("0.00");
        return;
    }

    cart.forEach((item, index) => {
        total += item.price * item.quantity;

        cartHtml += `
            <div class="cart-item">
                <span>
                    ${item.name} x ${item.quantity} - €${(item.price * item.quantity).toFixed(2)}
                </span>

                <button class="remove-item" data-index="${index}">
                    Verwijderen
                </button>
            </div>
        `;
    });

    $("#cart-items").html(cartHtml);
    $("#cart-total").text(total.toFixed(2));
}

$(document).on("click", ".remove-item", function () {
    let index = $(this).data("index");

    cart.splice(index, 1);

    updateCart();
});

$("#place-order").on("click", function () {
    let tableNumber = $("#table-number").val();

    if (tableNumber === "" || cart.length === 0) {
        alert("Vul een tafelnummer in en kies minimaal één product.");
        return;
    }

    $.ajax({
        url: "api/create_order.php",
        method: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({
            table_number: tableNumber,
            items: cart
        }),
        success: function (response) {
            if (response.success) {
                alert("Bestelling geplaatst! Je bestelnummer is: " + response.order_id);

                cart = [];
                updateCart();
                $("#table-number").val("");
            } else {
                alert(response.message);
            }
        },
        error: function (xhr) {
            console.log(xhr.responseText);
            alert("Er ging iets fout. Kijk in de console voor de foutmelding.");
        }
    });
});