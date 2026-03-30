<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false,'errors' => []];

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root',
        '', 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['passworde'] ?? '';
    $confirm = $_POST['nouveau_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    $errors = [];
    
    if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
        $errors[] = "Erreur de sécurité. Veuillez recharger la page.";
    }
    
    // Validation nom
    if (empty($nom)) {
        $errors[] = "Le nom d'utilisateur est requis";
    } elseif (strlen($nom) < 3) {
        $errors[] = "Le nom doit contenir au moins 3 caractères";
    } elseif (strlen($nom) > 50) {
        $errors[] = "Le nom ne peut pas dépasser 50 caractères";
    }
    
    // Validation email
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }
    
    // Validation mot de passe
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une minuscule";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins un chiffre";
    }
    
    // Validation confirmation
    if ($password !== $confirm) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit;
    }
    
    // Verifie si email existe deja
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $response['errors'][] = "Cet email est déjà utilisé par un autre compte";
        echo json_encode($response);
        exit;
    }
    
    // VÉRIFIER SI LA TABLE EXISTE, SINON LA CRÉER
    $tableExists = $pdo->query("SHOW TABLES LIKE 'utilisateur'")->fetch();
    
    
    // INSÉRER LE NOUVEL UTILISATEUR
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, email, mot_de_passe) 
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$nom, $email, $hash]);
    $user_id = $pdo->lastInsertId();
    
    // VÉRIFIER SI L'UTILISATEUR A UNE BOUTIQUE
    $stmt = $pdo->prepare("SELECT id FROM boutiques WHERE utilisateur_id = ?");
    $stmt->execute([$user_id]);
    $boutique = $stmt->fetch();
    
    // session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'vendeur';
    $_SESSION['user_nom'] = $nom;
    $_SESSION['user_email'] = $email;
    $_SESSION['has_boutique'] = $boutique ? true : false;
    $_SESSION['boutique_id'] = $boutique ? $boutique['id'] : null;
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    $response['success'] = true;
    $response['message'] = 'Inscription réussie !';
    $response['user'] = [
        'id' => $user_id,
        'nom' => $nom,
        'email' => $email,
        'role' => 'vendeur',
        'has_boutique' => $_SESSION['has_boutique']
    ];
    
    // Redirection suggérée
    $response['redirect'] = $_SESSION['has_boutique'] ? 'dashboard.php' : 'creer_boutique.php';
    
} catch (PDOException $e) {
    $response['errors'][] = "Erreur base de données : " . $e->getMessage();
    error_log("Erreur PDO inscription: " . $e->getMessage());
} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
}

// ENVOYER LA RÉPONSE
echo json_encode($response);
exit;
?>