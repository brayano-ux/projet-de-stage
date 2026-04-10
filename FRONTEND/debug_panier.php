<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Panier</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-btn { 
            background: #007bff; color: white; 
            padding: 15px 30px; border: none; 
            border-radius: 8px; cursor: pointer; 
            margin: 10px; font-size: 16px;
        }
        .test-btn:hover { background: #0056b3; }
        .debug-info { 
            background: #f8f9fa; padding: 15px; 
            border-radius: 8px; margin: 10px 0; 
            border-left: 4px solid #007bff;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Debug Panier - Test JavaScript</h1>
    
    <div class="debug-info">
        <h3>Test de la fonction addToCart</h3>
        <p>Cliquez sur les boutons pour tester si le panier fonctionne :</p>
    </div>

    <button class="test-btn" onclick="testAddToCart1()">Ajouter Produit 1</button>
    <button class="test-btn" onclick="testAddToCart2()">Ajouter Produit 2</button>
    <button class="test-btn" onclick="showCart()">Voir Panier</button>
    <button class="test-btn" onclick="clearCart()">Vider Panier</button>

    <div id="debugOutput" class="debug-info" style="display: none;">
        <h4>Output Debug:</h4>
        <pre id="debugText"></pre>
    </div>

    <script>
        // Variables globales comme dans index_fixed.php
        let cart = [];
        const WHATSAPP_NUMBER = "237657300644";
        const BOUTIQUE_ID = 1;
        const BOUTIQUE_NAME = "brayan kameni";

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
            const debugDiv = document.getElementById('debugOutput');
            const debugText = document.getElementById('debugText');
            debugDiv.style.display = 'block';
            debugText.innerHTML += new Date().toLocaleTimeString() + ': ' + message + '\\n';
        }

        function updateCart() {
            console.log('Panier mis à jour:', cart);
            showToast(`Panier contient ${cart.length} produit(s) - Total: ${calculateTotal()}`);
        }

        function calculateTotal() {
            return cart.reduce((total, item) => total + (item.price * item.qty), 0);
        }

        // Fonction addToCart comme dans index_fixed.php
        function addToCart(btnElement, productData) {
            console.log('addToCart appelé avec:', productData);
            
            try {
                const existing = cart.find(i => i.id === productData.id);
                if (existing) {
                    existing.qty++;
                    console.log('Produit existant, quantité augmentée:', existing.qty);
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
                    console.log('Nouveau produit ajouté:', cart[cart.length - 1]);
                }
                
                updateCart();
                showToast(`${productData.nom} ajouté au panier`);
                
                // Animation du bouton
                if (btnElement) {
                    const btn = btnElement;
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '✓';
                    btn.style.background = '#28a745';
                    btn.style.transform = 'scale(1.1)';
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.style.background = '';
                        btn.style.transform = '';
                    }, 1000);
                }
                
            } catch (error) {
                console.error('Erreur dans addToCart:', error);
                showToast('ERREUR: ' + error.message);
            }
        }

        // Fonctions de test
        function testAddToCart1() {
            const product1 = {
                id: 1,
                nom: 'Airpods Pro',
                prix: 25000,
                image: '../uploads/produits/produit_69a76d69372bd8.91900972_1772580201.jpeg',
                categorie: 'Audio',
                icon: 'fas fa-headphones'
            };
            
            addToCart(null, product1);
        }

        function testAddToCart2() {
            const product2 = {
                id: 2,
                nom: 'iPhone 13',
                prix: 450000,
                image: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=300&fit=crop&auto=format',
                categorie: 'Téléphonie',
                icon: 'fas fa-mobile-alt'
            };
            
            addToCart(null, product2);
        }

        function showCart() {
            console.log('Contenu du panier:', cart);
            let output = '=== CONTENU PANIER ===\\n';
            cart.forEach((item, index) => {
                output += `${index + 1}. ${item.name} - ${formatPrice(item.price)} x ${item.qty} = ${formatPrice(item.price * item.qty)}\\n`;
            });
            output += `\\nTOTAL: ${formatPrice(calculateTotal())}`;
            
            const debugDiv = document.getElementById('debugOutput');
            const debugText = document.getElementById('debugText');
            debugDiv.style.display = 'block';
            debugText.innerHTML = output;
        }

        function clearCart() {
            cart = [];
            updateCart();
            showToast('Panier vidé');
        }

        // Test automatique au chargement
        window.addEventListener('load', function() {
            console.log('Page chargée, test automatique...');
            showToast('Page de debug chargée');
        });
    </script>
</body>
</html>
