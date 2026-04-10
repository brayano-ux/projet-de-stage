<?php
require_once __DIR__ . '/config/index.php';

$boutique_id = $_GET['boutique_id'] ?? null;

if (!$boutique_id) {
    http_response_code(400);
    die("Boutique non spécifiée. Veuillez fournir un ID de boutique dans l'URL (ex: ?boutique_id=1).");
}

try {
    $pdo = DatabaseConfig::getConnection();

    $stmt_boutique = $pdo->prepare("
        SELECT b.*,
               u.nom           AS proprietaire_nom,
               u.email         AS proprietaire_email,
               u.date_inscription AS proprietaire_date_inscription,
               (SELECT COUNT(*) FROM commandes   WHERE boutique_id = b.id)                         AS total_commandes,
               (SELECT COUNT(*) FROM favoris     WHERE boutique_id = b.id)                         AS total_favoris,
               (SELECT COUNT(*) FROM produits    WHERE boutique_id = b.id AND statut = 'disponible') AS produits_actifs
        FROM boutiques b
        LEFT JOIN utilisateurs u ON b.utilisateur_id = u.id
        WHERE b.id = ?
        LIMIT 1
    ");
    $stmt_boutique->execute([$boutique_id]);
    $boutique = $stmt_boutique->fetch(PDO::FETCH_ASSOC);

    if (!$boutique) {
        http_response_code(404);
        die("Boutique introuvable.");
    }

    $stmt_produits = $pdo->prepare("
        SELECT * FROM produits
        WHERE boutique_id = ? AND statut = 'disponible'
        ORDER BY date_ajout DESC
    ");
    $stmt_produits->execute([$boutique_id]);
    $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

    /* ──/* Récupérer les catégories dynamiquement */
    // Temporairement désactivé car la colonne 'categorie' n'existe pas dans la table produits
    $categories = [];
    /*
    $stmt_cats = $pdo->prepare("
        SELECT categorie, COUNT(*) AS nb
        FROM produits
        WHERE boutique_id = ? AND statut = 'disponible' AND categorie IS NOT NULL AND categorie <> ''
        GROUP BY categorie
        ORDER BY nb DESC
    ");
    $stmt_cats->execute([$boutique_id]);
    $categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
    */

} catch (PDOException $e) {
    error_log("DB error boutique.php: " . $e->getMessage());
    http_response_code(500);
    die("Erreur de connexion à la base de données.");
}

/* ── Helpers ── */
$whatsapp_clean = preg_replace('/[^0-9]/', '', $boutique['whatsapp'] ?? '');
$whatsapp_url   = "https://wa.me/{$whatsapp_clean}?text=" . urlencode("Bonjour, je viens de CreatorMarket, je suis intéressé(e) par vos produits.");
$membre_depuis  = isset($boutique['proprietaire_date_inscription'])
                    ? date('Y', strtotime($boutique['proprietaire_date_inscription']))
                    : date('Y');

/* ── Données JS ── */
$js_products        = json_encode($produits,                          JSON_UNESCAPED_UNICODE);
$js_whatsapp        = json_encode($whatsapp_clean);
$js_boutique_name   = json_encode(htmlspecialchars($boutique['nom'], ENT_QUOTES));
$js_boutique_desc   = json_encode(htmlspecialchars($boutique['description'] ?? '', ENT_QUOTES));
$js_boutique_adresse= json_encode(htmlspecialchars($boutique['adresse'] ?? '', ENT_QUOTES));
$js_proprietaire    = json_encode(htmlspecialchars($boutique['proprietaire_nom'] ?? '', ENT_QUOTES));
$js_categories      = json_encode($categories,                        JSON_UNESCAPED_UNICODE);
$js_boutique_id     = json_encode($boutique_id);

/* ── Icônes de catégorie ── */
$cat_icons = [
    'visage'      => 'fa-spa',       'corps'      => 'fa-bath',
    'maquillage'  => 'fa-palette',   'cosmetique' => 'fa-star',
    'cheveux'     => 'fa-cut',       'coiffure'   => 'fa-cut',
    'parfum'      => 'fa-wind',      'accessoire' => 'fa-gem',
    'soin'        => 'fa-leaf',      'bijoux'     => 'fa-ring',
    'mode'        => 'fa-tshirt',    'chaussures' => 'fa-shoe-prints',
    'electronique'=> 'fa-mobile-alt','alimentation'=> 'fa-utensils',
];
function getCatIcon(string $cat, array $map): string {
    $key = strtolower(trim($cat));
    foreach ($map as $k => $v) { if (str_contains($key, $k)) return $v; }
    return 'fa-tag';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($boutique['nom']) ?> — CreatorMarket</title>
<meta name="description" content="<?= htmlspecialchars(substr($boutique['description'] ?? '', 0, 155)) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ═══════════════════════════════════════════
   DESIGN SYSTEM
═══════════════════════════════════════════ */
:root {
  --ink:      #16100F;
  --ink-mid:  #4B3832;
  --ink-mute: #9B7B72;
  --cream:    #FBF7F2;
  --parchment:#F2EAE0;
  --rose:     #C8625C;
  --rose-lt:  #F5E3E2;
  --rose-dk:  #8C3632;
  --gold:     #C9964A;
  --gold-lt:  #F7EDDA;
  --gold-dk:  #7A5920;
  --white:    #FFFFFF;
  --border:   rgba(201,150,74,.22);
  --shadow:   0 12px 48px rgba(22,16,15,.10);
  --shadow-sm:0 4px 16px rgba(22,16,15,.08);
  --r-sm: 10px; --r-md: 18px; --r-lg: 28px; --r-full: 999px;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--cream);color:var(--ink);
  overflow-x:hidden;
}
img{max-width:100%;display:block}

/* ─ Scrollbar ─ */
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:var(--parchment)}
::-webkit-scrollbar-thumb{background:var(--rose);border-radius:99px}

/* ═══════════════════════════════════════════
   NAV
═══════════════════════════════════════════ */
nav{
  position:fixed;top:0;left:0;right:0;z-index:900;
  height:68px;
  background:rgba(251,247,242,.93);
  backdrop-filter:blur(14px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 5%;
  transition:box-shadow .3s;
}
nav.scrolled{box-shadow:var(--shadow-sm)}
.nav-logo{
  font-family:'Playfair Display',serif;
  font-size:1.45rem;font-weight:600;
  color:var(--ink);text-decoration:none;
  letter-spacing:-.01em;
}
.nav-logo span{color:var(--rose)}
.nav-links{display:flex;gap:2.2rem;list-style:none}
.nav-links a{
  text-decoration:none;color:var(--ink-mid);
  font-size:.82rem;font-weight:400;letter-spacing:.07em;text-transform:uppercase;
  transition:color .25s;position:relative;
}
.nav-links a::after{
  content:'';position:absolute;bottom:-4px;left:0;width:0;height:1px;
  background:var(--rose);transition:width .3s;
}
.nav-links a:hover{color:var(--rose)}
.nav-links a:hover::after{width:100%}
.nav-right{display:flex;align-items:center;gap:.75rem}
.cart-btn{
  display:flex;align-items:center;gap:8px;
  background:var(--ink);color:var(--cream);
  border:none;cursor:pointer;
  padding:9px 20px;border-radius:var(--r-full);
  font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:500;
  letter-spacing:.05em;text-transform:uppercase;
  transition:background .25s,transform .2s;
}
.cart-btn:hover{background:var(--rose);transform:translateY(-1px)}
.cart-count{
  background:var(--gold);color:var(--ink);
  border-radius:50%;width:20px;height:20px;
  display:flex;align-items:center;justify-content:center;
  font-size:.7rem;font-weight:700;transition:transform .3s;
}
.cart-count.bump{transform:scale(1.5)}
.hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;background:none;border:none;padding:4px}
.hamburger span{display:block;width:22px;height:1.5px;background:var(--ink);transition:.3s}
.hamburger.active span:nth-child(1){transform:rotate(45deg) translate(4px,4px)}
.hamburger.active span:nth-child(2){opacity:0}
.hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(4px,-4px)}

/* ─ Mobile nav ─ */
.nav-mobile{
  display:none;position:fixed;inset:68px 0 0;
  background:var(--cream);z-index:899;
  flex-direction:column;padding:2rem 6%;gap:1.5rem;
  list-style:none;border-top:1px solid var(--border);
  overflow-y:auto;
}
.nav-mobile.open{display:flex}
.nav-mobile a{font-size:1.1rem;color:var(--ink);text-decoration:none;font-weight:400;padding:.5rem 0;border-bottom:1px solid var(--border)}

