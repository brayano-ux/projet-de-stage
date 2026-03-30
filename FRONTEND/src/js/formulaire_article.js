$(document).ready(function () {

    var fichierImage = null;

    function afficherApercu(file) {
        if (!file || !file.type.startsWith('image/')) return;
        fichierImage = file;
        var reader = new FileReader();
        reader.onload = function (ev) {
            $('#apercu-wrap').addClass('a-image').html('<img src="' + ev.target.result + '" alt="Aperçu">');
        };
        reader.readAsDataURL(file);
    }

    $('#fichier-image').on('change', function () { afficherApercu(this.files[0]); });

    var $zone = $('#zone-upload');
    $zone
        .on('dragover',  function (e) { e.preventDefault(); $zone.addClass('dragover'); })
        .on('dragleave', function (e) { e.preventDefault(); $zone.removeClass('dragover'); })
        .on('drop',      function (e) {
            e.preventDefault();
            $zone.removeClass('dragover');
            afficherApercu(e.originalEvent.dataTransfer.files[0]);
        });

    $('#formulaire-produit').on('submit', function (e) {
        e.preventDefault();

        var $bouton       = $('#bouton-soumettre');
        var texteOriginal = $bouton.html();

        $bouton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ajout en cours…');
        $('#zone-messages').empty();

        var fd = new FormData(this);
        if (fichierImage) fd.set('logo', fichierImage, fichierImage.name);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {
                if (res.success) {
                    $('#zone-messages').html(
                        '<div class="msg msg-succes">' +
                        '<i class="fas fa-circle-check"></i>' +
                        '<div><strong>✅ ' + res.message + '</strong>' +
                        '<p style="margin-top:3px;opacity:0.8;">Redirection en cours…</p></div>' +
                        '</div>'
                    );
                    setTimeout(function () {
                        window.location.href ='dashboard.php';
                    }, 2000);
                } else {
                    var erreursHtml = '';
                    if (res.erreurs && res.erreurs.length) {
                        erreursHtml = '<ul>' + res.erreurs.map(function (e) {
                            return '<li>' + e + '</li>';
                        }).join('') + '</ul>';
                    }
                    $('#zone-messages').html(
                        '<div class="msg msg-erreur">' +
                        '<i class="fas fa-triangle-exclamation"></i>' +
                        '<div><strong>❌ Erreur</strong>' +
                        (res.message ? '<p style="margin-top:3px;">' + res.message + '</p>' : '') +
                        erreursHtml + '</div></div>'
                    );
                    $bouton.prop('disabled', false).html(texteOriginal);
                    $('html,body').animate({ scrollTop: $('#zone-messages').offset().top - 80 }, 400);
                }
            },

            error: function () {
                $('#zone-messages').html(
                    '<div class="msg msg-erreur">' +
                    '<i class="fas fa-wifi"></i>' +
                    '<div><strong>❌ Erreur de communication</strong>' +
                    '<p style="margin-top:3px;">Impossible de contacter le serveur.</p>' +
                    '</div></div>'
                );
                $bouton.prop('disabled', false).html(texteOriginal);
            }
        });
    });
});