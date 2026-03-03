<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Vous devez être connecté');
    }

    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $user_id = $_SESSION['user_id'];

    // 1. Statistiques générales
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT b.id) as nombre_boutiques,
            COUNT(p.id) as total_produits,
            COUNT(CASE WHEN p.statut = 'disponible' THEN 1 END) as produits_disponibles,
            COUNT(CASE WHEN p.statut = 'vendu' THEN 1 END) as produits_vendus,
            SUM(CASE WHEN p.statut = 'disponible' THEN p.prix ELSE 0 END) as valeur_stock,
            SUM(CASE WHEN p.statut = 'vendu' THEN p.prix ELSE 0 END) as total_ventes
        FROM utilisateur u
        LEFT JOIN boutique b ON u.id = b.utilisateur_id
        LEFT JOIN produits p ON u.id = p.utilisateur_id
        WHERE u.id = ?
        GROUP BY u.id
    ");

    $stmt->execute([$user_id]);
    $stats_generales = $stmt->fetch();

    // 2. Produits récents (5 derniers)
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.nom,
            p.prix,
            p.image,
            p.statut,
            p.date_ajout,
            b.nom as nom_boutique
        FROM produits p
        INNER JOIN boutique b ON p.boutique_id = b.id
        WHERE p.utilisateur_id = ?
        ORDER BY p.date_ajout DESC
        LIMIT 5
    ");

    $stmt->execute([$user_id]);
    $produits_recents = $stmt->fetchAll();

    // 3. Informations de la boutique
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nom,
            lieu,
            adresse,
            contact,
            logo,
            date_creation
        FROM boutique
        WHERE utilisateur_id = ?
    ");

    $stmt->execute([$user_id]);
    $boutique = $stmt->fetch();

    // 4. Produits par statut (pour graphique)
    $stmt = $pdo->prepare("
        SELECT 
            statut,
            COUNT(*) as nombre
        FROM produits
        WHERE utilisateur_id = ?
        GROUP BY statut
    ");

    $stmt->execute([$user_id]);
    $produits_par_statut = $stmt->fetchAll();

    // 5. Ventes par mois (6 derniers mois)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date_ajout, '%Y-%m') as mois,
            COUNT(*) as nombre_produits,
            SUM(prix) as total
        FROM produits
        WHERE utilisateur_id = ?
        AND date_ajout >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(date_ajout, '%Y-%m')
        ORDER BY mois DESC
    ");

    $stmt->execute([$user_id]);
    $ventes_mensuelles = $stmt->fetchAll();

    // 6. Top 5 produits les plus chers
    $stmt = $pdo->prepare("
        SELECT 
            nom,
            prix,
            localisation,
            statut
        FROM produits
        WHERE utilisateur_id = ?
        ORDER BY prix DESC
        LIMIT 5
    ");

    $stmt->execute([$user_id]);
    $top_produits = $stmt->fetchAll();

    // Construire la réponse
    $response['success'] = true;
    $response['data'] = [
        'statistiques_generales' => $stats_generales,
        'produits_recents' => $produits_recents,
        'boutique' => $boutique,
        'produits_par_statut' => $produits_par_statut,
        'ventes_mensuelles' => $ventes_mensuelles,
        'top_produits' => $top_produits,
        'utilisateur' => [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['role'] ?? 'vendeur'
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = "Erreur base de données: " . $e->getMessage();
    error_log("Erreur PDO dashboard_stats: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;

/*
EXEMPLE DE RÉPONSE JSON:

{
  "success": true,
  "data": {
    "statistiques_generales": {
      "nombre_boutiques": 1,
      "total_produits": 15,
      "produits_disponibles": 12,
      "produits_vendus": 3,
      "valeur_stock": 450000,
      "total_ventes": 75000
    },
    "produits_recents": [
      {
        "id": 123,
        "nom": "Sac à main",
        "prix": 25000,
        "image": "uploads/produits/sac_123.jpg",
        "statut": "disponible",
        "date_ajout": "2025-12-27 10:30:00",
        "nom_boutique": "Ma Belle Boutique"
      }
    ],
    "boutique": {
      "id": 5,
      "nom": "Ma Belle Boutique",
      "lieu": "Douala",
      "adresse": "Akwa, Rue 123",
      "contact": "+237 6XX XX XX XX",
      "logo": "uploads/logo_5.jpg"
    }
  }
}
*/
?>