<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
$response = ['success' => false, 'message' => '', 'action' => ''];

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root', '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $utilisateur_id = $_SESSION['user_id'] ?? null;
    $produit_id     = isset($_POST['boutique_id']) ? (int) $_POST['boutique_id'] : null;

    // Vérifications
    if (!$utilisateur_id) {
        throw new Exception('Vous devez être connecté pour ajouter un favori');
    }
    if (!$produit_id || $produit_id <= 0) {
        throw new Exception('Identifiant de produit invalide');
    }

    // Vérifier que le produit existe
    $check = $pdo->prepare("SELECT boutique_id FROM produits WHERE id = ?");
    $check->execute([$produit_id]);
    $produit = $check->fetch();
    if (!$produit) {
        throw new Exception('Produit introuvable');
    }
    $boutique_id = $produit['boutique_id'];

    // Toggle favori : vérifier d'abord AVANT toute action
    $check_fav = $pdo->prepare(
        "SELECT id FROM favoris WHERE utilisateur_id = ? AND boutique_id = ?"
    );
    $check_fav->execute([$utilisateur_id, $produit_id]);

    if ($check_fav->fetch()) {
        // Déjà en favori → supprimer
        $pdo->prepare(
            "DELETE FROM favoris WHERE utilisateur_id = ? AND boutique_id = ?"
        )->execute([$utilisateur_id, $produit_id]);

        $response['message'] = 'Retiré des favoris';
        $response['action']  = 'removed';
    } else {
        // Pas encore en favori → ajouter
        $pdo->prepare(
            "INSERT INTO favoris (utilisateur_id, boutique_id) VALUES (?, ?)"
        )->execute([$utilisateur_id, $produit_id]);

        $response['message'] = 'Ajouté aux favoris';
        $response['action']  = 'added';
    }

    // Recalcul du compteur dans la table boutiques
    $pdo->prepare(
        "UPDATE boutiques SET total_favoris = 
        (SELECT COUNT(*) FROM favoris WHERE boutique_id = ?) WHERE id = ?"
    )->execute([$produit_id, $boutique_id]);

    $response['success'] = true;

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données : ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);