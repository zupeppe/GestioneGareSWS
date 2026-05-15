<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe PilotiGara
 * 
 * Modello per la gestione del roster dei piloti iscritti a una singola gara.
 */
class PilotiGara {
    /**
     * @var PDO Oggetto di connessione al database
     */
    private $db;

    /**
     * Costruttore della classe.
     * Inizializza la connessione al database tramite il Singleton.
     */
    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    /**
     * Recupera tutti i piloti del nostro team iscritti a una specifica gara.
     * Unisce i dati con la tabella piloti_mio_team.
     * 
     * @param int $gara_id L'ID della gara
     * @return array Array di record contenenti id di associazione, pilota_id, nome e cognome
     */
    public function ottieniPerGara($gara_id) {
        $sql = "
            SELECT pg.id, pg.gara_id, pg.pilota_id, pg.team_id, p.nome, p.cognome, t.nome_team 
            FROM piloti_gara pg
            JOIN piloti_mio_team p ON pg.pilota_id = p.id
            LEFT JOIN teams t ON pg.team_id = t.id
            WHERE pg.gara_id = :gara_id
            ORDER BY t.nome_team, p.cognome, p.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutti i piloti del nostro team che NON sono ancora iscritti a questa gara.
     * 
     * @param int $gara_id L'ID della gara
     * @return array Array dei piloti non ancora nel roster
     */
    public function ottieniNonIscritti($gara_id) {
        $sql = "
            SELECT p.* 
            FROM piloti_mio_team p
            WHERE p.id NOT IN (
                SELECT pg.pilota_id FROM piloti_gara pg WHERE pg.gara_id = :gara_id
            )
            ORDER BY p.cognome, p.nome ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }

    /**
     * Associa un pilota a una gara e a un team (inserisce nel roster).
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @param int $team_id L'ID del team
     * @return bool True se successo, False altrimenti
     */
    public function crea($gara_id, $pilota_id, $team_id = null) {
        // Verifica se la colonna team_id esiste
        try {
            $sql = "SELECT team_id FROM piloti_gara LIMIT 1";
            $this->db->query($sql);
            $hasTeamId = true;
        } catch (Exception $e) {
            $hasTeamId = false;
        }
        
        if ($hasTeamId) {
            $sql = "INSERT INTO piloti_gara (gara_id, pilota_id, team_id) VALUES (:gara_id, :pilota_id, :team_id)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':gara_id' => $gara_id,
                ':pilota_id' => $pilota_id,
                ':team_id' => $team_id
            ]);
        } else {
            // Fallback per database senza team_id
            $sql = "INSERT INTO piloti_gara (gara_id, pilota_id) VALUES (:gara_id, :pilota_id)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':gara_id' => $gara_id,
                ':pilota_id' => $pilota_id
            ]);
        }
    }

    /**
     * Verifica se un pilota è già iscritto a una gara (qualsiasi team).
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @return array|false Dati dell'iscrizione o false se non trovato
     */
    public function ottieniPilotaPerGara($gara_id, $pilota_id) {
        $sql = "
            SELECT pg.*, p.nome, p.cognome, t.nome_team 
            FROM piloti_gara pg
            JOIN piloti_mio_team p ON pg.pilota_id = p.id
            LEFT JOIN teams t ON pg.team_id = t.id
            WHERE pg.gara_id = :gara_id AND pg.pilota_id = :pilota_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':pilota_id' => $pilota_id]);
        return $stmt->fetch();
    }

    /**
     * Recupera i piloti di un team specifico per una gara.
     * Se team_id non esiste nel database, usa logica alternativa.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $team_id L'ID del team
     * @return array Array di piloti del team
     */
    public function ottieniPerGaraETeam($gara_id, $team_id) {
        // Verifica se la colonna team_id esiste
        try {
            $sql = "SELECT team_id FROM piloti_gara LIMIT 1";
            $this->db->query($sql);
            $hasTeamId = true;
        } catch (Exception $e) {
            $hasTeamId = false;
        }
        
        if ($hasTeamId) {
            // Database con team_id - filtra per team
            $sql = "
                SELECT pg.id, pg.gara_id, pg.pilota_id, pg.team_id, p.nome, p.cognome, t.nome_team
                FROM piloti_gara pg
                JOIN piloti_mio_team p ON pg.pilota_id = p.id
                LEFT JOIN teams t ON pg.team_id = t.id
                WHERE pg.gara_id = :gara_id AND pg.team_id = :team_id
                ORDER BY p.cognome, p.nome ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':gara_id' => $gara_id, ':team_id' => $team_id]);
            return $stmt->fetchAll();
        } else {
            // Database senza team_id - ritorna tutti i piloti (fallback)
            // In questo caso, il filtraggio verrà fatto a livello di logica applicativa
            return $this->ottieniPerGara($gara_id);
        }
    }

    /**
     * Aggiorna il team_id di un pilota nel roster.
     * 
     * @param int $id L'ID dell'associazione (tabella piloti_gara)
     * @param int $team_id L'ID del team da assegnare
     * @return bool True se successo, False altrimenti
     */
    public function aggiornaTeamId($id, $team_id) {
        $sql = "UPDATE piloti_gara SET team_id = :team_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':team_id' => $team_id, ':id' => $id]);
    }

    /**
     * Rimuove un pilota dal roster di una gara.
     * 
     * @param int $id L'ID dell'associazione (tabella piloti_gara)
     * @return bool True se successo, False altrimenti
     */
    public function elimina($id) {
        $sql = "DELETE FROM piloti_gara WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
