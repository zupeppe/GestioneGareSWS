<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\PilotiGara;
use App\Models\StintMioTeam;
use App\Core\TimeHelper;

/**
 * Classe MurettoController
 * 
 * Gestisce la dashboard live per gli stint, la strategia e il pit stop.
 */
class MurettoController {
    
    /**
     * Valida il formato HH:MM
     * @param string $tempo
     * @return bool
     */
    private function validaFormatoTempo($tempo) {
        return preg_match('/^[0-9]{2}:[0-9]{2}$/', $tempo) === 1;
    }

    /**
     * Calcola i parametri strategici della gara.
     * 
     * @param array $gara I dati della gara (inclusi i parametri regolamentari)
     * @param array $tuttiStint Tutti gli stint (aperti e chiusi)
     * @param int $minutiResidui Il tempo mancante alla fine
     * @return array Array con i dati strategici calcolati
     */
    private function calcolaStrategia($gara, $tuttiStint, $minutiResidui) {
        $stint_chiusi = 0;
        foreach ($tuttiStint as $s) {
            if ($s['durata_minuti'] !== null) {
                $stint_chiusi++;
            }
        }

        $min_stint = (int)($gara['min_stint'] ?? 0);
        $durata_max = (int)($gara['durata_max_stint'] ?? 0);

        $pit_fatti = $stint_chiusi;
        $pit_rimanenti_obbligatori = max(0, $min_stint - $pit_fatti);

        // Calcolo Jolly
        // Formula richiesta: durata_max_stint * (pit_rimanenti_obbligatori + 1)
        $stint_utile = max(1, $durata_max);
        $stint_rimanenti = $pit_rimanenti_obbligatori + 1;
        $tempo_massimo_copribile = $stint_rimanenti * $stint_utile;
        
        $margine = $tempo_massimo_copribile - $minutiResidui;
        $jolly_disponibili = 0;
        $pit_extra_necessari = 0;

        if ($margine >= 0) {
            $jolly_disponibili = floor($margine / $stint_utile);
            $stato_strategia = "OK";
            $colore_strategia = "#28a745"; // Verde
        } else {
            $pit_extra_necessari = ceil(abs($margine) / $stint_utile);
            $stato_strategia = "IN AFFANNO";
            $colore_strategia = "#dc3545"; // Rosso
        }

        return [
            'pit_fatti' => $pit_fatti,
            'pit_minimi' => $min_stint,
            'pit_rimanenti_obbligatori' => $pit_rimanenti_obbligatori,
            'tempo_massimo_copribile' => $tempo_massimo_copribile,
            'stato_strategia' => $stato_strategia,
            'colore_strategia' => $colore_strategia,
            'jolly_disponibili' => $jolly_disponibili,
            'pit_extra_necessari' => $pit_extra_necessari
        ];
    }

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
        $tuttiStint = $stintModel->ottieniTuttiStintGara($gara_id);

        $minutoUltimaUscita = 0;
        foreach ($tuttiStint as $stint) {
            if ($stint['durata_minuti'] !== null) {
                // Calcoliamo dove siamo arrivati col tempo (ultimo ingresso + ultima durata + pit successivo ipotetico non lo conto qui)
                $uscita = $stint['minuto_ingresso'] + $stint['durata_minuti'];
                if ($uscita > $minutoUltimaUscita) {
                    $minutoUltimaUscita = $uscita;
                }
            }
        }
        
        // Se c'è uno stint attivo, il tempo di gara continua a scorrere ma non sappiamo quando uscirà
        // Ai fini strategici, il "residuo" si calcola solitamente rispetto al momento attuale.
        // Se non specificato, lo calcolo semplicemente rispetto all'ultima uscita confermata:
        $minutiResidui = $gara['durata_minuti'] - $minutoUltimaUscita;
        if ($minutiResidui < 0) {
            $minutiResidui = 0;
        }

        require_once dirname(__DIR__) . '/Core/TimeHelper.php';
        $tempoResiduoHHMM = \App\Core\TimeHelper::daMinutiaHHMM($minutiResidui);

        $strategia = $this->calcolaStrategia($gara, $tuttiStint, $minutiResidui);

        require_once BASE_PATH . '/app/Views/muretto/index.php';
    }

    public function inizia($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pilota_id = $_POST['pilota_id'] ?? null;

            if ($pilota_id) {
                $stintModel = new StintMioTeam();
                
                if ($stintModel->ottieniStintAttivo($gara_id)) {
                    $_SESSION['error'] = "C'è già un pilota in pista! Termina il suo stint prima di farne salire un altro.";
                } else {
                    $stintModel->iniziaStint($gara_id, $pilota_id);
                    $_SESSION['success'] = "Stint iniziato.";
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

    public function termina($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $durata = $_POST['durata'] ?? null;

            if ($stint_id && $durata) {
                if (!$this->validaFormatoTempo($durata)) {
                    $_SESSION['error'] = "Formato tempo non valido. Usa HH:MM (es. 01:15).";
                } else {
                    $stintModel = new StintMioTeam();
                    $stintModel->terminaStint($stint_id, $durata, $gara_id);
                    $_SESSION['success'] = "Stint terminato.";
                }
            } else {
                $_SESSION['error'] = "Durata mancante.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    public function modificaDurata($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $durata = $_POST['durata'] ?? null;

            if ($stint_id && $durata) {
                if (!$this->validaFormatoTempo($durata)) {
                    $_SESSION['error'] = "Formato tempo non valido. Usa HH:MM.";
                } else {
                    $stintModel = new StintMioTeam();
                    $stintModel->aggiornaDurata($stint_id, $durata, $gara_id);
                    $_SESSION['success'] = "Durata aggiornata. Timeline ricalcolata a cascata.";
                }
            } else {
                $_SESSION['error'] = "Dati mancanti.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    public function modificaIngressoPrimoStint($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $ingresso = $_POST['ingresso'] ?? null;

            if ($stint_id && $ingresso !== null && $ingresso !== '') {
                if (!$this->validaFormatoTempo($ingresso)) {
                    $_SESSION['error'] = "Formato tempo non valido per l'ingresso. Usa HH:MM.";
                } else {
                    $stintModel = new StintMioTeam();
                    $stintModel->aggiornaIngresso($stint_id, $ingresso, $gara_id);
                    $_SESSION['success'] = "Ingresso modificato. L'intera timeline è stata riadattata.";
                }
            } else {
                $_SESSION['error'] = "Ingresso mancante.";
            }
            
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }
}
