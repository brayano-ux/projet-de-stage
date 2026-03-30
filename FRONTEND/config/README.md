# 📁 Configuration de Creator Market

Ce dossier contient tous les fichiers de configuration centralisés pour l'application Creator Market.

## 🗂️ Structure des fichiers

```
config/
├── index.php        # Point d'entrée principal
├── app.php          # Configuration générale de l'application
├── database.php     # Configuration de la base de données
└── README.md        # Documentation
```

## 🔧 Utilisation

### Inclure la configuration dans vos fichiers PHP

```php
<?php
require_once __DIR__ . '/config/index.php';

// Maintenant vous pouvez utiliser:
// - DatabaseConfig::getConnection() pour la BDD
// - Les constantes définies dans app.php
?>
```

### Obtenir une connexion à la base de données

```php
<?php
try {
    $pdo = DatabaseConfig::getConnection();
    // Utiliser $pdo pour vos requêtes
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
?>
```

### Fonction helper

```php
<?php
$pdo = getDB(); // Alternative rapide
?>
```

## 📋 Configuration de la base de données

Les paramètres sont définis dans `database.php` :

- **Host**: localhost
- **Port**: 3307
- **Database**: projet_de_stage
- **User**: root
- **Password**: (vide)
- **Charset**: utf8mb4

### Méthodes disponibles

```php
// Connexion standard
$pdo = DatabaseConfig::getConnection();

// Test de connexion
$isConnected = DatabaseConfig::testConnection();

// Informations de configuration (sans mot de passe)
$config = DatabaseConfig::getConfigInfo();
```

## 🌍 Configuration de l'application

Dans `app.php`, vous trouverez :

### Constantes de l'application
- `APP_NAME`: Nom de l'application
- `APP_URL`: URL de base
- `APP_VERSION`: Version actuelle

### Chemins
- `ROOT_PATH`: Chemin racine du projet
- `UPLOAD_PATH`: Dossier des uploads
- `UPLOAD_URL`: URL des uploads

### Uploads
- `MAX_FILE_SIZE`: Taille maximale des fichiers (5MB)
- `ALLOWED_EXTENSIONS`: Extensions autorisées

### Environnement
- `ENV_DEV`: Mode développement/production
- Configuration des erreurs et sessions

## 🔒 Sécurité

### Configuration de la base de données
- Utilisation de PDO avec prepared statements
- Gestion des erreurs sécurisée
- Protection contre les injections SQL

### Sessions
- `session.cookie_httponly = 1`
- `session.use_only_cookies = 1`
- `session.cookie_samesite = 'Strict'`

### Uploads
- Validation des types MIME
- Taille maximale contrôlée
- Extensions autorisées uniquement

## 🚀 Avantages de cette configuration

### 1. **Centralisation**
- Tous les paramètres au même endroit
- Facile à maintenir et mettre à jour

### 2. **Sécurité**
- Pas de mots de passe en dur dans les fichiers
- Gestion centralisée des erreurs

### 3. **Flexibilité**
- Facile de changer d'environnement
- Configuration modulaire

### 4. **Réutilisabilité**
- Code DRY (Don't Repeat Yourself)
- Helper functions disponibles

## 📝 Exemple d'utilisation complète

```php
<?php
require_once __DIR__ . '/config/index.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connexion sécurisée à la BDD
try {
    $pdo = DatabaseConfig::getConnection();
    
    // Requête sécurisée avec prepared statement
    $stmt = $pdo->prepare("SELECT * FROM boutiques WHERE utilisateur_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $boutiques = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Erreur sécurisée (pas de détails sensibles en production)
    if (ENV_DEV) {
        die("Erreur BDD: " . $e->getMessage());
    } else {
        die("Erreur technique. Veuillez réessayer plus tard.");
    }
}

// Utiliser les constantes de l'application
echo "<h1>Bienvenue sur " . APP_NAME . "</h1>";
echo "<p>Version: " . APP_VERSION . "</p>";
?>
```

## 🔧 Personnalisation

### Changer les paramètres de la base de données

Modifiez les constantes dans `database.php` :

```php
const DB_HOST = 'votre-host';
const DB_PORT = '3306';
const DB_NAME = 'votre-bdd';
const DB_USER = 'votre-user';
const DB_PASS = 'votre-password';
```

### Ajouter de nouvelles constantes

Dans `app.php` :

```php
define('NOUVELLE_CONSTANTE', 'valeur');
```

## 🐛 Dépannage

### Erreur "Connection refused"
- Vérifiez que votre serveur MySQL est démarré
- Vérifiez le port (3307 par défaut)

### Erreur "Access denied"
- Vérifiez les identifiants dans `database.php`
- Assurez-vous que l'utilisateur a les permissions nécessaires

### Erreur "Database not found"
- Vérifiez le nom de la base de données
- Assurez-vous que la BDD existe bien

## 📞 Support

Pour toute question sur la configuration, consultez la documentation ou contactez l'équipe de développement.
