<?php
session_start();
require_once __DIR__ . '/config/index.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier si la boutique existe
    if (!isset($_GET['boutique_id']) || !is_numeric($_GET['boutique_id'])) {
        throw new Exception('ID boutique invalide');
    }
    
    $boutique_id = (int)$_GET['boutique_id'];
    
    // Connexion BDD via la configuration
    $pdo = DatabaseConfig::getConnection();
    
    // Récupérer l'adresse IP du visiteur
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Vérifier si cette IP a déjà visité cette boutique aujourd'hui
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM visiteurs_uniques 
        WHERE boutique_id = ? AND ip_address = ? AND DATE(date_visite) = CURDATE()
    ");
    $stmt->execute([$boutique_id, $ip_address]);
    $alreadyVisited = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    // Si c'est un nouveau visiteur pour aujourd'hui
    if (!$alreadyVisited) {
        // Ajouter le visiteur unique
        $stmt = $pdo->prepare("
            INSERT INTO visiteurs_uniques (boutique_id, ip_address, date_visite) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$boutique_id, $ip_address]);
        
        // Incrémenter le compteur de visiteurs uniques dans la boutique
        $stmt = $pdo->prepare("
            UPDATE boutiques 
            SET visiteurs_uniques = visiteurs_uniques + 1 
            WHERE id = ?
        ");
        $stmt->execute([$boutique_id]);
    }
    
    // Incrémenter le compteur total de vues (à chaque visite)
    $stmt = $pdo->prepare("
        UPDATE boutiques 
        SET total_vues = total_vues + 1 
        WHERE id = ?
    ");
    $stmt->execute([$boutique_id]);
    
    // Mettre à jour la table vues pour l'historique journalier
    $stmt = $pdo->prepare("
        INSERT INTO vues (shop_id, date_vue, nombre) 
        VALUES (?, CURDATE(), 1)
        ON DUPLICATE KEY UPDATE nombre = nombre + 1
    ");
    $stmt->execute([$boutique_id]);
    
    $response['success'] = true;
    $response['message'] = 'Visite enregistrée avec succès';
    $response['new_visitor'] = !$alreadyVisited;
    
} catch (Exception $e) {
    error_log("[tracker_visiteur] Erreur: " . $e->getMessage());
    $response['message'] = 'Erreur lors du suivi de la visite';
}

echo json_encode($response);
?>
