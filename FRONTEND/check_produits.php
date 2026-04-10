<?php
require_once __DIR__ . '/config/index.php';

echo "<h2>Structure de la table produits</h2>";

try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->query("DESCRIBE produits");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Colonnes disponibles:</h3>";
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li style='color: blue;'><code>" . $col['Field'] . "</code> - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
    
    // Vérifier les produits de la boutique #18
    echo "<h3>Produits de la boutique #18:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE boutique_id = 18 AND statut = 'disponible'");
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($produits)) {
        echo "<p style='color: orange;'>Aucun produit trouvé pour la boutique #18</p>";
    } else {
        echo "<p style='color: green;'>Produits trouvés: " . count($produits) . "</p>";
        foreach ($produits as $p) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>ID:</strong> " . $p['id'] . "<br>";
            echo "<strong>Nom:</strong> " . htmlspecialchars($p['nom']) . "<br>";
            echo "<strong>Prix:</strong> " . number_format($p['prix'], 0, ',', ' ') . " FCFA<br>";
            echo "<strong>Statut:</strong> " . $p['statut'] . "<br>";
            echo "<strong>Date ajout:</strong> " . $p['date_ajout'] . "<br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
}
?>
