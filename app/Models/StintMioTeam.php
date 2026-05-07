<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;
use App\Core\TimeHelper;

/**
 * Classe StintMioTeam
 * 
 * Modello per la gestione degli stint dei piloti con logica a catena (timeline).
 */
class StintMioTeam {
    private $db;

    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    /**
     * Ricalcola a cascata tutti i minuti di ingresso per la gara.
     * 
     * @param int $gara_id L'ID della gara
     * @return void
     */
    public function ricalcolaTimeline($gara_id) {
        // Recupera tutti gli stint della gara ordinati per ID (cronologicamente)
        $sql = "SELECT id, durata_minuti FROM stint_mio_team WHERE gara_id = :gara_id ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        $stints = $stmt->fetchAll();

        $corrente_minuto_ingresso = 0;

        $updateSql = "UPDATE stint_mio_team SET minuto_ingresso = :minuto_ingresso WHERE id = :id";
        $updateStmt = $this->db->prepare($updateSql);

        foreach ($stints as $stint) {
            // Aggiorna il minuto di ingresso
            $updateStmt->execute([
                ':minuto_ingresso' => $corrente_minuto_ingresso,
                ':id' => $stint['id']
            ]);

            // Se lo stint ha una durata (è chiuso), avanza il contatore del tempo per il prossimo
            if ($stint['durata_minuti'] !== null) {
                $corrente_minuto_ingresso += $stint['durata_minuti'];
            }
        }
    }

    /**
     * Inizia un nuovo stint per un pilota. 
     * Calcola automaticamente il minuto di ingresso dall'ultimo stint.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @return bool
     */
    public function iniziaStint($gara_id, $pilota_id) {
        // Il minuto d'ingresso verrà comunque corretto da ricalcolaTimeline, ma lo inseriamo calcolandolo al volo
        // Trovo l'ultimo stint per prendere la sua uscita
        $sqlUltimo = "SELECT minuto_ingresso, durata_minuti FROM stint_mio_team WHERE gara_id = :gara_id ORDER BY id DESC LIMIT 1";
        $stmtUltimo = $this->db->prepare($sqlUltimo);
        $stmtUltimo->execute([':gara_id' => $gara_id]);
        $ultimo = $stmtUltimo->fetch();

        $minuto_ingresso = 0;
        if ($ultimo && $ultimo['durata_minuti'] !== null) {
            $minuto_ingresso = $ultimo['minuto_ingresso'] + $ultimo['durata_minuti'];
        }

        $sql = "INSERT INTO stint_mio_team (gara_id, pilota_id, minuto_ingresso, durata_minuti) VALUES (:gara_id, :pilota_id, :minuto_ingresso, NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id,
            ':minuto_ingresso' => $minuto_ingresso
        ]);
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

        $sql = "UPDATE stint_mio_team SET durata_minuti = :durata_minuti WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':durata_minuti' => $minuti
        ]);

        $this->ricalcolaTimeline($gara_id);
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

        $sql = "UPDATE stint_mio_team SET durata_minuti = :durata_minuti WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':durata_minuti' => $minuti
        ]);

        $this->ricalcolaTimeline($gara_id);
        return $result;
    }

    /**
     * Recupera lo stint attualmente attivo per una gara (durata_minuti IS NULL).
     * 
     * @param int $gara_id L'ID della gara
     * @return array|false
     */
    public function ottieniStintAttivo($gara_id) {
        $sql = "
            SELECT s.*, p.nome, p.cognome 
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id AND s.durata_minuti IS NULL
            ORDER BY s.id DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetch();
    }

    /**
     * Recupera tutti gli stint completati da un pilota.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @return array
     */
    public function ottieniStintCompletatiPilota($gara_id, $pilota_id) {
        $sql = "
            SELECT * 
            FROM stint_mio_team
            WHERE gara_id = :gara_id AND pilota_id = :pilota_id AND durata_minuti IS NOT NULL
            ORDER BY id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutti gli stint (chiusi e aperti) di una gara in ordine cronologico.
     * 
     * @param int $gara_id
     * @return array
     */
    public function ottieniTuttiStintGara($gara_id) {
        $sql = "
            SELECT s.*, p.nome, p.cognome 
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id
            ORDER BY s.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }
}
