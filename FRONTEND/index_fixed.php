<?php
require_once __DIR__ . '/config/index.php';

// Récupération du boutique_id depuis l'URL
$boutique_id = $_GET['boutique_id'] ?? null;

// Si aucun boutique_id, essayer de récupérer depuis la session ou utiliser une valeur par défaut
if (!$boutique_id) {
    // Pour le développement, vous pouvez définir un ID par défaut
    // En production, vous devriez rediriger vers une page de sélection de boutique
    $boutique_id = 5; // ID par défaut pour le développement
}

try {
    $pdo = DatabaseConfig::getConnection();

    // Récupération TOUTES les informations complètes de la boutique et du vendeur
    $stmt_boutique = $pdo->prepare("
        SELECT b.*, 
               u.nom as proprietaire_nom, 
               u.email as proprietaire_email,
               u.date_inscription as proprietaire_date_inscription,
               (SELECT COUNT(*) FROM commandes WHERE boutique_id = b.id) as total_commandes,
               (SELECT COUNT(*) FROM favoris WHERE boutique_id = b.id) as total_favoris,
               (SELECT COUNT(*) FROM produits WHERE boutique_id = b.id AND statut = 'disponible') as produits_actifs
        FROM boutiques b 
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id 
        WHERE b.id = ? 
        LIMIT 1
    ");
    $stmt_boutique->execute([$boutique_id]);
    $boutique = $stmt_boutique->fetch(PDO::FETCH_ASSOC);

    if (!$boutique) {
        if (defined('ENV_DEV') && ENV_DEV) {
            // Afficher les boutiques disponibles pour le débogage
            $debug_stmt = $pdo->query("SELECT id, nom, statut FROM boutiques ORDER BY id");
            $boutiques_dispo = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div style='background: #fff3cd; color: #856404; padding: 20px; margin: 20px; border-radius: 8px; border: 1px solid #ffeaa7;'>";
            echo "<h3>Boutique introuvable (Développement)</h3>";
            echo "<p><strong>ID recherché:</strong> " . htmlspecialchars($boutique_id) . "</p>";
            echo "<h4>Boutiques disponibles:</h4>";
            echo "<ul>";
            foreach ($boutiques_dispo as $b) {
                echo "<li>ID: " . $b['id'] . " - Nom: " . htmlspecialchars($b['nom']) . " - Statut: " . htmlspecialchars($b['statut'] ?? 'non défini') . "</li>";
            }
            echo "</ul>";
            echo "<p><strong>Solution:</strong> Essayez avec un ID valide ci-dessus</p>";
            echo "</div>";
        } else {
            http_response_code(404);
        }
        die("Boutique introuvable.");
    }
    
    // Mode débogage : afficher les informations de la boutique
    if (defined('ENV_DEV') && ENV_DEV && isset($_GET['debug'])) {
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 20px; margin: 20px; border-radius: 8px; border: 1px solid #bee5eb;'>";
        echo "<h3>Informations de la boutique (Débogage)</h3>";
        echo "<pre>" . print_r($boutique, true) . "</pre>";
        echo "</div>";
    }

    // Récupération des produits de cette boutique (sans jointure categories pour éviter l'erreur)
    $stmt_produits = $pdo->prepare("
        SELECT p.*, 
               COALESCE(c.nom, 'Autres') as categorie_nom,
               COALESCE(c.icone, 'fas fa-box') as categorie_icone
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id
        WHERE p.boutique_id = ? AND p.statut = 'disponible' 
        ORDER BY p.date_ajout DESC
    ");
    $stmt_produits->execute([$boutique_id]);
    $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des catégories disponibles (si la table existe)
    try {
        $stmt_categories = $pdo->prepare("
            SELECT c.nom, c.icone, COUNT(p.id) as produit_count 
            FROM categories c 
            LEFT JOIN produits p ON c.id = p.categorie_id AND p.boutique_id = ? AND p.statut = 'disponible'
            WHERE c.id IN (SELECT categorie_id FROM produits WHERE boutique_id = ? AND categorie_id IS NOT NULL)
               OR c.id = 1 -- Inclure toujours la catégorie par défaut
            GROUP BY c.id, c.nom, c.icone
            ORDER BY c.nom
        ");
        $stmt_categories->execute([$boutique_id, $boutique_id]);
        $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Si la table categories n'existe pas, créer des catégories par défaut
        $categories = [
            ['nom' => 'Tout voir', 'icone' => 'fas fa-th', 'produit_count' => count($produits)],
            ['nom' => 'Électronique', 'icone' => 'fas fa-laptop', 'produit_count' => 0],
            ['nom' => 'Accessoires', 'icone' => 'fas fa-gem', 'produit_count' => 0],
            ['nom' => 'Audio', 'icone' => 'fas fa-headphones', 'produit_count' => 0],
            ['nom' => 'Téléphonie', 'icone' => 'fas fa-mobile-alt', 'produit_count' => 0]
        ];
        
        // Compter les produits par catégorie basée sur le nom
        foreach ($produits as $produit) {
            $nom = strtolower($produit['nom']);
            if (strpos($nom, 'airpod') !== false || strpos($nom, 'casque') !== false || strpos($nom, 'ecouteur') !== false) {
                $categories[2]['produit_count']++; // Audio
            } elseif (strpos($nom, 'sac') !== false || strpos($nom, 'bijoux') !== false) {
                $categories[1]['produit_count']++; // Accessoires
            } elseif (strpos($nom, 'phone') !== false || strpos($nom, 'telephone') !== false) {
                $categories[3]['produit_count']++; // Téléphonie
            } else {
                $categories[0]['produit_count']++; // Tout voir
            }
        }
    }

    // Statistiques complètes et avancées de la boutique
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_produits,
            AVG(p.prix) as prix_moyen,
            MIN(p.prix) as prix_min,
            MAX(p.prix) as prix_max,
            SUM(CASE WHEN p.date_ajout >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as nouveaux_produits,
            SUM(CASE WHEN p.statut = 'disponible' THEN 1 ELSE 0 END) as produits_disponibles
        FROM produits p 
        WHERE p.boutique_id = ?
    ");
    $stmt_stats->execute([$boutique_id]);
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
    // Activité récente de la boutique
    $stmt_activite = $pdo->prepare("
        SELECT 
            COUNT(*) as vues_aujourd_hui,
            COALESCE((SELECT COUNT(*) FROM visiteurs_uniques WHERE boutique_id = ? AND DATE(date_visite) = CURDATE()), 0) as visiteurs_aujourd_hui
        FROM vues_boutiques 
        WHERE boutique_id = ? AND date_visite = CURDATE()
    ");
    $stmt_activite->execute([$boutique_id, $boutique_id]);
    $activite = $stmt_activite->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error in index_fixed.php: " . $e->getMessage());
    
    // En développement, afficher l'erreur détaillée
    if (defined('ENV_DEV') && ENV_DEV) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
        echo "<h3>Erreur de base de données (Développement)</h3>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Fichier:</strong> index_fixed.php</p>";
        echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
        echo "<details><summary>Stack Trace</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
        echo "<hr>";
        echo "<h4>Solutions possibles:</h4>";
        echo "<ul>";
        echo "<li>Vérifiez que la base de données est accessible</li>";
        echo "<li>Vérifiez que les tables existent (boutiques, utilisateurs, produits)</li>";
        echo "<li>Vérifiez que la boutique_id existe et est active</li>";
        echo "</ul>";
        echo "</div>";
        die();
    } else {
        // En production, message générique
        die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
    }
}

// Fonctions utilitaires
function getProductIcon($categorie) {
    $icons = [
        'visage' => 'fas fa-spa',
        'maquillage' => 'fas fa-palette', 
        'cheveux' => 'fas fa-cut',
        'corps' => 'fas fa-hand-sparkles',
        'parfum' => 'fas fa-spray-can',
        'accessoire' => 'fas fa-gem',
        'électronique' => 'fas fa-laptop',
        'audio' => 'fas fa-headphones',
        'téléphonie' => 'fas fa-mobile-alt',
        'autres' => 'fas fa-box'
    ];
    return $icons[strtolower($categorie)] ?? 'fas fa-box';
}

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

function getProductBadge($product) {
    if (isset($product['badge']) && $product['badge']) {
        return '<span class="product-badge ' . htmlspecialchars($product['badge']) . '">' . htmlspecialchars($product['badge'] ?? '') . '</span>';
    }
    
    // Badge automatique basé sur la date d'ajout
    $days_ago = (time() - strtotime($product['date_ajout'])) / (24 * 60 * 60);
    if ($days_ago <= 7) {
        return '<span class="product-badge new">Nouveau</span>';
    }
    
    return '';
}

// Préparation des données pour JavaScript
$js_products = json_encode(array_map(function($product) {
    return [
        'id' => (int)$product['id'],
        'nom' => $product['nom'],
        'description' => $product['description'] ?? '',
        'prix' => (float)$product['prix'],
        'image' => $product['image'] ?? 'https://via.placeholder.com/400x300',
        'categorie' => $product['categorie_nom'] ?? 'Autres',
        'icone' => $product['categorie_icone'] ?? getProductIcon($product['categorie_nom'] ?? 'Autres'),
        'badge' => $product['badge'] ?? ''
    ];
}, $produits));

$js_boutique_whatsapp = json_encode(preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? ''));
$js_boutique_name = json_encode(htmlspecialchars($boutique['nom']));
$js_boutique_id = json_encode($boutique_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($boutique['nom']); ?> - Boutique Premium | <?php echo htmlspecialchars($boutique['proprietaire_nom']); ?></title>
<meta name="description" content="<?php echo htmlspecialchars(substr($boutique['description'] ?? 'Découvrez notre boutique premium', 0, 160)); ?>">
<meta name="keywords" content="boutique, <?php echo htmlspecialchars($boutique['nom']); ?>, <?php echo htmlspecialchars($boutique['services'] ?? 'produits premium'); ?>">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root {
    --primary: #C8706A;
    --primary-light: #F5E6E4;
    --primary-dark: #8B3E3A;
    --secondary: #C9A86C;
    --secondary-light: #F7F0E6;
    --secondary-dark: #8B6B35;
    --cream: #FAF6F1;
    --dark: #1A1110;
    --mid: #4A3530;
    --muted: #9A7B74;
    --white: #FFFFFF;
    --success: #10B981;
    --warning: #F59E0B;
    --error: #EF4444;
    --info: #3B82F6;
    --border: rgba(201,168,108,0.25);
    --shadow: 0 8px 40px rgba(26,17,16,0.12);
    --gradient-primary: linear-gradient(135deg, var(--primary), var(--secondary));
    --gradient-secondary: linear-gradient(135deg, var(--secondary), var(--primary-light));
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Jost', sans-serif;
    background: var(--cream);
    color: var(--dark);
    overflow-x: hidden;
    line-height: 1.6;
  }
  
  /* Animations globales */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }
  
  @keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-50px); }
    to { opacity: 1; transform: translateX(0); }
  }
  
  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
  }
  
  .animate-fade-in { animation: fadeInUp 0.8s ease-out; }
  .animate-pulse { animation: pulse 2s infinite; }
  .animate-slide-in { animation: slideInLeft 0.6s ease-out; }
  .animate-float { animation: float 3s ease-in-out infinite; }

  /* NAV */
  nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
    background: rgba(250,246,241,0.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
    padding: 0 5%;
    display: flex; align-items: center; justify-content: space-between;
    height: 70px;
  }
  .nav-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem; font-weight: 600;
    color: var(--dark); text-decoration: none;
    letter-spacing: 0.02em;
  }
  .nav-logo span { color: var(--rose); }
  .nav-links { display: flex; gap: 2rem; list-style: none; }
  .nav-links a {
    text-decoration: none; color: var(--mid);
    font-size: 0.85rem; font-weight: 400; letter-spacing: 0.08em;
    text-transform: uppercase; transition: color 0.3s;
  }
  .nav-links a:hover { color: var(--rose); }
  .nav-right { display: flex; align-items: center; gap: 1rem; }
  .cart-btn {
    position: relative; background: var(--dark); color: var(--cream);
    border: none; cursor: pointer;
    padding: 10px 20px; border-radius: 30px;
    font-family: 'Jost', sans-serif; font-size: 0.82rem;
    font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase;
    display: flex; align-items: center; gap: 8px;
    transition: background 0.3s, transform 0.2s;
  }
  .cart-btn:hover { background: var(--rose); transform: translateY(-1px); }
  .cart-count {
    background: var(--gold); color: var(--dark);
    border-radius: 50%; width: 20px; height: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 600;
    transition: transform 0.3s;
  }
  .cart-count.bump { transform: scale(1.4); }
  .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; background: none; border: none; padding: 4px; }
  .hamburger span { display: block; width: 22px; height: 1.5px; background: var(--dark); transition: 0.3s; }

  /* HERO */
  .hero {
    min-height: 100vh;
    display: grid; grid-template-columns: 1fr 1fr;
    padding-top: 70px;
    position: relative; overflow: hidden;
  }
  .hero-left {
    display: flex; flex-direction: column;
    justify-content: center; padding: 6% 5% 6% 7%;
    position: relative; z-index: 2;
  }
  .hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--gold-light); border: 1px solid var(--border);
    border-radius: 30px; padding: 6px 16px;
    font-size: 0.75rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--gold-dark); margin-bottom: 2rem;
    width: fit-content;
  }
  .hero-badge::before { content: 'X'; font-size: 0.6rem; }
  .hero h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2.8rem, 5vw, 4.5rem);
    font-weight: 300; line-height: 1.1;
    color: var(--dark); margin-bottom: 1.5rem;
    letter-spacing: -0.01em;
  }
  .hero h1 em { font-style: italic; color: var(--rose); }
  .hero p {
    font-size: 1rem; font-weight: 300; color: var(--muted);
    line-height: 1.8; max-width: 440px; margin-bottom: 2.5rem;
  }
  .hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
  .btn-primary {
    background: var(--rose); color: var(--white);
    padding: 14px 32px; border-radius: 40px;
    text-decoration: none; font-size: 0.85rem;
    font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase;
    transition: background 0.3s, transform 0.2s;
    display: inline-block;
  }
  .btn-primary:hover { background: var(--rose-dark); transform: translateY(-2px); }
  .btn-outline {
    border: 1px solid var(--gold); color: var(--gold-dark);
    padding: 14px 32px; border-radius: 40px;
    text-decoration: none; font-size: 0.85rem;
    font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase;
    transition: all 0.3s;
    display: inline-block;
  }
  .btn-outline:hover { background: var(--gold); color: var(--white); }
  .hero-right {
    position: relative; overflow: hidden;
    background: linear-gradient(135deg, #E8D5D0 0%, #D4B5AF 40%, #C8A09A 100%);
  }
  .hero-img-overlay {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 1rem;
  }
  .hero-circle {
    width: min(380px, 85%); aspect-ratio: 1;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    position: relative;
  }
  .hero-circle::before {
    content: ''; position: absolute; inset: 15px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
  }
  .hero-icon-center {
    font-size: 5rem; z-index: 1;
    filter: drop-shadow(0 4px 20px rgba(0,0,0,0.1));
    animation: float 3s ease-in-out infinite;
  }
  @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
  .hero-stats {
    position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
    display: flex; gap: 2.5rem;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(12px);
    padding: 1.2rem 2.5rem; border-radius: 60px;
    border: 2px solid rgba(255,255,255,0.6);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  }
  .stat { text-align: center; transition: transform 0.3s; }
  .stat:hover { transform: translateY(-3px); }
  .stat-num { font-family:'Cormorant Garamond',serif; font-size:1.6rem; font-weight:700; color:var(--primary); display: block; margin-bottom: 4px; }
  .stat-label { font-size:0.75rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-weight:500; }
  
  /* Header Vendeur */
  .vendeur-header {
    background: var(--gradient-primary);
    color: white;
    padding: 3rem 7%;
    position: relative;
    overflow: hidden;
  }
  .vendeur-header::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
  }
  .vendeur-content {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 3rem;
    align-items: center;
    position: relative;
    z-index: 2;
  }
  .vendeur-logo {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    overflow: hidden;
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .vendeur-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .vendeur-info h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    line-height: 1.2;
  }
  .vendeur-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    opacity: 0.9;
  }
  .meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .vendeur-description {
    font-size: 1rem;
    line-height: 1.6;
    opacity: 0.95;
    max-width: 600px;
  }
  .vendeur-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .btn-vendeur {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    padding: 12px 24px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(10px);
  }
  .btn-vendeur:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }
  .btn-vendeur.primary {
    background: white;
    color: var(--primary);
    border-color: white;
  }
  .btn-vendeur.primary:hover {
    background: var(--primary-light);
    color: white;
  }
  
  /* Stats Cards */
  .stats-section {
    padding: 4rem 7%;
    background: var(--cream);
  }
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
  }
  .stat-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border: 1px solid var(--border);
  }
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  }
  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
  }
  .stat-icon.primary { background: var(--primary-light); color: var(--primary); }
  .stat-icon.secondary { background: var(--secondary-light); color: var(--secondary); }
  .stat-icon.success { background: rgba(16,185,129,0.1); color: var(--success); }
  .stat-icon.warning { background: rgba(245,158,11,0.1); color: var(--warning); }
  .stat-number {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
  }
  .stat-title {
    font-size: 0.9rem;
    color: var(--muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .stat-change {
    font-size: 0.8rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
  }
  .stat-change.positive { color: var(--success); }
  .stat-change.negative { color: var(--error); }
  
  /* Contact Section */
  .contact-section {
    padding: 5rem 7%;
    background: var(--white);
  }
  .contact-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 4rem;
    max-width: 1200px;
    margin: 0 auto;
  }
  .contact-info {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }
  .contact-card {
    display: flex;
    gap: 1.5rem;
    padding: 2rem;
    background: var(--cream);
    border-radius: 20px;
    border: 1px solid var(--border);
    transition: all 0.3s;
  }
  .contact-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }
  .contact-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
  }
  .contact-details h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
    color: var(--dark);
    margin-bottom: 0.5rem;
  }
  .contact-details p {
    color: var(--muted);
    line-height: 1.6;
    margin-bottom: 1rem;
  }
  .contact-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  .contact-actions {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }
  .action-card {
    background: var(--cream);
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    border: 1px solid var(--border);
    transition: all 0.3s;
  }
  .action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }
  .action-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
  }
  .action-icon.whatsapp { background: #25D366; }
  .action-icon.qr { background: var(--secondary); }
  .action-icon.share { background: var(--primary); }
  .action-card h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    color: var(--dark);
    margin-bottom: 0.5rem;
  }
  .action-card p {
    color: var(--muted);
    margin-bottom: 1.5rem;
    line-height: 1.5;
  }
  .qr-preview {
    margin-top: 1rem;
  }
  .qr-preview img {
    max-width: 120px;
    border-radius: 10px;
    border: 2px solid var(--border);
  }
  
  /* Footer */
  .footer-section {
    background: var(--dark);
    color: var(--muted);
    padding: 3rem 7% 1rem;
  }
  .footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto 2rem;
  }
  .footer-brand h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.8rem;
    color: var(--white);
    margin-bottom: 1rem;
  }
  .footer-brand p {
    line-height: 1.6;
    margin-bottom: 1.5rem;
    opacity: 0.8;
  }
  .footer-social {
    display: flex;
    gap: 1rem;
  }
  .social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    text-decoration: none;
    transition: all 0.3s;
  }
  .social-link:hover {
    background: var(--primary);
    transform: translateY(-2px);
  }
  .footer-links h4,
  .footer-info h4 {
    color: var(--white);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1rem;
  }
  .footer-links ul,
  .footer-info ul {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  .footer-links a {
    color: var(--muted);
    text-decoration: none;
    transition: color 0.3s;
  }
  .footer-links a:hover {
    color: var(--primary);
  }
  .footer-info li {
    color: var(--muted);
    font-size: 0.85rem;
    line-height: 1.5;
  }
  .footer-info strong {
    color: var(--white);
  }
  .status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .status-badge.active {
    background: rgba(16,185,129,0.2);
    color: var(--success);
  }
  .footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 2rem;
    text-align: center;
    font-size: 0.8rem;
  }
  .footer-bottom p {
    margin-bottom: 0.5rem;
    opacity: 0.7;
  }
  
  /* QR Modal */
  .qr-modal {
    position: fixed;
    inset: 0;
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  }
  .qr-modal.show {
    opacity: 1;
    visibility: visible;
  }
  .qr-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(5px);
  }
  .qr-modal-content {
    position: relative;
    background: var(--white);
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    transform: scale(0.9);
    transition: transform 0.3s ease;
  }
  .qr-modal.show .qr-modal-content {
    transform: scale(1);
  }
  .qr-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--cream);
    border: 1px solid var(--border);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    color: var(--muted);
  }
  .qr-modal-close:hover {
    background: var(--primary);
    color: var(--white);
    transform: rotate(90deg);
  }
  .qr-modal-content h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
    color: var(--dark);
    margin-bottom: 1.5rem;
  }
  .qr-modal-image {
    margin-bottom: 1.5rem;
  }
  .qr-modal-image img {
    max-width: 200px;
    border-radius: 15px;
    border: 3px solid var(--border);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  }
  .qr-modal-content p {
    color: var(--muted);
    margin-bottom: 2rem;
    line-height: 1.5;
  }
  .deco-line {
    position: absolute; left: 7%; top: 50%;
    transform: translateY(-50%);
    width: 2px; height: 120px;
    background: linear-gradient(to bottom, transparent, var(--gold), transparent);
  }

  /* MARQUEE */
  .marquee-strip {
    background: var(--dark); color: var(--gold);
    padding: 12px 0; overflow: hidden;
    font-size: 0.78rem; letter-spacing: 0.15em; text-transform: uppercase;
    white-space: nowrap;
  }
  .marquee-inner { display: inline-flex; animation: marquee 20s linear infinite; }
  .marquee-inner span { padding: 0 2rem; }
  .marquee-inner .dot { color: var(--rose); }
  @keyframes marquee { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }

  /* SECTION HEADERS */
  .section-header { text-align: center; margin-bottom: 3.5rem; }
  .section-tag {
    display: inline-block; font-size: 0.72rem; letter-spacing: 0.16em;
    text-transform: uppercase; color: var(--gold-dark);
    margin-bottom: 0.8rem;
  }
  .section-tag::before, .section-tag::after { content: ' X '; font-size: 0.55rem; }
  .section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4vw, 3rem); font-weight: 300;
    color: var(--dark); line-height: 1.15;
  }
  .section-title em { font-style: italic; color: var(--rose); }
  .section-sub {
    margin-top: 0.8rem; font-size: 0.95rem; color: var(--muted);
    font-weight: 300; max-width: 520px; margin-left: auto; margin-right: auto;
    line-height: 1.7;
  }

  /* CATEGORIES */
  .categories { padding: 6rem 7%; background: var(--white); }
  .cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
  }
  .cat-card {
    border: 1px solid var(--border); border-radius: 16px;
    padding: 2rem 1.5rem; text-align: center; cursor: pointer;
    transition: all 0.35s; background: var(--cream);
    text-decoration: none; color: inherit;
    display: block;
  }
  .cat-card:hover, .cat-card.active {
    background: var(--rose-light); border-color: var(--rose);
    transform: translateY(-4px);
  }
  .cat-icon { font-size: 2.2rem; margin-bottom: 1rem; display: block; }
  .cat-name { font-family:'Cormorant Garamond',serif; font-size:1.1rem; font-weight:600; color:var(--dark); }
  .cat-count { font-size:0.75rem; color:var(--muted); margin-top:4px; }

  /* PRODUCTS */
  .products { padding: 6rem 7%; background: var(--cream); }
  .products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 2rem;
  }
  .product-card {
    background: var(--white); border-radius: 20px;
    border: 1px solid var(--border);
    overflow: hidden; transition: all 0.35s;
    position: relative;
  }
  .product-card:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
  .product-card.hidden { display: none; }
  .product-img {
    width: 100%; aspect-ratio: 4/3;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  }
  .product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease, filter 0.3s ease;
  }
  .product-card:hover .product-img img {
    transform: scale(1.1);
    filter: brightness(1.05);
  }
  .product-badge {
    position: absolute; top: 12px; left: 12px;
    background: var(--rose); color: var(--white);
    font-size: 0.68rem; padding: 4px 10px;
    border-radius: 20px; font-weight: 500;
    letter-spacing: 0.06em; text-transform: uppercase;
  }
  .product-badge.new { background: var(--gold); color: var(--dark); }
  .product-badge.promo { background: var(--rose-dark); }
  .product-body { padding: 1.25rem; }
  .product-cat {
    font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 6px;
  }
  .product-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem; font-weight: 600; color: var(--dark);
    margin-bottom: 6px; line-height: 1.3;
  }
  .product-desc { font-size: 0.82rem; color: var(--muted); line-height: 1.6; margin-bottom: 1rem; }
  .product-footer { display: flex; align-items: center; justify-content: space-between; }
  .product-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem; font-weight: 600; color: var(--rose);
  }
  .product-price small {
    font-size: 0.75rem; color: var(--muted);
    text-decoration: line-through; margin-left: 6px;
    font-family: 'Jost', sans-serif; font-weight: 300;
  }
  .add-btn {
    background: var(--dark); color: var(--white);
    border: none; cursor: pointer;
    width: 40px; height: 40px; border-radius: 50%;
    font-size: 1.2rem; display: flex; align-items: center; justify-content: center;
    transition: background 0.3s, transform 0.2s;
  }
  .add-btn:hover { background: var(--rose); transform: scale(1.1); }
  .no-products {
    grid-column: 1/-1; text-align: center;
    padding: 4rem; color: var(--muted);
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.4rem; font-style: italic;
  }

  /* CART DRAWER */
  .cart-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    z-index: 2000; opacity: 0; pointer-events: none;
    transition: opacity 0.3s;
  }
  .cart-overlay.open { opacity: 1; pointer-events: all; }
  .cart-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: min(420px, 100vw);
    background: var(--cream); z-index: 2001;
    transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25,0.46,0.45,0.94);
    display: flex; flex-direction: column;
    box-shadow: -8px 0 40px rgba(0,0,0,0.15);
  }
  .cart-drawer.open { transform: translateX(0); }
  .cart-drawer-header {
    padding: 1.5rem 1.5rem 1rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
  }
  .cart-drawer-header h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem; font-weight: 600; color: var(--dark);
  }
  .cart-close {
    background: none; border: 1px solid var(--border);
    cursor: pointer; color: var(--mid);
    width: 36px; height: 36px; border-radius: 50%;
    font-size: 1.1rem; display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
  }
  .cart-close:hover { background: var(--rose); color: white; border-color: var(--rose); }
  .cart-items { flex: 1; overflow-y: auto; padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
  .cart-empty {
    flex: 1; display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 1rem; padding: 3rem;
    color: var(--muted); text-align: center;
  }
  .cart-empty-icon { font-size: 3.5rem; opacity: 0.4; }
  .cart-empty p { font-family:'Cormorant Garamond',serif; font-size:1.1rem; font-style:italic; }
  .cart-item {
    background: var(--white); border: 1px solid var(--border);
    border-radius: 14px; padding: 1rem;
    display: flex; align-items: center; gap: 1rem;
    animation: slideIn 0.3s ease;
  }
  @keyframes slideIn { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }
  .cart-item-icon { 
    font-size: 2rem; 
    flex-shrink: 0; 
    width: 56px; 
    height: 56px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    background: var(--primary-light); 
    border-radius: 10px;
    overflow: hidden;
  }
  .cart-item-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .cart-item-info { flex: 1; min-width: 0; }
  .cart-item-name { font-weight: 500; font-size: 0.9rem; color: var(--dark); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .cart-item-price { font-family:'Cormorant Garamond',serif; font-size: 1rem; color: var(--rose); font-weight: 600; }
  .cart-item-controls { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
  .qty-btn {
    width: 26px; height: 26px; border-radius: 50%;
    border: 1px solid var(--border); background: none;
    cursor: pointer; font-size: 0.9rem; display: flex;
    align-items: center; justify-content: center;
    color: var(--mid); transition: all 0.2s;
  }
  .qty-btn:hover { background: var(--rose); color: white; border-color: var(--rose); }
  .qty-display { font-size: 0.85rem; font-weight: 500; color: var(--dark); min-width: 20px; text-align: center; }
  .cart-remove { background: none; border: none; cursor: pointer; color: var(--muted); font-size: 1rem; padding: 4px; transition: color 0.2s; flex-shrink: 0; }
  .cart-remove:hover { color: var(--rose); }
  .cart-footer { padding: 1rem 1.5rem 1.5rem; border-top: 1px solid var(--border); }
  .cart-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
  .cart-total-label { font-size: 0.85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }
  .cart-total-amount { font-family:'Cormorant Garamond',serif; font-size: 1.6rem; font-weight: 600; color: var(--dark); }
  .checkout-btn {
    width: 100%; padding: 16px;
    background: #25D366; color: white; border: none;
    border-radius: 14px; cursor: pointer;
    font-family: 'Jost', sans-serif; font-size: 0.9rem; font-weight: 500;
    letter-spacing: 0.05em; display: flex; align-items: center;
    justify-content: center; gap: 10px;
    transition: all 0.3s;
  }
  .checkout-btn:hover { background: #128C7E; transform: translateY(-1px); }
  .checkout-btn:disabled { background: var(--muted); cursor: not-allowed; transform: none; }
  .checkout-btn svg { width: 20px; height: 20px; }
  .cart-note { font-size: 0.72rem; color: var(--muted); text-align: center; margin-top: 0.75rem; line-height: 1.5; }

  /* TOAST */
  .toast {
    position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(100px);
    background: var(--dark); color: white;
    padding: 12px 24px; border-radius: 50px;
    font-size: 0.85rem; z-index: 3000;
    transition: transform 0.3s; white-space: nowrap;
    display: flex; align-items: center; gap: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  }
  .toast.show { transform: translateX(-50%) translateY(0); }
  .toast .toast-icon { font-size: 1rem; }

  /* MOBILE */
  @media (max-width: 900px) {
    nav { padding: 0 5%; }
    .nav-links { display: none; }
    .hamburger { display: flex; }
    .hero { grid-template-columns: 1fr; }
    .hero-right { min-height: 340px; }
    .hero-left { padding: 3rem 5% 2rem; }
    .categories { padding: 4rem 5%; }
    .products { padding: 4rem 5%; }
    .nav-mobile {
      display: none; position: fixed; inset: 70px 0 0;
      background: var(--cream); z-index: 999; flex-direction: column;
      padding: 2rem 5%; gap: 1.5rem; list-style: none;
      border-top: 1px solid var(--border);
    }
    .nav-mobile.open { display: flex; }
    .nav-mobile a { font-size: 1.2rem; color: var(--dark); text-decoration: none; font-weight: 400; }
  }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="#"><span><?php echo htmlspecialchars($boutique['nom'] ?? 'Ma Boutique'); ?></span></a>
  <ul class="nav-links">
    <li><a href="#categories">Catégories</a></li>
    <li><a href="#produits">Produits</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="nav-right">
    <button class="cart-btn" onclick="toggleCart()">
      <i class="fas fa-shopping-bag"></i>
      Panier
      <span class="cart-count" id="cartCount">0</span>
    </button>
    <button class="hamburger" onclick="toggleMobile()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<ul class="nav-mobile" id="navMobile">
  <li><a href="#categories" onclick="closeMobile()">Catégories</a></li>
  <li><a href="#produits" onclick="closeMobile()">Produits</a></li>
  <li><a href="#contact" onclick="closeMobile()">Contact</a></li>
</ul>

<!-- HEADER VENDEUR AMÉLIORÉ -->
<header class="vendeur-header animate-fade-in">
  <div class="vendeur-content">
    <div class="vendeur-logo animate-slide-in">
      <?php if ($boutique['logo']): ?>
        <img src="<?php echo htmlspecialchars($boutique['logo']); ?>" alt="Logo <?php echo htmlspecialchars($boutique['nom']); ?>">
      <?php else: ?>
        <i class="fas fa-store" style="font-size: 3rem; color: white;"></i>
      <?php endif; ?>
    </div>
    
    <div class="vendeur-info animate-fade-in">
      <h1><?php echo htmlspecialchars($boutique['nom']); ?></h1>
      <div class="vendeur-meta">
        <div class="meta-item">
          <i class="fas fa-user"></i>
          <span><?php echo htmlspecialchars($boutique['proprietaire_nom'] ?? 'Vendeur'); ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-calendar"></i>
          <span>Membre depuis <?php echo date('M Y', strtotime($boutique['proprietaire_date_inscription'] ?? $boutique['date_creation'])); ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-check-circle"></i>
          <span>Boutique vérifiée</span>
        </div>
        <div class="meta-item">
          <i class="fas fa-map-marker-alt"></i>
          <span><?php echo htmlspecialchars($boutique['adresse'] ?? 'Localisation non spécifiée'); ?></span>
        </div>
      </div>
      <p class="vendeur-description">
        <?php echo nl2br(htmlspecialchars($boutique['description'] ?? 'Découvrez notre boutique premium et notre sélection exclusive de produits de qualité.')); ?>
      </p>
    </div>
    
    <div class="vendeur-actions animate-fade-in">
      <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? ''); ?>" class="btn-vendeur primary" target="_blank">
        <i class="fab fa-whatsapp"></i>
        Contacter via WhatsApp
      </a>
      <a href="#produits" class="btn-vendeur">
        <i class="fas fa-shopping-bag"></i>
        Voir les produits
      </a>
      <?php if ($boutique['qrcode']): ?>
      <a href="#" class="btn-vendeur" onclick="showQRCode()">
        <i class="fas fa-qrcode"></i>
        QR Code
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- STATISTIQUES AVANCÉES -->
<section class="stats-section">
  <div class="stats-grid">
    <div class="stat-card animate-fade-in" style="animation-delay: 0.1s;">
      <div class="stat-icon primary">
        <i class="fas fa-box"></i>
      </div>
      <div class="stat-number"><?php echo number_format($stats['total_produits'] ?? 0, 0, '', ' '); ?></div>
      <div class="stat-title">Produits</div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i>
        <span><?php echo $stats['nouveaux_produits'] ?? 0; ?> nouveaux cette semaine</span>
      </div>
    </div>
    
    <div class="stat-card animate-fade-in" style="animation-delay: 0.2s;">
      <div class="stat-icon secondary">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <div class="stat-number"><?php echo number_format($boutique['total_commandes'] ?? 0, 0, '', ' '); ?></div>
      <div class="stat-title">Commandes</div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i>
        <span>En croissance</span>
      </div>
    </div>
    
    <div class="stat-card animate-fade-in" style="animation-delay: 0.3s;">
      <div class="stat-icon success">
        <i class="fas fa-eye"></i>
      </div>
      <div class="stat-number"><?php echo number_format($boutique['total_vues'] ?? $activite['vues_aujourd_hui'] ?? 0, 0, '', ' '); ?></div>
      <div class="stat-title">Vues aujourd'hui</div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i>
        <span><?php echo $activite['visiteurs_aujourd_hui'] ?? 0; ?> visiteurs uniques</span>
      </div>
    </div>
    
    <div class="stat-card animate-fade-in" style="animation-delay: 0.4s;">
      <div class="stat-icon warning">
        <i class="fas fa-heart"></i>
      </div>
      <div class="stat-number"><?php echo number_format($boutique['total_favoris'] ?? 0, 0, '', ' '); ?></div>
      <div class="stat-title">Favoris</div>
      <div class="stat-change positive">
        <i class="fas fa-arrow-up"></i>
        <span>Appréciée</span>
      </div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-strip" aria-hidden="true">
  <div class="marquee-inner">
    <span>Produits Premium</span><span class="dot">X</span>
    <span>Livraison Disponible</span><span class="dot">X</span>
    <span>Commande via WhatsApp</span><span class="dot">X</span>
    <span>Qualité Garantie</span><span class="dot">X</span>
    <span>Service Client</span><span class="dot">X</span>
    <span>Produits Premium</span><span class="dot">X</span>
    <span>Livraison Disponible</span><span class="dot">X</span>
    <span>Commande via WhatsApp</span><span class="dot">X</span>
    <span>Qualité Garantie</span><span class="dot">X</span>
    <span>Service Client</span><span class="dot">X</span>
  </div>
</div>

<!-- CATEGORIES -->
<section class="categories" id="categories">
  <div class="section-header">
    <div class="section-tag">Nos Univers</div>
    <h2 class="section-title">Explorez nos <em>catégories</em></h2>
    <p class="section-sub">Découvrez notre sélection de produits organisés par catégories pour faciliter votre recherche.</p>
  </div>
  <div class="cat-grid">
    <a class="cat-card active" onclick="filterProducts('all')" href="#produits">
      <span class="cat-icon"><i class="fas fa-th"></i></span>
      <div class="cat-name">Tout voir</div>
      <div class="cat-count"><?php echo count($produits); ?> produits</div>
    </a>
    <?php if (!empty($categories)): ?>
      <?php foreach ($categories as $categorie): ?>
        <?php if ($categorie['nom'] !== 'Tout voir'): ?>
        <a class="cat-card" onclick="filterProducts('<?php echo strtolower(htmlspecialchars($categorie['nom'])); ?>')" href="#produits">
          <span class="cat-icon"><i class="<?php echo htmlspecialchars($categorie['icone'] ?? 'fas fa-box'); ?>"></i></span>
          <div class="cat-name"><?php echo htmlspecialchars($categorie['nom']); ?></div>
          <div class="cat-count"><?php echo $categorie['produit_count']; ?> produit<?php echo $categorie['produit_count'] > 1 ? 's' : ''; ?></div>
        </a>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- PRODUCTS -->
<section class="products" id="produits">
  <div class="section-header">
    <div class="section-tag">Notre Boutique</div>
    <h2 class="section-title">Nos <em>produits</em> phares</h2>
    <p class="section-sub">Des produits soigneusement sélectionnés pour répondre à vos besoins.</p>
  </div>
  <div class="products-grid" id="productsGrid">
    <?php if (empty($produits)): ?>
        <div class="no-products">
          <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
          <p>Aucun produit disponible pour le moment.</p>
          <small>Revenez bientôt pour découvrir nos nouveautés !</small>
        </div>
    <?php else: ?>
        <?php foreach ($produits as $product): ?>
            <div class="product-card" 
                 data-cat="<?php echo strtolower(htmlspecialchars($product['categorie_nom'] ?? 'all')); ?>" 
                 data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
              <div class="product-img">
                <?php 
                // Gestion améliorée des images avec fallbacks
                $image_url = '';
                $product_name = strtolower($product['nom'] ?? '');
                $categorie = strtolower($product['categorie_nom'] ?? 'autres');
                
                // 1. Image de la base de données si elle existe et est valide
                if (!empty($product['image']) && filter_var($product['image'], FILTER_VALIDATE_URL)) {
                    $image_url = htmlspecialchars($product['image']);
                }
                // 2. Image Unsplash basée sur la catégorie
                elseif (strpos($product_name, 'airpod') !== false || strpos($product_name, 'casque') !== false || strpos($product_name, 'ecouteur') !== false) {
                    $image_url = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop&auto=format';
                }
                elseif (strpos($product_name, 'phone') !== false || strpos($product_name, 'telephone') !== false || strpos($categorie, 'téléphonie') !== false) {
                    $image_url = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=300&fit=crop&auto=format';
                }
                elseif (strpos($product_name, 'sac') !== false || strpos($categorie, 'accessoire') !== false) {
                    $image_url = 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop&auto=format';
                }
                elseif (strpos($categorie, 'électronique') !== false) {
                    $image_url = 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=400&h=300&fit=crop&auto=format';
                }
                else {
                    // Image par défaut selon l'ID du produit pour éviter les répétitions
                    $seed = $product['id'] ?? 'default';
                    $image_url = "https://picsum.photos/seed/{$seed}/400/300.jpg";
                }
                ?>
                <img src="<?php echo $image_url; ?>" 
                     alt="<?php echo htmlspecialchars($product['nom']); ?>" 
                     loading="lazy"
                     onerror="this.onerror=null; this.src='https://picsum.photos/seed/fallback<?php echo $product['id'] ?? 'default'; ?>/400/300.jpg';">
                <?php echo getProductBadge($product); ?>
              </div>
              <div class="product-body">
                <div class="product-cat"><?php echo htmlspecialchars($product['categorie_nom'] ?? 'Autres'); ?></div>
                <div class="product-name"><?php echo htmlspecialchars($product['nom']); ?></div>
                <div class="product-desc"><?php echo htmlspecialchars($product['description'] ?? 'Découvrez ce produit exceptionnel.'); ?></div>
                <div class="product-footer">
                  <div class="product-price">
                    <?php echo formatPrice($product['prix']); ?>
                  </div>
                  <button class="add-btn" onclick="addToCart(this, <?php echo htmlspecialchars(json_encode([
                    'id' => (int)$product['id'],
                    'nom' => $product['nom'],
                    'prix' => (float)$product['prix'],
                    'image' => $product['image'] ?? 'https://via.placeholder.com/400x300',
                    'categorie' => $product['categorie_nom'] ?? 'Autres',
                    'icon' => $product['categorie_icone'] ?? getProductIcon($product['categorie_nom'] ?? 'Autres')
                  ])); ?>)">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- CTA -->
