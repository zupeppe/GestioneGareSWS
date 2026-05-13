-- Migrazione per aggiungere team_id alle tabelle piloti_gara e stint_mio_team
-- Esegui queste query per aggiornare il database esistente

-- 1. Aggiungi colonna team_id a piloti_gara
ALTER TABLE piloti_gara 
ADD COLUMN team_id INT NULL AFTER pilota_id,
ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL;

-- 2. Aggiungi colonna team_id a stint_mio_team  
ALTER TABLE stint_mio_team
ADD COLUMN team_id INT NULL AFTER pilota_id,
ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL;

-- 3. Popola team_id esistente (opzionale - solo se hai dati esistenti da migrare)
-- Questa query assegna team_id basandosi su iscritti_gara.is_gestito = 1
-- NOTA: Esegui solo se hai dati esistenti che vuoi migrare

-- UPDATE piloti_gara pg
-- JOIN iscritti_gara ig ON pg.gara_id = ig.gara_id 
-- SET pg.team_id = ig.team_id 
-- WHERE ig.is_gestito = 1;

-- UPDATE stint_mio_team smt
-- JOIN piloti_gara pg ON smt.gara_id = pg.gara_id AND smt.pilota_id = pg.pilota_id
-- SET smt.team_id = pg.team_id
-- WHERE pg.team_id IS NOT NULL;

-- 4. Indici per performance
CREATE INDEX idx_piloti_gara_team ON piloti_gara(gara_id, team_id);
CREATE INDEX idx_stint_mio_team_team ON stint_mio_team(gara_id, team_id);
