<?php
session_start();
require_once __DIR__ . '/config/index.php';

// Vérifier si l'utilisateur est connecté, sinon rediriger vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: INSCRIPTION.php');
    exit();
}

// Connexion à la base de données via la configuration
try {
    $pdo = DatabaseConfig::getConnection();
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT logo, nom, adresse, whatsapp, services, description, date_creation, slug, qrcode FROM boutiques WHERE utilisateur_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$boutique = $stmt->fetch(PDO::FETCH_ASSOC);

// Infos utilisateur pour la section Profil
$stmtUser = $pdo->prepare("SELECT nom, email FROM utilisateurs WHERE id = ? LIMIT 1");
$stmtUser->execute([$_SESSION['user_id']]);
$utilisateur = $stmtUser->fetch(PDO::FETCH_ASSOC);

$protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$urlPublique = $boutique && $boutique['slug']
    ? $protocol . '://' . $host . $basePath . '/boutique_publique.php?b=' . urlencode($boutique['slug'])
    : '';
$qrcodeUrl = $boutique && $boutique['qrcode']
    ? $protocol . '://' . $host . $basePath . '/' . $boutique['qrcode']
    : '';
    // Récupérer l'id de la boutique du vendeur connecté
$stmtBoutique = $pdo->prepare("SELECT id FROM boutiques WHERE utilisateur_id = ? LIMIT 1");
$stmtBoutique->execute([$_SESSION['user_id']]);
$boutique_vendeur = $stmtBoutique->fetch(PDO::FETCH_ASSOC);
$boutique_id_vendeur = $boutique_vendeur['id'] ?? 0;

// Récupérer toutes les commandes de cette boutique
$stmtCmd = $pdo->prepare("
    SELECT 
        c.id,
        c.nom_client,
        c.telephone,
        c.quantite,
        c.montant,
        c.statut,
        c.date_commande,
        p.nom AS produit_nom
    FROM commandes c
    LEFT JOIN produits p ON p.id = c.produit_id
    WHERE c.boutique_id = ?
    ORDER BY c.date_commande DESC
    LIMIT 10
");
$stmtCmd->execute([$boutique_id_vendeur]);
$commandes = $stmtCmd->fetchAll(PDO::FETCH_ASSOC); 
//chiffre d'affaire
$stmtCmd = $pdo->prepare("
    SELECT SUM(c.montant) AS montant
    FROM commandes c
    WHERE c.boutique_id = ?
");
$stmtCmd->execute([$boutique_id_vendeur]);
$row = $stmtCmd->fetch(PDO::FETCH_ASSOC);
$somme = $row['montant'] ?? 0;

// Récupérer les statistiques réelles de la boutique
$stmtStats = $pdo->prepare("SELECT total_vues, visiteurs_uniques FROM boutiques WHERE utilisateur_id = ? LIMIT 1");
$stmtStats->execute([$_SESSION['user_id']]);
$statsBoutique = $stmtStats->fetch(PDO::FETCH_ASSOC);

// Récupérer le nombre total de commandes
$stmtNbCmd = $pdo->prepare("SELECT COUNT(*) AS nb_commandes FROM commandes WHERE boutique_id = ?");
$stmtNbCmd->execute([$boutique_id_vendeur]);
$nbCommandes = $stmtNbCmd->fetch(PDO::FETCH_ASSOC)['nb_commandes'] ?? 0;

$visiteursUniques = $statsBoutique['visiteurs_uniques'] ?? 0;
$conversionRate = 0;
if ($visiteursUniques > 0) {
    $conversionRate = round(($nbCommandes / $visiteursUniques) * 100, 2);
}

$stats = [
    'vues'       => $statsBoutique['total_vues'] ?? 0,
    'visiteurs'  => $visiteursUniques,
    'conversion' => $conversionRate . '%',
    'revenus'    => number_format($somme, 0, ',', ' ') . ' FCFA',
    'commandes'  => $nbCommandes
];

$favoris = $pdo->prepare("SELECT b.id FROM favoris f JOIN boutiques b ON f.boutique_id = b.id WHERE f.utilisateur_id = ?");
$favoris->execute([$_SESSION['user_id']]);
$favoris = $favoris->fetchAll(PDO::FETCH_ASSOC);

$stmtProd = $pdo->prepare("SELECT * FROM produits WHERE utilisateur_id = ?");
$stmtProd->execute([$_SESSION['user_id']]);
$produits = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Creator Market</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/css/dahsbord.css">
    <style>
        /* ─── Sections masquées par défaut ─── */
        .section-vue { display: none; }
        .section-vue.active { display: block; }

        /* ─── Nav item actif ─── */
        .nav-item.active {
            background: linear-gradient(135deg, rgba(59,130,246,.15) 0%, rgba(139,92,246,.15) 100%);
            color: var(--primary-light);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(59,130,246,.15);
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 0 2px 2px 0;
        }

        /* ════════════════════════════════════════
           SECTION PRODUITS
        ════════════════════════════════════════ */
        .produits-section {
            background: var(--bg-card, linear-gradient(135deg,#1e293b,#334155));
            border-radius: 18px;
            border: 1px solid var(--border-color-strong, rgba(59,130,246,.15));
            box-shadow: var(--shadow-sm, 0 4px 16px rgba(0,0,0,.2));
            overflow: hidden;
            margin-bottom: 28px;
        }
        .produits-header {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 22px 28px;
            border-bottom: 1px solid var(--border-color, rgba(255,255,255,.06));
            flex-wrap: wrap; gap: 12px;
        }
        .produits-header-left { display: flex; align-items: center; gap: 14px; }
        .produits-header-icon {
            width: 46px; height: 46px;
            background: linear-gradient(135deg,rgba(59,130,246,.18),rgba(139,92,246,.18));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #60a5fa;
            box-shadow: 0 4px 14px rgba(59,130,246,.2);
        }
        .produits-header h3 { font-size: 1.15rem; font-weight: 700; color: var(--text-primary,#fff); margin:0; }
        .produits-count {
            background: rgba(59,130,246,.15); color: #60a5fa;
            border: 1px solid rgba(59,130,246,.25);
            border-radius: 20px; font-size:.75rem; font-weight:700;
            padding: 3px 10px; margin-left: 4px;
        }
        .btn-ajouter-produit {
            display: inline-flex; align-items: center; gap: 7px;
            background: linear-gradient(135deg,#3b82f6,#2563eb);
            color: white; border: none;
            padding: 10px 20px; border-radius: 11px;
            font-size:.85rem; font-weight:700;
            cursor: pointer; text-decoration: none;
            transition: all .25s;
            box-shadow: 0 4px 14px rgba(59,130,246,.3);
        }
        .btn-ajouter-produit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,.45); }
        .produits-toolbar {
            padding: 16px 28px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06));
            flex-wrap: wrap;
        }
        .search-produit {
            display: flex; align-items: center;
            background: var(--overlay-bg,rgba(255,255,255,.05));
            border: 1px solid var(--overlay-border,rgba(255,255,255,.08));
            border-radius: 10px; padding: 8px 14px;
            flex:1; max-width:340px; gap:8px; transition:border-color .2s;
        }
        .search-produit:focus-within { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
        .search-produit i { color:var(--text-muted,#94a3b8); font-size:14px; }
        .search-produit input {
            background:transparent; border:none; outline:none;
            color:var(--text-primary,#fff); font-size:.85rem; width:100%;
        }
        .search-produit input::placeholder { color:var(--text-muted,#94a3b8); }
        .produits-nb { margin-left:auto; font-size:.8rem; color:var(--text-muted,#94a3b8); font-weight:500; }
        .table-wrapper { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .produits-table { width:100%; border-collapse:collapse; min-width:720px; }
        .produits-table thead { background:var(--overlay-bg,rgba(255,255,255,.04)); }
        .produits-table th {
            padding:13px 16px; text-align:left;
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.8px; color:var(--text-muted,#94a3b8);
            border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));
            white-space:nowrap;
        }
        .produits-table th:last-child { text-align:center; }
        .produits-table tbody tr {
            border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));
            transition:background .18s;
            animation: rowFadeIn .4s ease both;
        }
        @keyframes rowFadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .produits-table tbody tr:hover { background:var(--overlay-bg-hover,rgba(255,255,255,.06)); }
        .produits-table tbody tr:last-child { border-bottom:none; }
        .produits-table td { padding:14px 16px; font-size:.85rem; color:var(--text-secondary,#e2e8f0); vertical-align:middle; }
        .cell-produit { display:flex; align-items:center; gap:12px; }
        .produit-img-wrapper {
            width:52px; height:52px; border-radius:10px; overflow:hidden;
            flex-shrink:0; background:var(--overlay-bg,rgba(255,255,255,.05));
            border:1px solid var(--border-color,rgba(255,255,255,.06));
            display:flex; align-items:center; justify-content:center;
        }
        .produit-img-wrapper img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
        .produits-table tbody tr:hover .produit-img-wrapper img { transform:scale(1.08); }
        .produit-img-placeholder { font-size:22px; color:var(--text-muted,#94a3b8); }
        .produit-nom { font-weight:700; color:var(--text-primary,#fff); font-size:.88rem; line-height:1.3; }
        .produit-desc { font-size:.76rem; color:var(--text-muted,#94a3b8); margin-top:2px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .badge-prix {
            display:inline-block;
            background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(16,185,129,.25));
            color:#10b981; border:1px solid rgba(16,185,129,.25);
            border-radius:8px; padding:5px 12px; font-size:.82rem; font-weight:800; white-space:nowrap;
        }
        .cell-lieu { display:flex; align-items:center; gap:6px; color:var(--text-tertiary,#cbd5e1); }
        .cell-lieu i { color:#f59e0b; font-size:13px; }
        .cell-wa { display:inline-flex; align-items:center; gap:6px; color:#25D366; font-weight:600; font-size:.82rem; text-decoration:none; transition:opacity .2s; }
        .cell-wa:hover { opacity:.8; }
        .cell-actions { display:flex; align-items:center; justify-content:center; gap:8px; }
        .btn-action {
            display:inline-flex; align-items:center; gap:5px;
            padding:7px 13px; border-radius:8px;
            font-size:.78rem; font-weight:700; cursor:pointer;
            transition:all .2s; border:none; text-decoration:none; white-space:nowrap;
        }
        .btn-modifier { background:rgba(59,130,246,.15); color:#60a5fa; border:1px solid rgba(59,130,246,.25); }
        .btn-modifier:hover { background:rgba(59,130,246,.3); transform:translateY(-2px); box-shadow:0 4px 12px rgba(59,130,246,.2); }
        .btn-supprimer { background:rgba(239,68,68,.12); color:#f87171; border:1px solid rgba(239,68,68,.2); }
        .btn-supprimer:hover { background:rgba(239,68,68,.25); transform:translateY(-2px); box-shadow:0 4px 12px rgba(239,68,68,.2); }
        .produits-vide { padding:56px 28px; text-align:center; }
        .produits-vide-icon { font-size:52px; margin-bottom:16px; opacity:.35; }
        .produits-vide h4 { font-size:1.05rem; font-weight:700; color:var(--text-primary,#fff); margin-bottom:8px; }
        .produits-vide p { font-size:.88rem; color:var(--text-muted,#94a3b8); margin-bottom:20px; }
        .produits-footer {
            padding:14px 28px;
            border-top:1px solid var(--border-color,rgba(255,255,255,.06));
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:10px;
        }
        .produits-footer-info { font-size:.8rem; color:var(--text-muted,#94a3b8); }

        /* ─── Modal suppression ─── */
        #modal-supprimer { display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:1rem; }
        #modal-supprimer.visible { display:flex; }
        #modal-supprimer .modal-bg { position:absolute; inset:0; background:rgba(0,0,0,.65); backdrop-filter:blur(6px); }
        #modal-supprimer .modal-box {
            position:relative; background:var(--bg-secondary,#1e293b);
            border:1px solid rgba(239,68,68,.2); border-radius:18px;
            padding:2rem; max-width:400px; width:100%; text-align:center;
            animation:popIn .3s cubic-bezier(.175,.885,.32,1.275);
            box-shadow:0 24px 60px rgba(0,0,0,.4);
        }
        @keyframes popIn { from{transform:scale(.88);opacity:0} to{transform:scale(1);opacity:1} }
        .modal-danger-icon {
            width:60px; height:60px;
            background:linear-gradient(135deg,rgba(239,68,68,.2),rgba(239,68,68,.35));
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            margin:0 auto 1.2rem; font-size:26px; color:#f87171;
            box-shadow:0 8px 24px rgba(239,68,68,.25);
        }
        #modal-supprimer h3 { font-size:1.15rem; font-weight:800; color:var(--text-primary,#fff); margin-bottom:.5rem; }
        #modal-supprimer p { font-size:.88rem; color:var(--text-muted,#94a3b8); margin-bottom:1.6rem; line-height:1.5; }
        #modal-supprimer strong { color:#f87171; }
        .modal-actions { display:flex; gap:.75rem; justify-content:center; }
        .btn-annuler {
            flex:1; padding:.75rem 1rem; border-radius:10px;
            background:var(--overlay-bg,rgba(255,255,255,.06));
            border:1px solid var(--overlay-border,rgba(255,255,255,.1));
            color:var(--text-primary,#fff); font-weight:700; font-size:.88rem;
            cursor:pointer; transition:all .2s;
        }
        .btn-annuler:hover { background:rgba(255,255,255,.1); }
        .btn-confirmer-suppr {
            flex:1; padding:.75rem 1rem; border-radius:10px;
            background:linear-gradient(135deg,#ef4444,#dc2626);
            border:none; color:white; font-weight:700; font-size:.88rem;
            cursor:pointer; transition:all .2s;
            box-shadow:0 4px 14px rgba(239,68,68,.35);
        }
        .btn-confirmer-suppr:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(239,68,68,.5); }

        /* ─── Toast ─── */
        .toast-produit {
            position:fixed; bottom:28px; right:28px;
            background:linear-gradient(135deg,#1e293b,#334155);
            border:1px solid rgba(59,130,246,.2); color:#e2e8f0;
            padding:14px 20px; border-radius:12px;
            font-size:.88rem; font-weight:600;
            box-shadow:0 12px 40px rgba(0,0,0,.35);
            z-index:10001; display:flex; align-items:center; gap:10px;
            animation:slideUp .3s ease; max-width:320px;
        }
        .toast-produit.succes { border-color:rgba(16,185,129,.3); }
        .toast-produit.succes i { color:#10b981; }
        .toast-produit.erreur { border-color:rgba(239,68,68,.3); }
        .toast-produit.erreur i { color:#f87171; }
        @keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
    </style>
</head>
<body data-theme="dark">

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

<!-- ═══════ SIDEBAR ═══════ -->
<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <h2><i class="fas fa-store"></i> Creator Market</h2>
    </div>

    <div class="nav-section">
        <div class="nav-title">Principal</div>

        <!-- data-vue indique quelle section afficher -->
        <a href="#" class="nav-item active" data-vue="dashboard">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>
        <a href="#" class="nav-item" data-vue="boutique">
            <i class="fas fa-store"></i><span>Ma Boutique</span>
        </a>
        <a href="#" class="nav-item" data-vue="produits">
            <i class="fas fa-box"></i><span>Produits</span>
            <span class="notification-badge"><?= count($produits) ?></span>
        </a>
        <a href="#" class="nav-item" data-vue="commandes">
            <i class="fas fa-shopping-cart"></i><span>Commandes</span>
            <span class="notification-badge">5</span>
        </a>

        <div class="nav-title">Marketing</div>
        <a href="#" class="nav-item" data-vue="analytics">
            <i class="fas fa-chart-line"></i><span>Analytics</span>
        </a>
        <a href="#" class="nav-item" data-vue="dashboard">
            <i class="fas fa-bullhorn"></i><span>Promotions</span>
        </a>

        <div class="nav-title">Paramètres</div>
        <a href="#" class="nav-item" data-vue="parametres">
            <i class="fas fa-cog"></i><span>Paramètres</span>
        </a>
        <a href="#" class="nav-item" data-vue="profil">
            <i class="fas fa-user"></i><span>Profil</span>
        </a>
        <a href="#" class="nav-item" data-vue="dashboard">
            <i class="fas fa-question-circle"></i><span>Aide & Support</span>
        </a>
    </div>

    <div class="upgrade-card">
        <h4>✨ Passez à Premium</h4>
        <p>Obtenez 2x plus de clients</p>
        <button class="upgrade-btn" onclick="upgradePremium()">
            <i class="fas fa-crown"></i><span>Découvrir Premium</span>
        </button>
    </div>
</div>

<!-- ═══════ MAIN CONTENT ═══════ -->
<div class="main-content">

    <!-- Header -->
    <header class="main-header">
        <div class="header-left">
            <div class="search-bar">
                <i classonclick="window.location.href='acceuil.php'"            <inputhome="text" placeholder="Rechercher produits, commandes...">
            </div>
        </div>
        <div class="header-actions">
            <button class="theme-toggle" onclick="toggleTheme()" title="Changer le thème">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
            <button class="action-btn" onclick="toggleLanguage()"><i class="fas fa-globe"></i></button>
            <div style="position:relative;">
                <button class="action-btn" onclick="openNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" style="position:absolute;top:-5px;right:-5px;">3</span>
                </button>
            </div>
            <button class="action-btn" onclick="openWhatsApp()">
                <i class="fab fa-whatsapp" style="color:#25D366;"></i>
            </button>
            <div class="user-profile">
                <?php if ($boutique && $boutique['logo']): ?>
                    <img src="<?= htmlspecialchars($boutique['logo']) ?>" alt="Logo" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar" style="background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-store" style="color:white;"></i>
                    </div>
                <?php endif; ?>
                <div class="user-info">
                    <div class="user-name"><?= $boutique ? htmlspecialchars($boutique['nom']) : 'Ma Boutique' ?></div>
                    <div class="user-role">Propriétaire</div>
                </div>
            </div>
        </div>
    </header>

    <div class="section-vue active content-area" id="vue-dashboard">
        <div class="boutique-header fade-in">
            <?php if ($boutique && $boutique['logo']): ?>
                <img src="<?= htmlspecialchars($boutique['logo']) ?>" alt="Logo boutique" class="boutique-logo">
            <?php else: ?>
                <div class="boutique-logo" style="background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-store" style="font-size:44px;color:white;"></i>
                </div>
            <?php endif; ?>
            <div class="boutique-info">
                <h1><?= $boutique ? htmlspecialchars($boutique['nom']) : 'Ma Boutique' ?></h1>
                <div class="boutique-meta">
                    <span><i class="fas fa-calendar"></i> Créée le <?= $boutique ? date('d/m/Y', strtotime($boutique['date_creation'])) : date('d/m/Y') ?></span>
                    <span class="meta-divider">•</span>
                    <span><i class="fas fa-star" style="color:var(--warning);"></i> Boutique Active</span>
                </div>
                <div class="boutique-actions">
                    <button class="btn-primary" onclick="window.location.href='ajout_produits.html'">
                        <i class="fas fa-plus"></i><span>Ajouter un produit</span>
                    </button>
                    <a href="index.php?boutique_id=<?= $boutique_id_vendeur ?>" class="btn-outline" target="_blank">
                        <i class="fas fa-eye"></i><span>Voir ma boutique</span>
                    </a>
                    <button class="btn-outline" onclick="shareBoutique()">
                        <i class="fas fa-share-alt"></i><span>Partager</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-header">
                    <div class="stat-icon blue"><i class="fas fa-eye"></i></div>
                    <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 12.5%</div>
                </div>
                <div class="stat-value"><?= number_format($stats['vues']) ?></div>
                <div class="stat-label">Total de vues</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-header">
                    <div class="stat-icon green"><i class="fas fa-users"></i></div>
                    <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 8.3%</div>
                </div>
                <div class="stat-value"><?= number_format($stats['visiteurs']) ?></div>
                <div class="stat-label">Visiteurs uniques</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-header">
                    <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 2.1%</div>
                </div>
                <div class="stat-value"><?= number_format($somme, 0, ',', ' ') ?> FCFA</div>
<div class="stat-label">Chiffres D'affaire</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-header">
                    <div class="stat-icon red"><i class="fas fa-heart"></i></div>
                    <div class="stat-trend positive"><i class="fas fa-arrow-up"></i> 15.7%</div>
                </div>
                <div class="stat-value"><?= number_format(count($favoris)) ?></div>
                <div class="stat-label">J'aime reçus</div>
            </div>
        </div>

        <!-- Content grid -->
        <div class="content-grid">
           <div class="left-column">
    <div class="section-card fade-in-up">
        <div class="section-title">
            <span>📦 Commandes récentes</span>
            <a href="#">Voir tout <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="orders-table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commandes)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">
                            Aucune commande reçue pour l'instant.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($commandes as $cmd): ?>
                    <tr>
                        <td><strong>#<?= $cmd['id'] ?></strong></td>
                        <td>
                            <?= htmlspecialchars($cmd['nom_client']) ?>
                            <div style="font-size:.75rem; color:var(--text-muted);">
                                <?= htmlspecialchars($cmd['telephone']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($cmd['produit_nom'] ?? '—') ?></td>
                        <td><strong><?= number_format($cmd['montant'], 0, ',', ' ') ?> FCFA</strong></td>
                        <td>
                            <?php
                            $badges = [
                                'nouveau'     => 'pending',
                                'confirme'    => 'processing',
                                'preparation' => 'processing',
                                'livre'       => 'completed',
                                'annule'      => 'cancelled',
                            ];
                            $labels = [
                                'nouveau'     => 'Nouveau',
                                'confirme'    => 'Confirmé',
                                'preparation' => 'En préparation',
                                'livre'       => 'Livrée',
                                'annule'      => 'Annulée',
                            ];
                            $classe = $badges[$cmd['statut']] ?? 'pending';
                            $label  = $labels[$cmd['statut']] ?? $cmd['statut'];
                            ?>
                            <span class="status-badge <?= $classe ?>">
                                <?= $label ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

            <div class="right-column">
                <div class="section-card fade-in-up" style="animation-delay:.1s;">
                    <div class="section-title"><span>⚡ Actions rapides</span></div>
                    <div class="quick-actions">
                        <div class="quick-action-btn" onclick="window.location.href='ajout_produits.php'">
                            <i class="fas fa-plus" style="color:var(--success);"></i><div>Ajouter produit</div>
                        </div>
                        <div class="quick-action-btn" onclick="afficherVue('produits')">
                            <i class="fas fa-box" style="color:var(--primary);"></i><div>Modifier Boutique</div>
                        </div>
                        <div class="quick-action-btn">
                            <i class="fas fa-chart-bar" style="color:var(--warning);"></i><div>Analytics</div>
                        </div>
                        <div class="quick-action-btn" onclick="shareBoutique()">
                            <i class="fas fa-share-alt" style="color:var(--secondary);"></i><div>Partager</div>
                        </div>
                    </div>
                </div>

                <div class="section-card fade-in-up" style="margin-top:24px;animation-delay:.2s;">
                    <div class="section-title"><span>🔔 Activité récente</span></div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon" style="background:rgba(16,185,129,.15);color:var(--success);"><i class="fas fa-shopping-bag"></i></div>
                            <div class="activity-content"><div class="activity-title">Nouvelle commande reçue</div><div class="activity-time">Il y a 2 heures</div></div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background:rgba(59,130,246,.15);color:var(--primary);"><i class="fas fa-box"></i></div>
                            <div class="activity-content"><div class="activity-title">Produit ajouté avec succès</div><div class="activity-time">Il y a 5 heures</div></div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon" style="background:rgba(139,92,246,.15);color:var(--secondary);"><i class="fas fa-heart"></i></div>
                            <div class="activity-content"><div class="activity-title">12 nouveaux j'aime</div><div class="activity-time">Aujourd'hui</div></div>
                        </div>
                    </div>
                </div>

                <div class="section-card fade-in-up" style="margin-top:24px;animation-delay:.3s;">
                    <div class="section-title"><span>🎯 Objectifs du mois</span></div>
                    <div class="progress-container">
                        <div class="progress-item">
                            <div class="progress-header"><span class="progress-label">Revenus</span><span class="progress-value">65%</span></div>
                            <div class="progress-bar"><div class="progress-fill" style="width:65%;"></div></div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-header"><span class="progress-label">Commandes</span><span class="progress-value">48/100</span></div>
                            <div class="progress-bar"><div class="progress-fill" style="width:48%;"></div></div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-header"><span class="progress-label">Nouveaux clients</span><span class="progress-value">82%</span></div>
                            <div class="progress-bar"><div class="progress-fill" style="width:82%;"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section-vue content-area" id="vue-produits">

        <div class="produits-section">
            <!-- En-tête -->
            <div class="produits-header">
                <div class="produits-header-left">
                    <div class="produits-header-icon"><i class="fas fa-box-open"></i></div>
                    <h3>Mes produits <span class="produits-count"><?= count($produits) ?></span></h3>
                </div>
                <a href="ajout_produits.HTML" class="btn-ajouter-produit">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
            </div>

            <?php if (!empty($produits)): ?>
            <!-- Recherche -->
            <div class="produits-toolbar">
                <div class="search-produit">
                    <i class="fas fa-search"></i>
                    <input type="text" id="recherche-produit" placeholder="Rechercher un produit...">
                </div>
                <div class="produits-nb" id="compteur-produits">
                    <?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?>
                </div>
            </div>

            <!-- Tableau -->
            <div class="table-wrapper">
                <table class="produits-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Localisation</th>
                            <th>WhatsApp</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-produits">
                        <?php foreach ($produits as $i => $p): ?>
                        <tr class="ligne-produit"
                            data-id="<?= (int)$p['id'] ?>"
                            data-nom="<?= htmlspecialchars($p['nom'], ENT_QUOTES) ?>"
                            style="animation-delay:<?= $i * 0.06 ?>s">
                            <td>
                                <div class="cell-produit">
                                    <div class="produit-img-wrapper">
                                        <?php if (!empty($p['image'])): ?>
                                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" loading="lazy">
                                        <?php else: ?>
                                            <span class="produit-img-placeholder"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="produit-nom"><?= htmlspecialchars($p['nom']) ?></div>
                                        <?php if (!empty($p['description'])): ?>
                                        <div class="produit-desc" title="<?= htmlspecialchars($p['description']) ?>">
                                            <?= htmlspecialchars($p['description']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-prix"><?= number_format((float)$p['prix'], 0, ',', ' ') ?> FCFA</span></td>
                            <td>
                                <span class="cell-lieu">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($p['localisation'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $p['whatsapp']) ?>" target="_blank" rel="noopener" class="cell-wa">
                                    <i class="fab fa-whatsapp"></i><?= htmlspecialchars($p['whatsapp']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="cell-actions">
                                    <a href="modifier_produit.php?id=<?= (int)$p['id'] ?>" class="btn-action btn-modifier">
                                        <i class="fas fa-pen"></i> Modifier
                                    </a>
                                    <button class="btn-action btn-supprimer"
                                        onclick="confirmerSuppression(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['nom'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="produits-footer">
                <div class="produits-footer-info" id="footer-info">
                    Affichage de <strong><?= count($produits) ?></strong> produit<?= count($produits) > 1 ? 's' : '' ?>
                </div>
            </div>

            <?php else: ?>
            <div class="produits-vide">
                <div class="produits-vide-icon">📦</div>
                <h4>Aucun produit pour l'instant</h4>
                <p>Commencez par ajouter votre premier produit.</p>
                <a href="ajout_produits.php" class="btn-ajouter-produit" style="display:inline-flex;margin-top:8px;">
                    <i class="fas fa-plus"></i> Ajouter mon premier produit
                </a>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /vue-produits -->

    <!-- SECTION COMMANDES -->
    <div class="section-vue content-area" id="vue-commandes">
        <div class="section-header">
            <h1><i class="fas fa-shopping-cart"></i> Commandes</h1>
            <p>Gérez et suivez toutes vos commandes</p>
        </div>

        <div class="commandes-container">
            <div class="commandes-stats">
                <div class="stat-box">
                    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#10b981;"><i class="fas fa-inbox"></i></div>
                    <div class="stat-details">
                        <div class="stat-label">Nouvelles</div>
                        <div class="stat-value"><?= count(array_filter($commandes, fn($c) => $c['statut'] === 'nouveau')) ?></div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background:rgba(59,130,246,.15);color:#3b82f6;"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-details">
                        <div class="stat-label">En cours</div>
                        <div class="stat-value"><?= count(array_filter($commandes, fn($c) => $c['statut'] === 'preparation')) ?></div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background:rgba(34,197,94,.15);color:#22c55e;"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-details">
                        <div class="stat-label">Livrées</div>
                        <div class="stat-value"><?= count(array_filter($commandes, fn($c) => $c['statut'] === 'livre')) ?></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="table-wrapper">
                    <table class="standard-table">
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Client</th>
                                <th>Produit</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $cmd): ?>
                            <tr>
                                <td><strong>#<?= $cmd['id'] ?></strong></td>
                                <td><?= htmlspecialchars($cmd['nom_client']) ?></td>
                                <td><?= htmlspecialchars($cmd['produit_nom'] ?? '—') ?></td>
                                <td><strong><?= number_format($cmd['montant'], 0, ',', ' ') ?> FCFA</strong></td>
                                <td>
                                    <select class="status-select" onchange="changerStatutCommande(<?= $cmd['id'] ?>, this.value)">
                                        <option value="nouveau" <?= $cmd['statut'] === 'nouveau' ? 'selected' : '' ?>>Nouveau</option>
                                        <option value="confirme" <?= $cmd['statut'] === 'confirme' ? 'selected' : '' ?>>Confirmé</option>
                                        <option value="preparation" <?= $cmd['statut'] === 'preparation' ? 'selected' : '' ?>>En préparation</option>
                                        <option value="livre" <?= $cmd['statut'] === 'livre' ? 'selected' : '' ?>>Livrée</option>
                                        <option value="annule" <?= $cmd['statut'] === 'annule' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                </td>
                                <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
                                <td>
                                    <button class="action-icon-btn" onclick="afficherCommande(<?= $cmd['id'] ?>)" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- /vue-commandes -->

    <!-- SECTION ANALYTICS -->
    <div class="section-vue content-area" id="vue-analytics">
        <div class="section-header">
            <h1><i class="fas fa-chart-line"></i> Analytics</h1>
            <p>Analysez vos performances et croissance</p>
        </div>

        <div class="analytics-container">
            <div class="analytics-grid">
                <div class="analytics-card">
                    <div class="card-title">Vues</div>
                    <div class="analytics-value"><?= $stats['vues'] ?></div>
                    <div class="analytics-change">+12% ce mois</div>
                </div>
                <div class="analytics-card">
                    <div class="card-title">Visiteurs</div>
                    <div class="analytics-value"><?= $stats['visiteurs'] ?></div>
                    <div class="analytics-change">+8% ce mois</div>
                </div>
                <div class="analytics-card">
                    <div class="card-title">Taux conversion</div>
                    <div class="analytics-value"><?= $stats['conversion'] ?></div>
                    <div class="analytics-change">+3% ce mois</div>
                </div>
                <div class="analytics-card">
                    <div class="card-title">Revenus</div>
                    <div class="analytics-value" style="font-size:1.2rem;"><?= $stats['revenus'] ?></div>
                    <div class="analytics-change">+25% ce mois</div>
                </div>
            </div>

            <div class="section-card">
                <div class="card-title">Ventes par jour (30 derniers jours)</div>
                <div class="chart-placeholder">
                    <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                        <i class="fas fa-chart-bar" style="font-size:3rem;margin-bottom:1rem;opacity:0.3;"></i>
                        <p>Graphique des ventes en cours de développement</p>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /vue-analytics -->

    <!-- SECTION MA BOUTIQUE -->
    <div class="section-vue content-area" id="vue-boutique">
        <div class="section-header">
            <h1><i class="fas fa-store"></i> Ma Boutique</h1>
            <p>Modifiez les informations de votre boutique</p>
        </div>

        <div class="boutique-container">
            <?php if ($boutique): ?>
            <div class="section-card">
                <form id="form-boutique" onsubmit="sauvegarderBoutique(event)">
                    <div class="form-group">
                        <label>Nom de la boutique</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($boutique['nom'] ?? '') ?>" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Adresse</label>
                        <input type="text" name="adresse" value="<?= htmlspecialchars($boutique['adresse'] ?? '') ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Contact WhatsApp</label>
                        <input type="text" name="whatsapp" value="<?= htmlspecialchars($boutique['whatsapp'] ?? '') ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Services</label>
                        <input type="text" name="services" value="<?= htmlspecialchars($boutique['services'] ?? '') ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-input" rows="3"><?= htmlspecialchars($boutique['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Date de création</label>
                        <input type="text" value="<?= htmlspecialchars($boutique['date_creation'] ?? '') ?>" disabled class="form-input" style="background:var(--overlay-bg);">
                    </div>
                    <div class="form-group">
                        <label>Slug URL</label>
                        <input type="text" value="<?= htmlspecialchars($boutique['slug'] ?? '') ?>" disabled class="form-input" style="background:var(--overlay-bg);">
                    </div>
                    <div class="form-group">
                        <label>QR Code</label>
                        <?php if ($boutique['qrcode']): ?>
                        <div style="padding:1rem;background:var(--overlay-bg);border-radius:10px;text-align:center;">
                            <img src="<?= htmlspecialchars($qrcodeUrl) ?>" alt="QR Code" style="max-width:150px;border-radius:5px;">
                            <p style="margin-top:0.5rem;font-size:0.85rem;color:var(--text-muted);">QR Code de votre boutique</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>URL publique de votre boutique</label>
                        <div style="display:flex;gap:10px;">
                            <input type="text" id="url-boutique" readonly value="<?= htmlspecialchars($urlPublique) ?>" class="form-input" style="background:var(--overlay-bg);">
                            <button type="button" onclick="copierURL()" class="btn-primary">
                                <i class="fas fa-copy"></i> Copier
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;margin-top:1rem;">
                        <i class="fas fa-save"></i> Sauvegarder les modifications
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="section-card">
                <div style="text-align:center;padding:2rem;">
                    <i class="fas fa-store" style="font-size:3rem;margin-bottom:1rem;opacity:0.3;"></i>
                    <p>Vous n'avez pas encore créé de boutique.</p>
                    <a href="templates.html" class="btn-primary" style="margin-top:1rem;">
                        <i class="fas fa-plus"></i> Créer une boutique
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div><!-- /vue-boutique -->

    <!-- SECTION PARAMÈTRES -->
    <div class="section-vue content-area" id="vue-parametres">
        <div class="section-header">
            <h1><i class="fas fa-cog"></i> Paramètres</h1>
            <p>Configurez votre compte et préférences</p>
        </div>

        <div class="settings-container">
            <div class="section-card">
                <div class="settings-section">
                    <h3>Email et notifications</h3>
                    <div class="settings-item">
                        <label class="settings-toggle">
                            <input type="checkbox" checked>
                            <span class="switch"></span>
                        </label>
                        <div class="settings-label">
                            <div>Notifications de commandes</div>
                            <small>Recevez une notification à chaque nouvelle commande</small>
                        </div>
                    </div>
                    <div class="settings-item">
                        <label class="settings-toggle">
                            <input type="checkbox" checked>
                            <span class="switch"></span>
                        </label>
                        <div class="settings-label">
                            <div>Alertes de stock</div>
                            <small>Soyez informé quand un produit est en rupture</small>
                        </div>
                    </div>
                    <div class="settings-item">
                        <label class="settings-toggle">
                            <input type="checkbox">
                            <span class="switch"></span>
                        </label>
                        <div class="settings-label">
                            <div>Newsletters</div>
                            <small>Recevez nos actualités et conseils</small>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Confidentialité et sécurité</h3>
                    <button class="btn-settings" onclick="afficherPassword()">
                        <i class="fas fa-key"></i> Changer le mot de passe
                    </button>
                    <button class="btn-settings" onclick="afficherSecurite()">
                        <i class="fas fa-shield-alt"></i> Sécurité du compte
                    </button>
                </div>

                <div class="settings-section">
                    <h3>Danger zone</h3>
                    <button class="btn-danger" onclick="supprimerCompte()">
                        <i class="fas fa-trash-alt"></i> Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div><!-- /vue-parametres -->

    <!-- SECTION PROFIL -->
    <div class="section-vue content-area" id="vue-profil">
        <div class="section-header">
            <h1><i class="fas fa-user"></i> Mon Profil</h1>
            <p>Gérez vos informations personnelles</p>
        </div>

        <div class="profil-container">
            <div class="section-card">
                <form id="form-profil" onsubmit="sauvegarderProfil(event)">
                    <div class="profil-header">
                        <div class="profil-avatar" onclick="changerAvatar()">
                            <i class="fas fa-user"></i>
                            <div class="avatar-edit"><i class="fas fa-camera"></i></div>
                        </div>
                        <div class="profil-info">
                            <h2 id="user-name">Profil utilisateur</h2>
                            <p id="user-email" style="color:var(--text-muted);">email@example.com</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>" class="form-input" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>" class="form-input" placeholder="Votre email" required>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe (laisser vide pour conserver)</label>
                        <input type="password" name="mot_de_passe" class="form-input" placeholder="Nouveau mot de passe">
                    </div>
                    <div class="form-group">
                        <label>Bio (facultatif)</label>
                        <textarea name="bio" class="form-input" rows="4" placeholder="Décrivez-vous..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%;margin-top:1rem;">
                        <i class="fas fa-save"></i> Sauvegarder le profil
                    </button>
                </form>
            </div>
        </div>
    </div><!-- /vue-profil -->

</div><!-- /main-content -->

<!-- ═══ MODAL SUPPRESSION ═══ -->
<div id="modal-supprimer" role="dialog" aria-modal="true">
    <div class="modal-bg" onclick="fermerModalSuppr()"></div>
    <div class="modal-box">
        <div class="modal-danger-icon"><i class="fas fa-trash-alt"></i></div>
        <h3>Supprimer ce produit ?</h3>
        <p>Vous allez supprimer<br><strong id="modal-nom-produit">—</strong><br>Cette action est <strong>irréversible</strong>.</p>
        <div class="modal-actions">
            <button class="btn-annuler" onclick="fermerModalSuppr()"><i class="fas fa-times"></i> Annuler</button>
            <button class="btn-confirmer-suppr" id="btn-confirmer-suppr" onclick="executerSuppression()">
                <i class="fas fa-trash-alt"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<script src="src/js/dahsbord.js"></script>
<script>

function afficherVue(nomVue) {
    // Masquer toutes les sections
    document.querySelectorAll('.section-vue').forEach(function(s) {
        s.classList.remove('active');
    });
    // Afficher la bonne section
    const cible = document.getElementById('vue-' + nomVue);
    if (cible) cible.classList.add('active');

    // Mettre à jour l'état actif dans la sidebar
    document.querySelectorAll('.nav-item').forEach(function(item) {
        item.classList.remove('active');
        if (item.dataset.vue === nomVue) item.classList.add('active');
    });

    // Fermer la sidebar sur mobile
    closeSidebar();
}

// Attacher les clics sur les nav-items
document.querySelectorAll('.nav-item[data-vue]').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        afficherVue(this.dataset.vue);
    });
});

/* ════════════════════════════════════════════════════════════
   PRODUITS — RECHERCHE + SUPPRESSION
════════════════════════════════════════════════════════════ */
(function () {
    let idASupprimer = null;

    // Recherche live
    const inp = document.getElementById('recherche-produit');
    if (inp) {
        inp.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let n = 0;
            document.querySelectorAll('.ligne-produit').forEach(function(tr) {
                const match = tr.textContent.toLowerCase().includes(q);
                tr.style.display = match ? '' : 'none';
                if (match) n++;
            });
            const comp = document.getElementById('compteur-produits');
            const foot = document.getElementById('footer-info');
            if (comp) comp.textContent = n + ' produit' + (n > 1 ? 's' : '');
            if (foot) foot.innerHTML = 'Affichage de <strong>' + n + '</strong> produit' + (n > 1 ? 's' : '');
        });
    }

    window.confirmerSuppression = function(id, nom) {
        idASupprimer = id;
        document.getElementById('modal-nom-produit').textContent = nom;
        document.getElementById('modal-supprimer').classList.add('visible');
        document.body.style.overflow = 'hidden';
    };

    window.fermerModalSuppr = function() {
        document.getElementById('modal-supprimer').classList.remove('visible');
        document.body.style.overflow = '';
        idASupprimer = null;
    };

    window.executerSuppression = async function() {
        if (!idASupprimer) return;
        const btn = document.getElementById('btn-confirmer-suppr');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression...';

        try {
            const fd = new FormData();
            fd.append('id', idASupprimer);
            const res  = await fetch('supprimer_produit.php', { method:'POST', body:fd });
            const data = await res.json();
            fermerModalSuppr();

            if (data.success) {
                const ligne = document.querySelector('.ligne-produit[data-id="' + idASupprimer + '"]');
                if (ligne) {
                    ligne.style.transition = 'opacity .3s, transform .3s';
                    ligne.style.opacity = '0';
                    ligne.style.transform = 'translateX(20px)';
                    setTimeout(function() {
                        ligne.remove();
                        const r = document.querySelectorAll('.ligne-produit').length;
                        const c = document.getElementById('compteur-produits');
                        const f = document.getElementById('footer-info');
                        const b = document.querySelector('.produits-count');
                        if (c) c.textContent = r + ' produit' + (r !== 1 ? 's' : '');
                        if (f) f.innerHTML = 'Affichage de <strong>' + r + '</strong> produit' + (r !== 1 ? 's' : '');
                        if (b) b.textContent = r;
                    }, 320);
                }
                toast('succes', 'fa-check-circle', 'Produit supprimé avec succès.');
            } else {
                toast('erreur', 'fa-exclamation-circle', data.message || 'Erreur.');
            }
        } catch(e) {
            fermerModalSuppr();
            toast('erreur', 'fa-exclamation-circle', 'Erreur réseau.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash-alt"></i> Supprimer';
        }
    };

    function toast(type, icone, msg) {
        const t = document.createElement('div');
        t.className = 'toast-produit ' + type;
        t.innerHTML = '<i class="fas ' + icone + '"></i>' + msg;
        document.body.appendChild(t);
        setTimeout(function() {
            t.style.transition = 'opacity .3s,transform .3s';
            t.style.opacity = '0';
            t.style.transform = 'translateY(10px)';
            setTimeout(function() { t.remove(); }, 350);
        }, 3500);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') fermerModalSuppr();
    });
})();

/* ════════════════════════════════════════════════════════════
   COMMANDES - CHANGEMENT DE STATUT
════════════════════════════════════════════════════════════ */
function changerStatutCommande(id, nouveau_statut) {
    if (!confirm('Êtes-vous sûr de vouloir changer le statut ?')) {
        return;
    }
    
    const fd = new FormData();
    fd.append('commande_id', id);
    fd.append('statut', nouveau_statut);
    
    fetch('update_commande_statut.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('succes', 'Statut mis à jour avec succès');
            } else {
                showToast('erreur', data.message || 'Erreur lors de la mise à jour');
            }
        })
        .catch(() => {
            showToast('erreur', 'Erreur réseau');
        });
}

function afficherCommande(id) {
    alert('Détails de la commande #' + id);
}

/* ════════════════════════════════════════════════════════════
   BOUTIQUE - COPIER & SAUVEGARDER
════════════════════════════════════════════════════════════ */
function copierURL() {
    const url = document.getElementById('url-boutique').value;
    navigator.clipboard.writeText(url).then(() => {
        const toast = document.createElement('div');
        toast.className = 'toast-produit succes';
        toast.innerHTML = '<i class="fas fa-check-circle"></i>URL copiée dans le presse-papiers';
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity .3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }).catch(() => {
        alert('Erreur lors de la copie');
    });
}

function sauvegarderBoutique(e) {
    e.preventDefault();
    const form = document.getElementById('form-boutique');
    const fd = new FormData(form);

    fetch('update_boutique.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('succes', 'Boutique mise à jour avec succès');
            } else {
                showToast('erreur', data.message || 'Erreur lors de la mise à jour');
            }
        })
        .catch(() => showToast('erreur', 'Erreur réseau'));
}

/* ════════════════════════════════════════════════════════════
   PROFIL - SAUVEGARDER
════════════════════════════════════════════════════════════ */
function sauvegarderProfil(e) {
    e.preventDefault();
    const form = document.getElementById('form-profil');
    const fd = new FormData(form);

    fetch('update_profil.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('succes', 'Profil mis à jour avec succès');
            } else {
                showToast('erreur', data.message || 'Erreur lors de la mise à jour');
            }
        })
        .catch(() => showToast('erreur', 'Erreur réseau'));
}

function showToast(type, msg) {
    const toast = document.createElement('div');
    toast.className = 'toast-produit ' + type;
    toast.innerHTML = '<i class="fas ' + (type === 'succes' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i>' + msg;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity .3s, transform .3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function changerAvatar() {
    alert('Fonctionnalité de téléchargement de photo en cours de développement');
}

/* ════════════════════════════════════════════════════════════
   PARAMÈTRES
════════════════════════════════════════════════════════════ */
function afficherPassword() {
    const pwd = prompt('Entrez votre nouveau mot de passe:');
    if (pwd) {
        alert('Mot de passe mis à jour (fonctionnalité en développement)');
    }
}

function afficherSecurite() {
    alert('Page de sécurité en cours de développement');
}

function supprimerCompte() {
    if (confirm('⚠️ Êtes-vous ABSOLUMENT sûr ? Cette action est définitive!')) {
        if (confirm('Tapez "OUI" pour confirmer la suppression de votre compte')) {
            alert('Suppression en cours (fonctionnalité en développement)');
        }
    }
}

/* ════════════════════════════════════════════════════════════
   FONCTIONS SUPPLÉMENTAIRES
════════════════════════════════════════════════════════════ */
function shareBoutique() {
    const url = document.getElementById('url-boutique')?.value || window.location.href;
    if (navigator.share) {
        navigator.share({
            title: 'Ma Boutique Creator Market',
            text: 'Découvrez ma boutique sur Creator Market!',
            url: url
        }).catch(err => console.log('Erreur partage:', err));
    } else {
        alert('Lien à partager: ' + url);
    }
}

function toggleTheme() {
    const current = document.body.getAttribute('data-theme');
    const newTheme = current === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', newTheme);
    localStorage.setItem('dashboard-theme', newTheme);
    document.getElementById('themeIcon').className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
}

function toggleLanguage() {
    alert('Changement de langue en cours de développement');
}

function openNotifications() {
    alert('Notifications: Vous n\'avez pas de nouvelles notifications');
}

function openWhatsApp() {
    window.open('https://wa.me/1234567890', '_blank');
}

function upgradePremium() {
    alert('Plan Premium en cours de développement');
}

// Restaurer le thème au chargement
window.addEventListener('load', function() {
    const savedTheme = localStorage.getItem('dashboard-theme') || 'dark';
    document.body.setAttribute('data-theme', savedTheme);
});
</script>
</body>
</html>