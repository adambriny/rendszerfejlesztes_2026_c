<?php
require 'db.php';
bejelentkezesEllenorzes();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM edzesek WHERE id = ? AND felhasznalo_id = ?");
    $stmt->execute([(int)$_GET['id'], $_SESSION['felhasznalo_id']]);
}

header("Location: workouts.php");
exit;
?>