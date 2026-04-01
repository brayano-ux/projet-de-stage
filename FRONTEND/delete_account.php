<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

require_once __DIR__ . '/config/index.php';

$user_id = $_SESSION['user_id'];
$confirmation = $_POST['confirmation'] ?? '';
$password = $_POST['password'] ?? '';

// L'utilisateur doit confirmer avec "OUI"
if ($confirmation !== 'OUI') {
    echo json_encode(['success' => false, 'message' => 'Confirmation invalide']);
    exit;
}

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe est requis']);
    exit;
}

try {
    $pdo = DatabaseConfig::getConnection();
    
    // Vérifier le mot de passe
    $check = $pdo->prepare('SELECT mot_de_passe FROM utilisateurs WHERE id = ?');
    $check->execute([$user_id]);
    $user = $check->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        exit;
    }
    
    // Supprimer le compte et toutes ses données
    $pdo->beginTransaction();
    
    // Récupérer la boutique pour supprimer les produits et QR codes
    $boutique_query = $pdo->prepare('SELECT id FROM boutiques WHERE utilisateur_id = ?');
    $boutique_query->execute([$user_id]);
    $boutique = $boutique_query->fetch(PDO::FETCH_ASSOC);
    
    if ($boutique) {
        // Supprimer les commandes
        $pdo->prepare('DELETE FROM commandes WHERE boutique_id = ?')->execute([$boutique['id']]);
        
        // Supprimer les produits
        $pdo->prepare('DELETE FROM produits WHERE boutique_id = ?')->execute([$boutique['id']]);
        
        // Supprimer la boutique
        $pdo->prepare('DELETE FROM boutiques WHERE id = ?')->execute([$boutique['id']]);
    }
    
    // Supprimer les favoris
    $pdo->prepare('DELETE FROM favoris WHERE utilisateur_id = ?')->execute([$user_id]);
    
    // Supprimer l'utilisateur
    $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?')->execute([$user_id]);
    
    $pdo->commit();
    
    // Détruire la session
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Compte supprimé avec succès']);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
