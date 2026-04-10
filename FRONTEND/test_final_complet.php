<?php
// Test final complet pour vérifier panier et images
require_once __DIR__ . '/config/index.php';

$boutique_id = 1;

try {
    $pdo = DatabaseConfig::getConnection();
    
    // Récupérer la boutique
    $stmt_boutique = $pdo->prepare("
        SELECT b.*, 
               u.nom as proprietaire_nom, 
               u.email as proprietaire_email,
               u.date_inscription as proprietaire_date_inscription
        FROM boutiques b 
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id 
        WHERE b.id = ? 
        LIMIT 1
    ");
    $stmt_boutique->execute([$boutique_id]);
    $boutique = $stmt_boutique->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les produits
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
    
    echo "<h1>Test Final Complet - Panier et Images</h1>";
    
    if (!$boutique) {
        echo "<p style='color: red;'>Boutique non trouvée</p>";
        exit;
    }
    
    echo "<h2>Informations Boutique</h2>";
    echo "<p><strong>Nom:</strong> " . htmlspecialchars($boutique['nom']) . "</p>";
    echo "<p><strong>Propriétaire:</strong> " . htmlspecialchars($boutique['proprietaire_nom']) . "</p>";
    echo "<p><strong>WhatsApp:</strong> " . htmlspecialchars($boutique['whatsapp']) . "</p>";
    
    echo "<h2>Produits avec Images: " . count($produits) . "</h2>";
    
    foreach ($produits as $index => $product) {
        echo "<div style='border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 10px; background: #f9f9f9;'>";
        echo "<h3>Produit #" . ($index + 1) . " - " . htmlspecialchars($product['nom']) . "</h3>";
        
        // Test de la logique d'image corrigée
        $image_url = '';
        $product_name = strtolower($product['nom'] ?? '');
        $categorie = strtolower($product['categorie_nom'] ?? 'autres');
        
        // 1. Image de la base de données (chemin relatif)
        if (!empty($product['image'])) {
            // Vérifier si c'est un chemin relatif ou une URL complète
            if (strpos($product['image'], 'http') === 0) {
                $image_url = htmlspecialchars($product['image']);
                echo "<p><strong>Image BDD (URL complète):</strong> <code>$image_url</code></p>";
            } else {
                // Chemin relatif depuis la racine du projet
                $image_url = '../' . htmlspecialchars($product['image']);
                echo "<p><strong>Image BDD (chemin relatif):</strong> <code>$image_url</code></p>";
                echo "<p><strong>Chemin original:</strong> <code>" . htmlspecialchars($product['image']) . "</code></p>";
            }
        }
        // 2. Image Unsplash basée sur la catégorie si aucune image en BDD
        elseif (strpos($product_name, 'airpod') !== false || strpos($product_name, 'casque') !== false || strpos($product_name, 'ecouteur') !== false) {
            $image_url = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop&auto=format';
            echo "<p><strong>Image Unsplash (Audio):</strong> <code>$image_url</code></p>";
        }
        else {
            // Image par défaut selon l'ID du produit
            $seed = $product['id'] ?? 'default';
            $image_url = "https://picsum.photos/seed/{$seed}/400/300.jpg";
            echo "<p><strong>Image par défaut:</strong> <code>$image_url</code></p>";
        }
        
        // Afficher l'image
        echo "<div style='margin: 15px 0;'>";
        echo "<img src='$image_url' alt='" . htmlspecialchars($product['nom']) . "' style='width: 200px; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;' onerror=\"this.style.border='2px solid red'; this.alt='Image failed to load';\">";
        echo "</div>";
        
        // Afficher les autres informations
        echo "<p><strong>Prix:</strong> " . number_format($product['prix'], 0, ',', ' ') . " FCFA</p>";
        echo "<p><strong>Catégorie:</strong> " . htmlspecialchars($product['categorie_nom']) . "</p>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars(substr($product['description'] ?? '', 0, 100)) . "...</p>";
        
        // Données pour le panier (test)
        $cart_data = [
            'id' => (int)$product['id'],
            'nom' => $product['nom'],
            'prix' => (float)$product['prix'],
            'image' => (!empty($product['image']) && strpos($product['image'], 'http') === 0) ? $product['image'] : '../' . ($product['image'] ?? ''),
            'categorie' => $product['categorie_nom'] ?? 'Autres',
            'icon' => $product['categorie_icone'] ?? 'fas fa-box'
        ];
        
        echo "<p><strong>Données panier:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px; max-height: 150px; overflow-y: auto;'>";
        echo htmlspecialchars(json_encode($cart_data, JSON_PRETTY_PRINT));
        echo "</pre>";
        
        echo "</div>";
    }
    
    // Variables JavaScript test
    $js_boutique_whatsapp = json_encode(preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? ''));
    $js_boutique_id = json_encode((int)$boutique_id);
    $js_boutique_name = json_encode($boutique['nom'] ?? '');
    $js_products = json_encode(array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'nom' => $p['nom'],
            'prix' => (float)$p['prix'],
            'image' => (!empty($p['image']) && strpos($p['image'], 'http') === 0) ? $p['image'] : '../' . ($p['image'] ?? ''),
            'categorie' => $p['categorie_nom'] ?? 'Autres',
            'icon' => $p['categorie_icone'] ?? 'fas fa-box'
        ];
    }, $produits));
    
    echo "<hr>";
    echo "<h2>Variables JavaScript</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>WHATSAPP_NUMBER:</strong> " . $js_boutique_whatsapp . "</p>";
    echo "<p><strong>BOUTIQUE_ID:</strong> " . $js_boutique_id . "</p>";
    echo "<p><strong>BOUTIQUE_NAME:</strong> " . $js_boutique_name . "</p>";
    echo "<p><strong>ALL_PRODUCTS:</strong> " . substr($js_products, 0, 200) . "...</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h2>Test de la Boutique</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h3>Corrections Appliquées:</h3>";
    echo "<ul style='text-align: left; max-width: 600px; margin: 0 auto;'>";
    echo "<li>Images BDD avec chemins relatifs corrigés</li>";
    echo "<li>Variables JavaScript définies correctement</li>";
    echo "<li>Fonction loadCartFromStorage au lieu de loadCart</li>";
    echo "<li>Données panier avec URLs d'images corrigées</li>";
    echo "<li>Fallbacks Unsplash et Picsum</li>";
    echo "</ul>";
    echo "<br>";
    echo "<a href='index_fixed.php?boutique_id=1' target='_blank' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block; margin: 10px;'>Tester la Boutique Complète</a>";
    echo "<br><br>";
    echo "<small>Ouvrez ce lien dans un nouvel onglet pour tester le panier et les images</small>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>ERREUR</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='color: red;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
