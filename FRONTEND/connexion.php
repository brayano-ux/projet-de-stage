<?php
//connexion.php
session_start();
$message = '';
$type = ''; 
if(isset($_POST['submit'])){
    $nom = $_POST['nom'];
    $password = $_POST['password'];

    try{
        $pdo = new PDO(
            'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
            'root',
            '', 
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE nom = :nom");
        $stmt->bindParam(':nom', $nom);
        $stmt->execute();
        $user = $stmt->fetch();

        
        if($user && password_verify($password, $user['mot_de_passe'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $message = "Connexion réussie !";
            $type = "succes";
            header("location: dashbord.php");
            exit; 
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
            $type = "error";
        }
    } catch (PDOException $e) {
        $message = "Erreur de connexion à la base de données : " . $e->getMessage();
        $type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome-free-7.0.1-web/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .error{
            color: red;
        }
        .succes{
            color: green;
        }
    </style>
</head>
<body>
 <form action="" method="post">
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-7 position-relative p-3 overflow-hidden">
                <div id="carouselExampleSlidesOnly"  class="carousel slide h-100" data-bs-ride="carousel">
                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100  border">
                            <img  src="https://tse2.mm.bing.net/th/id/OIP.VvA3gwdx3FHAd4AZsp0dRwHaE6?cb=defcachec2&rs=1&pid=ImgDetMain&o=7&rm=3" class="d-block w-100 h-100" style="object-fit: cover; filter: brightness(0.85);" alt="Bibliothèque">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.3), rgba(108, 117, 125, 0.2));"></div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="https://images.pexels.com/photos/3213283/pexels-photo-3213283.jpeg?cs=srgb&dl=pexels-omotayo-tajudeen-1650120-3213283.jpg&fm=jpg" class="d-block w-100 h-100" style="object-fit: cover; filter: brightness(0.85);" alt="Livres">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.3), rgba(108, 117, 125, 0.2));"></div>
                        </div>
                        <div class="carousel-item h-100">
                            <img src="https://groupe-routage.fr/uploads/marketplace_04c44eae29.jpg" class="d-block w-100 h-100" style="object-fit: cover; filter: brightness(0.85);" alt="Lecture">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.3), rgba(108, 117, 125, 0.2));"></div>
                        </div>
                    </div>
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#carouselExampleSlidesOnly" data-bs-slide-to="2"></button>
                    </div>
                </div>
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 10;">
                    <h1 class="display-4 fw-bold mb-3" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.5);">Creation  de boutique en ligne</h1>
                    <p class="fs-5" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Crez votre boutique en 5 minutes</p>
                </div>
            </div>
            
            <div class="col-lg-5 d-flex align-items-center justify-content-center bg-light p-4">
                <div class="card shadow-lg border-0 p-4" style="width: 100%; max-width: 450px; border-radius: 15px;">
                    <div class="text-center mb-5">
                        <i class="fas fa-book-open fs-1 text-primary mb-3"></i>
                        <h3 class="card-title fs-4 fw-bold text-dark">
                            Bienvenue dans la page de <br> connexion
                        </h3>
                        <p class="text-muted small">Connectez-vous à votre compte</p>
                    </div>

                    <div class="card-body p-0">
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-user text-secondary"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0" 
                                    placeholder="Entrez votre username"
                                    name="nom"
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-secondary"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control border-start-0" 
                                    placeholder="Entrez votre password"
                                    name="password"
                                >
                             </div>
                         <div class="">
                            <div class="d-flex align-items-center mt-2">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input mb-3 mt-0" 
                                    aria-label="Checkbox for following text input"
                                >
                                <label for="" class="text-muted">Se souvenir de moi</label>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            class="btn btn-primary w-100 fw-bold py-2 mb-3"
                            name="submit"
                        >
                            <span class="text-white border-end-0">
                                <i class="fas fa-sign-in-alt"></i>
                            </span>
                            Se connecter
                        </button>
                        
                       <?php if(!empty($message)):?>
    <div class="<?= $type ?> text-center fw-bold mb-3">
        <?= $message ?>
    </div>
<?php endif;?>

                        <div class="text-center">
                            <p class="text-muted small">
                                Pas encore inscrit? 
                                <a href="inscription.php" class="text-decoration-none text-primary fw-bold">S'inscrire</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="fontawesome-free-7.0.1-web/js/all.min.js" crossorigin="anonymous"></script>
</body>
</html>