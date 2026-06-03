-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : sql13.artsemm.dev:6033
-- Généré le : mer. 03 juin 2026 à 13:11
-- Version du serveur : 10.11.14-MariaDB-0+deb12u2
-- Version de PHP : 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `driveloc`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catégories de véhicules disponibles sur la plateforme';

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Citadine', '2026-06-02 08:57:08'),
(2, 'Berline', '2026-06-02 08:57:08'),
(3, 'SUV', '2026-06-02 08:57:08'),
(4, 'Utilitaire', '2026-06-02 08:57:08'),
(5, 'Moto', '2026-06-02 08:57:08'),
(6, 'Vélo électrique', '2026-06-02 08:57:08');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → vehicles.id',
  `client_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → users.id (rôle client)',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL COMMENT 'price_per_day × nb jours',
  `status` enum('upcoming','ongoing','done','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Réservations de véhicules';

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `vehicle_id`, `client_id`, `start_date`, `end_date`, `total_price`, `status`, `created_at`) VALUES
(3, 8, 3, '2026-06-02', '2026-06-27', 2225.00, 'upcoming', '2026-06-02 18:43:01');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hash bcrypt – généré via password_hash()',
  `role` enum('client','owner','admin') NOT NULL DEFAULT 'client',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comptes utilisateurs – rôles : client, owner, admin';

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(2, 'Drive', 'Loc', 'admin1@driveloc.fr', '$2y$12$stThRINkweiyiXRHwisZcuQZo6VmG.GBVlx0Wvk4UgywujgiOHf2S', 'owner', 'active', '2026-06-02 08:59:58'),
(3, 'Admin', 'Driveloc', 'admin@driveloc.fr', '$2y$12$BVSbpiosUDP8b2OK2oZb1uuTscMw5ZjxMLrdNn/v5BPP4NNirRuYC', 'admin', 'active', '2026-06-02 09:03:03'),
(4, 'Client', 'Réserve', 'admin2@driveloc.fr', '$2y$12$dmKdmxOCgD4LLLPNQa2U4eQ6220J4Gp0RusIo/efB5EteGEKorRrq', 'client', 'active', '2026-06-02 09:03:37');

-- --------------------------------------------------------

--
-- Structure de la table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(10) UNSIGNED NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → users.id (rôle owner)',
  `category_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → categories.id',
  `title` varchar(255) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `registration` varchar(20) NOT NULL COMMENT 'Immatriculation ou référence interne',
  `price_per_day` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL COMMENT 'Nom de fichier de la photo principale',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Annonces de véhicules à louer';

--
-- Déchargement des données de la table `vehicles`
--

