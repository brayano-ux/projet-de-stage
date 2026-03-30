<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/index.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

if ($nom === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'Nom et e-mail requis']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'E-mail invalide']);
    exit;
}

try {
    $pdo = DatabaseConfig::getConnection();
    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ? AND id <> ?');
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cet e-mail est déjà utilisé']);
        exit;
    }

    $sql = 'UPDATE utilisateurs SET nom = ?, email = ?';
    $params = [$nom, $email];

    if ($mot_de_passe !== '') {
        $sql .= ', mot_de_passe = ?';
        $params[] = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    }

    $sql .= ' WHERE id = ?';
    $params[] = $_SESSION['user_id'];

    $update = $pdo->prepare($sql);
    $update->execute($params);

    echo json_encode(['success' => true, 'message' => 'Profil mis à jour']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
