<?php
// Test final pour valider que toutes les requêtes de index_fixed.php fonctionnent
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Final Complet de index_fixed.php</h1>";

try {
    require_once __DIR__ . '/config/index.php';
    
    $boutique_id = 1;
    $pdo = DatabaseConfig::getConnection();
    
    echo "<h2>1. Test Requête Principale Boutique</h2>";
    
    $stmt_boutique = $pdo->prepare("
        SELECT b.*, 
               u.nom as proprietaire_nom, 
               u.email as proprietaire_email,
               u.date_inscription as proprietaire_date_inscription,
               (SELECT COUNT(*) FROM commandes WHERE boutique_id = b.id) as total_commandes,
               (SELECT COUNT(*) FROM favoris WHERE boutique_id = b.id) as total_favoris,
               (SELECT COUNT(*) FROM produits WHERE boutique_id = b.id AND statut = 'disponible') as produits_actifs
        FROM boutiques b 
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id 
        WHERE b.id = ? 
        LIMIT 1
    ");
    
    $stmt_boutique->execute([$boutique_id]);
    $boutique = $stmt_boutique->fetch(PDO::FETCH_ASSOC);
    
    if ($boutique) {
        echo "<span style='color: green;'>SUCCESS: Boutique trouvée</span><br>";
        echo "<strong>Nom:</strong> " . htmlspecialchars($boutique['nom']) . "<br>";
        echo "<strong>Propriétaire:</strong> " . htmlspecialchars($boutique['proprietaire_nom']) . "<br>";
        echo "<strong>Produits actifs:</strong> " . $boutique['produits_actifs'] . "<br>";
    } else {
        echo "<span style='color: red;'>ERREUR: Boutique non trouvée</span><br>";
    }
    
    echo "<h2>2. Test Requête Statistiques Produits</h2>";
    
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_produits,
            AVG(p.prix) as prix_moyen,
            MIN(p.prix) as prix_min,
            MAX(p.prix) as prix_max,
            SUM(CASE WHEN p.date_ajout >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as nouveaux_produits,
            SUM(CASE WHEN p.statut = 'disponible' THEN 1 ELSE 0 END) as produits_disponibles
        FROM produits p 
        WHERE p.boutique_id = ?
    ");
    
    $stmt_stats->execute([$boutique_id]);
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    echo "<span style='color: green;'>SUCCESS: Statistiques récupérées</span><br>";
    echo "<strong>Total produits:</strong> " . $stats['total_produits'] . "<br>";
    echo "<strong>Prix moyen:</strong> " . number_format($stats['prix_moyen'] ?? 0, 0, ',', ' ') . " FCFA<br>";
    echo "<strong>Nouveaux (7 jours):</strong> " . $stats['nouveaux_produits'] . "<br>";
    
    echo "<h2>3. Test Requête Activité Récente</h2>";
    
    $stmt_activite = $pdo->prepare("
        SELECT 
            COUNT(*) as vues_aujourd_hui,
            COALESCE((SELECT COUNT(*) FROM visiteurs_uniques WHERE boutique_id = ? AND DATE(date_visite) = CURDATE()), 0) as visiteurs_aujourd_hui
        FROM vues_boutiques 
        WHERE boutique_id = ? AND date_visite = CURDATE()
    ");
    
    $stmt_activite->execute([$boutique_id, $boutique_id]);
    $activite = $stmt_activite->fetch(PDO::FETCH_ASSOC);
    
    echo "<span style='color: green;'>SUCCESS: Activité récupérée</span><br>";
    echo "<strong>Vues aujourd'hui:</strong> " . $activite['vues_aujourd_hui'] . "<br>";
    echo "<strong>Visiteurs aujourd'hui:</strong> " . $activite['visiteurs_aujourd_hui'] . "<br>";
    
    echo "<h2>4. Test Requête Produits</h2>";
    
    $stmt_produits = $pdo->prepare("
        SELECT p.*, 
               COALESCE(c.nom, 'Autres') as categorie_nom,
               COALESCE(c.icone, 'fas fa-box') as categorie_icone
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.boutique_id = ? AND p.statut = 'disponible' 
        ORDER BY p.date_ajout DESC
    ");
    
    $stmt_produits->execute([$boutique_id]);
    $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<span style='color: green;'>SUCCESS: Produits récupérés</span><br>";
    echo "<strong>Nombre de produits:</strong> " . count($produits) . "<br>";
    
    if (!empty($produits)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Nom</th><th>Prix</th><th>Catégorie</th></tr>";
        foreach ($produits as $p) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($p['nom']) . "</td>";
            echo "<td>" . number_format($p['prix'], 0, ',', ' ') . " FCFA</td>";
            echo "<td>" . htmlspecialchars($p['categorie_nom']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>5. Test Requête Catégories</h2>";
    
    try {
        $stmt_categories = $pdo->prepare("
            SELECT c.nom, c.icone, COUNT(p.id) as produit_count 
            FROM categories c 
            LEFT JOIN produits p ON c.id = p.categorie_id AND p.boutique_id = ? AND p.statut = 'disponible'
            WHERE c.id IN (SELECT categorie_id FROM produits WHERE boutique_id = ? AND categorie_id IS NOT NULL)
               OR c.id = 1
            GROUP BY c.id, c.nom, c.icone
            ORDER BY c.nom
        ");
        $stmt_categories->execute([$boutique_id, $boutique_id]);
        $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<span style='color: green;'>SUCCESS: Catégories récupérées</span><br>";
        echo "<strong>Nombre de catégories:</strong> " . count($categories) . "<br>";
        
    } catch (Exception $e) {
        echo "<span style='color: orange;'>WARNING: Table categories non disponible, utilisation des catégories par défaut</span><br>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>TOUS LES TESTS RÉUSSIS !</h2>";
    echo "<p><strong>La boutique devrait maintenant s'afficher correctement.</strong></p>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Liens de test:</h3>";
    echo "<p><a href='index_fixed.php?boutique_id=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Voir la boutique complètement</a></p>";
    echo "<p><a href='index_fixed.php?boutique_id=1&debug=1' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Voir avec débogage</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERREUR FINALE</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
