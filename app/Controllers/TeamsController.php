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
}