<section class="cta-section" id="contact" style="padding: 6rem 7%; background: var(--white); text-align: center;">
  <div class="section-tag">Contactez-nous</div>
  <h2 class="section-title">Une question ? <em>Contactez-nous</em></h2>
  <p class="section-sub">Notre équipe est disponible sur WhatsApp pour vous conseiller et prendre votre commande.</p>
  <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? ''); ?>" target="_blank" class="btn-primary" style="display: inline-flex; align-items: center; gap: 10px; background: #25D366;">
    <i class="fab fa-whatsapp"></i>
    Écrire sur WhatsApp
  </a>
</section>

<!-- CART OVERLAY -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>

<!-- CART DRAWER -->
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-drawer-header">
    <h2>Mon Panier</h2>
    <button class="cart-close" onclick="toggleCart()">X</button>
  </div>
  <div class="cart-items" id="cartItems">
    <div class="cart-empty">
      <div class="cart-empty-icon"><i class="fas fa-shopping-bag"></i></div>
      <p>Votre panier est vide</p>
      <small style="font-size:0.8rem;color:var(--muted)">Ajoutez des produits pour commencer</small>
    </div>
  </div>
  <div class="cart-footer">
    <div class="cart-total">
      <span class="cart-total-label">Total</span>
      <span class="cart-total-amount" id="cartTotal">0 FCFA</span>
    </div>
    <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
      <i class="fab fa-whatsapp"></i>
      Commander via WhatsApp
    </button>
    <p class="cart-note">Vous serez redirigé vers WhatsApp pour finaliser votre commande.</p>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast">
  <span class="toast-icon"><i class="fas fa-check"></i></span>
  <span id="toastMsg">Produit ajouté au panier</span>
