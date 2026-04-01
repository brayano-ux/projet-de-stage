<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

require_once __DIR__ . '/config/index.php';

$user_id = $_SESSION['user_id'];

// Vérifier le fichier upload
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement']);
    exit;
}

$file = $_FILES['avatar'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Vérifier le type MIME
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Format non autorisé. Utilisez JPG, PNG, GIF ou WebP']);
    exit;
}

// Vérifier la taille
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (max 5MB)']);
    exit;
}

try {
    // Créer le dossier s'il n'existe pas
    $upload_dir = __DIR__ . '/uploads/avatars';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Générer un nom unique
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . '/' . $filename;
    
    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Impossible de sauvegarder le fichier');
    }
    
    // Mettre à jour la base de données
    $pdo = DatabaseConfig::getConnection();
    $update = $pdo->prepare('UPDATE utilisateurs SET avatar = ? WHERE id = ?');
    $update->execute(['uploads/avatars/' . $filename, $user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Avatar mis à jour avec succès',
        'avatar_url' => 'uploads/avatars/' . $filename
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
