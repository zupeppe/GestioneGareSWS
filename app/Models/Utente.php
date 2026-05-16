<?php
namespace App\Models;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use Database;
use PDO;

class Utente {
    private $db;

    public function __construct() {
        $this->db = Database::getIstanza()->getConnessione();
    }

    public function ottieniPerUsername($username) {
        $sql = "SELECT * FROM utenti WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function ottieniTutti() {
        $sql = "SELECT id, username, ruolo, attivo FROM utenti ORDER BY username ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ottieniPerId($id) {
        $sql = "SELECT id, username, ruolo, attivo FROM utenti WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crea($dati) {
        $sql = "INSERT INTO utenti (username, password_hash, ruolo, attivo) 
                VALUES (:username, :password_hash, :ruolo, :attivo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $dati['username'],
            ':password_hash' => $dati['password_hash'],
            ':ruolo' => $dati['ruolo'],
            ':attivo' => $dati['attivo'] ?? 1
        ]);
    }

    public function aggiornaPassword($id, $nuovoHash) {
        $sql = "UPDATE utenti SET password_hash = :password_hash WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password_hash' => $nuovoHash,
            ':id' => $id
        ]);
    }
}