INSERT INTO `vehicles` (`id`, `owner_id`, `category_id`, `title`, `brand`, `model`, `registration`, `price_per_day`, `description`, `main_image`, `status`, `created_at`) VALUES
(1, 2, 1, 'Voiture d\'occasion de Théo', 'Burger King', 'Festin du Roi', 'RN-067-BOI', 120.00, 'Jolie voiture pour vos trajets quotidien !', 'https://img5.autodeclics.com/6/2026/01/photo_article/142541/386691/1200-L-une-voiture-hamburger-arpente-les-rues-de-ce-dpartement-pour-les-10-ans-de-burger-king.jpg', 'active', '2026-06-02 09:05:06'),
(2, 2, 5, 'Moto 25 km/h débridé de Tangui', 'Kitty Road', 'Hello 4', 'DZ-213-AL', 0.50, 'Parfait pour l\'autoroute !', 'https://i.pinimg.com/736x/a0/b6/d2/a0b6d203e00d9d0d41dd8229c578b5de.jpg', 'active', '2026-06-02 09:09:54'),
(3, 2, 4, 'Smoby Véhicule de Fonction (Occasion)', 'Smoby', 'Disney Princesse', 'DIS-CO-456', 8999.00, 'Parfait pour les routes de montagnes !', 'https://media.cdn.kaufland.de/product-images/1024x1024/3624f4f2bcd60585b21124b7940f1fa5.webp', 'active', '2026-06-02 09:15:06'),
(4, 2, 3, 'Voiture atteinte de syndrome du personnage principal', 'Flash McQueen', 'IRL edition', 'AB-BA-RHYME', 566.00, 'Nickel !', 'https://static.actu.fr/uploads/2023/03/337379732-1140815933377979-6476066608785048666-n-960x640.jpg', 'active', '2026-06-02 09:18:12'),
(5, 2, 2, 'Transforme-l\'heure (Transformers)', 'La Batman', 'Véhicule de Police', 'ROBO-CAR-POLI', 5.00, 'Parfait pour les roadrage', 'https://play-lh.googleusercontent.com/dDNFvNB9u_noz9Y-EWDoNrSMqOiTwBBXffcNspVW2CaR-IOkopHc7P5zgvecbd-IbeY=w240-h480-rw', 'active', '2026-06-02 09:19:52'),
(6, 2, 2, 'Voiture Volante', 'BM double V', 'V5 TURBO', 'AB-123-AB', 9999.00, 'Parfait pour le CIEEEEEL!', 'https://i.ibb.co/8g3mrFxS/Voiture-volante-fb312096f0.jpg', 'active', '2026-06-02 09:24:50'),
(7, 2, 1, 'Voiture CCI Formation (500 000 km, parfaite état, presque pas utilisée)', 'CCI', 'Formation Lot et Garonne', 'CCI-49-BRDN', 53.00, '', 'https://img.centrefrance.com/01h2z4XM9g7rsjCAZzojX7wZR59ct5DcalH2CP52wRQ/rs:fit:657:438:1:0/bG9jYWw6Ly8vMDAvMDAvMDcvMzYvMDYvMjAwMDAwNzM2MDY3NQ.webp', 'active', '2026-06-02 09:28:10'),
(8, 2, 5, 'Citronier', 'Citron', 'IER', 'AB555AAA', 89.00, '', 'https://media.istockphoto.com/id/175848378/fr/photo/voiture-de-citron.jpg?s=612x612&w=0&k=20&c=fsOX4kqeNx-8Cok36vhLYlOrppCldVehygRh8ENzDtA=', 'active', '2026-06-02 09:29:38'),
(9, 2, 4, 'Angle Obtus, parfait état', 'Rondeau', 'Client 5', 'FF55G66FF', 789.00, '', 'https://voiture.kidioui.fr/blog/wp-content/uploads/2016/08/voiture-les-plus-moches.jpg', 'active', '2026-06-02 09:30:29'),
(18, 2, 2, 'Rolls-Royce Ghost V12 - Prestige', 'Rolls-Royce', 'Ghost', 'LUX-RR-GHOST01', 790.00, 'Berline ultra premium pour evenements VIP, mariages et deplacements executifs.', 'https://images.pexels.com/photos/28374850/pexels-photo-28374850.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(19, 2, 2, 'Rolls-Royce Phantom VIII', 'Rolls-Royce', 'Phantom', 'LUX-RR-PHANTOM01', 990.00, 'Le summum du luxe automobile avec chauffeur.', 'https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(20, 2, 2, 'Bentley Flying Spur Mulliner', 'Bentley', 'Flying Spur', 'LUX-BEN-FS01', 690.00, 'Berline anglaise de prestige alliant confort et performances.', 'https://images.pexels.com/photos/358070/pexels-photo-358070.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(21, 2, 2, 'Bentley Continental GT', 'Bentley', 'Continental GT', 'LUX-BEN-CGT01', 650.00, 'Coupe grand tourisme raffine et puissant.', 'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(22, 2, 2, 'Mercedes-Maybach S680', 'Mercedes-Maybach', 'S680', 'LUX-MAY-S68001', 720.00, 'Experience de voyage exceptionnelle en premiere classe.', 'https://images.pexels.com/photos/120049/pexels-photo-120049.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(23, 2, 2, 'Mercedes Classe G AMG', 'Mercedes-Benz', 'G63 AMG', 'LUX-MER-G6301', 580.00, 'SUV de luxe iconique et tres polyvalent.', 'https://images.pexels.com/photos/892522/pexels-photo-892522.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(24, 2, 2, 'BMW Serie 7 Executive', 'BMW', '740i', 'LUX-BMW-740I01', 390.00, 'Grande berline executive moderne et confortable.', 'https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(25, 2, 2, 'BMW X7 M60i', 'BMW', 'X7', 'LUX-BMW-X701', 450.00, 'SUV premium 7 places pour voyages haut de gamme.', 'https://images.pexels.com/photos/100653/pexels-photo-100653.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(26, 2, 2, 'Audi RS7 Sportback', 'Audi', 'RS7', 'LUX-AUD-RS701', 480.00, 'Performance sportive et design agressif.', 'https://images.pexels.com/photos/244206/pexels-photo-244206.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(27, 2, 2, 'Audi A8L Quattro', 'Audi', 'A8L', 'LUX-AUD-A8L01', 420.00, 'Berline de luxe technologique et silencieuse.', 'https://images.pexels.com/photos/2365572/pexels-photo-2365572.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(28, 2, 2, 'Porsche Panamera Turbo', 'Porsche', 'Panamera', 'LUX-POR-PAN01', 550.00, 'Confort de berline et performances de sportive.', 'https://images.pexels.com/photos/210019/pexels-photo-210019.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(29, 2, 2, 'Porsche Cayenne Turbo GT', 'Porsche', 'Cayenne', 'LUX-POR-CAY01', 520.00, 'SUV de luxe ultra performant.', 'https://images.pexels.com/photos/248747/pexels-photo-248747.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(30, 2, 2, 'Lamborghini Urus Performante', 'Lamborghini', 'Urus', 'LUX-LAM-URUS01', 890.00, 'SUV supercar au caractere unique.', 'https://images.pexels.com/photos/3954440/pexels-photo-3954440.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(31, 2, 2, 'Ferrari Roma', 'Ferrari', 'Roma', 'LUX-FER-ROMA01', 950.00, 'Coupe italien elegant et exclusif.', 'https://images.pexels.com/photos/337909/pexels-photo-337909.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30'),
(32, 2, 2, 'Aston Martin DBX707', 'Aston Martin', 'DBX707', 'LUX-AST-DBX01', 690.00, 'SUV britannique melangeant sportivite et raffinement.', 'https://images.pexels.com/photos/1149831/pexels-photo-1149831.jpeg?auto=compress&cs=tinysrgb&w=800', 'active', '2026-06-03 12:31:30');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_name` (`name`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reservations_vehicle` (`vehicle_id`),
  ADD KEY `fk_reservations_client` (`client_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Index pour la table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vehicles_owner` (`owner_id`),
  ADD KEY `fk_vehicles_category` (`category_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservations_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservations_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vehicles_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
