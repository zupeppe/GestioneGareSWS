<?php
namespace App\Controllers;

use App\Models\Team;

/**
 * Classe TeamsController
 * 
 * Gestisce le operazioni CRUD e l'interfaccia per l'anagrafica dei team.
 */
class TeamsController {
    /**
     * Mostra la lista di tutti i team.
     * 
     * @return void
     */
    public function index() {
        $teamModel = new Team();
        $teams = $teamModel->ottieniTutti();
        
        // Carica la vista dedicata
        require_once BASE_PATH . '/app/Views/teams/index.php';
    }

    /**
     * Gestisce la sottomissione del form per salvare un nuovo team.
     * Valida i campi essenziali e reindirizza alla lista al termine.
     * 
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome_team = trim($_POST['nome_team'] ?? '');

            if ($nome_team !== '') {
                $teamModel = new Team();
                $teamModel->crea([
                    'nome_team' => $nome_team
                ]);
            }
        }
        
        // Pattern Post-Redirect-Get
        header('Location: ' . BASE_URL . '/teams/index');
        exit;
    }

    /**
     * Mostra il form di modifica per un team specifico pre-popolando i campi.
     * 
     * @param int $id L'ID del team
     * @return void
     */
    public function modifica($id) {
        $teamModel = new Team();
        $team = $teamModel->ottieniPerId($id);
        
        if ($team) {
            require_once BASE_PATH . '/app/Views/teams/modifica.php';
        } else {
            header('Location: ' . BASE_URL . '/teams/index');
            exit;
        }
    }

    /**
     * Salva le modifiche di un team esistente e reindirizza alla lista.
     * 
     * @param int $id L'ID del team
     * @return void
     */
    public function aggiorna($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome_team = trim($_POST['nome_team'] ?? '');

            if ($nome_team !== '') {
                $teamModel = new Team();
                $teamModel->aggiorna($id, [
                    'nome_team' => $nome_team
                ]);
            }
        }
        
        header('Location: ' . BASE_URL . '/teams/index');
        exit;
    }

    /**
     * Elimina un team e reindirizza alla lista.
     * 
     * @param int $id L'ID del team da eliminare
     * @return void
     */
    public function elimina($id) {
        $teamModel = new Team();
        $teamModel->elimina($id);
        
        header('Location: ' . BASE_URL . '/teams/index');
        exit;
    }
}
