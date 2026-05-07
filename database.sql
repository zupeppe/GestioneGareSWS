CREATE DATABASE IF NOT EXISTS gestione_gare_sws CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestione_gare_sws;

CREATE TABLE gare (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_gara VARCHAR(255) NOT NULL,
    data_evento DATETIME NOT NULL,
    durata_minuti INT DEFAULT 0,
    stato ENUM('setup', 'in_corso', 'finita') DEFAULT 'setup'
) ENGINE=InnoDB;

CREATE TABLE piloti_mio_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_team VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE iscritti_gara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gara_id INT NOT NULL,
    team_id INT NOT NULL,
    numero_gara INT,
    FOREIGN KEY (gara_id) REFERENCES gare(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE kart_gara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gara_id INT NOT NULL,
    numero_kart INT NOT NULL,
    rating TINYINT DEFAULT 0 COMMENT '0: ignoto, 1: scarso, 2: medio, 3: buono',
    ultima_fila VARCHAR(50),
    note TEXT,
    FOREIGN KEY (gara_id) REFERENCES gare(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stint_mio_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gara_id INT NOT NULL,
    pilota_id INT NOT NULL,
    kart_id INT,
    minuto_ingresso INT NOT NULL,
    minuto_uscita INT,
    note TEXT,
    FOREIGN KEY (gara_id) REFERENCES gare(id) ON DELETE CASCADE,
    FOREIGN KEY (pilota_id) REFERENCES piloti_mio_team(id) ON DELETE CASCADE,
    FOREIGN KEY (kart_id) REFERENCES kart_gara(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE monitoraggio_pit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gara_id INT NOT NULL,
    iscritto_gara_id INT NOT NULL,
    kart_lasciato_id INT,
    kart_preso_id INT,
    fila_colore VARCHAR(50),
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (gara_id) REFERENCES gare(id) ON DELETE CASCADE,
    FOREIGN KEY (iscritto_gara_id) REFERENCES iscritti_gara(id) ON DELETE CASCADE,
    FOREIGN KEY (kart_lasciato_id) REFERENCES kart_gara(id) ON DELETE SET NULL,
    FOREIGN KEY (kart_preso_id) REFERENCES kart_gara(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE file_pit_gara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gara_id INT NOT NULL,
    nome_colore VARCHAR(50) NOT NULL,
    ordine INT DEFAULT 0,
    FOREIGN KEY (gara_id) REFERENCES gare(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE piloti_gara (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gara_id INT NOT NULL,
    pilota_id INT NOT NULL,
    FOREIGN KEY (gara_id) REFERENCES gare(id) ON DELETE CASCADE,
    FOREIGN KEY (pilota_id) REFERENCES piloti_mio_team(id) ON DELETE CASCADE
) ENGINE=InnoDB;
