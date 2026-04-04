<?php
/**
 * supprimer_produit.php
 * Reçoit une requête POST AJAX et supprime le produit
 * en vérifiant que l'utilisateur en est bien le propriétaire.
 */
session_start();
require_once __DIR__ . '/config/index.php';
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée.');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('ID produit invalide.');
    }

    // Connexion à la base de données via la configuration
    $pdo = DatabaseConfig::getConnection();

    $user_id = (int)$_SESSION['user_id'];

    // Récupérer le produit et vérifier l'appartenance
    $check = $pdo->prepare("SELECT id, image FROM produits WHERE id = ? AND utilisateur_id = ? LIMIT 1");
    $check->execute([$id, $user_id]);
    $produit = $check->fetch();

    if (!$produit) {
        throw new Exception('Produit introuvable ou accès refusé.');
    }

    // Supprimer l'image associée si elle existe
    if (!empty($produit['image'])) {
        $imagePath = __DIR__ . '/' . ltrim($produit['image'], '/');
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    // Supprimer le produit
    $del = $pdo->prepare("DELETE FROM produits WHERE id = ? AND utilisateur_id = ?");
    $del->execute([$id, $user_id]);

    $response['success'] = true;
    $response['message'] = 'Produit supprimé avec succès.';

} catch (PDOException $e) {
    error_log('[supprimer_produit.php] PDO : ' . $e->getMessage());
    $response['message'] = 'Erreur base de données.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);