</div>

<script>
  const WHATSAPP_NUMBER = <?php echo $js_boutique_whatsapp; ?>;
  const BOUTIQUE_ID = <?php echo $js_boutique_id; ?>;
  const BOUTIQUE_NAME = <?php echo $js_boutique_name; ?>;
  const ALL_PRODUCTS = <?php echo $js_products; ?>;
  
  // let cart = [];
  
 // ============================================================
//  PANIER — CODE JS CORRIGÉ (bugs 1-6)
//  À intégrer dans index.php en remplacement du bloc <script>
// ============================================================

// Table de correspondance icônes FA → emojis WhatsApp (Bug 3)
const ICON_EMOJI_MAP = {
  'fas fa-box':          '📦',
  'fas fa-laptop':       '💻',
  'fas fa-headphones':   '🎧',
  'fas fa-mobile-alt':   '📱',
  'fas fa-gem':          '💎',
  'fas fa-spa':          '🌸',
  'fas fa-palette':      '🎨',
  'fas fa-cut':          '✂️',
  'fas fa-hand-sparkles':'✨',
  'fas fa-spray-can':    '🧴',
  'fas fa-th':           '🛍️',
};

function getEmojiFromIcon(icon) {
  return ICON_EMOJI_MAP[icon] || '🛍️';
}

