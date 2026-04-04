<?php
session_start();
try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $produits = $pdo->query("SELECT * FROM produits ORDER BY id DESC")->fetchAll();

    // Récupérer les boutiques favorites de l'utilisateur connecté
    $boutiques_favorites = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT boutique_id FROM favoris WHERE utilisateur_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $boutiques_favorites = array_column($stmt->fetchAll(), 'boutique_id');
    }
} catch (Exception $e) {
    $produits = [];
    $boutiques_favorites = [];
}
$total = count($produits);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Creator Market — Marché Local</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bl:   #1A56F0;
    --bl2:  #0D3ED4;
    --bl3:  #E8EFFE;
    --bl4:  #C7D6FC;
    --vert: #0EBF7A;
    --vert2:#D1FAF0;
    --rouge:#F03B3B;
    --txt:  #0F1729;
    --txt2: #5A6585;
    --txt3: #9BA4BE;
    --bg:   #F5F7FF;
    --card: #FFFFFF;
    --brd:  #E4E9F7;
    --r:    16px;
    --r2:   24px;
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'DM Sans',sans-serif;
    background:var(--bg);
    color:var(--txt);
    -webkit-font-smoothing:antialiased;
    overflow-x:hidden;
}

/* ── NAV ─────────────────────────────────────────────── */
nav{
    position:fixed;top:0;left:0;right:0;z-index:200;
    background:rgba(255,255,255,0.92);
    backdrop-filter:blur(16px);
    border-bottom:1px solid var(--brd);
    height:66px;
    display:flex;align-items:center;
    padding:0 2rem;
    gap:1rem;
}
.nav-logo{
    font-family:'Sora',sans-serif;
    font-weight:800;font-size:1.25rem;
    color:var(--bl);
    display:flex;align-items:center;gap:8px;
    flex-shrink:0;
    margin-right:auto;
}
.nav-logo span{
    background:var(--bl);color:#fff;
    border-radius:8px;width:32px;height:32px;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:0.9rem;
}
.nav-links{display:flex;gap:4px;}
.nav-links button{
    background:none;border:none;
    font-family:'DM Sans',sans-serif;
    font-size:0.875rem;font-weight:500;
    color:var(--txt2);
    padding:8px 14px;border-radius:10px;
    cursor:pointer;transition:all .2s;
}
.nav-links button:hover,.nav-links button.active{
    background:var(--bl3);color:var(--bl);
}
.nav-actions{display:flex;align-items:center;gap:8px;}
.nav-icon-btn{
    width:38px;height:38px;border-radius:10px;
    background:none;border:1.5px solid var(--brd);
    color:var(--txt2);font-size:0.9rem;
    cursor:pointer;transition:all .2s;
    display:flex;align-items:center;justify-content:center;
}
.nav-icon-btn:hover{background:var(--bl3);border-color:var(--bl4);color:var(--bl);}
.hamburger{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:4px;}
.hamburger div{width:22px;height:2px;background:var(--txt);border-radius:2px;transition:all .3s;}

/* ── HERO ────────────────────────────────────────────── */
.hero{
    margin-top:66px;
    background:linear-gradient(135deg,#1A56F0 0%,#0D3ED4 40%,#1565E8 70%,#2979FF 100%);
    padding:5rem 2rem 7rem;
    text-align:center;
    position:relative;
    overflow:hidden;
}
/* Motif grille */
.hero::before{
    content:'';position:absolute;inset:0;
    background-image:
        linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);
    background-size:40px 40px;
}
/* Cercles décoratifs */
.hero-orb{
    position:absolute;border-radius:50%;
    background:rgba(255,255,255,.08);
    pointer-events:none;
}
.hero-orb.a{width:400px;height:400px;top:-150px;right:-100px;}
.hero-orb.b{width:250px;height:250px;bottom:-80px;left:5%;}
.hero-orb.c{width:120px;height:120px;top:30%;left:8%;}

.hero-inner{position:relative;z-index:1;max-width:700px;margin:0 auto;}

