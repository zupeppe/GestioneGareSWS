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
            $ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $nome = trim($_POST['nome'] ?? '');
            $cognome = trim($_POST['cognome'] ?? '');
            $redirect_to = $_POST['redirect_to'] ?? '/piloti/index';

            if ($nome !== '' && $cognome !== '') {
                $pilotaModel = new PilotaMioTeam();
                $creato = $pilotaModel->crea([
                    'nome' => $nome,
                    'cognome' => $cognome
                ]);
                if ($ajax) {
                    header('Content-Type: application/json');
                    if (!$creato) {
                        http_response_code(500);
                        echo json_encode(['status' => 'error', 'message' => 'Creazione pilota fallita.']);
                        exit;
                    }
                    echo json_encode([
                        'status' => 'success',
                        'data' => [
                            'id' => $pilotaModel->ottieniUltimoIdInserito(),
                            'nome' => $nome,
                            'cognome' => $cognome
                        ]
                    ]);
                    exit;
                }
            } elseif ($ajax) {
                header('Content-Type: application/json');
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Nome e cognome obbligatori.']);
                exit;
            }
            
            // Pattern Post-Redirect-Get
            header('Location: ' . BASE_URL . $redirect_to);
            exit;
        }
        
        header('Location: ' . BASE_URL . '/piloti/index');
        exit;
    }

    /**
     * Mostra il form di modifica per un pilota specifico pre-popolando i campi.
     * 
     * @param int $id L'ID del pilota
     * @return void
     */
    public function modifica($id) {
        $pilotaModel = new PilotaMioTeam();
        $pilota = $pilotaModel->ottieniPerId($id);
        
        if ($pilota) {
            require_once BASE_PATH . '/app/Views/piloti/modifica.php';
        } else {
            header('Location: ' . BASE_URL . '/piloti/index');
            exit;
        }
    }

    /**
     * Salva le modifiche di un pilota esistente e reindirizza alla lista.
     * 
     * @param int $id L'ID del pilota
     * @return void
     */
    public function aggiorna($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $cognome = trim($_POST['cognome'] ?? '');

            if ($nome !== '' && $cognome !== '') {
                $pilotaModel = new PilotaMioTeam();
                $pilotaModel->aggiorna($id, [
                    'nome' => $nome,
                    'cognome' => $cognome
                ]);
            }
        }
        
        header('Location: ' . BASE_URL . '/piloti/index');
        exit;
    }

    /**
     * Elimina un pilota e reindirizza alla lista.
     * 
     * @param int $id L'ID del pilota da eliminare
     * @return void
     */
    public function elimina($id) {
        $pilotaModel = new PilotaMioTeam();
        $pilotaModel->elimina($id);
        
        header('Location: ' . BASE_URL . '/piloti/index');
        exit;
    }
}
