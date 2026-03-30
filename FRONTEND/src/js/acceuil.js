    
    function allerMarketplace() {
        if (isLoggedIn) {
            window.location.href = 'marcher.php';
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Connexion requise',
                text: 'Vous devez être connecté pour accéder au Marketplace.',
                confirmButtonText: 'Se connecter',
                confirmButtonColor: 'rgb(255, 84, 4)',
                showCancelButton: true,
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'INSCRIPTION.php';
                }
            });
        }
    }
    //faq
    const faqs = document.querySelectorAll(".faq-question");
faqs.forEach(faq => {
  faq.addEventListener("click", () => {
    const parent = faq.parentElement;
    document.querySelectorAll(".faq").forEach(item => {
      if (item !== parent) {
        item.classList.remove("active");
        item.classList.add("desactive");
      }
    });
    parent.classList.toggle("active");
    parent.classList.toggle("desactive");
  });
});
//function dashboard
function allerdashboard() { 
    if(!$_SESSION['user_id']){
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
        }       
}
        //function ia
  let chatbotId = 0;
  let currentLang = 'fr';
  let isDarkMode = false;
        
        const responses = {
            fr: {
                "bonjour": "Bonjour ! Comment puis-je vous aider aujourd'hui ?",
                "comment ca fonctionne":"Choisissez un template Restaurant, salon de beauté, boutique, services...Plus de 20 templates disponibles.Personnalisez Ajoutez vos images, vos couleurs et vos textesen quelques clics.Publiez Mettez en ligne votre boutique et partagezinstantanément votre lien ou QR Code.Encaissez Recevez vos paiements via Mobile Money,WhatsApp ou directement en ligne.",
                "comment tu vas ":"Bien et toi veuillez m'exuser poser moi de vrai question",
                "ok":"D'accord merci et a ientot",
                "aide moi":"A quel niveau vouriez quon vous aide?",
                "hello": "Bonjour ! Comment puis-je vous aider ?",
                "aide": "Je suis là pour répondre à vos questions sur Creator Market. Que souhaitez-vous savoir ?",
                "contact": "Vous pouvez nous contacter au +237 657 30 06 44 ou par email à ulrichbrayan492@gmail.com",
                "prix": "Creator Market est gratuit pour commencer ! Consultez nos plans ou contactez-nous pour plus d'infos.",
                "service": "Nous offrons la création de mini-boutiques, QR codes, intégration WhatsApp et Mobile Money.",
                "template": "Nous avons plus de 20 templates : restaurants, salons, boutiques, services et bien plus !",
                "paiement": "Mobile Money, Orange Money, MTN MoMo sont tous intégrés dans nos solutions.",
                "merci": "Je vous en prie ! N'hésitez pas si vous avez d'autres questions.",
                "au revoir": "Au revoir ! J'espère avoir pu vous aider. À bientôt !"
            },
            en: {
                "hello": "Hello! How can I help you today?",
                "bonjour": "Hello! How can I help you today?",
                "help": "I'm here to answer your questions about Creator Market. What would you like to know?",
                "contact": "You can contact us at +237 657 30 06 44 or by email at ulrichbrayan492@gmail.com",
                "price": "Creator Market is free to start! Check our plans or contact us for more info.",
                "service": "We offer mini-shop creation, QR codes, WhatsApp and Mobile Money integration.",
                "template": "We have over 20 templates: restaurants, salons, shops, services and much more!",
                "payment": "Mobile Money, Orange Money, MTN MoMo are all integrated in our solutions.",
                "thank": "You're welcome! Don't hesitate if you have other questions.",
                "goodbye": "Goodbye! I hope I could help you. See you soon!"
            }
        };
