
CREATE DATABASE IF NOT EXISTS campus_event CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_event;

CREATE TABLE utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    date_naissance DATE,
    filiere VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    verification_token VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    role ENUM('admin', 'participant', 'organisateur') DEFAULT 'membre',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE club (
    id_club INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    id_organisateur INT NOT NULL,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    INDEX idx_organisateur (id_organisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE evenement (
    id_evenement INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    lieu VARCHAR(255),
    id_club INT NOT NULL,
    id_organisateur INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_club) REFERENCES club(id_club) ON DELETE CASCADE,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    INDEX idx_date (date),
    INDEX idx_club (id_club),
    INDEX idx_organisateur (id_organisateur)
        capacite INT NULL DEFAULT NULL,
    tarif DECIMAL(10,2) DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fichier (
    id_fichier INT PRIMARY KEY AUTO_INCREMENT,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email (
    id_email INT PRIMARY KEY AUTO_INCREMENT,
    sujet VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    expediteur INT NOT NULL,
    FOREIGN KEY (expediteur) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attestation (
    id_attestation INT PRIMARY KEY AUTO_INCREMENT,
    type_attestation VARCHAR(100) NOT NULL,
    date_delivrance DATE NOT NULL,
    id_evenement INT NOT NULL,
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE utilisateur_evenement (
    id_utilisateur INT NOT NULL,
    id_evenement INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_evenement),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE,
        statut ENUM('en_attente', 'accepté', 'refusé', 'annulé') DEFAULT 'en_attente',
    paiement ENUM('non_payé', 'payé', 'remboursé') DEFAULT 'non_payé',
    INDEX idx_date_inscription (date_inscription)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE fichier_evenement (
    id_fichier INT NOT NULL,
    id_evenement INT NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_fichier, id_evenement),
    FOREIGN KEY (id_fichier) REFERENCES fichier(id_fichier) ON DELETE CASCADE,
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE email_utilisateur (
    id_email INT NOT NULL,
    id_utilisateur INT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_email, id_utilisateur),
    FOREIGN KEY (id_email) REFERENCES email(id_email) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attestation_utilisateur (
    id_attestation INT NOT NULL,
    id_utilisateur INT NOT NULL,
    date_attribution TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_attestation, id_utilisateur),
    FOREIGN KEY (id_attestation) REFERENCES attestation(id_attestation) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Insertion d'un utilisateur admin par défaut
INSERT INTO utilisateur (nom, prenom, email, password, role, is_verified) 
VALUES ('Jihane', 'CHOUHE', 'jihane@campusevents.ma', '$2y$10$G2aU6X0x9WCV2E74DEJ6W.XwlnlBepxBKkWXrbgPKVOkCi5s5WoL2', 'admin', TRUE); --mot de passe admin@GI
