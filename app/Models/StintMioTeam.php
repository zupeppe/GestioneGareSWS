<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;
use App\Core\TimeHelper;
use App\Models\Gara;

/**
 * Classe StintMioTeam
 * 
 * Modello per la gestione degli stint dei piloti con logica a catena (timeline) e pit stop.
 */
class StintMioTeam {
    private $db;

    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    /**
     * Ricalcola a cascata tutti i minuti di ingresso per la gara e team specifici, considerando il tempo di pit stop.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $team_id L'ID del team (se null, calcola per tutti i team)
     * @return void
     */
    public function ricalcolaTimeline($gara_id, $team_id = null) {
        // Recupera i parametri della gara (es. tempo_minimo_pit)
        require_once __DIR__ . '/Gara.php';
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);
        $tempo_pit_secondi = isset($gara['tempo_minimo_pit']) ? (int)$gara['tempo_minimo_pit'] : 0;
        $tempo_pit = (int)round($tempo_pit_secondi / 60);

        // Recupera tutti gli stint della gara e team specifici ordinati per ID (cronologicamente), escludendo cancellati
        $sql = "SELECT id, minuto_ingresso, durata_minuti FROM stint_mio_team WHERE gara_id = :gara_id AND (cancellato = 0 OR cancellato IS NULL)";
        $params = [':gara_id' => $gara_id];
        
        if ($team_id !== null) {
            $sql .= " AND team_id = :team_id";
            $params[':team_id'] = $team_id;
        }
        
        $sql .= " ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stints = $stmt->fetchAll();

        if (empty($stints)) return;

        $updateSql = "UPDATE stint_mio_team SET minuto_ingresso = :minuto_ingresso WHERE id = :id";
        $updateStmt = $this->db->prepare($updateSql);

        $is_primo_stint = true;
        $corrente_minuto_ingresso = 0;

