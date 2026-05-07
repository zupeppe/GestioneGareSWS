<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\PilotiGara;
use App\Models\StintMioTeam;

/**
 * Classe MurettoController
 * 
 * Gestisce la dashboard live per gli stint e la strategia di gara.
 */
class MurettoController {
    /**
     * Mostra la dashboard del muretto per una gara specifica.
     * 
     * @param int $gara_id L'ID della gara
     * @return void
     */
    public function index($gara_id) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);

        if (!$gara) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }

        $pilotiGaraModel = new PilotiGara();
        $roster = $pilotiGaraModel->ottieniPerGara($gara_id);

        $stintModel = new StintMioTeam();
        $stintAttivo = $stintModel->ottieniStintAttivo($gara_id);

        // Calcola i minuti totali guidati per ogni pilota del roster
        foreach ($roster as &$pilota) {
            $pilota['minuti_guidati'] = 0;
            $stintCompletati = $stintModel->ottieniStintCompletatiPilota($gara_id, $pilota['pilota_id']);
            
            foreach ($stintCompletati as $stint) {
                // durata = ingresso - uscita (poiché è a ritroso, es. 600 - 540 = 60)
                $durata = $stint['minuto_ingresso'] - $stint['minuto_uscita'];
                $pilota['minuti_guidati'] += $durata;
            }
        }
        unset($pilota); // Rompi il riferimento per sicurezza

        require_once BASE_PATH . '/app/Views/muretto/index.php';
    }

    /**
     * Avvia un nuovo stint per un pilota.
     * 
     * @param int $gara_id L'ID della gara
     * @return void
     */
    public function inizia($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pilota_id = $_POST['pilota_id'] ?? null;
            $minuto_ingresso = $_POST['minuto_ingresso'] ?? null;

            if ($pilota_id && $minuto_ingresso !== null && $minuto_ingresso !== '') {
                $stintModel = new StintMioTeam();
                
                // Controllo se c'è già uno stint attivo
                if ($stintModel->ottieniStintAttivo($gara_id)) {
                    $_SESSION['error'] = "C'è già un pilota in pista! Termina il suo stint prima di farne salire un altro.";
                } else {
                    $stintModel->iniziaStint($gara_id, $pilota_id, (int)$minuto_ingresso);
                    $_SESSION['success'] = "Stint iniziato.";
                }
            } else {
                $_SESSION['error'] = "Dati mancanti per iniziare lo stint.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Termina lo stint attivo.
     * 
     * @param int $gara_id L'ID della gara
     * @return void
     */
    public function termina($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $minuto_uscita = $_POST['minuto_uscita'] ?? null;

            if ($stint_id && $minuto_uscita !== null && $minuto_uscita !== '') {
                $stintModel = new StintMioTeam();
                $stintModel->terminaStint($stint_id, (int)$minuto_uscita);
                $_SESSION['success'] = "Stint terminato con successo.";
            } else {
                $_SESSION['error'] = "Minuto di uscita mancante.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }
}
