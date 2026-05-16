-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mag 16, 2026 alle 07:40
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestione_gare_sws`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `file_pit_gara`
--

CREATE TABLE `file_pit_gara` (
  `id` int(11) NOT NULL,
  `gara_id` int(11) NOT NULL,
  `nome_colore` varchar(50) NOT NULL,
  `ordine` int(11) DEFAULT 0,
  `colore_hex` varchar(7) DEFAULT '#343a40'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `gare`
--

CREATE TABLE `gare` (
  `id` int(11) NOT NULL,
  `nome_gara` varchar(255) NOT NULL,
  `data_evento` datetime NOT NULL,
  `durata_minuti` int(11) DEFAULT 0,
  `min_stint` int(11) DEFAULT 0,
  `tempo_minimo_pit` int(11) DEFAULT 0,
  `durata_max_stint` int(11) DEFAULT 0,
  `durata_min_stint` int(11) DEFAULT NULL,
  `stato` enum('setup','in_corso','finita') DEFAULT 'setup',
  `mio_team_id` int(11) DEFAULT NULL,
  `tempo_max_pilota` int(11) DEFAULT 0 COMMENT 'Tempo massimo di guida per pilota (minuti)',
  `tempo_min_pilota` int(11) DEFAULT 0 COMMENT 'Tempo minimo di guida per pilota (minuti)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `iscritti_gara`
--

CREATE TABLE `iscritti_gara` (
  `id` int(11) NOT NULL,
  `gara_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `numero_gara` int(11) DEFAULT NULL,
  `is_gestito` tinyint(4) DEFAULT 0 COMMENT '0: non gestito, 1: team della nostra scuderia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `kart_gara`
--

