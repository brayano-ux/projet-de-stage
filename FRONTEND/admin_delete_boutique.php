<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$boutique_id = $input['boutique_id'] ?? null;

if (!$boutique_id) {
    echo json_encode(['success' => false, 'message' => 'ID de boutique manquant']);
    exit;
}

try {
    $pdo = getDB();
    // Delete related records first
    $pdo->prepare("DELETE FROM commandes WHERE boutique_id = ?")->execute([$boutique_id]);
    $pdo->prepare("DELETE FROM produits WHERE boutique_id = ?")->execute([$boutique_id]);
    $pdo->prepare("DELETE FROM favoris WHERE boutique_id = ?")->execute([$boutique_id]);
    $pdo->prepare("DELETE FROM vues_boutiques WHERE boutique_id = ?")->execute([$boutique_id]);
    $pdo->prepare("DELETE FROM visiteurs_uniques WHERE boutique_id = ?")->execute([$boutique_id]);
    // Then delete boutique
    $stmt = $pdo->prepare("DELETE FROM boutiques WHERE id = ?");
    $stmt->execute([$boutique_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Boutique non trouvée']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>