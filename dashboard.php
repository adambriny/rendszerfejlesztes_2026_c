<?php
require 'db.php';
bejelentkezesEllenorzes();

$uid = $_SESSION['felhasznalo_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS db, COALESCE(SUM(idotartam), 0) AS perc FROM edzesek WHERE felhasznalo_id = ?");
$stmt->execute([$uid]);
$osszes = $stmt->fetch(PDO::FETCH_ASSOC);

$osszes_ora = round($osszes['perc'] / 60, 1);

$izomStmt = $pdo->prepare("
    SELECT i.nev, COUNT(*) AS db
    FROM edzesek e
    JOIN edzes_gyakorlatok eg ON e.id = eg.edzes_id
    JOIN gyakorlatok g ON eg.gyakorlat_id = g.id
    JOIN izomcsoportok i ON g.izomcsoport_id = i.id
    WHERE e.felhasznalo_id = ?
    GROUP BY i.id
    ORDER BY db DESC
    LIMIT 1
");
$izomStmt->execute([$uid]);
$topIzom = $izomStmt->fetch(PDO::FETCH_ASSOC);

$recentStmt = $pdo->prepare("SELECT * FROM edzesek WHERE felhasznalo_id = ? ORDER BY datum DESC, id DESC LIMIT 4");
$recentStmt->execute([$uid]);
$legutobbiak = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Kezdőlap</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <div>
            <div class="logo">🏋️ FitLog</div>
            <nav class="nav-links">
                <a href="dashboard.php" class="active">🏠 Kezdőlap</a>
                <a href="workouts.php">📋 Edzések</a>
                <a href="exercises.php">💪 Gyakorlatok</a>
                <a href="stats.php">📊 Statisztikák</a>
            </nav>
        </div>
        <a href="logout.php" class="logout-link">🚪 Kijelentkezés</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Szia, <?= htmlspecialchars($_SESSION['felhasznalonev']) ?>!</h1>
                <p>Készen állsz egy újabb edzésre?</p>
            </div>
            <a href="add_workout.php" class="btn btn-primary">+ Új edzés</a>
        </div>

        <div class="stats-grid">
            <div class="card stat-card">
                <div class="label">Összes edzés</div>
                <div class="value"><?= $osszes['db'] ?></div>
            </div>
            <div class="card stat-card">
                <div class="label">Összes edzésidő</div>
                <div class="value"><?= $osszes_ora ?> óra</div>
            </div>
            <div class="card stat-card">
                <div class="label">Leggyakoribb izom</div>
                <div class="value"><?= $topIzom ? htmlspecialchars($topIzom['nev']) : 'Nincs adat' ?></div>
            </div>
            <div class="card stat-card">
                <div class="label">Heti cél</div>
                <div class="value">3 edzés</div>
            </div>
        </div>

        <div class="two-col-grid">
            <div class="card">
                <div style="display:flex; justify-content:space-between; margin-bottom:16px;">
                    <h3>Legutóbbi edzések</h3>
                    <a href="workouts.php" style="color:#3b82f6; font-size:14px;">Még több →</a>
                </div>
                <?php if (empty($legutobbiak)): ?>
                    <p style="color:#94a3b8;">Még nincs rögzített edzésed.</p>
                <?php else: ?>
                    <?php foreach ($legutobbiak as $edzes): ?>
                        <div class="list-item">
                            <div>
                                <span style="color:#60a5fa; font-size:13px; margin-right:10px;"><?= htmlspecialchars($edzes['datum']) ?></span>
                                <strong><?= htmlspecialchars($edzes['nev']) ?></strong>
                            </div>
                            <span style="color:#94a3b8;"><?= $edzes['idotartam'] ?> perc</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 style="margin-bottom:16px;">Edzések időtartama</h3>
                <?php foreach (array_reverse($legutobbiak) as $edzes): ?>
                    <?php $szazalek = min(100, round(($edzes['idotartam'] / 90) * 100)); ?>
                    <div class="bar-row">
                        <div class="bar-label">
                            <span><?= htmlspecialchars($edzes['nev']) ?></span>
                            <span><?= $edzes['idotartam'] ?> perc</span>
                        </div>
                        <div class="bar-bg">
                            <div class="bar-fill" style="width: <?= $szazalek ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>