<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'errors' => []
];

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
    
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
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
    
    if ($password !== $confirm) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
        echo json_encode($response);
        exit;
    }
    
    // VÉRIFIER SI LA TABLE utilisateurs EXISTE (au pluriel)
    $tableExists = $pdo->query("SHOW TABLES LIKE 'utilisateurs'")->fetch();
    
    if (!$tableExists) {
        $pdo->exec("
            CREATE TABLE utilisateurs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                role VARCHAR(50) DEFAULT 'vendeur',
                mot_de_passe VARCHAR(255) NOT NULL,
                statut VARCHAR(50) DEFAULT 'actif',
                date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // Verifie si email existe deja dans la table utilisateurs
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $response['errors'][] = "Cet email est déjà utilisé par un autre compte";
        echo json_encode($response);
        exit;
    }
    
    // INSÉRER LE NOUVEL UTILISATEUR
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, email, mot_de_passe) 
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$nom, $email, $hash]);
    $user_id = $pdo->lastInsertId();
        
    // Initialisation de la session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = 'vendeur';
    $_SESSION['user_nom'] = $nom;
    $_SESSION['user_email'] = $email;
    $_SESSION['has_boutique'] = false; 
    $_SESSION['boutique_id'] = null;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $response['success'] = true;
    $response['message'] = 'Inscription réussie !';
    $response['user'] = [
        'id' => $user_id,
        'nom' => $nom,
        'email' => $email,
        'role' => 'vendeur',
        'has_boutique' => false
    ];
    
    $response['redirect'] = 'creer_boutique.php';
    
} catch (PDOException $e) {
    $response['errors'][] = "Erreur base de données. Veuillez réessayer.";
    error_log("Erreur PDO inscription: " . $e->getMessage());
} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
}

echo json_encode($response);
exit;
?>