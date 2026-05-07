<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\PilotiGara;
use App\Models\StintMioTeam;
use App\Core\TimeHelper;

/**
 * Classe MurettoController
 * 
 * Gestisce la dashboard live per gli stint, utilizzando la Timeline a cascata.
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
        
        // Per ricalcolare o mostrare i dati ho bisogno di tutti gli stint
        $stintAttivo = $stintModel->ottieniStintAttivo($gara_id);
        $tuttiStint = $stintModel->ottieniTuttiStintGara($gara_id);

        $sosteEffettuate = 0;
        $minutoUltimaUscita = 0;

        foreach ($tuttiStint as $stint) {
            if ($stint['durata_minuti'] !== null) {
                $sosteEffettuate++;
                // L'ultimo minuto coperto dagli stint è l'ingresso + la durata
                $uscita = $stint['minuto_ingresso'] + $stint['durata_minuti'];
                if ($uscita > $minutoUltimaUscita) {
                    $minutoUltimaUscita = $uscita;
                }
            }
        }

        // Tempo residuo
        $minutiResidui = $gara['durata_minuti'] - $minutoUltimaUscita;
        if ($minutiResidui < 0) {
            $minutiResidui = 0;
        }
        require_once dirname(__DIR__) . '/Core/TimeHelper.php';
        $tempoResiduoHHMM = \App\Core\TimeHelper::daMinutiaHHMM($minutiResidui);

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

            if ($pilota_id) {
                $stintModel = new StintMioTeam();
                
                // Controllo se c'è già uno stint attivo
                if ($stintModel->ottieniStintAttivo($gara_id)) {
                    $_SESSION['error'] = "C'è già un pilota in pista! Termina il suo stint prima di farne salire un altro.";
                } else {
                    $stintModel->iniziaStint($gara_id, $pilota_id);
                    $_SESSION['success'] = "Stint iniziato. La timeline è scattata in avanti.";
                }
            } else {
                $_SESSION['error'] = "Seleziona un pilota per iniziare lo stint.";
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
            $durata = $_POST['durata'] ?? null;

            if ($stint_id && $durata !== null && $durata !== '') {
                $stintModel = new StintMioTeam();
                $stintModel->terminaStint($stint_id, $durata, $gara_id);
                $_SESSION['success'] = "Stint terminato e timeline ricalcolata.";
            } else {
                $_SESSION['error'] = "Durata (Tempo in Pista) mancante.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Modifica la durata di uno stint chiuso e propaga le modifiche.
     * 
     * @param int $gara_id L'ID della gara (per ricalcolo e redirect)
     * @return void
     */
    public function modificaDurata($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $durata = $_POST['durata'] ?? null;

            if ($stint_id && $durata !== null && $durata !== '') {
                $stintModel = new StintMioTeam();
                $stintModel->aggiornaDurata($stint_id, $durata, $gara_id);
                $_SESSION['success'] = "Durata stint aggiornata! Tutta la timeline successiva è slittata di conseguenza.";
            } else {
                $_SESSION['error'] = "Dati mancanti per l'aggiornamento.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }
}
