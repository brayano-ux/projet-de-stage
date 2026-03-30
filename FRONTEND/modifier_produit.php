<?php
session_start();
require_once __DIR__ . '/config/index.php';

// ── Sécurité ──────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Connexion à la base de données via la configuration
try {
    $pdo = DatabaseConfig::getConnection();
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$user_id = (int) $_SESSION['user_id'];
$id      = (int) ($_GET['id'] ?? 0);

// ── Récupérer le produit (appartient à cet utilisateur) ──────────────────────
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ? AND utilisateur_id = ? LIMIT 1");
$stmt->execute([$id, $user_id]);
$produit = $stmt->fetch();

if (!$produit) {
    header('Location: dashboard.php');
    exit;
}

// ── CSRF ──────────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$success = false;

// ── Traitement POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide. Rechargez la page.';
    }

    if (empty($errors)) {
        $nom         = trim($_POST['nom']         ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix        = trim($_POST['prix']         ?? '');
        $localisation= trim($_POST['localisation'] ?? '');
        $whatsapp    = trim($_POST['whatsapp']     ?? '');

        // Validation
        if (empty($nom))          $errors[] = 'Le nom du produit est obligatoire.';
        if (strlen($nom) > 200)   $errors[] = 'Nom trop long (200 car. max).';
        if (!is_numeric($prix) || (float)$prix < 0) $errors[] = 'Prix invalide.';

        // Normalisation WhatsApp
        if (!empty($whatsapp)) {
            $waClean = preg_replace('/[\s\-\(\)]/', '', $whatsapp);
            if (!preg_match('/^\+?[0-9]{8,20}$/', $waClean)) {
                $errors[] = 'Numéro WhatsApp invalide.';
            } else {
                if (!str_starts_with($waClean, '+')) {
                    if (str_starts_with($waClean, '237'))      $waClean = '+' . $waClean;
                    elseif (str_starts_with($waClean, '6') && strlen($waClean) === 9) $waClean = '+237' . $waClean;
                }
                $whatsapp = $waClean;
            }
        }
    }

    // Upload nouvelle image (optionnel)
    $imagePath = $produit['image']; // garder l'ancienne par défaut

    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $maxSize      = 5 * 1024 * 1024;
        $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
        $mimeToExt    = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];

        if ($_FILES['image']['size'] > $maxSize) {
            $errors[] = "Image trop grande (5 Mo max).";
        }
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = "Format non autorisé (JPEG, PNG, GIF, WebP).";
        }
        if (empty($errors) && !getimagesize($_FILES['image']['tmp_name'])) {
            $errors[] = "Fichier image invalide.";
        }

        if (empty($errors)) {
            $uploadDir = __DIR__ . '/uploads/produits/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            // .htaccess sécurité
            if (!file_exists($uploadDir . '.htaccess')) {
                file_put_contents($uploadDir . '.htaccess', "php_flag engine off\n");
            }

            $ext        = $mimeToExt[$mimeType];
            $fileName   = 'produit_' . uniqid('', true) . '_' . time() . '.' . $ext;
            $fullPath   = $uploadDir . $fileName;
            $newPath    = 'uploads/produits/' . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
                chmod($fullPath, 0644);
                // Supprimer l'ancienne image
                if (!empty($produit['image']) && file_exists(__DIR__ . '/' . $produit['image'])) {
                    @unlink(__DIR__ . '/' . $produit['image']);
                }
                $imagePath = $newPath;
            } else {
                $errors[] = "Erreur lors de l'upload de l'image.";
            }
        }
    }

    // Mise à jour BDD
    if (empty($errors)) {
        $upd = $pdo->prepare("
            UPDATE produits
            SET nom = ?, description = ?, prix = ?, localisation = ?, whatsapp = ?, image = ?
            WHERE id = ? AND utilisateur_id = ?
        ");
        $upd->execute([
            $nom, $description, (float)$prix,
            $localisation, $whatsapp, $imagePath,
            $id, $user_id
        ]);

        // Rafraîchir les données affichées
        $produit = array_merge($produit, [
            'nom' => $nom, 'description' => $description,
            'prix' => $prix, 'localisation' => $localisation,
            'whatsapp' => $whatsapp, 'image' => $imagePath,
        ]);

        $success = true;
        unset($_SESSION['csrf_token']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le produit — Creator Market</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:       #3b82f6;
            --primary-dark:  #2563eb;
            --primary-light: #60a5fa;
            --success:       #10b981;
            --warning:       #f59e0b;
            --danger:        #ef4444;
            --bg:            #0f172a;
            --surface:       #1e293b;
            --surface2:      #334155;
            --border:        rgba(255,255,255,.07);
            --border-strong: rgba(59,130,246,.18);
            --text:          #f1f5f9;
            --text-2:        #cbd5e1;
            --text-muted:    #64748b;
            --radius:        14px;
            --transition:    all .25s cubic-bezier(.4,0,.2,1);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background:white;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 32px 16px 60px;
        }

        /* ── Page wrapper ── */
        .page {
            width: 100%;
            max-width: 760px;
            animation: fadeUp .5s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── Breadcrumb ── */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: var(--text-muted);
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color .2s;
        }
        .breadcrumb a:hover { color: var(--primary-light); }
        .breadcrumb i { font-size: .65rem; }
        .breadcrumb .current { color: var(--text-2); font-weight: 600; }

        .card {
            background:#256;
            border: 1px solid var(--border-strong);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }

        .card-header {
            padding: 28px 32px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg,rgba(59,130,246,.2),rgba(139,92,246,.2));
            border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: var(--primary-light);
            box-shadow: 0 4px 16px rgba(59,130,246,.2);
            flex-shrink: 0;
        }
        .card-header h1 {
            font-family: 'Syne', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.3px;
        }
        .card-header p {
            font-size: .84rem;
            color: var(--text-muted);
            margin-top: 3px;
        }

        /* ── Card Body ── */
        .card-body { padding: 32px; }

        /* ── Alertes ── */
        .alerte {
            border-radius: 11px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: .88rem;
            line-height: 1.5;
        }
        .alerte i { font-size: 16px; flex-shrink: 0; margin-top: 2px; }
        .alerte-succes {
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.25);
            color: #6ee7b7;
        }
        .alerte-erreur {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.25);
            color: #fca5a5;
        }
        .alerte-erreur ul { padding-left: 1.2rem; margin-top: 6px; }

        /* ── Grille formulaire ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-grid .full { grid-column: 1 / -1; }

        /* ── Champ ── */
        .champ { display: flex; flex-direction: column; gap: 7px; }
        .champ label {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .champ label i { font-size: 13px; color: var(--primary-light); }
        .req { color: var(--danger); }

        .champ input,
        .champ textarea,
        .champ select {
            background: rgba(255,255,255,.04);
            border: 1.5px solid var(--border);
            border-radius: 11px;
            padding: 12px 16px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            transition: var(--transition);
            outline: none;
            width: 100%;
        }
        .champ input:focus,
        .champ textarea:focus {
            border-color: var(--primary);
            background: rgba(59,130,246,.06);
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }
        .champ input::placeholder,
        .champ textarea::placeholder { color: var(--text-muted); }
        .champ textarea { resize: vertical; min-height: 100px; }

        /* ── Upload image ── */
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: 13px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .upload-zone:hover {
            border-color: var(--primary);
            background: rgba(59,130,246,.05);
        }
        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .upload-zone i {
            font-size: 32px;
            color: var(--primary-light);
            margin-bottom: 10px;
            display: block;
        }
        .upload-zone p { font-size: .85rem; color: var(--text-2); }
        .upload-zone span { color: var(--primary-light); font-weight: 700; }
        .upload-zone small { display: block; color: var(--text-muted); margin-top: 4px; font-size: .76rem; }

        /* Aperçu image */
        .image-actuelle {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 11px;
            margin-bottom: 12px;
        }
        .image-actuelle img {
            width: 60px; height: 60px;
            object-fit: cover;
            border-radius: 9px;
            border: 1px solid var(--border);
        }
        .image-actuelle-info { flex: 1; }
        .image-actuelle-info p {
            font-size: .82rem;
            color: var(--text-2);
            font-weight: 600;
        }
        .image-actuelle-info small {
            font-size: .75rem;
            color: var(--text-muted);
        }

        /* Aperçu nouvelle image */
        #apercu-nouvelle {
            display: none;
            margin-top: 12px;
        }
        #apercu-nouvelle img {
            width: 80px; height: 80px;
            object-fit: cover;
            border-radius: 11px;
            border: 2px solid var(--primary);
            box-shadow: 0 4px 14px rgba(59,130,246,.25);
        }
        #apercu-nouvelle p {
            font-size: .76rem;
            color: var(--success);
            margin-top: 6px;
            font-weight: 600;
        }

        /* ── Séparateur ── */
        .separator {
            border: none;
            border-top: 1px solid var(--border);
            margin: 28px 0;
        }

        /* ── Footer actions ── */
        .form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 32px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 24px;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }
        .btn-retour {
            background: rgba(255,255,255,.06);
            color: var(--text-2);
            border: 1px solid var(--border);
        }
        .btn-retour:hover {
            background: rgba(255,255,255,.1);
            color: var(--text);
            transform: translateX(-3px);
        }
        .btn-sauvegarder {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 18px rgba(59,130,246,.35);
            min-width: 180px;
            justify-content: center;
        }
        .btn-sauvegarder:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(59,130,246,.5);
        }
        .btn-sauvegarder:active { transform: translateY(0); }
        .btn-sauvegarder.loading {
            opacity: .75;
            pointer-events: none;
        }
        .btn-sauvegarder.loading i { animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .card-header { padding: 22px 20px 18px; }
            .card-body   { padding: 22px 20px; }
            .form-grid   { grid-template-columns: 1fr; }
            .form-footer { flex-direction: column; }
            .btn         { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <a href="dashboard.php#produits">Mes produits</a>
        <i class="fas fa-chevron-right"></i>
        <span class="current">Modifier</span>
    </nav>

    <div class="card">

        <!-- Header -->
        <div class="card-header">
            <div class="header-icon"><i class="fas fa-pen"></i></div>
            <div>
                <h1>Modifier le produit</h1>
                <p><?= htmlspecialchars($produit['nom']) ?> — ID #<?= $id ?></p>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">

            <?php if ($success): ?>
            <div class="alerte alerte-succes">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Produit mis à jour avec succès !</strong><br>
                    Les modifications ont bien été enregistrées.
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="alerte alerte-erreur">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="form-modifier" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-grid">

                    <!-- Nom -->
                    <div class="champ full">
                        <label><i class="fas fa-tag"></i> Nom du produit <span class="req">*</span></label>
                        <input
                            type="text"
                            name="nom"
                            value="<?= htmlspecialchars($produit['nom']) ?>"
                            placeholder="Ex : Robe en wax traditionnelle"
                            required
                            maxlength="200"
                        >
                    </div>

                    <!-- Prix -->
                    <div class="champ">
                        <label><i class="fas fa-coins"></i> Prix (FCFA) <span class="req">*</span></label>
                        <input
                            type="number"
                            name="prix"
                            value="<?= htmlspecialchars($produit['prix']) ?>"
                            placeholder="5000"
                            min="0"
                            step="any"
                            required
                        >
                    </div>

                    <!-- Localisation -->
                    <div class="champ">
                        <label><i class="fas fa-map-marker-alt"></i> Localisation</label>
                        <input
                            type="text"
                            name="localisation"
                            value="<?= htmlspecialchars($produit['localisation'] ?? '') ?>"
                            placeholder="Douala, Akwa"
                        >
                    </div>

                    <!-- WhatsApp -->
                    <div class="champ full">
                        <label><i class="fab fa-whatsapp"></i> WhatsApp</label>
                        <input
                            type="tel"
                            name="whatsapp"
                            value="<?= htmlspecialchars($produit['whatsapp'] ?? '') ?>"
                            placeholder="+237 6XX XX XX XX"
                        >
                    </div>

                    <!-- Description -->
                    <div class="champ full">
                        <label><i class="fas fa-align-left"></i> Description</label>
                        <textarea name="description" placeholder="Décrivez votre produit..."><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Image -->
                    <div class="champ full">
                        <label><i class="fas fa-image"></i> Image du produit</label>

                        <?php if (!empty($produit['image'])): ?>
                        <div class="image-actuelle">
                            <img src="<?= htmlspecialchars($produit['image']) ?>" alt="Image actuelle">
                            <div class="image-actuelle-info">
                                <p>Image actuelle</p>
                                <small>Choisissez une nouvelle image pour la remplacer</small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="upload-zone" id="upload-zone">
                            <input type="file" name="image" id="input-image" accept="image/*">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p><span>Cliquez</span> ou glissez une nouvelle image</p>
                            <small>JPEG, PNG, WebP — max 5 Mo</small>
                        </div>

                        <div id="apercu-nouvelle">
                            <img id="img-apercu" src="" alt="Aperçu">
                            <p><i class="fas fa-check-circle"></i> Nouvelle image sélectionnée</p>
                        </div>
                    </div>

                </div>

                <hr class="separator">

                <!-- Footer actions -->
                <div class="form-footer">
                    <a href="dashboard.php" class="btn btn-retour">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-sauvegarder" id="btn-save">
                        <i class="fas fa-save" id="icone-save"></i>
                        Sauvegarder les modifications
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Aperçu image
    document.getElementById('input-image').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('img-apercu').src = e.target.result;
            document.getElementById('apercu-nouvelle').style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Spinner sur submit
    document.getElementById('form-modifier').addEventListener('submit', function () {
        const btn  = document.getElementById('btn-save');
        const icon = document.getElementById('icone-save');
        btn.classList.add('loading');
        icon.className = 'fas fa-spinner';
        btn.childNodes[1].textContent = ' Sauvegarde...';
    });
</script>
</body>
</html>