// -----------------------------------------------
//  Variables globales
// -----------------------------------------------
let cart = [];

// -----------------------------------------------
//  Initialisation au chargement de la page
// -----------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
  trackVisitor();
  loadCartFromStorage(); // Charger le panier sauvegardé
});

// -----------------------------------------------
//  Ouvrir / fermer le drawer
// -----------------------------------------------
function toggleCart() {
  const drawer  = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartOverlay');
  drawer.classList.toggle('open');
  overlay.classList.toggle('open');
}

// -----------------------------------------------
//  Ajouter un produit au panier
// -----------------------------------------------
function addToCart(btnElement, productData) {
  // Bug 1 fix : comparaison en String pour éviter number vs string
  const existing = cart.find(i => String(i.id) === String(productData.id));

  if (existing) {
    existing.qty++;
  } else {
    cart.push({
      id:       productData.id,
      name:     productData.nom,
      price:    parseFloat(productData.prix),
      qty:      1,
      image:    productData.image || '',
      icon:     productData.icon  || 'fas fa-box',
      emoji:    getEmojiFromIcon(productData.icon), // Bug 3 fix : stocker l'emoji dès l'ajout
      categorie: productData.categorie || 'Autres'
    });
  }

  updateCart();
  showToast(`${productData.nom} ajouté au panier`);

  // Animation du bouton
  const originalHTML = btnElement.innerHTML;
  btnElement.innerHTML    = '<i class="fas fa-check"></i>';
  btnElement.style.background = 'var(--success)';
  btnElement.style.transform  = 'scale(1.1)';
  setTimeout(() => {
    btnElement.innerHTML    = originalHTML;
    btnElement.style.background = '';
    btnElement.style.transform  = '';
  }, 1000);

  saveCartToStorage();
}

