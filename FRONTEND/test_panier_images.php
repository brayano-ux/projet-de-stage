<?php
// Test pour vérifier que le panier et les images fonctionnent
require_once __DIR__ . '/config/index.php';

$boutique_id = 1;

try {
    $pdo = DatabaseConfig::getConnection();
    
    // Récupérer les produits avec leurs images
    $stmt_produits = $pdo->prepare("
        SELECT p.*, 
               COALESCE(c.nom, 'Autres') as categorie_nom,
               COALESCE(c.icone, 'fas fa-box') as categorie_icone
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.boutique_id = ? AND p.statut = 'disponible' 
        ORDER BY p.date_ajout DESC
        LIMIT 5
    ");
    $stmt_produits->execute([$boutique_id]);
    $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Test Panier et Images</h1>";
    
    if (empty($produits)) {
        echo "<p>Aucun produit trouvé pour la boutique ID: $boutique_id</p>";
        exit;
    }
    
    echo "<h2>Produits trouvés: " . count($produits) . "</h2>";
    
    foreach ($produits as $index => $product) {
        echo "<div style='border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 10px;'>";
        echo "<h3>Produit #" . ($index + 1) . " - " . htmlspecialchars($product['nom']) . "</h3>";
        
        // Test de la logique d'image
        $image_url = '';
        $product_name = strtolower($product['nom'] ?? '');
        $categorie = strtolower($product['categorie_nom'] ?? 'autres');
        
        // 1. Image de la base de données si elle existe et est valide
        if (!empty($product['image']) && filter_var($product['image'], FILTER_VALIDATE_URL)) {
            $image_url = htmlspecialchars($product['image']);
            echo "<p><strong>Image BDD:</strong> <code>$image_url</code></p>";
        }
        // 2. Image Unsplash basée sur la catégorie
        elseif (strpos($product_name, 'airpod') !== false || strpos($product_name, 'casque') !== false || strpos($product_name, 'ecouteur') !== false) {
            $image_url = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop&auto=format';
            echo "<p><strong>Image Unsplash (Audio):</strong> <code>$image_url</code></p>";
        }
        elseif (strpos($product_name, 'phone') !== false || strpos($product_name, 'telephone') !== false || strpos($categorie, 'téléphonie') !== false) {
            $image_url = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=300&fit=crop&auto=format';
            echo "<p><strong>Image Unsplash (Téléphone):</strong> <code>$image_url</code></p>";
        }
        elseif (strpos($product_name, 'sac') !== false || strpos($categorie, 'accessoire') !== false) {
            $image_url = 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop&auto=format';
            echo "<p><strong>Image Unsplash (Accessoire):</strong> <code>$image_url</code></p>";
        }
        elseif (strpos($categorie, 'électronique') !== false) {
            $image_url = 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=400&h=300&fit=crop&auto=format';
            echo "<p><strong>Image Unsplash (Électronique):</strong> <code>$image_url</code></p>";
        }
        else {
            // Image par défaut selon l'ID du produit pour éviter les répétitions
            $seed = $product['id'] ?? 'default';
            $image_url = "https://picsum.photos/seed/{$seed}/400/300.jpg";
            echo "<p><strong>Image par défaut (Picsum):</strong> <code>$image_url</code></p>";
        }
        
        // Afficher l'image
        echo "<div style='margin: 10px 0;'>";
        echo "<img src='$image_url' alt='" . htmlspecialchars($product['nom']) . "' style='width: 200px; height: 150px; object-fit: cover; border-radius: 8px;' onerror=\"this.style.border='2px solid red'; this.alt='Image failed';\">";
        echo "</div>";
        
        // Afficher les autres informations
        echo "<p><strong>Prix:</strong> " . number_format($product['prix'], 0, ',', ' ') . " FCFA</p>";
        echo "<p><strong>Catégorie:</strong> " . htmlspecialchars($product['categorie_nom']) . "</p>";
        echo "<p><strong>Icône:</strong> <i class='" . htmlspecialchars($product['categorie_icone']) . "'></i> " . htmlspecialchars($product['categorie_icone']) . "</p>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars(substr($product['description'] ?? '', 0, 100)) . "...</p>";
        
        // Données pour le panier (test)
        $cart_data = [
            'id' => (int)$product['id'],
            'nom' => $product['nom'],
            'prix' => (float)$product['prix'],
            'image' => $image_url,
            'categorie' => $product['categorie_nom'] ?? 'Autres',
            'icon' => $product['categorie_icone'] ?? 'fas fa-box'
        ];
        
        echo "<p><strong>Données panier (JSON):</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px;'>";
        echo htmlspecialchars(json_encode($cart_data, JSON_PRETTY_PRINT));
        echo "</pre>";
        
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h2>Test du JavaScript</h2>";
    echo "<p>Pour tester le panier et les images:</p>";
    echo "<ol>";
    echo "<li><a href='index_fixed.php?boutique_id=1' target='_blank'>Ouvrir la boutique</a></li>";
    echo "<li>Cliquez sur les boutons '+' pour ajouter des produits au panier</li>";
    echo "<li>Vérifiez que les images s'affichent correctement</li>";
    echo "<li>Testez les quantités et la suppression</li>";
    echo "<li>Vérifiez que le panier se sauvegarde (rechargez la page)</li>";
    echo "</ol>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Fonctionnalités testées:</h3>";
    echo "<ul>";
    echo "<li>Images Unsplash selon les catégories</li>";
    echo "<li>Fallbacks Picsum Photos</li>";
    echo "<li>Gestion d'erreur d'image</li>";
    echo "<li>Panier localStorage</li>";
    echo "<li>Animations et interactions</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERREUR</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
