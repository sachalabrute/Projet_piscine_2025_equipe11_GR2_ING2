-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 26 mai 2025 à 13:19
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `omnes_immobilier`
--

-- --------------------------------------------------------

--
-- Structure de la table `agents`
--

DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `specialite` varchar(50) DEFAULT NULL,
  `cv` text,
  `planning` text,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `agents`
--

INSERT INTO `agents` (`id`, `utilisateur_id`, `telephone`, `specialite`, `cv`, `planning`) VALUES
(1, 1, '0102030405', 'Immobilier résidentiel', 'Expert en vente de maisons et d\'appartements.', '[\"AM\",\"PM\",\"AM/PM\",\"\",\"\",\"\"]');

-- --------------------------------------------------------

--
-- Structure de la table `biens`
--

DROP TABLE IF EXISTS `biens`;
CREATE TABLE IF NOT EXISTS `biens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) DEFAULT NULL,
  `description` text,
  `categorie` enum('résidentiel','commercial','terrain','location','enchère') DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `agent_id` int DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `digicode` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `biens`
--

INSERT INTO `biens` (`id`, `titre`, `description`, `categorie`, `prix`, `agent_id`, `adresse`, `digicode`) VALUES
(1, 'Grande maison familiale', 'Maison de 7 pièces avec jardin.', 'résidentiel', 450000.00, 1, NULL, NULL),
(2, 'Bureau moderne', 'Espace de travail dans le centre-ville.', 'commercial', 150000.00, 1, NULL, NULL),
(3, 'Terrain constructible', 'Terrain plat, idéal pour construire.', 'terrain', 90000.00, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rdv`
--

DROP TABLE IF EXISTS `rdv`;
CREATE TABLE IF NOT EXISTS `rdv` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `agent_id` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure` time DEFAULT NULL,
  `statut` enum('confirmé','annulé') DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `digicode` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `agent_id` (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('client','agent','admin') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `mot_de_passe`, `role`) VALUES
(1, 'Jean-Pierre SEGADO', 'jean-pierre.segado@omnesimmobilier.fr', 'test', 'agent');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `biens`
--
ALTER TABLE `biens`
  ADD CONSTRAINT `biens_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`);

--
-- Contraintes pour la table `rdv`
--
ALTER TABLE `rdv`
  ADD CONSTRAINT `rdv_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `rdv_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
