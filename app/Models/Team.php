<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe Team
 * 
 * Modello per la gestione dell'anagrafica dei team (sia propri che avversari).
 */
class Team {
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
     * Recupera tutti i team dal database.
     * I risultati sono ordinati alfabeticamente per nome.
     * 
     * @return array Array associativo di team
     */
    public function ottieniTutti() {
        $sql = "SELECT * FROM teams WHERE cancellato = 0 ORDER BY nome_team ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuovo team nel database.
     * Utilizza i prepared statements per evitare SQL injection.
     * 
     * @param array $dati Array associativo contenente 'nome_team'
     * @return bool True se l'inserimento ha avuto successo, False altrimenti
     */
    public function crea($dati) {
        $sql = "INSERT INTO teams (nome_team) VALUES (:nome_team)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome_team' => $dati['nome_team']
        ]);
    }

    /**
     * Restituisce l'ID dell'ultima riga inserita nella connessione corrente.
     *
     * @return int ID generato o 0 se non disponibile
     */
    public function ottieniUltimoIdInserito(): int {
        return (int)$this->db->lastInsertId();
    }

    /**
     * Recupera un team per ID.
     * 
     * @param int $id ID del team
     * @return array|false Dati del team o false se non trovato
     */
    public function ottieniPerId($id) {
        $sql = "SELECT * FROM teams WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Aggiorna i dati di un team esistente.
     * Utilizza prepared statements per sicurezza.
     * 
     * @param int $id ID del team da aggiornare
     * @param array $dati Array associativo contenente 'nome_team'
     * @return bool True in caso di successo, False altrimenti
     */
    public function aggiorna($id, $dati) {
        $sql = "UPDATE teams SET nome_team = :nome_team WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nome_team' => $dati['nome_team']
        ]);
    }

    /**
     * Elimina un team tramite il suo ID.
     * 
     * @param int $id ID del team da eliminare
     * @return bool True in caso di successo, False altrimenti
     */
    public function elimina($id) {
        $sql = "DELETE FROM teams WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Recupera tutti i team che NON sono ancora iscritti a una determinata gara.
     * 
     * @param int $gara_id L'ID della gara
     * @return array Array dei team non iscritti
     */
    public function ottieniNonIscritti($gara_id) {
        $sql = "SELECT * FROM teams 
                WHERE cancellato = 0 AND id NOT IN (SELECT team_id FROM iscritti_gara WHERE gara_id = :gara_id) 
                ORDER BY nome_team ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }
}
