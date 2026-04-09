<?php
// connexion.php - Version corrigée et sécurisée
session_start();

// Si déjà connecté, rediriger vers dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$type = '';

if (isset($_POST['submit'])) {
    // Validation et sanitization des inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Validation basique
    if (!$email || empty($password)) {
        $message = "Veuillez remplir tous les champs correctement.";
        $type = "error";
    } else {
        try {
            // Utilisation de la configuration centralisée
            require_once __DIR__ . '/config/index.php';
            $pdo = DatabaseConfig::getConnection();

            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email AND statut = 'actif'");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Régénération de l'ID de session pour prévenir les attaques
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['nom'];
                $_SESSION['login_time'] = time();
                
                // Journalisation de la connexion
                $stmt_log = $pdo->prepare("INSERT INTO connexions (utilisateur_id, ip_address, date_connexion) VALUES (?, ?, NOW())");
                $stmt_log->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
                
                header('Location: dashboard.php');
                exit;
            } else {
                $message = "Email ou mot de passe incorrect.";
                $type = "error";
                
                // Journalisation de la tentative échouée
                $stmt_fail = $pdo->prepare("INSERT INTO tentatives_connexion (email, ip_address, date_tentative) VALUES (?, ?, NOW())");
                $stmt_fail->execute([$email, $_SERVER['REMOTE_ADDR']]);
            }
        } catch (PDOException $e) {
            error_log("Erreur connexion BDD: " . $e->getMessage());
            $message = "Erreur technique. Veuillez réessayer plus tard.";
            $type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Creator Market</title>
    
    <!-- Bootstrap 5 (une seule version) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .carousel-item img {
            object-fit: cover;
            filter: brightness(0.85);
        }
        
        .gradient-overlay {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.3), rgba(108, 117, 125, 0.2));
        }
        
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .input-group-text {
            background-color: white;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        @media (max-width: 768px) {
            .col-lg-7 {
                display: none !important;
            }
            
            .col-lg-5 {
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }
        }
    </style>
</head>
<body>
    <form action="" method="post" id="loginForm">
        <div class="container-fluid p-0">
            <div class="row g-0 min-vh-100">
                <!-- Section Carousel -->
                <div class="col-lg-7 position-relative p-3 overflow-hidden d-none d-lg-block">
                    <div id="carouselExampleSlidesOnly" class="carousel slide h-100" data-bs-ride="carousel">
                        <div class="carousel-inner h-100">
                            <div class="carousel-item active h-100">
                                <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="d-block w-100 h-100" alt="Marketplace">
                                <div class="position-absolute top-0 start-0 w-100 h-100 gradient-overlay"></div>
                            </div>
                            <div class="carousel-item h-100">
                                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="d-block w-100 h-100" alt="Boutique">
                                <div class="position-absolute top-0 start-0 w-100 h-100 gradient-overlay"></div>
                            </div>
                            <div class="carousel-item h-100">
                                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="d-block w-100 h-100" alt="E-commerce">
                                <div class="position-absolute top-0 start-0 w-100 h-100 gradient-overlay"></div>
                            </div>
                        </div>
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide-to="2"></button>
                        </div>
                    </div>
                    <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 10;">
                        <h1 class="display-4 fw-bold mb-3" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.5);">
                            Creator Market
                        </h1>
                        <p class="fs-5" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">
                            Créez votre boutique en ligne en 5 minutes
                        </p>
                    </div>
                </div>
                
                <!-- Section Formulaire -->
                <div class="col-lg-5 d-flex align-items-center justify-content-center bg-light p-4">
                    <div class="card login-card p-4" style="width: 100%; max-width: 450px;">
                        <div class="text-center mb-5">
                            <i class="fas fa-store fs-1 text-primary mb-3"></i>
                            <h3 class="card-title fs-4 fw-bold text-dark">
                                Connexion à votre<br>espace Creator
                            </h3>
                            <p class="text-muted small">Accédez à votre boutique</p>
                        </div>

                        <div class="card-body p-0">
                            <!-- Messages d'erreur/succès -->
                            <?php if (!empty($message)): ?>
                                <div class="<?= $type === 'error' ? 'error' : 'success' ?> text-center fw-bold mb-3">
                                    <?= htmlspecialchars($message) ?>
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope text-secondary"></i>
                                    </span>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        placeholder="Entrez votre email"
                                        name="email"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock text-secondary"></i>
                                    </span>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        placeholder="Entrez votre mot de passe"
                                        name="password"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input" 
                                        id="remember"
                                        name="remember"
                                    >
                                    <label class="form-check-label text-muted small" for="remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                                <a href="forgot-password.php" class="text-primary text-decoration-none small">
                                    Mot de passe oublié?
                                </a>
                            </div>

                            <button 
                                type="submit" 
                                class="btn btn-primary w-100 fw-bold py-2 mb-3"
                                name="submit"
                            >
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>

                            <div class="text-center">
                                <p class="text-muted small mb-0">
                                    Pas encore de compte? 
                                    <a href="inscription.php" class="text-decoration-none text-primary fw-bold">
                                        S'inscrire gratuitement
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validation côté client
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
                return false;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide');
                return false;
            }
        });
        
        // Animation du carousel
        const carousel = new bootstrap.Carousel(document.getElementById('carouselExampleSlidesOnly'), {
            interval: 4000,
            ride: 'carousel'
        });
    </script>
</body>
</html>