// -----------------------------------------------
//  Mettre à jour l'affichage du panier
// -----------------------------------------------
function updateCart() {
  const cartItems   = document.getElementById('cartItems');
  const cartTotal   = document.getElementById('cartTotal');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const cartCount   = document.getElementById('cartCount');

  if (cart.length === 0) {
    cartItems.innerHTML = `
      <div class="cart-empty">
        <div class="cart-empty-icon"><i class="fas fa-shopping-bag"></i></div>
        <p>Votre panier est vide</p>
        <small style="font-size:0.8rem;color:var(--muted)">Ajoutez des produits pour commencer</small>
      </div>`;
    cartTotal.textContent  = '0 FCFA';
    checkoutBtn.disabled   = true;
    cartCount.textContent  = '0';
    return;
  }

  let html  = '';
  let total = 0;
  let count = 0;

  cart.forEach(item => {
    const itemTotal = item.price * item.qty;
    total += itemTotal;
    count += item.qty;

    const imageContent = item.image
      ? `<img src="${item.image}" alt="${item.name}"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
         <i class="${item.icon}" style="display:none;"></i>`
      : `<i class="${item.icon}"></i>`;

    // Bug 1 fix : IDs en String dans les onclick pour cohérence avec changeQty / removeFromCart
    html += `
      <div class="cart-item">
        <div class="cart-item-icon">${imageContent}</div>
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-price">${formatPrice(item.price)}</div>
          <div class="cart-item-controls">
            <button class="qty-btn" onclick="changeQty('${String(item.id)}', -1)">-</button>
            <span class="qty-display">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty('${String(item.id)}', 1)">+</button>
          </div>
        </div>
        <button class="cart-remove" onclick="removeFromCart('${String(item.id)}')">
          <i class="fas fa-trash"></i>
        </button>
      </div>`;
  });

  cartItems.innerHTML   = html;
  cartTotal.textContent = formatPrice(total);
  checkoutBtn.disabled  = false;
  cartCount.textContent = count;

  // Animation du compteur (Bug 6 fix : annuler avant de relancer)
  cartCount.classList.remove('bump');
  void cartCount.offsetWidth; // force reflow pour relancer l'animation
  cartCount.classList.add('bump');
  setTimeout(() => cartCount.classList.remove('bump'), 300);
}

