<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe StintMioTeam
 * 
 * Modello per la gestione degli stint (turni di guida) dei piloti.
 */
class StintMioTeam {
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
     * Inizia un nuovo stint per un pilota.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @param int $minuto_ingresso Il minuto a ritroso in cui il pilota inizia a guidare
     * @return bool True se successo, False altrimenti
     */
    public function iniziaStint($gara_id, $pilota_id, $minuto_ingresso) {
        $sql = "INSERT INTO stint_mio_team (gara_id, pilota_id, minuto_ingresso) VALUES (:gara_id, :pilota_id, :minuto_ingresso)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id,
            ':minuto_ingresso' => $minuto_ingresso
        ]);
    }

    /**
     * Termina uno stint attivo registrando il minuto di uscita.
     * 
     * @param int $id L'ID dello stint da terminare
     * @param int $minuto_uscita Il minuto a ritroso in cui il pilota termina il turno
     * @return bool True se successo, False altrimenti
     */
    public function terminaStint($id, $minuto_uscita) {
        $sql = "UPDATE stint_mio_team SET minuto_uscita = :minuto_uscita WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':minuto_uscita' => $minuto_uscita
        ]);
    }

    /**
     * Recupera lo stint attualmente attivo (senza minuto_uscita) per una gara, con i dettagli del pilota.
     * 
     * @param int $gara_id L'ID della gara
     * @return array|false I dati dello stint e del pilota, o false se nessuno è in pista
     */
    public function ottieniStintAttivo($gara_id) {
        $sql = "
            SELECT s.*, p.nome, p.cognome 
            FROM stint_mio_team s
            JOIN piloti_mio_team p ON s.pilota_id = p.id
            WHERE s.gara_id = :gara_id AND s.minuto_uscita IS NULL
            ORDER BY s.id DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetch();
    }

    /**
     * Recupera tutti gli stint completati da un pilota in una data gara.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $pilota_id L'ID del pilota
     * @return array Array degli stint completati
     */
    public function ottieniStintCompletatiPilota($gara_id, $pilota_id) {
        $sql = "
            SELECT * 
            FROM stint_mio_team
            WHERE gara_id = :gara_id AND pilota_id = :pilota_id AND minuto_uscita IS NOT NULL
            ORDER BY id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':gara_id' => $gara_id,
            ':pilota_id' => $pilota_id
        ]);
        return $stmt->fetchAll();
    }
}
