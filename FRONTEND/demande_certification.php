<?php
/**
 * demande_certification.php
 * Reçoit POST AJAX → met à jour le statut de certification de la boutique
 * Retourne JSON { success, message }
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée.');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non authentifié.');
    }

    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $user_id = (int)$_SESSION['user_id'];

    // Vérifier que la boutique existe et récupérer son statut actuel
    $stmt = $pdo->prepare("
        SELECT id, certification FROM boutiques
        WHERE utilisateur_id = ? LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $boutique = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$boutique) {
        throw new Exception('Vous n\'avez pas encore de boutique.');
    }

    if ($boutique['certification'] === 'certifie') {
        throw new Exception('Votre boutique est déjà certifiée.');
    }

    if ($boutique['certification'] === 'en_attente') {
        throw new Exception('Votre demande est déjà en cours de traitement.');
    }

    // Mettre à jour le statut
    $pdo->prepare("
        UPDATE boutiques
        SET certification = 'en_attente',
            date_demande_certification = NOW()
        WHERE id = ?
    ")->execute([$boutique['id']]);

    $response['success'] = true;
    $response['message'] = 'Demande envoyée avec succès. Nous vous contacterons sous 48h.';

} catch (PDOException $e) {
    error_log('[demande_certification] PDO : ' . $e->getMessage());
    $response['message'] = 'Erreur base de données.';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;