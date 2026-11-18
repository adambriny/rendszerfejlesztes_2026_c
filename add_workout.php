<?php
require 'db.php';
bejelentkezesEllenorzes();

$uid = $_SESSION['felhasznalo_id'];
$gyakorlatLista = $pdo->query("SELECT id, nev FROM gyakorlatok ORDER BY nev ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nev = trim($_POST['nev']);
    $datum = $_POST['datum'];
    $idotartam = (int)$_POST['idotartam'];

    $stmt = $pdo->prepare("INSERT INTO edzesek (felhasznalo_id, nev, datum, idotartam) VALUES (?, ?, ?, ?)");
    $stmt->execute([$uid, $nev, $datum, $idotartam]);
    $edzes_id = $pdo->lastInsertId();

    if (!empty($_POST['gyakorlat_id'])) {
        foreach ($_POST['gyakorlat_id'] as $index => $gy_id) {
            $sorozat_db = (int)$_POST['sorozatok'][$index];
            $ismetles = (int)$_POST['ismetlesek'][$index];
            $suly = (float)$_POST['suly'][$index];

            $egStmt = $pdo->prepare("INSERT INTO edzes_gyakorlatok (edzes_id, gyakorlat_id, sorrend) VALUES (?, ?, ?)");
            $egStmt->execute([$edzes_id, $gy_id, $index + 1]);
            $eg_id = $pdo->lastInsertId();

            for ($s = 1; $s <= $sorozat_db; $s++) {
                $sStmt = $pdo->prepare("INSERT INTO sorozatok (edzes_gyakorlat_id, sorozat_szama, ismetlesszam, suly) VALUES (?, ?, ?, ?)");
                $sStmt->execute([$eg_id, $s, $ismetles, $suly]);
            }
        }
    }

    header("Location: workouts.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Új edzés felvitele</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <div>
            <div class="logo">🏋️ FitLog</div>
            <nav class="nav-links">
                <a href="dashboard.php">🏠 Kezdőlap</a>
                <a href="workouts.php" class="active">📋 Edzések</a>
                <a href="exercises.php">💪 Gyakorlatok</a>
                <a href="stats.php">📊 Statisztikák</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-link">🚪 Kijelentkezés</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Új edzés felvitele</h1>
        </div>

        <form method="POST" class="card">
            <div class="form-group">
                <label>Edzés neve</label>
                <input type="text" name="nev" placeholder="Pl: Mell + Tricepsz" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label>Dátum</label>
                    <input type="date" name="datum" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Időtartam (perc)</label>
                    <input type="number" name="idotartam" value="60" min="1" required>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin: 16px 0;">
                <h3>Gyakorlatok</h3>
                <button type="button" class="btn btn-primary btn-small" onclick="ujGyakorlatSor()">+ Gyakorlat hozzáadása</button>
            </div>

            <div id="gyakorlat-kontener"></div>

            <div style="display:flex; justify-content:space-between; margin-top:24px;">
                <a href="workouts.php" class="btn btn-secondary">Mégsem</a>
                <button type="submit" class="btn btn-primary">Mentés</button>
            </div>
        </form>
    </main>
</div>

<script>
function ujGyakorlatSor() {
    const kontener = document.getElementById('gyakorlat-kontener');
    const div = document.createElement('div');
    div.className = 'list-item';
    div.style.gap = '10px';
    div.innerHTML = `
        <select name="gyakorlat_id[]" required style="flex:2;">
            <?php foreach ($gyakorlatLista as $gy): ?>
                <option value="<?= $gy['id'] ?>"><?= htmlspecialchars($gy['nev']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="sorozatok[]" placeholder="Sorozat" value="3" min="1" required style="flex:1;">
        <input type="number" name="ismetlesek[]" placeholder="Ismétlés" value="8" min="1" required style="flex:1;">
        <input type="number" step="0.5" name="suly[]" placeholder="Súly (kg)" value="50" min="0" required style="flex:1;">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">X</button>
    `;
    kontener.appendChild(div);
}
ujGyakorlatSor();
</script>
</body>
</html>