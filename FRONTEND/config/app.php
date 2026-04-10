<?php
/**
 * Configuration de l'application
 * Définit les constantes et paramètres globaux
 */

// Environnement
define('ENV_DEV', true); // Mettre à false en production

// URLs de base
define('BASE_URL', 'http://localhost/PROJET%20DE%20STAGE/FRONTEND');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Chemins des répertoires
define('UPLOADS_PATH', __DIR__ . '/../uploads');

// Configuration des images
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Messages d'erreur
define('ERROR_MESSAGES', [
    'database' => 'Erreur de connexion à la base de données',
    'boutique_not_found' => 'Boutique introuvable',
    'product_not_found' => 'Produit introuvable',
    'upload_error' => 'Erreur lors du téléchargement de l\'image',
    'invalid_file_type' => 'Type de fichier non autorisé',
    'file_too_large' => 'Fichier trop volumineux'
]);
?>