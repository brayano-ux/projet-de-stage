<?php
// Récupérer le slug de la boutique
$slug = $_GET['b'] ?? '';
if (empty($slug)) {
    die("Boutique non trouvée");
}

$pdo = new PDO(
    'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Récupérer les infos de la boutique
$stmt = $pdo->prepare("SELECT * FROM boutiques WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$boutique = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$boutique) {
    die("Boutique non trouvée");
}

// Tracker de visiteurs - Appel AJAX
?>
<script>
// Suivre la visite
fetch('tracker_visiteur.php?boutique_id=<?php echo $boutique['id']; ?>')
    .then(response => response.json())
    .then(data => console.log('Visite suivie:', data))
    .catch(error => console.error('Erreur:', error));
</script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($boutique['nom']); ?> - Creator Market</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 150px; border-radius: 10px; margin-bottom: 20px; }
        .nom { font-size: 2em; color: #333; margin-bottom: 10px; }
        .description { color: #666; max-width: 600px; margin: 0 auto; }
        .contact { margin-top: 20px; }
        .contact a { color: #25D366; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($boutique['logo']): ?>
            <img src="<?php echo $boutique['logo']; ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <h1 class="nom"><?php echo htmlspecialchars($boutique['nom']); ?></h1>
        <p class="description"><?php echo nl2br(htmlspecialchars($boutique['description'])); ?></p>
        <div class="contact">
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $boutique['whatsapp']); ?>" target="_blank">
                <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($boutique['whatsapp']); ?>
            </a>
        </div>
    </div>
</body>
</html>
