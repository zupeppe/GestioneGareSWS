<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\PilotiGara;
use App\Models\StintMioTeam;
use App\Models\IscrittoGara;
use App\Models\KartGara;
use App\Models\FilePit;
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
        // Formula richiesta: (Stint_Rimanenti * (durata_max_stint - 1)) + (Pit_Rimanenti * tempo_minimo_pit)
        $stint_utile = max(1, $durata_max - 1);
        $stint_rimanenti = $pit_rimanenti_obbligatori + 1;
        $tempo_minimo_pit = (int)($gara['tempo_minimo_pit'] ?? 0);
        
        $tempo_massimo_copribile = ($stint_rimanenti * $stint_utile) + ($pit_rimanenti_obbligatori * $tempo_minimo_pit);
        
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
            'margine' => $margine,
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

        // Calcoliamo dove siamo arrivati col tempo
        $minutoUltimaUscita = 0;
        foreach ($tuttiStint as $stint) {
            if ($stint['durata_minuti'] !== null) {
                $uscita = $stint['minuto_ingresso'] + $stint['durata_minuti'];
                if ($uscita > $minutoUltimaUscita) {
                    $minutoUltimaUscita = $uscita;
                }
            }
        }
        
        $minutiResidui = $gara['durata_minuti'] - $minutoUltimaUscita;
        if ($minutiResidui < 0) {
            $minutiResidui = 0;
        }

        // Calcolo tempo residuo strategico
        $minuto_strategico_partenza = 0;
        if ($stintAttivo) {
            $minuto_strategico_partenza = $stintAttivo['minuto_ingresso'];
        } else {
            $minuto_strategico_partenza = $minutoUltimaUscita;
        }

        $minutiResiduiStrategici = $gara['durata_minuti'] - $minuto_strategico_partenza;
        if ($minutiResiduiStrategici < 0) {
            $minutiResiduiStrategici = 0;
        }

        require_once dirname(__DIR__) . '/Core/TimeHelper.php';
        $tempoResiduoHHMM = \App\Core\TimeHelper::daMinutiaHHMM($minutiResidui);

        $strategia = $this->calcolaStrategia($gara, $tuttiStint, $minutiResiduiStrategici);

        // -- LOGICA SPOTTER / KART AVVERSARI --
        $iscrittoModel = new IscrittoGara();
        $iscritti = $iscrittoModel->ottieniPerGara($gara_id);
        usort($iscritti, function($a, $b) {
            return (int)$a['numero_gara'] <=> (int)$b['numero_gara'];
        });

        $kartModel = new KartGara();
        
        $avversari_kart = [];
        $nostro_kart = null;

        foreach ($iscritti as $iscritto) {
            $kartAttuale = $kartModel->ottieniKartAttualeTeam($gara_id, $iscritto['id']);
            $datiTeam = [
                'iscritto' => $iscritto,
                'kart' => $kartAttuale
            ];
            $avversari_kart[] = $datiTeam;

            if ($gara['mio_team_id'] !== null && $iscritto['team_id'] == $gara['mio_team_id']) {
                $nostro_kart = $kartAttuale;
            }
        }

        $filePitModel = new FilePit();
        $file_pit = $filePitModel->ottieniPerGara($gara_id);
        
        $kart_in_fila = [];
        foreach ($file_pit as $fila) {
            $kartFila = $kartModel->ottieniKartInFila($gara_id, $fila['nome_colore']);
            $kart_in_fila[$fila['nome_colore']] = [
                'fila' => $fila,
                'kart' => $kartFila
            ];
        }

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

    /**
     * Annulla lo stint attivo eliminando l'inserimento appena effettuato.
     *
     * @param int $gara_id ID della gara
     * @param int $stint_id ID dello stint da annullare
     * @return void
     */
    public function annullaStintAttivo($gara_id, $stint_id) {
        $stint_id = (int)$stint_id;
        if ($stint_id <= 0) {
            $_SESSION['error'] = "Stint non valido.";
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            exit;
        }

        $stintModel = new StintMioTeam();
        $eliminato = $stintModel->eliminaStintAttivo($gara_id, $stint_id);

        if ($eliminato) {
            $_SESSION['success'] = "Inserimento stint annullato con successo.";
        } else {
            $_SESSION['error'] = "Impossibile annullare lo stint (potrebbe non essere piu attivo).";
        }

        header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
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

    public function aggiornaPrimoIngresso($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $ingresso = $_POST['minuto_ingresso_hhmm'] ?? null;

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

    /**
     * API Endpoint per ottenere in tempo reale lo stato dei kart di tutti i team.
     * Restituisce JSON.
     * 
     * @param int $gara_id
     */
    public function apiStatoKart($gara_id) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);
        if (!$gara) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Gara non trovata']);
            exit;
        }

        $iscrittoModel = new IscrittoGara();
        $iscritti = $iscrittoModel->ottieniPerGara($gara_id);
        usort($iscritti, function($a, $b) {
            return (int)$a['numero_gara'] <=> (int)$b['numero_gara'];
        });

        $kartModel = new KartGara();
        
        $avversari_kart = [];
        $nostro_kart = null;

        foreach ($iscritti as $iscritto) {
            $kartAttuale = $kartModel->ottieniKartAttualeTeam($gara_id, $iscritto['id']);
            $avversari_kart[] = [
                'iscritto' => $iscritto,
                'kart' => $kartAttuale
            ];

            if ($gara['mio_team_id'] !== null && $iscritto['team_id'] == $gara['mio_team_id']) {
                $nostro_kart = $kartAttuale;
            }
        }

        $filePitModel = new FilePit();
        $file_pit = $filePitModel->ottieniPerGara($gara_id);
        
        $kart_in_fila = [];
        foreach ($file_pit as $fila) {
            $kartFila = $kartModel->ottieniKartInFila($gara_id, $fila['nome_colore']);
            $kart_in_fila[$fila['nome_colore']] = [
                'fila' => $fila,
                'kart' => $kartFila
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'avversari_kart' => $avversari_kart,
            'nostro_kart' => $nostro_kart,
            'kart_in_fila' => $kart_in_fila
        ]);
        exit;
    }
}