.hero-badge{
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(255,255,255,.15);
    border:1px solid rgba(255,255,255,.25);
    border-radius:100px;
    padding:6px 16px;
    font-size:.78rem;font-weight:600;
    color:rgba(255,255,255,.9);
    letter-spacing:.08em;text-transform:uppercase;
    margin-bottom:1.5rem;
}
.hero-badge i{color:#FFD700;}

.hero h1{
    font-family:'Sora',sans-serif;
    font-size:clamp(2rem,5vw,3.2rem);
    font-weight:800;
    color:#fff;
    line-height:1.15;
    letter-spacing:-.02em;
    margin-bottom:1rem;
}
.hero h1 em{
    font-style:normal;
    background:linear-gradient(90deg,#FFD700,#FFA726);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    background-clip:text;
}

.hero p{
    color:rgba(255,255,255,.82);
    font-size:1.05rem;font-weight:300;
    line-height:1.7;margin-bottom:2.5rem;
}

.search-wrap{
    position:relative;
    max-width:580px;margin:0 auto;
}
.search-wrap i{
    position:absolute;left:20px;top:50%;transform:translateY(-50%);
    color:var(--txt3);font-size:1rem;pointer-events:none;
}
.search-input{
    width:100%;
    padding:1rem 1.25rem 1rem 3rem;
    border-radius:100px;
    border:none;
    font-family:'DM Sans',sans-serif;
    font-size:.95rem;font-weight:500;
    background:#fff;
    box-shadow:0 8px 32px rgba(0,0,0,.18);
    outline:none;
    color:var(--txt);
    transition:box-shadow .2s;
}
.search-input:focus{box-shadow:0 8px 40px rgba(0,0,0,.25);}
.search-input::placeholder{color:var(--txt3);}

/* Vague en bas du hero */
.hero-wave{
    position:absolute;bottom:-1px;left:0;right:0;
    line-height:0;
}
.hero-wave svg{display:block;width:100%;}

/* ── FILTRES ─────────────────────────────────────────── */
.filtres{
    background:#fff;
    border-bottom:1px solid var(--brd);
    position:sticky;top:66px;z-index:100;
    padding:.75rem 2rem;
    display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;
}
.filtre-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 16px;
    border-radius:100px;
    border:1.5px solid var(--brd);
    background:#fff;
    font-family:'DM Sans',sans-serif;
    font-size:.82rem;font-weight:600;
    color:var(--txt2);
    cursor:pointer;transition:all .2s;
    white-space:nowrap;
}
.filtre-btn:hover{border-color:var(--bl4);background:var(--bl3);color:var(--bl);}
.filtre-btn.actif{
    background:var(--bl);color:#fff;
    border-color:var(--bl);
    box-shadow:0 4px 14px rgba(26,86,240,.3);
}
.filtre-sep{flex:1;}
.filtre-select{
    padding:8px 16px;
    border-radius:100px;
    border:1.5px solid var(--brd);
    font-family:'DM Sans',sans-serif;
    font-size:.82rem;font-weight:500;
    color:var(--txt2);
    background:#fff;
    cursor:pointer;outline:none;
    transition:border-color .2s;
}
.filtre-select:focus{border-color:var(--bl);}

/* ── CONTENU PRINCIPAL ───────────────────────────────── */
.main{
    max-width:1360px;margin:0 auto;
    padding:2.5rem 2rem 5rem;
}
.main-header{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:1.75rem;flex-wrap:wrap;gap:.75rem;
}
.main-titre{
    font-family:'Sora',sans-serif;
    font-size:1.4rem;font-weight:700;
    color:var(--txt);
}
.main-titre span{color:var(--bl);}
.main-sous{
    display:flex;align-items:center;gap:6px;
    font-size:.82rem;font-weight:600;
    color:var(--vert);
    background:var(--vert2);
    padding:5px 12px;border-radius:100px;
}

/* ── GRILLE PRODUITS ─────────────────────────────────── */
.grille{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:1.5rem;
}

/* ── CARTE PRODUIT ───────────────────────────────────── */
.carte{
    background:var(--card);
    border-radius:var(--r2);
    border:1px solid var(--brd);
    overflow:hidden;
    transition:all .3s cubic-bezier(.4,0,.2,1);
    animation:entree .5s ease both;
    box-shadow:0 2px 12px rgba(26,86,240,.06);
    display:flex;flex-direction:column;
}
@keyframes entree{
    from{opacity:0;transform:translateY(24px);}
    to{opacity:1;transform:translateY(0);}
}
.carte:nth-child(2){animation-delay:.07s;}
.carte:nth-child(3){animation-delay:.14s;}
.carte:nth-child(4){animation-delay:.21s;}
.carte:nth-child(5){animation-delay:.28s;}
.carte:nth-child(6){animation-delay:.35s;}

.carte:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 48px rgba(26,86,240,.14);
    border-color:var(--bl4);
}

