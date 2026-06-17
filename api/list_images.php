<?php

// Geef aan dat de response JSON bevat
header('Content-Type: application/json');

// Map waarin alle productfoto's worden opgeslagen
$photoDir = __DIR__ . "/../foto's/";

// Webpad dat gebruikt wordt om de afbeeldingen in de browser te tonen
$photoWebPath = "foto's/";

// Toegestane afbeeldingsformaten
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

// Array waarin alle gevonden afbeeldingen worden opgeslagen
$images = [];

// Controleer of de map bestaat
if (is_dir($photoDir)) {

    // Lees alle bestanden uit de map
    $files = scandir($photoDir);

    // Loop door alle bestanden heen
    foreach ($files as $file) {

        // Sla de huidige map (.) en bovenliggende map (..) over
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Volledig bestandspad
        $fullPath = $photoDir . $file;

        // Haal de extensie van het bestand op
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Controleer of het een geldig afbeeldingsbestand is
        if (
            is_file($fullPath) &&
            in_array($extension, $allowedExtensions, true)
        ) {

            // Voeg afbeelding toe aan de array
            $images[] = [
                'name' => $file,                    // Bestandsnaam
                'path' => $photoWebPath . $file     // Pad voor gebruik in HTML
            ];
        }
    }
}

// Sorteer afbeeldingen alfabetisch op naam
usort($images, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Zet de array om naar JSON en stuur deze terug
echo json_encode($images);