CREATE TABLE `kart_gara` (
  `id` int(11) NOT NULL,
  `gara_id` int(11) NOT NULL,
  `numero_kart` int(11) NOT NULL,
  `rating` tinyint(4) DEFAULT 0 COMMENT '0: ignoto, 1: scarso, 2: medio, 3: buono, 4: bomba, 5: best lap',
  `ultima_fila` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `monitoraggio_pit`
--

CREATE TABLE `monitoraggio_pit` (
  `id` int(11) NOT NULL,
  `gara_id` int(11) NOT NULL,
  `iscritto_gara_id` int(11) NOT NULL,
  `kart_lasciato_id` int(11) DEFAULT NULL,
  `kart_preso_id` int(11) DEFAULT NULL,
  `fila_colore` varchar(50) DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `stato` enum('attivo','annullato') DEFAULT 'attivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `piloti_gara`
--

CREATE TABLE `piloti_gara` (
  `id` int(11) NOT NULL,
  `gara_id` int(11) NOT NULL,
  `pilota_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `piloti_mio_team`
--

CREATE TABLE `piloti_mio_team` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cognome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessioni`
--

CREATE TABLE `sessioni` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attivo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `stint_mio_team`
--

CREATE TABLE `stint_mio_team` (
  `id` int(11) NOT NULL,
  `gara_id` int(11) NOT NULL,
  `pilota_id` int(11) NOT NULL,
  `kart_id` int(11) DEFAULT NULL,
  `minuto_ingresso` int(11) NOT NULL DEFAULT 0,
  `durata_minuti` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `cancellato` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=attivo, 1=cancellato soft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `nome_team` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `ruolo` enum('admin','team_manager','muretto','spotter') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `attivo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `file_pit_gara`
--
ALTER TABLE `file_pit_gara`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gara_id` (`gara_id`);

--
-- Indici per le tabelle `gare`
--
ALTER TABLE `gare`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mio_team` (`mio_team_id`);

--
-- Indici per le tabelle `iscritti_gara`
--
ALTER TABLE `iscritti_gara`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gara_id` (`gara_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indici per le tabelle `kart_gara`
--
ALTER TABLE `kart_gara`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gara_id` (`gara_id`);

--
-- Indici per le tabelle `monitoraggio_pit`
--
ALTER TABLE `monitoraggio_pit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gara_id` (`gara_id`),
  ADD KEY `iscritto_gara_id` (`iscritto_gara_id`),
  ADD KEY `kart_lasciato_id` (`kart_lasciato_id`),
  ADD KEY `kart_preso_id` (`kart_preso_id`);

--
-- Indici per le tabelle `piloti_gara`
--
ALTER TABLE `piloti_gara`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gara_id` (`gara_id`),
  ADD KEY `pilota_id` (`pilota_id`),
  ADD KEY `idx_team_id` (`team_id`);

--
-- Indici per le tabelle `piloti_mio_team`
--
ALTER TABLE `piloti_mio_team`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `sessioni`
--
ALTER TABLE `sessioni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session` (`session_id`);

--
-- Indici per le tabelle `stint_mio_team`
--
ALTER TABLE `stint_mio_team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gara_id` (`gara_id`),
  ADD KEY `pilota_id` (`pilota_id`),
  ADD KEY `kart_id` (`kart_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indici per le tabelle `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_utenti_username` (`username`),
  ADD KEY `idx_utenti_ruolo` (`ruolo`),
  ADD KEY `idx_utenti_attivo` (`attivo`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `file_pit_gara`
--
ALTER TABLE `file_pit_gara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `gare`
--
ALTER TABLE `gare`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `iscritti_gara`
--
ALTER TABLE `iscritti_gara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `kart_gara`
--
ALTER TABLE `kart_gara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `monitoraggio_pit`
--
ALTER TABLE `monitoraggio_pit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `piloti_gara`
--
ALTER TABLE `piloti_gara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `piloti_mio_team`
--
ALTER TABLE `piloti_mio_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `sessioni`
--
ALTER TABLE `sessioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `stint_mio_team`
--
ALTER TABLE `stint_mio_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `file_pit_gara`
--
ALTER TABLE `file_pit_gara`
  ADD CONSTRAINT `file_pit_gara_ibfk_1` FOREIGN KEY (`gara_id`) REFERENCES `gare` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `gare`
--
ALTER TABLE `gare`
  ADD CONSTRAINT `fk_mio_team` FOREIGN KEY (`mio_team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `iscritti_gara`
--
ALTER TABLE `iscritti_gara`
  ADD CONSTRAINT `iscritti_gara_ibfk_1` FOREIGN KEY (`gara_id`) REFERENCES `gare` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `iscritti_gara_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `kart_gara`
--
ALTER TABLE `kart_gara`
  ADD CONSTRAINT `kart_gara_ibfk_1` FOREIGN KEY (`gara_id`) REFERENCES `gare` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `monitoraggio_pit`
--
ALTER TABLE `monitoraggio_pit`
  ADD CONSTRAINT `monitoraggio_pit_ibfk_1` FOREIGN KEY (`gara_id`) REFERENCES `gare` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoraggio_pit_ibfk_2` FOREIGN KEY (`iscritto_gara_id`) REFERENCES `iscritti_gara` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitoraggio_pit_ibfk_3` FOREIGN KEY (`kart_lasciato_id`) REFERENCES `kart_gara` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `monitoraggio_pit_ibfk_4` FOREIGN KEY (`kart_preso_id`) REFERENCES `kart_gara` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `piloti_gara`
--
ALTER TABLE `piloti_gara`
  ADD CONSTRAINT `fk_piloti_gara_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `piloti_gara_ibfk_1` FOREIGN KEY (`gara_id`) REFERENCES `gare` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `piloti_gara_ibfk_2` FOREIGN KEY (`pilota_id`) REFERENCES `piloti_mio_team` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `stint_mio_team`
--
ALTER TABLE `stint_mio_team`
  ADD CONSTRAINT `stint_mio_team_ibfk_1` FOREIGN KEY (`gara_id`) REFERENCES `gare` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stint_mio_team_ibfk_2` FOREIGN KEY (`pilota_id`) REFERENCES `piloti_mio_team` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stint_mio_team_ibfk_3` FOREIGN KEY (`kart_id`) REFERENCES `kart_gara` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stint_mio_team_ibfk_4` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
