<?php
namespace App\Controllers;

use App\Models\PilotaMioTeam;

/**
 * Classe PilotiController
 * 
 * Gestisce le operazioni CRUD e l'interfaccia per i piloti del proprio team.
 */
class PilotiController {
    /**
     * Mostra la lista di tutti i piloti.
     * 
     * @return void
     */
    public function index() {
        $pilotaModel = new PilotaMioTeam();
        $piloti = $pilotaModel->ottieniTutti();
        
        // Carica la vista dedicata
        require_once BASE_PATH . '/app/Views/piloti/index.php';
    }

    /**
     * Gestisce la sottomissione del form per salvare un nuovo pilota.
     * Valida i campi essenziali e reindirizza alla lista al termine.
     * 
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $cognome = trim($_POST['cognome'] ?? '');

            if ($nome !== '' && $cognome !== '') {
                $pilotaModel = new PilotaMioTeam();
                $pilotaModel->crea([
                    'nome' => $nome,
                    'cognome' => $cognome
                ]);
            }
        }
        
        // Pattern Post-Redirect-Get
        header('Location: ' . BASE_URL . '/piloti/index');
        exit;
    }
}
