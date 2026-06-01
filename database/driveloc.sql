-- ==============================================================
-- DriveLoc – Script SQL complet
-- Compatible : MySQL 8 / phpMyAdmin
-- Encodage   : utf8mb4
-- ==============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
SET NAMES utf8mb4;

-- --------------------------------------------------------------
-- Création et sélection de la base
-- --------------------------------------------------------------
DROP DATABASE IF EXISTS driveloc;
CREATE DATABASE driveloc
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE driveloc;

-- ==============================================================
-- TABLE : categories
-- Référentiel des types de véhicules
-- ==============================================================
CREATE TABLE categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_categories      PRIMARY KEY (id),
    CONSTRAINT uq_categories_name UNIQUE (name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Catégories de véhicules disponibles sur la plateforme';

-- ==============================================================
-- TABLE : users
-- Tous les comptes (clients, propriétaires, administrateurs)
-- ==============================================================
CREATE TABLE users (
    id         INT UNSIGNED                          NOT NULL AUTO_INCREMENT,
    firstname  VARCHAR(100)                          NOT NULL,
    lastname   VARCHAR(100)                          NOT NULL,
    email      VARCHAR(255)                          NOT NULL,
    password   VARCHAR(255)                          NOT NULL COMMENT 'Hash bcrypt – généré via password_hash()',
    role       ENUM('client', 'owner', 'admin')      NOT NULL DEFAULT 'client',
    status     ENUM('active', 'inactive')            NOT NULL DEFAULT 'active',
    created_at DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_users       PRIMARY KEY (id),
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT chk_users_email CHECK (email REGEXP '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$')
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Comptes utilisateurs – rôles : client, owner, admin';

-- ==============================================================
-- TABLE : vehicles
-- Véhicules publiés par les propriétaires
-- ==============================================================
CREATE TABLE vehicles (
    id             INT UNSIGNED                  NOT NULL AUTO_INCREMENT,
    owner_id       INT UNSIGNED                  NOT NULL COMMENT 'FK → users.id (rôle owner)',
    category_id    INT UNSIGNED                  NOT NULL COMMENT 'FK → categories.id',
    title          VARCHAR(255)                  NOT NULL,
    brand          VARCHAR(100)                  NOT NULL,
    model          VARCHAR(100)                  NOT NULL,
    registration   VARCHAR(20)                   NOT NULL COMMENT 'Immatriculation ou référence interne',
    price_per_day  DECIMAL(10, 2)                NOT NULL,
    description    TEXT                              NULL,
    main_image     VARCHAR(255)                      NULL COMMENT 'Nom de fichier de la photo principale',
    status         ENUM('active', 'inactive')    NOT NULL DEFAULT 'active',
    created_at     DATETIME                      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_vehicles          PRIMARY KEY (id),
    CONSTRAINT fk_vehicles_owner    FOREIGN KEY (owner_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_vehicles_category FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT chk_vehicles_price   CHECK (price_per_day > 0)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Annonces de véhicules à louer';

-- ==============================================================
-- TABLE : vehicle_images
-- Photos supplémentaires (galerie) liées à un véhicule
-- ==============================================================
CREATE TABLE vehicle_images (
    id          INT UNSIGNED         NOT NULL AUTO_INCREMENT,
    vehicle_id  INT UNSIGNED         NOT NULL COMMENT 'FK → vehicles.id',
    filename    VARCHAR(255)         NOT NULL COMMENT 'Nom du fichier uploadé',
    sort_order  TINYINT UNSIGNED     NOT NULL DEFAULT 0 COMMENT 'Ordre d'affichage dans la galerie',
    uploaded_at DATETIME             NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_vehicle_images         PRIMARY KEY (id),
    CONSTRAINT fk_vehicle_images_vehicle FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Images supplémentaires des véhicules (galerie)';

-- ==============================================================
-- TABLE : reservations
-- Réservations effectuées par les clients
-- ==============================================================
CREATE TABLE reservations (
    id          INT UNSIGNED                                        NOT NULL AUTO_INCREMENT,
    vehicle_id  INT UNSIGNED                                        NOT NULL COMMENT 'FK → vehicles.id',
    client_id   INT UNSIGNED                                        NOT NULL COMMENT 'FK → users.id (rôle client)',
    start_date  DATE                                                NOT NULL,
    end_date    DATE                                                NOT NULL,
    total_price DECIMAL(10, 2)                                      NOT NULL COMMENT 'price_per_day × nb jours',
    status      ENUM('upcoming', 'ongoing', 'done', 'cancelled')   NOT NULL DEFAULT 'upcoming',
    created_at  DATETIME                                            NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_reservations         PRIMARY KEY (id),
    CONSTRAINT fk_reservations_vehicle FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_reservations_client  FOREIGN KEY (client_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT chk_reservations_dates  CHECK (end_date > start_date),
    CONSTRAINT chk_reservations_price  CHECK (total_price > 0)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Réservations de véhicules';

-- ==============================================================
-- INDEX DE RECHERCHE
-- ==============================================================

-- Connexion : lookup email (requête la plus fréquente)
CREATE INDEX idx_users_email
    ON users(email);

-- Véhicules actifs par catégorie (page listing / recherche)
CREATE INDEX idx_vehicles_category_status
    ON vehicles(category_id, status);

-- Tous les véhicules d'un propriétaire (dashboard owner)
CREATE INDEX idx_vehicles_owner
    ON vehicles(owner_id);

-- Vérification de disponibilité (requête critique avant réservation)
CREATE INDEX idx_reservations_vehicle_dates
    ON reservations(vehicle_id, start_date, end_date);

-- Tableau de bord client : mes réservations par statut
CREATE INDEX idx_reservations_client_status
    ON reservations(client_id, status);

-- Galerie triée d'un véhicule
CREATE INDEX idx_vehicle_images_vehicle_order
    ON vehicle_images(vehicle_id, sort_order);

-- ==============================================================
-- DONNÉES INITIALES
-- ==============================================================

-- Catégories par défaut
INSERT INTO categories (name) VALUES
    ('Citadine'),
    ('Berline'),
    ('SUV'),
    ('Utilitaire'),
    ('Moto'),
    ('Vélo électrique');

-- Compte administrateur par défaut
-- Mot de passe : Admin1234!
-- Hash généré via : password_hash('Admin1234!', PASSWORD_BCRYPT, ['cost' => 12])
-- /!\ Changer le mot de passe dès la première connexion
INSERT INTO users (firstname, lastname, email, password, role, status) VALUES
    (
        'Admin',
        'DriveLoc',
        'admin@driveloc.fr',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        'active'
    );

-- ==============================================================
-- RÉACTIVATION DES CONTRAINTES
-- ==============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================
-- FIN DU SCRIPT
-- ==============================================================
