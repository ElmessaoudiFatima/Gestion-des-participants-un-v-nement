
CREATE DATABASE IF NOT EXISTS campus_events CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_event;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    date_naissance DATE NOT NULL,
    filiere VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    verification_token VARCHAR(64),
    is_verified TINYINT(1) DEFAULT 0,
    role ENUM('participant', 'admin', 'organisateur') DEFAULT 'participant',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table club
CREATE TABLE IF NOT EXISTS club (
    id_club INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    id_organisateur INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateurs(id) ON DELETE RESTRICT,
    INDEX idx_organisateur (id_organisateur),
    INDEX idx_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table evenement 
CREATE TABLE IF NOT EXISTS evenement (
    id_evenement INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    heure TIME NOT NULL,
    lieu VARCHAR(255) NOT NULL,
    capacite INT NOT NULL DEFAULT NULL,
    tarif DECIMAL(10,2) DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    id_club INT NOT NULL,
    id_organisateur INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_club) REFERENCES club(id_club) ON DELETE CASCADE,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateurs(id) ON DELETE RESTRICT,
    INDEX idx_date (date),
    INDEX idx_club (id_club),
    INDEX idx_organisateur (id_organisateur),
    INDEX idx_titre (titre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table participer_events 
CREATE TABLE IF NOT EXISTS participer_events (
    id_participation INT PRIMARY KEY AUTO_INCREMENT,
    id_evenement INT NOT NULL,
    id_participant INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en attente', 'annule', 'refuse', 'accepte') DEFAULT 'en attente',
    paiement ENUM('non paye', 'paye', 'rembourse') DEFAULT 'non paye',
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE,
    FOREIGN KEY (id_participant) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (id_evenement, id_participant),
    INDEX idx_evenement (id_evenement),
    INDEX idx_participant (id_participant),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

