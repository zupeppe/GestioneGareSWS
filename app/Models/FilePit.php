<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe FilePit
 * 
 * Modello per la gestione delle file dei box (corsie colorate) per una specifica gara.
 */
class FilePit {
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
     * Recupera tutte le file pit associate a una specifica gara, ordinate per 'ordine' e id.
     * 
     * @param int $gara_id L'ID della gara
     * @return array Array associativo delle file pit
     */
    public function ottieniPerGara($gara_id) {
        $sql = "SELECT * FROM file_pit_gara WHERE gara_id = :gara_id ORDER BY ordine ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }

    /**
     * Crea una nuova fila pit per una gara.
     * 
     * @param array $dati Array associativo contenente 'gara_id', 'nome_colore' e opzionalmente 'ordine'
     * @return bool True se l'inserimento ha avuto successo, False altrimenti
     */
    public function crea($dati) {
        $sql = "INSERT INTO file_pit_gara (gara_id, nome_colore, ordine) VALUES (:gara_id, :nome_colore, :ordine)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $dati['gara_id'],
            ':nome_colore' => $dati['nome_colore'],
            ':ordine' => $dati['ordine'] ?? 0
        ]);
    }

    /**
     * Elimina una fila pit tramite il suo ID.
     * 
     * @param int $id ID della fila pit da eliminare
     * @return bool True in caso di successo, False altrimenti
     */
    public function elimina($id) {
        $sql = "DELETE FROM file_pit_gara WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
