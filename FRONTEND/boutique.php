<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => '', 'errors' => []];

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'vendeur') {
        throw new Exception('Accès refusé. Veuillez vous connecter en tant que vendeur.');
    }
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        throw new Exception('Token de sécurité invalide. Veuillez recharger la page.');
    }
    $pdo = new PDO(
        "mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS boutiques (
            id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id  INT UNSIGNED NOT NULL,
            nom             VARCHAR(200)  NOT NULL,
            adresse         VARCHAR(500)  NOT NULL,
            whatsapp        VARCHAR(30)   NOT NULL,
            services        VARCHAR(255)  DEFAULT NULL,
            description     TEXT          DEFAULT NULL,
            logo            VARCHAR(300)  DEFAULT NULL,
            date_creation   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_utilisateur (utilisateur_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $user_id = (int) $_SESSION['user_id'];

    $check = $pdo->prepare("SELECT id FROM boutiques WHERE utilisateur_id = ? LIMIT 1");
    $check->execute([$user_id]);
    if ($check->fetch()) {
        $response['errors'][] = "Vous avez déjà créé une boutique.";
        echo json_encode($response);
        exit;
    }

    $nom         = trim($_POST['nom']         ?? '');
    $adresse     = trim($_POST['adresse']     ?? '');
    $whatsapp    = trim($_POST['whatsapp']    ?? '');
    $services    = trim($_POST['services']    ?? '');  
    $description = trim($_POST['description'] ?? '');
    $errors = [];

    if (empty($nom)) {
        $errors[] = "Le nom de la boutique est obligatoire.";
    } elseif (strlen($nom) < 3) {
        $errors[] = "Le nom doit contenir au moins 3 caractères.";
    } elseif (strlen($nom) > 200) {
        $errors[] = "Le nom ne peut pas dépasser 200 caractères.";
    }

    if (empty($adresse)) {
        $errors[] = "L'adresse est obligatoire.";
    } elseif (strlen($adresse) > 500) {
        $errors[] = "L'adresse ne peut pas dépasser 500 caractères.";
    }

    if (empty($whatsapp)) {
        $errors[] = "Le numéro WhatsApp est obligatoire.";
    } else {
        $whatsappClean = preg_replace('/[\s\-\(\)]/', '', $whatsapp);
        if (!preg_match('/^\+?[0-9]{8,20}$/', $whatsappClean)) {
            $errors[] = "Format de numéro WhatsApp invalide. Exemple : +237 6XX XX XX XX";
        } else {
            if (!str_starts_with($whatsappClean, '+')) {
                if (str_starts_with($whatsappClean, '237')) {
                    $whatsappClean = '+' . $whatsappClean;
                } elseif (str_starts_with($whatsappClean, '6') && strlen($whatsappClean) === 9) {
                    $whatsappClean = '+237' . $whatsappClean;
                }
            }
            $whatsapp = $whatsappClean;
        }
    }

    if (!empty($services) && strlen($services) > 255) {
        $errors[] = "Le lieu ne peut pas dépasser 255 caractères.";
    }

    if (!empty($description) && strlen($description) > 2000) {
        $errors[] = "La description ne peut pas dépasser 2000 caractères.";
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit;
    }

    $logoPath = null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {

        $maxSize      = 5 * 1024 * 1024; 
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeToExt    = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if ($_FILES['logo']['size'] > $maxSize) {
            $errors[] = "L'image est trop grande. Maximum : 5 Mo.";
        }
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = "Format non autorisé. Formats acceptés : JPEG, PNG, GIF, WebP.";
        }

        if (empty($errors) && !getimagesize($_FILES['logo']['tmp_name'])) {
            $errors[] = "Le fichier n'est pas une image valide.";
        }

        if (empty($errors)) {
            $uploadDir = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception("Impossible de créer le dossier d'upload.");
                }
            }

            $htaccessPath = $uploadDir . '.htaccess';
            if (!file_exists($htaccessPath)) {
                file_put_contents(
                    $htaccessPath,
                    "php_flag engine off\nAddType application/octet-stream .php .php3 .php4 .php5 .phtml .phar"
                );
            }

            $extension  = $mimeToExt[$mimeType];
            $uniqueName = 'logo_' . uniqid('', true) . '_' . time() . '.' . $extension;
            $fullPath   = $uploadDir . $uniqueName;
            $logoPath   = 'uploads/' . $uniqueName;

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $fullPath)) {
                throw new Exception("Erreur lors du déplacement du fichier uploadé.");
            }

            chmod($fullPath, 0644);
        }

    } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => "Le fichier dépasse la limite autorisée par le serveur.",
            UPLOAD_ERR_FORM_SIZE  => "Le fichier dépasse la limite du formulaire.",
            UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléchargé.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire introuvable.",
            UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque.",
            UPLOAD_ERR_EXTENSION  => "Upload bloqué par une extension PHP.",
        ];
        $errors[] = $uploadErrors[$_FILES['logo']['error']] ?? "Erreur inconnue lors du téléchargement.";
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit;
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO boutiques
                (utilisateur_id, nom, adresse, whatsapp, services, description, logo, date_creation)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $user_id,       
            $nom,           
            $adresse,       
            $whatsapp,      
            $services,   
            $description,
            $logoPath   
        ]);

        $boutique_id = (int) $pdo->lastInsertId();
        $pdo->commit();

        unset($_SESSION['csrf_token']);
        $_SESSION['has_boutique'] = true;
        $_SESSION['boutique_id']  = $boutique_id;

        $response['success']     = true;
        $response['message']     = 'Votre boutique a été créée avec succès !';
        $response['boutique_id'] = $boutique_id;
        $response['redirect']    = 'ajout_produits.php';

    } catch (Exception $e) {
        $pdo->rollBack();

        if ($logoPath && file_exists(__DIR__ . '/' . $logoPath)) {
            unlink(__DIR__ . '/' . $logoPath);
        }

        throw $e;
    }

} catch (PDOException $e) {
    error_log("[boutique.php] Erreur PDO : " . $e->getMessage());
    $response['message'] = "Erreur base de données. Veuillez réessayer.";

} catch (Exception $e) {
    error_log("[boutique.php] Erreur : " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>