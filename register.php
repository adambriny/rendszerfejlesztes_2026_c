<?php
require 'db.php';

$hiba = "";
$siker = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $felhasznalonev = trim($_POST['felhasznalonev']);
    $email = trim($_POST['email']);
    $jelszo = $_POST['jelszo'];

    if (strlen($jelszo) < 4) {
        $hiba = "A jelszó legyen legalább 4 karakter!";
    } else {
        $check = $pdo->prepare("SELECT id FROM felhasznalok WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $hiba = "Ez az e-mail cím már foglalt!";
        } else {
            $hash = password_hash($jelszo, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO felhasznalok (felhasznalonev, email, jelszo_hash) VALUES (?, ?, ?)");
            $stmt->execute([$felhasznalonev, $email, $hash]);
            $siker = "Sikeres regisztráció! Most már bejelentkezhetsz.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Regisztráció</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-left">
        <div style="font-size: 50px;">🏋️</div>
        <h1>FitLog</h1>
        <p>Kezdd el követni az edzéseidet még ma!</p>
    </div>
    <div class="auth-right">
        <div class="auth-box">
            <h2>Regisztráció</h2>
            <p class="subtitle">Hozz létre egy új FitLog fiókot.</p>

            <?php if ($hiba): ?>
                <div class="alert-error"><?= htmlspecialchars($hiba) ?></div>
            <?php endif; ?>
            <?php if ($siker): ?>
                <div class="alert-success"><?= htmlspecialchars($siker) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Felhasználónév</label>
                    <input type="text" name="felhasznalonev" required>
                </div>
                <div class="form-group">
                    <label>E-mail cím</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Jelszó</label>
                    <input type="password" name="jelszo" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Fiók létrehozása</button>
            </form>

            <div style="margin-top: 16px;">
                <a href="index.php" class="btn btn-secondary btn-block">Vissza a bejelentkezéshez</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>