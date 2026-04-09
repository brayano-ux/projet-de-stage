<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, nom, description, date_creation FROM boutiques WHERE statut = 'active' ORDER BY date_creation DESC");
    $boutiques = $stmt->fetchAll();

    echo json_encode($boutiques);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>