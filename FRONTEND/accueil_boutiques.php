<?php
require_once __DIR__ . '/config/index.php';

try {
    $pdo = DatabaseConfig::getConnection();
    
    $stmt = $pdo->query("
        SELECT b.id, b.nom, b.description, b.logo, b.date_creation,
               u.nom as proprietaire_nom,
               (SELECT COUNT(*) FROM produits WHERE boutique_id = b.id AND statut = 'disponible') as nb_produits
        FROM boutiques b
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id
        ORDER BY b.date_creation DESC
    ");
    $boutiques = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CreatorMarket - Toutes les boutiques</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --rose: #C8625C;
            --gold: #C9964A;
            --cream: #FBF7F2;
            --dark: #16100F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--dark);
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .header p {
            color: #666;
            font-size: 1.1rem;
        }
        .boutiques-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        .boutique-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(201,150,74,0.22);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
        }
        .boutique-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
        }
        .boutique-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--rose), var(--gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        .boutique-content {
            padding: 1.5rem;
        }
        .boutique-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .boutique-owner {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .boutique-desc {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .boutique-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--rose);
        }
        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .boutique-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--rose);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }
        .boutique-link:hover {
            background: #a0403a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CreatorMarket</h1>
            <p>Découvrez toutes nos boutiques partenaires</p>
        </div>
        
        <?php if (empty($boutiques)): ?>
            <div style="text-align: center; padding: 4rem; color: #666;">
                <i class="fas fa-store" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h2>Aucune boutique trouvée</h2>
                <p>Revenez bientôt pour découvrir de nouvelles boutiques !</p>
            </div>
        <?php else: ?>
            <div class="boutiques-grid">
                <?php foreach ($boutiques as $boutique): ?>
                    <a href="index.php?boutique_id=<?php echo $boutique['id']; ?>" class="boutique-card">
                        <div class="boutique-image">
                            <?php if (!empty($boutique['logo'])): ?>
                                <img src="<?php echo '../' . htmlspecialchars($boutique['logo']); ?>" alt="<?php echo htmlspecialchars($boutique['nom']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-store"></i>
                            <?php endif; ?>
                        </div>
                        <div class="boutique-content">
                            <h3 class="boutique-name"><?php echo htmlspecialchars($boutique['nom']); ?></h3>
                            <p class="boutique-owner">
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars($boutique['proprietaire_nom'] ?? 'Vendeur'); ?>
                            </p>
                            <?php if (!empty($boutique['description'])): ?>
                                <p class="boutique-desc"><?php echo htmlspecialchars(substr($boutique['description'], 0, 100)) . '...'; ?></p>
                            <?php endif; ?>
                            <div class="boutique-stats">
                                <div class="stat">
                                    <div class="stat-number"><?php echo $boutique['nb_produits']; ?></div>
                                    <div class="stat-label">Produits</div>
                                </div>
                            </div>
                            <div class="boutique-link">
                                <i class="fas fa-eye"></i>
                                Voir la boutique
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
