<?php
require 'db.php';
bejelentkezesEllenorzes();

$uid = $_SESSION['felhasznalo_id'];

$alap = $pdo->prepare("SELECT COUNT(*) AS db, COALESCE(SUM(idotartam), 0) AS perc FROM edzesek WHERE felhasznalo_id = ?");
$alap->execute([$uid]);
$stat = $alap->fetch(PDO::FETCH_ASSOC);

$izomStat = $pdo->prepare("
    SELECT i.nev, i.szin, COUNT(*) AS db
    FROM edzesek e
    JOIN edzes_gyakorlatok eg ON e.id = eg.edzes_id
    JOIN gyakorlatok g ON eg.gyakorlat_id = g.id
    JOIN izomcsoportok i ON g.izomcsoport_id = i.id
    WHERE e.felhasznalo_id = ?
    GROUP BY i.id
    ORDER BY db DESC
");
$izomStat->execute([$uid]);
$izomAdatok = $izomStat->fetchAll(PDO::FETCH_ASSOC);
$osszesGyakorlat = array_sum(array_column($izomAdatok, 'db'));

$rekordStmt = $pdo->prepare("
    SELECT g.nev, MAX(s.suly) AS max_suly
    FROM edzesek e
    JOIN edzes_gyakorlatok eg ON e.id = eg.edzes_id
    JOIN gyakorlatok g ON eg.gyakorlat_id = g.id
    JOIN sorozatok s ON eg.id = s.edzes_gyakorlat_id
    WHERE e.felhasznalo_id = ? AND s.suly > 0
    GROUP BY g.id
    ORDER BY max_suly DESC
    LIMIT 5
");
$rekordStmt->execute([$uid]);
$rekordok = $rekordStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Statisztikák</title>
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
                <a href="exercises.php">💪 Gyakorlatok</a>
                <a href="stats.php" class="active">📊 Statisztikák</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-link">🚪 Kijelentkezés</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Statisztikák</h1>
                <p>Kövesd a fejlődésedet számokban!</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card stat-card">
                <div class="label">Edzések száma</div>
                <div class="value" style="color:#3b82f6;"><?= $stat['db'] ?></div>
            </div>
            <div class="card stat-card">
                <div class="label">Összes edzésidő</div>
                <div class="value"><?= round($stat['perc'] / 60, 1) ?> óra</div>
            </div>
            <div class="card stat-card">
                <div class="label">Átlagos időtartam</div>
                <div class="value"><?= $stat['db'] > 0 ? round($stat['perc'] / $stat['db']) : 0 ?> perc</div>
            </div>
        </div>

        <div class="two-col-grid">
            <div class="card">
                <h3 style="margin-bottom:16px;">Izomcsoportok megoszlása</h3>
                <?php if (empty($izomAdatok)): ?>
                    <p style="color:#94a3b8;">Még nincs elég adat.</p>
                <?php else: ?>
                    <?php foreach ($izomAdatok as $ia): ?>
                        <?php $szazalek = round(($ia['db'] / $osszesGyakorlat) * 100); ?>
                        <div class="bar-row">
                            <div class="bar-label">
                                <span><?= htmlspecialchars($ia['nev']) ?></span>
                                <span><?= $szazalek ?>%</span>
                            </div>
                            <div class="bar-bg">
                                <div class="bar-fill" style="width:<?= $szazalek ?>%; background-color:<?= htmlspecialchars($ia['szin']) ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="margin-bottom:16px;">Személyes rekordok</h3>
                <?php if (empty($rekordok)): ?>
                    <p style="color:#94a3b8;">Még nincs súlyzós rekordod.</p>
                <?php else: ?>
                    <?php foreach ($rekordok as $rek): ?>
                        <div class="list-item">
                            <span><?= htmlspecialchars($rek['nev']) ?></span>
                            <strong><?= $rek['max_suly'] ?> kg</strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>