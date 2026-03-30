-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : ven. 27 mars 2026 à 15:16
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_de_stage`
--

-- --------------------------------------------------------

--
-- Structure de la table `boutiques`
--

CREATE TABLE `boutiques` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `services` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `lien_public` varchar(100) DEFAULT NULL,
  `qrcode` varchar(300) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('active','inactive','suspendue') DEFAULT 'active',
  `total_vues` int(11) DEFAULT 0,
  `visiteurs_uniques` int(11) DEFAULT 0,
  `total_commandes` int(11) DEFAULT 0,
  `total_favoris` int(11) DEFAULT 0,
  `banniere` varchar(255) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `boutiques`
--

INSERT INTO `boutiques` (`id`, `utilisateur_id`, `nom`, `adresse`, `whatsapp`, `services`, `description`, `logo`, `lien_public`, `qrcode`, `date_creation`, `statut`, `total_vues`, `visiteurs_uniques`, `total_commandes`, `total_favoris`, `banniere`, `slug`) VALUES
(1, 3, 'brayan kameni', 'douala', '+237657300644', 'DOUALA CAMEROUN', 'DE BONNE QUALITER', 'uploads/logo_69a76ab3f1aaa4.87676233.jpeg', NULL, NULL, '2026-03-03 23:11:47', 'active', 0, 0, 0, 1, NULL, NULL),
(2, 9, 'brayano_shop', 'YAOUNDE-MELEN', '+237657300644', 'douala', 'de bonne qualiter', 'uploads/logo_69bb201315d121.58729137_1773871123.png', NULL, NULL, '2026-03-18 21:58:43', 'active', 0, 0, 0, 2, NULL, NULL),
(3, 10, 'brayano', 'melen', '+237657300644', 'doaula', 'cameroun', 'uploads/logo_69bdb9200e8f77.18156714_1774041376.png', 'brayano-5cdb1f2f5b', 'uploads/qrcodes/qr_brayano-5cdb1f2f5b.svg', '2026-03-20 21:16:20', 'active', 0, 0, 0, 0, NULL, NULL),
(4, 11, 'brayano_shop', 'djdd', '+237657300644', 'douala', 'ddd', 'uploads/logo_69bdbefc2e1134.18908195_1774042876.jpg', 'brayano-shop-03a0a8a356', 'uploads/qrcodes/qr_brayano-shop-03a0a8a356.svg', '2026-03-20 21:41:19', 'active', 0, 0, 0, 0, NULL, NULL),
(5, 12, 'brayano_shop', 'YAOUNDE-MELEN', '+237657300644', 'DOAULA', 'DE BONNE QUALITER', 'uploads/logo_69bdc07f9defc0.72109626_1774043263.jpg', NULL, NULL, '2026-03-20 21:47:43', 'active', 0, 0, 0, 1, NULL, NULL),
(6, 14, 'BRAYANO', 'YAOUNDE-MELEN', '+237657300644', 'douala', '', 'uploads/logo_69c02812930e22.40476332_1774200850.jpg', NULL, NULL, '2026-03-22 17:34:10', 'active', 0, 0, 0, 0, NULL, NULL),
(7, 15, 'PHONE', 'doula- yassa', '+237683260520', 'DOUALA', 'DE', 'uploads/logo_69c02938aba9f8.13121466_1774201144.jpg', NULL, NULL, '2026-03-22 17:39:04', 'active', 0, 0, 0, 0, NULL, NULL),
(8, 17, 'projet', 'MELONG, Cameroun', '+237657300644', 'doula', 'de bonne qualiter', 'uploads/logo_69c26abfe669f9.21826821_1774348991.jpg', NULL, NULL, '2026-03-24 10:43:11', 'active', 0, 0, 0, 0, NULL, NULL),
(9, 18, 'brayano_shop', 'YAOUNDE-MELEN', '+237657300644', 'douala', 'NOUS FAISONS DANS LA VENTE ET LA PROSPECTION DES SERVICES DE BEAUTER', 'uploads/logo_69c3aff6299af3.84603782_1774432246.jpg', NULL, NULL, '2026-03-25 09:50:46', 'active', 0, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `boutique_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `nom_client` varchar(100) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `quantite` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `montant` decimal(10,2) NOT NULL,
  `statut` enum('nouveau','confirme','preparation','livre','annule') DEFAULT 'nouveau',
  `note` text DEFAULT NULL,
  `date_commande` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `utilisateur_id`, `boutique_id`, `produit_id`, `nom_client`, `telephone`, `quantite`, `montant`, `statut`, `note`, `date_commande`) VALUES
