CREATE DATABASE IF NOT EXISTS hadj_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE hadj_platform;

CREATE TABLE IF NOT EXISTS User (
    nin VARCHAR(18) PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    prenom_pere VARCHAR(100),
    nom_mere VARCHAR(100),
    prenom_mere VARCHAR(100),
    date_naiss DATE,
    adresse TEXT,
    email VARCHAR(150) UNIQUE NOT NULL,
    tel VARCHAR(20),
    psswd VARCHAR(255) NOT NULL,
    etat_compte INT NOT NULL DEFAULT 1, 
    role INT NOT NULL DEFAULT 2 
);

CREATE TABLE IF NOT EXISTS Tirage (
    id_tirage INT AUTO_INCREMENT PRIMARY KEY,
    date_ouverture_insc DATE,
    date_cloture_insc DATE,
    date_tirage DATE,
    nbr_gagnants INT,
    etat_tirage INT 
);

CREATE TABLE IF NOT EXISTS Inscrits (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    nin VARCHAR(18),
    id_tirage INT,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nin) REFERENCES User(nin) ON DELETE CASCADE,
    FOREIGN KEY (id_tirage) REFERENCES Tirage(id_tirage) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Resultats (
    id_resultat INT AUTO_INCREMENT PRIMARY KEY,
    nin VARCHAR(18),
    id_tirage INT,
    FOREIGN KEY (nin) REFERENCES User(nin) ON DELETE CASCADE,
    FOREIGN KEY (id_tirage) REFERENCES Tirage(id_tirage) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Notifications (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    nin VARCHAR(18),
    message TEXT NOT NULL,
    etat_notification INT DEFAULT 1, 
    FOREIGN KEY (nin) REFERENCES User(nin) ON DELETE CASCADE
);


INSERT IGNORE INTO User (nin, nom, prenom, email, psswd, etat_compte, role)
VALUES ('123456789012345678', 'Admin', 'Super', 'admin@hadj.dz', '$2y$10$eE.AN6yWs1e84wNFtlq3xeVaQOWucKmG4EpezufgS3.AHyYXeK0BO', 1, 1);
