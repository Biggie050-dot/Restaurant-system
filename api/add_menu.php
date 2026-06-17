<?php
// Voeg de databaseverbinding toe. Dit bestand bevat de variabele $conn
// die we nodig hebben voor PostgreSQL-query's.
require '../includes/db.php';


header('Content-Type: application/json'); // dit is voor de client (admin.php) zodat deze weet dat het antwoord JSON is en deze automatisch kan omzetten 

// --- Ontvang de POST-gegevens ---
// Naam van het gerecht, trim om witruimte voor en achter te verwijderen.
$name = trim($_POST['name'] ?? '');
// Prijs (als string, later wordt deze door de database omgezet naar een numeriek type).
$price = $_POST['price'] ?? '';
// Categorie, eveneens getrimd.
$category = trim($_POST['category'] ?? '');
// Het pad naar de gekozen afbeelding, zoals verzonden vanuit de dropdown in admin.php.
$selectedImage = trim($_POST['image_path'] ?? '');
// Dit wordt de uiteindelijke waarde die in de database komt (null of een pad).
$imagePath = null;

// --- Validatie: verplichte velden ---
// Als een van de drie verplichte velden leeg is, stuur dan een foutmelding.
// De HTTP-statuscode 400 (Bad Request) geeft aan dat de aanvraag onvolledig is.
if ($name === '' || $price === '' || $category === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Naam, prijs en categorie zijn verplicht."]);
    exit(); // Stop de uitvoering.
}

// --- Als er een afbeelding is geselecteerd (niet leeg) ---
if ($selectedImage !== '') {
    // Lijst van toegestane bestandsextensies (alleen afbeeldingen).
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    // Haal de extensie uit de bestandsnaam en zet deze om naar kleine letters.
    $extension = strtolower(pathinfo($selectedImage, PATHINFO_EXTENSION));

    // --- Veiligheidscheck 1: Pad en extensie ---
    // Controleer of het pad begint met "foto's/" (de map waarin we de afbeeldingen bewaren)
    // EN of de extensie in de toegestane lijst staat.
    // substr($selectedImage, 0, 7) pakt de eerste 7 tekens (dat is "foto's/").
    if (substr($selectedImage, 0, 7) !== "foto's/" || !in_array($extension, $allowedExtensions, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ongeldige foto gekozen."]);
        exit();
    }

    // --- Veiligheidscheck 2: Bestaat het bestand echt? ---
    // We gebruiken basename() om alleen de bestandsnaam te pakken (zonder pad).
    // Dit voorkomt dat iemand probeert te 'ontsnappen' met paden als ../../etc/passwd.
    $fileName = basename($selectedImage);

    // realpath() geeft het absolute pad van de map "foto's" (of false als die niet bestaat).
    $realPhotoDir = realpath(__DIR__ . "/../foto's/");
    // realpath() van het volledige pad naar het bestand (als het bestaat).
    $realImagePath = realpath(__DIR__ . "/../foto's/" . $fileName);

    // --- Veiligheidscheck 3: Bestaat de map? Bestaat het bestand? Ligt het in de juiste map? ---
    // $realPhotoDir === false  → de map bestaat niet.
    // $realImagePath === false  → het bestand bestaat niet.
    // strpos($realImagePath, $realPhotoDir) !== 0  → het bestandspad begint niet met de map "foto's"
    //   (dit voorkomt dat iemand via symbolische links of ../../ naar buiten de map kan komen).
    // !is_file($realImagePath)  → het is geen gewoon bestand (misschien een map?).
    if ($realPhotoDir === false || $realImagePath === false || strpos($realImagePath, $realPhotoDir) !== 0 || !is_file($realImagePath)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "De gekozen foto bestaat niet in de map foto's."]);
        exit();
    }

    // --- Alles veilig: sla het relatieve pad op ---
    // We slaan het pad op zoals het in de database moet komen (relatief ten opzichte van de webroot).
    // Omdat we de veiligheid al hebben gecontroleerd, kunnen we vertrouwen op de bestandsnaam.
    $imagePath = "foto's/" . $fileName;
}

// --- Voeg het nieuwe menu-item toe aan de database ---
// Gebruik pg_query_params om SQL-injectie te voorkomen.
// De placeholders $1, $2, $3, $4 worden veilig vervangen door de waarden uit de array.
// is_active wordt standaard op true gezet, zodat het item direct zichtbaar is in het menu.
$result = pg_query_params($conn,
    "INSERT INTO menu_items (name, price, category, image_path, is_active) VALUES ($1, $2, $3, $4, true)",
    [$name, $price, $category, $imagePath]
);

// Als de query mislukt, stuur dan een 500-fout (Internal Server Error).
if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Gerecht kon niet worden toegevoegd."]);
    exit();
}

// --- Alles is gelukt: stuur een succesvolle JSON-response ---
// De client (admin.php) verwacht { "success": true } en zal dan de menulijst herladen.
echo json_encode(["success" => true]);