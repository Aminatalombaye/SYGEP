-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 16 oct. 2024 à 15:14
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sygepbi`
--

-- --------------------------------------------------------

--
-- Structure de la table `agents`
--

CREATE TABLE `agents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(255) NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `assets`
--

CREATE TABLE `assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `date_achat` date DEFAULT NULL,
  `date_mise_en_service` date DEFAULT NULL,
  `modele` varchar(255) DEFAULT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `assets`
--

INSERT INTO `assets` (`id`, `serial_number`, `name`, `notes`, `type`, `date_achat`, `date_mise_en_service`, `modele`, `assigned_to`, `qr_code`, `created_at`, `updated_at`, `deleted_at`, `category_id`, `status_id`, `location_id`) VALUES
(1, 'LAPTOP-123456789', 'MacBook Air M2', 'Utilisé pour les réunions virtuelles', 'Ordinateur Portable', '2024-08-22', '2024-08-29', 'MacBook Air M2', 'amina', NULL, '2024-08-28 20:59:50', '2024-08-28 20:59:50', NULL, 1, 5, 17),
(2, 'MB-2024-00456', 'Chaise de Bureau Ergonomique', 'Chaise récemment acquise pour le bureau du directeur.', 'Consommable', '2024-08-31', '2024-09-20', 'Chair', 'Awa Diop', NULL, '2024-08-29 17:45:48', '2024-10-01 12:55:00', NULL, 2, 6, 2),
(3, '2', 'Laptop', NULL, 'Ordinateur', '2024-10-02', NULL, NULL, 'anta', NULL, '2024-10-01 15:02:02', '2024-10-01 15:02:02', NULL, 1, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `assets_histories`
--

CREATE TABLE `assets_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `asset_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `assets_histories`
--

INSERT INTO `assets_histories` (`id`, `created_at`, `updated_at`, `asset_id`, `status_id`, `location_id`, `assigned_user_id`) VALUES
(1, '2024-10-01 12:54:38', '2024-10-01 12:54:38', 2, 6, 2, NULL),
(2, '2024-10-01 12:55:00', '2024-10-01 12:55:00', 2, 6, 2, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `asset_assignment`
--

CREATE TABLE `asset_assignment` (
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_assignment`
--

INSERT INTO `asset_assignment` (`assignment_id`, `asset_id`) VALUES
(1, 1),
(2, 2),
(4, 2),
(5, 3),
(6, 3),
(7, 2),
(8, 1),
(9, 2);

-- --------------------------------------------------------

--
-- Structure de la table `asset_bon`
--

