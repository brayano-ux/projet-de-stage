<?php
require_once __DIR__ . '/config/index.php';

$boutique_id = $_GET['boutique_id'] ?? 1;

try {
    $pdo = DatabaseConfig::getConnection();
    
    // Requête simple sans sous-requêtes
    $stmt = $pdo->prepare("SELECT * FROM boutiques WHERE id = ? LIMIT 1");
    $stmt->execute([$boutique_id]);
    $boutique = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$boutique) {
        die("Boutique #$boutique_id non trouvée");
    }
    
    echo "<h1>Boutique: " . htmlspecialchars($boutique['nom']) . "</h1>";
    echo "<p>ID: " . $boutique['id'] . "</p>";
    echo "<p>Description: " . htmlspecialchars($boutique['description'] ?? 'Non définie') . "</p>";
    echo "<p>WhatsApp: " . htmlspecialchars($boutique['whatsapp'] ?? 'Non défini') . "</p>";
    echo "<p>Adresse: " . htmlspecialchars($boutique['adresse'] ?? 'Non définie') . "</p>";
    echo "<hr>";
    
    // Produits simples
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE boutique_id = ? AND statut = 'disponible' LIMIT 5");
    $stmt->execute([$boutique_id]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Produits (" . count($produits) . ")</h2>";
    foreach ($produits as $p) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>" . htmlspecialchars($p['nom']) . "</strong><br>";
        echo "Prix: " . number_format($p['prix'], 0, ',', ' ') . " FCFA<br>";
        echo "Catégorie: " . htmlspecialchars($p['categorie'] ?? 'Non définie') . "<br>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
?>
