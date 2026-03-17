<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Générer le token s'il n'existe pas encore
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function reponseJSON($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

try {
    // 1. CONNEXION PDO
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        reponseJSON(false, 'Méthode non autorisée');
    }

    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        reponseJSON(false, 'Token de sécurité invalide');
    }

    // 2. CRÉATION DE LA TABLE EN PREMIER (Avant toute requête SELECT)
    $tableExists = $pdo->query("SHOW TABLES LIKE 'boutiques'")->fetch();
    if (!$tableExists) {
        $pdo->exec("
            CREATE TABLE boutiques (
                id INT AUTO_INCREMENT PRIMARY KEY,
                utilisateur_id INT NOT NULL,
                nom VARCHAR(100) NOT NULL,
                adresse VARCHAR(255) NOT NULL,
                whatsapp VARCHAR(20) NOT NULL,
                services TEXT,
                description TEXT,
                logo VARCHAR(255),
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // 3. RÉCUPÉRATION ET VALIDATION DES DONNÉES
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $services = trim($_POST['services'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date_creation = date('Y-m-d H:i:s');

    if (empty($nom) || empty($adresse) || empty($whatsapp)) {
        reponseJSON(false, 'Tous les champs obligatoires doivent être remplis');
    }

    if (strlen($nom) > 100) {
        reponseJSON(false, 'Le nom de la boutique est trop long (max 100 caractères)');
    }

    if (strlen($adresse) > 255) {
        reponseJSON(false, 'L\'adresse est trop longue (max 255 caractères)');
    }

    $whatsapp = preg_replace('/\s+/', '', $whatsapp); 

    if (!preg_match('/^(\+?237)?6[0-9]{8}$/', $whatsapp)) {
        reponseJSON(false, 'Numéro WhatsApp invalide. Format attendu: +237 6XX XX XX XX');
    }

    if (!str_starts_with($whatsapp, '+')) {
        $whatsapp = (!str_starts_with($whatsapp, '237')) ? '+237' . $whatsapp : '+' . $whatsapp;
    }

    // 4. VÉRIFICATION SI LA BOUTIQUE EXISTE (Maintenant que la table existe, ça va marcher !)
    $stmt = $pdo->prepare('SELECT id FROM boutiques WHERE nom = ? OR whatsapp = ?');
    $stmt->execute([$nom, $whatsapp]);
    if ($stmt->fetch()) {
        reponseJSON(false, 'Une boutique avec ce nom ou ce numéro WhatsApp existe déjà');
    }

    // 5. GESTION DE L'UPLOAD DE L'IMAGE
    $image_url = null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {

        $taille_max = 5 * 1024 * 1024; // 5MB
        if ($_FILES['logo']['size'] > $taille_max) {
            reponseJSON(false, 'Le logo est trop volumineux (maximum 5MB)');
        }

        $types_autorises = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['logo']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $types_autorises)) {
            reponseJSON(false, 'Format d\'image non autorisé. Utilisez JPG, PNG, GIF ou WebP');
        }

        $dossier_upload = __DIR__ . '/uploads/';
        if (!is_dir($dossier_upload)) {
            if (!mkdir($dossier_upload, 0755, true)) {
                reponseJSON(false, 'Impossible de créer le dossier de téléchargement');
            }
        }

        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $nom_fichier = uniqid('logo_', true) . '.' . $extension;
        $chemin_final = $dossier_upload . $nom_fichier;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $chemin_final)) {
            $image_url = 'uploads/' . $nom_fichier;
            redimensionnerImage($chemin_final, 800, 800);
        } else {
            reponseJSON(false, 'Erreur lors du téléchargement du logo');
        }
    }

    $utilisateur_id = $_SESSION['user_id'] ?? null;

    if (!$utilisateur_id) {
        reponseJSON(false, 'Utilisateur non identifié. Veuillez vous connecter.');
    }

    // 6. INSERTION DANS LA BASE DE DONNÉES
    $stmt = $pdo->prepare('
        INSERT INTO boutiques (utilisateur_id, nom, adresse, whatsapp, services, description, logo, date_creation)
        VALUES (:utilisateur_id, :nom, :adresse, :whatsapp, :services, :description, :logo, :date_creation)
    ');

    $resultat = $stmt->execute([
        ':utilisateur_id' => $utilisateur_id,
        ':nom' => $nom,
        ':adresse' => $adresse,
        ':whatsapp' => $whatsapp,
        ':services' => $services,
        ':description' => $description,
        ':logo' => $image_url,
        ':date_creation' => $date_creation
    ]);

    if ($resultat) {
        $boutique_id = $pdo->lastInsertId();

        // Mise à jour de la session
        $_SESSION['boutique_id'] = $boutique_id;
        $_SESSION['boutique_nom'] = $nom;
        $_SESSION['has_boutique'] = true;

        reponseJSON(true, 'Votre boutique a été enregistrée avec succès ! Redirection...', [
            'boutique_id' => $boutique_id
        ]);
    } else {
        reponseJSON(false, 'Erreur lors de l\'enregistrement de la boutique');
    }

} catch (PDOException $e) {
    error_log('Erreur PDO: ' . $e->getMessage());
    reponseJSON(false, 'Erreur de base de données. Veuillez réessayer plus tard');
} catch (Exception $e) {
    error_log('Erreur: ' . $e->getMessage());
    reponseJSON(false, 'Une erreur est survenue. Veuillez réessayer');
}

// Fonction redimensionnerImage
function redimensionnerImage($chemin, $largeur_max, $hauteur_max) {
    list($largeur_orig, $hauteur_orig, $type) = getimagesize($chemin);
    $ratio = min($largeur_max / $largeur_orig, $hauteur_max / $hauteur_orig);

    if ($ratio < 1) {
        $nouvelle_largeur = (int)($largeur_orig * $ratio);
        $nouvelle_hauteur = (int)($hauteur_orig * $ratio);
        $image_redim = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $image_source = imagecreatefromjpeg($chemin);
                break;
            case IMAGETYPE_PNG:
                $image_source = imagecreatefrompng($chemin);
                imagealphablending($image_redim, false);
                imagesavealpha($image_redim, true);
                break;
            case IMAGETYPE_GIF:
                $image_source = imagecreatefromgif($chemin);
                break;
            default:
                return false;
        }

        imagecopyresampled($image_redim, $image_source, 0, 0, 0, 0,
            $nouvelle_largeur, $nouvelle_hauteur, $largeur_orig, $hauteur_orig);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image_redim, $chemin, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($image_redim, $chemin, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($image_redim, $chemin);
                break;
        }

        imagedestroy($image_source);
        imagedestroy($image_redim);
    }
    return true;
    error_log("SESSION token: " . ($_SESSION['csrf_token'] ?? 'VIDE'));
error_log("POST token: " . ($_POST['csrf_token'] ?? 'VIDE'));
}
?>