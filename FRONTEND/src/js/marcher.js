(function(){

    // ── Toast ─────────────────────────────────────────────
    function toast(msg){
        const z = document.getElementById('toasts');
        const t = document.createElement('div');
        t.className = 'toast';
        t.innerHTML = '<i class="fas fa-check-circle"></i><span>' + msg + '</span>';
        z.appendChild(t);
        setTimeout(()=>{
            t.classList.add('out');
            setTimeout(()=>t.remove(), 350);
        }, 2800);
    }

    // ── Favoris ───────────────────────────────────────────
    document.querySelectorAll('.btn-coeur').forEach(b => {
        b.addEventListener('click', e => {
            e.stopPropagation();
            const boutique_id = b.dataset.id;
            fetch('favoris.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'boutique_id=' + encodeURIComponent(boutique_id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        b.classList.add('actif');
                        b.textContent = '♥';
                        toast('Ajouté aux favoris !');
                    } else {
                        b.classList.remove('actif');
                        b.textContent = '♡';
                        toast('Retiré des favoris.');
                    }
                } else {
                    toast('⚠ ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(() => toast('⚠ Erreur réseau, réessayez.'));
        });
    });

    // ═════════════════════════════════════════════════════
    // MODAL COMMANDE
    // ═════════════════════════════════════════════════════
    let panierN           = 0;
    let currentPrix       = 0;
    let currentWa         = '';
    let currentBoutiqueId = '';
    let currentProduitId  = '';

    const panierCount    = document.getElementById('panier-count');
    const modal          = document.getElementById('modal-commande');
    const overlay        = document.getElementById('cmd-overlay');
    const closeBtn       = document.getElementById('cmd-close');
    const validerBtn     = document.getElementById('cmd-btn-valider');
    const cmdNomProduit  = document.getElementById('cmd-nom-produit');
    const cmdPrixProduit = document.getElementById('cmd-prix-produit');
    const cmdTotal       = document.getElementById('cmd-total');
    const cmdQte         = document.getElementById('cmd-qte');
    const cmdNote        = document.getElementById('cmd-note');
    const cmdNom         = document.getElementById('cmd-nom');
    const cmdTel         = document.getElementById('cmd-tel');
    const cmdErreur      = document.getElementById('cmd-erreur');

    // ── Ouvrir le modal au clic sur Commander ─────────────
    document.querySelectorAll('.btn-commander').forEach(b => {
        b.addEventListener('click', () => {
            const c    = b.closest('.carte');
            const nom  = c.dataset.nom  || '';
            const prix = parseInt(c.dataset.prix, 10) || 0;
            const wa   = c.dataset.wa   || '';
            const lieu = c.dataset.lieu || '';

            // Vérification WhatsApp
            if (!wa || wa.trim() === '') {
                toast('⚠ Ce vendeur n\'a pas de numéro WhatsApp.');
                return;
            }

            // Stocker les données du produit courant
            currentPrix       = prix;
            currentWa         = wa.replace(/[^0-9+]/g, '');
            currentBoutiqueId = c.dataset.boutique || '';
            currentProduitId  = c.dataset.id       || '';

            // Remplir l'en-tête du modal
            cmdNomProduit.textContent  = nom;
            cmdPrixProduit.textContent = prix.toLocaleString('fr-FR') + ' FCFA';
            cmdTotal.textContent       = prix.toLocaleString('fr-FR') + ' FCFA';

            // Image du produit dans l'en-tête
            const imgWrap = document.getElementById('cmd-img-wrap');
            if (imgWrap) {
                const imgEl = c.querySelector('.carte-img img');
                if (imgEl && imgEl.src) {
                    imgWrap.innerHTML = '<img src="' + imgEl.src + '" alt="' + nom + '" style="width:100%;height:100%;object-fit:cover;">';
                } else {
                    imgWrap.innerHTML = '<i class="fas fa-image"></i>';
                }
            }

            // Reset formulaire
            cmdNom.value            = '';
            cmdTel.value            = '';
            cmdQte.value            = '1';
            cmdNote.value           = '';
            cmdErreur.style.display = 'none';
            cmdErreur.textContent   = '';

            // Afficher le modal
            modal.style.display          = 'flex';
            document.body.style.overflow = 'hidden';

            setTimeout(() => cmdNom.focus(), 80);
        });
    });

    // ── Mise à jour du total en temps réel ───────────────
    cmdQte.addEventListener('input', () => {
        const total = currentPrix * (parseInt(cmdQte.value) || 1);
        cmdTotal.textContent = total.toLocaleString('fr-FR') + ' FCFA';
    });

    // ── Valider la commande ───────────────────────────────
    validerBtn.addEventListener('click', () => {

        const nom  = cmdNom.value.trim();
        const tel  = cmdTel.value.trim();
        const qte  = parseInt(cmdQte.value) || 1;
        const note = cmdNote.value.trim();

        // Validation
        if (!nom) {
            cmdErreur.innerHTML     = '<i class="fas fa-exclamation-circle"></i> Veuillez entrer votre nom.';
            cmdErreur.style.display = 'flex';
            cmdNom.focus();
            return;
        }
        if (!tel) {
            cmdErreur.innerHTML     = '<i class="fas fa-exclamation-circle"></i> Veuillez entrer votre téléphone.';
            cmdErreur.style.display = 'flex';
            cmdTel.focus();
            return;
        }

        cmdErreur.style.display = 'none';

        const total = currentPrix * qte;

        // ── Construire le message WhatsApp ────────────────
        const lignes = [
            'Creator Market — Nouvelle commande',
            'Bonjour 👋',
            '',
            'Je souhaite commander :',
            '',
            '🛒 *Produit*  : ' + cmdNomProduit.textContent,
            '📦 *Quantité* : ' + qte,
            '💰 *Total*    : ' + total.toLocaleString('fr-FR') + ' FCFA',
            '',
            '👤 *Mon nom*  : ' + nom,
            '📞 *Tél*      : ' + tel,
        ];
        if (note) lignes.push('📝 *Note*     : ' + note);
        lignes.push('', 'Merci de confirmer la disponibilité !');

        const msg = lignes.join('\n');
        const url = 'https://wa.me/' + currentWa + '?text=' + encodeURIComponent(msg);

        // ── Ouvrir fenêtre MAINTENANT (évite blocage popup) ──
        // On ouvre une fenêtre vide immédiatement dans l'événement clic
        // puis on la redirige vers WhatsApp après le fetch
        const waFenetre = window.open('', '_blank');

        // ── Spinner ───────────────────────────────────────
        validerBtn.disabled  = true;
        validerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi…';

        // ── Enregistrer la commande en BDD ────────────────
        const fd = new FormData();
        fd.append('nom_client',  nom);
        fd.append('telephone',   tel);
        fd.append('quantite',    qte);
        fd.append('montant',     total);
        fd.append('note',        note);
        fd.append('boutique_id', currentBoutiqueId);
        fd.append('produit_id',  currentProduitId);

        fetch('enregistrer_commande.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.warn('Commande non enregistrée :', data.message);
                } else {
                    console.log('✅ Commande #' + data.commande_id + ' enregistrée');
                }
            })
            .catch(err => console.warn('Erreur réseau commande :', err))
            .finally(() => {
                // Rediriger la fenêtre déjà ouverte vers WhatsApp
                if (waFenetre) waFenetre.location.href = url;

                // Reset bouton
                validerBtn.disabled  = false;
                validerBtn.innerHTML = '<i class="fab fa-whatsapp"></i> Confirmer et ouvrir WhatsApp';
            });

        // ── Fermer le modal immédiatement ─────────────────
        fermer();

        // ── Compteur panier ───────────────────────────────
        panierN++;
        if (panierCount) {
            panierCount.textContent = panierN;
            panierCount.classList.add('visible');
        }

        toast('✅ Redirection vers WhatsApp…');
    });

    // ── Fermer le modal ───────────────────────────────────
    closeBtn.addEventListener('click', fermer);
    overlay.addEventListener('click', fermer);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') fermer(); });

    function fermer() {
        modal.style.display          = 'none';
        document.body.style.overflow = '';
    }

    // ── Partage ───────────────────────────────────────────
    document.querySelectorAll('.btn-partage').forEach(b => {
        b.addEventListener('click', () => {
            const c   = b.closest('.carte');
            const nom = c.dataset.nom;
            if (navigator.share) {
                navigator.share({ title: nom, url: location.href });
            } else {
                navigator.clipboard.writeText(location.href);
                toast('Lien copié !');
            }
        });
    });

    // ── Recherche ─────────────────────────────────────────
    document.getElementById('search-input').addEventListener('input', filtrer);

    // ── Filtres catégorie ─────────────────────────────────
    document.querySelectorAll('.filtre-btn').forEach(b => {
        b.addEventListener('click', () => {
            document.querySelectorAll('.filtre-btn').forEach(x => x.classList.remove('actif'));
            b.classList.add('actif');
            filtrer();
        });
    });

    document.getElementById('city-select').addEventListener('change', filtrer);
    document.getElementById('sort-select').addEventListener('change', filtrer);

    function filtrer() {
        const q     = document.getElementById('search-input').value.toLowerCase().trim();
        const ville = document.getElementById('city-select').value.toLowerCase();
        const cartes = document.querySelectorAll('#grille .carte');
        let visible = 0;

        cartes.forEach(c => {
            const nom  = (c.dataset.nom  || '').toLowerCase();
            const desc = (c.dataset.desc || '').toLowerCase();
            const lieu = (c.dataset.lieu || '').toLowerCase();
            const okQ  = !q    || nom.includes(q) || desc.includes(q) || lieu.includes(q);
            const okV  = !ville || lieu.includes(ville);
            const show = okQ && okV;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('count-label').textContent = visible;
    }

    window.toggleMenu = function(){
        document.querySelector('.nav-links').style.display =
            document.querySelector('.nav-links').style.display === 'flex' ? 'none' : 'flex';
    };

})();