        foreach ($stints as $stint) {
            if ($is_primo_stint) {
                // Il primo stint mantiene il suo minuto_ingresso attuale (che può essere stato modificato manualmente)
                $corrente_minuto_ingresso = (int)$stint['minuto_ingresso'];
                $is_primo_stint = false;
            }

            // Aggiorna il minuto di ingresso (utile per gli stint successivi al primo)
            $updateStmt->execute([
                ':minuto_ingresso' => $corrente_minuto_ingresso,
                ':id' => $stint['id']
            ]);

            // Avanza il contatore del tempo per il prossimo stint
            if ($stint['durata_minuti'] !== null) {
                // Il prossimo stint inizierà all'uscita di questo + il tempo del pit
                $corrente_minuto_ingresso += $stint['durata_minuti'] + $tempo_pit;
            }
        }
    }

    /**
     * Inizia un nuovo stint per un pilota (imposta durata_minuti a NULL).
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @param int $team_id L'ID del team
     * @return bool
     */
    public function iniziaStint($gara_id, $pilota_id, $team_id) {
        require_once __DIR__ . '/Gara.php';
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);
        $tempo_pit_secondi = isset($gara['tempo_minimo_pit']) ? (int)$gara['tempo_minimo_pit'] : 0;
        $tempo_pit = (int)round($tempo_pit_secondi / 60);

        // Trovo l'ultimo stint per prendere la sua uscita (solo per questo team, escludendo cancellati)
        $sqlUltimo = "SELECT minuto_ingresso, durata_minuti FROM stint_mio_team WHERE gara_id = :gara_id AND team_id = :team_id AND (cancellato = 0 OR cancellato IS NULL) ORDER BY id DESC LIMIT 1";
        $stmtUltimo = $this->db->prepare($sqlUltimo);
        $stmtUltimo->execute([':gara_id' => $gara_id, ':team_id' => $team_id]);
        $ultimo = $stmtUltimo->fetch();

        $minuto_ingresso = 0;
        if ($ultimo && $ultimo['durata_minuti'] !== null) {
            $minuto_ingresso = $ultimo['minuto_ingresso'] + $ultimo['durata_minuti'] + $tempo_pit;
        } elseif ($ultimo && $ultimo['durata_minuti'] === null) {
            // C'è uno stint attivo, non dovrebbe succedere perché controllato dal controller
            return false;
        }

        $sql = "INSERT INTO stint_mio_team (gara_id, pilota_id, team_id, minuto_ingresso, durata_minuti) VALUES (:gara_id, :pilota_id, :team_id, :minuto_ingresso, NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id,
            ':team_id' => $team_id,
            ':minuto_ingresso' => $minuto_ingresso
        ]);
    }

    /**
     * Aggiorna il minuto di ingresso del PRIMO stint e ricalcola.
     * 
     * @param int $id L'ID del primo stint
     * @param string $nuovo_ingresso_hhmm L'ingresso in HH:MM
     * @param int $gara_id
     * @return bool
     */
    public function aggiornaIngresso($id, $nuovo_ingresso_hhmm, $gara_id) {
        require_once dirname(__DIR__) . '/Core/TimeHelper.php';
        $minuti = \App\Core\TimeHelper::daHHMMaMinuti($nuovo_ingresso_hhmm);

        // Recupera team_id dello stint
        $sqlTeam = "SELECT team_id FROM stint_mio_team WHERE id = :id";
        $stmtTeam = $this->db->prepare($sqlTeam);
        $stmtTeam->execute([':id' => $id]);
        $stint = $stmtTeam->fetch();
        $team_id = $stint ? $stint['team_id'] : null;

        $sql = "UPDATE stint_mio_team SET minuto_ingresso = :minuto_ingresso WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':minuto_ingresso' => $minuti
        ]);

        $this->ricalcolaTimeline($gara_id, $team_id);
        return $result;
    }

    /**
     * Termina uno stint attivo registrando la durata in HH:MM e ricalcolando la timeline.
     * 
     * @param int $id L'ID dello stint da terminare
     * @param string $durata_hhmm La durata in pista (HH:MM)
     * @param int $gara_id L'ID della gara per la timeline
     * @return bool
     */
    public function terminaStint($id, $durata_hhmm, $gara_id) {
        require_once dirname(__DIR__) . '/Core/TimeHelper.php';
        $minuti = \App\Core\TimeHelper::daHHMMaMinuti($durata_hhmm);

        // Recupera team_id dello stint
        $sqlTeam = "SELECT team_id FROM stint_mio_team WHERE id = :id";
        $stmtTeam = $this->db->prepare($sqlTeam);
        $stmtTeam->execute([':id' => $id]);
        $stint = $stmtTeam->fetch();
        $team_id = $stint ? $stint['team_id'] : null;

        $sql = "UPDATE stint_mio_team SET durata_minuti = :durata_minuti WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':durata_minuti' => $minuti
        ]);

        $this->ricalcolaTimeline($gara_id, $team_id);
        return $result;
    }

    /**
     * Modifica la durata di uno stint esistente e ricalcola la timeline.
     * 
     * @param int $id L'ID dello stint
     * @param string $nuova_durata_hhmm
     * @param int $gara_id L'ID della gara
     * @return bool
     */
    public function aggiornaDurata($id, $nuova_durata_hhmm, $gara_id) {
        require_once dirname(__DIR__) . '/Core/TimeHelper.php';
        $minuti = \App\Core\TimeHelper::daHHMMaMinuti($nuova_durata_hhmm);

        // Recupera team_id dello stint
        $sqlTeam = "SELECT team_id FROM stint_mio_team WHERE id = :id";
        $stmtTeam = $this->db->prepare($sqlTeam);
        $stmtTeam->execute([':id' => $id]);
        $stint = $stmtTeam->fetch();
        $team_id = $stint ? $stint['team_id'] : null;

        $sql = "UPDATE stint_mio_team SET durata_minuti = :durata_minuti WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':durata_minuti' => $minuti
        ]);

        $this->ricalcolaTimeline($gara_id, $team_id);
        return $result;
    }

    /**
     * Recupera lo stint attualmente attivo (senza durata) per una gara e team specifici.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $team_id L'ID del team (se null, cerca tra tutti i team)
     * @return array|false
     */
    public function ottieniStintAttivo($gara_id, $team_id = null) {
        $sql = "
            SELECT s.*, p.nome, p.cognome 
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id AND s.durata_minuti IS NULL 
            AND (s.cancellato = 0 OR s.cancellato IS NULL)
        ";
        $params = [':gara_id' => $gara_id];
        
        if ($team_id !== null) {
            $sql .= " AND s.team_id = :team_id";
            $params[':team_id'] = $team_id;
        }
        
        $sql .= " ORDER BY s.id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Controlla se ci sono stint attivi per qualsiasi team della gara.
     * 
     * @param int $gara_id L'ID della gara
     * @return bool True se ci sono stint attivi, false altrimenti
     */
    public function haStintAttivi($gara_id) {
        $sql = "SELECT COUNT(*) as count FROM stint_mio_team WHERE gara_id = :gara_id AND durata_minuti IS NULL AND (cancellato = 0 OR cancellato IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        $result = $stmt->fetch();
        return $result && $result['count'] > 0;
    }

    /**
     * Recupera tutti gli stint (chiusi e aperti) di una gara e team specifici in ordine cronologico.
     * 
     * @param int $gara_id
     * @param int $team_id L'ID del team (se null, recupera tutti i team)
     * @return array
     */
    public function ottieniTuttiStintGara($gara_id, $team_id = null) {
        $sql = "
            SELECT s.*, p.nome, p.cognome 
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id 
            AND (s.cancellato = 0 OR s.cancellato IS NULL)
        ";
        $params = [':gara_id' => $gara_id];
        
        if ($team_id !== null) {
            $sql .= " AND s.team_id = :team_id";
            $params[':team_id'] = $team_id;
        }
        
        $sql .= " ORDER BY s.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Calcola il tempo totale di guida per un pilota (solo stint completati).
     * 
     * @param int $gara_id ID della gara
     * @param int $pilota_id ID del pilota
     * @return int Tempo totale in minuti
     */
    public function calcolaTempoTotalePilota($gara_id, $pilota_id) {
        $sql = "
            SELECT COALESCE(SUM(durata_minuti), 0) as tempo_totale
            FROM stint_mio_team 
            WHERE gara_id = :gara_id AND pilota_id = :pilota_id AND durata_minuti IS NOT NULL 
            AND (cancellato = 0 OR cancellato IS NULL)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id
        ]);
        $result = $stmt->fetch();
        return (int)$result['tempo_totale'];
    }

    /**
     * Recupera lo stint attivo per un team specifico.
     * 
     * @param int $gara_id ID della gara
     * @param int $team_id ID del team
     * @return array|false
     */
    public function ottieniStintAttivoPerTeam($gara_id, $team_id) {
        // Nota: questo metodo richiede una modifica alla struttura per supportare il team_id
        // Per ora filtriamo dopo aver ottenuto tutti gli stint attivi
        $sql = "
            SELECT s.*, p.nome, p.cognome, i.team_id
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            JOIN piloti_gara pg ON s.pilota_id = pg.pilota_id AND s.gara_id = pg.gara_id
            JOIN iscritti_gara i ON pg.gara_id = i.gara_id AND i.team_id = :team_id
            WHERE s.gara_id = :gara_id AND s.durata_minuti IS NULL
            ORDER BY s.id DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':team_id' => $team_id]);
        return $stmt->fetch();
    }

    /**
     * Recupera tutti gli stint di una gara per un team specifico (esclusi cancellati).
     * 
     * @param int $gara_id ID della gara
     * @param int $team_id ID del team
     * @return array
     */
    public function ottieniTuttiStintGaraPerTeam($gara_id, $team_id) {
        // Filtra gli stint per team specifico, escludendo cancellati
        $sql = "
            SELECT s.*, p.nome, p.cognome
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id 
            AND (s.cancellato = 0 OR s.cancellato IS NULL)
            AND EXISTS (
                SELECT 1 FROM piloti_gara pg 
                JOIN iscritti_gara i ON pg.gara_id = i.gara_id 
                WHERE pg.gara_id = s.gara_id AND pg.pilota_id = s.pilota_id AND i.team_id = :team_id
            )
            ORDER BY s.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':team_id' => $team_id]);
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutti gli stint cancellati di una gara per un team specifico.
     * 
     * @param int $gara_id ID della gara
     * @param int $team_id ID del team
     * @return array
     */
    public function ottieniStintCancellatiPerTeam($gara_id, $team_id) {
        // Filtra gli stint cancellati per team specifico
        $sql = "
            SELECT s.*, p.nome, p.cognome
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id 
            AND s.cancellato = 1
            AND s.team_id = :team_id
            ORDER BY s.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':team_id' => $team_id]);
        return $stmt->fetchAll();
    }

    /**
     * Recupera uno stint specifico per ID.
     *
     * @param int $stint_id ID dello stint
     * @return array|false
     */
    public function ottieniPerId($stint_id) {
        $sql = "
            SELECT s.*, p.nome, p.cognome
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.id = :stint_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':stint_id' => $stint_id]);
        return $stmt->fetch();
    }

    /**
     * Elimina in modo sicuro uno stint attivo specifico.
     *
     * @param int $gara_id ID della gara
     * @param int $stint_id ID dello stint da eliminare
     * @return bool
     */
    public function eliminaStintAttivo($gara_id, $stint_id) {
        $sql = "DELETE FROM stint_mio_team WHERE id = :stint_id AND gara_id = :gara_id AND durata_minuti IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':stint_id' => $stint_id,
            ':gara_id' => $gara_id
        ]);
    }
}
