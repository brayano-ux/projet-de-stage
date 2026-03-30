<?php
session_start();
require_once __DIR__ . '/config/index.php';

// Connexion à la base de données via la configuration
try {
    $pdo = DatabaseConfig::getConnection();
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
$boutique_id = $_GET['boutique_id'] ?? null;
$boutique_info = null;

if ($boutique_id) {
    // Récupérer les informations de la boutique
    $stmtBoutique = $pdo->prepare("
        SELECT 
            b.id,
            b.nom,
            b.description,
            b.logo,
            b.whatsapp,
            b.adresse,
            b.date_creation,
            u.nom AS proprietaire_nom
        FROM boutiques b
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id
        WHERE b.id = ?
    ");
    $stmtBoutique->execute([$boutique_id]);
    $boutique_info = $stmtBoutique->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les produits de la boutique
    $stmt = $pdo->prepare("
        SELECT
            p.id           AS produit_id,
            p.nom          AS nom,
            p.description  AS description,
            p.prix         AS prix,
            p.image        AS image,
            p.localisation AS localisation,
            p.date_ajout   AS date_ajout,
            b.nom          AS boutique_nom,
            b.whatsapp     AS whatsapp
        FROM produits p
        INNER JOIN boutiques b ON p.boutique_id = b.id
        WHERE p.boutique_id = :boutique_id
        ORDER BY p.date_ajout DESC
    ");
    $stmt->execute([':boutique_id' => $boutique_id]);
    $produits = $stmt->fetchAll();
} else {
    $produits = [];
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <?php if ($boutique_info): ?>
  <title><?= htmlspecialchars($boutique_info['nom']) ?> - Boutique à <?= htmlspecialchars($boutique_info['adresse'] ?? 'Yaoundé') ?></title>
  <?php else: ?>
  <title>Beauty Innova - Salon de beauté à Yaoundé</title>
  <?php endif; ?>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500;1,700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/f005d38d38.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="NOUVEAU/assets/css/style-starter.css">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

  <style>
    /* ─── VARIABLES ─────────────────────────────────────────── */
    :root {
      --pink:    #f50499;
      --pink-dk: #c40378;
      --purple:  #764ba2;
      --blue:    #667eea;
      --text:    #2d3748;
      --muted:   #6c757d;
      --radius:  18px;
      --shadow:  0 10px 40px rgba(0,0,0,.1);
    }

    /* ─── GRILLE PRODUITS ───────────────────────────────────── */
    #products-content {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
      gap: 28px;
      padding: 10px 0;
    }

    /* ─── CARTE PRODUIT ─────────────────────────────────────── */
    .service-card {
      background: #fff;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: transform .3s ease, box-shadow .3s ease;
      display: flex;
      flex-direction: column;
    }
    .service-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 18px 50px rgba(0,0,0,.16);
    }

    .image-container {
      position: relative;
      overflow: hidden;
      height: 220px;
    }
    .service-image {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform .5s ease;
    }
    .service-card:hover .service-image { transform: scale(1.08); }

    .image-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.65) 0%, transparent 55%);
      opacity: 0; transition: opacity .3s;
    }
    .service-card:hover .image-overlay { opacity: 1; }

    .service-title {
      position: absolute; bottom: 16px; left: 16px;
      color: #fff; font-size: 18px; font-weight: 700;
      opacity: 0; transform: translateY(14px);
      transition: all .3s ease; text-shadow: 0 2px 6px rgba(0,0,0,.4);
    }
    .service-card:hover .service-title { opacity: 1; transform: translateY(0); }

    .badge-popular {
      position: absolute; top: 12px; right: 12px;
      background: linear-gradient(135deg, var(--blue), var(--purple));
      color: #fff; padding: 4px 12px; border-radius: 20px;
      font-size: 11px; font-weight: 700; letter-spacing: .4px;
    }

    .card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }

    .product-desc {
      font-size: 13px; color: #555; line-height: 1.6;
      margin-bottom: 14px; flex: 1;
      display: -webkit-box; -webkit-line-clamp: 3;
      -webkit-box-orient: vertical; overflow: hidden;
    }

    .service-info {
      display: flex; gap: 10px;
      margin-bottom: 16px;
    }
    .info-item {
      flex: 1; display: flex; flex-direction: column; align-items: center;
      padding: 10px 8px; background: #f8f9fa; border-radius: 12px;
      transition: background .2s;
    }
    .info-item:hover { background: #e9ecef; }
    .info-icon { font-size: 16px; color: var(--blue); margin-bottom: 5px; }
    .info-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
    .info-value { font-size: 13px; font-weight: 700; color: var(--text); text-align: center; }

    .action-buttons { display: flex; gap: 10px; }
    .btn-service {
      flex: 1; padding: 12px 10px; border: none; border-radius: 12px;
      font-size: 13px; font-weight: 700; cursor: pointer;
      transition: all .25s ease; display: flex; align-items: center;
      justify-content: center; gap: 6px; letter-spacing: .3px;
    }
    .btn-service.btn-primary {
      background: linear-gradient(135deg, var(--pink), var(--pink-dk));
      color: #fff; box-shadow: 0 4px 14px rgba(245,4,153,.3);
    }
    .btn-service.btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(245,4,153,.45);
    }
    .btn-service.btn-secondary-custom {
      background: #fff; color: var(--pink);
      border: 2px solid var(--pink);
    }
    .btn-service.btn-secondary-custom:hover {
      background: var(--pink); color: #fff;
      transform: translateY(-2px);
    }
    .empty-products {
      grid-column: 1/-1; text-align: center; padding: 50px 20px; color: var(--muted);
    }

    /* ─── BOUTONS FLOTTANTS ─────────────────────────────────── */
    .fab-btn {
      padding: 0; border: none; border-radius: 50%;
      position: fixed; right: 30px; width: 62px; height: 62px;
      z-index: 9999; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: all .3s cubic-bezier(.68,-.55,.265,1.55);
      animation: fabPulse 2.5s infinite;
    }
    .fab-btn i { font-size: 24px; color: #fff; transition: transform .3s; }
    .fab-btn:hover { transform: scale(1.15) rotate(8deg); }
    .fab-btn:hover i { animation: shake .5s ease-in-out; }
    .fab-btn:active { transform: scale(.94); }

    #btn-ai   { background: linear-gradient(135deg, var(--blue), var(--purple)); bottom: 80px; }
    #btn-ai:hover { background: linear-gradient(135deg, #10b981, #059669); }
    #btn-ai::after  { content: 'Assistant IA'; }

    /* tooltip générique */
    .fab-btn::after {
      position: absolute; right: 74px; top: 50%; transform: translateY(-50%);
      background: #1f2937; color: #fff; padding: 7px 14px; border-radius: 8px;
      font-size: 13px; white-space: nowrap; opacity: 0; pointer-events: none;
      transition: opacity .25s; box-shadow: 0 4px 12px rgba(0,0,0,.25);
    }
    .fab-btn:hover::after { opacity: 1; }

    @keyframes fabPulse {
      0%,100% { box-shadow: 0 6px 22px rgba(102,126,234,.45); }
      50%      { box-shadow: 0 8px 32px rgba(102,126,234,.7); }
    }
    @keyframes shake {
      0%,100% { transform: rotate(0); }
      25%      { transform: rotate(-15deg); }
      75%      { transform: rotate(15deg); }
    }

    /* ─── CHAT IA ────────────────────────────────────────────── */
    #ai-panel {
      background: #fff; border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,.25);
      width: 370px; height: 88vh; max-height: 620px;
      position: fixed; right: 30px; bottom: 155px;
      z-index: 9998; display: none;
      flex-direction: column; overflow: hidden;
    }
    .chat-header {
      background: linear-gradient(135deg, var(--pink) 0%, var(--pink-dk) 100%);
      color: #fff; padding: 18px 20px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: space-between;
    }
    .chat-header h2 { font-size: 17px; margin: 0; }
    .chat-header p  { font-size: 12px; opacity: .85; margin: 2px 0 0; }
    .chat-close {
      background: rgba(255,255,255,.25); border: none; border-radius: 50%;
      width: 30px; height: 30px; color: #fff; font-size: 16px; cursor: pointer;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    #chatContainer {
      flex: 1; overflow-y: auto; padding: 16px; background: #f8f9fa;
      scroll-behavior: smooth;
    }
    .message { margin-bottom: 14px; display: flex; animation: msgIn .3s ease; }
    .message.user { justify-content: flex-end; }
    @keyframes msgIn {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .message-content {
      max-width: 75%; padding: 12px 16px; border-radius: 18px;
      word-wrap: break-word; font-size: 14px; line-height: 1.55;
    }
    .message.user .message-content {
      background: linear-gradient(135deg, var(--pink), var(--pink-dk));
      color: #fff; border-bottom-right-radius: 4px;
    }
    .message.ai .message-content {
      background: #fff; color: #333;
      border: 1px solid #e0e0e0; border-bottom-left-radius: 4px;
      box-shadow: 0 2px 6px rgba(0,0,0,.06);
    }
    .chat-input-row {
      padding: 14px 16px; background: #fff;
      border-top: 1px solid #e8e8e8; display: flex; gap: 8px; flex-shrink: 0;
    }
    #userInput {
      flex: 1; padding: 11px 16px; border: 2px solid #e0e0e0; border-radius: 24px;
      font-size: 14px; outline: none; transition: border-color .25s; font-family: inherit;
    }
    #userInput:focus { border-color: var(--pink); }
    #sendBtn {
      padding: 11px 20px; background: linear-gradient(135deg, var(--blue), var(--purple));
      color: #fff; border: none; border-radius: 24px; cursor: pointer;
      font-size: 14px; font-weight: 700; transition: transform .2s, box-shadow .2s;
      font-family: inherit;
    }
    #sendBtn:hover { transform: translateY(-2px); box-shadow: 0 5px 14px rgba(102,126,234,.4); }
    #sendBtn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    .typing-dots { display: flex; gap: 5px; padding: 4px 2px; }
    .typing-dots span {
      width: 8px; height: 8px; background: var(--blue); border-radius: 50%;
      animation: dot-bounce 1.4s infinite;
    }
    .typing-dots span:nth-child(2) { animation-delay: .2s; }
    .typing-dots span:nth-child(3) { animation-delay: .4s; }
    @keyframes dot-bounce {
      0%,60%,100% { transform: translateY(0); }
      30%          { transform: translateY(-8px); }
    }

    .suggestions { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 10px; }
    .sug-btn {
      padding: 6px 13px; background: #fff; border: 1.5px solid var(--pink);
      color: var(--pink); border-radius: 18px; cursor: pointer; font-size: 12px;
      transition: all .25s; font-family: inherit;
    }
    .sug-btn:hover { background: var(--pink); color: #fff; }

    /* ─── RESPONSIVE ────────────────────────────────────────── */
    @media (max-width: 576px) {
      #products-content { grid-template-columns: 1fr; }
      .action-buttons   { flex-direction: column; }
      #ai-panel         { width: calc(100vw - 20px); right: 10px; bottom: 140px; }
    }
    @media (max-width: 768px) {
      #products-content { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ═══════════════════════════════════════════════════ -->
<header id="site-header" class="fixed-top">
  <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark stroke">
      <h1 class="navbar-brand expand-lg mx-lg-auto">
        <a class="navbar-brand" href="index.php">
          <span id="nom_footer"><i class="fa fa-cut"></i> <?= $boutique_info ? htmlspecialchars($boutique_info['nom']) : 'Beauty Innova' ?></span>
          <span class="logo">Rendez-vous belle !</span>
        </a>
      </h1>
      <button class="navbar-toggler collapsed bg-gradient" type="button"
              data-toggle="collapse" data-target="#navbarTogglerDemo02"
              aria-controls="navbarTogglerDemo02" aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
        <span class="navbar-toggler-icon fa icon-close fa-times"></span>
      </button>
      <div class="collapse navbar-collapse test" id="navbarTogglerDemo02">
        <ul class="navbar-nav mx-lg-auto">
          <li class="nav-item active"><a class="nav-link" href="index.php">Accueil<span class="sr-only">(current)</span></a></li>
          <li class="nav-item"><a class="nav-link" href="services.html">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="projects.html">Galerie</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">À propos</a></li>
        </ul>
      </div>
      <div class="top-quote mr-lg-2 mt-lg-0 mt-3 d-lg-block d-none">
        <a href="contact.html" class="btn btn-style btn-secondary">Réservez</a>
      </div>
      <div class="mobile-position">
        <nav class="navigation">
          <div class="theme-switch-wrapper">
            <label class="theme-switch" for="checkbox">
              <input type="checkbox" id="checkbox">
              <div class="mode-container py-1">
                <i class="gg-sun"></i><i class="gg-moon"></i>
              </div>
            </label>
          </div>
        </nav>
      </div>
    </nav>
  </div>
</header>

<!-- ══ BOUTON IA ════════════════════════════════════════════════ -->
<button id="btn-ai" class="fab-btn" title="Assistant IA">
  <i class="fas fa-robot"></i>
</button>

<!-- ══ PANNEAU CHAT IA ══════════════════════════════════════════ -->
<div id="ai-panel">
  <div class="chat-header">
    <div>
      <h2>💄 IA Beauté & Mode</h2>
      <p>Assistante virtuelle – conseils beauté & style</p>
    </div>
    <button class="chat-close" id="chat-close-btn" title="Fermer">✕</button>
  </div>

  <div id="chatContainer">
    <div class="message ai">
      <div class="message-content">
        Bonjour&nbsp;! 👋 Je suis votre assistante virtuelle spécialisée en beauté et mode. Je peux vous aider avec&nbsp;:
        <br><br>
        ✨ Conseils maquillage &amp; soins de la peau<br>
        👗 Suggestions de style &amp; tendances<br>
        💇‍♀️ Conseils capillaires<br>
        🎨 Associations de couleurs<br><br>
        Comment puis-je vous aider aujourd'hui&nbsp;?
        <div class="suggestions">
          <button class="sug-btn" onclick="sendSuggestion('Routine pour peau sèche ?')">Peau sèche</button>
          <button class="sug-btn" onclick="sendSuggestion('Tendances mode 2025')">Mode 2025</button>
          <button class="sug-btn" onclick="sendSuggestion('Choisir son rouge à lèvres')">Rouge à lèvres</button>
        </div>
      </div>
    </div>
  </div>

  <div class="chat-input-row">
    <input type="text" id="userInput" placeholder="Posez votre question…" autocomplete="off">
    <button id="sendBtn">Envoyer</button>
  </div>
</div>

<!-- ══ SLIDER ════════════════════════════════════════════════════ -->
<style>
  .w3l-main-slider .item {
    position: relative; height: 100vh; overflow: hidden;
  }
  .w3l-main-slider .slider-info {
    width: 100%; height: 100%;
    background-size: cover; background-position: center;
    transition: transform .6s ease;
  }
  .w3l-main-slider .item:hover .slider-info { transform: scale(1.04); }
  .banner-info { position: relative; z-index: 2; color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,.6); }
  .w3l-main-slider .slider-info::after {
    content: ""; position: absolute; inset: 0;
    background: rgba(0,0,0,.42); z-index: 1;
  }
  .banner-info-bg h5 span { color: #f8c146; }
</style>

<section class="w3l-main-slider" id="home">
  <div class="companies20-content">
    <div class="owl-one owl-carousel owl-theme">
      <div class="item">
        <div class="slider-info banner-view bg" style="background-image:url('https://static.vecteezy.com/ti/photos-gratuite/p1/29561123-interieur-de-salon-de-beaute-gratuit-photo.jpg');">
          <div class="banner-info"><div class="container"><div class="banner-info-bg">
            <h5>Votre destination beauté <span>exceptionnelle</span></h5>
            <p class="mt-4">Des services professionnels de coiffure, soins et esthétique.<br>Yaoundé – Cameroun</p>
            <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="about.html">En savoir plus</a>
          </div></div></div>
        </div>
      </div>
      <div class="item">
        <div class="slider-info banner-view bg" style="background-image:url('https://i.pinimg.com/originals/1d/4f/d5/1d4fd563760f4009512a009e0e138f60.jpg');">
          <div class="banner-info"><div class="container"><div class="banner-info-bg">
            <h5>Sublimez votre <span>beauté naturelle</span></h5>
            <p class="mt-4">Des soins personnalisés dans un cadre élégant et raffiné.<br>Yaoundé – Cameroun</p>
            <a class="btn btn-style btn-primary mt-sm-5 mt-4 mr-2" href="about.html">En savoir plus</a>
          </div></div></div>
        </div>
      </div>
      <div class="item">
        <div class="slider-info banner-view bg" style="background-image:url('https://cdn.pixabay.com/photo/2020/07/17/23/17/salon-5415669_1280.jpg');">
          <div class="banner-info"><div class="container"><div class="banner-info-bg">
            <h5>Offrez-vous un moment de <span>détente</span> absolue</h5>
            <p class="mt-4">Coiffure, maquillage, soins du visage et du corps.<br>Yaoundé – Cameroun</p>
            <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="about.html">Découvrir</a>
          </div></div></div>
        </div>
      </div>
      <div class="item">
        <div class="slider-info banner-view bg" style="background-image:url('https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1200&q=80');">
          <div class="banner-info"><div class="container"><div class="banner-info-bg">
            <h5>Expertise et <span>élégance</span> à votre service</h5>
            <p class="mt-4">Une équipe passionnée pour révéler votre beauté.<br>Yaoundé – Cameroun</p>
            <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="contact.html">Réserver</a>
          </div></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ SERVICES ICONS ════════════════════════════════════════════ -->
<div class="w3l-grids-slider pt-5" id="about">
  <div class="container">
    <div class="w3l-customers row my-lg-5 my-sm-4">
      <div class="col-md-12">
        <div class="owl-three owl-carousel owl-theme logo-view">
          <?php
          $services = [
            ['fa-cut','Coiffure'], ['fa-star','Soins visage'], ['fa-hand-o-up','Manucure'],
            ['fa-paint-brush','Maquillage'], ['fa-heart','Pédicure'],
            ['fa-leaf','Soins capillaires'], ['fa-sun-o','Épilation']
          ];
          foreach ($services as $s): ?>
          <div class="item">
            <div class="grid">
              <span class="fa <?= $s[0] ?>"></span>
              <h4><a href="#projects"><?= $s[1] ?></a></h4>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ À PROPOS ══════════════════════════════════════════════════ -->
<section class="w3l-homeblock2" id="work">
  <div class="midd-w3 py-5">
    <div class="container pb-lg-5 pb-md-3">
      <div class="row">
        <div class="col-lg-5 left-wthree-img">
          <div class="position-relative">
            <img src="https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&q=80" alt="Salon Beauty Innova" class="img-fluid">
            <a href="#small-dialog" class="popup-with-zoom-anim play-view text-center position-absolute">
              <span class="video-play-icon"><span class="fa fa-play"></span></span>
            </a>
            <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
              <iframe src="https://www.youtube.com/embed/hxh8LdkoAcQ" allow="autoplay; fullscreen" allowfullscreen></iframe>
            </div>
          </div>
        </div>
        <div class="col-lg-7 mt-lg-0 mt-sm-5 mt-4 align-self">
          <span class="title-small">À propos</span>
          <h3 class="title-big">Bienvenue chez <span><?= $boutique_info ? htmlspecialchars($boutique_info['nom']) : 'Beauty Innova' ?></span></h3>
          <h5 class="mt-3"><?= $boutique_info ? 'Boutique spécialisée à ' . htmlspecialchars($boutique_info['adresse'] ?? 'Yaoundé') : 'Des services diversifiés dans le domaine de la beauté et du bien-être.' ?></h5>
          <p class="mt-4">
            <?= $boutique_info ? 
              nl2br(htmlspecialchars($boutique_info['description'] ?? 'Découvrez nos produits et services de qualité.')) :
              'Beauty Innova, ayant démarré ses activités en 2017, a pour objectif principal de fournir des services de beauté à valeur ajoutée qui répondent aux besoins de nos clientes.<br><br>Notre salon offre une gamme complète de soins personnalisés dans un cadre élégant et professionnel à Yaoundé.'
            ?>
          </p>
          <a href="services.html" class="btn btn-style btn-bleu mt-sm-5 mt-4">Voir nos services</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ RÉALISATIONS / PRODUITS ═══════════════════════════════════ -->
<section class="w3l-products py-5" id="projects">
  <div class="container py-lg-3">
    <div class="header-section text-center mx-auto">
      <h3 class="title-big">Nos <span>réalisations</span></h3>
      <p class="mt-3">Découvrez nos créations beauté et laissez-vous inspirer par le talent de notre équipe.</p>
    </div>
    <div class="mt-5">
      <div id="products-content">
        <?php if (empty($produits)): ?>
          <div class="empty-products">
            <i class="fas fa-box-open" style="font-size:48px;opacity:.4;display:block;margin-bottom:14px;"></i>
            <p style="font-size:17px;font-weight:600;">Aucun produit disponible pour le moment.</p>
            <p style="font-size:13px;opacity:.65;">Les produits ajoutés apparaîtront ici.</p>
          </div>
        <?php else: ?>
          <?php foreach ($produits as $i => $p): ?>
          <div class="service-card">
            <div class="image-container">
              <img src="<?= htmlspecialchars($p['image']) ?>"
                   class="service-image"
                   alt="<?= htmlspecialchars($p['nom']) ?>"
                   loading="lazy">
              <div class="image-overlay"></div>
              <h3 class="service-title"><?= htmlspecialchars($p['nom']) ?></h3>
              <?php if ($i < 3): ?>
                <span class="badge-popular">Populaire</span>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <p class="product-desc"><?= htmlspecialchars($p['description']) ?></p>

              <div class="service-info">
                <div class="info-item">
                  <i class="fas fa-tag info-icon"></i>
                  <span class="info-label">Prix</span>
                  <span class="info-value"><?= number_format((float)$p['prix'], 0, ',', ' ') ?> FCFA</span>
                </div>
                <div class="info-item">
                  <i class="fas fa-map-marker-alt info-icon"></i>
                  <span class="info-label">Localisation</span>
                  <span class="info-value"><?= htmlspecialchars($p['localisation']) ?></span>
                </div>
              </div>

              <?php
                $jsNom    = addslashes(htmlspecialchars($p['nom'],    ENT_QUOTES));
                $jsImg    = addslashes(htmlspecialchars($p['image'],  ENT_QUOTES));
                $jsPrix   = (int)$p['prix'];
                $jsWa     = addslashes(htmlspecialchars($p['whatsapp'] ?? '', ENT_QUOTES));
                $jsId     = (int)$p['produit_id'];
              ?>
              <div class="action-buttons">
                <button class="btn-service btn-primary"
                        onclick="acheter('<?= $jsWa ?>', '<?= $jsNom ?>', '<?= $jsPrix ?>', '<?= $jsImg ?>')">
                  <i class="fas fa-shopping-cart"></i> Acheter
                </button>
                <button class="btn-service btn-secondary-custom"
                        onclick="reserver('<?= $jsWa ?>', '<?= $jsNom ?>', '<?= $jsPrix ?>', '<?= $jsImg ?>')">
                  <i class="fas fa-calendar-check"></i> Réserver
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ══ POURQUOI NOUS CHOISIR ═════════════════════════════════════ -->
<section class="w3l-progressblock py-5" id="why">
  <div class="container py-lg-5 py-md-3">
    <div class="row">
      <div class="col-lg-6 about-right-faq">
        <h3 class="title-big">Pourquoi <span>nous choisir ?</span></h3>
        <p class="mt-lg-4 mt-3 mb-lg-5 mb-4">
          Chez Beauty Innova, notre approche de la beauté va au-delà des standards. Nous sommes animés par le désir de révéler votre beauté naturelle et d'avoir un impact positif sur votre confiance en vous.
        </p>
        <?php
        $progres = [
          ['Qualité des services', 95],
          ['Satisfaction client',  98],
          ['Expertise professionnelle', 90],
        ];
        foreach ($progres as [$label, $val]): ?>
        <div class="progress-info info1">
          <h6 class="progress-tittle"><?= $label ?> <span><?= $val ?>%</span></h6>
          <div class="progress">
            <div class="progress-bar progress-bar-striped" role="progressbar"
                 style="width:<?= $val ?>%" aria-valuenow="<?= $val ?>" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-lg-6 stats mt-lg-0 mt-4 pl-lg-5 align-self">
        <div class="row">
          <?php
          $stats = [
            ['fa-users','2500+','Clientes satisfaites'],
            ['fa-smile-o','98%','Taux de satisfaction'],
            ['fa-star-o','800+','Avis 5 étoiles'],
            ['fa-calendar','7','Années d\'expérience'],
          ];
          foreach ($stats as [$icon, $num, $label]): ?>
          <div class="col-md-6 col-sm-4 col-6 stat">
            <span class="fa <?= $icon ?>"></span>
            <div class="stats-info">
              <span class="number"><?= $num ?></span>
              <h4><?= $label ?></h4>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FAQ + RÉSERVATION ═════════════════════════════════════════ -->
<div class="w3l-faq-block py-5" id="faq">
  <div class="container py-lg-5">
    <div class="row">
      <div class="col-lg-7">
        <h3 class="title-big">Pour en savoir plus, consultez<br><span>notre FAQ</span></h3>
        <p class="mt-3">Réponses aux questions fréquentes sur nos services et tarifs.</p>
        <div class="faq-page mt-4">
          <ul>
            <?php
            $faqs = [
              ['Quels services proposez-vous ?','Nous offrons : coiffure (coupe, coloration, brushing), soins esthétiques, manucure, pédicure, maquillage professionnel, épilation et massages relaxants.'],
              ['Comment prendre rendez-vous ?','Réservez en ligne, par téléphone au +237 679 118 000, ou directement au salon. Nous recommandons de réserver à l\'avance.'],
              ['Proposez-vous des forfaits mariages ?','Oui ! Forfaits complets (coiffure, maquillage, essais, déplacement). Contactez-nous pour un devis personnalisé.'],
              ['Quels produits utilisez-vous ?','Exclusivement des produits professionnels de haute qualité, adaptés à tous types de cheveux et de peaux.'],
            ];
            foreach ($faqs as [$q, $r]): ?>
            <li>
              <input type="checkbox" checked>
              <i></i>
              <h2><?= htmlspecialchars($q) ?></h2>
              <p><?= htmlspecialchars($r) ?></p>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <div class="col-lg-5 mt-lg-0 mt-sm-5 mt-4">
        <div class="banner-form-w3">
          <form action="reserver.php" method="post">
            <h3 class="title-big">Réserver un <span>rendez-vous</span></h3>
            <p class="mt-3">Remplissez le formulaire pour prendre rendez-vous avec nos experts beauté.</p>
            <div class="form-style-w3ls mt-4">
              <input placeholder="Votre nom" name="name" type="text" required>
              <input placeholder="Votre email" name="email" type="email" required>
              <input placeholder="Numéro de téléphone" name="phone" type="text" required>
              <button type="submit" class="btn btn-style btn-red w-100">Réserver</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ BANDEAU CTA ═══════════════════════════════════════════════ -->
<div class="w3l-bg-image">
  <div class="bg-mask py-5">
    <div class="container py-lg-5 py-sm-4 py-2">
      <div class="text-align text-center py-lg-4 py-md-3">
        <h3 class="title-big">Des services de beauté exceptionnels pour révéler votre éclat naturel.</h3>
        <p class="mt-4">Nous sommes passionnés par la beauté et dédiés à votre satisfaction.</p>
        <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="#work">En savoir plus</a>
        <a class="btn btn-style btn-red mt-sm-5 mt-4" href="contact.html">Réservez maintenant</a>
      </div>
    </div>
  </div>
</div>

<!-- ══ FEATURES ══════════════════════════════════════════════════ -->
<section class="feature-style-one py-5">
  <div class="container py-lg-4 py-md-3">
    <div class="row px-2">
      <?php
      $features = [
        ['primary','fa-shield','Hygiène irréprochable','Protocoles sanitaires stricts et matériel stérilisé pour votre sécurité.'],
        ['','fa-star','Expertise reconnue','Une équipe de professionnels qualifiés et passionnés à votre service.'],
        ['primary','fa-clock-o','Horaires flexibles','Ouvert 7j/7 pour s\'adapter à votre emploi du temps.'],
      ];
      foreach ($features as $i => [$cls, $icon, $title, $desc]): ?>
      <div class="col-lg-4 col-md-6 px-2<?= $i > 0 ? ' mt-lg-0 mt-4' : '' ?>">
        <div class="single-feature-style-<?= $i === 2 ? 'two' : 'one' ?> <?= $cls ?>">
          <div class="icon-box"><span class="fa <?= $icon ?>"></span></div>
          <div class="text-box"><h3><?= $title ?></h3><p><?= $desc ?></p></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ FOOTER ════════════════════════════════════════════════════ -->
<section class="w3l-footer">
  <div class="w3l-footer-16-main py-5">
    <div class="container pt-lg-4">
      <div class="row">
        <div class="col-lg-7 column">
          <div class="row">
            <div class="col-sm-4 col-6 column">
              <h3>Nos Services</h3>
              <ul class="footer-gd-16">
                <li><a href="#projects">Coiffure</a></li>
                <li><a href="#projects">Soins visage</a></li>
                <li><a href="#projects">Manucure &amp; Pédicure</a></li>
                <li><a href="about.html">Maquillage professionnel</a></li>
                <li><a href="#projects">Notre équipe</a></li>
              </ul>
            </div>
            <div class="col-sm-4 col-6 column mt-sm-0 mt-4">
              <h3>Liens utiles</h3>
              <ul class="footer-gd-16">
                <li><a href="#projects">Épilation</a></li>
                <li><a href="#projects">Soins capillaires</a></li>
                <li><a href="#projects">Massage relaxant</a></li>
                <li><a href="#projects">Nail Art</a></li>
                <li><a href="contact.html">Forfaits mariages</a></li>
              </ul>
            </div>
            <div class="col-sm-4 col-6 column">
              <h3>Appelez-nous</h3>
              <ul class="footer-gd-16">
                <li>
                  <p>Réservations :<br>
                    <a href="tel:+237679118000" id="numero">+237 679 118 000</a><br>
                    <a href="tel:+237676614813">+237 676 614 813</a>
                  </p>
                </li>
                <li>
                  <p>Renseignements :<br>
                    <a href="tel:+237696526777">+237 696 526 777</a><br>
                    <a href="tel:+237699920776">+237 699 920 776</a>
                  </p>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-5 col-md-12 column pl-lg-5 column4 mt-lg-0 mt-5">
          <h3>Newsletter</h3>
          <div class="end-column">
            <h4>Recevez nos offres spéciales et nouveautés.</h4>
            <form action="newsletter.php" class="subscribe" method="post">
              <input type="email" name="email" placeholder="Votre adresse email" required>
              <button type="submit"><span class="fa fa-paper-plane"></span></button>
            </form>
            <p>Inscrivez-vous pour recevoir nos promotions et conseils beauté.</p>
          </div>
        </div>
      </div>
      <div class="d-flex below-section justify-content-between align-items-center pt-4 mt-5">
        <div class="columns text-lg-left text-center">
          <p>&copy; <?= date('Y') ?> <span id="nom_footer_copy"><?= $boutique_info ? htmlspecialchars($boutique_info['nom']) : 'Beauty Innova' ?></span>. Tous droits réservés.</p>
        </div>
        <div class="columns-2 mt-lg-0 mt-3">
          <ul class="social">
            <li><a href="#linkedin"><span class="fa fa-linkedin" aria-hidden="true"></span></a></li>
            <li><a href="https://www.facebook.com/"><span class="fa fa-facebook" aria-hidden="true"></span></a></li>
            <li><a href="#tiktok"><span class="fa-brands fa-tiktok" aria-hidden="true"></span></a></li>
            <li><a href="#twitter"><span class="fa-brands fa-x-twitter" aria-hidden="true"></span></a></li>
            <li><a href="#instagram"><span class="fa fa-instagram" aria-hidden="true"></span></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <button onclick="topFunction()" id="movetop" title="Retour en haut">
    <span class="fa fa-angle-up"></span>
  </button>
</section>

<!-- ══ SCRIPTS ═══════════════════════════════════════════════════ -->
<script src="NOUVEAU/assets/js/jquery-1.9.1.min.js"></script>
<script src="NOUVEAU/assets/js/theme-change.js"></script>
<script src="NOUVEAU/assets/js/easyResponsiveTabs.js"></script>
<script src="NOUVEAU/assets/js/owl.carousel.js"></script>
<script src="NOUVEAU/assets/js/jquery.magnific-popup.min.js"></script>
<script src="NOUVEAU/assets/js/bootstrap.min.js"></script>

<script>
/* ─── OWL CAROUSELS ─────────────────────────────────────────── */
$(document).ready(function () {
  $('.owl-one').owlCarousel({
    loop:true, margin:0, nav:false, autoplay:true,
    autoplayTimeout:5000, autoplaySpeed:1000,
    responsive:{ 0:{items:1}, 667:{items:1,nav:true}, 1000:{items:1,nav:true} }
  });
  $('.owl-three').owlCarousel({
    margin:20, nav:false, dots:false, autoplay:true,
    autoplayTimeout:5000, autoplaySpeed:1000,
    responsive:{ 0:{items:2}, 480:{items:2}, 767:{items:3}, 992:{items:4}, 1280:{items:5} }
  });
  $('.owl-testimonial').owlCarousel({
    loop:true, margin:0, nav:true, autoplay:false,
    responsive:{ 0:{items:1,nav:false}, 667:{items:1,nav:true}, 1000:{items:1,nav:true} }
  });

  /* Magnific Popup */
  $('.popup-with-zoom-anim').magnificPopup({
    type:'inline', fixedContentPos:false, fixedBgPos:true,
    overflowY:'auto', closeBtnInside:true, preloader:false,
    midClick:true, removalDelay:300, mainClass:'my-mfp-zoom-in'
  });

  /* Header scroll */
  $(window).on('scroll', function(){
    $('#site-header').toggleClass('nav-fixed', $(window).scrollTop() >= 80);
    $('#movetop').toggle($(window).scrollTop() > 20);
  });

  /* Burger */
  $('.navbar-toggler').on('click', function(){ $('header').toggleClass('active'); });

  /* localStorage branding */
  const nom = localStorage.getItem('nom_footer');
  if (nom) {
    $('#nom_footer').text(nom);
    $('#nom_footer_copy').text(nom);
  }
  const wa = localStorage.getItem('whatsapp');
  if (wa) $('#numero').text(wa);
});

function topFunction(){
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
}

/* ─── ACTIONS PRODUITS ──────────────────────────────────────── */
// ✅ FIX : fonctions corrigées avec paramètres séparés
function buildWhatsAppUrl(whatsapp, message) {
  if (!whatsapp) return null;
  const num = whatsapp.replace(/\D/g, '');
  return 'https://wa.me/' + num + '?text=' + encodeURIComponent(message);
}

function acheter(whatsapp, nom, prix, image) {
  const msg = 'Bonjour, je suis intéressé(e) par le produit : *' + nom + '*\nPrix : ' + prix + ' FCFA\nImage : ' + image;
  const url = buildWhatsAppUrl(whatsapp, msg);
  if (url) window.open(url, '_blank');
  else alert('Pour acheter ce produit, veuillez contacter le vendeur.');
}

function reserver(whatsapp, nom, prix, image) {
  const msg = 'Bonjour, je souhaite réserver le produit : *' + nom + '*\nPrix : ' + prix + ' FCFA\nImage : ' + image;
  const url = buildWhatsAppUrl(whatsapp, msg);
  if (url) window.open(url, '_blank');
  else alert('Pour réserver ce produit, veuillez contacter le vendeur.');
}

/* ─── ASSISTANT IA (Anthropic API) ─────────────────────────── */
const chatContainer = document.getElementById('chatContainer');
const userInput     = document.getElementById('userInput');
const sendBtn       = document.getElementById('sendBtn');

// Historique de conversation pour le contexte
const conversationHistory = [];

const SYSTEM_PROMPT = `Tu es "IA Beauté", une assistante virtuelle experte<?= $boutique_info ? ' de la boutique ' . addslashes(htmlspecialchars($boutique_info['nom'])) . ' à ' . addslashes(htmlspecialchars($boutique_info['adresse'] ?? 'Yaoundé')) : ' du salon de beauté Beauty Innova à Yaoundé' ?>, au Cameroun.
Tu es chaleureuse, professionnelle et passionnée par la beauté.
Tu réponds UNIQUEMENT en français, de façon concise (3-5 phrases max).
Tu conseilles sur : soins de la peau, maquillage, coiffure, mode, tendances beauté.
Si on te demande autre chose, redirige poliment vers la beauté.
Tu peux aussi encourager à prendre rendez-vous<?= $boutique_info ? ' chez ' . addslashes(htmlspecialchars($boutique_info['nom'])) . ' (' . addslashes(htmlspecialchars($boutique_info['whatsapp'] ?? '+237 679 118 000')) . ')' : ' chez Beauty Innova (+237 679 118 000)' ?>.`;

function addMessage(html, isUser) {
  const div = document.createElement('div');
  div.className = 'message ' + (isUser ? 'user' : 'ai');
  const content = document.createElement('div');
  if (isUser) content.textContent = html;
  else content.innerHTML = html;
  div.appendChild(content);
  chatContainer.appendChild(div);
  chatContainer.scrollTop = chatContainer.scrollHeight;
  return content;
}

function showTyping() {
  const div = document.createElement('div');
  div.className = 'message ai';
  div.id = 'typing-indicator';
  div.innerHTML = '<div class="message-content"><div class="typing-dots"><span></span><span></span><span></span></div></div>';
  chatContainer.appendChild(div);
  chatContainer.scrollTop = chatContainer.scrollHeight;
}
function removeTyping() {
  const t = document.getElementById('typing-indicator');
  if (t) t.remove();
}

async function sendMessage() {
  const text = userInput.value.trim();
  if (!text) return;

  addMessage(text, true);
  conversationHistory.push({ role: 'user', content: text });
  userInput.value = '';
  sendBtn.disabled = true;
  showTyping();

  try {
    const response = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'claude-sonnet-4-20250514',
        max_tokens: 400,
        system: SYSTEM_PROMPT,
        messages: conversationHistory
      })
    });

    removeTyping();

    if (!response.ok) throw new Error('API error ' + response.status);

    const data = await response.json();
    const reply = data.content?.[0]?.text || 'Désolée, je n\'ai pas pu répondre.';

    conversationHistory.push({ role: 'assistant', content: reply });
    // Convertit les retours à la ligne en <br>
    addMessage(reply.replace(/\n/g, '<br>'), false);

  } catch (err) {
    removeTyping();
    addMessage('⚠️ Une erreur est survenue. Veuillez réessayer.', false);
    console.error(err);
  } finally {
    sendBtn.disabled = false;
    userInput.focus();
  }
}

function sendSuggestion(text) {
  userInput.value = text;
  sendMessage();
}

sendBtn.addEventListener('click', sendMessage);
userInput.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });

/* ─── TOGGLE PANNEAU IA ─────────────────────────────────────── */
const btnAI   = document.getElementById('btn-ai');
const aiPanel = document.getElementById('ai-panel');
const closeBtn= document.getElementById('chat-close-btn');

btnAI.addEventListener('click', e => {
  e.stopPropagation();
  const open = aiPanel.style.display === 'flex';
  aiPanel.style.display = open ? 'none' : 'flex';
  if (!open) userInput.focus();
});

closeBtn.addEventListener('click', () => { aiPanel.style.display = 'none'; });

document.addEventListener('click', e => {
  if (!aiPanel.contains(e.target) && e.target !== btnAI) {
    aiPanel.style.display = 'none';
  }
});
aiPanel.addEventListener('click', e => e.stopPropagation());
</script>
</body>
</html>