/* ═══════════════════════════════════════════
   HERO
═══════════════════════════════════════════ */
.hero{
  min-height:100vh;padding-top:68px;
  display:grid;grid-template-columns:1fr 1fr;
  overflow:hidden;position:relative;
}
.hero-left{
  display:flex;flex-direction:column;justify-content:center;
  padding:6% 5% 6% 7%;position:relative;z-index:2;
}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--gold-lt);border:1px solid var(--border);
  border-radius:var(--r-full);padding:5px 14px;
  font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;
  color:var(--gold-dk);margin-bottom:1.8rem;width:fit-content;
}
.hero-eyebrow i{font-size:.6rem;color:var(--gold)}
.hero h1{
  font-family:'Playfair Display',serif;
  font-size:clamp(2.6rem,4.5vw,4.2rem);
  font-weight:700;line-height:1.1;
  color:var(--ink);margin-bottom:1.4rem;
  letter-spacing:-.02em;
}
.hero h1 em{font-style:italic;color:var(--rose)}
.hero-desc{
  font-size:.97rem;font-weight:300;color:var(--ink-mute);
  line-height:1.8;max-width:440px;margin-bottom:2.2rem;
}
.hero-actions{display:flex;gap:1rem;flex-wrap:wrap}
.btn-primary{
  background:var(--rose);color:#fff;
  padding:13px 30px;border-radius:var(--r-full);
  text-decoration:none;font-size:.83rem;font-weight:500;
  letter-spacing:.05em;text-transform:uppercase;
  transition:background .25s,transform .2s;
  display:inline-flex;align-items:center;gap:8px;
}
.btn-primary:hover{background:var(--rose-dk);transform:translateY(-2px)}
.btn-ghost{
  border:1px solid var(--border);color:var(--ink-mid);
  padding:13px 30px;border-radius:var(--r-full);
  text-decoration:none;font-size:.83rem;font-weight:500;
  letter-spacing:.05em;text-transform:uppercase;
  transition:all .25s;
  display:inline-flex;align-items:center;gap:8px;
}
.btn-ghost:hover{background:var(--ink);color:#fff;border-color:var(--ink)}

/* Hero right — visual panel */
.hero-right{
  position:relative;overflow:hidden;
  background:linear-gradient(140deg,#E5CBC8 0%,#D4A89A 50%,#C48070 100%);
}
.hero-pattern{
  position:absolute;inset:0;
  background-image:radial-gradient(circle at 70% 30%, rgba(255,255,255,.15) 0%, transparent 50%),
    repeating-linear-gradient(45deg, transparent, transparent 30px, rgba(255,255,255,.04) 30px, rgba(255,255,255,.04) 60px);
}
.hero-floater{
  position:absolute;inset:0;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.5rem;
  padding:2rem;
}
.hero-ring{
  width:min(300px,75%);aspect-ratio:1;
  border-radius:50%;
  border:1px solid rgba(255,255,255,.35);
  display:flex;align-items:center;justify-content:center;
  position:relative;
  animation:ringPulse 4s ease-in-out infinite;
}
.hero-ring::before{
  content:'';position:absolute;inset:18px;border-radius:50%;
  border:1px dashed rgba(255,255,255,.25);
}
@keyframes ringPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.04)}}
.hero-center-icon{
  font-size:4.5rem;
  animation:float 3.5s ease-in-out infinite;
  filter:drop-shadow(0 8px 24px rgba(0,0,0,.12));
}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}

/* Vendor card in hero */
.hero-vendor{
  position:absolute;bottom:1.8rem;left:1.5rem;
  background:rgba(255,255,255,.88);
  backdrop-filter:blur(10px);
  border-radius:var(--r-md);padding:1rem 1.25rem;
  display:flex;align-items:center;gap:.9rem;
  box-shadow:var(--shadow-sm);
  border:1px solid rgba(255,255,255,.6);
  max-width:260px;
}
.vendor-avatar{
  width:46px;height:46px;border-radius:50%;
  background:var(--rose);display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;font-weight:700;color:#fff;
  flex-shrink:0;overflow:hidden;border:2px solid #fff;
}
.vendor-avatar img{width:100%;height:100%;object-fit:cover}
.vendor-info .vendor-name{font-weight:600;font-size:.88rem;color:var(--ink);line-height:1.2}
.vendor-info .vendor-since{font-size:.72rem;color:var(--ink-mute);margin-top:2px}
.vendor-info .vendor-badge{
  display:inline-flex;align-items:center;gap:4px;
  font-size:.68rem;color:var(--gold-dk);
  background:var(--gold-lt);padding:2px 8px;border-radius:99px;margin-top:4px;
}

/* Stats bar */
.hero-stats{
  position:absolute;top:1.5rem;right:1.5rem;
  display:flex;flex-direction:column;gap:.6rem;
}
.stat-pill{
  background:rgba(255,255,255,.82);backdrop-filter:blur(8px);
  border-radius:var(--r-full);padding:6px 14px;
  display:flex;align-items:center;gap:7px;
  font-size:.78rem;font-weight:500;color:var(--ink);
  border:1px solid rgba(255,255,255,.5);
  box-shadow:var(--shadow-sm);
}
.stat-pill i{color:var(--rose);font-size:.75rem}

/* ─ Marquee ─ */
.marquee{
  background:var(--ink);color:var(--gold);
  padding:11px 0;overflow:hidden;
  font-size:.75rem;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;
}
.marquee-inner{display:inline-flex;animation:marquee 24s linear infinite}
.marquee-inner:hover{animation-play-state:paused}
.marquee-inner span{padding:0 1.8rem}
.marquee-dot{color:var(--rose)}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* ═══════════════════════════════════════════
   SECTION COMMONS
═══════════════════════════════════════════ */
.section-header{text-align:center;margin-bottom:3.5rem}
.section-tag{
  display:inline-block;font-size:.7rem;letter-spacing:.18em;
  text-transform:uppercase;color:var(--gold-dk);margin-bottom:.7rem;
}
.section-tag::before,.section-tag::after{content:' ✦ ';font-size:.52rem;opacity:.7}
.section-title{
  font-family:'Playfair Display',serif;
  font-size:clamp(1.9rem,3.5vw,2.8rem);font-weight:700;
  color:var(--ink);line-height:1.15;
}
.section-title em{font-style:italic;color:var(--rose)}
.section-sub{
  margin-top:.75rem;font-size:.93rem;color:var(--ink-mute);
  font-weight:300;max-width:500px;margin-left:auto;margin-right:auto;line-height:1.75;
}

/* ═══════════════════════════════════════════
   VENDOR PROFILE SECTION
═══════════════════════════════════════════ */
.vendor-profile{
  padding:5rem 7%;
  background:var(--white);
  display:grid;grid-template-columns:1fr 2fr;gap:4rem;align-items:start;
}
.vp-card{
  background:var(--cream);border:1px solid var(--border);
  border-radius:var(--r-lg);padding:2rem;text-align:center;
  position:sticky;top:80px;
}
.vp-photo{
  width:110px;height:110px;border-radius:50%;
  background:var(--rose-lt);border:3px solid var(--rose);
  display:flex;align-items:center;justify-content:center;
  font-size:2.2rem;font-weight:700;color:var(--rose);
  margin:0 auto 1rem;overflow:hidden;
}
.vp-photo img{width:100%;height:100%;object-fit:cover}
.vp-name{font-family:'Playfair Display',serif;font-size:1.25rem;font-weight:600;color:var(--ink)}
.vp-role{font-size:.78rem;color:var(--ink-mute);letter-spacing:.07em;text-transform:uppercase;margin-top:2px}
.vp-bio{font-size:.85rem;color:var(--ink-mid);line-height:1.7;margin:1rem 0;font-weight:300}
.vp-divider{border:none;border-top:1px solid var(--border);margin:1rem 0}
.vp-meta{display:flex;flex-direction:column;gap:.6rem;text-align:left}
.vp-meta-item{display:flex;align-items:center;gap:.7rem;font-size:.82rem;color:var(--ink-mid)}
.vp-meta-item i{color:var(--rose);width:16px;text-align:center;flex-shrink:0}
.vp-actions{margin-top:1.2rem;display:flex;flex-direction:column;gap:.6rem}
.vp-btn-wa{
  display:flex;align-items:center;justify-content:center;gap:8px;
  background:#25D366;color:#fff;
  padding:11px;border-radius:var(--r-full);
  text-decoration:none;font-size:.82rem;font-weight:500;
  transition:all .25s;
}
.vp-btn-wa:hover{background:#128C7E;transform:translateY(-1px)}

/* Boutique info grid */
.vp-info .vp-info-header{margin-bottom:1.5rem}
.vp-info .vp-info-header .section-title{text-align:left}
.vp-info .section-tag{text-align:left}
.boutique-highlights{
  display:grid;grid-template-columns:repeat(3,1fr);gap:1.2rem;margin-top:1.5rem;
}
.bh-card{
  background:var(--cream);border:1px solid var(--border);
  border-radius:var(--r-md);padding:1.25rem;text-align:center;
}
.bh-icon{font-size:1.6rem;color:var(--rose);margin-bottom:.6rem}
.bh-num{
  font-family:'Playfair Display',serif;
  font-size:1.8rem;font-weight:700;color:var(--ink);
}
.bh-label{font-size:.73rem;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.07em;margin-top:2px}
.boutique-details-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.5rem;
}
.bd-item{
  background:var(--gold-lt);border:1px solid var(--border);
  border-radius:var(--r-md);padding:1rem 1.25rem;
  display:flex;align-items:flex-start;gap:.9rem;
}
.bd-item i{color:var(--gold-dk);font-size:1rem;margin-top:2px;flex-shrink:0}
.bd-item strong{display:block;font-size:.8rem;letter-spacing:.05em;text-transform:uppercase;color:var(--gold-dk);margin-bottom:2px}
.bd-item span{font-size:.88rem;color:var(--ink-mid)}

