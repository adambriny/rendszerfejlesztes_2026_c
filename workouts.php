<?php
require 'db.php';
bejelentkezesEllenorzes();

$uid = $_SESSION['felhasznalo_id'];
$kereses = isset($_GET['kereses']) ? trim($_GET['kereses']) : '';

if ($kereses !== '') {
    $stmt = $pdo->prepare("SELECT * FROM edzesek WHERE felhasznalo_id = ? AND (nev LIKE ? OR datum LIKE ?) ORDER BY datum DESC");
    $stmt->execute([$uid, "%$kereses%", "%$kereses%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM edzesek WHERE felhasznalo_id = ? ORDER BY datum DESC");
    $stmt->execute([$uid]);
}
$edzesek = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>FitLog – Edzések</title>
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
            <div>
                <h1>Edzések</h1>
                <p>Nézd meg korábbi edzéseidet, vagy vegyél fel egy újat!</p>
            </div>
            <a href="add_workout.php" class="btn btn-primary">+ Új edzés</a>
        </div>

        <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
            <input type="text" name="kereses" placeholder="Keresés edzés nevére vagy dátumra (pl. Mell vagy 2026-11)..." value="<?= htmlspecialchars($kereses) ?>">
            <button type="submit" class="btn btn-primary">Szűrés</button>
            <?php if ($kereses !== ''): ?>
                <a href="workouts.php" class="btn btn-secondary">Törlés</a>
            <?php endif; ?>
        </form>

        <?php if (empty($edzesek)): ?>
            <div class="card">Nincs megjeleníthető edzés.</div>
        <?php else: ?>
            <?php foreach ($edzesek as $edzes): ?>
                <?php
                $gyStmt = $pdo->prepare("
                    SELECT g.nev, COUNT(s.id) AS sorozat_db, MAX(s.ismetlesszam) AS ismetles, MAX(s.suly) AS max_suly
                    FROM edzes_gyakorlatok eg
                    JOIN gyakorlatok g ON eg.gyakorlat_id = g.id
                    LEFT JOIN sorozatok s ON eg.id = s.edzes_gyakorlat_id
                    WHERE eg.edzes_id = ?
                    GROUP BY eg.id
                ");
                $gyStmt->execute([$edzes['id']]);
                $gyakorlatok = $gyStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="list-item" style="align-items: flex-start;">
                    <div>
                        <strong style="font-size:18px;">💪 <?= htmlspecialchars($edzes['nev']) ?></strong>
                        <div style="color:#94a3b8; font-size:13px; margin:4px 0 10px 0;">
                            <?= htmlspecialchars($edzes['datum']) ?> | <?= $edzes['idotartam'] ?> perc
                        </div>
                        <?php foreach ($gyakorlatok as $gy): ?>
                            <div style="font-size:13px; color:#cbd5e1; margin-bottom:3px;">
                                • <?= htmlspecialchars($gy['nev']) ?>: <?= $gy['sorozat_db'] ?> × <?= $gy['ismetles'] ?> (<?= $gy['max_suly'] ?> kg)
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <a href="edit_workout.php?id=<?= $edzes['id'] ?>" class="btn btn-secondary btn-small">Szerkesztés</a>
                        <a href="delete_workout.php?id=<?= $edzes['id'] ?>" class="btn btn-danger" onclick="return confirm('Biztosan törlöd ezt az edzést?');">Törlés</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>