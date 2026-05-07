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
            SELECT pg.id, pg.gara_id, pg.pilota_id, p.nome, p.cognome 
            FROM piloti_gara pg
            JOIN piloti_mio_team p ON pg.pilota_id = p.id
            WHERE pg.gara_id = :gara_id
            ORDER BY p.cognome, p.nome ASC
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
     * Associa un pilota a una gara (inserisce nel roster).
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @return bool True se successo, False altrimenti
     */
    public function crea($gara_id, $pilota_id) {
        $sql = "INSERT INTO piloti_gara (gara_id, pilota_id) VALUES (:gara_id, :pilota_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id
        ]);
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
