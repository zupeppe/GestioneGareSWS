<?php
namespace App\Controllers;

use App\Models\Gara;

/**
 * Classe HomeController
 * 
 * Gestisce le richieste per la pagina iniziale dell'applicazione.
 */
class HomeController {
    /**
     * Mostra la pagina principale.
     * Recupera le gare dal database tramite il modello Gara e carica la vista.
     * 
     * @return void
     */
    public function index() {
        $garaModel = new Gara();
        
        // Recupera i dati o inizializza array vuoto
        try {
            $gare = $garaModel->ottieniTutte();
        } catch (\PDOException $e) {
            // Se il database non è ancora configurato, mostra array vuoto e non bloccare
            $gare = [];
        }
        
        require_once dirname(__DIR__) . '/Views/home.php';
    }
}
