<?php
session_start();
include "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users 
            WHERE username = '$username' 
            AND password = '$password'";

    $result = pg_query($conn, $sql);

    if (pg_num_rows($result) == 1) {

        $user = pg_fetch_assoc($result);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] == "customer") {
            header("Location: customer.php");
        } elseif ($user["role"] == "kitchen") {
            header("Location: kitchen.php");
        } elseif ($user["role"] == "cashier") {
            header("Location: cashier.php");
        } elseif ($user["role"] == "admin") {
            header("Location: admin.php");
        }

        exit();
    } else {
        $error = "Gebruikersnaam of wachtwoord is fout.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">

    <h1>Restaurant System</h1>

    <form method="POST">

        <input 
            type="text" 
            name="username" 
            placeholder="Gebruikersnaam"
            required
        >

        <input 
            type="password" 
            name="password" 
            placeholder="Wachtwoord"
            required
        >

        <button type="submit">
            Inloggen
        </button>

    </form>

    <p class="error">
        <?php echo $error; ?>
    </p>

</div>

</body>
</html>