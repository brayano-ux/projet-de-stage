<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/index.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide']);
    exit;
}

$commandeId = intval($_POST['commande_id'] ?? 0);
$statut = trim($_POST['statut'] ?? '');
$statutsValides = ['nouveau', 'confirme', 'preparation', 'livre', 'annule'];

if ($commandeId <= 0 || !in_array($statut, $statutsValides, true)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    $pdo = DatabaseConfig::getConnection();
    // S'assurer que la commande appartient à l'utilisateur
    $stmt = $pdo->prepare(
        'SELECT c.id FROM commandes c
         JOIN boutiques b ON c.boutique_id = b.id
         WHERE c.id = ? AND b.utilisateur_id = ?'
    );
    $stmt->execute([$commandeId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Commande introuvable ou accès refusé']);
        exit;
    }

    $update = $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?');
    $update->execute([$statut, $commandeId]);

    echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
