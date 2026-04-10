<?php
// Test de connexion exactement comme dans index.php
require_once __DIR__ . '/config/index.php';

echo "<h2>Debug du fichier index.php</h2>";

// Test 1: Connexion simple
echo "<h3>1. Test de connexion simple</h3>";
try {
    $pdo = DatabaseConfig::getConnection();
    echo "<p style='color: green;'>Connexion réussie</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
    die();
}

// Test 2: Simulation de la requête boutique
echo "<h3>2. Test de la requête boutique (ID=1)</h3>";
try {
    $boutique_id = 1;
    $stmt_boutique = $pdo->prepare("
        SELECT b.*,
               u.nom           AS proprietaire_nom,
               u.email         AS proprietaire_email,
               u.telephone     AS proprietaire_telephone,
               u.photo_profil  AS proprietaire_photo,
               u.bio           AS proprietaire_bio,
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
        echo "<p style='color: green;'>Boutique trouvée: " . htmlspecialchars($boutique['nom']) . "</p>";
        echo "<pre>" . print_r($boutique, true) . "</pre>";
    } else {
        echo "<p style='color: orange;'>Aucune boutique trouvée avec ID=1</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur requête boutique: " . $e->getMessage() . "</p>";
}

// Test 3: Vérification des tables
echo "<h3>3. Vérification des tables</h3>";
$tables = ['boutiques', 'utilisateurs', 'produits', 'commandes', 'favoris'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "<p style='color: blue;'>Table $table: $count enregistrements</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Table $table: Erreur - " . $e->getMessage() . "</p>";
    }
}

// Test 4: Vérification de la structure de la table boutiques
echo "<h3>4. Structure de la table boutiques</h3>";
try {
    $stmt = $pdo->query("DESCRIBE boutiques");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur structure: " . $e->getMessage() . "</p>";
}

// Test 5: Simulation exacte du code index.php
echo "<h3>5. Simulation exacte du code index.php</h3>";
$boutique_id = 1;
echo "<p>Boutique ID utilisé: $boutique_id</p>";

try {
    $pdo = DatabaseConfig::getConnection();
    echo "<p style='color: green;'>Connexion OK</p>";
    
    $stmt_boutique = $pdo->prepare("
        SELECT b.*,
               u.nom           AS proprietaire_nom,
               u.email         AS proprietaire_email,
               u.telephone     AS proprietaire_telephone,
               u.photo_profil  AS proprietaire_photo,
               u.bio           AS proprietaire_bio,
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
    
    if (!$boutique) {
        echo "<p style='color: orange;'>Boutique introuvable (simulation)</p>";
    } else {
        echo "<p style='color: green;'>Boutique trouvée (simulation)</p>";
        
        // Test des produits
        $stmt_produits = $pdo->prepare("
            SELECT * FROM produits
            WHERE boutique_id = ? AND statut = 'disponible'
            ORDER BY date_ajout DESC
        ");
        $stmt_produits->execute([$boutique_id]);
        $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: blue;'>Produits trouvés: " . count($produits) . "</p>";
        
        // Test des catégories
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
    }
    
    echo "<p style='color: green; font-weight: bold;'>Simulation RÉUSSIE !</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>Erreur PDO: " . $e->getMessage() . "</p>";
    echo "<p>Code d'erreur: " . $e->getCode() . "</p>";
    echo "<p>Fichier: " . $e->getFile() . " Ligne: " . $e->getLine() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Erreur générale: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Recommandations:</h3>";
echo "<p>Si la simulation réussit mais que index.php échoue, le problème pourrait être:</p>";
echo "<ul>";
echo "<li>Une erreur PHP avant la connexion (syntaxe, etc.)</li>";
echo "<li>Un problème avec les variables \$_GET</li>";
echo "<li>Une différence dans l'environnement d'exécution</li>";
echo "</ul>";
?>
