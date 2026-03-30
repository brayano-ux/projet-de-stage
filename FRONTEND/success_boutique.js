/**
 * ─────────────────────────────────────────────────────────────────────────────
 * success_boutique.js
 * À inclure dans votre page de création de boutique.
 * Gère la soumission du formulaire et affiche la modal de succès avec
 * le lien public et le QR code généré par le serveur.
 * ─────────────────────────────────────────────────────────────────────────────
 */

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formulaire-boutique'); // adapter l'ID si nécessaire

    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn = form.querySelector('[type="submit"]');
        const formData = new FormData(form);

        // Désactiver le bouton pendant l'envoi
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Création en cours…';
        }

        try {
            const res  = await fetch('boutique.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                afficherSucces(data);
            } else {
                const msgs = data.errors?.length
                    ? data.errors.join('\n')
                    : data.message || 'Une erreur est survenue.';
                alert(msgs);
            }

        } catch (err) {
            alert('Erreur réseau. Veuillez réessayer.');
            console.error(err);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Créer ma boutique';
            }
        }
    });

    /**
     * Affiche la modal de succès avec le lien et le QR code
     * @param {Object} data - Réponse JSON du serveur
     */
    function afficherSucces(data) {

        // ── Supprimer une éventuelle ancienne modal ────────────────────────────
        document.getElementById('modal-boutique-succes')?.remove();

        // ── Construire la modal ───────────────────────────────────────────────
        const modal = document.createElement('div');
        modal.id    = 'modal-boutique-succes';
        modal.innerHTML = `
            <div class="modal-overlay" id="modal-overlay-boutique"></div>
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-titre">

                <!-- Icône succès -->
                <div class="modal-icon">&#10003;</div>

                <h2 id="modal-titre">Boutique créée avec succès !</h2>
                <p class="modal-subtitle">Partagez votre boutique avec vos clients.</p>

                <!-- Lien public -->
                <div class="modal-section">
                    <label>🔗 Lien de votre boutique</label>
                    <div class="lien-wrapper">
                        <input
                            type="text"
                            id="lien-boutique"
                            value="${escapeHtml(data.lien_public)}"
                            readonly
                        />
                        <button type="button" onclick="copierLien()" title="Copier le lien">
                            📋
                        </button>
                    </div>
                    <span id="copie-msg" class="copie-msg" aria-live="polite"></span>
                </div>

                <!-- QR Code -->
                <div class="modal-section">
                    <label>📱 QR Code de votre boutique</label>
                    <div class="qr-wrapper">
                        <img
                            src="${escapeHtml(data.qrcode_url)}"
                            alt="QR Code boutique"
                            id="qr-img"
                            width="200"
                            height="200"
                        />
                    </div>
                    <a
                        href="${escapeHtml(data.qrcode_url)}"
                        download="qrcode-boutique.png"
                        class="btn-telecharger"
                    >
                        ⬇ Télécharger le QR Code
                    </a>
                </div>

                <!-- Actions -->
                <div class="modal-actions">
                    <button type="button" class="btn-continuer" onclick="continuerVersAjout('${escapeHtml(data.redirect)}')">
                        Ajouter mes produits →
                    </button>
                    <button type="button" class="btn-fermer" onclick="fermerModal()">
                        Fermer
                    </button>
                </div>
            </div>
        `;

        // ── Injecter les styles si pas déjà présents ──────────────────────────
        if (!document.getElementById('style-modal-succes')) {
            const style = document.createElement('style');
            style.id    = 'style-modal-succes';
            style.textContent = `
                #modal-boutique-succes {
                    position: fixed;
                    inset: 0;
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 1rem;
                    font-family: 'Segoe UI', sans-serif;
                }
                .modal-overlay {
                    position: absolute;
                    inset: 0;
                    background: rgba(0,0,0,0.55);
                    backdrop-filter: blur(3px);
                }
                .modal-box {
                    position: relative;
                    background: #fff;
                    border-radius: 16px;
                    padding: 2rem;
                    max-width: 480px;
                    width: 100%;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
                    text-align: center;
                    animation: modalEnter .3s ease;
                }
                @keyframes modalEnter {
                    from { transform: scale(.9); opacity: 0; }
                    to   { transform: scale(1);  opacity: 1; }
                }
                .modal-icon {
                    width: 56px; height: 56px;
                    background: #22c55e;
                    color: #fff;
                    border-radius: 50%;
                    font-size: 2rem;
                    line-height: 56px;
                    margin: 0 auto 1rem;
                }
                .modal-box h2 {
                    margin: 0 0 .25rem;
                    color: #1a1a1a;
                    font-size: 1.4rem;
                }
                .modal-subtitle {
                    color: #6b7280;
                    margin-bottom: 1.5rem;
                    font-size: .9rem;
                }
                .modal-section {
                    text-align: left;
                    margin-bottom: 1.2rem;
                }
                .modal-section label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: .4rem;
                    color: #374151;
                    font-size: .9rem;
                }
                .lien-wrapper {
                    display: flex;
                    gap: .4rem;
                }
                .lien-wrapper input {
                    flex: 1;
                    padding: .5rem .75rem;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    font-size: .8rem;
                    color: #1d4ed8;
                    background: #f0f4ff;
                    cursor: text;
                }
                .lien-wrapper button {
                    padding: .5rem .7rem;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    background: #fff;
                    cursor: pointer;
                    font-size: 1rem;
                    transition: background .2s;
                }
                .lien-wrapper button:hover { background: #f3f4f6; }
                .copie-msg {
                    display: block;
                    font-size: .78rem;
                    color: #22c55e;
                    min-height: 1.2em;
                    margin-top: .2rem;
                }
                .qr-wrapper {
                    display: flex;
                    justify-content: center;
                    margin: .6rem 0;
                    padding: .75rem;
                    border: 1px dashed #d1d5db;
                    border-radius: 10px;
                    background: #fafafa;
                }
                .qr-wrapper img {
                    border-radius: 8px;
                    max-width: 200px;
                }
                .btn-telecharger {
                    display: inline-block;
                    margin-top: .4rem;
                    padding: .4rem 1rem;
                    background: #1d4ed8;
                    color: #fff;
                    border-radius: 6px;
                    font-size: .82rem;
                    text-decoration: none;
                    transition: background .2s;
                }
                .btn-telecharger:hover { background: #1e40af; }
                .modal-actions {
                    display: flex;
                    gap: .75rem;
                    justify-content: center;
                    margin-top: 1.5rem;
                }
                .btn-continuer {
                    padding: .65rem 1.4rem;
                    background: #22c55e;
                    color: #fff;
                    border: none;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    font-size: .9rem;
                    transition: background .2s;
                }
                .btn-continuer:hover { background: #16a34a; }
                .btn-fermer {
                    padding: .65rem 1.2rem;
                    background: #f3f4f6;
                    color: #374151;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    font-size: .9rem;
                    cursor: pointer;
                    transition: background .2s;
                }
                .btn-fermer:hover { background: #e5e7eb; }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(modal);

        // Fermer en cliquant sur l'overlay
        document.getElementById('modal-overlay-boutique')
            ?.addEventListener('click', fermerModal);
    }

    // ── Fonctions globales accessibles depuis le HTML inline ──────────────────

    window.copierLien = function () {
        const input = document.getElementById('lien-boutique');
        const msg   = document.getElementById('copie-msg');
        if (!input) return;

        navigator.clipboard.writeText(input.value)
            .then(() => {
                if (msg) {
                    msg.textContent = '✓ Lien copié dans le presse-papiers !';
                    setTimeout(() => { msg.textContent = ''; }, 3000);
                }
            })
            .catch(() => {
                // Fallback ancienne méthode
                input.select();
                document.execCommand('copy');
                if (msg) {
                    msg.textContent = '✓ Lien copié !';
                    setTimeout(() => { msg.textContent = ''; }, 3000);
                }
            });
    };

    window.fermerModal = function () {
        document.getElementById('modal-boutique-succes')?.remove();
    };

    window.continuerVersAjout = function (url) {
        if (url) window.location.href = url;
    };

    // ── Utilitaire : échapper les caractères HTML ─────────────────────────────
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#039;');
    }

});