CREATE TABLE `asset_bon` (
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `bon_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_bon`
--

INSERT INTO `asset_bon` (`asset_id`, `bon_id`) VALUES
(1, 1),
(2, 2),
(3, 2);

-- --------------------------------------------------------

--
-- Structure de la table `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_categories`
--

INSERT INTO `asset_categories` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Équipements Informatiques', '2024-08-28 20:24:56', '2024-08-28 20:24:56', NULL),
(2, 'Mobilier de Bureau', '2024-08-28 20:25:06', '2024-08-28 20:25:06', NULL),
(3, 'Équipements Électriques', '2024-08-28 20:25:15', '2024-08-28 20:25:15', NULL),
(4, 'Outils et Matériels de Maintenance', '2024-08-28 20:25:25', '2024-08-28 20:25:25', NULL),
(5, 'Véhicules et Transports', '2024-08-28 20:25:33', '2024-08-28 20:25:33', NULL),
(6, 'Matériels de Sécurité', '2024-08-28 20:25:46', '2024-08-28 20:25:46', NULL),
(7, 'Équipements Audiovisuels', '2024-08-28 20:25:58', '2024-08-28 20:25:58', NULL),
(8, 'Fournitures de Bureau', '2024-08-28 20:26:08', '2024-08-28 20:26:08', NULL),
(9, 'Matériels de Construction', '2024-08-28 20:26:16', '2024-08-28 20:26:16', NULL),
(10, 'Équipements Médicaux', '2024-08-28 20:26:27', '2024-08-28 20:26:27', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `asset_inventaire`
--

CREATE TABLE `asset_inventaire` (
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `inventaire_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_inventaire`
--

INSERT INTO `asset_inventaire` (`asset_id`, `inventaire_id`) VALUES
(1, 1),
(2, 5),
(3, 5);

-- --------------------------------------------------------

--
-- Structure de la table `asset_locations`
--

CREATE TABLE `asset_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_locations`
--

INSERT INTO `asset_locations` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Dakar', '2024-08-28 20:26:58', '2024-08-28 20:26:58', NULL),
(2, 'Thiès', '2024-08-28 20:27:07', '2024-08-28 20:27:07', NULL),
(3, 'Saint-Louis', '2024-08-28 20:27:15', '2024-08-28 20:27:15', NULL),
(4, 'Kaolack', '2024-08-28 20:27:23', '2024-08-28 20:27:23', NULL),
(5, 'Ziguinchor', '2024-08-28 20:27:35', '2024-08-28 20:27:35', NULL),
(6, 'Tambacounda', '2024-08-28 20:27:43', '2024-08-28 20:27:43', NULL),
(7, 'Université Cheikh Anta Diop de Dakar', '2024-08-28 20:27:58', '2024-08-28 20:27:58', NULL),
(8, 'Bignona', '2024-08-28 20:28:24', '2024-08-28 20:28:24', NULL),
(9, 'Nioro du Rip', '2024-08-28 20:28:33', '2024-08-28 20:28:33', NULL),
(10, 'Guédiawaye', '2024-08-28 20:28:41', '2024-08-28 20:28:41', NULL),
(11, 'Pikine', '2024-08-28 20:28:52', '2024-08-28 20:28:52', NULL),
(12, 'Fann', '2024-08-28 20:29:02', '2024-08-28 20:29:02', NULL),
(13, 'Koungheul', '2024-08-28 20:29:19', '2024-08-28 20:29:19', NULL),
(14, 'Touba', '2024-08-28 20:29:24', '2024-08-28 20:29:24', NULL),
(15, 'Tivaouane', '2024-08-28 20:29:33', '2024-08-28 20:29:33', NULL),
(16, 'Diamniadio', '2024-08-28 20:29:45', '2024-08-28 20:29:45', NULL),
(17, 'Dage', '2024-08-28 20:29:52', '2024-08-28 20:29:52', NULL),
(18, 'DRH', '2024-08-28 20:29:58', '2024-08-28 20:29:58', NULL),
(19, 'DA', '2024-08-28 20:30:26', '2024-08-28 20:30:26', NULL),
(20, 'DPSE', '2024-08-28 20:32:34', '2024-08-28 20:32:34', NULL),
(21, 'DOP', '2024-08-28 20:32:44', '2024-08-28 20:32:44', NULL),
(22, 'DECPC', '2024-08-28 20:33:04', '2024-08-28 20:33:04', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `asset_statuses`
--

CREATE TABLE `asset_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_statuses`
--

INSERT INTO `asset_statuses` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Disponible', '2024-08-04 01:22:42', '2024-08-28 21:04:54', NULL),
(2, 'Pas disponible', '2024-08-04 01:22:42', '2024-08-28 21:05:07', NULL),
(3, 'Broken', '2024-08-04 01:22:42', '2024-10-10 12:33:57', '2024-10-10 12:33:57'),
(4, 'Out for Repair', '2024-08-04 01:22:42', '2024-10-10 12:33:52', '2024-10-10 12:33:52'),
(5, 'Neuf', '2024-08-28 20:33:18', '2024-08-28 20:33:18', NULL),
(6, 'Bon état', '2024-08-28 20:33:27', '2024-08-28 20:33:27', NULL),
(7, 'Usagé', '2024-08-28 20:33:37', '2024-08-28 20:33:37', NULL),
(8, 'Réparé', '2024-08-28 20:33:46', '2024-08-28 20:33:46', NULL),
(9, 'Endommagé', '2024-08-28 20:33:54', '2024-08-28 20:33:54', NULL),
(10, 'Hors service', '2024-08-28 20:34:02', '2024-08-28 20:34:02', NULL),
(11, 'Perdu', '2024-08-28 20:34:12', '2024-08-28 20:34:12', NULL),
(12, 'En attente de réparation', '2024-08-28 20:34:20', '2024-08-28 20:34:20', NULL),
(13, 'Obsolète', '2024-08-28 20:34:29', '2024-08-28 20:34:29', NULL),
(14, 'En transit', '2024-08-28 20:34:38', '2024-08-28 20:34:38', NULL),
(15, 'Retiré', '2024-08-28 20:34:48', '2024-08-28 20:34:48', NULL),
(16, 'Éliminé', '2024-08-28 20:34:57', '2024-08-28 20:34:57', NULL),
(17, 'Réservé', '2024-08-28 20:35:04', '2024-08-28 20:35:04', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `asset_supplier`
--

CREATE TABLE `asset_supplier` (
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `asset_supplier`
--

INSERT INTO `asset_supplier` (`asset_id`, `supplier_id`) VALUES
(1, 7),
(2, 7),
(3, 6);

-- --------------------------------------------------------

--
-- Structure de la table `asset_task`
--

CREATE TABLE `asset_task` (
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `utilisateur` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `assignments`
--

INSERT INTO `assignments` (`id`, `quantity`, `utilisateur`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2', 'Modou', '2024-08-28 21:10:05', '2024-10-10 11:15:59', '2024-10-10 11:15:59'),
(2, '2', 'Aminata', '2024-10-03 15:17:50', '2024-10-10 11:22:54', '2024-10-10 11:22:54'),
(3, NULL, NULL, '2024-10-10 11:13:00', '2024-10-10 11:16:11', '2024-10-10 11:16:11'),
(4, '1', 'Antaa', '2024-10-10 11:13:36', '2024-10-10 11:16:19', '2024-10-10 11:16:19'),
(5, '1', 'Am', '2024-10-10 11:14:20', '2024-10-10 11:15:55', '2024-10-10 11:15:55'),
(6, '1', 'Anta', '2024-10-10 11:18:43', '2024-10-10 11:18:43', NULL),
(7, '2', 'Ana', '2024-10-10 11:24:06', '2024-10-10 11:24:06', NULL),
(8, '3', 'sale', '2024-10-10 11:27:17', '2024-10-10 11:27:25', '2024-10-10 11:27:25'),
(9, '2', 'ANTA', '2024-10-10 14:04:28', '2024-10-10 14:04:28', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `assignment_attribution`
--

CREATE TABLE `assignment_attribution` (
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `attribution_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `assignment_attribution`
--

INSERT INTO `assignment_attribution` (`assignment_id`, `attribution_id`) VALUES
(1, 1),
(2, 2),
(4, 1),
(5, 4),
(6, 2),
(7, 2),
(8, 3),
(9, 1);

-- --------------------------------------------------------

--
-- Structure de la table `assignment_service`
--

CREATE TABLE `assignment_service` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `assignment_service`
--

INSERT INTO `assignment_service` (`id`, `assignment_id`, `service_id`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, NULL),
(2, 4, 1, NULL, NULL),
(3, 5, 1, NULL, NULL),
(4, 6, 1, NULL, NULL),
(5, 7, 1, NULL, NULL),
(6, 8, 1, NULL, NULL),
(7, 9, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `attributions`
--

CREATE TABLE `attributions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `type_atribution` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `attributions`
--

INSERT INTO `attributions` (`id`, `details`, `nom`, `type_atribution`, `created_at`, `updated_at`) VALUES
(1, NULL, 'A', 'Affectation', '2024-08-28 20:35:31', '2024-10-10 12:30:28'),
(2, NULL, 'B', 'Réservation', '2024-08-28 20:35:48', '2024-10-10 12:30:40'),
(3, NULL, 'C', 'Usage spécifique', '2024-08-28 20:36:04', '2024-10-10 12:30:48'),
(4, NULL, 'D', 'Distribution', '2024-08-28 20:36:19', '2024-10-10 12:31:00'),
(5, NULL, 'E', 'Maintenance', '2024-08-28 20:36:35', '2024-10-10 12:31:09'),
(6, NULL, 'F', 'Formation', '2024-08-28 20:36:49', '2024-10-10 12:31:16'),
(7, NULL, 'G', 'Programme', '2024-08-28 20:37:08', '2024-10-10 12:31:25'),
(8, NULL, 'H', 'Affectation', '2024-08-28 20:37:35', '2024-10-10 12:31:35');

-- --------------------------------------------------------

--
-- Structure de la table `bons`
--

CREATE TABLE `bons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date_emission` date NOT NULL,
  `organisation` varchar(255) NOT NULL,
  `reference_commande` varchar(255) NOT NULL,
  `nom_destinataire` varchar(255) NOT NULL,
  `bon` varchar(255) DEFAULT NULL,
  `date_livraison` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `bons`
--

INSERT INTO `bons` (`id`, `date_emission`, `organisation`, `reference_commande`, `nom_destinataire`, `bon`, `date_livraison`, `created_at`, `updated_at`) VALUES
(1, '2024-08-21', 'MFP', 'BC-2024-002', 'InfoTech Dakar', 'BC-2024-002', '2024-08-29', '2024-08-28 20:46:30', '2024-08-28 20:46:30'),
(2, '2024-08-29', 'Ministere', 'BC-2024-003', 'TechForm', 'BC-2024-003', '2024-09-07', '2024-08-28 20:47:35', '2024-08-28 20:47:35'),
(3, '2024-08-15', 'Ministère de la Formation Professionnelle', 'BC-2024-004', 'Bureau Plus', 'BC-2024-004', '2024-09-08', '2024-08-28 20:48:22', '2024-08-28 20:48:22');

-- --------------------------------------------------------

--
-- Structure de la table `chef_projets`
--

CREATE TABLE `chef_projets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `e_mail` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chef_projets`
--

INSERT INTO `chef_projets` (`id`, `nom`, `prenom`, `adresse`, `e_mail`, `telephone`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Diop', 'Abdou', 'Dakar', 'abdou.diop@gmail.com', '77 777 7 77', '2024-08-28 21:18:16', '2024-08-28 21:18:16', NULL),
(2, 'Fall', 'Mareme', 'Dakar', 'mareme.fall@gmail.com', '77 766 76 76', '2024-08-28 21:18:43', '2024-08-28 21:18:43', NULL),
(3, 'Mbaye', 'Amina', 'Sacre Coeur', 'amina.a@gmail.com', '77 778 87 87', '2024-08-28 21:19:14', '2024-10-10 12:32:27', '2024-10-10 12:32:27'),
(4, 'Abdou Diop', NULL, 'Thies', 'abdou.diop@gmail.com', '77 877 89 89', '2024-08-29 17:22:27', '2024-08-29 17:22:27', NULL),
(5, 'Anta Mbaye', NULL, 'Thies', 'anta.a@gmail.com', '77 888 88 88', '2024-08-29 17:32:15', '2024-08-29 17:32:15', NULL),
(6, 'Mouhamed diop', NULL, 'Dakar', 'mouhamed.diop@gmail.com', '77 776 67 76', '2024-08-29 17:33:03', '2024-08-29 17:33:03', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `chef_projet_project`
--

CREATE TABLE `chef_projet_project` (
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `chef_projet_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chef_projet_project`
--

INSERT INTO `chef_projet_project` (`project_id`, `chef_projet_id`) VALUES
(1, 2),
(2, 3),
(3, 4),
(4, 4),
(5, 6),
(6, 5),
(7, 5);

-- --------------------------------------------------------

--
-- Structure de la table `infrastructures`
--

CREATE TABLE `infrastructures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `construction_date` date NOT NULL,
  `depreciation_plan` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `infrastructures`
--

INSERT INTO `infrastructures` (`id`, `name`, `description`, `status`, `location`, `construction_date`, `depreciation_plan`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Bâtiment Principal', 'Bâtiment administratif principal du ministère avec plusieurs bureaux et une salle de réunion.', 'En construction', 'Dakar', '2024-08-23', '5 ans', 'Bureau', '2024-08-28 21:14:05', '2024-08-28 21:14:05', NULL),
(2, 'Salle de Formation A', 'Salle dédiée aux formations avec une capacité de 30 personnes, équipée de matériel audiovisuel.', 'En opération', 'Thiès, Zone Industrielle', '2024-09-06', '10 ans', 'Salle de Formation', '2024-08-28 21:15:30', '2024-08-28 21:15:30', NULL),
(3, 'Atelier de Maintenance', 'Atelier de Maintenance', 'En maintenance', 'Saint-Louis', '2024-07-30', '8 ans', 'Atelier', '2024-08-28 21:16:55', '2024-08-28 21:16:55', NULL),
(4, 'Bâtiment Administratif Principal', 'Le principal bâtiment administratif du ministère, abritant les bureaux des directeurs et du personnel de soutien', 'En service', 'Dakar, Plateau', '2023-09-11', '10 ans', 'Bâtiment administratif', '2024-08-29 17:26:32', '2024-08-29 17:26:32', NULL),
(5, 'Centre de Formation de Thies', 'Centre dédié à la formation professionnelle des étudiants dans divers domaines techniques.', 'En construction', 'Thiès, Route de Tivaouane', '2023-11-13', '15 ans', 'Centre de formation', '2024-08-29 17:27:50', '2024-08-29 17:30:26', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `infrastructure_project`
--

CREATE TABLE `infrastructure_project` (
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `infrastructure_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `infrastructure_project`
--

INSERT INTO `infrastructure_project` (`project_id`, `infrastructure_id`) VALUES
(1, 2),
(2, 2),
(3, 1),
(4, 5),
(5, 5),
(6, 4),
(7, 4);

-- --------------------------------------------------------

--
-- Structure de la table `inventaires`
--

CREATE TABLE `inventaires` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `in` varchar(255) DEFAULT NULL,
  `out` varchar(255) DEFAULT NULL,
  `balance` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inventaires`
--

INSERT INTO `inventaires` (`id`, `in`, `out`, `balance`, `reference`, `nom`, `created_at`, `updated_at`) VALUES
(1, '10', '3', '7', 'INV-001', 'Inventaire des Équipements Informatiques', '2024-08-28 20:39:27', '2024-08-28 20:39:27'),
(2, '5', '1', '4', 'INV-002', 'Inventaire pour Imprimantes Laser', '2024-08-28 21:02:24', '2024-08-28 21:02:24'),
(3, '7', '3', '4', 'INV-003', 'Inventaire pour Systèmes de Climatisation', '2024-08-28 21:03:11', '2024-08-28 21:03:11'),
(4, '20', '2', '18', 'INV-004', 'Inventaire pour Tableaux Blancs Interactifs', '2024-08-28 21:03:58', '2024-08-28 21:03:58'),
(5, '7', '2', '5', 'INV-005', 'Inventaire pour Scanners de Documents', '2024-08-28 21:04:25', '2024-08-28 21:04:25');

-- --------------------------------------------------------

--
-- Structure de la table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`id`, `description`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Correction', 'Envoyé', 'Abdou Diop', '2024-08-28 21:11:34', '2024-08-28 21:11:34', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `maintenance_request_task`
--

CREATE TABLE `maintenance_request_task` (
  `maintenance_request_id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `maintenance_request_task`
--

INSERT INTO `maintenance_request_task` (`maintenance_request_id`, `task_id`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `collection_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `disk` varchar(255) NOT NULL,
  `conversions_disk` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`manipulations`)),
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`custom_properties`)),
  `generated_conversions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`generated_conversions`)),
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responsive_images`)),
  `order_column` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `media`
--

INSERT INTO `media` (`id`, `model_type`, `model_id`, `uuid`, `collection_name`, `name`, `file_name`, `mime_type`, `disk`, `conversions_disk`, `size`, `manipulations`, `custom_properties`, `generated_conversions`, `responsive_images`, `order_column`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Asset', 1, 'aeea5360-c5f9-46e5-8cfc-0a286517d17f', 'photos', '66cf8f760c16f_img', '66cf8f760c16f_img.png', 'image/png', 'public', 'public', 107361, '[]', '[]', '{\"thumb\":true,\"preview\":true}', '[]', 1, '2024-08-28 20:59:50', '2024-08-28 20:59:50'),
(2, 'App\\Models\\Report', 1, '6a06838b-0b0b-4238-9d0c-0b8f7c6e7137', 'content', '66d0b210df742_resumé des besoins ', '66d0b210df742_resumé-des-besoins-.pdf', 'application/pdf', 'public', 'public', 113230, '[]', '[]', '[]', '[]', 1, '2024-08-29 17:38:46', '2024-08-29 17:38:46'),
(3, 'App\\Models\\Report', 2, '1a85f1a9-dca9-43a7-9fb3-6648d5a5e606', 'content', '66d0b27f1e726_resumé des besoins ', '66d0b27f1e726_resumé-des-besoins-.pdf', 'application/pdf', 'public', 'public', 113230, '[]', '[]', '[]', '[]', 1, '2024-08-29 17:40:37', '2024-08-29 17:40:37'),
(4, 'App\\Models\\Asset', 2, 'fc929de8-529a-4c02-b645-682dd47cf8f1', 'photos', '66fbf0f22a89d_CMC_1580.jpg', '66fbf0f22a89d_CMC_1580.jpg.webp', 'image/webp', 'public', 'public', 197752, '[]', '[]', '{\"thumb\":true,\"preview\":true}', '[]', 1, '2024-10-01 12:54:38', '2024-10-01 12:54:39');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(44, '2014_10_12_100000_create_password_resets_table', 1),
(45, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(46, '2024_08_28_000001_create_media_table', 2),
(47, '2024_08_28_000002_create_permissions_table', 3),
(48, '2024_10_03_113652_create_services_table', 4),
(49, '2024_10_03_152908_create_assignment_service_table', 5),
(50, '2024_10_16_112738_create_agents_table', 6),
(51, '2024_10_16_115543_create_agents_table', 7);

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'user_management_access', NULL, NULL, NULL),
(2, 'permission_create', NULL, NULL, NULL),
(3, 'permission_edit', NULL, NULL, NULL),
(4, 'permission_show', NULL, NULL, NULL),
(5, 'permission_delete', NULL, NULL, NULL),
(6, 'permission_access', NULL, NULL, NULL),
(7, 'role_create', NULL, NULL, NULL),
(8, 'role_edit', NULL, NULL, NULL),
(9, 'role_show', NULL, NULL, NULL),
(10, 'role_delete', NULL, NULL, NULL),
(11, 'role_access', NULL, NULL, NULL),
(12, 'user_create', NULL, NULL, NULL),
(13, 'user_edit', NULL, NULL, NULL),
(14, 'user_show', NULL, NULL, NULL),
(15, 'user_delete', NULL, NULL, NULL),
(16, 'user_access', NULL, NULL, NULL),
(17, 'asset_management_access', NULL, NULL, NULL),
(18, 'asset_category_create', NULL, NULL, NULL),
(19, 'asset_category_edit', NULL, NULL, NULL),
(20, 'asset_category_show', NULL, NULL, NULL),
(21, 'asset_category_delete', NULL, NULL, NULL),
(22, 'asset_category_access', NULL, NULL, NULL),
(23, 'asset_location_create', NULL, NULL, NULL),
(24, 'asset_location_edit', NULL, NULL, NULL),
(25, 'asset_location_show', NULL, NULL, NULL),
(26, 'asset_location_delete', NULL, NULL, NULL),
(27, 'asset_location_access', NULL, NULL, NULL),
(28, 'asset_status_create', NULL, NULL, NULL),
(29, 'asset_status_edit', NULL, NULL, NULL),
(30, 'asset_status_show', NULL, NULL, NULL),
(31, 'asset_status_delete', NULL, NULL, NULL),
(32, 'asset_status_access', NULL, NULL, NULL),
(33, 'asset_create', NULL, NULL, NULL),
(34, 'asset_edit', NULL, NULL, NULL),
(35, 'asset_show', NULL, NULL, NULL),
(36, 'asset_delete', NULL, NULL, NULL),
(37, 'asset_access', NULL, NULL, NULL),
(38, 'assets_history_access', NULL, NULL, NULL),
(39, 'task_management_access', NULL, NULL, NULL),
(40, 'task_status_create', NULL, NULL, NULL),
(41, 'task_status_edit', NULL, NULL, NULL),
(42, 'task_status_show', NULL, NULL, NULL),
(43, 'task_status_delete', NULL, NULL, NULL),
(44, 'task_status_access', NULL, NULL, NULL),
(45, 'task_tag_create', NULL, NULL, NULL),
(46, 'task_tag_edit', NULL, NULL, NULL),
(47, 'task_tag_show', NULL, NULL, NULL),
(48, 'task_tag_delete', NULL, NULL, NULL),
(49, 'task_tag_access', NULL, NULL, NULL),
(50, 'task_create', NULL, NULL, NULL),
(51, 'task_edit', NULL, NULL, NULL),
(52, 'task_show', NULL, NULL, NULL),
(53, 'task_delete', NULL, NULL, NULL),
(54, 'task_access', NULL, NULL, NULL),
(55, 'tasks_calendar_access', NULL, NULL, NULL),
(56, 'attribution_create', NULL, NULL, NULL),
(57, 'attribution_edit', NULL, NULL, NULL),
(58, 'attribution_show', NULL, NULL, NULL),
(59, 'attribution_delete', NULL, NULL, NULL),
(60, 'attribution_access', NULL, NULL, NULL),
(61, 'assignment_create', NULL, NULL, NULL),
(62, 'assignment_edit', NULL, NULL, NULL),
(63, 'assignment_show', NULL, NULL, NULL),
(64, 'assignment_delete', NULL, NULL, NULL),
(65, 'assignment_access', NULL, NULL, NULL),
(66, 'inventaire_create', NULL, NULL, NULL),
(67, 'inventaire_edit', NULL, NULL, NULL),
(68, 'inventaire_show', NULL, NULL, NULL),
(69, 'inventaire_delete', NULL, NULL, NULL),
(70, 'inventaire_access', NULL, NULL, NULL),
(71, 'supplier_create', NULL, NULL, NULL),
(72, 'supplier_edit', NULL, NULL, NULL),
(73, 'supplier_show', NULL, NULL, NULL),
(74, 'supplier_delete', NULL, NULL, NULL),
(75, 'supplier_access', NULL, NULL, NULL),
(76, 'maintenance_request_create', NULL, NULL, NULL),
(77, 'maintenance_request_edit', NULL, NULL, NULL),
(78, 'maintenance_request_show', NULL, NULL, NULL),
(79, 'maintenance_request_delete', NULL, NULL, NULL),
(80, 'maintenance_request_access', NULL, NULL, NULL),
(81, 'infrastructure_management_access', NULL, NULL, NULL),
(82, 'infrastructure_create', NULL, NULL, NULL),
(83, 'infrastructure_edit', NULL, NULL, NULL),
(84, 'infrastructure_show', NULL, NULL, NULL),
(85, 'infrastructure_delete', NULL, NULL, NULL),
(86, 'infrastructure_access', NULL, NULL, NULL),
(87, 'project_create', NULL, NULL, NULL),
(88, 'project_edit', NULL, NULL, NULL),
(89, 'project_show', NULL, NULL, NULL),
(90, 'project_delete', NULL, NULL, NULL),
(91, 'project_access', NULL, NULL, NULL),
(92, 'report_create', NULL, NULL, NULL),
(93, 'report_edit', NULL, NULL, NULL),
(94, 'report_show', NULL, NULL, NULL),
(95, 'report_delete', NULL, NULL, NULL),
(96, 'report_access', NULL, NULL, NULL),
(97, 'chef_projet_create', NULL, NULL, NULL),
(98, 'chef_projet_edit', NULL, NULL, NULL),
(99, 'chef_projet_show', NULL, NULL, NULL),
(100, 'chef_projet_delete', NULL, NULL, NULL),
(101, 'chef_projet_access', NULL, NULL, NULL),
(102, 'user_alert_create', NULL, NULL, NULL),
(103, 'user_alert_show', NULL, NULL, NULL),
(104, 'user_alert_delete', NULL, NULL, NULL),
(105, 'user_alert_access', NULL, NULL, NULL),
(106, 'bon_create', NULL, NULL, NULL),
(107, 'bon_edit', NULL, NULL, NULL),
(108, 'bon_show', NULL, NULL, NULL),
(109, 'bon_delete', NULL, NULL, NULL),
(110, 'bon_access', NULL, '2024-10-03 10:53:53', NULL),
(111, 'profile_password_edit', NULL, '2024-10-03 10:51:14', NULL),
(112, 'service_access', '2024-10-03 16:05:53', '2024-10-03 16:05:53', NULL),
(113, 'service_delete', '2024-10-03 16:06:22', '2024-10-03 16:06:22', NULL),
(114, 'service_show', '2024-10-03 16:06:46', '2024-10-03 16:06:46', NULL),
(115, 'service_edit', '2024-10-03 16:07:09', '2024-10-03 16:07:09', NULL),
(116, 'service_create', '2024-10-03 16:07:21', '2024-10-03 16:07:21', NULL),
(117, 'agent_access', '2024-10-16 12:59:08', '2024-10-16 12:59:08', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `permission_role`
--

CREATE TABLE `permission_role` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permission_role`
--

INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
(2, 81),
(2, 82),
(2, 83),
(2, 84),
(2, 85),
(2, 86),
(2, 87),
(2, 88),
(2, 89),
(2, 90),
(2, 91),
(2, 92),
(2, 93),
(2, 94),
(2, 95),
(2, 96),
(2, 97),
(2, 98),
(2, 99),
(2, 100),
(2, 101),
(2, 111),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 21),
(3, 22),
(3, 23),
(3, 24),
(3, 25),
(3, 26),
(3, 27),
(3, 28),
(3, 29),
(3, 30),
(3, 31),
(3, 32),
(3, 33),
(3, 34),
(3, 35),
(3, 36),
(3, 37),
(3, 38),
(3, 39),
(3, 40),
(3, 41),
(3, 42),
(3, 43),
(3, 44),
(3, 45),
(3, 46),
(3, 47),
(3, 48),
(3, 49),
(3, 50),
(3, 51),
(3, 52),
(3, 53),
(3, 54),
(3, 55),
(3, 56),
(3, 57),
(3, 58),
(3, 59),
(3, 60),
(3, 61),
(3, 62),
(3, 63),
(3, 64),
(3, 65),
(3, 66),
(3, 67),
(3, 68),
(3, 69),
(3, 70),
(3, 71),
(3, 72),
(3, 73),
(3, 74),
(3, 75),
(3, 76),
(3, 77),
(3, 78),
(3, 79),
(3, 80),
(3, 81),
(3, 82),
(3, 83),
(3, 84),
(3, 85),
(3, 86),
(3, 87),
(3, 88),
(3, 89),
(3, 90),
(3, 91),
(3, 92),
(3, 93),
(3, 94),
(3, 95),
(3, 96),
(3, 97),
(3, 98),
(3, 99),
(3, 100),
(3, 101),
(3, 102),
(3, 103),
(3, 104),
(3, 105),
(3, 106),
(3, 107),
(3, 108),
(3, 109),
(3, 110),
(3, 111),
(4, 17),
(4, 18),
(4, 19),
(4, 20),
(4, 21),
(4, 22),
(4, 23),
(4, 24),
(4, 25),
(4, 26),
(4, 27),
(4, 28),
(4, 29),
(4, 30),
(4, 31),
(4, 32),
(4, 33),
(4, 34),
(4, 35),
(4, 36),
(4, 37),
(4, 38),
(4, 56),
(4, 57),
(4, 58),
(4, 59),
(4, 60),
(4, 61),
(4, 62),
(4, 63),
(4, 64),
(4, 65),
(4, 66),
(4, 67),
(4, 68),
(4, 69),
(4, 70),
(4, 71),
(4, 72),
(4, 73),
(4, 74),
(4, 75),
(4, 102),
(4, 103),
(4, 104),
(4, 105),
(4, 106),
(4, 107),
(4, 108),
(4, 109),
(4, 110),
(4, 111),
(2, 102),
(2, 103),
(2, 104),
(2, 105),
(5, 39),
(5, 40),
(5, 41),
(5, 42),
(5, 43),
(5, 44),
(5, 45),
(5, 46),
(5, 47),
(5, 48),
(5, 49),
(5, 50),
(5, 51),
(5, 52),
(5, 53),
(5, 54),
(5, 55),
(5, 76),
(5, 77),
(5, 78),
(5, 79),
(5, 80),
(5, 102),
(5, 103),
(5, 104),
(5, 105),
(5, 111),
(6, 17),
(6, 18),
(6, 19),
(6, 20),
(6, 21),
(6, 22),
(6, 23),
(6, 24),
(6, 25),
(6, 26),
(6, 27),
(6, 28),
(6, 29),
(6, 30),
(6, 31),
(6, 32),
(6, 33),
(6, 34),
(6, 35),
(6, 36),
(6, 37),
(6, 38),
(6, 56),
(6, 57),
(6, 58),
(6, 59),
(6, 60),
(6, 61),
(6, 62),
(6, 63),
(6, 64),
(6, 65),
(6, 66),
(6, 67),
(6, 68),
(6, 69),
(6, 70),
(6, 71),
(6, 72),
(6, 73),
(6, 74),
(6, 75),
(6, 102),
(6, 103),
(6, 104),
(6, 105),
(6, 106),
(6, 107),
(6, 108),
(6, 109),
(6, 110),
(6, 111),
(7, 17),
(7, 18),
(7, 19),
(7, 20),
(7, 21),
(7, 22),
(7, 23),
(7, 24),
(7, 25),
(7, 26),
(7, 27),
(7, 28),
(7, 29),
(7, 30),
(7, 31),
(7, 32),
(7, 33),
(7, 34),
(7, 35),
(7, 36),
(7, 37),
(7, 38),
(7, 56),
(7, 57),
(7, 58),
(7, 59),
(7, 60),
(7, 61),
(7, 62),
(7, 63),
(7, 64),
(7, 65),
(7, 66),
(7, 67),
(7, 68),
(7, 69),
(7, 70),
(7, 71),
(7, 72),
(7, 73),
(7, 74),
(7, 75),
(7, 102),
(7, 103),
(7, 104),
(7, 105),
(7, 106),
(7, 107),
(7, 108),
(7, 109),
(7, 110),
(3, 112),
(3, 113),
(3, 114),
(3, 115),
(3, 116),
(8, 111),
(1, 117);

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Mon projet', 'construction', '2024-07-29', '2024-09-07', 'en cours', '2024-08-28 21:19:54', '2024-08-28 21:19:54', NULL),
(2, 'Construction du Nouveau Centre de Formation', 'Projet de construction d\'un centre de formation moderne à Thiès pour accueillir jusqu\'à 200 apprenants.', '2024-08-29', '2024-11-30', 'Planifié', '2024-08-29 17:21:16', '2024-08-29 17:21:16', NULL),
(3, 'Modernisation des Systèmes Informatiques', 'Mise à jour des systèmes informatiques du ministère pour améliorer la sécurité et l\'efficacité des opérations.', '2024-03-13', '2024-11-14', 'En cours', '2024-08-29 17:23:46', '2024-08-29 17:23:46', NULL),
(4, 'Extension du Centre de Formation de Thiès', 'Projet visant à augmenter la capacité d\'accueil du centre de formation de Thiès, avec la construction de nouveaux bâtiments.', '2023-03-02', '2023-12-31', 'Encours', '2024-08-29 17:31:36', '2024-08-29 17:31:36', NULL),
(5, 'Centre de Formation de Dakar', 'Centre dédié à la formation professionnelle des étudiants dans divers domaines techniques.', '2023-10-11', '2024-11-10', 'En construction', '2024-08-29 17:34:49', '2024-08-29 17:34:49', NULL),
(6, 'Rénovation des Bâtiments Administratifs', 'Rénovation complète des bureaux administratifs, y compris les systèmes électriques et les infrastructures de communication.', '2024-05-15', '2024-11-30', 'Planifié', '2024-08-29 17:36:43', '2024-08-29 17:36:43', NULL),
(7, 'Rénovation des Bâtiments Administratifs', 'Rénovation complète des bureaux administratifs, y compris les systèmes électriques et les infrastructures de communication.', '2024-05-15', '2024-11-30', 'Planifié', '2024-08-29 17:36:43', '2024-08-29 17:36:43', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `project_report`
--

CREATE TABLE `project_report` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `project_report`
--

INSERT INTO `project_report` (`report_id`, `project_id`) VALUES
(1, 4),
(2, 4);

-- --------------------------------------------------------

--
-- Structure de la table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `report_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reports`
--

INSERT INTO `reports` (`id`, `title`, `report_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Rapport Final sur l\'Extension du Centre de Formation de Thiès', '2024-08-16', '2024-08-29 17:38:46', '2024-08-29 17:38:46', NULL),
(2, 'Extension du Centre de Formation de Thiès', '2024-08-25', '2024-08-29 17:40:37', '2024-08-29 17:41:46', '2024-08-29 17:41:46');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', NULL, NULL, NULL),
(2, 'Responsable Infrastructures', NULL, '2024-08-28 20:14:49', NULL),
(3, 'Directeur', '2024-08-28 20:09:31', '2024-08-28 20:09:31', NULL),
(4, 'Responsable Matières', '2024-08-28 20:12:58', '2024-08-28 20:12:58', NULL),
(5, 'responsable Maintenance', '2024-08-28 20:16:54', '2024-08-28 20:16:54', NULL),
(6, 'Comptable principal', '2024-08-28 20:18:50', '2024-08-28 20:18:50', NULL),
(7, 'Comptable Secondaire', '2024-08-28 20:20:20', '2024-08-28 20:20:20', NULL),
(8, 'Agent simple', '2024-10-10 14:32:23', '2024-10-10 14:34:18', '2024-10-10 14:34:18');

-- --------------------------------------------------------

--
-- Structure de la table `role_user`
--

CREATE TABLE `role_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_user`
--

INSERT INTO `role_user` (`user_id`, `role_id`) VALUES
(2, 3),
(3, 2),
(4, 6),
(5, 5),
(6, 7),
(7, 4),
(8, 1),
(9, 1),
(10, 2);

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Dage', '2024-10-03 16:11:24', '2024-10-03 16:11:24', NULL),
(2, 'CI', '2024-10-10 14:27:42', '2024-10-10 14:27:42', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'EduTech Sénégal', 'contact@edutechsn.sn +221 33 123 45 67', '2024-08-28 20:40:04', '2024-08-28 20:40:04', NULL),
(2, 'InfoTech Dakar', 'sales@infotechdakar.sn +221 33 765 43 21', '2024-08-28 20:41:04', '2024-08-28 20:41:04', NULL),
(3, 'TechForm', 'info@techform.sn +221 33 456 78 90', '2024-08-28 20:42:18', '2024-08-28 20:42:18', NULL),
(4, 'Bureau Plus', 'contact@bureauplus.sn +221 33 234 56 78', '2024-08-28 20:42:42', '2024-08-28 20:42:42', NULL),
(5, 'SoftEdu Solutions', 'support@softedu.sn  +221 33 345 67 89', '2024-08-28 20:43:11', '2024-08-28 20:43:11', NULL),
(6, 'ConstrucTech', 'info@constructech.sn +221 33 678 90 12', '2024-08-28 20:44:09', '2024-08-28 20:44:09', NULL),
(7, 'GIZ', '+221 33 234 56 78', '2024-08-28 20:44:39', '2024-08-28 20:44:39', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `description`, `due_date`, `scheduled_date`, `created_at`, `updated_at`, `deleted_at`, `status_id`, `assigned_to_id`) VALUES
(1, 'Vérification mensuelle des équipements informatiques', 'Effectuer une vérification complète des équipements informatiques pour s\'assurer qu\'ils fonctionnent correctement et qu\'ils sont à jour.', '2024-08-31', '2024-09-08', '2024-08-28 20:54:37', '2024-08-28 20:54:37', NULL, 1, 3),
(2, 'Tâche de Maintenance Corrective', 'Réparation de l\'imprimante de la salle de réunion\r\n\r\nRéparer l\'imprimante qui ne fonctionne plus correctement depuis hier.', '2024-08-23', '2024-08-30', '2024-08-28 20:56:00', '2024-08-28 20:56:00', NULL, 2, NULL),
(3, 'Nettoyage des filtres de la climatisation', 'Description : Nettoyer les filtres de la climatisation pour améliorer la qualité de l\'air et l\'efficacité du système.', NULL, '2024-08-29', '2024-08-28 20:56:56', '2024-08-28 20:56:56', NULL, 2, 3);

-- --------------------------------------------------------

--
-- Structure de la table `task_statuses`
--

CREATE TABLE `task_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `task_statuses`
--

INSERT INTO `task_statuses` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Ouvrir', NULL, '2024-08-28 20:49:26', NULL),
(2, 'En cours', NULL, '2024-08-28 20:49:07', NULL),
(3, 'Fermée', NULL, '2024-08-28 20:49:15', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `task_tags`
--

CREATE TABLE `task_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `task_tags`
--

INSERT INTO `task_tags` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Maintenance', '2024-08-28 20:49:51', '2024-08-28 20:49:51', NULL),
(2, 'Urgent', '2024-08-28 20:50:00', '2024-08-28 20:50:00', NULL),
(3, 'Installation', '2024-08-28 20:50:10', '2024-08-28 20:50:10', NULL),
(4, 'Réparation', '2024-08-28 20:50:20', '2024-08-28 20:50:20', NULL),
(5, 'Formation', '2024-08-28 20:50:29', '2024-08-28 20:50:29', NULL),
(6, 'Équipement', '2024-08-28 20:50:54', '2024-08-28 20:50:54', NULL),
(7, 'Inventaire', '2024-08-28 20:51:02', '2024-08-28 20:51:02', NULL),
(8, 'Technologie', '2024-08-28 20:51:10', '2024-08-28 20:51:10', NULL),
(9, 'Bureau', '2024-08-28 20:51:28', '2024-08-28 20:51:28', NULL),
(10, 'Préventive', '2024-08-28 20:51:41', '2024-08-28 20:51:41', NULL),
(11, 'Corrective', '2024-08-28 20:51:48', '2024-08-28 20:51:48', NULL),
(12, 'Planifiée', '2024-08-28 20:51:57', '2024-08-28 20:51:57', NULL),
(13, 'Urgente', '2024-08-28 20:52:05', '2024-08-28 20:52:05', NULL),
(14, 'Révision', '2024-08-28 20:52:13', '2024-08-28 20:52:13', NULL),
(15, 'Nettoyage', '2024-08-28 20:52:23', '2024-08-28 20:52:23', NULL),
(16, 'Inspection', '2024-08-28 20:52:31', '2024-08-28 20:52:31', NULL),
(17, 'Remplacement', '2024-08-28 20:52:40', '2024-08-28 20:52:40', NULL),
(18, 'Réparation', '2024-08-28 20:52:49', '2024-08-28 20:52:49', NULL),
(19, 'Suivi', '2024-08-28 20:52:57', '2024-08-28 20:52:57', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `task_task_tag`
--

CREATE TABLE `task_task_tag` (
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `task_tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `task_task_tag`
--

INSERT INTO `task_task_tag` (`task_id`, `task_tag_id`) VALUES
(1, 10),
(1, 16),
(2, 11),
(2, 13);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `approved`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Babacar', 'babacar@gmail.com', NULL, '$2y$10$hYIzonKbogq55B4Di72p8OUEbwxYEESFGeFfC46i4YB97DfRk2Qki', 1, NULL, '2024-08-28 20:21:04', '2024-08-28 20:21:04', NULL),
(3, 'Alioune', 'alioune@gmail.com', NULL, '$2y$10$fVUiok1YC9tv9A1mQ3vIAePE0pn4zqnj6mQyGTNcMYZb1I00iPdvC', 1, NULL, '2024-08-28 20:22:04', '2024-08-28 20:22:04', NULL),
(4, 'Awa', 'awa@gmail.com', NULL, '$2y$10$pyldetvpr/srTWUYQVX4NetoHaYSMt35HOXqrjt/RHs8.lk7cOQlK', 1, NULL, '2024-08-28 20:22:23', '2024-08-28 20:22:23', NULL),
(5, 'Tapha', 'tapha@gmail.com', NULL, '$2y$10$41cGkUodUrrT9l1ihO0o.e.hvR.3OfsnL1TD5p0OE4t0VUF473Jp2', 1, NULL, '2024-08-28 20:22:55', '2024-08-28 20:22:55', NULL),
(6, 'Amina', 'amina@gmail.com', NULL, '$2y$10$JIT.LfwFQEYU5aLTpaDV7urdQ/shrLXJmaGH78MD34sK3Im3zusNC', 1, NULL, '2024-08-28 20:23:40', '2024-08-28 20:23:40', NULL),
(7, 'Abdou', 'abdou@gmail.com', NULL, '$2y$10$mh05tXZ1S0YYfXogDKwAW.VAqrTuu9GesPVSXyXfvSMG6XRQYLwHC', 1, NULL, '2024-08-28 20:24:14', '2024-08-28 20:24:14', NULL),
(8, 'Mbaye', 'amina.a@esp.sn', NULL, '$2y$10$rwTMrPAex3BaEKWhkbrBFOZ2s8uakvsGjE52X.UFqk/yG9tycT.KK', 1, NULL, '2024-10-16 09:30:38', '2024-10-16 09:30:38', NULL),
(9, 'Admin', 'admin@admin.com', NULL, '$2y$10$rqUkSZdrUJIeczs5BF2q6utyKAy4Ei.4V8s64ueuKkWaJswySyyye', 1, NULL, '2024-10-16 09:35:09', '2024-10-16 09:35:09', NULL),
(10, 'Aminata mbaye', 'aminata.a@esp.sn', NULL, '$2y$10$ixs8lO.YGyA6ZGIbkbVcPOcxfVkeIlXSfN1ye50SjjQONF9cNPXnu', 1, NULL, '2024-10-16 09:37:17', '2024-10-16 09:37:37', '2024-10-16 09:37:37');

-- --------------------------------------------------------

--
-- Structure de la table `user_alerts`
--

CREATE TABLE `user_alerts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alert_text` varchar(255) DEFAULT NULL,
  `alert_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_user_alert`
--

CREATE TABLE `user_user_alert` (
  `user_alert_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agents_email_unique` (`email`),
  ADD KEY `agents_service_id_foreign` (`service_id`);

--
-- Index pour la table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_fk_9995964` (`category_id`),
  ADD KEY `status_fk_9995968` (`status_id`),
  ADD KEY `location_fk_9995969` (`location_id`);

--
-- Index pour la table `assets_histories`
--
ALTER TABLE `assets_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_fk_9995976` (`asset_id`),
  ADD KEY `status_fk_9995977` (`status_id`),
  ADD KEY `location_fk_9995978` (`location_id`),
  ADD KEY `assigned_user_fk_9995979` (`assigned_user_id`);

--
-- Index pour la table `asset_assignment`
--
ALTER TABLE `asset_assignment`
  ADD KEY `assignment_id_fk_10016937` (`assignment_id`),
  ADD KEY `asset_id_fk_10016937` (`asset_id`);

--
-- Index pour la table `asset_bon`
--
ALTER TABLE `asset_bon`
  ADD KEY `asset_id_fk_10016574` (`asset_id`),
  ADD KEY `bon_id_fk_10016574` (`bon_id`);

--
-- Index pour la table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `asset_inventaire`
--
ALTER TABLE `asset_inventaire`
  ADD KEY `asset_id_fk_10081118` (`asset_id`),
  ADD KEY `inventaire_id_fk_10081118` (`inventaire_id`);

--
-- Index pour la table `asset_locations`
--
ALTER TABLE `asset_locations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `asset_statuses`
--
ALTER TABLE `asset_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `asset_supplier`
--
ALTER TABLE `asset_supplier`
  ADD KEY `asset_id_fk_10016534` (`asset_id`),
  ADD KEY `supplier_id_fk_10016534` (`supplier_id`);

--
-- Index pour la table `asset_task`
--
ALTER TABLE `asset_task`
  ADD KEY `task_id_fk_9996163` (`task_id`),
  ADD KEY `asset_id_fk_9996163` (`asset_id`);

--
-- Index pour la table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `assignment_attribution`
--
ALTER TABLE `assignment_attribution`
  ADD KEY `assignment_id_fk_10016939` (`assignment_id`),
  ADD KEY `attribution_id_fk_10016939` (`attribution_id`);

--
-- Index pour la table `assignment_service`
--
ALTER TABLE `assignment_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_service_assignment_id_foreign` (`assignment_id`),
  ADD KEY `assignment_service_service_id_foreign` (`service_id`);

--
-- Index pour la table `attributions`
--
ALTER TABLE `attributions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `bons`
--
ALTER TABLE `bons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bons_organisation_unique` (`organisation`);

--
-- Index pour la table `chef_projets`
--
ALTER TABLE `chef_projets`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `chef_projet_project`
--
ALTER TABLE `chef_projet_project`
  ADD KEY `project_id_fk_10016902` (`project_id`),
  ADD KEY `chef_projet_id_fk_10016902` (`chef_projet_id`);

--
-- Index pour la table `infrastructures`
--
ALTER TABLE `infrastructures`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `infrastructure_project`
--
ALTER TABLE `infrastructure_project`
  ADD KEY `project_id_fk_9996231` (`project_id`),
  ADD KEY `infrastructure_id_fk_9996231` (`infrastructure_id`);

--
-- Index pour la table `inventaires`
--
ALTER TABLE `inventaires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `maintenance_request_task`
--
ALTER TABLE `maintenance_request_task`
  ADD KEY `maintenance_request_id_fk_9996164` (`maintenance_request_id`),
  ADD KEY `task_id_fk_9996164` (`task_id`);

--
-- Index pour la table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `permission_role`
--
ALTER TABLE `permission_role`
  ADD KEY `role_id_fk_9988358` (`role_id`),
  ADD KEY `permission_id_fk_9988358` (`permission_id`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `project_report`
--
ALTER TABLE `project_report`
  ADD KEY `report_id_fk_9996239` (`report_id`),
  ADD KEY `project_id_fk_9996239` (`project_id`);

--
-- Index pour la table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `role_user`
--
ALTER TABLE `role_user`
  ADD KEY `user_id_fk_9988367` (`user_id`),
  ADD KEY `role_id_fk_9988367` (`role_id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status_fk_9995995` (`status_id`),
  ADD KEY `assigned_to_fk_9995999` (`assigned_to_id`);

--
-- Index pour la table `task_statuses`
--
ALTER TABLE `task_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `task_tags`
--
ALTER TABLE `task_tags`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `task_task_tag`
--
ALTER TABLE `task_task_tag`
  ADD KEY `task_id_fk_9995996` (`task_id`),
  ADD KEY `task_tag_id_fk_9995996` (`task_tag_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Index pour la table `user_alerts`
--
ALTER TABLE `user_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user_user_alert`
--
ALTER TABLE `user_user_alert`
  ADD KEY `user_alert_id_fk_9999131` (`user_alert_id`),
  ADD KEY `user_id_fk_9999131` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `assets_histories`
--
ALTER TABLE `assets_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `asset_locations`
--
ALTER TABLE `asset_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `asset_statuses`
--
ALTER TABLE `asset_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `assignment_service`
--
ALTER TABLE `assignment_service`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `attributions`
--
ALTER TABLE `attributions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `bons`
--
ALTER TABLE `bons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `chef_projets`
--
ALTER TABLE `chef_projets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `infrastructures`
--
ALTER TABLE `infrastructures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `inventaires`
--
ALTER TABLE `inventaires`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `task_statuses`
--
ALTER TABLE `task_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `task_tags`
--
ALTER TABLE `task_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user_alerts`
--
ALTER TABLE `user_alerts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Contraintes pour la table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `category_fk_9995964` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`),
  ADD CONSTRAINT `location_fk_9995969` FOREIGN KEY (`location_id`) REFERENCES `asset_locations` (`id`),
  ADD CONSTRAINT `status_fk_9995968` FOREIGN KEY (`status_id`) REFERENCES `asset_statuses` (`id`);

--
-- Contraintes pour la table `assets_histories`
--
ALTER TABLE `assets_histories`
  ADD CONSTRAINT `asset_fk_9995976` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `assigned_user_fk_9995979` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `location_fk_9995978` FOREIGN KEY (`location_id`) REFERENCES `asset_locations` (`id`),
  ADD CONSTRAINT `status_fk_9995977` FOREIGN KEY (`status_id`) REFERENCES `asset_statuses` (`id`);

--
-- Contraintes pour la table `asset_assignment`
--
ALTER TABLE `asset_assignment`
  ADD CONSTRAINT `asset_id_fk_10016937` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_id_fk_10016937` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `asset_bon`
--
ALTER TABLE `asset_bon`
  ADD CONSTRAINT `asset_id_fk_10016574` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bon_id_fk_10016574` FOREIGN KEY (`bon_id`) REFERENCES `bons` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `asset_inventaire`
--
ALTER TABLE `asset_inventaire`
  ADD CONSTRAINT `asset_id_fk_10081118` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventaire_id_fk_10081118` FOREIGN KEY (`inventaire_id`) REFERENCES `inventaires` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `asset_supplier`
--
ALTER TABLE `asset_supplier`
  ADD CONSTRAINT `asset_id_fk_10016534` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_id_fk_10016534` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `asset_task`
--
ALTER TABLE `asset_task`
  ADD CONSTRAINT `asset_id_fk_9996163` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_id_fk_9996163` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `assignment_attribution`
--
ALTER TABLE `assignment_attribution`
  ADD CONSTRAINT `assignment_id_fk_10016939` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attribution_id_fk_10016939` FOREIGN KEY (`attribution_id`) REFERENCES `attributions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `assignment_service`
--
ALTER TABLE `assignment_service`
  ADD CONSTRAINT `assignment_service_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `chef_projet_project`
--
ALTER TABLE `chef_projet_project`
  ADD CONSTRAINT `chef_projet_id_fk_10016902` FOREIGN KEY (`chef_projet_id`) REFERENCES `chef_projets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_id_fk_10016902` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `infrastructure_project`
--
ALTER TABLE `infrastructure_project`
  ADD CONSTRAINT `infrastructure_id_fk_9996231` FOREIGN KEY (`infrastructure_id`) REFERENCES `infrastructures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_id_fk_9996231` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `maintenance_request_task`
--
ALTER TABLE `maintenance_request_task`
  ADD CONSTRAINT `maintenance_request_id_fk_9996164` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintenance_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_id_fk_9996164` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_id_fk_9988358` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_id_fk_9988358` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `project_report`
--
ALTER TABLE `project_report`
  ADD CONSTRAINT `project_id_fk_9996239` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_id_fk_9996239` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_id_fk_9988367` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_id_fk_9988367` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `assigned_to_fk_9995999` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `status_fk_9995995` FOREIGN KEY (`status_id`) REFERENCES `task_statuses` (`id`);

--
-- Contraintes pour la table `task_task_tag`
--
ALTER TABLE `task_task_tag`
  ADD CONSTRAINT `task_id_fk_9995996` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_tag_id_fk_9995996` FOREIGN KEY (`task_tag_id`) REFERENCES `task_tags` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_user_alert`
--
ALTER TABLE `user_user_alert`
  ADD CONSTRAINT `user_alert_id_fk_9999131` FOREIGN KEY (`user_alert_id`) REFERENCES `user_alerts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_id_fk_9999131` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
