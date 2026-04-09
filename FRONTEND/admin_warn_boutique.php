<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$boutique_id = $input['boutique_id'] ?? null;
$reason = $input['reason'] ?? '';

if (!$boutique_id || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $pdo = getDB();
    // For now, just log or do nothing, since no warnings table
    // In future, could insert into a warnings table or send email
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>