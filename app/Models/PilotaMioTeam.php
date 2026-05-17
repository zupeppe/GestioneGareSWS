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
        $sql = "SELECT * FROM piloti_mio_team WHERE cancellato = 0 ORDER BY cognome, nome ASC";
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

    /**
     * Restituisce l'ID dell'ultima riga inserita nella connessione corrente.
     *
     * @return int ID generato o 0 se non disponibile
     */
    public function ottieniUltimoIdInserito(): int {
        return (int)$this->db->lastInsertId();
    }

    /**
     * Recupera un pilota per ID.
     * 
     * @param int $id ID del pilota
     * @return array|false Dati del pilota o false se non trovato
     */
    public function ottieniPerId($id) {
        $sql = "SELECT * FROM piloti_mio_team WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Aggiorna i dati di un pilota esistente.
     * Utilizza prepared statements per sicurezza.
     * 
     * @param int $id ID del pilota da aggiornare
     * @param array $dati Array associativo contenente 'nome' e 'cognome'
     * @return bool True in caso di successo, False altrimenti
     */
    public function aggiorna($id, $dati) {
        $sql = "UPDATE piloti_mio_team SET nome = :nome, cognome = :cognome WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nome' => $dati['nome'],
            ':cognome' => $dati['cognome']
        ]);
    }

    /**
     * Elimina un pilota tramite il suo ID.
     * 
     * @param int $id ID del pilota da eliminare
     * @return bool True in caso di successo, False altrimenti
     */
    public function elimina($id) {
        $sql = "DELETE FROM piloti_mio_team WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
