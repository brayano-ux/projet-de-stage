<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/index.php';

$reponse = [
    'success' => false,
    'message' => '',
    'erreurs' => []
];


try {
        $pdo = DatabaseConfig::getConnection();
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendeur') {
        throw new Exception('Vous devez être connecté en tant que vendeur pour ajouter un produit.');
    }

    // Vérifier si l'utilisateur a une boutique
    if (!isset($_SESSION['boutique_id'])) {
        throw new Exception('Vous devez d\'abord créer une boutique avant d\'ajouter des produits.');
    }

    // Vérifier la méthode de requête
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception('Méthode non autorisée');
    }

    // Récupération des données du formulaire
    $user_id = $_SESSION['user_id'];
    $boutique_id = $_SESSION['boutique_id'];
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = trim($_POST['prix'] ?? '');
    $localisation = trim($_POST['localisation'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');

    // Validation des champs
    $erreurs = [];

    if (empty($nom)) {
        $erreurs[] = "Le nom du produit est obligatoire.";
    } elseif (strlen($nom) < 3) {
        $erreurs[] = "Le nom doit contenir au moins 3 caractères.";
    } elseif (strlen($nom) > 200) {
        $erreurs[] = "Le nom ne peut pas dépasser 200 caractères.";
    }

    if (empty($prix)) {
        $erreurs[] = "Le prix est obligatoire.";
    } elseif (!is_numeric($prix)) {
        $erreurs[] = "Le prix doit être un nombre.";
    } elseif ($prix <= 0) {
        $erreurs[] = "Le prix doit être supérieur à 0.";
    }

    if (empty($localisation)) {
        $erreurs[] = "La localisation est obligatoire.";
    } elseif (strlen($localisation) > 255) {
        $erreurs[] = "La localisation ne peut pas dépasser 255 caractères.";
    }

    if (empty($whatsapp)) {
        $erreurs[] = "Le contact WhatsApp est obligatoire.";
    } elseif (!preg_match('/^\+?[0-9\s\-\(\)]{8,20}$/', $whatsapp)) {
        $erreurs[] = "Format de numéro WhatsApp invalide.";
    }

    if (empty($description)) {
        $erreurs[] = "La description est obligatoire.";
    } elseif (strlen($description) < 10) {
        $erreurs[] = "La description doit contenir au moins 10 caractères.";
    } elseif (strlen($description) > 2000) {
        $erreurs[] = "La description ne peut pas dépasser 2000 caractères.";
    }

    // Validation de l'image (nom corrigé : 'logo' selon votre HTML)
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        $erreurs[] = "L'image du produit est obligatoire.";
    } elseif ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "L'image est trop grande",
            UPLOAD_ERR_FORM_SIZE => "L'image dépasse la taille maximale autorisée",
            UPLOAD_ERR_PARTIAL => "L'image n'a été que partiellement téléchargée",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
            UPLOAD_ERR_CANT_WRITE => "Échec de l'écriture sur le disque",
            UPLOAD_ERR_EXTENSION => "Upload stoppé par une extension PHP"
        ];
        $erreurs[] = $uploadErrors[$_FILES['logo']['error']] ?? "Erreur lors du téléchargement de l'image";
    }

    // Si erreurs de validation, arrêter
    if (!empty($erreurs)) {
        $reponse['erreurs'] = $erreurs;
        echo json_encode($reponse);
        exit;
    }

    // Gestion de l'upload de l'image
    $image = $_FILES['logo'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Vérifier le type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $image['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        $erreurs[] = "Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF, WebP";
    }

    if ($image['size'] > $maxSize) {
        $erreurs[] = "L'image est trop grande. Maximum: 5MB";
    }

    if (!getimagesize($image['tmp_name'])) {
        $erreurs[] = "Le fichier n'est pas une image valide";
    }

    // Si erreurs après validation de l'image, arrêter
    if (!empty($erreurs)) {
        $reponse['erreurs'] = $erreurs;
        echo json_encode($reponse);
        exit;
    }

    // Créer le dossier uploads s'il n'existe pas
    $dossier_upload = __DIR__ . '/uploads/produits/';
    if (!is_dir($dossier_upload)) {
        mkdir($dossier_upload, 0755, true);
    }

    // Créer un fichier .htaccess pour sécuriser le dossier
    $htaccessPath = $dossier_upload . '.htaccess';
    if (!file_exists($htaccessPath)) {
        file_put_contents($htaccessPath, "php_flag engine off\nAddType application/octet-stream .php");
    }

    // Générer un nom unique pour l'image
    $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $nom_fichier = uniqid('produit_', true) . '_' . time() . '.' . $extension;
    $chemin_relatif = 'uploads/produits/' . $nom_fichier;
    $chemin_final = __DIR__ . '/' . $chemin_relatif;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($image['tmp_name'], $chemin_final)) {
        $erreurs[] = "Erreur lors du téléchargement de l'image.";
        $reponse['erreurs'] = $erreurs;
        echo json_encode($reponse);
        exit;
    }

    // Définir les permissions appropriées
    chmod($chemin_final, 0644);

    // Démarrer une transaction
    $pdo->beginTransaction();

    try {
        // Vérifier que la table produits existe, sinon la créer
        $tableExists = $pdo->query("SHOW TABLES LIKE 'produits'")->fetch();
        
        if (!$tableExists) {
            $pdo->exec("
                CREATE TABLE produits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    boutique_id INT NOT NULL,
                    utilisateur_id INT NOT NULL,
                    nom VARCHAR(200) NOT NULL,
                    description TEXT NOT NULL,
                    prix DECIMAL(10,2) NOT NULL,
                    localisation VARCHAR(255) NOT NULL,
                    whatsapp VARCHAR(20) NOT NULL,
                    image VARCHAR(255) NOT NULL,
                    statut VARCHAR(50) DEFAULT 'disponible',
                    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (boutique_id) REFERENCES boutique(id) ON DELETE CASCADE,
                    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
                    INDEX idx_boutique (boutique_id),
                    INDEX idx_utilisateur (utilisateur_id),
                    INDEX idx_statut (statut)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Insérer le produit dans la base de données
        $stmt = $pdo->prepare("
            INSERT INTO produits 
            (boutique_id, utilisateur_id, nom, description, prix, localisation, whatsapp, image, date_ajout)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $boutique_id,
            $user_id,
            $nom,
            $description,
            $prix,
            $localisation,
            $whatsapp,
            $chemin_relatif
        ]);

        $produit_id = $pdo->lastInsertId();

        // Valider la transaction
        $pdo->commit();

        $reponse['success'] = true;
        $reponse['message'] = '🎉 Produit ajouté avec succès !';
        $reponse['produit_id'] = $produit_id;
        $reponse['redirect'] = 'mes_produits.php';

    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        
        // Supprimer l'image uploadée
        if (file_exists($chemin_final)) {
            unlink($chemin_final);
        }
        
        throw $e;
    }

} catch (PDOException $e) {
    $reponse['message'] = "Erreur base de données: " . $e->getMessage();
    error_log("Erreur PDO ajouter_produit: " . $e->getMessage());
} catch (Exception $e) {
    $reponse['message'] = $e->getMessage();
    error_log("Erreur ajouter_produit: " . $e->getMessage());
}

echo json_encode($reponse);
exit;
?>