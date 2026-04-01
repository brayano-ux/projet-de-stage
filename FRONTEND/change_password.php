<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

require_once __DIR__ . '/config/index.php';

$user_id = $_SESSION['user_id'];
$ancien_pwd = $_POST['ancien_pwd'] ?? '';
$nouveau_pwd = $_POST['nouveau_pwd'] ?? '';
$confirmer_pwd = $_POST['confirmer_pwd'] ?? '';

// Validation
if (empty($ancien_pwd) || empty($nouveau_pwd) || empty($confirmer_pwd)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

if ($nouveau_pwd !== $confirmer_pwd) {
    echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
    exit;
}

if (strlen($nouveau_pwd) < 6) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
    exit;
}

try {
    $pdo = DatabaseConfig::getConnection();
    
    // Vérifier l'ancien mot de passe
    $check = $pdo->prepare('SELECT mot_de_passe FROM utilisateurs WHERE id = ?');
    $check->execute([$user_id]);
    $user = $check->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($ancien_pwd, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Ancien mot de passe incorrect']);
        exit;
    }
    
    // Mettre à jour le mot de passe
    $update = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?');
    $update->execute([password_hash($nouveau_pwd, PASSWORD_DEFAULT), $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Mot de passe changé avec succès']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