// -----------------------------------------------
//  Changer la quantité d'un produit
// -----------------------------------------------
function changeQty(productId, delta) {
  // Bug 1 fix : comparaison String === String
  const item = cart.find(i => String(i.id) === String(productId));
  if (!item) return;

  item.qty += delta;
  if (item.qty <= 0) {
    removeFromCart(productId);
  } else {
    updateCart();
    saveCartToStorage();
  }
}

// -----------------------------------------------
//  Retirer un produit du panier
// -----------------------------------------------
function removeFromCart(productId) {
  // Bug 2 fix : comparaison String === String
  const item = cart.find(i => String(i.id) === String(productId));
  cart = cart.filter(i => String(i.id) !== String(productId));
  updateCart();
  saveCartToStorage();
  showToast(`${item ? item.name : 'Produit'} retiré du panier`);
}

// -----------------------------------------------
//  Commander via WhatsApp
// -----------------------------------------------
function checkout() {
  if (cart.length === 0) return;

  const total = cart.reduce((s, i) => s + i.price * i.qty, 0);

  // Sauvegarder la commande côté backend
  saveOrderToBackend(cart, total);

  // Bug 3 fix : utiliser item.emoji (pas item.icon) dans le message
  let msg = `🛍️ *Bonjour ${BOUTIQUE_NAME} !*\n\nJe souhaite commander les produits suivants :\n\n`;

  cart.forEach(item => {
    msg += `${item.emoji} *${item.name}*\n`;
    msg += `   Quantité : ${item.qty}\n`;
    msg += `   Prix unitaire : ${item.price.toLocaleString('fr-FR')} FCFA\n`;
    msg += `   Sous-total : ${(item.price * item.qty).toLocaleString('fr-FR')} FCFA\n\n`;
  });

  msg += `─────────────────────\n`;
  msg += `💰 *TOTAL : ${total.toLocaleString('fr-FR')} FCFA*\n\n`;
  msg += `Merci de confirmer la disponibilité et les modalités de livraison. ✅`;

  // Ouvrir WhatsApp
  const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(msg)}`;
  window.open(url, '_blank');

  // Bug 5 fix : vider le panier après la commande
  cart = [];
  saveCartToStorage();
  updateCart();
  toggleCart();
  showToast('Commande envoyée avec succès ! 🎉');
}

// -----------------------------------------------
//  Filtrer les produits par catégorie
// -----------------------------------------------
function filterProducts(cat) {
  document.querySelectorAll('.product-card').forEach(card => {
    if (cat === 'all' || card.dataset.cat === cat) {
      card.classList.remove('hidden');
    } else {
      card.classList.add('hidden');
    }
  });

  // Bug filterProducts fix : activation via data-cat au lieu du map fragile
  document.querySelectorAll('.cat-card').forEach(c => {
    c.classList.remove('active');
    const match = c.getAttribute('onclick')?.match(/'([^']+)'/);
    if (match && match[1] === cat) {
      c.classList.add('active');
    }
  });
}

// -----------------------------------------------
//  Persistance localStorage
// -----------------------------------------------
function saveCartToStorage() {
  try {
    localStorage.setItem(`cart_${BOUTIQUE_ID}`, JSON.stringify(cart));
  } catch (e) {
    console.warn('Impossible de sauvegarder le panier :', e);
  }
}

function loadCartFromStorage() {
  try {
    const saved = localStorage.getItem(`cart_${BOUTIQUE_ID}`);
    // Bug 4 fix : reset propre si JSON invalide
    cart = saved ? JSON.parse(saved) : [];
  } catch (e) {
    console.warn('Panier corrompu, réinitialisation :', e);
    cart = [];
  }
  updateCart();
}

// -----------------------------------------------
//  Tracker de visiteurs
// -----------------------------------------------
function trackVisitor() {
  fetch('track_visitor.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `boutique_id=${BOUTIQUE_ID}`
  })
    .then(r => r.json())
    .then(data => { if (data.success) console.log('Visiteur tracké'); })
    .catch(err => console.error('Erreur tracking visiteur :', err));
}

// -----------------------------------------------
//  Sauvegarder la commande côté backend
// -----------------------------------------------
function saveOrderToBackend(cartItems, totalAmount) {
  const orderData = {
    boutique_id: BOUTIQUE_ID,
    nom_client:  'Client Web',
    telephone:   WHATSAPP_NUMBER,
    produits: cartItems.map(item => ({
      produit_id:      item.id,
      quantite:        item.qty,
      montant_unitaire: item.price
    })),
    montant_total: totalAmount
  };

  fetch('save_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(orderData)
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) console.log('Commande sauvegardée :', data.commande_id);
      else console.error('Erreur sauvegarde commande :', data.error);
    })
    .catch(err => console.error('Erreur save_order :', err));
}

// -----------------------------------------------
//  Utilitaires
// -----------------------------------------------
function formatPrice(price) {
  return price.toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' FCFA';
}

function showToast(msg) {
  const toast = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function toggleMobile() {
  document.getElementById('navMobile').classList.toggle('open');
}
function closeMobile() {
  document.getElementById('navMobile').classList.remove('open');
}

// Fermer le drawer avec Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.getElementById('cartDrawer').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
  }
});

// -----------------------------------------------
//  Partager la boutique
// -----------------------------------------------
function shareBoutique() {
  const url   = window.location.href;
  const title = BOUTIQUE_NAME;
  const text  = `Découvrez la boutique ${title} sur Creator Market !`;

  if (navigator.share) {
    navigator.share({ title, text, url })
      .then(() => showToast('Boutique partagée avec succès !'))
      .catch(() => {});
  } else if (navigator.clipboard) {
    navigator.clipboard.writeText(url)
      .then(() => showToast('Lien copié dans le presse-papiers !'));
  } else {
    const ta = document.createElement('textarea');
    ta.value = url;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    showToast('Lien copié dans le presse-papiers !');
  }
}

// -----------------------------------------------
//  QR Code
// -----------------------------------------------
function showQRCode() {
  const qrUrl = document.querySelector('[data-qr-url]')?.dataset?.qrUrl || '';
  if (!qrUrl) { showToast('QR Code non disponible'); return; }

  const modal = document.createElement('div');
  modal.className = 'qr-modal';
  modal.innerHTML = `
    <div class="qr-modal-overlay" onclick="closeQRModal()"></div>
    <div class="qr-modal-content">
      <button class="qr-modal-close" onclick="closeQRModal()"><i class="fas fa-times"></i></button>
      <h3>QR Code de la Boutique</h3>
      <div class="qr-modal-image"><img src="${qrUrl}" alt="QR Code"></div>
      <p>Scannez ce QR Code pour accéder directement à la boutique</p>
      <button onclick="downloadQRCode('${qrUrl}')" class="btn-primary">
        <i class="fas fa-download"></i> Télécharger le QR Code
      </button>
    </div>`;

  document.body.appendChild(modal);
  setTimeout(() => modal.classList.add('show'), 10);
}

function closeQRModal() {
  const modal = document.querySelector('.qr-modal');
  if (modal) {
    modal.classList.remove('show');
    setTimeout(() => document.body.removeChild(modal), 300);
  }
}

function downloadQRCode(url) {
  const link = document.createElement('a');
  link.href     = url;
  link.download = `qrcode-${BOUTIQUE_NAME}.png`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  showToast('QR Code téléchargé !');
}
  </script>
<!-- SECTION CONTACT COMPLÈTE -->
<section class="contact-section" id="contact">
  <div class="section-header">
    <div class="section-tag">Contact</div>
    <h2 class="section-title">Entrer en <em>contact</em></h2>
    <p class="section-sub">Nous sommes là pour répondre à toutes vos questions</p>
  </div>
  
  <div class="contact-container">
    <div class="contact-info animate-fade-in">
      <div class="contact-card">
        <div class="contact-icon">
          <i class="fas fa-store"></i>
        </div>
        <div class="contact-details">
          <h3><?php echo htmlspecialchars($boutique['nom']); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($boutique['description'] ?? 'Boutique premium')); ?></p>
          <div class="contact-meta">
            <div class="meta-item">
              <i class="fas fa-user"></i>
              <span><?php echo htmlspecialchars($boutique['proprietaire_nom'] ?? 'Propriétaire'); ?></span>
            </div>
            <div class="meta-item">
              <i class="fas fa-envelope"></i>
              <span><?php echo htmlspecialchars($boutique['proprietaire_email'] ?? 'Email non disponible'); ?></span>
            </div>
            <div class="meta-item">
              <i class="fas fa-phone"></i>
              <span><?php echo htmlspecialchars($boutique['whatsapp'] ?? 'Téléphone non disponible'); ?></span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="contact-card">
        <div class="contact-icon">
          <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="contact-details">
          <h3>Adresse</h3>
          <p><?php echo nl2br(htmlspecialchars($boutique['adresse'] ?? 'Adresse non spécifiée')); ?></p>
          <?php if ($boutique['adresse']): ?>
          <div class="contact-meta">
            <div class="meta-item">
              <i class="fas fa-globe"></i>
              <span><?php echo htmlspecialchars($boutique['adresse']); ?></span>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="contact-card">
        <div class="contact-icon">
          <i class="fas fa-concierge-bell"></i>
        </div>
        <div class="contact-details">
          <h3>Services</h3>
          <p><?php echo nl2br(htmlspecialchars($boutique['services'] ?? 'Service client disponible')); ?></p>
        </div>
      </div>
    </div>
    
    <div class="contact-actions animate-fade-in">
      <div class="action-card">
        <div class="action-icon whatsapp">
          <i class="fab fa-whatsapp"></i>
        </div>
        <h3>WhatsApp Direct</h3>
        <p>Commandez et posez vos questions directement via WhatsApp</p>
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? ''); ?>" class="btn-primary" target="_blank">
          <i class="fab fa-whatsapp"></i>
          <span>Contacter maintenant</span>
        </a>
      </div>
      
      <?php if ($boutique['qrcode']): ?>
      <div class="action-card">
        <div class="action-icon qr">
          <i class="fas fa-qrcode"></i>
        </div>
        <h3>QR Code</h3>
        <p>Scannez pour accéder rapidement à la boutique</p>
        <div class="qr-preview">
          <img src="<?php echo htmlspecialchars($boutique['qrcode']); ?>" alt="QR Code">
        </div>
      </div>
      <?php endif; ?>
      
      <div class="action-card">
        <div class="action-icon share">
          <i class="fas fa-share-alt"></i>
        </div>
        <h3>Partager</h3>
        <p>Partagez cette boutique avec vos amis</p>
        <button onclick="shareBoutique()" class="btn-outline">
          <i class="fas fa-share-alt"></i>
          <span>Partager la boutique</span>
        </button>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER AMÉLIORÉ -->
<footer class="footer-section">
  <div class="footer-content">
    <div class="footer-brand">
      <h3><?php echo htmlspecialchars($boutique['nom']); ?></h3>
      <p><?php echo htmlspecialchars(substr($boutique['description'] ?? 'Boutique premium', 0, 120)); ?>...</p>
      <div class="footer-social">
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? ''); ?>" class="social-link" target="_blank">
          <i class="fab fa-whatsapp"></i>
        </a>
        <?php if ($boutique['qrcode']): ?>
        <a href="#" onclick="showQRCode()" class="social-link">
          <i class="fas fa-qrcode"></i>
        </a>
        <?php endif; ?>
        <a href="#" onclick="shareBoutique()" class="social-link">
          <i class="fas fa-share-alt"></i>
        </a>
      </div>
    </div>
    
    <div class="footer-links">
      <h4>Navigation</h4>
      <ul>
        <li><a href="#categories">Catégories</a></li>
        <li><a href="#produits">Produits</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </div>
    
    <div class="footer-info">
      <h4>Informations</h4>
      <ul>
        <li><strong>Propriétaire:</strong> <?php echo htmlspecialchars($boutique['proprietaire_nom'] ?? 'Non spécifié'); ?></li>
        <li><strong>Email:</strong> <?php echo htmlspecialchars($boutique['proprietaire_email'] ?? 'Non disponible'); ?></li>
        <li><strong>Membre depuis:</strong> <?php echo date('M Y', strtotime($boutique['proprietaire_date_inscription'] ?? $boutique['date_creation'])); ?></li>
        <li><strong>Statut:</strong> <span class="status-badge active">Boutique vérifiée</span></li>
      </ul>
    </div>
  </div>
  
  <div class="footer-bottom">
    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($boutique['nom']); ?>. Tous droits réservés.</p>
    <p>Fait avec ❤️ par Creator Market</p>
  </div>
</footer>

</script>
</body>
</html>
