<?php
namespace App\Controllers;

use App\Models\Utente;

class UserController {
    public function index() {
        $utenteModel = new Utente();
        $utenti = $utenteModel->ottieniTutti();
        
        require_once dirname(__DIR__) . '/Views/users/index.php';
    }

    public function create() {
        $errore = '';
        $successo = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $ruolo = $_POST['ruolo'] ?? 'spotter';

            if (empty($username) || empty($password)) {
                $errore = 'Compila tutti i campi obbligatori.';
            } else {
                $utenteModel = new Utente();
                $esiste = $utenteModel->ottieniPerUsername($username);

                if ($esiste) {
                    $errore = 'Username già in uso.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $dati = [
                        'username' => $username,
                        'password_hash' => $hash,
                        'ruolo' => $ruolo,
                        'attivo' => 1
                    ];
                    
                    if ($utenteModel->crea($dati)) {
                        $successo = 'Utente creato con successo.';
                    } else {
                        $errore = 'Errore durante la creazione dell\'utente.';
                    }
                }
            }
        }

        // Recupera di nuovo la lista per mostrare la form insieme all'elenco
        $utenteModel = new Utente();
        $utenti = $utenteModel->ottieniTutti();
        
        require_once dirname(__DIR__) . '/Views/users/index.php';
    }

    public function resetPassword() {
        $errore = '';
        $successo = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['user_id'] ?? 0;
            $newPassword = $_POST['new_password'] ?? '';

            if (empty($id) || empty($newPassword)) {
                $errore = 'Compila tutti i campi.';
            } else {
                $utenteModel = new Utente();
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                
                if ($utenteModel->aggiornaPassword($id, $hash)) {
                    $successo = 'Password aggiornata con successo.';
                } else {
                    $errore = 'Errore durante l\'aggiornamento della password.';
                }
            }
        }

        $utenteModel = new Utente();
        $utenti = $utenteModel->ottieniTutti();
        
        require_once dirname(__DIR__) . '/Views/users/index.php';
    }
}
