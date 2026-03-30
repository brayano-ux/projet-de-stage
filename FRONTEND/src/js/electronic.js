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