
-- ============================================================
-- Base de données : TDW
-- Projet : Application Web de Gestion d’un Laboratoire Informatique Universitaire
-- Auteur : Dr. Dellys Hachemi
-- Date de création : 2025-10-29
-- ============================================================

-- 1. Database
CREATE DATABASE IF NOT EXISTS TDW CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE TDW;

-- ============================================================
--  2.  users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    photo VARCHAR(255),
    grade VARCHAR(100) NOT NULL, -- status
    poste VARCHAR(100),
    domaine_recherche VARCHAR(255),
    role ENUM('admin','enseignant-chercheur','doctorant','etudiant','invite') DEFAULT 'enseignant-chercheur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  3.  equipes
-- ============================================================
CREATE TABLE IF NOT EXISTS teams (
    id_team INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) UNIQUE NOT NULL,
    description TEXT,
    chef_id INT,
    FOREIGN KEY (chef_id) REFERENCES users(id_user) ON DELETE SET NULL
);

-- ============================================================
--  4.  equipes_membres
-- ============================================================
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    usr_id INT NOT NULL,
    role_dans_equipe VARCHAR(100),
    FOREIGN KEY (team_id) REFERENCES teams(id_team) ON DELETE CASCADE,
    FOREIGN KEY (usr_id) REFERENCES users(id_user) ON DELETE CASCADE
);