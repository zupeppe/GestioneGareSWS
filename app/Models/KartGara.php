<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe KartGara
 * 
 * Modello per gestire lo stato dei kart all'interno di una specifica gara.
 */
class KartGara {
    private $db;

    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    /**
     * Cerca se un kart esiste già per questa gara.
     * @param int $gara_id ID della gara
     * @param string $numero_kart Numero identificativo del kart
     * @return int L'ID del kart in gara
     */
    public function trovaOCrea($gara_id, $numero_kart) {
        $sql = "SELECT id FROM kart_gara WHERE gara_id = :gara_id AND numero_kart = :numero_kart LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':numero_kart' => $numero_kart]);
        $risultato = $stmt->fetch();
        if ($risultato) return (int)$risultato['id'];

        $sqlInsert = "INSERT INTO kart_gara (gara_id, numero_kart, rating) VALUES (:gara_id, :numero_kart, 0)";
        $stmtInsert = $this->db->prepare($sqlInsert);
        $stmtInsert->execute([':gara_id' => $gara_id, ':numero_kart' => $numero_kart]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Crea un kart iniziale e lo assegna a una fila (se specificata).
     */
    public function creaIniziale($gara_id, $numero_kart, $fila = null) {
        $sql = "INSERT INTO kart_gara (gara_id, numero_kart, rating, ultima_fila) VALUES (:gara_id, :numero_kart, 0, :fila)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':gara_id' => $gara_id,
            ':numero_kart' => $numero_kart,
            ':fila' => $fila
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Inizializza i kart fittizi per la gara se non esistono.
     */
    public function inizializzaGaraSeNecessario($gara_id, $iscritti, $file) {
        $sql = "SELECT COUNT(*) FROM kart_gara WHERE gara_id = :gara_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        if ($stmt->fetchColumn() == 0) {
            // Crea un kart per ogni team (numero_kart = iscritto_id)
            foreach ($iscritti as $iscritto) {
                $this->creaIniziale($gara_id, $iscritto['id'], null);
            }
            // Crea un kart per ogni fila (numero_kart = 9000 + id_fila)
            foreach ($file as $fila) {
                $this->creaIniziale($gara_id, 9000 + $fila['id'], $fila['nome_colore']);
            }
        }
    }

    /**
     * Recupera il kart parcheggiato in una determinata fila.
     */
    public function ottieniKartInFila($gara_id, $fila_nome) {
        $sql = "SELECT * FROM kart_gara WHERE gara_id = :gara_id AND ultima_fila = :fila LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':fila' => $fila_nome]);
        return $stmt->fetch();
    }

    /**
     * Trova il kart che un team sta attualmente guidando.
     */
    public function ottieniKartAttualeTeam($gara_id, $iscritto_gara_id) {
        // 1. Cerca l'ultimo cambio in monitoraggio_pit (solo quelli attivi)
        $sql = "SELECT kart_preso_id FROM monitoraggio_pit 
                WHERE gara_id = :gara_id AND iscritto_gara_id = :iscritto_id AND stato = 'attivo'
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':iscritto_id' => $iscritto_gara_id]);
        $risultato = $stmt->fetch();
        
        $kart_id = null;
        if ($risultato) {
            $kart_id = $risultato['kart_preso_id'];
        } else {
            // Se non ha mai fatto cambi, ha il suo kart iniziale (numero_kart = iscritto_gara_id)
            $sql2 = "SELECT id FROM kart_gara WHERE gara_id = :gara_id AND numero_kart = :numero_kart LIMIT 1";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([':gara_id' => $gara_id, ':numero_kart' => $iscritto_gara_id]);
            $res2 = $stmt2->fetch();
            if ($res2) {
                $kart_id = $res2['id'];
            }
        }
        
        if ($kart_id) {
            $sql3 = "SELECT * FROM kart_gara WHERE id = :id";
            $stmt3 = $this->db->prepare($sql3);
            $stmt3->execute([':id' => $kart_id]);
            return $stmt3->fetch();
        }
        
        return null;
    }

    /**
     * Scambia le posizioni di due kart (lasciato va in fila, preso va in pista).
     */
    public function scambiaPosizioni($kart_lasciato_id, $kart_preso_id, $fila_nome) {
        $this->db->beginTransaction();
        try {
            // Il kart lasciato parcheggia nella fila
            $sql1 = "UPDATE kart_gara SET ultima_fila = :fila WHERE id = :id";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([':fila' => $fila_nome, ':id' => $kart_lasciato_id]);
            
            // Il kart preso entra in pista (fila null)
            $sql2 = "UPDATE kart_gara SET ultima_fila = NULL WHERE id = :id";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([':id' => $kart_preso_id]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Aggiorna il rating del kart.
     */
    public function aggiornaRating($kart_id, $rating) {
        $sql = "UPDATE kart_gara SET rating = :rating WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':rating' => $rating, ':id' => $kart_id]);
    }

    /**
     * Resetta a 0 (Ignoto) tutti i rating dei kart di una gara.
     */
    public function resetRatingGara($gara_id) {
        $sql = "UPDATE kart_gara SET rating = 0 WHERE gara_id = :gara_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':gara_id' => $gara_id]);
    }

    /**
     * Annulla uno scambio fisico ripristinando le file precedenti.
     */
    public function annullaScambio($kart_lasciato_id, $kart_preso_id, $fila_nome) {
        $this->db->beginTransaction();
        try {
            // Il kart che era stato lasciato (e messo in fila) torna in pista (fila null)
            $sql1 = "UPDATE kart_gara SET ultima_fila = NULL WHERE id = :id";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([':id' => $kart_lasciato_id]);
            
            // Il kart che era stato preso (e messo in pista) torna in fila
            $sql2 = "UPDATE kart_gara SET ultima_fila = :fila WHERE id = :id";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([':fila' => $fila_nome, ':id' => $kart_preso_id]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
