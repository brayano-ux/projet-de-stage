<?php
require_once __DIR__ . '/config/index.php';

echo "<h2>Debug avec boutique_id=18</h2>";

$boutique_id = 18;

// Test 1: Vérifier si la boutique existe
echo "<h3>1. Test si la boutique #18 existe</h3>";
try {
    $pdo = DatabaseConfig::getConnection();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM boutiques WHERE id = ?");
    $stmt->execute([$boutique_id]);
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        echo "<p style='color: red;'>Boutique #18 n'existe pas !</p>";
        
        // Afficher les IDs disponibles
        $stmt = $pdo->query("SELECT id, nom FROM boutiques ORDER BY id");
        $boutiques = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Boutiques disponibles:</h4>";
        echo "<ul>";
        foreach ($boutiques as $b) {
            echo "<li>ID: " . $b['id'] . " - " . htmlspecialchars($b['nom']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: green;'>Boutique #18 existe</p>";
        
        // Test la requête complète
        echo "<h3>2. Test de la requête complète</h3>";
        try {
            $stmt_boutique = $pdo->prepare("
                SELECT b.*,
                       u.nom           AS proprietaire_nom,
                       u.email         AS proprietaire_email,
                       u.date_inscription AS proprietaire_date_inscription,
                       (SELECT COUNT(*) FROM commandes   WHERE boutique_id = b.id)                         AS total_commandes,
                       (SELECT COUNT(*) FROM favoris     WHERE boutique_id = b.id)                         AS total_favoris,
                       (SELECT COUNT(*) FROM produits    WHERE boutique_id = b.id AND statut = 'disponible') AS produits_actifs
                FROM boutiques b
                LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id
                WHERE b.id = ?
                LIMIT 1
            ");
            $stmt_boutique->execute([$boutique_id]);
            $boutique = $stmt_boutique->fetch(PDO::FETCH_ASSOC);
            
            if ($boutique) {
                echo "<p style='color: green;'>Requête boutique réussie</p>";
                echo "<pre>" . print_r($boutique, true) . "</pre>";
                
                // Test des produits
                echo "<h3>3. Test des produits</h3>";
                $stmt_produits = $pdo->prepare("
                    SELECT * FROM produits
                    WHERE boutique_id = ? AND statut = 'disponible'
                    ORDER BY date_ajout DESC
                ");
                $stmt_produits->execute([$boutique_id]);
                $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<p style='color: blue;'>Produits trouvés: " . count($produits) . "</p>";
                
                // Test des catégories
                echo "<h3>4. Test des catégories</h3>";
                $stmt_cats = $pdo->prepare("
                    SELECT categorie, COUNT(*) AS nb
                    FROM produits
                    WHERE boutique_id = ? AND statut = 'disponible' AND categorie IS NOT NULL AND categorie <> ''
                    GROUP BY categorie
                    ORDER BY nb DESC
                ");
                $stmt_cats->execute([$boutique_id]);
                $categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<p style='color: blue;'>Catégories trouvées: " . count($categories) . "</p>";
                
                if (count($categories) > 0) {
                    echo "<ul>";
                    foreach ($categories as $c) {
                        echo "<li>" . htmlspecialchars($c['categorie']) . " (" . $c['nb'] . ")</li>";
                    }
                    echo "</ul>";
                }
                
            } else {
                echo "<p style='color: orange;'>Requête boutique: aucun résultat</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erreur requête: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur générale: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Recommandation:</h3>";
echo "<p>Utilisez un ID qui existe, par exemple:</p>";
echo "<ul>";
echo "<li><a href='index.php?boutique_id=1'>index.php?boutique_id=1</a></li>";
echo "<li><a href='index.php?boutique_id=2'>index.php?boutique_id=2</a></li>";
echo "<li><a href='index.php?boutique_id=3'>index.php?boutique_id=3</a></li>";
echo "</ul>";
?>
