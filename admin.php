<h2>Gerecht toevoegen</h2>

<form id="addItem">
    <input type="text" name="name" placeholder="Naam" required>
    <input type="number" name="price" placeholder="Prijs" required>
    <button type="submit">Toevoegen</button>
</form>

<div id="menu"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#addItem").submit(function(e){
    e.preventDefault();

    $.post("api/add_menu_item.php", $(this).serialize(), function(){
        alert("Gerecht toegevoegd!");
        loadMenu();
    });
});

function loadMenu() {
    $.get("api/get_menu.php", function(data){
        let items = JSON.parse(data);
        let html = "<h3>Menu Items</h3>";

        if (!items) return;

        items.forEach(i => {
            html += `<p>${i.name} - €${i.price}</p>`;
        });

        $("#menu").html(html);
    });
}

loadMenu();
</script>
