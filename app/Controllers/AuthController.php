<?php
namespace App\Controllers;

use App\Models\Utente;

class AuthController {
    public function login() {
        $errore = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $errore = 'Inserisci username e password.';
            } else {
                $utenteModel = new Utente();
                $utente = $utenteModel->ottieniPerUsername($username);

                if ($utente && $utente['attivo'] && password_verify($password, $utente['password_hash'])) {
                    // Login corretto
                    $_SESSION['utente'] = [
                        'id' => $utente['id'],
                        'username' => $utente['username'],
                        'ruolo' => $utente['ruolo']
                    ];
                    
                    header('Location: ' . BASE_URL . '/home');
                    exit;
                } else {
                    $errore = 'Credenziali non valide o utente disattivo.';
                }
            }
        }

        require_once dirname(__DIR__) . '/Views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    }
}