(1, NULL, 2, 4, 'brayan kameni', '+237623456789', 1, 4000.00, 'nouveau', 'juiiioo', '2026-03-22 18:02:51'),
(2, 15, 7, 11, 'ULRICH', '+237657300644', 1, 450000.00, 'nouveau', NULL, '2026-03-22 18:41:42'),
(3, 15, 7, 12, 'brayan', '+237683260520', 2, 30000.00, 'nouveau', NULL, '2026-03-22 21:06:45'),
(4, 17, 8, 13, 'brayan kameni', '+237623456789', 44, 88000.00, 'nouveau', 'dododod', '2026-03-24 11:45:31'),
(5, 18, 9, 14, 'Brayan Ulrich', '+237657300644', 1, 3000.00, 'nouveau', 'YAOUNDE-MELEN', '2026-03-25 11:29:27');

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `boutique_id` int(11) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `favoris`
--

INSERT INTO `favoris` (`id`, `utilisateur_id`, `boutique_id`, `date_ajout`) VALUES
(32, 9, 1, '2026-03-19 21:32:43'),
(36, 9, 2, '2026-03-19 23:28:46'),
(40, 12, 5, '2026-03-22 18:05:36'),
(41, 14, 5, '2026-03-22 18:33:22'),
(46, 15, 7, '2026-03-22 21:05:35'),
(47, 17, 8, '2026-03-24 11:45:13'),
(48, 18, 9, '2026-03-25 11:29:38');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `boutique_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `localisation` varchar(255) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `image` varchar(255) NOT NULL,
  `statut` varchar(50) DEFAULT 'disponible',
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `boutique_id`, `utilisateur_id`, `nom`, `description`, `prix`, `localisation`, `whatsapp`, `image`, `statut`, `date_ajout`) VALUES
(1, 1, 3, 'airpods', 'de bonne qualiter', 4000.00, 'Douala', '657300644', 'uploads/produits/produit_69a76d69372bd8.91900972_1772580201.jpeg', 'disponible', '2026-03-03 23:23:21'),
(2, 2, 9, 'sacs a main', 'de bonne qualiter', 3000.00, 'DOUALA YASSA', '657300644', 'uploads/produits/produit_69bb2b52642ea5.40385668_1773874002.jpeg', 'disponible', '2026-03-18 22:46:42'),
(3, 2, 9, 'blutheoth', 'de bonne qualiter', 4000.00, 'DOUALA YASSA', '657300644', 'uploads/produits/produit_69bb2df47da2f3.60972440_1773874676.jpeg', 'disponible', '2026-03-18 22:57:56'),
(4, 2, 9, 'sacs a main', 'DE BONNE QUALITER', 4000.00, 'DOUALA YASSA', '657300644', 'uploads/produits/produit_69bc283bb429e8.41697651_1773938747.png', 'disponible', '2026-03-19 16:45:47'),
(5, 2, 9, 'CASQUE', 'DE BONNE QUALITER', 5000.00, 'DOUALA YASSA', '683260520', 'uploads/produits/produit_69bc5575a56b25.21057721_1773950325.jpeg', 'disponible', '2026-03-19 19:58:45'),
(6, 2, 9, 'ECOUTEURS', 'DE BONNE QUALITER', 3500.00, 'DOUALA YASSA', '683260520', 'uploads/produits/produit_69bc78927beaf4.27735589_1773959314.jpeg', 'disponible', '2026-03-19 22:28:34'),
(7, 5, 12, 'BIJOUX', 'DE bonne qualiter QUALITER', 3000.00, 'DOUALA YASSA', '+237683260520', 'uploads/produits/produit_69bdda4c575f26.63462454_1774049868.png', 'disponible', '2026-03-20 21:48:25'),
(8, 5, 12, 'maths', 'de bonne qualiter', 5000.00, 'DOUALA YASSA', '657300644', 'uploads/produits/produit_69bdce57132527.02361051_1774046807.png', 'disponible', '2026-03-20 22:46:47'),
(9, 5, 12, 'BRAYAN', 'GRAN DE TAILLE', 100000.00, 'DOUALA YASSA', '683260520', 'uploads/produits/produit_69c00046002768.96154010_1774190662.jpeg', 'disponible', '2026-03-22 14:44:22'),
(10, 6, 14, 'TELEPHONE', 'DE BONNE QUALITER', 4000.00, 'DOUALA YASSA', '683260520', 'uploads/produits/produit_69c028379d6d86.59555072_1774200887.jpeg', 'disponible', '2026-03-22 17:34:47'),
(11, 7, 15, 'PHONE', 'DE BONNE QUALITER', 450000.00, 'DOUALA', '683260520', 'uploads/produits/produit_69c02986011a72.28270808_1774201222.jpeg', 'disponible', '2026-03-22 17:40:22'),
(12, 7, 15, 'application', 'de bonne qualiter', 15000.00, 'yaounder', '657300644', 'uploads/produits/produit_69c04b4d3446f9.18930003_1774209869.png', 'disponible', '2026-03-22 20:04:29'),
(13, 8, 17, 'AIRPODS', 'de bonne qaliter', 2000.00, 'douala', '+237657300644', 'uploads/produits/produit_69c26b10d8e268.27782722_1774349072.jpeg', 'disponible', '2026-03-24 10:44:32'),
(14, 9, 18, 'AIRPODS', 'DE BONNE QUALITER', 3000.00, 'DOUALA YASSA', '683260520', 'uploads/produits/produit_69c3b8c1acf125.84677404_1774434497.jpeg', 'disponible', '2026-03-25 10:28:17');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'vendeur',
  `mot_de_passe` varchar(255) NOT NULL,
  `statut` varchar(50) DEFAULT 'actif',
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `role`, `mot_de_passe`, `statut`, `date_inscription`) VALUES
(1, 'brayan', 'ulrichbrayan492@gmail.com', 'vendeur', '$2y$10$1aCgVxyTtjbmvkdCX73OEe0RBj9BaeI.xlJeIMEmwVdZM9Ho9Rq7K', 'actif', '2026-03-03 22:31:48'),
(2, 'BRAYANO', 'ulrichbran492@gmail.com', 'vendeur', '$2y$10$cH53WpGvq56.G/fG3TYTI.GCAyVKqgf2/ZpcmLtDh3.pXFA0q/35e', 'actif', '2026-03-03 22:44:52'),
(3, 'ulrichbra', 'richbrayan492@gmail.com', 'vendeur', '$2y$10$WElsJPKQTjIvtGZ8FkjWGOVVS4V0.PyYbp6xp5Qu/vujUP9KABQze', 'actif', '2026-03-03 23:00:31'),
(4, 'brayan', 'jdjdjjd@gmail.com', 'vendeur', '$2y$10$sIgpBzHMDmcuLbgpYKaNnOrjdp/gUOS4tmAgNbIipr5w1u82qEkki', 'actif', '2026-03-16 21:44:06'),
(5, 'brayan', 'ulrichbrayan42@gmail.com', 'vendeur', '$2y$10$T8Xrd8J2Qh0g9QPgtaTzIOCUTth.z6P1Ddqqw1p1WX7Evl7/Qd6Vy', 'actif', '2026-03-17 21:37:22'),
(6, 'brakdndnyankde', 'kdkdk@gmail.com', 'vendeur', '$2y$10$ifAFzfLTrvXle5OZAmX2L.pEI1i3DQoZFjUzOt0S6ZkQy3.XYvA5G', 'actif', '2026-03-17 21:45:50'),
(7, 'sksk', 'ulricbrayan492@gmail.com', 'vendeur', '$2y$10$5kk7oE5JpMdGwwufyqpho.xYgCrVXlZdxb5cJ3MAVDBMprQVjhS9e', 'actif', '2026-03-18 21:03:42'),
(8, 'ulrich', 'ulrichbr492@gmail.com', 'vendeur', '$2y$10$p7KBNLWqz9hmZxZc3TtCFOPXPLJRmgOZNehWEj093yoxcYNzrcgVK', 'actif', '2026-03-18 21:05:22'),
(9, 'brua', 'ulricr492@gmail.com', 'vendeur', '$2y$10$fNSym6WaOek8sdaELH1H9eu44OKiiafEWacm2rGfGEU7v3t1mgCvW', 'actif', '2026-03-18 21:14:58'),
(10, 'ulrichbra', 'richbrayan@gmail.com', 'vendeur', '$2y$10$4It88wCHEUG8O6cL2ycwwe8v59cApJmJYQlv7LN/H0iS9iXH/CJnK', 'actif', '2026-03-20 21:15:23'),
(11, 'brakdndnyankde', 'ejej@gmail.com', 'vendeur', '$2y$10$Ccj14qKAJgS.mlh6hUmjPO4Hn3.dMel/pKHD.Fw7c7qlvJBqCGAjy', 'actif', '2026-03-20 21:40:27'),
(12, 'brayano_s', 'ulrian492@gmail.com', 'vendeur', '$2y$10$QITyJivoGprjlZGaqt4FNOzEnxh38i2TETX5ShYvsJB1IU.CMFUlG', 'actif', '2026-03-20 21:47:04'),
(13, 'brakdndnyankde', 'ffff@gmail.com', 'vendeur', '$2y$10$vv6qsL84SWeb8ApIXXnQquFnUKdpehWEtCGBNRZrxmTy6OwdaAe4O', 'actif', '2026-03-22 17:21:00'),
(14, 'BRAYAN', 'ulrichb2@gmail.com', 'vendeur', '$2y$10$uVJSNprzeFasHAVqmhi/gerhcSexPQ.pq3HOuas87enD/UopyCz4a', 'actif', '2026-03-22 17:32:02'),
(15, 'brakdndnyankde', 'ZZSS@GMAIL.COM', 'vendeur', '$2y$10$1Zbl/6QZx7h6a2/vPTPM6u35KUiTLYYQfUJX0vM/xAYz/HDQsh9kG', 'actif', '2026-03-22 17:36:24'),
(16, 'ulrichbra', 'richbran492@gmail.com', 'vendeur', '$2y$10$TVgjDPZafjCx8ZTxiVOB2OgcgP0rcPE7JVI2WIg2zqZ3m8QXNkQOO', 'actif', '2026-03-23 22:25:37'),
(17, 'ulrichbra', 'richbn492@gmail.com', 'vendeur', '$2y$10$rFj1dPdOJ5DScpbRN/Kjx.kImRaZttusR1tEDd1jL40/S9dK6uona', 'actif', '2026-03-24 10:41:24'),
(18, 'BRAYAN', 'ulrayan492@gmail.com', 'vendeur', '$2y$10$lSHC9fUYiS7i5z5uuHOD/eBuLi1xwj8l1QSywONdhChmPGo93p6He', 'actif', '2026-03-25 09:45:12'),
(19, 'ulrichbra', 'rihbran492@gmail.com', 'vendeur', '$2y$10$peabN0iE7vSPlrzhKRzsVOJTKNoAyg0Hk8qHV8eQiz05NIBNO05ty', 'actif', '2026-03-26 13:41:10');

-- --------------------------------------------------------

--
-- Structure de la table `visiteurs_uniques`
--

CREATE TABLE `visiteurs_uniques` (
  `id` int(11) NOT NULL,
  `boutique_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `date_visite` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vues_boutiques`
--

CREATE TABLE `vues_boutiques` (
  `id` int(11) NOT NULL,
  `boutique_id` int(11) NOT NULL,
  `ip_visiteur` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `date_visite` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `boutiques`
--
ALTER TABLE `boutiques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `lien_public` (`lien_public`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_boutique` (`boutique_id`),
  ADD KEY `idx_produit` (`produit_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date` (`date_commande`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`utilisateur_id`,`boutique_id`),
  ADD KEY `boutique_id` (`boutique_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_boutique` (`boutique_id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `visiteurs_uniques`
--
ALTER TABLE `visiteurs_uniques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_boutique_ip` (`boutique_id`,`ip_address`),
  ADD KEY `idx_date` (`date_visite`);

--
-- Index pour la table `vues_boutiques`
--
ALTER TABLE `vues_boutiques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_visite` (`boutique_id`,`ip_visiteur`,`date_visite`),
  ADD KEY `idx_boutique` (`boutique_id`),
  ADD KEY `idx_date` (`date_visite`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `boutiques`
--
ALTER TABLE `boutiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `visiteurs_uniques`
--
ALTER TABLE `visiteurs_uniques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `vues_boutiques`
--
ALTER TABLE `vues_boutiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `boutiques`
--
ALTER TABLE `boutiques`
  ADD CONSTRAINT `boutiques_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `fk_cmd_boutique` FOREIGN KEY (`boutique_id`) REFERENCES `boutiques` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cmd_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cmd_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`boutique_id`) REFERENCES `boutiques` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`boutique_id`) REFERENCES `boutiques` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produits_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `visiteurs_uniques`
--
ALTER TABLE `visiteurs_uniques`
  ADD CONSTRAINT `visiteurs_uniques_ibfk_1` FOREIGN KEY (`boutique_id`) REFERENCES `boutiques` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
