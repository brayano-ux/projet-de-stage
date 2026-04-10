<?php
require_once __DIR__ . '/config/index.php';

echo "<h2>Structure de la table utilisateurs</h2>";

try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->query("DESCRIBE utilisateurs");
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
}
?>
