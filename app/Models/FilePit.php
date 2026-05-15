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
        $sql = "INSERT INTO file_pit_gara (gara_id, nome_colore, colore_hex, ordine) VALUES (:gara_id, :nome_colore, :colore_hex, :ordine)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $dati['gara_id'],
            ':nome_colore' => $dati['nome_colore'],
            ':colore_hex' => $dati['colore_hex'] ?? '#343a40',
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

    /**
     * Recupera una singola fila pit per ID.
     *
     * @param int $id ID della fila
     * @return array|false Record oppure false se assente
     */
    public function ottieniPerId($id) {
        $sql = "SELECT * FROM file_pit_gara WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: false;
    }

    /**
     * Aggiorna nome colore e hex di una fila della gara.
     * Se il nome cambia, aggiorna anche i riferimenti in kart_gara e monitoraggio_pit.
     *
     * @param int $id ID della fila (file_pit_gara)
     * @param int $gara_id ID della gara attesa
     * @param string $nome_colore Nuovo nome fila (non vuoto)
     * @param string $colore_hex Colore esadecimale (es. #RRGGBB)
     * @return bool True se ok, false in caso di errore o fila non trovata
     */
    public function aggiornaPerGara($id, $gara_id, $nome_colore, $colore_hex) {
        $nome_colore = trim((string)$nome_colore);
        $colore_hex = trim((string)$colore_hex);
        if ($nome_colore === '') {
            return false;
        }
        if ($colore_hex === '') {
            $colore_hex = '#343a40';
        }

        $this->db->beginTransaction();
        try {
            $sqlSel = "SELECT nome_colore FROM file_pit_gara WHERE id = :id AND gara_id = :gara_id LIMIT 1";
            $stmtSel = $this->db->prepare($sqlSel);
            $stmtSel->execute([':id' => $id, ':gara_id' => $gara_id]);
            $record = $stmtSel->fetch();
            if (!$record) {
                $this->db->rollBack();
                return false;
            }
            $nome_vecchio = (string)$record['nome_colore'];

            $sqlUp = "UPDATE file_pit_gara SET nome_colore = :nome_colore, colore_hex = :colore_hex WHERE id = :id AND gara_id = :gara_id";
            $stmtUp = $this->db->prepare($sqlUp);
            $stmtUp->execute([
                ':nome_colore' => $nome_colore,
                ':colore_hex' => $colore_hex,
                ':id' => $id,
                ':gara_id' => $gara_id
            ]);

            if ($nome_vecchio !== $nome_colore) {
                $sqlKart = "UPDATE kart_gara SET ultima_fila = :nuovo WHERE gara_id = :gara_id AND ultima_fila = :vecchio";
                $stmtKart = $this->db->prepare($sqlKart);
                $stmtKart->execute([
                    ':nuovo' => $nome_colore,
                    ':gara_id' => $gara_id,
                    ':vecchio' => $nome_vecchio
                ]);

                $sqlMon = "UPDATE monitoraggio_pit SET fila_colore = :nuovo WHERE gara_id = :gara_id AND fila_colore = :vecchio";
                $stmtMon = $this->db->prepare($sqlMon);
                $stmtMon->execute([
                    ':nuovo' => $nome_colore,
                    ':gara_id' => $gara_id,
                    ':vecchio' => $nome_vecchio
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
