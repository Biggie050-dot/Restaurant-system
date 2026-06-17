<?php
// Start een sessie zodat we gebruikersgegevens kunnen bewaren na het inloggen
session_start();

// Verbind met de database
include "includes/db.php";

// Variabele voor foutmeldingen
$error = "";

// Controleert of het formulier is verzonden met POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Haal de ingevulde gebruikersnaam en wachtwoord op uit het formulier
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Zoek een gebruiker in de database met dezelfde gebruikersnaam en wachtwoord
    $sql = "SELECT * FROM users 
            WHERE username = '$username' 
            AND password = '$password'";

    // Voer de query uit
    $result = pg_query($conn, $sql);

    // Controleer of er precies één gebruiker is gevonden
    if (pg_num_rows($result) == 1) {

        // Haal de gegevens van de gevonden gebruiker op
        $user = pg_fetch_assoc($result);

        // Sla belangrijke gebruikersgegevens op in de sessie
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        // Stuur de gebruiker door naar de juiste pagina op basis van zijn rol
        if ($user["role"] == "customer") {
            header("Location: customer.php");
        } elseif ($user["role"] == "kitchen") {
            header("Location: kitchen.php");
        } elseif ($user["role"] == "cashier") {
            header("Location: cashier.php");
        } elseif ($user["role"] == "admin") {
            header("Location: admin.php");
        }

        // Stop de code na het doorsturen
        exit();
    } else {
        // Toon foutmelding als de gegevens niet kloppen
        $error = "Gebruikersnaam of wachtwoord is fout.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Login</title>

    <!-- Koppeling naar het CSS-bestand voor de styling -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Loginblok -->
<div class="login-container">

    <h1>Restaurant System</h1>

    <!-- Loginformulier -->
    <form method="POST">

        <!-- Veld voor gebruikersnaam -->
        <input 
            type="text" 
            name="username" 
            placeholder="Gebruikersnaam"
            required
        >

        <!-- Veld voor wachtwoord -->
        <input 
            type="password" 
            name="password" 
            placeholder="Wachtwoord"
            required
        >

        <!-- Knop om in te loggen -->
        <button type="submit">
            Inloggen
        </button>

    </form>

    <!-- Laat een foutmelding zien als inloggen mislukt -->
    <p class="error">
        <?php echo $error; ?>
    </p>

</div>

</body>
</html>