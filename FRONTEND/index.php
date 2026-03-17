<?php
session_start();

$pdo = new PDO(
    'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$stmt = $pdo->prepare("
    SELECT *
    FROM produits p
    INNER JOIN boutiques b ON p.boutique_id = b.id
    INNER JOIN utilisateurs u ON b.utilisateur_id = u.id
    WHERE p.statut = 'disponible'
    ORDER BY p.date_ajout DESC
");

$stmt->execute();

$produits= $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Beauty Innova - Salon de beauté à Yaoundé</title>
    <!-- google fonts -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="//fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="//fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500;1,700&display=swap" rel="stylesheet">
     <!-- Font Awesome -->
     <script src="https://kit.fontawesome.com/f005d38d38.js" crossorigin="anonymous"></script>
    <!-- Template CSS -->
    <link rel="stylesheet" href="assets/css/style-starter.css">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    
    <style>
        .service-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .image-container {
            position: relative;
            overflow: hidden;
            height: 280px;
        }

        .service-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .service-card:hover .service-image {
            transform: scale(1.1);
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .service-card:hover .image-overlay {
            opacity: 1;
        }

        .service-title {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            font-size: 24px;
            font-weight: 600;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .service-card:hover .service-title {
            opacity: 1;
            transform: translateY(0);
        }

        .badge-popular {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 25px;
        }

        .service-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: background 0.3s ease;
        }

        .info-item:hover {
            background: #e9ecef;
        }

        .info-icon {
            font-size: 20px;
            color: #667eea;
            margin-bottom: 8px;
        }

        .info-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 700;
            color: #2d3748;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-service {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-service.btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-service.btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-service.btn-secondary-custom {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-service.btn-secondary-custom:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .btn-service i {
            font-size: 16px;
        }

        @media (max-width: 480px) {
            .action-buttons {
                flex-direction: column;
            }

            .service-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
  </head>
  <body>
<!--header-->
<header id="site-header" class="fixed-top">
  <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark stroke">
      <h1 class="navbar-brand expand-lg mx-lg-auto">
        <a class="navbar-brand" href="index.html">
          <span id="nom_footer"> <i class="fa fa-cut"></i>Beauty Innova</span> <span class="logo">Rendez-vous belle !</span>
        </a>
      </h1>

      <button class="navbar-toggler  collapsed bg-gradient" type="button" data-toggle="collapse"
        data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
        <span class="navbar-toggler-icon fa icon-close fa-times"></span>
      </button>

      <div class="collapse navbar-collapse test" id="navbarTogglerDemo02">
        <ul class="navbar-nav mx-lg-auto">
          <li class="nav-item active">
            <a class="nav-link" href="index.html">Accueil<span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="services.html">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="projects.html">Galerie</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="about.html">À propos</a>
          </li>
        </ul>
      </div>

      <div class="top-quote mr-lg-2 mt-lg-0 mt-3 d-lg-block d-none">
        <a href="contact.html" class="btn btn-style btn-secondary">Réservez</a>
      </div>

      <!-- toggle switch for light and dark theme -->
      <div class="mobile-position">
        <nav class="navigation">
          <div class="theme-switch-wrapper">
            <label class="theme-switch" for="checkbox">
              <input type="checkbox" id="checkbox">
              <div class="mode-container py-1">
                <i class="gg-sun"></i>
                <i class="gg-moon"></i>
              </div>
            </label>
          </div>
        </nav>
      </div>
    </nav>
  </div>
</header>
<style>
   /* Style du bouton flottant */
        #panier {
            padding: 0;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: fixed;
            right: 30px;
            bottom: 80px;
            width: 70px;
            height: 70px;
            z-index: 9999;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            animation: pulse 2s infinite;
        }

        #panier i {
            font-size: 28px;
            color: white;
            transition: transform 0.3s ease;
        }

        #panier:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transform: scale(1.15) rotate(10deg);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.6);
        }

        #panier:hover i {
            transform: scale(1.1) rotate(-10deg);
            animation: shake 0.5s ease-in-out;
        }

        #panier:active {
            transform: scale(0.95);
        }


        /* Effet de pulsation */
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            }
            50% {
                box-shadow: 0 8px 35px rgba(102, 126, 234, 0.8);
            }
        }

        /* Animation de secousse */
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
        }

        /* Animation de rebond pour le badge */
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Tooltip au survol */
        #panier::after {
            content: 'Assistant IA';
            position: absolute;
            right: 80px;
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        #panier:hover::after {
            opacity: 1;
        }

        /* Effet d'ondulation au clic */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            width: 100px;
            height: 100px;
            margin-top: -50px;
            margin-left: -50px;
            animation: ripple-effect 0.6s;
            pointer-events: none;
        }

        @keyframes ripple-effect {
            from {
                opacity: 1;
                transform: scale(0);
            }
            to {
                opacity: 0;
                transform: scale(2);
            }
        }

        /* Version alternative avec icône panier */
        .btn-variant-cart {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .btn-variant-cart:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

    
         #panie {
            padding: 0;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: fixed;
            right: 30px;
            bottom: 140px;
            width: 70px;
            height: 70px;
            z-index: 9999;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            animation: pulse 2s infinite;
        }

        #panie i {
            font-size: 28px;
            color: white;
            transition: transform 0.3s ease;
        }

        #panie:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transform: scale(1.15) rotate(10deg);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.6);
        }

        #panie:hover i {
            transform: scale(1.1) rotate(-10deg);
            animation: shake 0.5s ease-in-out;
        }

        #panie:active {
            transform: scale(0.95);
        }

        /* Badge de notification */
        #panie::before {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            border: 3px solid white;
            animation: bounce 1s infinite;
        }

        /* Effet de pulsation */
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            }
            50% {
                box-shadow: 0 8px 35px rgba(102, 126, 234, 0.8);
            }
        }

        /* Animation de secousse */
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
        }

        /* Animation de rebond pour le badge */
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Tooltip au survol */
        #panie::after {
            content: 'Panier';
            position: absolute;
            right: 80px;
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        #panie:hover::after {
            opacity: 1;
        }

        /* Effet d'ondulation au clic */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            width: 100px;
            height: 100px;
            margin-top: -50px;
            margin-left: -50px;
            animation: ripple-effect 0.6s;
            pointer-events: none;
        }

        @keyframes ripple-effect {
            from {
                opacity: 1;
                transform: scale(0);
            }
            to {
                opacity: 0;
                transform: scale(2);
            }
        }

        /* Version alternative avec icône panier */
        .btn-variant-cart {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .btn-variant-cart:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

          .containe{
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
            height: 90vh;
            position: fixed;
            left: 60%;
            top: 0%;
            z-index: 9999;
             display: flex;
            flex-direction: column;
            overflow-y:auto; 
        }
          .header {
            background: linear-gradient(135deg, #f50499 0%,#f00596 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message {
                      overflow-y: auto;
            margin-bottom: 20px;
            display: flex;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.user {
                                overflow-y: auto;
            justify-content: flex-end;
        }
        
        .message-content {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #f50499 0%, #f50499 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.ai .message-content {
            background: white;
            color: #333;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        
        #userInput {
            flex: 1;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
        }
        
        #userInput:focus {
            border-color: #667eea;
        }
        
        #sendBtn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        #sendBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        #sendBtn:active {
            transform: translateY(0);
        }
        
        .typing {
            display: flex;
            gap: 5px;
            padding: 15px;
        }
        
        .typing span {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        
        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        
        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .suggestion-btn {
            padding: 8px 16px;
            background: white;
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .suggestion-btn:hover {
            background: #667eea;
            color: white;}
            .products-content{
grid-template-columns: repeat(2, minmax(250px, 1fr));
            }
</style>
<!-- //section panier -->
<button id="panier">
        <i class="fas fa-robot"></i>
    </button>
    <!-- //SECTION Assistant -->

<div class="containe" style="display:none;" id="container">
  <div class="header">
    <h1>💄 IA Beauté & Mode</h1>
    <p>Votre assistante virtuelle pour tous vos conseils beauté et style</p>
  </div>

  <div class="chat-container" id="chatContainer" >
    <div class="message ai">
      <div class="message-content">
        Bonjour! 👋 Je suis votre assistante virtuelle spécialisée en beauté et mode. Je peux vous aider avec:
        <br><br>
        ✨ Conseils maquillage et soins de la peau<br>
        👗 Suggestions de style et tendances<br>
        💇‍♀️ Conseils capillaires<br>
        🎨 Associations de couleurs<br><br>
        Comment puis-je vous aider aujourd'hui?
        <div class="suggestions">
          <button class="suggestion-btn" onclick="sendSuggestion('Quelle routine de soin pour peau sèche?')">Routine peau sèche</button>
          <button class="suggestion-btn" onclick="sendSuggestion('Tendances mode 2024')">Tendances mode</button>
          <button class="suggestion-btn" onclick="sendSuggestion('Comment choisir son rouge à lèvres?')">Rouge à lèvres</button>
        </div>
      </div>
    </div>
  </div>

  <div class="input-container">
    <input type="text" id="userInput" placeholder="Posez votre question..." onkeypress="handleKeyPress(event)">
    <button id="sendBtn" onclick="sendMessage()">Envoyer</button>
  </div>
</div>

<script>
  const robot = document.getElementById('panier');
  const container = document.getElementById('container');

  // Bascule d'affichage du conteneur
  robot.addEventListener('click', (event) => {
    event.stopPropagation(); // empêche la fermeture immédiate
    const visible = window.getComputedStyle(container).display === 'block';
    container.style.display = visible ? 'none' : 'block';
  });

  // Clic sur la page = fermer
  document.body.addEventListener('click', () => {
    container.style.display = 'none';
  });

  // Empêche la fermeture quand on clique dans le conteneur
  container.addEventListener('click', (event) => {
    event.stopPropagation();
  });
</script>



<!-- main-slider -->
<style>
.w3l-main-slider .item {
  position: relative;
  height: 100vh;
  overflow: hidden;
}
.w3l-main-slider .slider-info {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  transition: transform 0.6s ease;
}
.w3l-main-slider .item:hover .slider-info {
  transform: scale(1.05);
}
.banner-info {
  position: relative;
  z-index: 2;
  color: #fff;
  text-shadow: 0 2px 10px rgba(0,0,0,0.7);
}
.w3l-main-slider .slider-info::after {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 1;
}
.banner-info-bg h5 span {
  color: #f8c146;
}
</style>

<section class="w3l-main-slider" id="home">
  <div class="companies20-content">
    <div class="owl-one owl-carousel owl-theme">

      <div class="item">
        <div class="slider-info banner-view bg" style="background-image: url('https://static.vecteezy.com/ti/photos-gratuite/p1/29561123-interieur-de-salon-de-beaute-gratuit-photo.jpg');">
          <div class="banner-info">
            <div class="container">
              <div class="banner-info-bg">
                <h5>Votre destination beauté <span>exceptionnelle</span></h5>
                <p class="mt-4 pr-lg-4">Des services professionnels de coiffure, soins et esthétique.<br>Yaoundé - Cameroun</p>
                <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="about.html">En savoir plus</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="item">
        <div class="slider-info banner-view bg" style="background-image: url('https://i.pinimg.com/originals/1d/4f/d5/1d4fd563760f4009512a009e0e138f60.jpg');">
          <div class="banner-info">
            <div class="container">
              <div class="banner-info-bg">
                <h5>Sublimez votre <span>beauté naturelle</span></h5>
                <p class="mt-4 pr-lg-4">Des soins personnalisés dans un cadre élégant et raffiné.<br>Yaoundé - Cameroun</p>
                <a class="btn btn-style btn-primary mt-sm-5 mt-4 mr-2" href="about.html">En savoir plus</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="item">
        <div class="slider-info banner-view bg" style="background-image: url('https://cdn.pixabay.com/photo/2020/07/17/23/17/salon-5415669_1280.jpg');">
          <div class="banner-info">
            <div class="container">
              <div class="banner-info-bg">
                <h5>Offrez-vous un moment de <span>détente</span> absolue</h5>
                <p class="mt-4 pr-lg-4">Coiffure, maquillage, soins du visage et du corps.<br>Yaoundé - Cameroun</p>
                <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="about.html">Découvrir</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="item">
        <div class="slider-info banner-view bg" style="background-image: url('https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1200&q=80');">
          <div class="banner-info">
            <div class="container">
              <div class="banner-info-bg">
                <h5>Expertise et <span>élégance</span> à votre service</h5>
                <p class="mt-4 pr-lg-4">Une équipe passionnée pour révéler votre beauté.<br>Yaoundé - Cameroun</p>
                <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="about.html">Réserver</a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- /main-slider -->

<div class="w3l-grids-slider pt-5" id="about">
    <div class="container ">
        <div class="w3l-customers row my-lg-5 my-sm-4">
            <div class="col-md-12">
                <div class="owl-three owl-carousel owl-theme logo-view">
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-cut"></span>
                            <h4><a href="#url">Coiffure</a></h4>
                        </div>
                    </div>
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-star"></span>
                            <h4><a href="#url">Soins visage</a></h4>
                        </div>
                    </div>
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-hand-o-up"></span>
                            <h4><a href="#url">Manucure</a></h4>
                        </div>
                    </div>
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-paint-brush"></span>
                            <h4><a href="#url">Maquillage</a></h4>
                        </div>
                    </div>
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-heart"></span>
                            <h4><a href="#url">Pédicure</a></h4>
                        </div>
                    </div>
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-leaf"></span>
                            <h4><a href="#url">Soins capillaires</a></h4>
                        </div>
                    </div>
                    <div class="item">
                        <div class="grid">
                            <span class="fa fa-sun-o"></span>
                            <h4><a href="#url">Épilation</a></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="w3l-homeblock2" id="work">
    <div class="midd-w3 py-5">
        <div class="container pb-lg-5 pb-md-3">
            <div class="row">
                <div class="col-lg-5 left-wthree-img text-righ">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&q=80" alt="Salon de beauté" class="img-fluid">
                        <a href="#small-dialog" class="popup-with-zoom-anim play-view text-center position-absolute">
                            <span class="video-play-icon">
                                <span class="fa fa-play"></span>
                            </span>
                        </a>
                        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
                            <iframe src="https://www.youtube.com/embed/hxh8LdkoAcQ" allow="autoplay; fullscreen"
                                allowfullscreen=""></iframe>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 mt-lg-0 mt-sm-5 mt-4 align-self">
                    <span class="title-small">À propos</span>
                    <h3 class="title-big">Bienvenue chez <span>Beauty Innova</span></h3>
                    <h5 class="mt-3">Des services diversifiés dans le domaine de la beauté et du bien-être.</h5>
                    <p class="mt-4">Beauty Innova, ayant démarré ses activités en 2017, a pour objectif principal 
                        de fournir des services de beauté à valeur ajoutée qui répondent aux 
                        besoins de nos clientes. <br>
                    Notre salon offre une gamme complète de soins personnalisés dans un cadre élégant et professionnel à Yaoundé.</p>
                    <a href="services.html" class="btn btn-style btn-bleu mt-sm-5 mt-4">Voir nos services</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- our products -->
<section class="w3l-products py-5" id="projects">
    <div class="container py-lg-3">
        <div class="header-section text-center mx-auto">
            <h3 class="title-big">Nos <span>réalisations</span></h3>
            <p class="mt-3">Découvrez nos créations beauté et laissez-vous inspirer par le talent de notre équipe passionnée.</p>
        </div>
        <div class="mt-5">
            <div class="mx-auto">
              
                    <div id="template" class="resp-tabs-container hor_1">
                        <div id="products-content" class="products-content"> 
                          <?php foreach ($produits as  $produit): ?>
    <div class="row">
        <div class="service-card">
            <div class="image-container">
                <img src="<?= htmlspecialchars($produit['image']) ?>" 
                     class="service-image" 
                     alt="<?= htmlspecialchars($produit['nom']) ?>" 
                     style="object-fit:cover; width:100%; height:200px;">
                <div class="image-overlay"></div>
                <h3 class="service-title" style="color:white;">
                    <?= htmlspecialchars($produit['nom']) ?>
                </h3>
                <span class="badge-popular">Populaire</span>
            </div>

            <p style="font-size:14px; color:#444; line-height:1.5; margin:8px 0; text-align:justify;">
                <?= htmlspecialchars($produit['description']) ?>
            </p>

            <div class="card-body">
                <div class="service-info">
                    <div class="info-item">
                        <i class="fas fa-tag info-icon"></i>
                        <span class="info-label">Prix</span>
                        <span class="info-value"><?= htmlspecialchars($produit['prix']) ?> FCFA</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt info-icon"></i>
                        <span class="info-label">Localisation</span>
                        <span class="info-value"><?= htmlspecialchars($produit['localisation']) ?></span>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn-service btn-primary">
                        <i class="fas fa-shopping-cart"></i> Acheter
                    </button>
                    <button class="btn-service btn-secondary-custom">
                        <i class="fas fa-calendar-check"></i> Réserver
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- //our products -->

<section class="w3l-progressblock py-5" id="why">
    <div class="container py-lg-5 py-md-3">
        <div class="row">
            <div class="col-lg-6 about-right-faq">
                <h3 class="title-big">Pourquoi <span>nous choisir ?</span></h3>
                <p class="mt-lg-4 mt-3 mb-lg-5 mb-4">Chez Beauty Innova, notre approche de la beauté va au-delà
                      des standards. Nous sommes animés par le désir de révéler votre beauté naturelle 
                    et d'avoir un impact positif sur votre confiance en vous.</p>
                <div class="progress-info info1">
                    <h6 class="progress-tittle">Qualité des services <span class="">95%</span></h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 95%"
                            aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <div class="progress-info info2">
                    <h6 class="progress-tittle">Satisfaction client <span class="">98%</span>
                    </h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 98%"
                            aria-valuenow="98" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <div class="progress-info info3">
                    <h6 class="progress-tittle">Expertise professionnelle <span class="">90%</span></h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 90%"
                            aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 stats mt-lg-0 mt-4 pl-lg-5 align-self">
                <div class="row">
                    <div class="col-md-6 col-sm-4 col-6 stat">
                        <span class="fa fa-users"></span>
                        <div class="stats-info">
                            <span class="number">2500+</span>
                            <h4>Clientes satisfaites</h4>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-4 col-6 stat">
                        <span class="fa fa-smile-o"></span>
                        <div class="stats-info">
                            <span class="number">98%</span>
                            <h4>Taux de satisfaction</h4>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-4 col-6 stat">
                        <span class="fa fa-star-o"></span>
                        <div class="stats-info">
                            <span class="number">800+</span>
                            <h4>Avis 5 étoiles</h4>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-4 col-6 stat">
                        <span class="fa fa-calendar"></span>
                        <div class="stats-info">
                            <span class="number">7</span>
                            <h4>Années d'expérience</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="w3l-faq-block py-5" id="faq">
    <div class="container py-lg-5">
        <div class="row">
            <div class="col-lg-7">
                <section class="w3l-faq" id="faq">
                    <h3 class="title-big">Pour en savoir plus, consultez <br><span>notre FAQ</span></h3>
                    <p class="mt-3">Trouvez les réponses aux questions fréquemment posées
                         sur nos services de beauté et nos tarifs.</p>
                    <div class="faq-page mt-4">
                        <ul>
                            <li>
                                <input type="checkbox" checked>
                                <i></i>
                                <h2>Quels services proposez-vous ?</h2>
                                <p>Nous offrons une gamme complète de services : coiffure (coupe, coloration, brushing), soins esthétiques (visage, corps), manucure, pédicure, maquillage professionnel, épilation et massages relaxants.</p>
                            </li>
                            <li>
                                <input type="checkbox" checked>
                                <i></i>
                                <h2>Comment prendre rendez-vous ?</h2>
                                <p>Vous pouvez réserver en ligne via notre site web, par téléphone au +237 679 118 000, ou directement au salon. Nous recommandons de réserver à l'avance pour garantir votre créneau horaire.</p>
                            </li>
                            <li>
                                <input type="checkbox" checked>
                                <i></i>
                                <h2>Proposez-vous des forfaits mariages ?</h2>
                                <p>Oui, nous proposons des forfaits complets pour mariages incluant coiffure, maquillage, essais préalables et déplacement possible. Contactez-nous pour un devis personnalisé.</p>
                            </li>
                            <li>
                                <input type="checkbox" checked>
                                <i></i>
                                <h2>Quels produits utilisez-vous ?</h2>
                                <p>Nous travaillons exclusivement avec des produits professionnels de haute qualité, adaptés à tous types de cheveux et de peaux, pour garantir des résultats exceptionnels.</p>
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
            <div class="col-lg-5 mt-lg-0 mt-sm-5 mt-4">
                <div class="banner-form-w3">
                    <form action="#" method="post">
                        <h3 class="title-big">Réserver un <span>rendez-vous</span></h3>
                        <p class="mt-3">Remplissez le formulaire pour prendre rendez-vous
                             avec nos experts beauté.</p>
                        <div class="form-style-w3ls mt-4">
                            <input placeholder="Votre nom" name="name" type="text" required="">
                            <input placeholder="Votre email" name="email" type="email" required="">
                            <input placeholder="Numéro de téléphone" name="phone" type="text" required="">
                            <button class="btn btn-style btn-red w-100">Réserver</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="w3l-bg-image">
    <div class="bg-mask py-5">
        <div class="container py-lg-5 py-sm-4 py-2">
            <div class="text-align text-center py-lg-4 py-md-3">
                <h3 class="title-big">Des services de beauté exceptionnels pour révéler votre éclat naturel.</h3> 
                <p class="mt-4">Nous sommes passionnés par la beauté et dédiés à votre satisfaction.</p>
                <a class="btn btn-style btn-secondary mt-sm-5 mt-4 mr-2" href="#learn">En savoir plus</a>
                <a class="btn btn-style btn-red mt-sm-5 mt-4" href="contact.html">Réservez maintenant</a>
            </div>
        </div>
    </div>
</div>

<section class="feature-style-one py-5">
    <div class="container py-lg-4 py-md-3">
        <div class="row px-2">
            <div class="col-lg-4 col-md-6 px-2 mt-md-0 mt-4">
                <div class="single-feature-style-one primary">
                    <div class="icon-box"> 
                        <span class="fa fa-shield"></span>
                    </div>
                    <div class="text-box">
                        <h3>Hygiène irréprochable</h3>
                        <p>Protocoles sanitaires stricts et matériel stérilisé pour votre sécurité.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 px-2">
                <div class="single-feature-style-one">
                    <div class="icon-box"> 
                        <span class="fa fa-star"></span>
                    </div>
                    <div class="text-box">
                        <h3>Expertise reconnue</h3>
                        <p>Une équipe de professionnels qualifiés et passionnés à votre service.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 px-2 mt-lg-0 mt-4">
                <div class="single-feature-style-two primary">
                    <div class="icon-box"> 
                        <span class="fa fa-clock-o"></span>
                    </div>
                    <div class="text-box">
                        <h3>Horaires flexibles</h3>
                        <p>Ouvert 7j/7 pour s'adapter à votre emploi du temps.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- footer -->
<section class="w3l-footer">
  <div class="w3l-footer-16-main py-5">
    <div class="container pt-lg-4">
      <div class="row">
        <div class="col-lg-7 column">
          <div class="row">
            <div class="col-sm-4 col-6 column">
                <h3>Nos Services</h3>
                <ul class="footer-gd-16">
                  <li><a href="#url">Coiffure</a></li>
                  <li><a href="#url">Soins visage</a></li>
                  <li><a href="#url">Manucure & Pédicure</a></li>
                  <li><a href="about.html">Maquillage professionnel</a></li>
                  <li><a href="#url">Notre équipe</a></li>
                </ul>
              </div>
              <div class="col-sm-4 col-6 column mt-sm-0 mt-4">
                <h3>Liens utiles</h3>
                <ul class="footer-gd-16">
                  <li><a href="#url">Épilation</a></li>
                  <li><a href="#url">Soins capillaires</a></li>
                  <li><a href="#url">Massage relaxant</a></li>
                  <li><a href="#url">Nail Art</a></li>
                  <li><a href="#url">Forfaits mariages</a></li>
                </ul>
              </div>
              <div class="col-sm-4 col-6 column">
                <h3>Appelez-nous</h3>
                <ul class="footer-gd-16">
                    <li><p>Réservations : 
                      <br><a href="tel:+237-679-118-000" id="numero">+237 679 118 000</a></p> 
                        <a href="tel:+237-676-614-813">+237 676 614 813</a>
                    </li>
                    <li><p>Renseignements : 
                      <br><a href="tel:+237-696-526-777">+237 696 526 777</a></p> 
                        <a href="tel:+237-699-920-776">+237 699 920 776</a>
                    </li>
                  </ul>
              </div>
          </div>
        </div>
        <div class="col-lg-5 col-md-12 column pl-lg-5 column4 mt-lg-0 mt-5">
          <h3>Newsletter</h3>
          <div class="end-column">
            <h4>Recevez nos offres spéciales et nouveautés.</h4>
            <form action="#" class="subscribe" method="post">
              <input type="email" name="email" placeholder="Votre adresse email" required="">
              <button type="submit"><span class="fa fa-paper-plane"></span></button>
            </form>
            <p>Inscrivez-vous pour recevoir nos promotions et conseils beauté.</p>
          </div>
        </div>
      </div>
      <div class="d-flex below-section justify-content-between align-items-center pt-4 mt-5">
        <div class="columns text-lg-left text-center">
          <p>&copy; 2024 <span id="nom_footer_copy"> Beauty Innova</span>. Tous droits réservés.</p>
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
  <button onclick="topFunction()" id="movetop" title="Go to top">
    <span class="fa fa-angle-up"></span>
  </button>
  
  <script>
        const knowledgeBase = {
            "routine": {
                keywords: ["routine", "soin", "soigner", "entretien", "quotidien"],
                responses: [
                    "Pour une routine beauté efficace, je recommande: 1) Nettoyage matin et soir avec un produit doux, 2) Tonique pour équilibrer le pH, 3) Sérum adapté à vos besoins, 4) Crème hydratante, et 5) Protection solaire le matin. Le soir, ajoutez un démaquillant avant le nettoyage."
                ]
            },
            "peau_seche": {
                keywords: ["peau sèche", "sèche", "déshydratée", "tiraillement"],
                responses: [
                    "Pour une peau sèche, privilégiez: des nettoyants crémeux sans savon, des sérums à l'acide hyaluronique, des crèmes riches en céramides et beurre de karité. Évitez l'eau trop chaude et n'oubliez pas le SPF. Un masque hydratant 2 fois par semaine fera des merveilles!"
                ]
            },
            "peau_grasse": {
                keywords: ["peau grasse", "grasse", "brillance", "sébum", "pores"],
                responses: [
                    "Pour une peau grasse: nettoyez avec un gel purifiant, utilisez un tonique à l'acide salicylique, appliquez un sérum à la niacinamide, et hydratez avec une crème légère non-comédogène. Les masques à l'argile 1-2 fois par semaine sont excellents!"
                ]
            },
            "acne": {
                keywords: ["acné", "bouton", "imperfection", "point noir"],
                responses: [
                    "Pour l'acné, je conseille: un nettoyage doux 2x/jour, des actifs comme l'acide salicylique ou le peroxyde de benzoyle, et une hydratation légère. Évitez de toucher votre visage, changez votre taie d'oreiller régulièrement. Si l'acné persiste, consultez un dermatologue."
                ]
            },
            "maquillage": {
                keywords: ["maquillage", "make-up", "fond de teint", "mascara", "fard"],
                responses: [
                    "Pour un maquillage réussi: commencez par une bonne base hydratante et primer, choisissez un fond de teint adapté à votre carnation, fixez avec une poudre légère. Pour les yeux, les neutres sont polyvalents. N'oubliez pas le mascara et une touche de blush pour la bonne mine!"
                ]
            },
            "rouge_levres": {
                keywords: ["rouge à lèvres", "rouge", "lèvres", "lipstick", "bouche"],
                responses: [
                    "Choisir son rouge à lèvres: pour un teint clair, privilégiez les roses et coraux; teint moyen, les rouges et mauves; teint foncé, les bordeaux et prunes. Les finitions mates durent plus longtemps, les satinées sont plus confortables. Exfoliez vos lèvres avant application!"
                ]
            },
            "cheveux": {
                keywords: ["cheveux", "cheveu", "capillaire", "coiffure", "shampoing"],
                responses: [
                    "Pour des cheveux sains: adaptez votre routine à votre type (secs, gras, mixtes). Lavez 2-3x/semaine maximum, utilisez un après-shampoing, et faites un masque hebdomadaire. Limitez la chaleur des appareils chauffants et coupez les pointes tous les 3 mois."
                ]
            },
            "tendances": {
                keywords: ["tendance", "mode", "style", "fashion", "look"],
                responses: [
                    "Les tendances actuelles incluent: le style minimaliste chic, les couleurs terreuses, le retour des années 90-2000, les silhouettes oversize, et les pièces vintage. Le plus important? Adaptez les tendances à votre personnalité pour créer un style qui vous ressemble!"
                ]
            },
            "vetements": {
                keywords: ["vêtement", "habiller", "tenue", "outfit", "porter"],
                responses: [
                    "Pour bien s'habiller: connaissez votre morphologie, investissez dans des basiques de qualité (jean parfait, chemise blanche, blazer), jouez avec les accessoires. Les couleurs près du visage doivent vous mettre en valeur. La confiance est votre meilleur accessoire!"
                ]
            },
            "couleurs": {
                keywords: ["couleur", "colorimétrie", "teint", "carnation"],
                responses: [
                    "Pour choisir vos couleurs: observez vos veines (bleues = tons froids, vertes = tons chauds). Tons froids: privilégiez bleu, rose, violet. Tons chauds: orange, jaune, rouge-orangé. Testez près du visage pour voir ce qui illumine votre teint!"
                ]
            },
            "parfum": {
                keywords: ["parfum", "fragrance", "senteur", "odeur"],
                responses: [
                    "Choisir un parfum: testez sur votre peau (pas sur une mouillette), attendez 30 min pour sentir les notes de cœur. Appliquez sur les points de pulsation. Les notes florales sont féminines, les boisées sophistiquées, les fraîches dynamiques. Changez selon les saisons!"
                ]
            },
            "ongles": {
                keywords: ["ongle", "manucure", "vernis", "nail"],
                responses: [
                    "Pour de beaux ongles: gardez-les hydratés avec de l'huile de cuticules, limez dans une seule direction, utilisez une base coat avant le vernis. Les nus et rouges classiques sont intemporels. Une manucure dure plus longtemps si vous portez des gants pour les tâches ménagères!"
                ]
            }
        };

        function addMessage(text, isUser) {
            const chatContainer = document.getElementById('chatContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = text;
            
            messageDiv.appendChild(contentDiv);
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function showTyping() {
            const chatContainer = document.getElementById('chatContainer');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message ai';
            typingDiv.id = 'typing';
            typingDiv.innerHTML = '<div class="message-content typing"><span></span><span></span><span></span></div>';
            chatContainer.appendChild(typingDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function removeTyping() {
            const typing = document.getElementById('typing');
            if (typing) typing.remove();
        }

        function getAIResponse(userMessage) {
            const lowerMessage = userMessage.toLowerCase();
            
            for (let category in knowledgeBase) {
                const data = knowledgeBase[category];
                for (let keyword of data.keywords) {
                    if (lowerMessage.includes(keyword)) {
                        return data.responses[Math.floor(Math.random() * data.responses.length)];
                    }
                }
            }
            
            const genericResponses = [
                "C'est une excellente question! Pour des conseils personnalisés sur ce sujet, je vous recommande de consulter un professionnel. Je peux vous aider avec des questions générales sur la beauté, la mode, les soins de la peau ou le maquillage.",
                "Je suis spécialisée en beauté et mode. Pourriez-vous reformuler votre question en lien avec ces thématiques? Par exemple: soins de la peau, maquillage, coiffure, style vestimentaire...",
                "Pour mieux vous aider, pourriez-vous me donner plus de détails sur votre question beauté ou mode? Je suis là pour vous conseiller sur le maquillage, les soins, la coiffure et le style!"
            ];
            
            return genericResponses[Math.floor(Math.random() * genericResponses.length)];
        }

        function sendMessage() {
            const input = document.getElementById('userInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            addMessage(message, true);
            input.value = '';
            
            showTyping();
            
            setTimeout(() => {
                removeTyping();
                const response = getAIResponse(message);
                addMessage(response, false);
            }, 1000 + Math.random() * 1000);
        }

        function sendSuggestion(text) {
            document.getElementById('userInput').value = text;
            sendMessage();
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }
    // CREATION DU PANIER
    

async function chargerProduits() {
  const conteneur = document.querySelector('#products-content .row');
  
  if (!conteneur) {
    console.error("Conteneur #products-content .row non trouvé");
    return;
  }

  conteneur.innerHTML = `
    <div style="text-align:center; padding:20px; width:100%;">
      <i class="fas fa-spinner fa-spin" style="font-size:32px; color:#666;"></i>
      <p style="margin-top:10px;">Chargement des produits...</p>
    </div>`;

  try {
    //  Lecture depuis localStorage au lieu de window.storage
    let produits = JSON.parse(localStorage.getItem('produits')) || [];

    conteneur.innerHTML = '';

    if (produits.length === 0) {
      conteneur.innerHTML = `
        <div style="text-align:center; padding:40px; color:#666; width:100%;">
          <i class="fas fa-box-open" style="font-size:48px; margin-bottom:15px; opacity:0.5;"></i>
          <p style="font-size:18px; margin:0;">Aucun produit disponible pour le moment.</p>
          <p style="font-size:14px; margin-top:10px; opacity:0.7;">Les produits ajoutés apparaîtront ici.</p>
        </div>`;
      return;
    }

    // On affiche les plus récents en premier
    produits.reverse().forEach((item, index) => {
      const div = document.createElement('div');
      div.className = 'col-6 col-lg-4 col-md-4 col-sm-6 mt-sm-0 mt-5';
      div.innerHTML = `
        <div class="service-card">
          <div class="image-container">
            <img src="${item.image}" class="service-image" alt="${item.nom}" style="object-fit:cover; width:100%; height:200px;">
            <div class="image-overlay"></div>
            <h3 class="service-title" style="color:white;">${item.nom}</h3>
            ${index < 3 ? '<span class="badge-popular">Populaire</span>' : ''}
          </div>
<p style="font-size:14px; color:#444; line-height:1.5; margin:8px 0; text-align:justify;">
  ${item.description}
</p>
          <div class="card-body">
            <div class="service-info">
              <div class="info-item">
                <i class="fas fa-tag info-icon"></i>
                <span class="info-label">Prix</span>
                <span class="info-value">${item.prix} FCFA</span>
              </div>
              <div class="info-item">
                <i class="fas fa-map-marker-alt info-icon"></i>
                <span class="info-label">Localisation</span>
                <span class="info-value">${item.localisation}</span>
              </div>
            </div>
            <div class="action-buttons">
              <button class="btn-service btn-primary" onclick="acheter('${item.id}', '${item.whatsapp}', '${item.nom}','${item.image}')">
                <i class="fas fa-shopping-cart"></i> Acheter
              </button>
              <button class="btn-service btn-secondary-custom" onclick="reserver('${item.id}', '${item.whatsapp}', '${item.nom},${item.prix},'${item.image}')">
                <i class="fas fa-calendar-check"></i> Réserver
              </button>
            </div>
          </div>
        </div>`;
      conteneur.appendChild(div);
    });

  } catch (error) {
    console.error("❌ Erreur lors du chargement des produits:", error);
    conteneur.innerHTML = `
      <div style="text-align:center; padding:40px; color:#d9534f; width:100%;">
        <i class="fas fa-exclamation-triangle" style="font-size:48px; margin-bottom:15px;"></i>
        <p style="font-size:18px; margin:0;">Erreur lors du chargement des produits.</p>
        <p style="font-size:14px; margin-top:10px;">Veuillez rafraîchir la page.</p>
        <button onclick="chargerProduits()" style="margin-top:15px; padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer;">
          <i class="fas fa-sync-alt"></i> Réessayer
        </button>
      </div>`;
  }
}

// Charger automatiquement
window.addEventListener('DOMContentLoaded', chargerProduits);
document.addEventListener('visibilitychange', () => {
  if (!document.hidden) chargerProduits();
});


// Fonctions pour les boutons d'action
function acheter(productId, whatsapp, nomProduit,prixProduit,imageProduit) {
  if (whatsapp) {
    const numeroWhatsApp = whatsapp.replace(/[^0-9]/g, '');
    const message = encodeURIComponent(`Bonjour, je suis intéressé(e) par le produit : ${nomProduit} qui coute :${prixProduit} lien de l'image:${imageProduit}`);
    const urlWhatsApp = `https://wa.me/${numeroWhatsApp}?text=${message}`;
    window.open(urlWhatsApp, '_blank');
  } else {
    alert(`Pour acheter ce produit, veuillez contacter le vendeur.`);
  }
}

function reserver(productId, whatsapp, nomProduit,prix,image) {
  if (whatsapp) {
    const numeroWhatsApp = whatsapp.replace(/[^0-9]/g, '');
    const message = encodeURIComponent(`Bonjour, je souhaite réserver le produit : ${nomProduit} dont le prix est :${prix} lien de l'image:${image}`);
    const urlWhatsApp = `https://wa.me/${numeroWhatsApp}?text=${message}`;
    window.open(urlWhatsApp, '_blank');
  } else {
    alert(`Pour réserver ce produit, veuillez contacter le vendeur.`);
  }
}

    // Liaison avec localStorage
    window.addEventListener('DOMContentLoaded', () => {
        const nom = localStorage.getItem('nom_footer');
        const whatsapp = localStorage.getItem('whatsapp');
        
        if (nom) {
            document.getElementById('nom_footer').textContent = nom;
            const nomFooterCopy = document.getElementById('nom_footer_copy');
            if (nomFooterCopy) nomFooterCopy.textContent = nom;
        }
        
        if (whatsapp) {
            const numeroElement = document.getElementById('numero');
            if (numeroElement) numeroElement.textContent = whatsapp;
        }
    });

    // Scroll to top functionality
    window.onscroll = function () {
      scrollFunction()
    };

    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("movetop").style.display = "block";
      } else {
        document.getElementById("movetop").style.display = "none";
      }
    }

    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
</section>
<!-- //footer -->

<!-- Template JavaScript -->
<script src="assets/js/jquery-1.9.1.min.js"></script>
<script src="assets/js/theme-change.js"></script>
<script src="assets/js/easyResponsiveTabs.js"></script>

<script type="text/javascript">
 $(document).ready(function () {
   $('#parentHorizontalTab').easyResponsiveTabs({
     type: 'default',
     width: 'auto',
     fit: true,
     tabidentify: 'hor_1',
     activate: function (event) {
       var $tab = $(this);
       var $info = $('#nested-tabInfo');
       var $name = $('span', $info);
       $name.text($tab.text());
       $info.show();
     }
   });
 });
</script>

<script src="assets/js/owl.carousel.js"></script>
<script>
  $(document).ready(function () {
    $('.owl-one').owlCarousel({
      loop: true,
      margin: 0,
      nav: false,
      responsiveClass: true,
      autoplay: true,
      autoplayTimeout: 5000,
      autoplaySpeed: 1000,
      autoplayHoverPause: false,
      responsive: {
        0: { items: 1, nav: false },
        480: { items: 1, nav: false },
        667: { items: 1, nav: true },
        1000: { items: 1, nav: true }
      }
    })
  })
</script>

<script>
  $(document).ready(function () {
    $('.owl-three').owlCarousel({
      margin: 20,
      nav: false,
      dots: false,
      responsiveClass: true,
      autoplay: true,
      autoplayTimeout: 5000,
      autoplaySpeed: 1000,
      autoplayHoverPause: false,
      responsive: {
        0: { items: 2 },
        480: { items: 2 },
        767: { items: 3 },
        992: { items: 4 },
        1280: { items: 5 }
      }
    })
  })
</script>

<script>
 $(document).ready(function () {
   $('.owl-testimonial').owlCarousel({
     loop: true,
     margin: 0,
     nav: true,
     responsiveClass: true,
     autoplay: false,
     autoplayTimeout: 5000,
     autoplaySpeed: 1000,
     autoplayHoverPause: false,
     responsive: {
       0: { items: 1, nav: false },
       480: { items: 1, nav: false },
       667: { items: 1, nav: true },
       1000: { items: 1, nav: true }
     }
   })
 })
</script>

<script src="assets/js/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({
      type: 'inline',
      fixedContentPos: false,
      fixedBgPos: true,
      overflowY: 'auto',
      closeBtnInside: true,
      preloader: false,
      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in'
    });

    $('.popup-with-move-anim').magnificPopup({
      type: 'inline',
      fixedContentPos: false,
      fixedBgPos: true,
      overflowY: 'auto',
      closeBtnInside: true,
      preloader: false,
      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-slide-bottom'
    });
  });
</script>

<script>
  $(function () {
    $('.navbar-toggler').click(function () {
      $('body').toggleClass('noscroll');
    })
  });
</script>

<script>
  $(window).on("scroll", function () {
    var scroll = $(window).scrollTop();

    if (scroll >= 80) {
      $("#site-header").addClass("nav-fixed");
    } else {
      $("#site-header").removeClass("nav-fixed");
    }
  });

  $(".navbar-toggler").on("click", function () {
    $("header").toggleClass("active");
  });
  
  $(document).on("ready", function () {
    if ($(window).width() > 991) {
      $("header").removeClass("active");
    }
    $(window).on("resize", function () {
      if ($(window).width() > 991) {
        $("header").removeClass("active");
      }
    });
  });
</script>

<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>