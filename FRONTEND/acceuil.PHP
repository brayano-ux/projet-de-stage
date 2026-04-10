<?php
session_start();
require_once __DIR__ . '/config/index.php';

$isLoggedIn = isset($_SESSION['user_id']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
 <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script></head>
    <link rel="stylesheet" href="src/css/acceuil.css">
    <style>

    </style>
</head>
<div class="container">
<header>
    <div class="titre">
        <div class="autre">
             <div class="menu-hamburger" onclick="hamburger()">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <blockquote  style="color: blue" class="entete">
                <i class="fas fa-envelope" ></i> 
                <span id="entete" data-lang-en="Creator Market">Creator market</span>
            </blockquote>
            <div class="ceux" id="ceux">
                <button id="acceuil" onclick="window.location.href='acceuil.php'" class="acceuil">
                    <i class="fas fa-envelope"></i> 
                    <span data-lang-en="Home">Acceuil</span>
                </button>
                <button onclick="window.location.href='marcher.php'" class="marcher">
                    <i class="fas fa-search"></i> 
                    <span data-lang-en="Market Place">Market Place</span>
                </button>
                <button onclick="window.location.href='templates.html'" class="marcher">
                    <i class="fas fa-crown"></i> 
                    <span data-lang-en="Create" onclick="allerMarketplace()">Créer</span>
                <button class="marcher">
                    <i class="fas fa-user"></i> 
                    <span data-lang-en="Dashboard" onclick="allerdashboard()">Tableau de bord</span>
                </button>
                <?php
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>
<script>
function allerdashboard() {
    let userId = <?php echo json_encode($user_id); ?>;
    if (!userId) {
        Swal.fire({
            icon: 'warning',
            title: 'Connexion requise',
            text: 'Vous devez être connecté pour accéder au dashboard.',
            confirmButtonText: 'Se connecter',
            confirmButtonColor: 'rgb(255, 84, 4)',
            showCancelButton: true,
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'INSCRIPTION.php';
            }
        });
    } else {
        window.location.href = 'dashboard.php';
    }
}
document.querySelector('.marcher').addEventListener('click',function(){
    allerdashboard();
})
</script>    
            </div>

            <div id="categorie" class="categorie">
                <button id="langue" class="notification">
                    <i class="fas fa-globe"></i> 
                    <span data-lang-en="EN">FR</span>
                </button>
                <button id="lemode" onclick="lumiositer()" class="mode" title="Mode sombre">
                    <i class="fas fa-moon"></i>
                </button>
                <div  id="ceulala">
                <?php if (!$isLoggedIn): ?>
                <button id="inscrire" onclick="window.location.href='inscription.html'"><i class="fas fa-sign-in-alt"></i> Inscription</button>
                <?php else: ?>
                <button id="deconnecter" onclick="logout()" title="Déconnexion"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                <?php endif; ?>
                </div>
                
                <script>
                function logout() {
                    Swal.fire({
                        icon: 'question',
                        title: 'Confirmation',
                        text: 'Êtes-vous sûr de vouloir vous déconnecter ?',
                        confirmButtonText: 'Oui, déconnecter',
                        confirmButtonColor: 'rgb(255, 84, 4)',
                        showCancelButton: true,
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                }
                </script>
            </div>
            
        </div>
    </div>
</header>

<div class="milieu">
    <div class="tect" style="position:relative;top: 150px;">
        <div class="text"> 
            <i class="fas fa-crown"></i> 
            <span data-lang-en="#1 African Creators Market">#1 Market place Createurs afrique</span>
        </div>
        <div class="essai">
        <h1 class="tit" style="padding: 10px;">
            <span data-lang-en="Create your Mini-Shop in 10 minutes">
                Crer votre <font color="rgb(255, 84, 4)"> Mini-Boutique <br></font>en 10 minutes
            </span>
        </h1>
        <p data-lang-en="The smart platform that allows any African creator, merchant or entrepreneur to launch their business online. QR Code, WhatsApp, Mobile Money - everything is included!">
            La plateforme intelligente qui permet 
           <br> à tout créateur, commerçant <br> ou entrepreneur africain de lancer son business en ligne. QR Code, WhatsApp, Mobile Money - tout est <br> inclus!
        </p>
        </div>
        <div class="ligne">
            <button id="commence" onclick="window.location.href='templates.html'" data-lang-en="Start for free">
                Commencer gratuitement <i class="fas fa-arrow-right"></i>
            </button>
            <a id="span" href="#" data-lang-en="Watch a Demo (2 min)">
                <i class="fas fa-video"></i> Voir une Demo (2 min)
            </a>
        </div>
        <br>
      <div class="client">
    <div class="crea">
        <i class="fa-solid fa-pen-nib"></i> <h2 class="h2" data-lang-en="10K +">10K +</h2> 
        <p class="p" data-lang-en="Active Creators">Créateurs actifs</p>
    </div>
    <div class="comm">
        <i class="fa-solid fa-box-open"></i> <h2 class="h2" data-lang-en="5000K +">5000K +</h2> 
        <p class="p" data-lang-en="Orders Processed">Commandes traitées</p>
    </div>
    <div class="fcfa">
        <i class="fa-solid fa-money-bill-wave"></i> <h2 class="h2" data-lang-en="50M +">50M +</h2> 
        <p class="p" data-lang-en="FCFA Generated">FCFA générés</p>
    </div>
    <div class="sat">
        <i class="fa-solid fa-users"></i>
        <h2 class="h2" data-lang-en="95%">95%</h2>
        <p class="p" data-lang-en="Customer Satisfaction">Satisfaction client</p>
    </div>
</div>
    </div>
</div>
    <section class="amelioration">
        <div class="separe">
            <div class="uncoter">
                <img src="https://img.freepik.com/photos-gratuite/influenceur-filmant-vlog-mode_482257-81586.jpg" alt="Influenceur filmant un vlog mode">
            </div>
            <div class="autrecoter">
                <h2>Rejoignez plus de 500 créateurs</h2>
                <p>Ils nous ont fait confiance.<br>
                À partir d'aujourd'hui, créez votre boutique en <strong>5 secondes</strong> !</p>
                <a href="#" class="cta">Créer ma boutique</a>
            </div>
        </div>
    </section>
<section>
    <div>
        <h2 class="creator" data-lang-en="Why Choose Creator Market?">Pourqoui Choisir Creator Market ?</h2>
        <p class="solution" data-lang-en="A complete solution designed for the African ecosystem with all the tools you need to succeed.">
            Une solution complète pensée pour l'écosystème africain avec tous les outils dont vous <br> avez besoin pour réussir.
        </p>
    </div>
</section>

<div class="creator">
    <div class="precreator">
        <h1><i class="fa-solid fa-qrcode"></i></h1>
        <h5 class="h5" data-lang-en="Instant QR Code">QR Code Instantané</h5>
        <p data-lang-en="Automatic generation of QR code and link for easy sharing">
            Génération automatique de QR code  et de lien pour <br> partage facile
        </p>
    </div>
    <div class="precreator">
        <h1><i class="fa-brands fa-whatsapp"></i></h1>
        <h5 class="h5" data-lang-en="Integrated WhatsApp">WhatsApp Intégré</h5>
        <p data-lang-en="Direct communication with your customers via WhatsApp">
            Communication directe avec vos clients via <br> WhatsApp
        </p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-mobile-screen-button"></i></h1>
        <h5 class="h5" data-lang-en="Mobile Payments">Paiements Mobiles</h5>
        <p data-lang-en="Mobile Money, Orange Money, MTN MoMo integrated">
            Mobile Money, Orange Money, MTN MoMo <br> intégrés
        </p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-mobile-screen-button"></i></h1>
        <h5 class="h5" data-lang-en="Mobile First">Mobile First</h5>
        <p data-lang-en="Optimized for Africa and mobile usage">Optimisé pour l'Afrique et l'usage mobile</p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-gear"></i></h1>
        <h5 class="h5" data-lang-en="Setup in 10 minutes">Setup en 10min</h5>
        <p data-lang-en="Create your store in just a few minutes">Créez votre boutique en quelques minutes <br> seulement</p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-chart-line"></i></h1>
        <h5 class="h5" data-lang-en="Advanced Analytics">Analytics Avancées</h5>
        <p data-lang-en="Get insights and track your store performance">
            Obtenez des insights et suivez les performances <br> de votre boutique
        </p>
    </div>
</div>
<section>
    <div class="ia" id="ia">
        <i  id="robot" class="fas fa-robot"></i>
    </div>
    <!-- zone de question -->
    <div id="div"></div>
</section>

<div class="steps" style="margin: 20px;">
    <div class="ma">
        <h4 style="text-align: center; font-size: 30px;" data-lang-en="How it works?">Comment ça marche?</h4>
        <p data-lang-en="3 simple steps to launch your business">3 étapes simples pour lancer votre business</p>
    </div>
    <div class="co-template">
        <h4 class="i1">1</h4>
        <h2 data-lang-en="Choose a template">Choisissez un template</h2>  
        <p data-lang-en="Restaurant, beauty salon, shop, services... More than 20 templates available.">
            Restaurant, salon de beauté, boutique, services...<br>Plus de 20 templates disponibles.
        </p>
    </div>
    <div class="co-template">
        <h4 class="i1">2</h4>
        <h2 data-lang-en="Customize">Personnalisez</h2>  
        <p data-lang-en="Add your images, colors, and texts in just a few clicks.">
            Ajoutez vos images, vos couleurs et vos textes<br>en quelques clics.
        </p>
    </div>
    <div class="co-template">
        <h4 class="i1">3</h4>
        <h2 data-lang-en="Publish">Publiez</h2>  
        <p data-lang-en="Put your shop online and instantly share your link or QR Code.">
            Mettez en ligne votre boutique et partagez<br>instantanément votre lien ou QR Code.
        </p>
    </div>
    <div class="co-template">
        <h4 class="i1">4</h4>
        <h2 data-lang-en="Collect Payments">Encaissez</h2>  
        <p data-lang-en="Receive your payments via Mobile Money, WhatsApp or online.">
            Recevez vos paiements via Mobile Money,<br>WhatsApp ou directement en ligne.
        </p>
    </div>
</div>
<section class="frequente">
    <div class="tous_contenu">
         <div class="title">
                <h2 class="question">Questions fréquentes</h2>
                <p class="reponse">Retrouvez les réponses aux questions les plus courantes</p>
            </div>
       <div class="faq">
                    <div class="faq-question">
                        <span>Creator Market est-il gratuit ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>Oui, pour débuter Creator Market est complètement gratuit et mis à la disposition de tous. Nous proposons également des formules premium avec des fonctionnalités avancées pour les entreprises en croissance.</p>
                    </div>
      </div>
        <div class="faq">
                    <div class="faq-question">
                        <span>Quels modes de paiement acceptez-vous ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>Nous acceptons tous les principaux modes de paiement utilisés en Afrique : Mobile Money (Orange Money, MTN MoMo), les cartes bancaires, et les virements. Nous travaillons également à intégrer de nouvelles solutions de paiement locales.</p>
                    </div>
                </div>
                 <div class="faq">
                    <div class="faq-question">
                        <span>Puis-je personnaliser ma boutique ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>Absolument ! Vous pouvez personnaliser votre boutique avec vos couleurs, images, textes et logo en quelques clics. Notre interface intuitive vous guide à chaque étape pour créer une boutique qui reflète votre identité de marque.</p>
                    </div>
                </div>
                 <div class="faq">
                    <div class="faq-question">
                        <span>Comment fonctionne l'intégration WhatsApp ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>L'intégration WhatsApp permet à vos clients de vous contacter directement depuis votre boutique en ligne. Ils peuvent poser des questions, passer des commandes ou demander des informations supplémentaires sans quitter votre site.</p>
                    </div>
                </div>
                
    </div>

<footer class="avant_footer">

</footer>

<footer class="pied-page">
<i class="fas fa-store" id="store"></i> 
<h2>Prêt à Vendre en Ligne ?</h2>
<p>Créez votre boutique gratuitement et commencez à vendre vos <br> produits via WhatsApp. Aucun site web nécessaire, aucun frais <br> cachés.</p>
<div class="conteneur_boutton"><button style="color: rgba(0, 0, 255, 0.76);" >
     <i class="fas fa-plus"></i>   Créer Ma Boutique</button>
     <button><i class="fas fa-info-circle" style="gap: 15px;"></i>     En Savoir Plus</button>
    </div>
    <div class="ensemble">
    <div class="pub">
        <i class="fas fa-bolt"></i>  
        <h3>Configuration Rapide</h3>
        <span>Créez votre boutique en moins de 5 <br> minutes</span>
    </div>
    <div class="pub">
        <i class="fas fa-shield-alt"></i>  
        <h3>Sécurité Garantie</h3>
        <span>Vos données et celles de vos clients sont <br> protégées</span>
    </div>
    <div class="pub">
<i class="fas fa-comments"></i> 
<h3>Vente via WhatsApp</h3>
<span>Communiquez directement avec vos <br> clients</span>
    </div>
    </div>
    <!-- <div class="conteneur-footer">
        <div class="contenu-footer">
            <div class="section-footer">
                <h3 class="titre-section" data-lang-en="Contact">Contact</h3>
                <div class="info-contact">
                    <i class="fas fa-map-marker-alt"></i>
                    <span data-lang-en="Nyalla Kambo, Douala">Nyalla Kambo, Douala</span>
                </div>
                <div class="info-contact">
                    <i class="fas fa-phone"></i>
                    <span data-lang-en="+237 657 30 06 44">+237 657 30 06 44</span>
                </div>
                <div class="info-contact">
                    <i class="fas fa-envelope"></i>
                    <span data-lang-en="ulrichbrayan492@gmail.com">ulrichbrayan492@gmail.com</span>
                </div>

                <div class="reseaux-sociaux">
                    <a href="#" class="bouton-reseau" id="facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="bouton-reseau" id="twitter">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="bouton-reseau" id="instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>

                <div class="formulaire-newsletter">
                    <input type="email" class="champ-email" placeholder="Votre email pour la newsletter" data-lang-en="Your email for the newsletter">
                    <button class="bouton-newsletter" onclick="inscrireNewsletter()" data-lang-en="Subscribe">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>
    <div class="bas-footer">
        <div class="conteneur-footer">
            <p class="texte-copyright" data-lang-en="© 2025 Creator Market. All rights reserved.">
                © 2025 Creator Market. Tous droits réservés.
            </p>
            <div class="liens-legaux">
                <a href="#" class="lien-legal" id="confidentialite" data-lang-en="Privacy">Confidentialité</a>
                <a href="#" class="lien-legal" id="conditions" data-lang-en="Terms of Use">Conditions d'utilisation</a>
            </div>
        </div>
    </div> -->
</footer>
</div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/js/acceuil.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>