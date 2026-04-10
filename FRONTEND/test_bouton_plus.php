<?php
// Test pour vérifier si le bouton + fonctionne
require_once __DIR__ . '/config/index.php';

$boutique_id = 1;

try {
    $pdo = DatabaseConfig::getConnection();
    
    // Récupérer un produit pour le test
    $stmt_produits = $pdo->prepare("
        SELECT p.*, 
               COALESCE(c.nom, 'Autres') as categorie_nom,
               COALESCE(c.icone, 'fas fa-box') as categorie_icone
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.boutique_id = ? AND p.statut = 'disponible' 
        ORDER BY p.date_ajout DESC
        LIMIT 1
    ");
    $stmt_produits->execute([$boutique_id]);
    $produit = $stmt_produits->fetch(PDO::FETCH_ASSOC);
    
    if (!$produit) {
        echo "<h1>Aucun produit trouvé pour tester</h1>";
        exit;
    }
    
    echo "<h1>Test Bouton Plus - Produit: " . htmlspecialchars($produit['nom']) . "</h1>";
    
    // Définir les variables JavaScript comme dans index_fixed.php
    $js_boutique_whatsapp = json_encode(preg_replace('/[^0-9]/', '', $produit['whatsapp'] ?? ''));
    $js_boutique_id = json_encode((int)$boutique_id);
    $js_boutique_name = json_encode('Test Boutique');
    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bouton Plus</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .product-card { 
            border: 1px solid #ddd; padding: 20px; 
            border-radius: 10px; max-width: 400px; 
            margin: 20px auto; text-align: center;
        }
        .product-img { 
            width: 200px; height: 150px; 
            margin: 0 auto 15px; border-radius: 8px; 
            overflow: hidden; background: #f5f5f5;
        }
        .product-img img { 
            width: 100%; height: 100%; 
            object-fit: cover; 
        }
        .add-btn { 
            background: #007bff; color: white; 
            border: none; padding: 15px 20px; 
            border-radius: 50%; cursor: pointer; 
            font-size: 18px; margin-top: 15px;
            transition: all 0.3s;
        }
        .add-btn:hover { 
            background: #0056b3; transform: scale(1.1); 
        }
        .debug-info { 
            background: #f8f9fa; padding: 15px; 
            border-radius: 8px; margin: 20px 0; 
            border-left: 4px solid #007bff;
        }
        .cart-status { 
            background: #e8f5e8; padding: 15px; 
            border-radius: 8px; margin: 20px 0;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="product-card">
        <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
        <div class="product-img">
            <?php 
            $image_url = '';
            if (!empty($produit['image'])) {
                if (strpos($produit['image'], 'http') === 0) {
                    $image_url = $produit['image'];
                } else {
                    $image_url = '../' . $produit['image'];
                }
            } else {
                $image_url = "https://picsum.photos/seed/{$produit['id']}/400/300.jpg";
            }
            ?>
            <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($produit['nom']); ?>" 
                 onerror="this.src='https://picsum.photos/seed/fallback<?php echo $produit['id']; ?>/400/300.jpg';">
        </div>
        <p><strong>Prix:</strong> <?php echo number_format($produit['prix'], 0, ',', ' '); ?> FCFA</p>
        <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($produit['categorie_nom'] ?? 'Autres'); ?></p>
        
        <button class="add-btn" onclick="addToCart(this, <?php echo htmlspecialchars(json_encode([
            'id' => (int)$produit['id'],
            'nom' => $produit['nom'],
            'prix' => (float)$produit['prix'],
            'image' => (!empty($produit['image']) && strpos($produit['image'], 'http') === 0) ? $produit['image'] : '../' . ($produit['image'] ?? ''),
            'categorie' => $produit['categorie_nom'] ?? 'Autres',
            'icon' => $produit['categorie_icone'] ?? 'fas fa-box'
        ])); ?>)">
            +
        </button>
    </div>

    <div class="debug-info">
        <h3>Debug Information</h3>
        <p><strong>Cliquez sur le bouton + ci-dessus pour tester</strong></p>
        <p>Ouvrez la console du navigateur (F12) pour voir les messages de debug</p>
    </div>

    <div class="cart-status" id="cartStatus">
        <h3>Statut du Panier</h3>
        <p>Articles: <span id="cartCount">0</span></p>
        <p>Total: <span id="cartTotal">0 FCFA</span></p>
        <div id="cartItems"></div>
    </div>

    <script>
        // Variables exactement comme dans index_fixed.php
        const WHATSAPP_NUMBER = <?php echo $js_boutique_whatsapp; ?>;
        const BOUTIQUE_ID = <?php echo $js_boutique_id; ?>;
        const BOUTIQUE_NAME = <?php echo $js_boutique_name; ?>;
        
        let cart = [];
        
        console.log('Variables initialisées:', {
            WHATSAPP_NUMBER,
            BOUTIQUE_ID,
            BOUTIQUE_NAME
        });

        // Fonctions utilitaires
        function formatPrice(price) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'XOF',
                minimumFractionDigits: 0
            }).format(price).replace('XOF', 'FCFA');
        }

        function showToast(message) {
            console.log('Toast:', message);
            const statusDiv = document.getElementById('cartStatus');
            const toastDiv = document.createElement('div');
            toastDiv.style.cssText = 'background: #28a745; color: white; padding: 10px; border-radius: 5px; margin: 10px 0;';
            toastDiv.textContent = message;
            statusDiv.appendChild(toastDiv);
            setTimeout(() => toastDiv.remove(), 3000);
        }

        // Fonction addToCart exactement comme dans index_fixed.php
        function addToCart(btnElement, productData) {
            console.log('=== addToCart appelé ===');
            console.log('productData:', productData);
            console.log('Panier avant:', cart);
            
            try {
                const existing = cart.find(i => i.id === productData.id);
                if (existing) {
                    existing.qty++;
                    console.log('Produit existant, quantité:', existing.qty);
                } else {
                    cart.push({
                        id: productData.id,
                        name: productData.nom,
                        price: parseFloat(productData.prix),
                        qty: 1,
                        image: productData.image,
                        icon: productData.icon,
                        categorie: productData.categorie
                    });
                    console.log('Nouveau produit ajouté');
                }
                
                console.log('Panier après ajout:', cart);
                updateCart();
                showToast(`${productData.nom} ajouté au panier`);
                
                // Animation du bouton
                const btn = btnElement;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '×';
                btn.style.background = '#28a745';
                btn.style.transform = 'scale(1.1)';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.background = '';
                    btn.style.transform = '';
                }, 1000);
                
            } catch (error) {
                console.error('Erreur dans addToCart:', error);
                showToast('ERREUR: ' + error.message);
            }
        }

        // Fonction updateCart simplifiée pour le test
        function updateCart() {
            console.log('=== updateCart appelé ===');
            console.log('Panier:', cart);
            
            const cartCount = document.getElementById('cartCount');
            const cartTotal = document.getElementById('cartTotal');
            const cartItems = document.getElementById('cartItems');
            
            if (!cartCount || !cartTotal || !cartItems) {
                console.error('Éléments DOM non trouvés!');
                return;
            }
            
            let total = 0;
            let count = 0;
            let html = '';
            
            cart.forEach(item => {
                const itemTotal = item.price * item.qty;
                total += itemTotal;
                count += item.qty;
                
                html += `<div style="border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px;">
                    <strong>${item.name}</strong> - ${formatPrice(item.price)} x ${item.qty} = ${formatPrice(itemTotal)}
                </div>`;
            });
            
            cartCount.textContent = count;
            cartTotal.textContent = formatPrice(total);
            cartItems.innerHTML = html || '<p>Panier vide</p>';
            
            console.log('Panier mis à jour:', { count, total });
        }

        // Test au chargement
        window.addEventListener('load', function() {
            console.log('Page chargée, prêt pour les tests');
            updateCart();
        });
    </script>
</body>
</html>

<?php
} catch (Exception $e) {
    echo "<h1 style='color: red;'>ERREUR</h1>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