/* Opening hours */
.hours-grid{
  margin-top:1.5rem;background:var(--cream);
  border:1px solid var(--border);border-radius:var(--r-md);overflow:hidden;
}
.hours-header{
  background:var(--ink);color:#fff;
  padding:.75rem 1.25rem;
  display:flex;align-items:center;gap:.6rem;
  font-size:.8rem;font-weight:500;letter-spacing:.06em;text-transform:uppercase;
}
.hours-row{
  display:flex;justify-content:space-between;align-items:center;
  padding:.6rem 1.25rem;border-bottom:1px solid var(--border);
  font-size:.83rem;
}
.hours-row:last-child{border:none}
.hours-row .day{color:var(--ink-mid)}
.hours-row .time{color:var(--ink);font-weight:500}
.hours-row .closed{color:var(--ink-mute);font-style:italic}
.hours-row.today{background:var(--rose-lt)}
.hours-row.today .day{color:var(--rose);font-weight:600}

/* ═══════════════════════════════════════════
   CATEGORIES
═══════════════════════════════════════════ */
.categories{padding:5rem 7%;background:var(--parchment)}
.cat-scroller{
  display:flex;gap:1rem;overflow-x:auto;
  padding-bottom:.5rem;scrollbar-width:none;
}
.cat-scroller::-webkit-scrollbar{display:none}
.cat-chip{
  flex-shrink:0;
  display:flex;align-items:center;gap:.6rem;
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--r-full);padding:.55rem 1.2rem;
  cursor:pointer;font-size:.82rem;font-weight:400;
  color:var(--ink-mid);text-decoration:none;
  transition:all .25s;white-space:nowrap;
}
.cat-chip i{font-size:.82rem;color:var(--ink-mute)}
.cat-chip:hover,.cat-chip.active{
  background:var(--rose);border-color:var(--rose);
  color:#fff;transform:translateY(-2px);
}
.cat-chip:hover i,.cat-chip.active i{color:#fff}
.cat-count-badge{
  background:var(--rose-lt);color:var(--rose);
  border-radius:99px;padding:1px 7px;
  font-size:.7rem;font-weight:600;margin-left:2px;
  transition:all .25s;
}
.cat-chip.active .cat-count-badge{background:rgba(255,255,255,.2);color:#fff}
.cat-grid-display{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
  gap:1.2rem;margin-top:2rem;
}
.cat-grid-card{
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--r-md);padding:1.5rem 1rem;
  text-align:center;cursor:pointer;
  text-decoration:none;color:inherit;
  transition:all .3s;
}
.cat-grid-card:hover,.cat-grid-card.active{
  background:var(--rose-lt);border-color:var(--rose);
  transform:translateY(-4px);box-shadow:var(--shadow-sm);
}
.cat-grid-card i{font-size:1.9rem;color:var(--rose);margin-bottom:.75rem;display:block}
.cat-grid-card .cat-grid-name{font-family:'Playfair Display',serif;font-size:1rem;font-weight:600;color:var(--ink)}
.cat-grid-card .cat-grid-nb{font-size:.73rem;color:var(--ink-mute);margin-top:3px}

/* ═══════════════════════════════════════════
   PRODUCTS
═══════════════════════════════════════════ */
.products{padding:5rem 7%;background:var(--cream)}
.products-toolbar{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:2rem;flex-wrap:wrap;gap:1rem;
}
.products-count{font-size:.82rem;color:var(--ink-mute)}
.products-sort{
  display:flex;align-items:center;gap:.5rem;
  font-size:.82rem;color:var(--ink-mid);
}
.products-sort select{
  border:1px solid var(--border);background:var(--white);
  border-radius:var(--r-full);padding:6px 14px;
  font-size:.8rem;color:var(--ink);cursor:pointer;
  font-family:'DM Sans',sans-serif;
}
.products-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(250px,1fr));
  gap:1.5rem;
}
.product-card{
  background:var(--white);border-radius:var(--r-md);
  border:1px solid var(--border);overflow:hidden;
  transition:all .3s;position:relative;
}
.product-card:hover{transform:translateY(-6px);box-shadow:var(--shadow)}
.product-card.hidden{display:none!important}
.product-img{
  width:100%;aspect-ratio:4/3;
  object-fit:cover;background:#eee;
  display:flex;align-items:center;justify-content:center;
  font-size:3.5rem;color:var(--ink-mute);
}
.product-img img{width:100%;height:100%;object-fit:cover}
.product-badge{
  position:absolute;top:10px;left:10px;
  font-size:.65rem;padding:3px 9px;border-radius:99px;
  font-weight:600;letter-spacing:.06em;text-transform:uppercase;
}
.product-badge.new{background:var(--gold);color:var(--ink)}
.product-badge.promo{background:var(--rose);color:#fff}
.product-badge.hot{background:var(--rose-dk);color:#fff}
.product-body{padding:1.1rem}
.product-cat{font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-mute);margin-bottom:4px}
.product-name{
  font-family:'Playfair Display',serif;
  font-size:1.08rem;font-weight:600;color:var(--ink);
  margin-bottom:4px;line-height:1.3;
}
.product-desc{font-size:.8rem;color:var(--ink-mute);line-height:1.6;margin-bottom:.9rem;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.product-footer{display:flex;align-items:center;justify-content:space-between}
.product-price{
  font-family:'Playfair Display',serif;
  font-size:1.2rem;font-weight:700;color:var(--rose);
}
.product-old-price{
  font-size:.75rem;color:var(--ink-mute);
  text-decoration:line-through;margin-left:5px;
  font-family:'DM Sans',sans-serif;font-weight:300;
}
.add-btn{
  background:var(--ink);color:#fff;border:none;cursor:pointer;
  width:38px;height:38px;border-radius:50%;
  font-size:1rem;display:flex;align-items:center;justify-content:center;
  transition:all .25s;flex-shrink:0;
}
.add-btn:hover{background:var(--rose);transform:scale(1.1)}
.add-btn.added{background:#25D366}
.no-products{
  grid-column:1/-1;text-align:center;
  padding:4rem;color:var(--ink-mute);
  font-family:'Playfair Display',serif;
  font-size:1.3rem;font-style:italic;
}

/* ═══════════════════════════════════════════
   ABOUT / TESTIMONIALS
═══════════════════════════════════════════ */
.about-section{
  padding:5rem 7%;
  background:var(--ink);
  display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start;
  position:relative;overflow:hidden;
}
.about-section::before{
  content:'';position:absolute;
  width:500px;height:500px;border-radius:50%;
  background:rgba(200,98,92,.06);
  top:-200px;right:10%;
}
.about-left{position:relative;z-index:1}
.about-left .section-tag{color:var(--gold);text-align:left}
.about-left .section-title{color:#fff;text-align:left}
.about-left .section-sub{color:rgba(255,255,255,.5);text-align:left;margin:0}
.feat-list{margin-top:2rem;display:flex;flex-direction:column;gap:.9rem}
.feat-item{
  display:flex;gap:1rem;
  background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);
  border-radius:var(--r-md);padding:1rem 1.2rem;
  transition:background .25s;
}
.feat-item:hover{background:rgba(255,255,255,.07)}
.feat-icon{
  width:38px;height:38px;border-radius:var(--r-sm);
  background:rgba(200,98,92,.15);display:flex;align-items:center;justify-content:center;
  color:var(--rose);font-size:.95rem;flex-shrink:0;
}
.feat-text strong{display:block;color:#fff;font-size:.87rem;font-weight:500;margin-bottom:2px}
.feat-text span{font-size:.78rem;color:rgba(255,255,255,.4)}
.about-right{position:relative;z-index:1}
.testi-grid{display:flex;flex-direction:column;gap:1rem}
.testi{
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
  border-radius:var(--r-md);padding:1.4rem;
  transition:background .25s;
}
.testi:hover{background:rgba(255,255,255,.08)}
.testi-stars{color:var(--gold);font-size:.82rem;margin-bottom:.5rem;letter-spacing:2px}
.testi-text{
  font-size:.87rem;color:rgba(255,255,255,.65);
  line-height:1.65;font-style:italic;margin-bottom:.9rem;
}
.testi-author{display:flex;align-items:center;gap:.7rem}
.testi-av{
  width:34px;height:34px;border-radius:50%;
  background:var(--rose);display:flex;align-items:center;justify-content:center;
  font-size:.8rem;font-weight:700;color:#fff;
}
.testi-name{font-size:.82rem;color:#fff;font-weight:500}
.testi-role{font-size:.7rem;color:rgba(255,255,255,.4)}

/* ═══════════════════════════════════════════
   CTA SECTION
═══════════════════════════════════════════ */
.cta-section{
  padding:5rem 7%;text-align:center;
  background:var(--rose-lt);
  border-top:1px solid rgba(200,98,92,.18);
  border-bottom:1px solid rgba(200,98,92,.18);
}
.wa-btn{
  display:inline-flex;align-items:center;gap:10px;
  background:#25D366;color:#fff;
  padding:15px 36px;border-radius:var(--r-full);
  text-decoration:none;font-size:.88rem;font-weight:500;
  letter-spacing:.04em;transition:all .3s;
  box-shadow:0 8px 28px rgba(37,211,102,.3);
  margin-top:1.5rem;
}
.wa-btn:hover{background:#128C7E;transform:translateY(-2px);box-shadow:0 12px 36px rgba(37,211,102,.35)}
.wa-btn i{font-size:1.1rem}

/* ═══════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════ */
footer{
  background:#0D0907;color:rgba(255,255,255,.45);
  padding:4rem 7% 2rem;
  display:grid;grid-template-columns:2fr 1fr 1fr;gap:3rem;
}
.footer-logo{
  font-family:'Playfair Display',serif;
  font-size:1.3rem;font-weight:600;color:#fff;
  text-decoration:none;display:block;margin-bottom:.9rem;
}
.footer-logo span{color:var(--rose)}
footer p{font-size:.82rem;line-height:1.7;max-width:280px}
footer h5{
  font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;
  color:#fff;margin-bottom:.9rem;
}
footer ul{list-style:none;display:flex;flex-direction:column;gap:.45rem}
footer ul li a{color:rgba(255,255,255,.4);text-decoration:none;font-size:.82rem;transition:color .25s}
footer ul li a:hover{color:var(--gold)}
.footer-bottom{
  background:#0D0907;text-align:center;
  padding:1.4rem 7%;border-top:1px solid rgba(255,255,255,.06);
  font-size:.72rem;color:rgba(255,255,255,.22);
}
.footer-bottom a{color:rgba(255,255,255,.3);text-decoration:none}
.footer-bottom a:hover{color:var(--gold)}

/* ═══════════════════════════════════════════
   CART DRAWER
═══════════════════════════════════════════ */
.overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.45);
  z-index:1000;opacity:0;pointer-events:none;transition:opacity .3s;
}
.overlay.open{opacity:1;pointer-events:all}
.cart-drawer{
  position:fixed;top:0;right:0;bottom:0;
  width:min(420px,100vw);
  background:var(--cream);z-index:1001;
  transform:translateX(100%);transition:transform .4s cubic-bezier(.25,.46,.45,.94);
  display:flex;flex-direction:column;
  box-shadow:-10px 0 50px rgba(0,0,0,.12);
}
.cart-drawer.open{transform:translateX(0)}
.drawer-header{
  padding:1.4rem 1.4rem 1rem;
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
}
.drawer-header h2{
  font-family:'Playfair Display',serif;
  font-size:1.4rem;font-weight:600;color:var(--ink);
}
.close-btn{
  background:none;border:1px solid var(--border);
  cursor:pointer;color:var(--ink-mid);
  width:34px;height:34px;border-radius:50%;
  font-size:1rem;display:flex;align-items:center;justify-content:center;
  transition:all .2s;
}
.close-btn:hover{background:var(--rose);color:#fff;border-color:var(--rose)}
.cart-items-list{
  flex:1;overflow-y:auto;
  padding:1rem 1.4rem;display:flex;flex-direction:column;gap:.9rem;
}
.cart-empty-state{
  flex:1;display:flex;flex-direction:column;align-items:center;
  justify-content:center;gap:.9rem;padding:3rem;
  color:var(--ink-mute);text-align:center;
}
.cart-empty-state i{font-size:3rem;opacity:.35}
.cart-empty-state p{font-family:'Playfair Display',serif;font-size:1.05rem;font-style:italic}
.cart-item{
  background:var(--white);border:1px solid var(--border);
  border-radius:var(--r-md);padding:.9rem;
  display:flex;align-items:center;gap:.9rem;
  animation:slideInRight .3s ease;
}
@keyframes slideInRight{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
.ci-thumb{
  width:52px;height:52px;border-radius:var(--r-sm);
  background:var(--rose-lt);display:flex;align-items:center;justify-content:center;
  font-size:1.6rem;flex-shrink:0;overflow:hidden;
}
.ci-thumb img{width:100%;height:100%;object-fit:cover}
.ci-info{flex:1;min-width:0}
.ci-name{font-weight:500;font-size:.87rem;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ci-price{font-family:'Playfair Display',serif;font-size:.95rem;color:var(--rose);font-weight:600}
.ci-controls{display:flex;align-items:center;gap:6px;margin-top:5px}
.qty-btn{
  width:24px;height:24px;border-radius:50%;
  border:1px solid var(--border);background:none;
  cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;
  color:var(--ink-mid);transition:all .2s;
}
.qty-btn:hover{background:var(--rose);color:#fff;border-color:var(--rose)}
.qty-val{font-size:.82rem;font-weight:500;color:var(--ink);min-width:18px;text-align:center}
.ci-remove{
  background:none;border:none;cursor:pointer;
  color:var(--ink-mute);font-size:.95rem;padding:4px;
  transition:color .2s;flex-shrink:0;
}
.ci-remove:hover{color:var(--rose)}
.drawer-footer{
  padding:1rem 1.4rem 1.4rem;
  border-top:1px solid var(--border);
}
.cart-total-row{
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:1.1rem;
}
.cart-total-label{font-size:.8rem;color:var(--ink-mute);text-transform:uppercase;letter-spacing:.08em}
.cart-total-amount{
  font-family:'Playfair Display',serif;
  font-size:1.5rem;font-weight:700;color:var(--ink);
}
.checkout-btn{
  width:100%;padding:14px;
  background:#25D366;color:#fff;border:none;
  border-radius:var(--r-md);cursor:pointer;
  font-family:'DM Sans',sans-serif;font-size:.87rem;font-weight:500;
  display:flex;align-items:center;justify-content:center;gap:9px;
  transition:all .25s;
}
.checkout-btn:hover:not(:disabled){background:#128C7E;transform:translateY(-1px)}
.checkout-btn:disabled{background:var(--ink-mute);cursor:not-allowed}
.checkout-btn i{font-size:1rem}
.cart-note{font-size:.7rem;color:var(--ink-mute);text-align:center;margin-top:.65rem;line-height:1.5}

/* ═══════════════════════════════════════════
   CHATBOT
═══════════════════════════════════════════ */
.chatbot-fab{
  position:fixed;bottom:1.8rem;right:1.8rem;
  z-index:1100;
  width:58px;height:58px;border-radius:50%;
  background:var(--rose);border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:1.3rem;
  box-shadow:0 6px 24px rgba(200,98,92,.5);
  transition:all .3s;
}
.chatbot-fab:hover{background:var(--rose-dk);transform:scale(1.08)}
.chatbot-fab .fab-badge{
  position:absolute;top:-4px;right:-4px;
  width:18px;height:18px;background:var(--gold);
  border-radius:50%;font-size:.65rem;font-weight:700;
  color:var(--ink);display:flex;align-items:center;justify-content:center;
  border:2px solid var(--cream);
}
.chatbot-window{
  position:fixed;bottom:5.5rem;right:1.8rem;
  z-index:1099;
  width:360px;max-height:520px;
  background:var(--white);border-radius:var(--r-lg);
  box-shadow:0 20px 60px rgba(0,0,0,.15);
  border:1px solid var(--border);
  display:flex;flex-direction:column;
  transform:scale(.9) translateY(20px);
  opacity:0;pointer-events:none;
  transition:all .3s cubic-bezier(.34,1.56,.64,1);
  overflow:hidden;
}
.chatbot-window.open{
  transform:scale(1) translateY(0);
  opacity:1;pointer-events:all;
}
.chat-header{
  background:var(--rose);color:#fff;
  padding:1rem 1.25rem;
  display:flex;align-items:center;justify-content:space-between;
}
.chat-header-left{display:flex;align-items:center;gap:.7rem}
.chat-avatar{
  width:36px;height:36px;border-radius:50%;
  background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;
  font-size:1rem;
}
.chat-title{font-weight:600;font-size:.9rem;line-height:1.2}
.chat-subtitle{font-size:.7rem;opacity:.75;display:flex;align-items:center;gap:4px}
.chat-dot{width:7px;height:7px;background:#4dff91;border-radius:50%;animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.chat-close-btn{
  background:none;border:none;cursor:pointer;
  color:rgba(255,255,255,.75);font-size:1rem;
  transition:color .2s;padding:2px;
}
.chat-close-btn:hover{color:#fff}
.chat-messages{
  flex:1;overflow-y:auto;
  padding:1rem;display:flex;flex-direction:column;gap:.7rem;
  scroll-behavior:smooth;
}
.chat-messages::-webkit-scrollbar{width:4px}
.chat-messages::-webkit-scrollbar-thumb{background:var(--border)}
.chat-msg{
  display:flex;gap:.5rem;align-items:flex-end;
  animation:fadeUp .25s ease;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.chat-msg.user{flex-direction:row-reverse}
.msg-bubble{
  max-width:78%;padding:.65rem .9rem;
  border-radius:14px;font-size:.83rem;line-height:1.55;
  word-break:break-word;
}
.chat-msg.bot .msg-bubble{
  background:var(--parchment);color:var(--ink);
  border-bottom-left-radius:4px;
}
.chat-msg.user .msg-bubble{
  background:var(--rose);color:#fff;
  border-bottom-right-radius:4px;
}
.msg-av{
  width:26px;height:26px;border-radius:50%;
  background:var(--rose);display:flex;align-items:center;justify-content:center;
  font-size:.7rem;color:#fff;flex-shrink:0;margin-bottom:2px;
}
.msg-av.user-av{background:var(--ink)}
.typing-indicator{
  display:flex;gap:3px;padding:.65rem .9rem;
  background:var(--parchment);border-radius:14px;
  width:fit-content;border-bottom-left-radius:4px;
}
.typing-indicator span{
  width:7px;height:7px;border-radius:50%;
  background:var(--ink-mute);animation:typeDot 1.2s infinite;
}
.typing-indicator span:nth-child(2){animation-delay:.2s}
.typing-indicator span:nth-child(3){animation-delay:.4s}
@keyframes typeDot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-6px)}}
.chat-suggestions{
  display:flex;flex-wrap:wrap;gap:.4rem;
  padding:0 1rem .6rem;
}
.chat-sugg{
  font-size:.75rem;padding:5px 11px;
  border:1px solid var(--rose);border-radius:99px;
  color:var(--rose);cursor:pointer;background:none;
  font-family:'DM Sans',sans-serif;
  transition:all .2s;white-space:nowrap;
}
.chat-sugg:hover{background:var(--rose);color:#fff}
.chat-input-row{
  padding:.75rem 1rem;border-top:1px solid var(--border);
  display:flex;gap:.5rem;align-items:flex-end;
}
.chat-input{
  flex:1;border:1px solid var(--border);border-radius:var(--r-full);
  padding:.5rem 1rem;font-size:.83rem;color:var(--ink);
  font-family:'DM Sans',sans-serif;outline:none;resize:none;
  background:var(--cream);max-height:80px;overflow-y:auto;
  transition:border-color .2s;
}
.chat-input:focus{border-color:var(--rose)}
.chat-send-btn{
  width:36px;height:36px;border-radius:50%;
  background:var(--rose);border:none;cursor:pointer;
  color:#fff;font-size:.9rem;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;transition:all .2s;
}
.chat-send-btn:hover{background:var(--rose-dk);transform:scale(1.08)}
.chat-send-btn:disabled{background:var(--border);cursor:not-allowed;transform:none}

/* ═══════════════════════════════════════════
   TOAST
═══════════════════════════════════════════ */
.toast{
  position:fixed;bottom:2.2rem;left:50%;transform:translateX(-50%) translateY(100px);
  background:var(--ink);color:#fff;
  padding:11px 22px;border-radius:var(--r-full);
  font-size:.83rem;z-index:2000;
  transition:transform .3s;white-space:nowrap;
  display:flex;align-items:center;gap:8px;
  box-shadow:0 8px 28px rgba(0,0,0,.18);
}
.toast.show{transform:translateX(-50%) translateY(0)}
.toast i{color:var(--gold)}

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media(max-width:960px){
  .hero{grid-template-columns:1fr}
  .hero-right{min-height:300px}
  .hero-left{padding:3rem 5% 2rem}
  .vendor-profile{grid-template-columns:1fr;gap:2rem}
  .vp-card{position:static}
  .about-section{grid-template-columns:1fr}
  footer{grid-template-columns:1fr;gap:2rem}
  .nav-links{display:none}
  .hamburger{display:flex}
  .boutique-highlights{grid-template-columns:repeat(3,1fr)}
  .boutique-details-grid{grid-template-columns:1fr}
}
@media(max-width:540px){
  .boutique-highlights{grid-template-columns:1fr 1fr}
  .chatbot-window{width:calc(100vw - 2rem);right:1rem;bottom:5rem}
  .hero-stats{flex-direction:column;gap:.4rem}
  .hero-vendor{left:.75rem;bottom:.75rem}
  footer{padding:3rem 5% 2rem}
}
</style>
</head>
<body>

<!-- ═══════ NAV ═══════ -->
<nav id="mainNav">
  <a class="nav-logo" href="#"><span><?= htmlspecialchars($boutique['nom']) ?></span></a>
  <ul class="nav-links">
    <li><a href="#vendeur">Vendeur</a></li>
    <li><a href="#categories">Catégories</a></li>
    <li><a href="#produits">Produits</a></li>
    <li><a href="#apropos">À propos</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="nav-right">
    <button class="cart-btn" onclick="toggleCart()">
      <i class="fas fa-shopping-bag"></i>
      Panier
      <span class="cart-count" id="cartCount">0</span>
    </button>
    <button class="hamburger" id="hamburger" onclick="toggleMobile()">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<ul class="nav-mobile" id="navMobile">
  <li><a href="#vendeur"    onclick="closeMobile()">Vendeur</a></li>
  <li><a href="#categories" onclick="closeMobile()">Catégories</a></li>
  <li><a href="#produits"   onclick="closeMobile()">Produits</a></li>
  <li><a href="#apropos"    onclick="closeMobile()">À propos</a></li>
  <li><a href="#contact"    onclick="closeMobile()">Contact</a></li>
</ul>

<!-- ═══════ HERO ═══════ -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-eyebrow">
      <i class="fas fa-award"></i>
      Boutique vérifiée CreatorMarket
    </div>
    <h1>
      <?= htmlspecialchars($boutique['nom']) ?>,<br>
      une <em>expérience</em><br>unique
    </h1>
    <p class="hero-desc"><?= htmlspecialchars($boutique['description'] ?? 'Découvrez notre sélection exclusive de produits de qualité, livrés rapidement à votre porte.') ?></p>
    <div class="hero-actions">
      <a href="#produits" class="btn-primary"><i class="fas fa-shopping-bag"></i> Voir les produits</a>
      <a href="#vendeur"  class="btn-ghost"  ><i class="fas fa-user-circle"></i> Contacter le vendeur</a>
    </div>
  </div>
  <div class="hero-right">
    <div class="hero-pattern"></div>
    <div class="hero-floater">
      <div class="hero-ring">
        <div class="hero-center-icon"><i class="fas fa-spa"></i></div>
      </div>
    </div>
    <!-- Vendor mini card -->
    <div class="hero-vendor">
      <div class="vendor-avatar">
        <?php if (!empty($boutique['proprietaire_photo'])): ?>
          <img src="<?= htmlspecialchars($boutique['proprietaire_photo']) ?>" alt="Vendeur">
        <?php else: ?>
          <?= mb_strtoupper(mb_substr($boutique['proprietaire_nom'] ?? 'V', 0, 2)) ?>
        <?php endif; ?>
      </div>
      <div class="vendor-info">
        <div class="vendor-name"><?= htmlspecialchars($boutique['proprietaire_nom'] ?? 'Vendeur') ?></div>
        <div class="vendor-since">Membre depuis <?= $membre_depuis ?></div>
        <div class="vendor-badge"><i class="fas fa-check-circle"></i> Vendeur vérifié</div>
      </div>
    </div>
    <!-- Stats pills -->
    <div class="hero-stats">
      <div class="stat-pill"><i class="fas fa-box"></i><?= $boutique['produits_actifs'] ?> produits</div>
      <div class="stat-pill"><i class="fas fa-shopping-cart"></i><?= $boutique['total_commandes'] ?> commandes</div>
      <div class="stat-pill"><i class="fas fa-heart"></i><?= $boutique['total_favoris'] ?> favoris</div>
    </div>
  </div>
</section>

<!-- ─ Marquee ─ -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-inner">
    <?php
    $marquee_items = ['Produits authentiques','Livraison rapide','Commande WhatsApp','Service 7j/7','Paiement sécurisé','<?= htmlspecialchars($boutique["adresse"] ?? "Douala, Cameroun") ?>'];
    $full = array_merge($marquee_items, $marquee_items);
    foreach ($full as $item) echo "<span>{$item}</span><span class='marquee-dot'>✦</span>";
    ?>
  </div>
</div>

<!-- ═══════ VENDOR PROFILE ═══════ -->
<section class="vendor-profile" id="vendeur">
  <!-- Left: vendor card -->
  <div class="vp-card">
    <div class="vp-photo">
      <?php if (!empty($boutique['proprietaire_photo'])): ?>
        <img src="<?= htmlspecialchars($boutique['proprietaire_photo']) ?>" alt="Photo vendeur">
      <?php else: ?>
        <?= mb_strtoupper(mb_substr($boutique['proprietaire_nom'] ?? 'V', 0, 2)) ?>
      <?php endif; ?>
    </div>
    <div class="vp-name"><?= htmlspecialchars($boutique['proprietaire_nom'] ?? 'Vendeur') ?></div>
    <div class="vp-role">Propriétaire de la boutique</div>
    <?php if (!empty($boutique['proprietaire_bio'])): ?>
      <p class="vp-bio"><?= htmlspecialchars($boutique['proprietaire_bio']) ?></p>
    <?php else: ?>
      <p class="vp-bio">Vendeur professionnel engagé à vous offrir les meilleurs produits avec un service client irréprochable.</p>
    <?php endif; ?>
    <hr class="vp-divider">
    <div class="vp-meta">
      <?php if (!empty($boutique['whatsapp'])): ?>
      <div class="vp-meta-item"><i class="fab fa-whatsapp"></i><?= htmlspecialchars($boutique['whatsapp']) ?></div>
      <?php endif; ?>
      <?php if (!empty($boutique['proprietaire_email'])): ?>
      <div class="vp-meta-item"><i class="fas fa-envelope"></i><?= htmlspecialchars($boutique['proprietaire_email']) ?></div>
      <?php endif; ?>
      <?php if (!empty($boutique['adresse'])): ?>
      <div class="vp-meta-item"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($boutique['adresse']) ?></div>
      <?php endif; ?>
      <div class="vp-meta-item"><i class="fas fa-calendar-check"></i>Membre depuis <?= $membre_depuis ?></div>
    </div>
    <div class="vp-actions">
      <a class="vp-btn-wa" href="<?= $whatsapp_url ?>" target="_blank" rel="noopener">
        <i class="fab fa-whatsapp"></i> Contacter sur WhatsApp
      </a>
    </div>
  </div>

  <!-- Right: boutique info -->
  <div class="vp-info">
    <div class="vp-info-header">
      <div class="section-tag">Informations Boutique</div>
      <h2 class="section-title"><?= htmlspecialchars($boutique['nom']) ?></h2>
      <?php if (!empty($boutique['description'])): ?>
        <p class="section-sub"><?= htmlspecialchars($boutique['description']) ?></p>
      <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="boutique-highlights">
      <div class="bh-card">
        <div class="bh-icon"><i class="fas fa-box-open"></i></div>
        <div class="bh-num"><?= $boutique['produits_actifs'] ?></div>
        <div class="bh-label">Produits disponibles</div>
      </div>
      <div class="bh-card">
        <div class="bh-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="bh-num"><?= $boutique['total_commandes'] ?></div>
        <div class="bh-label">Commandes reçues</div>
      </div>
      <div class="bh-card">
        <div class="bh-icon"><i class="fas fa-heart"></i></div>
        <div class="bh-num"><?= $boutique['total_favoris'] ?></div>
        <div class="bh-label">Favoris</div>
      </div>
    </div>

    <!-- Details -->
    <div class="boutique-details-grid">
      <?php if (!empty($boutique['adresse'])): ?>
      <div class="bd-item">
        <i class="fas fa-map-marker-alt"></i>
        <div><strong>Localisation</strong><span><?= htmlspecialchars($boutique['adresse']) ?></span></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($boutique['whatsapp'])): ?>
      <div class="bd-item">
        <i class="fab fa-whatsapp"></i>
        <div><strong>WhatsApp</strong><span><?= htmlspecialchars($boutique['whatsapp']) ?></span></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($boutique['proprietaire_email'])): ?>
      <div class="bd-item">
        <i class="fas fa-envelope"></i>
        <div><strong>Email</strong><span><?= htmlspecialchars($boutique['proprietaire_email']) ?></span></div>
      </div>
      <?php endif; ?>
      <div class="bd-item">
        <i class="fas fa-store"></i>
        <div><strong>Boutique créée</strong><span><?= $membre_depuis ?></span></div>
      </div>
    </div>

    <!-- Horaires -->
    <?php
    $today_idx = (int)date('w'); // 0=dim
    $days = [
      ['label'=>'Lundi','hours'=>'08:00 – 20:00'],
      ['label'=>'Mardi','hours'=>'08:00 – 20:00'],
      ['label'=>'Mercredi','hours'=>'08:00 – 20:00'],
      ['label'=>'Jeudi','hours'=>'08:00 – 20:00'],
      ['label'=>'Vendredi','hours'=>'08:00 – 20:00'],
      ['label'=>'Samedi','hours'=>'09:00 – 18:00'],
      ['label'=>'Dimanche','hours'=>null],
    ];
    // today_idx: 1=lundi...0=dim→6
    $php_day_map = [1=>0,2=>1,3=>2,4=>3,5=>4,6=>5,0=>6];
    $today_in_arr = $php_day_map[$today_idx];
    ?>
    <div class="hours-grid">
      <div class="hours-header"><i class="far fa-clock"></i> Horaires d'ouverture</div>
      <?php foreach ($days as $i => $d): ?>
      <div class="hours-row <?= $i === $today_in_arr ? 'today' : '' ?>">
        <span class="day"><?= $d['label'] ?><?= $i === $today_in_arr ? ' (aujourd\'hui)' : '' ?></span>
        <?php if ($d['hours']): ?>
          <span class="time"><?= $d['hours'] ?></span>
        <?php else: ?>
          <span class="closed">Fermé</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════ CATEGORIES ═══════ -->
<section class="categories" id="categories">
  <div class="section-header">
    <div class="section-tag">Nos Univers</div>
    <h2 class="section-title">Explorez nos <em>catégories</em></h2>
    <p class="section-sub">Naviguez facilement parmi toutes nos gammes de produits.</p>
  </div>
  <!-- Chip bar -->
  <div class="cat-scroller">
    <button class="cat-chip active" data-cat="all" onclick="filterProducts('all',this)">
      <i class="fas fa-th"></i> Tout voir
      <span class="cat-count-badge"><?= count($produits) ?></span>
    </button>
    <?php foreach ($categories as $cat): ?>
    <button class="cat-chip" data-cat="<?= htmlspecialchars($cat['categorie']) ?>"
      onclick="filterProducts('<?= htmlspecialchars($cat['categorie']) ?>',this)">
      <i class="fas <?= getCatIcon($cat['categorie'], $cat_icons) ?>"></i>
      <?= htmlspecialchars(ucfirst($cat['categorie'])) ?>
      <span class="cat-count-badge"><?= $cat['nb'] ?></span>
    </button>
    <?php endforeach; ?>
  </div>
  <!-- Grid display -->
  <div class="cat-grid-display">
    <a class="cat-grid-card active" href="#produits" onclick="filterProducts('all',null)">
      <i class="fas fa-th"></i>
      <div class="cat-grid-name">Tout voir</div>
      <div class="cat-grid-nb"><?= count($produits) ?> produits</div>
    </a>
    <?php foreach ($categories as $cat): ?>
    <a class="cat-grid-card" href="#produits"
       onclick="filterProducts('<?= htmlspecialchars($cat['categorie']) ?>',null)">
      <i class="fas <?= getCatIcon($cat['categorie'], $cat_icons) ?>"></i>
      <div class="cat-grid-name"><?= htmlspecialchars(ucfirst($cat['categorie'])) ?></div>
      <div class="cat-grid-nb"><?= $cat['nb'] ?> produit<?= $cat['nb'] > 1 ? 's' : '' ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══════ PRODUCTS ═══════ -->
<section class="products" id="produits">
  <div class="section-header">
    <div class="section-tag">Notre Boutique</div>
    <h2 class="section-title">Nos <em>produits</em> phares</h2>
    <p class="section-sub">Des articles soigneusement sélectionnés pour vous satisfaire.</p>
  </div>
  <div class="products-toolbar">
    <div class="products-count" id="productsCount"><?= count($produits) ?> produits trouvés</div>
    <div class="products-sort">
      <i class="fas fa-sort-amount-down" style="color:var(--rose)"></i>
      Trier par&nbsp;
      <select id="sortSelect" onchange="sortProducts(this.value)">
        <option value="default">Par défaut</option>
        <option value="price_asc">Prix croissant</option>
        <option value="price_desc">Prix décroissant</option>
        <option value="name_asc">Nom A-Z</option>
      </select>
    </div>
  </div>
  <div class="products-grid" id="productsGrid">
    <?php if (empty($produits)): ?>
      <div class="no-products"><i class="fas fa-box-open"></i><br>Aucun produit disponible pour le moment.</div>
    <?php else: ?>
      <?php foreach ($produits as $p): ?>
        <?php
          $img = $p['image'] ?? null;
          $cat = htmlspecialchars($p['categorie'] ?? 'general');
          $nom = htmlspecialchars($p['nom']);
          $desc = htmlspecialchars($p['description'] ?? '');
          $prix = number_format((float)$p['prix'], 0, ',', ' ');
          $p_json = htmlspecialchars(json_encode($p, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
        ?>
        <div class="product-card" data-cat="<?= $cat ?>" data-price="<?= $p['prix'] ?>" data-name="<?= $nom ?>">
          <div class="product-img">
            <?php if ($img): ?>
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= $nom ?>" loading="lazy"
                   onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\'fas fa-image\'></i>'">
            <?php else: ?>
              <i class="fas <?= getCatIcon($p['categorie'] ?? '', $cat_icons) ?>" style="font-size:3rem;color:var(--rose-lt)"></i>
            <?php endif; ?>
          </div>
          <div class="product-body">
            <div class="product-cat"><?= ucfirst($cat) ?></div>
            <div class="product-name"><?= $nom ?></div>
            <div class="product-desc"><?= $desc ?></div>
            <div class="product-footer">
              <div>
                <span class="product-price"><?= $prix ?> FCFA</span>
              </div>
              <button class="add-btn" title="Ajouter au panier"
                onclick="addToCart(this, <?= $p_json ?>)">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════ ABOUT + TESTIMONIALS ═══════ -->
<section class="about-section" id="apropos">
  <div class="about-left">
    <div class="section-tag">Pourquoi nous choisir ?</div>
    <h2 class="section-title">La qualité <em>authentique</em><br>à votre portée</h2>
    <p class="section-sub" style="color:rgba(255,255,255,.5)">
      <?= htmlspecialchars($boutique['nom']) ?> s'engage à vous offrir les meilleurs produits avec un service client irréprochable.
    </p>
    <div class="feat-list">
      <div class="feat-item">
        <div class="feat-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="feat-text">
          <strong>Produits 100% authentiques</strong>
          <span>Chaque article est vérifié avant mise en vente</span>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fas fa-shipping-fast"></i></div>
        <div class="feat-text">
          <strong>Livraison rapide<?php if (!empty($boutique['adresse'])): ?> à <?= htmlspecialchars($boutique['adresse']) ?><?php endif; ?></strong>
          <span>Commandez et recevez votre colis sous 24–48h</span>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fab fa-whatsapp"></i></div>
        <div class="feat-text">
          <strong>Commande simplifiée via WhatsApp</strong>
          <span>Finalisez votre achat en quelques messages</span>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fas fa-headset"></i></div>
        <div class="feat-text">
          <strong>Support client 7j/7</strong>
          <span>Notre équipe est toujours disponible pour vous aider</span>
        </div>
      </div>
    </div>
  </div>
  <div class="about-right">
    <div class="testi-grid">
      <div class="testi">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Les produits sont exactement comme décrits. Livraison rapide et emballage soigné. Je recommande vivement !"</p>
        <div class="testi-author">
          <div class="testi-av">AM</div>
          <div><div class="testi-name">Aminata M.</div><div class="testi-role">Cliente fidèle</div></div>
        </div>
      </div>
      <div class="testi">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"La commande via WhatsApp est super pratique. J'ai reçu mon colis le lendemain, impeccable !"</p>
        <div class="testi-author">
          <div class="testi-av">SC</div>
          <div><div class="testi-name">Sandra C.</div><div class="testi-role">Nouvelle cliente</div></div>
        </div>
      </div>
      <div class="testi">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Excellente qualité, prix compétitifs. Je commande régulièrement et je n'ai jamais été déçue."</p>
        <div class="testi-author">
          <div class="testi-av">FK</div>
          <div><div class="testi-name">Fatoumata K.</div><div class="testi-role">Cliente fidèle</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ CTA ═══════ -->
<section class="cta-section" id="contact">
  <div class="section-tag">Commander maintenant</div>
  <h2 class="section-title">Une question ? <em>Écrivez-nous</em></h2>
  <p class="section-sub">
    <?= htmlspecialchars($boutique['proprietaire_nom'] ?? 'Notre équipe') ?> est disponible 7j/7
    pour vous conseiller et finaliser votre commande.
  </p>
  <a class="wa-btn" href="<?= $whatsapp_url ?>" target="_blank" rel="noopener">
    <i class="fab fa-whatsapp"></i>
    Écrire sur WhatsApp
  </a>
</section>

<!-- ═══════ FOOTER ═══════ -->
<footer>
  <div>
    <a class="footer-logo" href="#"><span><?= htmlspecialchars($boutique['nom']) ?></span></a>
    <p>Votre boutique de confiance sur CreatorMarket. Des produits soigneusement sélectionnés, livrés rapidement.</p>
    <?php if (!empty($boutique['adresse'])): ?>
    <p style="margin-top:.5rem;font-size:.78rem">
      <i class="fas fa-map-marker-alt" style="color:var(--rose);margin-right:4px"></i>
      <?= htmlspecialchars($boutique['adresse']) ?>
    </p>
    <?php endif; ?>
  </div>
  <div>
    <h5>Boutique</h5>
    <ul>
      <li><a href="#produits">Tous les produits</a></li>
      <?php foreach ($categories as $cat): ?>
      <li><a href="#produits" onclick="filterProducts('<?= htmlspecialchars($cat['categorie']) ?>',null)"><?= htmlspecialchars(ucfirst($cat['categorie'])) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div>
    <h5>Contact</h5>
    <ul>
      <?php if (!empty($boutique['whatsapp'])): ?>
      <li><a href="<?= $whatsapp_url ?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
      <?php endif; ?>
      <?php if (!empty($boutique['proprietaire_email'])): ?>
      <li><a href="mailto:<?= htmlspecialchars($boutique['proprietaire_email']) ?>"><i class="fas fa-envelope"></i> Email</a></li>
      <?php endif; ?>
      <li><a href="#vendeur">Profil vendeur</a></li>
      <li><a href="#apropos">À propos</a></li>
    </ul>
  </div>
</footer>
<div class="footer-bottom">
  © <?= date('Y') ?> <?= htmlspecialchars($boutique['nom']) ?> · Tous droits réservés ·
  Propulsé par <a href="#">CreatorMarket</a>
</div>

<!-- ═══════ CART ═══════ -->
<div class="overlay" id="overlay" onclick="toggleCart()"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="drawer-header">
    <h2>Mon Panier</h2>
    <button class="close-btn" onclick="toggleCart()">✕</button>
  </div>
  <div class="cart-items-list" id="cartItemsList">
    <div class="cart-empty-state">
      <i class="fas fa-shopping-bag"></i>
      <p>Votre panier est vide</p>
      <small style="font-size:.78rem">Ajoutez des produits pour commencer</small>
    </div>
  </div>
  <div class="drawer-footer">
    <div class="cart-total-row">
      <span class="cart-total-label">Total</span>
      <span class="cart-total-amount" id="cartTotal">0 FCFA</span>
    </div>
    <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
      <i class="fab fa-whatsapp"></i>
      Commander via WhatsApp
    </button>
    <p class="cart-note">Vous serez redirigé vers WhatsApp pour finaliser avec le vendeur.</p>
  </div>
</div>

<!-- ═══════ CHATBOT ═══════ -->
<button class="chatbot-fab" id="chatFab" onclick="toggleChat()" title="Assistant boutique">
  <i class="fas fa-comment-dots" id="chatFabIcon"></i>
  <span class="fab-badge">AI</span>
</button>
<div class="chatbot-window" id="chatWindow">
  <div class="chat-header">
    <div class="chat-header-left">
      <div class="chat-avatar"><i class="fas fa-robot"></i></div>
      <div>
        <div class="chat-title">Assistant <?= htmlspecialchars($boutique['nom']) ?></div>
        <div class="chat-subtitle">
          <span class="chat-dot"></span>En ligne maintenant
        </div>
      </div>
    </div>
    <button class="chat-close-btn" onclick="toggleChat()"><i class="fas fa-times"></i></button>
  </div>
  <div class="chat-messages" id="chatMessages"></div>
  <div class="chat-suggestions" id="chatSuggs"></div>
  <div class="chat-input-row">
    <textarea class="chat-input" id="chatInput" placeholder="Posez votre question…" rows="1"
      onkeydown="handleChatKey(event)" oninput="autoResizeTextarea(this)"></textarea>
    <button class="chat-send-btn" id="chatSendBtn" onclick="sendChat()">
      <i class="fas fa-paper-plane"></i>
    </button>
  </div>
</div>

<!-- ═══════ TOAST ═══════ -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg">Produit ajouté au panier</span>
</div>

<!-- ═══════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════ -->
<script>
/* ── Config PHP → JS ── */
const WHATSAPP_NUM   = <?= $js_whatsapp ?>;
const BOUTIQUE_ID    = <?= $js_boutique_id ?>;
const BOUTIQUE_NAME  = <?= $js_boutique_name ?>;
const BOUTIQUE_DESC  = <?= $js_boutique_desc ?>;
const BOUTIQUE_ADDR  = <?= $js_boutique_adresse ?>;
const PROPRIETAIRE   = <?= $js_proprietaire ?>;
const ALL_PRODUCTS   = <?= $js_products ?>;
const ALL_CATEGORIES = <?= $js_categories ?>;

/* ══════════════════════════════════════════
   NAV SCROLL
══════════════════════════════════════════ */
window.addEventListener('scroll', () => {
  document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 30);
});

function toggleMobile() {
  const m = document.getElementById('navMobile');
  const h = document.getElementById('hamburger');
  m.classList.toggle('open');
  h.classList.toggle('active');
}
function closeMobile() {
  document.getElementById('navMobile').classList.remove('open');
  document.getElementById('hamburger').classList.remove('active');
}

/* ══════════════════════════════════════════
   CART
══════════════════════════════════════════ */
let cart = [];

function toggleCart() {
  document.getElementById('cartDrawer').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}

function addToCart(btn, product) {
  const id = String(product.id);
  const existing = cart.find(i => i.id === id);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({
      id,
      name: product.nom,
      price: parseFloat(product.prix),
      image: product.image || null,
      cat: product.categorie || 'general',
      qty: 1,
    });
  }
  renderCart();
  showToast('✓ ' + product.nom.substring(0, 30) + ' ajouté !');
  /* Feedback bouton */
  btn.innerHTML = '<i class="fas fa-check"></i>';
  btn.classList.add('added');
  setTimeout(() => { btn.innerHTML = '<i class="fas fa-plus"></i>'; btn.classList.remove('added'); }, 1400);
}

function changeQty(id, delta) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
  renderCart();
}

function removeItem(id) {
  cart = cart.filter(i => i.id !== id);
  renderCart();
}

function renderCart() {
  const countEl   = document.getElementById('cartCount');
  const totalEl   = document.getElementById('cartTotal');
  const listEl    = document.getElementById('cartItemsList');
  const checkBtn  = document.getElementById('checkoutBtn');

  const totalQty   = cart.reduce((s, i) => s + i.qty, 0);
  const totalPrice = cart.reduce((s, i) => s + i.price * i.qty, 0);

  countEl.textContent = totalQty;
  countEl.classList.add('bump');
  setTimeout(() => countEl.classList.remove('bump'), 300);

  totalEl.textContent = totalPrice.toLocaleString('fr-FR') + ' FCFA';
  checkBtn.disabled   = cart.length === 0;

  if (cart.length === 0) {
    listEl.innerHTML = `<div class="cart-empty-state">
      <i class="fas fa-shopping-bag"></i>
      <p>Votre panier est vide</p>
      <small style="font-size:.78rem">Ajoutez des produits pour commencer</small>
    </div>`;
    return;
  }

  listEl.innerHTML = cart.map(item => `
    <div class="cart-item">
      <div class="ci-thumb">${item.image
        ? `<img src="${escHtml(item.image)}" alt="${escHtml(item.name)}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>'">`
        : '<i class="fas fa-box"></i>'
      }</div>
      <div class="ci-info">
        <div class="ci-name">${escHtml(item.name)}</div>
        <div class="ci-price">${(item.price * item.qty).toLocaleString('fr-FR')} FCFA</div>
        <div class="ci-controls">
          <button class="qty-btn" onclick="changeQty('${escHtml(item.id)}',-1)">−</button>
          <span class="qty-val">${item.qty}</span>
          <button class="qty-btn" onclick="changeQty('${escHtml(item.id)}',1)">+</button>
        </div>
      </div>
      <button class="ci-remove" onclick="removeItem('${escHtml(item.id)}')"><i class="fas fa-trash-alt"></i></button>
    </div>
  `).join('');
}

function checkout() {
  if (!cart.length) return;
  const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
  let msg = `🛍️ *Bonjour ${BOUTIQUE_NAME} !*\n\nJe souhaite commander :\n\n`;
  cart.forEach(item => {
    msg += `• *${item.name}* × ${item.qty} = ${(item.price * item.qty).toLocaleString('fr-FR')} FCFA\n`;
  });
  msg += `\n━━━━━━━━━━━━━━\n💰 *Total : ${total.toLocaleString('fr-FR')} FCFA*\n\n`;
  msg += `Merci de confirmer la disponibilité et les modalités de livraison 🙏`;
  window.open(`https://wa.me/${WHATSAPP_NUM}?text=${encodeURIComponent(msg)}`, '_blank');
}

/* ══════════════════════════════════════════
   FILTER / SORT
══════════════════════════════════════════ */
let currentFilter = 'all';

function filterProducts(cat, chipEl) {
  currentFilter = cat;
  const cards = document.querySelectorAll('.product-card');
  let visible = 0;
  cards.forEach(card => {
    const show = cat === 'all' || card.dataset.cat === cat;
    card.classList.toggle('hidden', !show);
    if (show) visible++;
  });
  /* Update count */
  const cntEl = document.getElementById('productsCount');
  if (cntEl) cntEl.textContent = visible + ' produit' + (visible > 1 ? 's' : '') + ' trouvé' + (visible > 1 ? 's' : '');
  /* Chips */
  if (chipEl) {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    chipEl.classList.add('active');
  }
  /* Grid cards */
  document.querySelectorAll('.cat-grid-card').forEach(c => c.classList.remove('active'));
}

function sortProducts(method) {
  const grid = document.getElementById('productsGrid');
  const cards = [...grid.querySelectorAll('.product-card')];
  cards.sort((a, b) => {
    const pA = parseFloat(a.dataset.price), pB = parseFloat(b.dataset.price);
    const nA = a.dataset.name, nB = b.dataset.name;
    if (method === 'price_asc')  return pA - pB;
    if (method === 'price_desc') return pB - pA;
    if (method === 'name_asc')   return nA.localeCompare(nB, 'fr');
    return 0;
  });
  cards.forEach(c => grid.appendChild(c));
}

/* ══════════════════════════════════════════
   CHATBOT (AI Claude)
══════════════════════════════════════════ */
let chatOpen = false;
let chatTyping = false;
let chatStarted = false;

const SUGGESTIONS = [
  'Quels produits avez-vous ?',
  'Comment commander ?',
  'Délai de livraison ?',
  'Mode de paiement ?',
  'Contacter le vendeur',
];

function toggleChat() {
  chatOpen = !chatOpen;
  const win  = document.getElementById('chatWindow');
  const icon = document.getElementById('chatFabIcon');
  win.classList.toggle('open', chatOpen);
  icon.className = chatOpen ? 'fas fa-times' : 'fas fa-comment-dots';
  if (chatOpen && !chatStarted) {
    chatStarted = true;
    appendBotMsg(`Bonjour 👋 Je suis l'assistant de **${BOUTIQUE_NAME}**. Je peux vous aider à :\n• Trouver un produit\n• Comprendre comment commander\n• Obtenir des informations sur la boutique\n\nQue puis-je faire pour vous ?`);
    renderSuggestions(SUGGESTIONS);
  }
}

function renderSuggestions(list) {
  const el = document.getElementById('chatSuggs');
  el.innerHTML = list.map(s =>
    `<button class="chat-sugg" onclick="sendChatMsg('${s.replace(/'/g,"\\'")}')">
      ${escHtml(s)}
    </button>`
  ).join('');
}

function appendBotMsg(text) {
  const el = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = 'chat-msg bot';
  /* Simple markdown: **bold** */
  const formatted = escHtml(text)
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>');
  div.innerHTML = `
    <div class="msg-av"><i class="fas fa-robot"></i></div>
    <div class="msg-bubble">${formatted}</div>`;
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
}

function appendUserMsg(text) {
  const el = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = 'chat-msg user';
  div.innerHTML = `<div class="msg-bubble">${escHtml(text)}</div><div class="msg-av user-av"><i class="fas fa-user"></i></div>`;
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
}

function showTyping() {
  const el = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = 'chat-msg bot typing-msg';
  div.innerHTML = `
    <div class="msg-av"><i class="fas fa-robot"></i></div>
    <div class="typing-indicator"><span></span><span></span><span></span></div>`;
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
}
function removeTyping() {
  document.querySelector('.typing-msg')?.remove();
}

function handleChatKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChat(); }
}
function autoResizeTextarea(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 80) + 'px';
}

async function sendChat() {
  const input = document.getElementById('chatInput');
  const msg   = input.value.trim();
  if (!msg || chatTyping) return;
  input.value = '';
  input.style.height = 'auto';
  sendChatMsg(msg);
}

async function sendChatMsg(msg) {
  if (chatTyping) return;
  document.getElementById('chatSuggs').innerHTML = '';
  appendUserMsg(msg);
  chatTyping = true;
  document.getElementById('chatSendBtn').disabled = true;
  showTyping();

  /* Build context about the boutique */
  const productList = ALL_PRODUCTS.slice(0, 20)
    .map(p => `- ${p.nom} (${p.categorie || 'général'}) : ${parseInt(p.prix).toLocaleString('fr-FR')} FCFA`)
    .join('\n');

  const systemPrompt = `Tu es l'assistant IA de la boutique "${BOUTIQUE_NAME}" sur CreatorMarket, une plateforme e-commerce camerounaise. 
Propriétaire: ${PROPRIETAIRE}
Adresse: ${BOUTIQUE_ADDR}
WhatsApp: ${WHATSAPP_NUM}
Description: ${BOUTIQUE_DESC}

Produits disponibles (${ALL_PRODUCTS.length} au total):
${productList}
${ALL_PRODUCTS.length > 20 ? `...et ${ALL_PRODUCTS.length - 20} autres produits.` : ''}

Réponds en français, de manière chaleureuse, concise et utile. 
Tu aides les clients à :
- Trouver des produits qui correspondent à leurs besoins
- Comprendre comment passer commande via WhatsApp
- Obtenir des informations sur la livraison, les prix, la boutique
- Contacter le vendeur si nécessaire

Si un client veut commander, dis-lui qu'il peut cliquer sur "Ajouter au panier" puis "Commander via WhatsApp", ou écrire directement au ${WHATSAPP_NUM}.
Garde tes réponses courtes (max 3-4 phrases sauf si nécessaire).
Ne donne pas d'informations que tu ne connais pas.`;

  try {
    const resp = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'claude-sonnet-4-20250514',
        max_tokens: 400,
        system: systemPrompt,
        messages: [{ role: 'user', content: msg }],
      }),
    });

    if (!resp.ok) throw new Error('API error ' + resp.status);
    const data = await resp.json();
    const reply = data.content?.find(b => b.type === 'text')?.text || 'Désolé, je n\'ai pas pu traiter votre demande.';
    removeTyping();
    appendBotMsg(reply);
    /* Suggest follow-ups */
    renderSuggestions(['Voir les produits', 'Comment commander ?', 'Contacter le vendeur']);
  } catch (err) {
    removeTyping();
    appendBotMsg('Désolé, une erreur s\'est produite. Vous pouvez contacter directement le vendeur sur WhatsApp au ' + WHATSAPP_NUM + '.');
    console.error('Chat error:', err);
  } finally {
    chatTyping = false;
    document.getElementById('chatSendBtn').disabled = false;
  }
}

/* ══════════════════════════════════════════
   TOAST
══════════════════════════════════════════ */
function showToast(msg, duration = 3000) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), duration);
}

/* ══════════════════════════════════════════
   UTILS
══════════════════════════════════════════ */
function escHtml(str) {
  if (typeof str !== 'string') str = String(str ?? '');
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* Close cart/chat on Escape */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.getElementById('cartDrawer').classList.remove('open');
    document.getElementById('overlay').classList.remove('open');
  }
});
</script>
</body>
</html>