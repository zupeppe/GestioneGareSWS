<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

/**
 * Classe IscrittoGara
 * 
 * Modello per gestire l'iscrizione dei team ad una specifica gara.
 */
class IscrittoGara {
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
     * Inserisce una nuova iscrizione nel database (associa un team a una gara).
     * 
     * @param array $dati Array associativo con 'gara_id', 'team_id', 'numero_gara'
     * @return bool True se l'inserimento ha successo, False altrimenti
     */
    public function crea($dati) {
        $sql = "INSERT INTO iscritti_gara (gara_id, team_id, numero_gara) VALUES (:gara_id, :team_id, :numero_gara)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':gara_id' => $dati['gara_id'],
            ':team_id' => $dati['team_id'],
            ':numero_gara' => $dati['numero_gara']
        ]);
    }

    /**
     * Recupera l'elenco degli iscritti per una data gara.
     * Effettua una JOIN con la tabella teams per ottenere il nome del team.
     * 
     * @param int $gara_id L'ID della gara
     * @return array Array degli iscritti (id iscrizione, numero_gara, nome_team)
     */
    public function ottieniPerGara($gara_id) {
        $sql = "SELECT i.id, i.team_id, i.numero_gara, i.is_gestito, t.nome_team 
                FROM iscritti_gara i
                JOIN teams t ON i.team_id = t.id
                WHERE i.gara_id = :gara_id
                ORDER BY i.numero_gara ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }

    /**
     * Rimuove un'iscrizione dal database.
     * 
     * @param int $id L'ID dell'iscrizione (in iscritti_gara)
     * @return bool True se successo, False altrimenti
     */
    public function elimina($id) {
        $sql = "DELETE FROM iscritti_gara WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica se un team è già iscritto o se un numero di gara è già in uso.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $team_id L'ID del team
     * @param int $numero_gara Il numero di gara desiderato
     * @param int|null $escludi_id ID iscrizione da escludere (utile in modifica)
     * @return array|false Un array con l'errore ('team_esistente' o 'numero_esistente') o false se libero
     */
    public function esisteGia($gara_id, $team_id, $numero_gara, $escludi_id = null) {
        $sql = "SELECT team_id, numero_gara FROM iscritti_gara WHERE gara_id = :gara_id";
        $params = [':gara_id' => $gara_id];
        
        if ($escludi_id !== null) {
            $sql .= " AND id != :escludi_id";
            $params[':escludi_id'] = $escludi_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $iscritti = $stmt->fetchAll();
        
        foreach ($iscritti as $iscritto) {
            if ($iscritto['team_id'] == $team_id) {
                return 'team_esistente';
            }
            if ($iscritto['numero_gara'] == $numero_gara) {
                return 'numero_esistente';
            }
        }
        return false;
    }

    /**
     * Recupera una singola iscrizione per ID.
     * 
     * @param int $id L'ID dell'iscrizione
     * @return array|false Dati dell'iscrizione
     */
    public function ottieniPerId($id) {
        $sql = "SELECT * FROM iscritti_gara WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Aggiorna il numero di gara di un'iscrizione esistente.
     * 
     * @param int $id L'ID dell'iscrizione
     * @param int $nuovo_numero Il nuovo numero di gara
     * @return bool
     */
    public function aggiorna($id, $nuovo_numero) {
        $sql = "UPDATE iscritti_gara SET numero_gara = :numero_gara WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':numero_gara' => $nuovo_numero,
            ':id' => $id
        ]);
    }

    /**
     * Aggiorna lo stato di gestione di un team.
     * 
     * @param int $id ID dell'iscrizione
     * @param int $is_gestito 0 o 1
     * @return bool
     */
    public function aggiornaGestito($id, $is_gestito) {
        $sql = "UPDATE iscritti_gara SET is_gestito = :is_gestito WHERE id = :id";
        error_log("DEBUG: SQL: $sql, params: is_gestito=$is_gestito, id=$id");
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([':is_gestito' => $is_gestito, ':id' => $id]);
        
        error_log("DEBUG: SQL execute result: " . ($result ? 'true' : 'false'));
        error_log("DEBUG: PDO error info: " . print_r($stmt->errorInfo(), true));
        
        return $result;
    }

    /**
     * Conta quanti team sono gestiti per una gara.
     * 
     * @param int $gara_id L'ID della gara
     * @return int Numero di team gestiti
     */
    public function contaGestiti($gara_id) {
        $sql = "SELECT COUNT(*) FROM iscritti_gara WHERE gara_id = :gara_id AND is_gestito = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Recupera un'iscrizione specifica per team e gara.
     * 
     * @param int $gara_id L'ID della gara
     * @param int $team_id L'ID del team
     * @return array|false Dati dell'iscrizione
     */
    public function ottieniPerTeamEGara($gara_id, $team_id) {
        $sql = "SELECT i.*, t.nome_team 
                FROM iscritti_gara i
                JOIN teams t ON i.team_id = t.id
                WHERE i.gara_id = :gara_id AND i.team_id = :team_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id, ':team_id' => $team_id]);
        return $stmt->fetch();
    }

    /**
     * Recupera tutti i team gestiti per una gara.
     * 
     * @param int $gara_id L'ID della gara
     * @return array Array dei team gestiti
     */
    public function ottieniGestitiPerGara($gara_id) {
        $sql = "SELECT i.*, t.nome_team 
                FROM iscritti_gara i
                JOIN teams t ON i.team_id = t.id
                WHERE i.gara_id = :gara_id AND i.is_gestito = 1
                ORDER BY i.numero_gara ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        return $stmt->fetchAll();
    }
}
