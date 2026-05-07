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
        $sql = "SELECT * FROM teams ORDER BY nome_team ASC";
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
}
