<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe Gara
 * 
 * Modello per la gestione dei dati relativi alle gare.
 */
class Gara {
    /**
     * @var PDO Oggetto di connessione al database
     */
    private $db;

    /**
     * Costruttore della classe.
     * Inizializza la connessione al database ottenendo l'istanza Singleton.
     */
    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    /**
     * Recupera tutte le gare dal database ordinate per data decrescente.
     * 
     * @return array Un array contenente tutte le gare
     */
    public function ottieniTutte() {
        $sql = "SELECT * FROM gare ORDER BY data_evento DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crea una nuova gara nel database.
     * Utilizza prepared statements per evitare SQL injection.
     * 
     * @param array $dati Array associativo contenente 'nome_gara' e 'data_evento'
     * @return bool True se successo, False altrimenti
     */
    public function crea($dati) {
        $sql = "INSERT INTO gare (nome_gara, data_evento, stato) VALUES (:nome_gara, :data_evento, 'setup')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome_gara' => $dati['nome_gara'],
            ':data_evento' => $dati['data_evento']
        ]);
    }

    /**
     * Recupera i dettagli di una gara tramite il suo ID.
     * 
     * @param int $id L'ID della gara
     * @return array|false Dati della gara, false se non trovata
     */
    public function ottieniPerId($id) {
        $sql = "SELECT * FROM gare WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
