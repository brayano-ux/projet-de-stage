DROP DATABASE IF EXISTS projet_de_stage;
CREATE DATABASE projet_de_stage;
USE projet_de_stage;
  -- Table utilisateur
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('vendeur', 'client', 'admin') DEFAULT 'vendeur',
    mot_de_passe VARCHAR(255) NOT NULL,
    statut ENUM('actif', 'inactif') DEFAULT 'inactif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table template
CREATE TABLE template (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    preview_image VARCHAR(500)
);

-- Table boutique
CREATE TABLE boutique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_template INT,
    logo VARCHAR(255),
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,   -- pour le lien public /boutique/slug
    lieu VARCHAR(100) NOT NULL,
    description TEXT,
    contact VARCHAR(20),
    vues INT DEFAULT 0,
    statut ENUM('actif', 'inactif') DEFAULT 'inactif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (id_template) REFERENCES template(id) ON DELETE SET NULL
);

-- Table likes (remplace le compteur simple)
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    ip_address VARCHAR(45),             -- pour limiter le like par IP
    date_like TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES boutique(id) ON DELETE CASCADE
);

-- Table produits
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    image VARCHAR(255),
    nom VARCHAR(100) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    description TEXT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES boutique(id) ON DELETE CASCADE
);

-- Table commandes
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    nom_client VARCHAR(255),
    contact_client VARCHAR(20),
    message_whatsapp TEXT,              -- copie du message pré-rempli envoyé
    total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES boutique(id) ON DELETE CASCADE
);

-- Table commande_produits (détail des articles par commande)
CREATE TABLE commande_produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
);

-- Table vues (historique journalier)
CREATE TABLE vues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    date_vue DATE NOT NULL,
    nombre INT DEFAULT 1,               -- agrège les vues du même jour
    FOREIGN KEY (shop_id) REFERENCES boutique(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vue_jour (shop_id, date_vue)  -- une seule ligne par boutique/jour
);

-- Table qr_code
CREATE TABLE qr_code (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL UNIQUE,        -- une seule QR par boutique
    lien VARCHAR(500) NOT NULL,
    image_qr VARCHAR(500),
    FOREIGN KEY (shop_id) REFERENCES boutique(id) ON DELETE CASCADE
);

-- Table parametres (lié à l'utilisateur, pas à la boutique)
CREATE TABLE parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL UNIQUE,
    dark_mode BOOLEAN DEFAULT FALSE,
    langue ENUM('fr', 'en') DEFAULT 'fr',
    notification_whatsapp BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
);

-- Table admin_verification
CREATE TABLE admin_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    admin_id INT,
    statut ENUM('en_attente', 'valide', 'rejete') DEFAULT 'en_attente',
    note TEXT,
    date_verification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES boutique(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES utilisateur(id) ON DELETE SET NULL
);