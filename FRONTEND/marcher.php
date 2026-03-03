<?php
session_start();
$pdo = new PDO(
    'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $pdo->query("SELECT * FROM produits ORDER BY id DESC");
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Market</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="src/css/acceuil.css">
    <link rel="stylesheet" href="src/css/marcher.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #3b82f6;
            --success-color: #10b981;
            --success-dark: #059669;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* SECTION HERO */
        .createurs {
            position: relative;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            top: 80px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .createurs::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .createurs h1 {
            position: relative;
            font-size: clamp(28px, 5vw, 48px);
            font-weight: 800;
            color: white;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .createurs p {
            position: relative;
            color: rgba(255, 255, 255, 0.95);
            font-size: clamp(16px, 3vw, 20px);
            margin-bottom: 32px;
            line-height: 1.7;
            font-weight: 400;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .input {
            position: relative;
            width: min(90%, 600px);
            padding: 16px 24px 16px 52px;
            font-size: 16px;
            border-radius: var(--radius-xl);
            border: 2px solid transparent;
            background: white;
            box-shadow: var(--shadow-xl);
            transition: all 0.3s ease;
            outline: none;
            font-weight: 500;
        }

        .input::placeholder {
            color: var(--text-secondary);
        }

        .createurs::after {
            content: '\f002';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 50%;
            transform: translateX(-50%) translateX(-270px);
            bottom: 90px;
            color: var(--text-secondary);
            font-size: 18px;
            pointer-events: none;
        }

        .input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1), var(--shadow-xl);
        }

        .buttons {
            position: relative;
            top: 100px;
            background: var(--bg-primary);
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
            padding: 24px 20px;
            gap: 12px;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
        }

        .res, select {
            padding: 12px 24px;
            border-radius: var(--radius-lg);
            border: 2px solid var(--border-color);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            background: var(--bg-primary);
        }

        .res {
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .res i {
            font-size: 16px;
        }

        .res:hover {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
            transform: translateY(-2px);
        }

        .res.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        select {
            color: var(--text-primary);
            min-width: 160px;
            font-family: inherit;
            background: var(--bg-primary);
            cursor: pointer;
        }

        select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* PANIER FLOTTANT */
        .plan {
            position: fixed;
            bottom: 40px;
            right: 24px;
            z-index: 999;
        }

        .panier {
            position: relative;
            width: 64px;
            height: 64px;
            border: none;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: var(--shadow-xl);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panier::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--primary-light);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .panier:hover::before {
            opacity: 1;
        }

        .panier:hover {
            transform: scale(1.1) translateY(-4px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.4);
        }

        .panier:active {
            transform: scale(1.05) translateY(-2px);
        }

        .panier i {
            position: relative;
            z-index: 1;
        }

        .badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger-color);
            color: white;
            font-size: 12px;
            font-weight: 700;
            border-radius: 50%;
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--bg-secondary);
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .badge:not(:empty) {
            opacity: 1;
            transform: scale(1);
        }

        .boutique {
            position: relative;
            top: 120px;
            padding: 32px;
            background: transparent;
            margin: 0 auto;
            max-width: 1400px;
        }

        .boutique > span {
            font-weight: 700;
            font-size: 20px;
            color: var(--text-primary);
            display: block;
            margin-bottom: 8px;
        }

        .boutique > p {
            color: var(--warning-color);
            font-weight: 600;
            margin-bottom: 32px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* GRILLE PRODUITS */
        .businesses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .business-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid var(--border-color);
        }

        .business-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-color);
        }

        .business-image {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .business-image::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 60%, rgba(0,0,0,0.3) 100%);
        }

        .business-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .business-card:hover .business-image img {
            transform: scale(1.05);
        }

        .business-info {
            padding: 24px;
        }

        .business-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            line-height: 1.3;
        }

        .coeur {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 4px;
        }

        .coeur:hover {
            color: var(--danger-color);
            transform: scale(1.2);
        }

        .coeur.red {
            color: var(--danger-color);
        }

        .business-prix {
            color: var(--success-color);
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .business-prix::before {
            content: 'XAF';
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .business-category {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 12px;
            background: var(--bg-secondary);
            display: inline-block;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
        }

        .business-location {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .business-location i {
            color: var(--primary-color);
        }

        .business-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
        }

        .stars {
            color: #fbbf24;
            font-size: 16px;
        }

        .avis {
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
        }

        /* ACTIONS */
        .business-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .action-btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: none;
            letter-spacing: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            grid-column: 1 / -1;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-primary);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .partage {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .partage:hover {
            background: var(--success-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .partage i {
            font-size: 16px;
        }

        /* MINI PANIER */
        #mini-panier {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 400px;
            max-width: 90vw;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s ease;
            display: none;
            max-height: 80vh;
            overflow: hidden;
        }

        #mini-panier.show {
            transform: translateX(0);
            opacity: 1;
        }

        .panier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #3498db;
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .panier-header h3 {
            margin: 0;
            font-size: 18px;
        }

        #liste-produits {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .panier-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .item-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
.enbas{
    display: flex;
 align-items: center;
 justify-content: space-between;
}
        .item-prix {
            font-size: 14px;
            color: #27ae60;
        }

        #notification-container, #alert-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        #alert-container {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
        }

        .alert-swing, .order-notification {
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 16px 20px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid var(--warning-color);
            animation: slideInRight 0.4s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-swing i, .order-notification i {
            color: var(--warning-color);
            font-size: 20px;
        }

        @media (max-width: 1024px) {
            .businesses-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .createurs {
                top: 60px;
                padding: 40px 20px;
            }

            .createurs::after {
                display: none;
            }

            .buttons {
                display: flex!important;
            }
            .notification{
                position:relative ;
                left: 90px;
            }

            .boutique {
                top: 100px;
                padding: 20px;
            }

            .businesses-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .plan {
                bottom: 24px;
                right: 16px;
            }
            .entete{
                padding: 0 0 0 40px;
                position: relative !important;
            }
            .categorie{
                position: relative !important;
                justify-content: flex-end !important;
            }
            .business-nam{
                display: flex;
                justify-content: space-between;


            }

            .panier {
                width: 56px;
                height: 56px;
                font-size: 20px;
            }

            #mini-panier {
                width: calc(100vw - 32px);
                right: 16px;
            }

            .business-actions {
                grid-template-columns: 1fr;
            }

            .btn-primary {
                grid-column: 1;
            }
            .enbas{
                display: flex;
                flex-wrap: wrap
            }
        }

        @media (max-width: 480px) {
            .res {
                width: 100%;
                justify-content: center;
            }

            select {
                width: 100%;
            }

            .boutique {
                padding: 16px;
            }
        }

        .entete {
            color: var(--primary-color);
            font-weight: 700;
        }

        #liste-produits::-webkit-scrollbar {
            width: 6px;
        }

        #liste-produits::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        #liste-produits::-webkit-scrollbar-thumb {
            background: var(--text-secondary);
            border-radius: 3px;
        }

        #liste-produits::-webkit-scrollbar-thumb:hover {
            background: var(--text-primary);
        }

        /* ÉTATS DE CHARGEMENT */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .loading {
            animation: shimmer 2s infinite;
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 1000px 100%;
        }

        /* FOCUS VISIBLE POUR ACCESSIBILITÉ */
        *:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        button:focus-visible {
            outline-offset: 4px;
        }
    </style>