.carte-img{
    position:relative;height:210px;overflow:hidden;
    background:linear-gradient(135deg,#e8effe,#d0dcfd);
}
.carte-img img{
    width:100%;height:100%;object-fit:cover;
    transition:transform .4s ease;
}
.carte:hover .carte-img img{transform:scale(1.06);}

/* Overlay dégradé sur image */
.carte-img::after{
    content:'';position:absolute;inset:0;
    background:linear-gradient(180deg,transparent 50%,rgba(10,20,60,.35) 100%);
}

/* Badge favori */
.btn-coeur{
    position:absolute;top:12px;right:12px;z-index:2;
    width:36px;height:36px;border-radius:50%;
    background:rgba(255,255,255,.9);
    border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    font-size:1rem;
    transition:all .25s;
    backdrop-filter:blur(4px);
}
.btn-coeur:hover{background:#fff;transform:scale(1.15);}
.btn-coeur.actif{color:var(--rouge);}

/* Badge prix flottant */
.badge-prix{
    position:absolute;bottom:12px;left:12px;z-index:2;
    background:rgba(255,255,255,.95);
    border-radius:100px;
    padding:4px 12px;
    font-family:'Sora',sans-serif;
    font-size:.82rem;font-weight:700;
    color:var(--vert);
    backdrop-filter:blur(4px);
}
.badge-prix::before{content:'XAF ';font-size:.7rem;font-weight:500;color:var(--txt2);}

.carte-body{padding:1.25rem;flex:1;display:flex;flex-direction:column;gap:.6rem;}

.carte-nom{
    font-family:'Sora',sans-serif;
    font-size:1.05rem;font-weight:700;
    color:var(--txt);
    line-height:1.3;
}

.carte-desc{
    font-size:.82rem;color:var(--txt2);
    line-height:1.5;
    display:-webkit-box;-webkit-line-clamp:2;
    -webkit-box-orient:vertical;overflow:hidden;
    flex:1;
}

.carte-lieu{
    display:flex;align-items:center;gap:6px;
    font-size:.8rem;font-weight:500;color:var(--txt2);
}
.carte-lieu i{color:var(--bl);font-size:.75rem;}

/* Séparateur */
.carte-sep{height:1px;background:var(--brd);margin:.25rem 0;}

.carte-actions{display:flex;gap:.5rem;}

.btn-commander{
    flex:1;
    display:flex;align-items:center;justify-content:center;gap:7px;
    background:var(--bl);color:#fff;
    border:none;border-radius:12px;
    padding:.75rem 1rem;
    font-family:'DM Sans',sans-serif;
    font-size:.85rem;font-weight:700;
    cursor:pointer;transition:all .2s;
    letter-spacing:.01em;
}
.btn-commander:hover{
    background:var(--bl2);
    box-shadow:0 6px 20px rgba(26,86,240,.35);
    transform:translateY(-1px);
}
a{
    text-decoration: none;
}
.btn-voir{
    outline:none;
    width:40px;height:40px;
    border-radius:12px;
    border:1.5px solid var(--brd);
    background:#fff;
    color:var(--txt2);
    font-size:.85rem;
    cursor:pointer;transition:all .2s;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;
}
.btn-voir:hover{border-color:var(--bl4);background:var(--bl3);color:var(--bl);}
.btn-partage{
    width:40px;height:40px;
    border-radius:12px;
    border:none;
    background:var(--vert2);
    color:var(--vert);
    font-size:.85rem;
    cursor:pointer;transition:all .2s;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;
}
.btn-partage:hover{background:var(--vert);color:#fff;transform:translateY(-1px);}

/* ── ÉTAT VIDE ────────────────────────────────────────── */
.vide{
    grid-column:1/-1;
    text-align:center;padding:5rem 2rem;
    color:var(--txt3);
}
.vide i{font-size:3rem;margin-bottom:1rem;display:block;color:var(--bl4);}
.vide p{font-size:1.05rem;font-weight:500;}

/* ── PANIER FLOTTANT ─────────────────────────────────── */
.panier-btn{
    position:fixed;bottom:2rem;right:2rem;z-index:300;
    width:60px;height:60px;border-radius:50%;
    background:var(--bl);color:#fff;
    border:none;font-size:1.3rem;
    cursor:pointer;
    box-shadow:0 8px 28px rgba(26,86,240,.4);
    transition:all .3s cubic-bezier(.4,0,.2,1);
    display:flex;align-items:center;justify-content:center;
}
.panier-btn:hover{transform:scale(1.1) translateY(-3px);box-shadow:0 12px 36px rgba(26,86,240,.5);}
.panier-count{
    position:absolute;top:-4px;right:-4px;
    background:var(--rouge);color:#fff;
    font-size:.7rem;font-weight:700;
    border-radius:50%;min-width:20px;height:20px;
    display:flex;align-items:center;justify-content:center;
    border:2.5px solid var(--bg);
    opacity:0;transform:scale(0);
    transition:all .3s cubic-bezier(.68,-.55,.265,1.55);
}
.panier-count.visible{opacity:1;transform:scale(1);}

/* ── TOAST ───────────────────────────────────────────── */
#toasts{
    position:fixed;top:80px;right:1.5rem;z-index:1000;
    display:flex;flex-direction:column;gap:.6rem;
}
.toast{
    background:#fff;
    border:1px solid var(--brd);
    border-left:4px solid var(--bl);
    border-radius:var(--r);
    padding:.85rem 1.1rem;
    font-size:.85rem;font-weight:600;
    color:var(--txt);
    box-shadow:0 8px 24px rgba(0,0,0,.1);
    display:flex;align-items:center;gap:10px;
    animation:toast-in .35s ease;
    max-width:320px;
}
@keyframes toast-in{
    from{opacity:0;transform:translateX(60px);}
    to{opacity:1;transform:translateX(0);}
}
.toast.out{animation:toast-out .3s ease forwards;}
@keyframes toast-out{
    to{opacity:0;transform:translateX(60px);}
}
.toast i{color:var(--vert);font-size:1rem;flex-shrink:0;}

/* ── RESPONSIVE ──────────────────────────────────────── */
@media(max-width:900px){
    .nav-links{display:none;}
    .hamburger{display:flex;}
    .grille{grid-template-columns:repeat(auto-fill,minmax(260px,1fr));}
}
@media(max-width:600px){
    .hero{padding:4rem 1.25rem 6rem;}
    .filtres{padding:.6rem 1rem;}
    .main{padding:1.75rem 1rem 4rem;}
    .grille{grid-template-columns:1fr;}
    .filtre-sep{display:none;}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="nav-logo">
        <span><i class="fas fa-store"></i></span>
        Creator Market
    </div>
    <div class="nav-links">
        <button onclick="window.location.href='acceuil.php'"><i class="fas fa-home"></i> Accueil</button>
        <button class="active"><i class="fas fa-th"></i> Marché</button>
        <button onclick="location.href='templates.html'"><i class="fas fa-crown"></i> Créer</button>
        <button><i class="fas fa-user"></i> Dashboard</button>
    </div>
    <div class="nav-actions">
        <button class="nav-icon-btn" title="Notifications"><i class="fas fa-bell"></i></button>
        <button class="nav-icon-btn" title="Langue"><i class="fas fa-globe"></i></button>
        <button class="hamburger" onclick="toggleMenu()" aria-label="Menu">
            <div></div><div></div><div></div>
        </button>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-orb a"></div>
    <div class="hero-orb b"></div>
    <div class="hero-orb c"></div>
    <div class="hero-inner">
        <div class="hero-badge"><i class="fas fa-star"></i> Marché local camerounais</div>
        <h1>Découvrez les <em>créateurs</em><br>de votre quartier</h1>
        <p>Plus de <?= $total ?: '7' ?> articles locaux vous attendent.<br>Commandez directement via WhatsApp.</p>
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="search" class="search-input" id="search-input"
                placeholder="Recherchez un article, une boutique, une ville…">
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,40 C240,80 480,0 720,40 C960,80 1200,10 1440,40 L1440,60 L0,60 Z" fill="#F5F7FF"/>
        </svg>
    </div>
</section>

<!-- FILTRES -->
<div class="filtres" id="filtres">
    <button class="filtre-btn actif" data-cat="tous"><i class="fas fa-th-large"></i> Tous</button>
    <button class="filtre-btn" data-cat="restaurant"><i class="fas fa-utensils"></i> Restaurant</button>
    <button class="filtre-btn" data-cat="beaute"><i class="fas fa-spa"></i> Beauté</button>
    <button class="filtre-btn" data-cat="electronique"><i class="fas fa-tv"></i> Électronique</button>
    <button class="filtre-btn" data-cat="mode"><i class="fas fa-tshirt"></i> Mode</button>
    <div class="filtre-sep"></div>
    <select class="filtre-select" id="city-select">
        <option value="">Toutes les villes</option>
        <option value="yaounde">Yaoundé</option>
        <option value="douala">Douala</option>
        <option value="bafoussam">Bafoussam</option>
        <option value="garoua">Garoua</option>
        <option value="kribi">Kribi</option>
    </select>
    <select class="filtre-select" id="sort-select">
        <option value="recent">Plus récents</option>
        <option value="prix-asc">Prix croissant</option>
        <option value="prix-desc">Prix décroissant</option>
    </select>
</div>

<!-- CONTENU -->
<main class="main">
    <div class="main-header">
        <div class="main-titre">
            <span id="count-label"><?= $total ?></span> article<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?>
        </div>
        <div class="main-sous"><i class="fas fa-fire"></i> Articles en vedette</div>
    </div>

    <div class="grille" id="grille">
        <?php if(empty($produits)): ?>
        <div class="vide">
            <i class="fas fa-box-open"></i>
            <p>Aucun produit disponible pour le moment.</p>
        </div>
        <?php else: ?>
        <?php foreach($produits as $p): ?>
            <?php $p['boutique_id'] ?? 'VIDE' ?>
            
        <div class="carte"
        data-id="<?= $p['id'] ?>"
    data-boutique="<?= $p['boutique_id'] ?>"
            data-nom="<?= htmlspecialchars($p['nom']) ?>"
            data-desc="<?= htmlspecialchars($p['description']) ?>"
            data-lieu="<?= htmlspecialchars($p['localisation']) ?>"
            data-prix="<?= htmlspecialchars($p['prix']) ?>"
            data-wa="<?= htmlspecialchars($p['whatsapp']) ?>">
            <div class="carte-img">
                <img src="<?= htmlspecialchars($p['image']) ?>"
                     alt="<?= htmlspecialchars($p['nom']) ?>"
                     loading="lazy"
                     onerror="this.src='https://via.placeholder.com/400x210/E8EFFE/1A56F0?text=Image'">
<button class="btn-coeur<?= in_array($p['boutique_id'], $boutiques_favorites) ? ' actif' : '' ?>" data-id="<?= $p['boutique_id'] ?>" title="Ajouter aux favoris"><?= in_array($p['boutique_id'], $boutiques_favorites) ? '♥' : '♡' ?></button>                <div class="badge-prix"><?= number_format($p['prix'],0,',',' ') ?></div>
            </div>
            <div class="carte-body">
                <div class="carte-nom"><?= htmlspecialchars($p['nom']) ?></div>
                <div class="carte-desc"><?= htmlspecialchars($p['description']) ?></div>
                <div class="carte-lieu">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($p['localisation']) ?>
                </div>
                <div class="carte-sep"></div>
                <div class="carte-actions">
                    <button class="btn-commander">
                        <i class="fab fa-whatsapp"></i> Commander
                    </button>
    <a href="index.php?boutique_id=<?= $p['boutique_id'] ?>" class="btn-voir">
        <i class="fas fa-eye"></i>
        <!-- <span>Voir ma boutique</span> -->
    </a>
                    <!-- <button class="btn-voir" title="Voir le détail"><i class="fas fa-eye"></i></button> -->
                    <button class="btn-partage" title="Partager"><i class="fas fa-share-alt"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Panier flottant -->
<button class="panier-btn" id="panier-btn" title="Panier">
    <i class="fas fa-shopping-bag"></i>
    <span class="panier-count" id="panier-count">0</span>
</button>

<!-- Zone toasts -->
<div id="toasts"></div>
<style>
     #modal-commande {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        #modal-commande.visible { display: flex; }
 
        .cmd-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10, 10, 30, 0.55);
            backdrop-filter: blur(5px);
        }
 
        .cmd-box {
            position: relative;
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 460px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 32px 80px rgba(0,0,0,.22);
            animation: cmdPop .32s cubic-bezier(.175,.885,.32,1.275);
            font-family: 'Figtree', 'Inter', sans-serif;
        }
        @keyframes cmdPop {
            from { transform: scale(.88) translateY(20px); opacity: 0; }
            to   { transform: scale(1)   translateY(0);    opacity: 1; }
        }
 
        /* ── En-tête ── */
        .cmd-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px 14px;
            border-bottom: 1.5px solid #f0ede8;
            gap: 12px;
        }
        .cmd-produit-info { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
        .cmd-produit-img {
            width: 52px; height: 52px;
            border-radius: 10px;
            overflow: hidden;
            border: 1.5px solid #e8e5df;
            background: #f5f4f0;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: #ccc; font-size: 20px;
        }
        .cmd-produit-img img { width: 100%; height: 100%; object-fit: cover; }
        .cmd-produit-nom {
            font-weight: 700; font-size: .92rem;
            color: #1a1a1a;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            max-width: 220px;
        }
        .cmd-produit-prix {
            font-size: .78rem; color: #15803d;
            font-weight: 700; margin-top: 2px;
        }
        .cmd-close {
            background: #f5f4f0; border: none;
            width: 34px; height: 34px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #888; font-size: 15px;
            flex-shrink: 0; transition: all .15s;
        }
        .cmd-close:hover { background: #e8e5df; color: #1a1a1a; }
 
        /* ── Corps ── */
        .cmd-body { padding: 20px; }
        .cmd-titre {
            font-size: 1.1rem; font-weight: 800;
            color: #1a1a1a; margin-bottom: 5px;
            display: flex; align-items: center; gap: 8px;
        }
        .cmd-titre i { color: #25D366; }
        .cmd-sous-titre { font-size: .8rem; color: #888; margin-bottom: 20px; line-height: 1.5; }
 
        /* ── Champs ── */
        .cmd-form { display: flex; flex-direction: column; gap: 0; }
        .cmd-champ { margin-bottom: 14px; }
        .cmd-champ label {
            display: block;
            font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .7px;
            color: #666; margin-bottom: 6px;
        }
        .req { color: #dc2626; }
 
        .cmd-input-wrap {
            display: flex; align-items: center; gap: 10px;
            background: #faf9f7;
            border: 1.5px solid #e0ddd7;
            border-radius: 10px;
            padding: 0 13px;
            transition: border-color .15s, box-shadow .15s;
        }
        .cmd-input-wrap:focus-within {
            border-color: #25D366;
            box-shadow: 0 0 0 3px rgba(37,211,102,.1);
            background: #fff;
        }
        .cmd-input-wrap i { color: #aaa; font-size: 14px; flex-shrink: 0; }
        .cmd-input-wrap input,
        .cmd-input-wrap textarea {
            flex: 1; border: none; outline: none;
            background: transparent;
            font-family: inherit; font-size: .9rem;
            color: #1a1a1a; padding: 11px 0;
        }
        .cmd-input-wrap input::placeholder,
        .cmd-input-wrap textarea::placeholder { color: #c5c1ba; }
        .cmd-textarea-wrap { align-items: flex-start; padding-top: 10px; padding-bottom: 10px; }
        .cmd-textarea-wrap i { margin-top: 2px; }
        .cmd-textarea-wrap textarea { resize: none; line-height: 1.45; }
 
        .cmd-champ-row { display: flex; gap: 12px; }
 
        /* Total */
        .cmd-total {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 800; font-size: 1rem;
            color: #15803d;
            height: 44px;
            display: flex; align-items: center;
            margin-top: 0;
        }
 
        /* Erreur */
        .cmd-erreur {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: .82rem; color: #991b1b;
            margin-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }
 
        /* Bouton valider */
        .cmd-btn-valider {
            width: 100%;
            padding: 14px;
            background:blue;
            color: #fff; border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: .95rem; font-weight: 800;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            transition: all .2s;
            box-shadow: 0 4px 18px rgba(37,211,102,.35);
            margin-bottom: 12px;
        }
        .cmd-btn-valider:hover { background: #1da851; transform: translateY(-2px); box-shadow: 0 6px 24px rgba(37,211,102,.45); }
        .cmd-btn-valider:active { transform: translateY(0); }
        .cmd-btn-valider.loading { opacity: .7; pointer-events: none; }
        .cmd-btn-valider.loading i { animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
 
        .cmd-mention {
            text-align: center; font-size: .72rem; color: #aaa;
            display: flex; align-items: center; justify-content: center; gap: 5px;
        }
        .cmd-mention i { color: #ccc; }
 
        /* Scrollbar de la modal */
        .cmd-box::-webkit-scrollbar { width: 5px; }
        .cmd-box::-webkit-scrollbar-thumb { background: #e0ddd7; border-radius: 3px; }
 
        @media (max-width: 480px) {
            .cmd-box { border-radius: 16px; }
            .cmd-champ-row { flex-direction: column; gap: 14px; }
        }
    
</style>
<div id="modal-commande" aria-modal="true" role="dialog" aria-labelledby="modal-cmd-titre">
        <div class="cmd-overlay" id="cmd-overlay"></div>
        <div class="cmd-box">
 
            <!-- En-tête -->
            <div class="cmd-head">
                <div class="cmd-produit-info">
                    <div class="cmd-produit-img" id="cmd-img-wrap">
                        <i class="fas fa-image"></i>
                    </div>
                    <div>
                        <div class="cmd-produit-nom" id="cmd-nom-produit">—</div>
                        <div class="cmd-produit-prix" id="cmd-prix-produit">—</div>
                    </div>
                </div>
                <button class="cmd-close" id="cmd-close" aria-label="Fermer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
 
            <!-- Titre -->
            <div class="cmd-body">
                <h2 class="cmd-titre" id="modal-cmd-titre">
                    <i class="fab fa-whatsapp"></i> Passer votre commande
                </h2>
                <p class="cmd-sous-titre">
                    Remplissez vos informations — vous serez redirigé vers WhatsApp automatiquement.
                </p>
 
                <!-- Formulaire -->
                <div class="cmd-form">
 
                    <div class="cmd-champ">
                        <label for="cmd-nom">Votre prenom <span class="req">*</span></label>
                        <div class="cmd-input-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" name="nom_client" id="cmd-nom" placeholder="Ex : brayan" maxlength="100" autocomplete="name">
                        </div>
                    </div>
 
                    <div class="cmd-champ">
                        <label for="cmd-tel">Numéro de téléphone <span class="req">*</span></label>
                        <div class="cmd-input-wrap">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="cmd-tel" name="telephone" placeholder="+237 657300644" maxlength="20" autocomplete="tel">
                        </div>
                    </div>
 
                    <div class="cmd-champ cmd-champ-row">
                        <div class="cmd-champ" style="flex:1;margin-bottom:0;">
                            <label for="cmd-qte">Quantité <span class="req">*</span></label>
                            <div class="cmd-input-wrap">
                                <i class="fas fa-hashtag"></i>
                                <input type="number" id="cmd-qte" name="quantite" value="1" min="1" max="99">
                            </div>
                        </div>
                        <div class="cmd-champ" style="flex:2;margin-bottom:0;">
                            <label>Total estimé</label>
                            <div class="cmd-total"  id="cmd-total">—</div>
                        </div>
                    </div>
 
                    <div class="cmd-champ">
                        <label for="cmd-note">Note (optionnel)</label>
                        <div class="cmd-input-wrap cmd-textarea-wrap">
                            <i class="fas fa-comment-alt"></i>
                            <textarea id="cmd-note" name="note" placeholder="Couleur, taille, adresse de livraison…" rows="2" maxlength="300"></textarea>
                        </div>
                    </div>
 
                    <div class="cmd-erreur" id="cmd-erreur" style="display:none;"></div>
 
                    <button class="cmd-btn-valider" id="cmd-btn-valider">
                        <i class="fab fa-whatsapp"></i>
                        Confirmer et ouvrir WhatsApp
                    </button>
 
                    <p class="cmd-mention">
                        <i class="fas fa-lock"></i>
                        Vos informations sont uniquement partagées avec le vendeur.
                    </p>
                </div>
            </div>
        </div>
    </div>
   
<script src="src/js/marcher.js">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</body>
</html>