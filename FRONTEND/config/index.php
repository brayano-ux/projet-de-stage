<?php
/**
 * Point d'entrée pour la configuration
 * Charge tous les fichiers de configuration
 */

// Sécurité : empêche l'accès direct
if (!defined('APP_LOADED')) {
    define('APP_LOADED', true);

    // Charger la configuration principale
    require_once __DIR__ . '/app.php';
    
    // Charger la configuration de la base de données
    require_once __DIR__ . '/database.php';
}
?>