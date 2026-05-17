<?php
namespace App\Controllers;

require_once BASE_PATH . '/config/Database.php';

use Database;
use PDO;

class AdminDatiController {

    public function __construct() {
        // Controllo di sicurezza: solo admin
        if (!isset($_SESSION['utente']) || $_SESSION['utente']['ruolo'] !== 'admin') {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
    }

    public function index() {
        $db = Database::getIstanza()->getConnessione();

        // Recupera Gare
        $stmt = $db->query("SELECT * FROM gare ORDER BY data_evento DESC");
        $gare = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recupera Teams
        $stmt = $db->query("SELECT * FROM teams ORDER BY nome_team ASC");
        $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recupera Piloti
        $stmt = $db->query("SELECT * FROM piloti_mio_team ORDER BY cognome ASC, nome ASC");
        $piloti = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once dirname(__DIR__) . '/Views/admin/gestione_dati.php';
    }

    public function eliminaGara($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getIstanza()->getConnessione();
            $stmt = $db->prepare("DELETE FROM gare WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                $_SESSION['success'] = "Gara eliminata con successo.";
            } else {
                $_SESSION['error'] = "Errore durante l'eliminazione della gara.";
            }
        }
        header('Location: ' . BASE_URL . '/admindati');
        exit;
    }

    public function eliminaTeam($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getIstanza()->getConnessione();
            $stmt = $db->prepare("DELETE FROM teams WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                $_SESSION['success'] = "Team eliminato con successo.";
            } else {
                $_SESSION['error'] = "Errore durante l'eliminazione del team.";
            }
        }
        header('Location: ' . BASE_URL . '/admindati');
        exit;
    }

    public function eliminaPilota($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getIstanza()->getConnessione();
            $stmt = $db->prepare("DELETE FROM piloti_mio_team WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {
                $_SESSION['success'] = "Pilota eliminato con successo.";
            } else {
                $_SESSION['error'] = "Errore durante l'eliminazione del pilota.";
            }
        }
        header('Location: ' . BASE_URL . '/admindati');
        exit;
    }
}
