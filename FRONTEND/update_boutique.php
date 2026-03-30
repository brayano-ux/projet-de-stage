<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/index.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$services = trim($_POST['services'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($nom === '') {
    echo json_encode(['success' => false, 'message' => 'Nom de boutique requis']);
    exit;
}

try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->prepare('SELECT id FROM boutiques WHERE utilisateur_id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $boutique = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$boutique) {
        echo json_encode(['success' => false, 'message' => 'Boutique introuvable']);
        exit;
    }

    $update = $pdo->prepare('UPDATE boutiques SET nom = ?, adresse = ?, whatsapp = ?, services = ?, description = ? WHERE id = ?');
    $update->execute([$nom, $adresse, $whatsapp, $services, $description, $boutique['id']]);

    echo json_encode(['success' => true, 'message' => 'Boutique mise à jour']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
