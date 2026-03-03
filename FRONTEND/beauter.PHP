<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --couleur-principale: #EC4899;
            --couleur-secondaire: #F97316;
            --couleur-verte: #10B981;
            --couleur-grise-claire: #F3F4F6;
            --couleur-grise-foncee: #374151;
            --couleur-blanc: #FFFFFF;
            --ombre-legere: 0 1px 3px rgba(0, 0, 0, 0.1);
            --ombre-forte: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
           
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FDF2F8 0%, #FFFFFF 50%, #FFF7ED 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        .conteneur-principal {
           background: transparent;
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .carte-formulaire {
            width: 100%;
            border-radius: 2rem;
            box-shadow: var(--ombre-forte);
            overflow: hidden;
            background: white;
        }

        .en-tete-formulaire {
            background: linear-gradient(to right, var(--couleur-principale), var(--couleur-secondaire));
            padding: 3rem 2rem;
            text-align: center;
            color: var(--couleur-blanc);
        }

        .titre-principal {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .corps-formulaire {
            padding: 3rem 2rem;
        }

        .section-logo {
            background: linear-gradient(to right, #FDF2F8, #FFF7ED);
            border-radius: 1.5rem;
            padding: 2rem;
            border: 2px dashed var(--couleur-principale);
            margin-bottom: 2rem;
        }

        .titre-section {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--couleur-grise-foncee);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .conteneur-logo {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .zone-apercu-logo {
            width: 120px;
            height: 120px;
            background: var(--couleur-blanc);
            border-radius: 1rem;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #E5E7EB;
            flex-shrink: 0;
        }

        .image-apercu {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 1rem;
        }

        .image-cachee {
            display: none;
        }

        .zone-upload {
            position: relative;
            border: 2px dashed var(--couleur-principale);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            background: var(--couleur-blanc);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .zone-upload:hover {
            border-color: var(--couleur-secondaire);
            background-color: #FDF2F8;
        }

        .input-fichier {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .groupe-champs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .champ-formulaire {
            display: flex;
            flex-direction: column;
        }

        .label-champ {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--couleur-grise-foncee);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .input-texte, .textarea-champ {
            padding: 1rem 1.5rem;
            border: 2px solid #E5E7EB;
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-texte:focus, .textarea-champ:focus {
            border-color: var(--couleur-principale);
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .textarea-champ {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .bouton-soumettre {
            background: linear-gradient(to right, var(--couleur-principale), var(--couleur-secondaire));
            color: var(--couleur-blanc);
            font-weight: bold;
            font-size: 1.1rem;
            padding: 1rem 3rem;
            border: none;
            border-radius: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--ombre-forte);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .bouton-retour {
            background: #f3f4f6;
            color: #374151;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 1rem 3rem;
            border: none;
            border-radius: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--ombre-forte);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .next {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 200px;
            height: 45px;
            background: linear-gradient(135deg, #eb0779, #b84d83);
            color: #fff;
            gap: 8px;
            border: none;
            border-radius: 12px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .bouton-soumettre:hover, .next:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .bouton-retour:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }

        .bouton-soumettre:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .message-succes {
            background-color: #D1FAE5;
            color: #065F46;
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid var(--couleur-verte);
            margin-bottom: 1rem;
        }

        .message-erreur {
            background-color: #FEE2E2;
            color: #991B1B;
            padding: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid #EF4444;
            margin-bottom: 1rem;
        }

        .icone {
            width: 20px;
            height: 20px;
            margin-right: 0.5rem;
        }

        .steps-container {
            display: flex;
            padding: 10px;
            background-color: #F3F4F6;
            justify-content: space-around;
            align-items: center;
            margin: 30px;
            border-radius: 10px;
        }

        .step {
            width: 50px;
            height: 50px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
             Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
             font-weight: bold;
             color: white;
            background: #e5e7eb;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .step.active {
            background: var(--couleur-principale);
            width: 80px;
        }

        .deuxieme, #troisieme {
            display: none;
        }

        @media (max-width: 768px) {
            .conteneur-principal {
                padding: 1rem;
            }

            .corps-formulaire {
                padding: 2rem 1rem;
            }

            .titre-principal {
                font-size: 2rem;
            }

            .groupe-champs {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="carte-formulaire">
        <div class="en-tete-formulaire">
            <h1 class="titre-principal">✨ Créez votre Boutique ✨</h1>
            <p class="sous-titre">Rejoignez notre plateforme et développez votre clientèle</p>
        </div>
        
        <div class="steps-container">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>

        <div class="conteneur-principal">
            <form id="formulaire-boutique" method="post" action="boutique.php" class="corps-formulaire" enctype="multipart/form-data">
                <!-- etape 1-->
                <div class="section-logo">
                    <h2 class="titre-section">
                        <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Logo de votre boutique
                    </h2>
                    
                    <div class="conteneur-logo">
                        <div class="zone-apercu-logo">
                            <img id="apercu-logo" class="image-apercu image-cachee" alt="Aperçu du logo">
                            <div id="placeholder-logo" class="placeholder-logo">
                                <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 0.5rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p>Aperçu</p>
                            </div>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="conteneur-upload" style="flex: 1; min-width: 250px;">
                            <label class="label-champ">Téléchargez votre logo</label>
                            <div class="zone-upload" id="zone-upload">
                                <input type="file" id="fichier-logo" name="logo" accept="image/*" class="input-fichier">
                                <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px; margin: 0 auto 0.5rem; color: var(--couleur-principale);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p><span style="color: var(--couleur-principale); font-weight: 600;">Cliquez ici</span> ou glissez votre image</p>
                                <p style="font-size: 0.8rem; color: #9CA3AF; margin-top: 0.5rem;">PNG, JPG jusqu'à 5MB</p>
                            </div>
                        </div>
                    </div>
                     <div style="text-align: center; margin-top: 2rem;">
                        <button type="button" class="next" id="suiv">
                            Suivant <i class="fa-solid fa-arrow-right"></i>
                        </button>
                </div>
                    </div>
                <!-- etape 2 -->
                <div class="deuxieme" id="deuxieme">
                    <div class="groupe-champs">
                        <div class="champ-formulaire">
                            <label class="label-champ">
                                <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Nom de la Boutique *
                            </label>
                            <input type="text" name="nom" id="nom-boutique" placeholder="Ma Belle Boutique" required class="input-texte">
                        </div>

                        <div class="champ-formulaire">
                            <label class="label-champ">
                                <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Adresse *
                            </label>
                            <input type="text" name="adresse" id="adresse-boutique" placeholder="Yaoundé - Melen" required class="input-texte">
                        </div>
                    </div>
                    
                    <div class="champ-formulaire" style="margin-bottom: 2rem;">
                        <label class="label-champ">
                            <svg class="icone" fill="currentColor" viewBox="0 0 24 24" style="color: var(--couleur-verte);">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488z"/>
                            </svg>
                            WhatsApp *
                        </label>
                        <input type="tel" name="whatsapp" id="whatsapp-boutique" placeholder="+237 6XX XX XX XX" required class="input-texte">
                    </div>
              
                    <div style="text-align: center; display: flex; flex-wrap: wrap; gap: 1rem; padding-top: 2rem; justify-content: center;">
                        <button type="button" class="bouton-retour">
                            <i class="fa-solid fa-arrow-left"></i>
                            Précédent
                        </button>
                        <button type="button" class="next" id="suiv2">
                            Suivant <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- etape 3 -->
                <div id="troisieme">
                    <div class="groupe-champs" style="display: grid;">
                        <div class="champ-formulaire">
                            <label class="label-champ">
                                <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                                Lieu / Ville
                            </label>
                            <textarea name="services" placeholder="Douala, Yaoundé, etc." class="textarea-champ"></textarea>
                        </div>
                        
                        <div class="champ-formulaire">
                            <label class="label-champ">
                                <svg class="icone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Description de votre boutique
                            </label>
                            <textarea name="description" placeholder="Décrivez votre boutique..." class="textarea-champ"></textarea>
                        </div>
                    </div>

                    <div id="zone-messages" style="margin-bottom: 1rem;"></div>

                    <div style="text-align: center; display: flex; flex-wrap: wrap; gap: 1rem; padding-top: 2rem; justify-content: center;">
                        <button type="button" class="bouton-retour">
                            <i class="fa-solid fa-arrow-left"></i>
                            Précédent
                        </button>
                        <button type="submit" class="bouton-soumettre">
                            <i class="fas fa-plus"></i>
                            Créer votre Boutique
                        </button>
                    </div>
                </div>

                <p style="text-align: center; margin-top: 1rem; color: #6B7280; font-size: 0.9rem;">
                    En créant votre boutique, vous acceptez nos 
                    <a href="condition.html" style="text-decoration: none; color: var(--couleur-principale);">conditions d'utilisation</a>
                </p>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
$(document).ready(function() {
// gestion des etapes
var etapeActuelle = 0;
var etape1 = document.querySelector('.section-logo');
var etape2 = document.getElementById('deuxieme');
var etape3 = document.getElementById('troisieme');
var etapes = [etape1, etape2, etape3];

function afficherEtape(index) {
    etape1.style.display = "none";
    etape2.style.display = "none";
    etape3.style.display = "none";

    etapes[index].style.display = "block";

    var indicateurs = document.querySelectorAll('.step');
    for (var i = 0; i < indicateurs.length; i++) {
        indicateurs[i].classList.remove('active');
    }
    indicateurs[index].classList.add('active');

    etapeActuelle = index;

    window.scrollTo(0, 0);
}

afficherEtape(0);

// gestion d'image
var inputFile = document.getElementById('fichier-logo');
var preview = document.getElementById('apercu-logo');
var placeholder = document.getElementById('placeholder-logo');
var zoneUpload = document.getElementById('zone-upload');

inputFile.onchange = function () {

    var file = inputFile.files[0];

    if (!file) {
        return;
    }

    if (file.type.indexOf("image") === -1) {
        return;
    }

    var reader = new FileReader();

    reader.onload = function () {
        preview.src = reader.result;
        preview.classList.remove('image-cachee');
        placeholder.style.display = "none";

        setTimeout(function () {
            afficherEtape(1);
        }, 400);
    };

    reader.readAsDataURL(file);
};
zoneUpload.ondragover = function (e) {
    e.preventDefault();
    zoneUpload.style.background = "#FDF2F8";
};

zoneUpload.ondragleave = function (e) {
    e.preventDefault();
    zoneUpload.style.background = "#fff";
};

zoneUpload.ondrop = function (e) {
    e.preventDefault();
    zoneUpload.style.background = "#fff";

    var file = e.dataTransfer.files[0];

    if (!file) {
        return;
    }

    if (file.type.indexOf("image") === -1) {
        return;
    }

    var reader = new FileReader();

    reader.onload = function () {
        preview.src = reader.result;
        preview.classList.remove('image-cachee');
        placeholder.style.display = "none";

        setTimeout(function () {
           afficherEtape(1);
        }, 400);
    };

    reader.readAsDataURL(file);
};



var btnSuiv1 = document.getElementById('suiv');
var btnSuiv2 = document.getElementById('suiv2');
var btnRetour = document.querySelectorAll('.bouton-retour');

btnSuiv1.onclick = function () {
    afficherEtape(1);
};

btnSuiv2.onclick = function () {

    var nom = document.getElementById('nom-boutique').value;
    var adresse = document.getElementById('adresse-boutique').value;
    var whatsapp = document.getElementById('whatsapp-boutique').value;

    if (nom === "" || adresse === "" || whatsapp === "") {
        alert("Veuillez remplir tous les champs obligatoires");
        return;
    }

    afficherEtape(2);
};

for (var i = 0; i < btnRetour.length; i++) {
    btnRetour[i].onclick = function () {
        if (etapeActuelle > 0) {
            afficherEtape(etapeActuelle - 1);
        }
    };
}
            
            // Soumission du formulaire
            $('#formulaire-boutique').on('submit', function(e) {
                e.preventDefault();
                
                const bouton = $('.bouton-soumettre');
                const texteOriginal = bouton.html();
                
                bouton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Création en cours...');
                $('#zone-messages').empty();
                
                $.ajax({
                    url: 'boutique.php',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    
                    success: function(response) {
                        console.log('Réponse:', response);
                        
                        if (response.success) {
                            $('#zone-messages').html(`
                                <div class="message-succes">
                                    <strong>✅ ${response.message}</strong>
                                    <p>Redirection vers votre tableau de bord...</p>
                                </div>
                            `);
                            
                            setTimeout(function() {
                                window.location.href = 'ajout_produits.html';
                            }, 2000);
                            
                        } else {
                            let erreurHtml = '<div class="message-erreur">';
                            erreurHtml += '<strong>❌ Erreur</strong><br>';
                            
                            if (response.message) {
                                erreurHtml += response.message;
                            }
                            
                            if (response.errors && response.errors.length > 0) {
                                erreurHtml += '<ul style="margin-top: 10px; margin-left: 20px;">';
                                response.errors.forEach(function(error) {
                                    erreurHtml += `<li>${error}</li>`;
                                });
                                erreurHtml += '</ul>';
                            }
                            
                            erreurHtml += '</div>';
                            $('#zone-messages').html(erreurHtml);
                            bouton.prop('disabled', false).html(texteOriginal);
                            
                            $('html, body').animate({
                                scrollTop: $('#zone-messages').offset().top - 100
                            }, 500);
                        }
                    },
                    
                    error: function(xhr, status, error) {
                        console.error('Erreur AJAX:', error);
                        $('#zone-messages').html(`
                            <div class="message-erreur">
                                <strong>❌ Erreur de communication</strong>
                                <p>Impossible de contacter le serveur.</p>
                            </div>
                        `);
                        bouton.prop('disabled', false).html(texteOriginal);
                    }
                });
            });
        });
    </script>
</body>
</html>