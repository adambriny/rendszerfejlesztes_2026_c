<?php
require 'db.php';
bejelentkezesEllenorzes();

$uid = $_SESSION['felhasznalo_id'];
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM edzesek WHERE id = ? AND felhasznalo_id = ?");
$stmt->execute([$id, $uid]);
$edzes = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$edzes) {
    die("Az edzés nem található!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nev = trim($_POST['nev']);
    $datum = $_POST['datum'];
    $idotartam = (int)$_POST['idotartam'];

    $upd = $pdo->prepare("UPDATE edzesek SET nev = ?, datum = ?, idotartam = ? WHERE id = ? AND felhasznalo_id = ?");
    $upd->execute([$nev, $datum, $idotartam, $id, $uid]);

    header("Location: workouts.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Edzés módosítása</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <main class="main-content" style="max-width:600px; margin:auto;">
        <h1 style="margin-bottom:20px;">Edzés módosítása</h1>
        <form method="POST" class="card">
            <div class="form-group">
                <label>Edzés neve</label>
                <input type="text" name="nev" value="<?= htmlspecialchars($edzes['nev']) ?>" required>
            </div>
            <div class="form-group">
                <label>Dátum</label>
                <input type="date" name="datum" value="<?= htmlspecialchars($edzes['datum']) ?>" required>
            </div>
            <div class="form-group">
                <label>Időtartam (perc)</label>
                <input type="number" name="idotartam" value="<?= $edzes['idotartam'] ?>" required>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <a href="workouts.php" class="btn btn-secondary">Mégsem</a>
                <button type="submit" class="btn btn-primary">Módosítások mentése</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>