<?php
// Test simple pour vérifier que index_fixed.php fonctionne maintenant
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Simple de index_fixed.php</h1>";

try {
    // Charger la configuration
    require_once __DIR__ . '/config/index.php';
    
    // Simuler les variables de index_fixed.php
    $boutique_id = 1;
    
    // Test de la requête corrigée
    $pdo = DatabaseConfig::getConnection();
    
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
        echo "<h2 style='color: green;'>SUCCESS ! Boutique trouvée</h2>";
        echo "<h3>Informations principales :</h3>";
        echo "<ul>";
        echo "<li><strong>Nom boutique:</strong> " . htmlspecialchars($boutique['nom']) . "</li>";
        echo "<li><strong>Propriétaire:</strong> " . htmlspecialchars($boutique['proprietaire_nom']) . "</li>";
        echo "<li><strong>Email:</strong> " . htmlspecialchars($boutique['proprietaire_email']) . "</li>";
        echo "<li><strong>WhatsApp:</strong> " . htmlspecialchars($boutique['whatsapp']) . "</li>";
        echo "<li><strong>Adresse:</strong> " . htmlspecialchars($boutique['adresse']) . "</li>";
        echo "<li><strong>Statut:</strong> " . htmlspecialchars($boutique['statut']) . "</li>";
        echo "<li><strong>Total commandes:</strong> " . $boutique['total_commandes'] . "</li>";
        echo "<li><strong>Total favoris:</strong> " . $boutique['total_favoris'] . "</li>";
        echo "<li><strong>Produits actifs:</strong> " . $boutique['produits_actifs'] . "</li>";
        echo "</ul>";
        
        echo "<h3>Test des produits :</h3>";
        $stmt_produits = $pdo->prepare("SELECT * FROM produits WHERE boutique_id = ? AND statut = 'disponible' ORDER BY date_ajout DESC LIMIT 5");
        $stmt_produits->execute([$boutique_id]);
        $produits = $stmt_produits->fetchAll();
        
        echo "Nombre de produits: " . count($produits) . "<br>";
        if (!empty($produits)) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Prix</th><th>Description</th></tr>";
            foreach ($produits as $p) {
                echo "<tr>";
                echo "<td>" . $p['id'] . "</td>";
                echo "<td>" . htmlspecialchars($p['nom']) . "</td>";
                echo "<td>" . number_format($p['prix'], 0, ',', ' ') . " FCFA</td>";
                echo "<td>" . htmlspecialchars(substr($p['description'], 0, 50)) . "...</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<hr>";
        echo "<h2>Test réussi !</h2>";
        echo "<p><a href='index_fixed.php?boutique_id=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Tester index_fixed.php MAINTENANT</a></p>";
        echo "<p><a href='index_fixed.php?boutique_id=1&debug=1' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Tester avec débogage</a></p>";
        
    } else {
        echo "<h2 style='color: red;'>ERREUR: Boutique non trouvée</h2>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERREUR</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
