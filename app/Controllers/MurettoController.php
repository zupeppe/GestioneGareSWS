<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\PilotiGara;
use App\Models\StintMioTeam;
use App\Models\IscrittoGara;
use App\Models\KartGara;
use App\Models\FilePit;
use App\Core\TimeHelper;
use Database;

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
     * Calcola il tempo totale di guida per ogni pilota.
     * 
     * @param int $gara_id ID della gara
     * @param array $roster Piloti del team
     * @param array $stintAttivo Stint attuale (se presente)
     * @return array Array associativo con pilota_id => tempo_totale_minuti
     */
    private function calcolaTempiTotaliPiloti($gara_id, $roster, $stintAttivo) {
        $stintModel = new StintMioTeam();
        $tempiTotali = [];
        
        // Inizializza tutti i piloti a 0
        foreach ($roster as $pilota) {
            $tempiTotali[$pilota['pilota_id']] = 0;
        }
        
        // Calcola il tempo totale per ogni pilota dagli stint completati
        foreach ($roster as $pilota) {
            $tempoTotale = $stintModel->calcolaTempoTotalePilota($gara_id, $pilota['pilota_id']);
            $tempiTotali[$pilota['pilota_id']] = $tempoTotale;
        }
        
        // Se c'è uno stint attivo, aggiungi il tempo già trascorso
        if ($stintAttivo) {
            $pilotaAttivoId = $stintAttivo['pilota_id'];
            $minutoIngresso = $stintAttivo['minuto_ingresso'];
            
            // Calcola i minuti trascorsi dall'ingresso fino ad ora
            $tempoTrascorso = $this->calcolaMinutiTrascorsiStintAttivo($gara_id, $minutoIngresso);
            
            if (isset($tempiTotali[$pilotaAttivoId])) {
                $tempiTotali[$pilotaAttivoId] += $tempoTrascorso;
            }
        }
        
        return $tempiTotali;
    }
    
    /**
     * Calcola i minuti trascorsi per lo stint attivo usando la logica strategica.
     * 
     * @param int $gara_id ID della gara
     * @param int $minutoIngresso Minuto di ingresso dello stint
     * @return int Minuti trascorsi
     */
    private function calcolaMinutiTrascorsiStintAttivo($gara_id, $minutoIngresso) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);
        
        // Usa la logica del tempo strategico già esistente
        $minutiResiduiStrategici = $gara['durata_minuti'] - $minutoIngresso;
        if ($minutiResiduiStrategici < 0) {
            $minutiResiduiStrategici = 0;
        }
        
        // Il tempo trascorso è la differenza tra durata totale e residuo strategico
        $tempoTrascorso = $gara['durata_minuti'] - $minutiResiduiStrategici - $minutoIngresso;
        
        return max(0, $tempoTrascorso);
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
        // 1. Dati base e sicurezza
        $durata_max = (int)($gara['durata_max_stint'] ?? 0);
        $stint_utile = max(1, $durata_max - 1); 
        $stint_fatti = count($tuttiStint);
        $pit_fatti = max(0, $stint_fatti - 1);
        $pit_obbligatori = (int)($gara['min_stint'] ?? 0);
        $pit_rimanenti_obbligatori = max(0, $pit_obbligatori - $pit_fatti);
        $stint_rimanenti = $pit_rimanenti_obbligatori + 1;

        // 2. Calcolo tempo Pit in minuti (es. 140s = 2.333 min)
        $tempo_minimo_pit_sec = (int)($gara['tempo_minimo_pit'] ?? 0);
        $pit_time_min = $tempo_minimo_pit_sec / 60;
        $totale_tempo_pit_rimanente = $pit_rimanenti_obbligatori * $pit_time_min;

        // 3. Calcolo Tempo Massimo Copribile (Stint + Pit)
        $tempo_massimo_stint = $stint_rimanenti * $stint_utile;
        $tempo_massimo_copribile_totale = $tempo_massimo_stint + $totale_tempo_pit_rimanente;

        // 4. Calcolo Margine e Jolly
        $margine = $tempo_massimo_copribile_totale - $minutiResidui;

        $jolly_disponibili = 0;
        $pit_extra_necessari = 0;
        if ($margine >= 0) {
            $jolly_disponibili = floor($margine / $stint_utile);
            $stato_strategia = "OK";
            $colore_strategia = "#28a745";
        } else {
            $pit_extra_necessari = ceil(abs($margine) / $stint_utile);
            $stato_strategia = "IN AFFANNO";
            $colore_strategia = "#dc3545";
        }

        // 5. Calcolo Media Stint Rimanenti
        // Dobbiamo coprire i minuti residui. Togliamo il tempo fisso dei pit stop.
        // Il resto è il tempo netto da passare in pista, diviso per gli stint.
        $tempo_da_passare_in_pista = $minutiResidui - $totale_tempo_pit_rimanente;
        $media_stint = 0;
        if ($stint_rimanenti > 0 && $tempo_da_passare_in_pista > 0) {
            $media_stint = $tempo_da_passare_in_pista / $stint_rimanenti;
        }

        // 6. Formattazione in MM:SS sicura
        $minuti_media = floor($media_stint);
        $secondi_media = round(($media_stint - $minuti_media) * 60);
        if ($secondi_media == 60) {
            $minuti_media += 1;
            $secondi_media = 0;
        }
        $media_stint_formattata = sprintf("%02d:%02d", $minuti_media, $secondi_media);

        // Calcolo Tempo Eccedente
        $tempo_eccedente = $tempo_massimo_copribile_totale - $minutiResidui;
        if ($tempo_eccedente < 0) {
            $tempo_eccedente = 0;
        }
        $ore_eccedenti = floor($tempo_eccedente / 60);
        $min_eccedenti = $tempo_eccedente % 60;
        $tempo_eccedente_formattato = sprintf("%02d:%02d", $ore_eccedenti, $min_eccedenti);

        return [
            'pit_fatti' => $pit_fatti,
            'pit_minimi' => $pit_obbligatori,
            'pit_rimanenti_obbligatori' => $pit_rimanenti_obbligatori,
            'tempo_massimo_copribile' => $tempo_massimo_copribile_totale,
            'margine' => $margine,
            'stato_strategia' => $stato_strategia,
            'colore_strategia' => $colore_strategia,
            'jolly_disponibili' => $jolly_disponibili,
            'pit_extra_necessari' => $pit_extra_necessari,
            'media_stint_formattata' => $media_stint_formattata,
            'tempo_eccedente_formattato' => $tempo_eccedente_formattato
        ];
    }

    public function index($gara_id, $team_id = null) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);

        if (!$gara) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }

        // Se è specificato un team_id, verifichiamo che esista e sia gestito
        $teamSelezionato = null;
        if ($team_id) {
            $iscrittoModel = new IscrittoGara();
            $iscritto = $iscrittoModel->ottieniPerTeamEGara($gara_id, $team_id);
            if (!$iscritto || $iscritto['is_gestito'] != 1) {
                header('Location: ' . BASE_URL . '/home/index');
                exit;
            }
            $teamSelezionato = $iscritto;
        }

        $pilotiGaraModel = new PilotiGara();
        $roster = $pilotiGaraModel->ottieniPerGaraETeam($gara_id, $team_id);

        $stintModel = new StintMioTeam();
        
        $stintAttivo = $stintModel->ottieniStintAttivo($gara_id, $team_id);
        $tuttiStint = $stintModel->ottieniTuttiStintGara($gara_id, $team_id);
        // Recupera gli stint cancellati solo se c'è un team specifico
        $stintCancellati = [];
        if ($team_id) {
            $stintCancellati = $stintModel->ottieniStintCancellatiPerTeam($gara_id, $team_id);
        }

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

        // Calcola i tempi totali di guida per ogni pilota
        $tempiTotaliPiloti = $this->calcolaTempiTotaliPiloti($gara_id, $roster, $stintAttivo);

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

        // Passa anche gli stint cancellati alla vista
        $data = [
            'gara' => $gara,
            'teamSelezionato' => $teamSelezionato,
            'roster' => $roster,
            'stintAttivo' => $stintAttivo,
            'tuttiStint' => $tuttiStint,
            'stintCancellati' => $stintCancellati,
            'strategia' => $strategia,
            'tempiTotaliPiloti' => $tempiTotaliPiloti,
            'tempoResiduoHHMM' => $tempoResiduoHHMM,
            'kart_in_fila' => $kart_in_fila,
            'avversari_kart' => $avversari_kart
        ];

        require_once BASE_PATH . '/app/Views/muretto/index.php';
    }

    /**
     * Mostra il muretto multi-team con tutti i team gestiti affiancati.
     * 
     * @param int $gara_id ID della gara
     * @return void
     */
    public function multi($gara_id) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);

        if (!$gara) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }

        // Recupera tutti i team gestiti per questa gara
        $iscrittoModel = new IscrittoGara();
        $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara_id);

        if (empty($teamGestiti)) {
            // Se non ci sono team gestiti, reindirizza al setup
            header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
            exit;
        }

        // Prepara i dati per ogni team
        $teamData = [];
        foreach ($teamGestiti as $team) {
            $teamData[] = $this->preparaDatiTeam($gara_id, $team, $gara);
        }

        require_once BASE_PATH . '/app/Views/muretto/multi.php';
    }

    /**
     * Prepara i dati per un singolo team per il muretto multi-team.
     * 
     * @param int $gara_id ID della gara
     * @param array $team Dati del team
     * @param array $gara Dati della gara
     * @return array Dati completi del team
     */
    private function preparaDatiTeam($gara_id, $team, $gara) {
        $pilotiGaraModel = new PilotiGara();
        $roster = $pilotiGaraModel->ottieniPerGaraETeam($gara_id, $team['team_id']);

        $stintModel = new StintMioTeam();
        
        // Filtra gli stint per questo team specifico
        $stintAttivo = $stintModel->ottieniStintAttivo($gara_id, $team['team_id']);
        $tuttiStint = $stintModel->ottieniTuttiStintGara($gara_id, $team['team_id']);
        $stintCancellati = $stintModel->ottieniStintCancellatiPerTeam($gara_id, $team['team_id']);

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

        // Calcola i tempi totali di guida per ogni pilota
        $tempiTotaliPiloti = $this->calcolaTempiTotaliPiloti($gara_id, $roster, $stintAttivo);

        return [
            'team' => $team,
            'gara' => $gara,
            'roster' => $roster,
            'stintAttivo' => $stintAttivo,
            'tuttiStint' => $tuttiStint,
            'stintCancellati' => $stintCancellati,
            'tempoResiduoHHMM' => $tempoResiduoHHMM,
            'strategia' => $strategia,
            'tempiTotaliPiloti' => $tempiTotaliPiloti,
            'minutiResidui' => $minutiResidui
        ];
    }

    public function inizia($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pilota_id = $_POST['pilota_id'] ?? null;

            if ($pilota_id) {
                $pilotiGaraModel = new PilotiGara();
                $stintModel = new StintMioTeam();
                
                // Ottieni team_id dal POST o dal pilota selezionato
                $team_id = $_POST['team_id'] ?? null;
                $pilotaTrovato = false;
                
                // Verifica se team_id esiste nel database
                try {
                    $sql = "SELECT team_id FROM piloti_gara LIMIT 1";
                    Database::getIstanza()->getConnessione()->query($sql);
                    $hasTeamId = true;
                } catch (Exception $e) {
                    $hasTeamId = false;
                }
                
                if ($hasTeamId) {
                    // Database con team_id - verifica il pilota nel team specifico
                    if ($team_id) {
                        // team_id passato dal POST - cerca solo piloti di quel team
                        $roster = $pilotiGaraModel->ottieniPerGaraETeam($gara_id, $team_id);
                        
                        foreach ($roster as $pilota) {
                            if ($pilota['pilota_id'] == $pilota_id) {
                                $pilotaTrovato = true;
                                break;
                            }
                        }
                        
                        if (!$pilotaTrovato) {
                            $_SESSION['error'] = "Il pilota selezionato non è associato a questo team.";
                        }
                    } else {
                        // team_id non passato dal POST - cerca tra tutti i piloti della gara
                        $roster = $pilotiGaraModel->ottieniPerGara($gara_id);
                        
                        foreach ($roster as $pilota) {
                            if ($pilota['pilota_id'] == $pilota_id && $pilota['team_id']) {
                                // Pilota ha già team_id - usa quello
                                $team_id = $pilota['team_id'];
                                $pilotaTrovato = true;
                                break;
                            }
                        }
                        
                        if (!$team_id) {
                            $_SESSION['error'] = "Il pilota selezionato non è associato a un team gestito. Verifica il setup della gara.";
                        }
                    }
                } else {
                    // Database senza team_id - usa il primo team gestito disponibile
                    $iscrittoModel = new IscrittoGara();
                    $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara_id);
                    
                    if (!empty($teamGestiti)) {
                        $team_id = $teamGestiti[0]['team_id'];
                        $pilotaTrovato = true;
                    }
                }
                
                if (!$pilotaTrovato) {
                    $_SESSION['error'] = "Pilota non trovato nel roster.";
                } elseif ($stintModel->ottieniStintAttivo($gara_id, $team_id)) {
                    $_SESSION['error'] = "C'è già un pilota in pista! Termina il suo stint prima di farne salire un altro.";
                } else {
                    $stintModel->iniziaStint($gara_id, $pilota_id, $team_id);
                    $_SESSION['success'] = "Stint iniziato.";
                }
            } else {
                $_SESSION['error'] = "Seleziona un pilota per iniziare lo stint.";
            }
            
            // Redirect dinamico per multi-team
            $is_multi = isset($_GET['multi']) || isset($_POST['multi']);
            if ($is_multi) {
                header('Location: ' . BASE_URL . '/muretto/multi/' . $gara_id);
            } else {
                header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            }
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    public function termina($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $durata = $_POST['durata'] ?? null;
            
            // Ottieni team_id dal POST
            $team_id = $_POST['team_id'] ?? null;

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
            
            // Redirect dinamico per multi-team
            $is_multi = isset($_GET['multi']) || isset($_POST['multi']);
            if ($is_multi) {
                header('Location: ' . BASE_URL . '/muretto/multi/' . $gara_id);
            } else {
                header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            }
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
            // Redirect dinamico per multi-team
            $is_multi = isset($_GET['multi']) || isset($_POST['multi']);
            if ($is_multi) {
                header('Location: ' . BASE_URL . '/muretto/multi/' . $gara_id);
            } else {
                header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
            }
            exit;
        }

        $stintModel = new StintMioTeam();
        $eliminato = $stintModel->eliminaStintAttivo($gara_id, $stint_id);

        if ($eliminato) {
            $_SESSION['success'] = "Inserimento stint annullato con successo.";
        } else {
            $_SESSION['error'] = "Impossibile annullare lo stint (potrebbe non essere piu attivo).";
        }

        // Redirect dinamico per multi-team
        $is_multi = isset($_GET['multi']) || isset($_POST['multi']);
        if ($is_multi) {
            header('Location: ' . BASE_URL . '/muretto/multi/' . $gara_id);
        } else {
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id);
        }
        exit;
    }

    public function modificaDurata($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $durata = $_POST['durata'] ?? null;
            
            // Ottieni team_id dal POST
            $team_id = $_POST['team_id'] ?? null;

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
            
            // Redirect dinamico per multi-team
            $is_multi = isset($_GET['multi']) || isset($_POST['multi']);
            if ($is_multi) {
                header('Location: ' . BASE_URL . '/muretto/multi/' . $gara_id);
            } else {
                header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            }
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    public function aggiornaPrimoIngresso($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stint_id = $_POST['stint_id'] ?? null;
            $ingresso = $_POST['minuto_ingresso_hhmm'] ?? null;
            
            // Ottieni team_id dal POST
            $team_id = $_POST['team_id'] ?? null;

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
            
            // Redirect dinamico per multi-team
            $is_multi = isset($_GET['multi']) || isset($_POST['multi']);
            if ($is_multi) {
                header('Location: ' . BASE_URL . '/muretto/multi/' . $gara_id);
            } else {
                header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            }
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

    /**
     * API endpoint per polling AJAX dei dati multi-team
     * 
     * @param int $gara_id ID della gara
     * @return void
     */
    public function apiMultiData($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
            exit;
        }

        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);

        if (!$gara) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Gara non trovata']);
            exit;
        }

        $iscrittoModel = new IscrittoGara();
        $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara_id);

        if (empty($teamGestiti)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }

        $multiData = [];

        foreach ($teamGestiti as $team) {
            $teamData = $this->preparaDatiTeam($gara_id, $team, $gara);
            $multiData[] = [
                'team_id' => $team['team_id'],
                'stintAttivo' => $teamData['stintAttivo'],
                'strategia' => $teamData['strategia'],
                'roster' => $teamData['roster'],
                'tempiTotaliPiloti' => $teamData['tempiTotaliPiloti']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $multiData]);
        exit;
    }

    /**
     * Cancella uno stint (soft delete) con conferma.
     * 
     * @param int $stint_id ID dello stint da cancellare
     * @param int $gara_id ID della gara
     * @param int|null $team_id ID del team (opzionale)
     * @return void
     */
    public function cancellaStint($stint_id, $gara_id, $team_id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        // Verifica conferma
        $conferma = $_POST['conferma'] ?? '';
        if ($conferma !== 'CONFERMA') {
            $_SESSION['error'] = "Conferma non valida. Per cancellare uno stint, digitare esattamente 'CONFERMA'.";
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        $stintModel = new StintMioTeam();
        $stint = $stintModel->ottieniPerId($stint_id);

        if (!$stint || $stint['gara_id'] != $gara_id || ($team_id && $stint['team_id'] != $team_id)) {
            $_SESSION['error'] = "Stint non valido o non appartiene a questa gara/team.";
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        // Verifica che lo stint non sia attivo
        if ($stint['durata_minuti'] === null) {
            $_SESSION['error'] = "Impossibile cancellare uno stint attualmente in corso.";
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        try {
            // Soft delete: marca come cancellato
            $sql = "UPDATE stint_mio_team SET cancellato = 1 WHERE id = :id";
            $stmt = Database::getIstanza()->getConnessione()->prepare($sql);
            $stmt->execute([':id' => $stint_id]);

            // Ricalcola la timeline per mantenere la coerenza
            $stintModel->ricalcolaTimeline($gara_id, $team_id);

            $_SESSION['success'] = "Stint cancellato con successo. Puoi ripristinarlo utilizzando l'apposita funzione.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Errore durante la cancellazione dello stint: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
        exit;
    }

    /**
     * Ripristina uno stint precedentemente cancellato.
     * 
     * @param int $stint_id ID dello stint da ripristinare
     * @param int $gara_id ID della gara
     * @param int|null $team_id ID del team (opzionale)
     * @return void
     */
    public function ripristinaStint($stint_id, $gara_id, $team_id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        $stintModel = new StintMioTeam();
        $stint = $stintModel->ottieniPerId($stint_id);

        if (!$stint || $stint['gara_id'] != $gara_id || ($team_id && $stint['team_id'] != $team_id)) {
            $_SESSION['error'] = "Stint non valido o non appartiene a questa gara/team.";
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        // Verifica che lo stint sia effettivamente cancellato
        if (!isset($stint['cancellato']) || $stint['cancellato'] != 1) {
            $_SESSION['error'] = "Questo stint non è stato cancellato.";
            header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
            exit;
        }

        try {
            // Ripristina lo stint
            $sql = "UPDATE stint_mio_team SET cancellato = 0 WHERE id = :id";
            $stmt = Database::getIstanza()->getConnessione()->prepare($sql);
            $stmt->execute([':id' => $stint_id]);

            // Ricalcola la timeline per mantenere la coerenza
            $stintModel->ricalcolaTimeline($gara_id, $team_id);

            $_SESSION['success'] = "Stint ripristinato con successo.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Errore durante il ripristino dello stint: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/muretto/index/' . $gara_id . ($team_id ? "/$team_id" : ""));
        exit;
    }
}