//question

        function Response(Message) {
            const message = Message.toLowerCase();
            const currentResponses = responses[currentLang];
            
            for (let cle in currentResponses) {
                if (message.includes(cle)) {
                    return currentResponses[cle];
                }
            }
            
            return currentLang === 'fr' 
                ? "Je ne suis pas sûr de comprendre. Veuillez contacter le 657300644. Pouvez-vous reformuler votre question ? Vous pouvez me demander des informations sur nos services, nos tarifs, ou comment nous contacter."
                : "I'm not sure I understand. Can you rephrase your question? You can ask me about our services, prices, or how to contact us.";
        }

        // Gestion du mode sombre
        function lumiositer() {
            isDarkMode = !isDarkMode;
            const body = document.body;
            const mode = document.getElementById('lemode');
            
            if (isDarkMode) {
                body.setAttribute('theme', 'dark');
                mode.innerHTML = '<i class="fas fa-sun"></i>';
                mode.title = currentLang === 'fr' ? 'Mode clair' : 'Light mode';
            } else {
                body.removeAttribute('theme');
                mode.innerHTML = '<i class="fas fa-moon"></i>';
                mode.title = currentLang === 'fr' ? 'Mode sombre' : 'Dark mode';
            }
        }


        document.getElementById('robot').addEventListener('click', function() {
            const zone = document.getElementById('div');
                        if (zone.children.length > 0) {
                zone.innerHTML = ''; 
                return;
            }
            
            chatbotId++;
            const div = document.createElement('div');
            div.className = 'chatbot';
            div.setAttribute('id', 'chatbot-' + chatbotId);
            
            div.innerHTML = `
                <div class="debut">
                    <button class="fermer" onclick="fermerChatbot()">×</button>
                    <h3 data-lang-en="Do you have questions about the site?">Avez-vous des questions concernant le site?</h3>
                </div>
                <div class="milieu">
                    <p data-lang-en="Hello I am your personal assistant. Ask me questions about the site">Bonjour je suis votre<br> assistant personnel<br>
                          Posez-moi des questions sur le site</p>
                    <div class="conversation" id="conversation-${chatbotId}">
                        <!-- Les messages apparaîtront ici -->
                    </div>
                    <div class="input-zone">
                        <input type="text" placeholder="Posez votre question..." id="question-${chatbotId}" data-lang-en="Ask your question...">
                        <button  class="envoie" id="envoyer-${chatbotId}">Envoyer</button>
                    </div>
                </div>
            `;
            
            zone.appendChild(div);
            
            modifierlangue();
            
            const questionInput = document.getElementById(`question-${chatbotId}`);
            const envoyerBtn = document.getElementById(`envoyer-${chatbotId}`);
            const conversation = document.getElementById(`conversation-${chatbotId}`);
            
            function envoyerMessage() {
                const message = questionInput.value.trim();
                if (message) {
                    const userMsg = document.createElement('div');
                    userMsg.className = 'message-user';
                    userMsg.textContent = message;
                    conversation.appendChild(userMsg);
                    setTimeout(() => {
                        const botMsg = document.createElement('div');
                        botMsg.className = 'message-bot';
                        botMsg.textContent = Response(message);
                        conversation.appendChild(botMsg);
                        conversation.scrollTop = conversation.scrollHeight;
                    }, 1000);
                    
                    questionInput.value = '';
                    conversation.scrollTop = conversation.scrollHeight;
                }
            }
            
            envoyerBtn.addEventListener('click', envoyerMessage);
            
            questionInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    envoyerMessage();
                }
            });
                        questionInput.focus();
        });

        function fermerChatbot() {
            document.getElementById('div').innerHTML = '';
        }

        // Gestion des langues
        const btnLangue = document.getElementById('langue');
        function Language() {
            currentLang = currentLang === 'fr' ? 'en' : 'fr';
                 modifierlangue();
        }

        function modifierlangue() {
            const elements = document.querySelectorAll('[data-lang-en]');

            elements.forEach(el => {
                const frText = el.textContent || el.placeholder;
                const enText = el.getAttribute('data-lang-en');
                
                if (!el.getAttribute('data-lang-fr')) {
                    el.setAttribute('data-lang-fr', frText);
                }
                
                if (currentLang === 'en') {
                    if (el.tagName === 'INPUT') {
                        el.placeholder = enText;
                    } else {
                        el.textContent = enText;
                    }
                } else {
                    const originalFr = el.getAttribute('data-lang-fr');
                    if (el.tagName === 'INPUT') {
                        el.placeholder = originalFr;
                    } else {
                        el.textContent = originalFr;
                    }
                }
            });

            btnLangue.innerHTML = `<i class="fas fa-globe"></i> <span>${currentLang.toUpperCase()}</span>`;
            
           
        }

        btnLangue.addEventListener('click', Language);


        function inscrireNewsletter() {
            const email = document.querySelector('.champ-email').value;
            if (email) {
                const message = currentLang === 'fr' 
                    ? 'Merci ! Vous êtes inscrit à notre newsletter avec : ' + email
                    : 'Thank you! You are subscribed to our newsletter with: ' + email;
                alert(message);
                document.querySelector('.champ-email').value = '';
            } else {
                const message = currentLang === 'fr' 
                    ? 'Veuillez saisir votre email'
                    : 'Please enter your email';
                alert(message);
            }
        }

        document.querySelectorAll('.bouton-reseau').forEach(bouton => {
            bouton.addEventListener('click', function(e) {
                e.preventDefault();
                const reseau = this.id;
                const message = currentLang === 'fr' 
                    ? 'Redirection vers ' + reseau
                    : 'Redirecting to ' + reseau;
                alert(message);
            });
        });
 
        function hamburger() {
            const hamburger = document.querySelector('.menu-hamburger');
            const menu = document.querySelector('.ceux');
            hamburger.classList.toggle('ouvert');
            menu.classList.toggle('menu-ouvert');
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadDarkMode();
            Language();
        });

        // Fermer le menu mobile en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            const menu = document.querySelector('.ceux');
            const hamburger = document.querySelector('.menu-hamburger');
            
            if (!menu.contains(e.target) && !hamburger.contains(e.target)) {
                menu.classList.remove('menu-ouvert');
                hamburger.classList.remove('ouvert');
            }
        });
          div=document.getElementById('div');
        document.addEventListener('click',function(e){
            if(!div.contains(e.target))
            div.classList.remove('div');
        });