   $(document).ready(function() {
    $.get('get_csrf.php', function(data) {
        if (data.csrf_token) {
            $('#csrf_token').val(data.csrf_token);
            console.log('Token CSRF chargé:', data.csrf_token);
        }
    });

    let forceMotDePasse = 0;
    
    // Affichage/masquage mot de passe
    $('.bouton-afficher-mdp').on('click', function() {
        const target = $(this).data('target');
        const input = $(`#${target}`);
        const currentType = input.attr('type');
        const newType = currentType === 'password' ? 'text' : 'password';
        
        input.attr('type', newType);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // Force du mot de passe
    function verifierForce(password) {
        if (!password) {
            forceMotDePasse = 0;
            $('#force-mdp').css('width', '0%').removeClass();
            $('#texte-force').text('Utilisez 8 caractères minimum');
            return false;
        }
        
        let force = 0;
        if (password.length >= 8) force++;
        if (password.match(/[a-z]/)) force++;
        if (password.match(/[A-Z]/)) force++;
        if (password.match(/[0-9]/)) force++;
        if (password.match(/[^a-zA-Z0-9]/)) force++;

        const barre = $('#force-mdp');
        const texte = $('#texte-force');
        
        let largeur, classe, description;
        
        if (force <= 2) {
            largeur = '33%';
            classe = 'force-faible';
            description = 'Faible';
        } else if (force === 3 || force === 4) {
            largeur = '66%';
            classe = 'force-moyenne';
            description = 'Moyen';
        } else {
            largeur = '100%';
            classe = 'force-forte';
            description = 'Fort';
        }
        
        barre.css('width', largeur).removeClass().addClass('niveau-force ' + classe);
        texte.text(`Force : ${description}`);
        
        forceMotDePasse = force;
        return force >= 3;
    }

    $('#passworde').on('input', function() {
        verifierForce($(this).val());
    });

    // Soumission AJAX du formulaire
    $('#form-inscription').on('submit', function(e) {
        e.preventDefault();
        
        const btnSoumettre = $('#btn-soumettre');
        const texteOriginal = btnSoumettre.html();
        
        btnSoumettre.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>En cours...');
$.ajax({
    url: 'inscription.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    
    success: function(response) {
        console.log('Réponse du serveur:', response);
        
        if (response.success) {
            // Afficher les infos de debug si présentes
            if (response.debug && response.debug.length > 0) {
                console.log('Debug:', response.debug);
            }
            
            // Succès - rediriger
            $('#etape-1').removeClass('active').addClass('terminee');
            $('#ligne-1').addClass('terminee');
            $('#etape-2').removeClass('inactive').addClass('active');
            
            $('#titre-principal').html('<i class="fas fa-check me-2"></i>Inscription réussie !');
            $('#sous-titre').text('Votre compte a été créé avec succès');
            $('#nom-bienvenue').text(response.user.nom);
            
            $('#etape-formulaire').slideUp(400, function() {
                $('#etape-confirmation').slideDown(400);
                $('#section-connexion').hide();
            });
            
            // Redirection après 3 secondes
            setTimeout(function() {
                window.location.href = 'templates.html';
            }, 3000);
            
        } else {
            // Afficher les erreurs
            let errorHtml = '<div class="alert alert-danger">';
            
            if (response.errors && response.errors.length > 0) {
                errorHtml += '<ul class="mb-0">';
                response.errors.forEach(function(error) {
                    errorHtml += '<li>' + error + '</li>';
                });
                errorHtml += '</ul>';
            } else if (response.message) {
                errorHtml += response.message;
            } else {
                errorHtml += 'Une erreur est survenue';
            }
            
            errorHtml += '</div>';
            
            $('#zone-messages').html(errorHtml);
            
            // Réactiver le bouton
            btnSoumettre.prop('disabled', false).html(texteOriginal);
            
            // Scroll vers les erreurs
            $('html, body').animate({
                scrollTop: $('#zone-messages').offset().top - 100
            }, 500);
        }
    },
    
    error: function(xhr, status, error) {
        console.error('Erreur AJAX:', status, error);
        
        $('#zone-messages').html(`
            <div class="alert alert-danger">
                <strong>Erreur de communication avec le serveur</strong><br>
                Vérifiez votre connexion internet ou réessayez plus tard.
            </div>
        `);
        
        btnSoumettre.prop('disabled', false).html(texteOriginal);
    }
});

    });

    // Bouton de continuation
    $('#btn-continuer').on('click', function() {
        $(this).prop('disabled', true).html('<span class="loader me-2"></span>Redirection...');
        window.location.href = 'templates.html';
    });
    
    setTimeout(() => $('#nom').focus(), 500);
});