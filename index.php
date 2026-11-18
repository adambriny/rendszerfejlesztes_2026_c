<?php
require 'db.php';

if (isset($_SESSION['felhasznalo_id'])) {
    header("Location: dashboard.php");
    exit;
}

$hiba = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $jelszo = $_POST['jelszo'];

    $stmt = $pdo->prepare("SELECT * FROM felhasznalok WHERE email = ? OR felhasznalonev = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($jelszo, $user['jelszo_hash'])) {
        $_SESSION['felhasznalo_id'] = $user['id'];
        $_SESSION['felhasznalonev'] = $user['felhasznalonev'];
        header("Location: dashboard.php");
        exit;
    } else {
        $hiba = "Hibás felhasználónév/e-mail vagy jelszó!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-left">
        <div style="font-size: 50px;">🏋️</div>
        <h1>FitLog</h1>
        <p>Jobb edzések. Erősebb te.</p>
    </div>
    <div class="auth-right">
        <div class="auth-box">
            <h2>Bejelentkezés</h2>
            <p class="subtitle">Lépj be a fiókodba, hogy folytathasd!</p>

            <?php if ($hiba): ?>
                <div class="alert-error"><?= htmlspecialchars($hiba) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Felhasználónév vagy e-mail</label>
                    <input type="text" name="email" placeholder="pl. jozsef@fitlog.hu" required>
                </div>
                <div class="form-group">
                    <label>Jelszó</label>
                    <input type="password" name="jelszo" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Bejelentkezés</button>
            </form>

            <div style="text-align: center; margin: 16px 0; color: #64748b; font-size: 13px;">vagy</div>
            <a href="register.php" class="btn btn-secondary btn-block">Regisztráció</a>
        </div>
    </div>
</div>
</body>
</html>