</head>
<body>
    <header>
        <div class="titre">
            <div class="autre">
                <div class="menu-hamburger" onclick="toggleMenu()">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <blockquote class="entete">
                    <i class="fas fa-envelope"></i> 
                    <span data-lang-en="Creator Market">Creator market</span>
                </blockquote>
                <div class="ceux" id="ceux">
                    <button id="acceuil" class="acceuil">
                        <i class="fas fa-envelope"></i> 
                        <span data-lang-en="Home">Acceuil</span>
                    </button>
                    <button class="marcher">
                        <i class="fas fa-search"></i> 
                        <span data-lang-en="Market Place">Market Place</span>
                    </button>
                    <button onclick="window.location.href='templates.html'" class="marcher">
                        <i class="fas fa-crown"></i> 
                        <span data-lang-en="Create">Créer</span>
                    </button>
                    <button class="marcher">
                        <i class="fas fa-user"></i> 
                        <span data-lang-en="Dashboard">Dashboard</span>
                    </button>
                </div>

                <div id="categorie" class="categorie">
                    <button id="notification" class="notification">
                        <i class="fas fa-bell"></i>
                    </button>
                    <button id="langue" class="notification">
                        <i class="fas fa-globe"></i> 
                        <span data-lang-en="EN">FR</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="createurs">
        <h1>Découvrez les créateurs locaux</h1>
        <p>Plus de 7 businesses locaux vous attendent. Commandez, réservez <br> et soutenez l'économie locale!</p>
        <input type="search" class="input" placeholder="Recherchez une boutique, un salon de beauté, un restaurant...">
    </div>

    <div class="buttons">
        <button class="res active">
            <i class="fas fa-th-large"></i> Tous
        </button>
        <button class="res">
            <i class="fas fa-utensils"></i> Restaurant
        </button>
        <button class="res">
            <i class="fas fa-spa"></i> Beauté
        </button>
        <button class="res">
            <i class="fas fa-tv"></i> Électroniques
        </button>
        <button class="res">
            <i class="fas fa-tshirt"></i> Mode
        </button>
        
        <select name="city" id="citySelect">
            <option value="toutes les villes">Toutes Les villes</option>
            <option value="yaounde">Yaoundé</option>
            <option value="douala">Douala</option>
            <option value="bafoussam">Bafoussam</option>
            <option value="melong">Melong</option>
            <option value="garoua">Garoua</option>
            <option value="kribi">Kribi</option>
        </select>
        
        <select name="sort" id="sortSelect">
            <option value="tendances">Tendances</option>
            <option value="mieux notes">Mieux notés</option>
            <option value="plus recents">Plus récents</option>
        </select>
    </div>

    <div class="boutique">
        <span>7 Résultats</span>
        <p>⭐ Businesses en vedette</p>
        
        <div class="businesses-grid" id="businesses-grid">
            <?php foreach($produits as $produit): ?>
            <div class="business-card">
                <div class="business-image">
                    <img src="<?= htmlspecialchars($produit['image']) ?>" 
                         alt="<?= htmlspecialchars($produit['nom']) ?>">
                </div>
                <div class="business-info">
                    <div class="business-name">
                        <?= htmlspecialchars($produit['nom']) ?>
                        <button class="coeur">❤</button>
                    </div>
                    <div class="business-prix">
                        <?= htmlspecialchars($produit['prix']) ?>
                    </div>
                    <div class="business-category">
                        <?= htmlspecialchars($produit['description']) ?>
                    </div>
                    <div class="business-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($produit['localisation']) ?>
                    </div>
                    <div class="business-actions">
                        <button class="action-btn btn-primary boutton-reponse"
                            data-nom="<?= htmlspecialchars($produit['nom']) ?>"
                            data-prix="<?= htmlspecialchars($produit['prix']) ?>"
                            data-localisation="<?= htmlspecialchars($produit['localisation']) ?>"
                            data-whatsapp="<?= htmlspecialchars($produit['whatsapp']) ?>">
                            <i class="fas fa-shopping-cart"></i> Commander
                        </button> 
                        <div class="enbas">
                        <button class="action-btn btn-secondary">
                            <i class="fas fa-eye"></i> Voir
                        </button>
                        <button class="partage">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Redirection WhatsApp
        document.querySelectorAll('.boutton-reponse').forEach(button => {
            button.addEventListener('click', function() {
                let nom = this.getAttribute('data-nom');
                let prix = this.getAttribute('data-prix');
                let whatsap = this.getAttribute('data-whatsapp');
                let localisation = this.getAttribute('data-localisation');
                let message = `Bonjour madame/monsieur 👋

Je voudrais commander le produit suivant :

*Produit* : ${nom}
*Prix* : ${prix}
*Localisation* : ${localisation}

Merci de me confirmer la disponibilité et les modalités de livraison.`;
                
                let whatsapp = `https://wa.me/${whatsap}?text=` + encodeURIComponent(message);
                window.open(whatsapp, '_blank');
            });
        });

        // Recherche
        const recherche = document.querySelector('input[type="search"]');
        if (recherche) {
            recherche.addEventListener('input', (e) => {
                const minscule = e.target.value.toLowerCase();
                document.querySelectorAll('.business-card').forEach(card => {
                    const businessName = card.querySelector('.business-name').textContent.toLowerCase();
                    const businessCategory = card.querySelector('.business-category').textContent.toLowerCase();
                    card.style.display = (businessName.includes(minscule) || businessCategory.includes(minscule) || minscule === '') ? 'block' : 'none';
                });
            });
        }

        // Gestion des catégories
        document.querySelectorAll('.res').forEach(bouton => {
            bouton.addEventListener('click', () => {
                document.querySelectorAll('.res').forEach(btn => btn.classList.remove('active'));
                bouton.classList.add('active');
            });
        });

        // Menu hamburger
        function toggleMenu() {
            const hamburger = document.querySelector('.menu-hamburger');
            const menu = document.querySelector('.ceux');
            hamburger.classList.toggle('ouvert');
            menu.classList.toggle('menu-ouvert');
        }
    </script>
</body>
</html>