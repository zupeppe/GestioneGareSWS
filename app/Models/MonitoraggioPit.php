<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe MonitoraggioPit
 * 
 * Modello per gestire i log delle operazioni dello Spotter in pit lane.
 */
class MonitoraggioPit {
    private $db;

    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    /**
     * Registra un log di cambio kart.
     * 
     * @param array $dati Array con gara_id, iscritto_gara_id, kart_lasciato_id, kart_preso_id, fila_colore
     * @return bool
     */
    public function registraCambio($dati) {
        // Pulisce la coda dei "redo" (cambi annullati) per questa gara prima di un nuovo inserimento
        $sqlPulisci = "DELETE FROM monitoraggio_pit WHERE gara_id = :gara_id AND stato = 'annullato'";
        $stmtPulisci = $this->db->prepare($sqlPulisci);
        $stmtPulisci->execute([':gara_id' => $dati['gara_id']]);

        $sql = "INSERT INTO monitoraggio_pit 
                (gara_id, iscritto_gara_id, kart_lasciato_id, kart_preso_id, fila_colore, timestamp, stato) 
                VALUES 
                (:gara_id, :iscritto_gara_id, :kart_lasciato_id, :kart_preso_id, :fila_colore, NOW(), 'attivo')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $dati['gara_id'],
            ':iscritto_gara_id' => $dati['iscritto_gara_id'],
            ':kart_lasciato_id' => $dati['kart_lasciato_id'],
            ':kart_preso_id' => $dati['kart_preso_id'],
            ':fila_colore' => $dati['fila_colore']
        ]);
    }

    /**
     * Recupera gli ultimi N cambi registrati per la gara, con dati chiari tramite JOIN.
     * 
     * @param int $gara_id ID della gara
     * @param int $limite Quanti record mostrare
     * @return array
     */
    public function ottieniUltimiCambi($gara_id, $limite = 10) {
        $sql = "SELECT 
                    m.id, 
                    m.timestamp, 
                    m.fila_colore,
                    ig.numero_gara,
                    t.nome_team,
                    kl.numero_kart AS kart_lasciato,
                    kp.numero_kart AS kart_preso
                FROM monitoraggio_pit m
                JOIN iscritti_gara ig ON m.iscritto_gara_id = ig.id
                JOIN teams t ON ig.team_id = t.id
                JOIN kart_gara kl ON m.kart_lasciato_id = kl.id
                JOIN kart_gara kp ON m.kart_preso_id = kp.id
                WHERE m.gara_id = :gara_id AND m.stato = 'attivo'
                ORDER BY m.timestamp DESC
                LIMIT :limite";
                
        // L'operatore LIMIT non supporta bene il binding diretto in execute con array associativo
        // In PDO bisogna usare bindValue con PDO::PARAM_INT se l'emulazione prepares è off, ma execute([..]) lo tratta sempre come stringa.
        // Eseguiremo il binding esplicito
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':gara_id', $gara_id, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Recupera l'ultimo cambio registrato (attivo) con dettagli team per l'alert.
     */
    public function ottieniUltimoCambio($gara_id) {
        $sql = "SELECT m.*, ig.numero_gara, t.nome_team 
                FROM monitoraggio_pit m
                JOIN iscritti_gara ig ON m.iscritto_gara_id = ig.id
                JOIN teams t ON ig.team_id = t.id
                WHERE m.gara_id = :gara_id AND m.stato = 'attivo' 
                ORDER BY m.id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetch();
    }

    /**
     * Segna un log di cambio come annullato (invece di eliminarlo).
     */
    public function annullaCambioDato($id) {
        $sql = "UPDATE monitoraggio_pit SET stato = 'annullato' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Recupera l'ultimo cambio annullato (il più vecchio tra gli annullati, per il redo cronologico).
     */
    public function ottieniUltimoAnnullato($gara_id) {
        $sql = "SELECT m.*, ig.numero_gara, t.nome_team 
                FROM monitoraggio_pit m
                JOIN iscritti_gara ig ON m.iscritto_gara_id = ig.id
                JOIN teams t ON ig.team_id = t.id
                WHERE m.gara_id = :gara_id AND m.stato = 'annullato' 
                ORDER BY m.id DESC LIMIT 1";
        // Nota: se annulliamo in sequenza id 5, 4, 3, l'ultimo annullato è il 3 (il più piccolo), 
        // ma aspetta: l'undo si fa dal più grande al più piccolo.
        // Se facciamo Undo su 5, 4, 3, e poi facciamo Redo, vogliamo prima rifare il 3? 
        // No! Vogliamo rifare il 3 perché è l'azione subito successiva al punto attuale nel tempo! 
        // Quindi ordiniamo ASC?
        // Wait, se l'ultimo attivo è il 2. Gli annullati sono 3, 4, 5. Il prossimo da rifare è il 3.
        // Quindi ORDER BY m.id ASC.
        $sql = "SELECT m.*, ig.numero_gara, t.nome_team 
                FROM monitoraggio_pit m
                JOIN iscritti_gara ig ON m.iscritto_gara_id = ig.id
                JOIN teams t ON ig.team_id = t.id
                WHERE m.gara_id = :gara_id AND m.stato = 'annullato' 
                ORDER BY m.id ASC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetch();
    }

    /**
     * Ripristina un cambio annullato facendolo tornare attivo.
     */
    public function ripristinaStatoCambio($id) {
        $sql = "UPDATE monitoraggio_pit SET stato = 'attivo' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
