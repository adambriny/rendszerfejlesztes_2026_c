<?php
require 'db.php';
bejelentkezesEllenorzes();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uj_nev'])) {
    $stmt = $pdo->prepare("INSERT INTO gyakorlatok (nev, izomcsoport_id, leiras) VALUES (?, ?, ?)");
    $stmt->execute([trim($_POST['uj_nev']), (int)$_POST['izomcsoport_id'], trim($_POST['leiras'])]);
    header("Location: exercises.php");
    exit;
}

$izom_id = isset($_GET['izom']) ? (int)$_GET['izom'] : 0;
$kereses = isset($_GET['q']) ? trim($_GET['q']) : '';

$izomcsoportok = $pdo->query("SELECT * FROM izomcsoportok")->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT g.*, i.nev AS izom_nev FROM gyakorlatok g JOIN izomcsoportok i ON g.izomcsoport_id = i.id WHERE 1=1";
$params = [];

if ($izom_id > 0) {
    $sql .= " AND g.izomcsoport_id = ?";
    $params[] = $izom_id;
}
if ($kereses !== '') {
    $sql .= " AND g.nev LIKE ?";
    $params[] = "%$kereses%";
}
$sql .= " ORDER BY g.nev ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gyakorlatok = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Gyakorlatok</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <div>
            <div class="logo">🏋️ FitLog</div>
            <nav class="nav-links">
                <a href="dashboard.php">🏠 Kezdőlap</a>
                <a href="workouts.php">📋 Edzések</a>
                <a href="exercises.php" class="active">💪 Gyakorlatok</a>
                <a href="stats.php">📊 Statisztikák</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-link">🚪 Kijelentkezés</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Gyakorlatok</h1>
                <p>Keresd meg a hozzád illő gyakorlatokat, vagy adj hozzá újat!</p>
            </div>
            <button class="btn btn-primary" onclick="document.getElementById('uj-doboz').style.display='block'">+ Új gyakorlat</button>
        </div>

        <div id="uj-doboz" class="card" style="display:none; margin-bottom:20px;">
            <h3 style="margin-bottom:12px;">Új gyakorlat rögzítése</h3>
            <form method="POST">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>Gyakorlat neve</label>
                        <input type="text" name="uj_nev" required>
                    </div>
                    <div class="form-group">
                        <label>Izomcsoport</label>
                        <select name="izomcsoport_id">
                            <?php foreach ($izomcsoportok as $izom): ?>
                                <option value="<?= $izom['id'] ?>"><?= htmlspecialchars($izom['nev']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Rövid leírás</label>
                    <input type="text" name="leiras" placeholder="Pl. Mellizom alapgyakorlat">
                </div>
                <button type="submit" class="btn btn-primary btn-small">Mentés</button>
                <button type="button" class="btn btn-secondary btn-small" onclick="document.getElementById('uj-doboz').style.display='none'">Bezárás</button>
            </form>
        </div>

        <form method="GET" style="margin-bottom:16px;">
            <input type="text" name="q" placeholder="Gyakorlat keresése..." value="<?= htmlspecialchars($kereses) ?>">
        </form>

        <div class="filter-pills">
            <a href="exercises.php" class="pill <?= $izom_id === 0 ? 'active' : '' ?>">Összes</a>
            <?php foreach ($izomcsoportok as $izom): ?>
                <a href="exercises.php?izom=<?= $izom['id'] ?>" class="pill <?= $izom_id === (int)$izom['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($izom['nev']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($gyakorlatok as $gy): ?>
            <div class="list-item">
                <div>
                    <strong>💪 <?= htmlspecialchars($gy['nev']) ?></strong>
                    <div style="font-size:13px; color:#94a3b8; margin-top:4px;"><?= htmlspecialchars($gy['leiras']) ?></div>
                </div>
                <span class="badge"><?= htmlspecialchars($gy['izom_nev']) ?></span>
            </div>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>