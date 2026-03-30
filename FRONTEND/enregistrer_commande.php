<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée.');
    }

    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root', '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // ── Récupérer les données POST ─────────────────────
    $nom_client  = trim($_POST['nom_client']  ?? '');
    $telephone   = trim($_POST['telephone']   ?? '');
    $quantite    = max(1, (int)($_POST['quantite']   ?? 1));
    $montant     = max(0, (float)($_POST['montant']  ?? 0));
    $note        = trim($_POST['note']        ?? '');
    $produit_id  = (int)($_POST['produit_id']  ?? 0) ?: null;
    $boutique_id = (int)($_POST['boutique_id'] ?? 0) ?: null;

    // ── Validation ─────────────────────────────────────
    if (empty($nom_client)) throw new Exception('Nom client manquant.');
    if (empty($telephone))  throw new Exception('Téléphone manquant.');
    if (!$boutique_id)      throw new Exception('Boutique invalide.');

    // ── Normalisation téléphone Cameroun ───────────────
    $telClean = preg_replace('/[\s\-\(\)]/', '', $telephone);
    if (!preg_match('/^\+?[0-9]{8,20}$/', $telClean)) {
        throw new Exception('Numéro de téléphone invalide.');
    }
    if (!str_starts_with($telClean, '+')) {
        if (str_starts_with($telClean, '237'))
            $telClean = '+' . $telClean;
        elseif (str_starts_with($telClean, '6') && strlen($telClean) === 9)
            $telClean = '+237' . $telClean;
    }

    // ── Utilisateur connecté (null si client non connecté) ──
    $utilisateur_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    // ── Insertion ──────────────────────────────────────
    $stmt = $pdo->prepare("
        INSERT INTO commandes
            (utilisateur_id, boutique_id, produit_id,
             nom_client, telephone, quantite, montant, note, statut)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, 'nouveau')
    ");
    $stmt->execute([
        $utilisateur_id,
        $boutique_id,
        $produit_id,
        $nom_client,
        $telClean,
        $quantite,
        $montant,
        $note ?: null,
    ]);

    $response['success']     = true;
    $response['message']     = 'Commande enregistrée.';
    $response['commande_id'] = (int) $pdo->lastInsertId();

} catch (PDOException $e) {
    error_log('[enregistrer_commande] PDO : ' . $e->getMessage());
    $response['message'] = 'Erreur base de données : ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;