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
}
