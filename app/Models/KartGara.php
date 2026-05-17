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
    public function trovaOCrea($gara_id, $numero_kart, $rating = 0) {
        $sql = "SELECT id FROM kart_gara WHERE gara_id = :gara_id AND numero_kart = :numero_kart LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':numero_kart' => $numero_kart]);
        $risultato = $stmt->fetch();
        if ($risultato) return (int)$risultato['id'];

        $sqlInsert = "INSERT INTO kart_gara (gara_id, numero_kart, rating) VALUES (:gara_id, :numero_kart, :rating)";
        $stmtInsert = $this->db->prepare($sqlInsert);
        $stmtInsert->execute([':gara_id' => $gara_id, ':numero_kart' => $numero_kart, ':rating' => $rating]);
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
     * Ora controlla singolarmente ogni team e ogni fila!
     */
    public function inizializzaGaraSeNecessario($gara_id, $iscritti, $file) {
        
        // 1. Inizializza i kart fittizi iniziali per i team (solo se a questo specifico team manca)
        foreach ($iscritti as $iscritto) {
            
            // Usa il numero di gara del team se inserito, altrimenti usa l'ID interno come paracadute
            $numero_team = !empty($iscritto['numero_gara']) ? $iscritto['numero_gara'] : $iscritto['id'];
            
            $sql = "SELECT id FROM kart_gara WHERE gara_id = :gara_id AND numero_kart = :numero_kart LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':gara_id' => $gara_id, ':numero_kart' => $numero_team]);
            
            if (!$stmt->fetch()) {
                // Se questo team non ha il kart, lo crea!
                $this->creaIniziale($gara_id, $numero_team, null);
            }
        }

        // 2. Inizializza i kart per le file (solo per quelle che risultano vuote)
        foreach ($file as $fila) {
            $sql = "SELECT id FROM kart_gara WHERE gara_id = :gara_id AND ultima_fila = :fila LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':gara_id' => $gara_id, ':fila' => $fila['nome_colore']]);
            
            if (!$stmt->fetch()) {
                // Calcola il prossimo numero 900X disponibile per questa gara
                $sqlMax = "SELECT MAX(CAST(numero_kart AS UNSIGNED)) FROM kart_gara WHERE gara_id = :gara_id AND CAST(numero_kart AS UNSIGNED) >= 9000";
                $stmtMax = $this->db->prepare($sqlMax);
                $stmtMax->execute([':gara_id' => $gara_id]);
                $max = $stmtMax->fetchColumn();
                
                $nuovo_numero = $max ? $max + 1 : 9001;
                $this->creaIniziale($gara_id, $nuovo_numero, $fila['nome_colore']);
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
            // RECUPERA IL NUMERO DI GARA UFFICIALE DEL TEAM DAL DB
            $sqlNum = "SELECT numero_gara FROM iscritti_gara WHERE id = :id";
            $stmtNum = $this->db->prepare($sqlNum);
            $stmtNum->execute([':id' => $iscritto_gara_id]);
            $numero_gara = $stmtNum->fetchColumn();
            
            // Usa il numero_gara ufficiale, oppure l'ID se non era stato compilato nel setup
            $numero_ricerca = !empty($numero_gara) ? $numero_gara : $iscritto_gara_id;

            // Se non ha mai fatto cambi, ha il suo kart iniziale
            $sql2 = "SELECT id FROM kart_gara WHERE gara_id = :gara_id AND numero_kart = :numero_kart LIMIT 1";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([':gara_id' => $gara_id, ':numero_kart' => $numero_ricerca]);
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

    /**
     * Recupera un kart per ID.
     */
    public function ottieniPerId($id) {
        $sql = "SELECT * FROM kart_gara WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Imposta direttamente la fila per un kart (es. per inizializzazione).
     */
    public function impostaFila($kart_id, $fila_nome) {
        $sql = "UPDATE kart_gara SET ultima_fila = :fila WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':fila' => $fila_nome, ':id' => $kart_id]);
    }
}
