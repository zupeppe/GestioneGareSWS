<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe PilotaMioTeam
 * 
 * Modello per la gestione dei piloti appartenenti al proprio team.
 */
class PilotaMioTeam {
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
     * Recupera tutti i piloti dal database.
     * I risultati sono ordinati alfabeticamente per cognome e nome.
     * 
     * @return array Array associativo di piloti
     */
    public function ottieniTutti() {
        $sql = "SELECT * FROM piloti_mio_team ORDER BY cognome, nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuovo pilota nel database.
     * Utilizza i prepared statements per evitare SQL injection.
     * 
     * @param array $dati Array associativo contenente 'nome' e 'cognome'
     * @return bool True se l'inserimento ha avuto successo, False altrimenti
     */
    public function crea($dati) {
        $sql = "INSERT INTO piloti_mio_team (nome, cognome) VALUES (:nome, :cognome)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $dati['nome'],
            ':cognome' => $dati['cognome']
        ]);
    }
}
