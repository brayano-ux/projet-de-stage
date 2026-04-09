<?php
session_start();
require_once __DIR__ . '/config/index.php';

$isLoggedIn = isset($_SESSION['user_id']); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Market - La meilleure marketplace pour créateurs</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Firebase -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    
    <!-- CSS -->
    <link rel="stylesheet" href="src/css/acceuil-improved.css">
    
    <style>
        /* Suppression des styles inline conflictuels */
        .conteneur-footer {
            max-width: 1200px;
            margin: 0 auto; 
            padding: 0 20px;
        }

        .contenu-footer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .section-footer {
            text-align: left;
        }

        .titre-section {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .liste-liens {
            list-style: none;
        }

        .liste-liens li {
            margin-bottom: 12px;
        }

        .lien-footer {
            color: #bdc3c7;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .lien-footer:hover {
            color: var(--primary);
        }

        .info-contact {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 16px;
            color: #bdc3c7;
        }

        .info-contact i {
            margin-right: 10px;
            color: var(--primary);
            width: 20px;
        }

        .reseaux-sociaux {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .bouton-reseau {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background-color: #34495e;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .bouton-reseau:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }

        .bas-footer {
            border-top: 1px solid #34495e;
            padding: 25px 0;
            text-align: center;
            background-color: #0a1a2a;
        }

        .texte-copyright {
            color: #95a5a6;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .liens-legaux {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .lien-legal {
            color: #95a5a6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .lien-legal:hover {
            color: var(--primary);
        }

        /* Responsive fixes */
        @media (max-width: 768px) {
            .conteneur-footer {
                padding: 0 15px;
            }

            .titre-section {
                font-size: 18px;
            }

            .lien-footer {
                font-size: 15px;
            }

            .contenu-footer {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .section-footer {
                text-align: center;
            }

            .reseaux-sociaux {
                justify-content: center;
            }

            .liens-legaux {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .titre-section {
                font-size: 16px;
            }

            .lien-footer {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="titre">
        <div class="entete">
            <i class="fas fa-store"></i>
            Creator Market
        </div>
        
        <div class="autre">
            <div class="ceux">
                <button><i class="fas fa-home"></i> Accueil</button>
                <button><i class="fas fa-shopping-bag"></i> Boutiques</button>
                <button><i class="fas fa-info-circle"></i> À propos</button>
            </div>
            
            <div class="categorie">
                <button class="mode" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <?php if ($isLoggedIn): ?>
                    <button><i class="fas fa-user"></i> Profil</button>
                    <button><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                <?php else: ?>
                    <button><i class="fas fa-sign-in-alt"></i> Connexion</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="milieu">
        <div class="text">
            <i class="fas fa-crown"></i>
            N°1 Marketplace pour Créateurs
        </div>
        
        <h1 class="tit">
            Votre <font>Boutique</font><br>
            en Ligne
        </h1>
        
        <div class="essai">
            <p>
                La plateforme idéale pour les créateurs qui souhaitent vendre leurs produits en ligne. 
                Facile, rapide et professionnelle.
            </p>
        </div>
        
        <div class="ligne">
            <a href="#" id="commence">
                <i class="fas fa-rocket"></i>
                Commencer
            </a>
            <a href="#" id="span">
                <i class="fas fa-play-circle"></i>
                Voir la démo
            </a>
        </div>
        
        <!-- Stats -->
        <div class="client">
            <div class="crea">
                <i class="fas fa-store"></i>
                <div class="h2">500+</div>
                <div class="p">Boutiques Actives</div>
            </div>
            <div class="comm">
                <i class="fas fa-shopping-cart"></i>
                <div class="h2">10K+</div>
                <div class="p">Produits Vendus</div>
            </div>
            <div class="fcfa">
                <i class="fas fa-users"></i>
                <div class="h2">5K+</div>
                <div class="p">Clients Satisfaits</div>
            </div>
            <div class="sat">
                <i class="fas fa-star"></i>
                <div class="h2">4.8</div>
                <div class="p">Note Moyenne</div>
            </div>
        </div>
    </section>

    <!-- Section Amélioration -->
    <section class="amelioration">
        <div class="separe">
            <div class="uncoter">
                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Interface Creator Market">
            </div>
            <div>
                <h2 class="h2">Une Interface Moderne</h2>
                <p>
                    Notre plateforme offre une expérience utilisateur exceptionnelle avec un design moderne 
                    et intuitif. Gérez votre boutique facilement avec nos outils professionnels.
                </p>
                <div class="ligne">
                    <a href="#" id="commence">
                        <i class="fas fa-arrow-right"></i>
                        Explorer
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="pied-page">
        <div class="conteneur-footer">
            <div class="contenu-footer">
                <div class="section-footer">
                    <h3 class="titre-section">Creator Market</h3>
                    <p style="color: #bdc3c7; margin-bottom: 20px;">
                        La meilleure marketplace pour les créateurs qui souhaitent développer leur activité en ligne.
                    </p>
                    <div class="reseaux-sociaux">
                        <a href="#" class="bouton-reseau">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bouton-reseau">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bouton-reseau">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bouton-reseau">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div class="section-footer">
                    <h3 class="titre-section">Liens Rapides</h3>
                    <ul class="liste-liens">
                        <li><a href="#" class="lien-footer">Comment ça marche</a></li>
                        <li><a href="#" class="lien-footer">Tarifs</a></li>
                        <li><a href="#" class="lien-footer">Créer une boutique</a></li>
                        <li><a href="#" class="lien-footer">Devenir vendeur</a></li>
                    </ul>
                </div>
                
                <div class="section-footer">
                    <h3 class="titre-section">Support</h3>
                    <ul class="liste-liens">
                        <li><a href="#" class="lien-footer">Centre d'aide</a></li>
                        <li><a href="#" class="lien-footer">Contact</a></li>
                        <li><a href="#" class="lien-footer">FAQ</a></li>
                        <li><a href="#" class="lien-footer">Guide utilisateur</a></li>
                    </ul>
                </div>
                
                <div class="section-footer">
                    <h3 class="titre-section">Contact</h3>
                    <div class="info-contact">
                        <i class="fas fa-envelope"></i>
                        contact@creatormarket.com
                    </div>
                    <div class="info-contact">
                        <i class="fas fa-phone"></i>
                        +237 698 123 456
                    </div>
                    <div class="info-contact">
                        <i class="fas fa-map-marker-alt"></i>
                        Yaoundé, Cameroun
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bas-footer">
            <div class="texte-copyright">
                © 2024 Creator Market. Tous droits réservés.
            </div>
            <div class="liens-legaux">
                <a href="#" class="lien-legal">Mentions légales</a>
                <a href="#" class="lien-legal">Politique de confidentialité</a>
                <a href="#" class="lien-legal">CGU</a>
                <a href="#" class="lien-legal">Cookies</a>
            </div>
        </div>
    </footer>

    <!-- Chatbot IA -->
    <div class="ia">
        <button id="robot" onclick="toggleChat()">
            <i class="fas fa-robot"></i>
        </button>
    </div>

    <!-- Chat Container -->
    <div class="chat-container" id="chatContainer">
        <div class="chat-header">
            <div>
                <h3 style="margin: 0; font-size: 16px;">Assistant Creator Market</h3>
                <p style="margin: 0; font-size: 12px; opacity: 0.8;">Je suis là pour vous aider</p>
            </div>
            <button class="fermer" onclick="toggleChat()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="chat-body" id="chatBody">
            <div class="message-bot">
                Bonjour ! Je suis votre assistant Creator Market. Comment puis-je vous aider aujourd'hui ?
            </div>
        </div>
        
        <div class="input-zone">
            <input type="text" id="question" placeholder="Tapez votre message..." onkeypress="handleKeyPress(event)">
            <button id="envoyer" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        // Toggle Theme
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');
            
            if (html.getAttribute('theme') === 'dark') {
                html.removeAttribute('theme');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('theme', 'dark');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const themeIcon = document.getElementById('theme-icon');
            
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('theme', 'dark');
                themeIcon.className = 'fas fa-sun';
            }
        });

        // Chat functionality
        function toggleChat() {
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.style.display = chatContainer.style.display === 'flex' ? 'none' : 'flex';
        }

        function sendMessage() {
            const input = document.getElementById('question');
            const message = input.value.trim();
            
            if (message === '') return;
            
            const chatBody = document.getElementById('chatBody');
            
            // Add user message
            const userMessage = document.createElement('div');
            userMessage.className = 'message-user';
            userMessage.textContent = message;
            chatBody.appendChild(userMessage);
            
            // Clear input
            input.value = '';
            
            // Simulate bot response
            setTimeout(() => {
                const botMessage = document.createElement('div');
                botMessage.className = 'message-bot';
                botMessage.textContent = 'Merci pour votre message ! Je traite votre demande...';
                chatBody.appendChild(botMessage);
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 1000);
            
            // Scroll to bottom
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease both';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.client > div, .uncoter').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
