-- Table pour suivre les visiteurs uniques par IP et par jour
CREATE TABLE IF NOT EXISTS visiteurs_uniques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boutique_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    date_visite DATETIME NOT NULL,
    FOREIGN KEY (boutique_id) REFERENCES boutiques(id) ON DELETE CASCADE,
    INDEX idx_boutique_ip (boutique_id, ip_address),
    INDEX idx_date (date_visite)
);

-- Mettre à jour la table boutiques pour ajouter les colonnes de statistiques
ALTER TABLE boutiques 
ADD COLUMN IF NOT EXISTS total_vues INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS visiteurs_uniques INT DEFAULT 0;
