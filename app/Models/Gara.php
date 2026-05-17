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
        $sql = "INSERT INTO gare (nome_gara, data_evento, durata_minuti, min_stint, tempo_minimo_pit, durata_max_stint, durata_min_stint, tempo_max_pilota, tempo_min_pilota, stato, mio_team_id) 
                VALUES (:nome_gara, :data_evento, :durata_minuti, :min_stint, :tempo_minimo_pit, :durata_max_stint, :durata_min_stint, :tempo_max_pilota, :tempo_min_pilota, 'setup', :mio_team_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome_gara' => $dati['nome_gara'],
            ':data_evento' => $dati['data_evento'],
            ':durata_minuti' => $dati['durata_minuti'] ?? 0,
            ':min_stint' => $dati['min_stint'] ?? 0,
            ':tempo_minimo_pit' => $dati['tempo_minimo_pit'] ?? 0,
            ':durata_max_stint' => $dati['durata_max_stint'] ?? 0,
            ':durata_min_stint' => (!empty($dati['durata_min_stint']) ? $dati['durata_min_stint'] : null),
            ':tempo_max_pilota' => $dati['tempo_max_pilota'] ?? 0,
            ':tempo_min_pilota' => $dati['tempo_min_pilota'] ?? 0,
            ':mio_team_id' => (!empty($dati['mio_team_id']) ? $dati['mio_team_id'] : null)
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

    /**
     * Aggiorna i dati principali di una gara.
     * 
     * @param int $id L'ID della gara
     * @param array $dati Array associativo con 'nome_gara', 'data_evento', 'durata_minuti'
     * @return bool True se successo, False altrimenti
     */
    public function aggiorna($id, $dati) {
        $sql = "UPDATE gare SET 
                nome_gara = :nome_gara, 
                data_evento = :data_evento, 
                durata_minuti = :durata_minuti,
                min_stint = :min_stint,
                tempo_minimo_pit = :tempo_minimo_pit,
                durata_max_stint = :durata_max_stint,
                durata_min_stint = :durata_min_stint,
                tempo_max_pilota = :tempo_max_pilota,
                tempo_min_pilota = :tempo_min_pilota,
                mio_team_id = :mio_team_id
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nome_gara' => $dati['nome_gara'],
            ':data_evento' => $dati['data_evento'],
            ':durata_minuti' => $dati['durata_minuti'] ?? 0,
            ':min_stint' => $dati['min_stint'] ?? 0,
            ':tempo_minimo_pit' => $dati['tempo_minimo_pit'] ?? 0,
            ':durata_max_stint' => $dati['durata_max_stint'] ?? 0,
            ':durata_min_stint' => (!empty($dati['durata_min_stint']) ? $dati['durata_min_stint'] : null),
            ':tempo_max_pilota' => $dati['tempo_max_pilota'] ?? 0,
            ':tempo_min_pilota' => $dati['tempo_min_pilota'] ?? 0,
            ':mio_team_id' => (!empty($dati['mio_team_id']) ? $dati['mio_team_id'] : null)
        ]);
    }

    /**
     * Aggiorna lo stato della gara.
     * 
     * @param int $id L'ID della gara
     * @param string $nuovo_stato Lo stato ('setup', 'in_corso', 'finita')
     * @return bool
     */
    public function aggiornaStato($id, $nuovo_stato) {
        $sql = "UPDATE gare SET stato = :stato WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':stato' => $nuovo_stato,
            ':id' => $id
        ]);
    }

}
