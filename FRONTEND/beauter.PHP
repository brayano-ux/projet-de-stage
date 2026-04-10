
<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre Boutique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="src/css/vetement.css">
</head>
<body>
    <style>
.retour {
    position: fixed;
    top: 20px;
    left: 20px;

    background: linear-gradient(135deg, #bd329a, #cf4db3);
    color: white;
    border: none;
    border-radius: 50%;
    
    width: 50px;
    height: 50px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
    cursor: pointer;

    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.retour:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 15px rgba(0,0,0,0.3);
}

.retour:active {
    transform: scale(0.95);
}
</style>

<button class="retour" onclick="window.location.href='templates.html'">
  <i class="fas fa-arrow-left"></i>
</button>
    <div class="carte-formulaire">
        <div class="en-tete-formulaire">
            <h1 class="titre-principal"> <i class="fa-solid fa-sparkles"></i> Créez votre Boutique <i class="fa-solid fa-sparkles"></i></h1>
            <p class="sous-titre">Rejoignez notre plateforme et développez votre clientèle</p>
        </div>
        
        <div class="steps-container">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>

        <div class="conteneur-principal">
            <form id="formulaire-boutique" method="post" action="boutique.php" class="corps-formulaire" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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
    <script src="src/js/electronic.js">
    </script>
</body>
</html>