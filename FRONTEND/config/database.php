<?php
/**
 * Configuration de la base de données
 * Fichier centralisé pour éviter les répétitions
 */

class DatabaseConfig {
    // Paramètres de connexion
    const DB_HOST = 'localhost';
    const DB_PORT = '3307';
    const DB_NAME = 'projet_de_stage';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8mb4';

    /**
     * Crée une instance PDO avec la configuration
     * @return PDO
     */
    public static function getConnection() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);

            return $pdo;
        } catch (PDOException $e) {
            // Log l'erreur et affiche un message générique
            error_log('Erreur de connexion BDD: ' . $e->getMessage());
            
            // En environnement de développement, affiche l'erreur
            if (defined('ENV_DEV') && ENV_DEV) {
                throw new PDOException('Erreur de connexion: ' . $e->getMessage());
            } else {
                throw new PDOException('Erreur de connexion à la base de données');
            }
        }
    }

    /**
     * Teste la connexion à la base de données
     * @return bool
     */
    public static function testConnection() {
        try {
            self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retourne les informations de configuration (sans les mots de passe)
     * @return array
     */
    public static function getConfigInfo() {
        return [
            'host' => self::DB_HOST,
            'port' => self::DB_PORT,
            'database' => self::DB_NAME,
            'user' => self::DB_USER,
            'charset' => self::DB_CHARSET
        ];
    }
}

/**
 * Fonction helper pour obtenir rapidement une connexion
 * @return PDO
 */
function getDB() {
    return DatabaseConfig::getConnection();
}
