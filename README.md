# Restaurant Management Systeem 

Dit project is een volledig (gesimuleerd)functioneel, live-updating Restaurant Management Systeem gebouwd met PHP, PostgreSQL, JavaScript (jQuery & AJAX) en HTML/CSS. Het systeem maakt gebruik van rolgebaseerde toegangscontrole om specifieke dashboards te tonen voor klanten, keukenpersoneel, kassiers en administrators.

## Groepsleden & Taakverdeling

### Ensar Isci
* **Pagina's:** `login.php`, `customer.php`, `order_status.php`, `logout.php`
* **API / Backend:** `api/create_order.php`,`api/delete_menu.php`, `api/list_images.php`, `api/add_menu.php`, `js/app.js`

### Ersan Ascioglu
* **Pagina's:** `kitchen.php`, `cashier.php`, `admin.php`
* **API / Backend:** `api/get_orders.php`, `api/update_status.php`, , `api/get_menu.php`

---

## Werking van de Website & Architectuur

Het systeem draait op een **Client-Server architectuur** .

[Klant (customer.php)]     --> Plaatst order via AJAX --> [api/create_order.php] 
|
[Keuken (kitchen.php)]     <-- via AJAX <-- [api/get_orders.php]   <---------+
[Keuken (kitchen.php)]     --> Update status naar 'ready' -> [api/update_status.php] --------->+
|
[Kassa (cashier.php)]      <-- Filtert 'ready' orders    <-- [api/get_orders.php]   <---------+
[Kassa (cashier.php)]      --> Zet status op 'betaald'    -> [api/update_status.php] --------->+


### 1. Flow van een Bestelling (Order Lifecycle)
1. **Klant:** De klant logt in, navigeert door gecategoriseerde gerechten (`customer.php`), voegt items toe aan een lokaal JavaScript-winkelmandje (`app.js`) en verzendt de order met een tafelnummer naar `api/create_order.php`. De initiële status is `'in de wachtrij'`.

2. **Keuken:** Het keukendashboard (`kitchen.php`) haalt elke 2 seconden de openstaande bestellingen op via `api/get_orders.php`. De kok kan de status wijzigen naar `'preparing'` (in bereiding) en vervolgens naar `'ready'` (gereed) middels `api/update_status.php`.

3. **Kassa:** Het kassadashboard (`cashier.php`) haalt eveneens data op uit `api/get_orders.php`, maar filtert deze client-side zodanig dat *alleen* bestellingen met de status `'ready'` getoond worden. Zodra de klant betaalt, triggert de kassier een AJAX-call die de status op `'betaald'` zet. De order verdwijnt hiermee uit de actieve workflows.

### 2. Administratief Beheer (Menu Management)
* De Admin (`admin.php`) heeft de volledige controle over de beschikbare gerechten (`menu_items`).

* **Toevoegen:** Via een HTML5 `multipart/form-data` formulier en een jQuery AJAX-verzoek (`api/add_menu.php`) worden tekstgegevens gecombineerd met een fysieke bestandsupload (afbeelding). De API valideert de bestandskwaliteit, MIME-type, genereert een unieke bestandsnaam (`uniqid()`) en slaat het pad op in de database.

