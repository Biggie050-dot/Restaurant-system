// Winkelmandje-array waarin alle gekozen producten worden opgeslagen
let cart = [];

// Wanneer er op een "Toevoegen"-knop wordt geklikt
$(document).on("click", ".add-to-cart", function () {

    // Haal de productgegevens op uit de data-attributen van de knop
    let id = $(this).data("id");
    let name = $(this).data("name");
    let price = parseFloat($(this).data("price"));

    // Controleer of het product al in het winkelmandje zit
    let existingItem = cart.find(item => item.id === id);

    // Als het product al bestaat, verhoog dan alleen het aantal
    if (existingItem) {
        existingItem.quantity++;

    // Als het product nog niet bestaat, voeg het toe aan het winkelmandje
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });
    }

    // Werk het winkelmandje op de pagina bij
    updateCart();
});

// Functie om het winkelmandje opnieuw weer te geven
function updateCart() {
    let cartHtml = "";
    let total = 0;

    // Als het winkelmandje leeg is, toon een standaardtekst
    if (cart.length === 0) {
        $("#cart-items").html("<p>Nog geen producten gekozen.</p>");
        $("#cart-total").text("0.00");
        return;
    }

    // Loop door alle producten in het winkelmandje
    cart.forEach((item, index) => {

        // Bereken het totaalbedrag
        total += item.price * item.quantity;

        // Maak HTML voor elk product in het winkelmandje
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

    // Plaats de winkelmandje-items in de pagina
    $("#cart-items").html(cartHtml);

    // Toon het totaalbedrag met twee decimalen
    $("#cart-total").text(total.toFixed(2));
}

// Wanneer er op een verwijderknop in het winkelmandje wordt geklikt
$(document).on("click", ".remove-item", function () {

    // Haal de index op van het product dat verwijderd moet worden
    let index = $(this).data("index");

    // Verwijder het product uit het winkelmandje
    cart.splice(index, 1);

    // Werk het winkelmandje opnieuw bij
    updateCart();
});

// Wanneer de klant op "Bestelling plaatsen" klikt
$("#place-order").on("click", function () {

    // Haal het ingevulde tafelnummer op
    let tableNumber = $("#table-number").val();

    // Controleer of er een tafelnummer is ingevuld en of het winkelmandje niet leeg is
    if (tableNumber === "" || cart.length === 0) {
        alert("Vul een tafelnummer in en kies minimaal één product.");
        return;
    }

    // Verstuur de bestelling naar de backend via AJAX
    $.ajax({
        url: "api/create_order.php",
        method: "POST",

        // Geef aan dat de data als JSON wordt verstuurd
        contentType: "application/json",

        // Verwacht een JSON-response terug
        dataType: "json",

        // Zet de bestelling om naar JSON
        data: JSON.stringify({
            table_number: tableNumber,
            items: cart
        }),

        // Als de bestelling succesvol is geplaatst
        success: function (response) {
            if (response.success) {
                alert("Bestelling geplaatst! Je bestelnummer is: " + response.order_id);

                // Maak het winkelmandje leeg
                cart = [];

                // Werk het winkelmandje op de pagina bij
                updateCart();

                // Maak het tafelnummer-veld leeg
                $("#table-number").val("");

            // Als de backend een foutmelding teruggeeft
            } else {
                alert(response.message);
            }
        },

        // Als er een technische fout ontstaat
        error: function (xhr) {

            // Toon de foutmelding in de browserconsole
            console.log(xhr.responseText);

            // Toon een algemene foutmelding aan de gebruiker
            alert("Er ging iets fout. Kijk in de console voor de foutmelding.");
        }
    });
});