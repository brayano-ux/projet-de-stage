<?php
// Script de débogage pour identifier le problème
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic de la Boutique</h1>";

try {
    // Test de connexion à la base de données
    require_once __DIR__ . '/config/database.php';
    
    echo "<h2>1. Test de connexion à la base de données</h2>";
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo " <span style='color: green;'>Connexion réussie à la base de données</span><br>";

    // Test des tables
    echo "<h2>2. Vérification des tables</h2>";
    
    $tables = ['boutiques', 'utilisateurs', 'produits', 'categories'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "Table '$table': <span style='color: green;'>$count enregistrements</span><br>";
        } catch (Exception $e) {
            echo "Table '$table': <span style='color: red;'>Erreur - " . htmlspecialchars($e->getMessage()) . "</span><br>";
        }
    }

    // Test des boutiques disponibles
    echo "<h2>3. Boutiques disponibles</h2>";
    $stmt = $pdo->query("SELECT id, nom, statut, utilisateur_id FROM boutiques ORDER BY id");
    $boutiques = $stmt->fetchAll();
    
    if (empty($boutiques)) {
        echo "<span style='color: red;'>Aucune boutique trouvée dans la base de données</span><br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Statut</th><th>ID Utilisateur</th></tr>";
        foreach ($boutiques as $b) {
            echo "<tr>";
            echo "<td>" . $b['id'] . "</td>";
            echo "<td>" . htmlspecialchars($b['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($b['statut'] ?? 'non défini') . "</td>";
            echo "<td>" . $b['utilisateur_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test avec boutique_id = 1
    echo "<h2>4. Test avec boutique_id = 1</h2>";
    $boutique_id = 1;
    
    $stmt = $pdo->prepare("
        SELECT b.*, 
               u.nom as proprietaire_nom, 
               u.email as proprietaire_email,
               u.date_inscription as proprietaire_date_inscription
        FROM boutiques b 
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id 
        WHERE b.id = ?
        LIMIT 1
    ");
    $stmt->execute([$boutique_id]);
    $boutique = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($boutique) {
        echo "<span style='color: green;'>Boutique trouvée</span><br>";
        echo "<pre>" . print_r($boutique, true) . "</pre>";
        
        // Test des produits
        echo "<h2>5. Produits de cette boutique</h2>";
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE boutique_id = ? AND statut = 'disponible' ORDER BY date_ajout DESC");
        $stmt->execute([$boutique_id]);
        $produits = $stmt->fetchAll();
        
        echo "Nombre de produits: " . count($produits) . "<br>";
        if (!empty($produits)) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Prix</th><th>Statut</th></tr>";
            foreach ($produits as $p) {
                echo "<tr>";
                echo "<td>" . $p['id'] . "</td>";
                echo "<td>" . htmlspecialchars($p['nom']) . "</td>";
                echo "<td>" . $p['prix'] . "</td>";
                echo "<td>" . $p['statut'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<span style='color: red;'>Boutique ID 1 non trouvée</span><br>";
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERREUR</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='index_fixed.php?boutique_id=1'>Tester index_fixed.php avec boutique_id=1</a></p>";
echo "<p><a href='index_fixed.php?boutique_id=1&debug=1'>Tester index_fixed.php avec débogage</a></p>";
?>
