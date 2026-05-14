<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\Team;
use App\Models\IscrittoGara;
use App\Models\FilePit;
use App\Models\PilotiGara;
use App\Models\StintMioTeam;

/**
 * Classe GareController
 * 
 * Gestisce la logica di creazione gara e setup delle iscrizioni.
 */
class GareController {
    /**
     * Verifica se la richiesta corrente e asincrona (AJAX).
     *
     * @return bool
     */
    private function eRichiestaAjax() {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['CONTENT_TYPE']) &&
            strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) &&
            strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false
        );
    }

    /**
     * Restituisce una risposta JSON standardizzata.
     *
     * @param int $statusCode Codice HTTP della risposta
     * @param array $payload Dati da serializzare in JSON
     * @return void
     */
    private function rispondiJson($statusCode, $payload) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
    /**
     * Riceve i dati in POST per creare una nuova gara e reindirizza alla home.
     * 
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome_gara = trim($_POST['nome_gara'] ?? '');
            $data_evento = trim($_POST['data_evento'] ?? '');

            if ($nome_gara !== '' && $data_evento !== '') {
                $garaModel = new Gara();
                $garaModel->crea([
                    'nome_gara' => $nome_gara,
                    'data_evento' => $data_evento
                ]);
            }
        }
        
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Mostra l'interfaccia di setup per una specifica gara.
     * Carica i dati della gara, i team disponibili e gli iscritti attuali.
     * 
     * @param int $gara_id L'ID della gara
     * @return void
     */
    public function setup($gara_id) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);

        if (!$gara) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }

        $teamModel = new Team();
        $teams = $teamModel->ottieniNonIscritti($gara_id);
        $tuttiITeam = $teamModel->ottieniTutti();

        $iscrittoModel = new IscrittoGara();
        $iscritti = $iscrittoModel->ottieniPerGara($gara_id);

        $filePitModel = new FilePit();
        $filePit = $filePitModel->ottieniPerGara($gara_id);

        $pilotiGaraModel = new PilotiGara();
        $pilotiRoster = $pilotiGaraModel->ottieniPerGara($gara_id);
        $pilotiDisponibili = $pilotiGaraModel->ottieniNonIscritti($gara_id);

        // Recupera i team gestiti per il form piloti
        $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara_id);

        // Controlla se ci sono stint attivi (blocco di sicurezza)
        $stintModel = new StintMioTeam();
        $haStintAttivi = $stintModel->haStintAttivi($gara_id);

        require_once BASE_PATH . '/app/Views/gare/setup.php';
    }

    /**
     * Aggiorna i parametri base di una gara (nome, data, durata).
     * 
     * @return void
     */
    public function aggiornaParametri() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gara_id = $_POST['gara_id'] ?? null;
            $nome_gara = trim($_POST['nome_gara'] ?? '');
            $data_evento = trim($_POST['data_evento'] ?? '');
            $durata_minuti = (int)($_POST['durata_minuti'] ?? 0);
            $min_stint = (int)($_POST['min_stint'] ?? 0);
            $tempo_minimo_pit = (int)($_POST['tempo_minimo_pit'] ?? 0);
            $durata_max_stint = (int)($_POST['durata_max_stint'] ?? 0);
            $durata_min_stint = isset($_POST['durata_min_stint']) && $_POST['durata_min_stint'] !== '' ? (int)$_POST['durata_min_stint'] : null;
            $tempo_max_pilota = (int)($_POST['tempo_max_pilota'] ?? 0);
            $tempo_min_pilota = (int)($_POST['tempo_min_pilota'] ?? 0);
            $mio_team_id = isset($_POST['mio_team_id']) && $_POST['mio_team_id'] !== '' ? (int)$_POST['mio_team_id'] : null;

            if ($gara_id && $nome_gara !== '' && $data_evento !== '') {
                $garaModel = new Gara();
                $garaModel->aggiorna($gara_id, [
                    'nome_gara' => $nome_gara,
                    'data_evento' => $data_evento,
                    'durata_minuti' => $durata_minuti,
                    'min_stint' => $min_stint,
                    'tempo_minimo_pit' => $tempo_minimo_pit,
                    'durata_max_stint' => $durata_max_stint,
                    'durata_min_stint' => $durata_min_stint,
                    'tempo_max_pilota' => $tempo_max_pilota,
                    'tempo_min_pilota' => $tempo_min_pilota,
                    'mio_team_id' => $mio_team_id
                ]);
                $_SESSION['success'] = "Parametri gara aggiornati con successo.";
            } else {
                $_SESSION['error'] = "Campi obbligatori mancanti per l'aggiornamento gara.";
            }
            header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * API JSON per aggiornare lo stato di gestione di un team (is_gestito).
     *
     * @param int $gara_id ID della gara
     * @return void
     */
    public function apiAggiornaGestito($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->rispondiJson(405, ['status' => 'error', 'message' => 'Metodo non consentito.']);
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            $this->rispondiJson(400, ['status' => 'error', 'message' => 'Payload JSON non valido.']);
            return;
        }

        $iscritto_id = (int)($payload['iscritto_id'] ?? 0);
        $is_gestito = (int)($payload['is_gestito'] ?? 0);

        if ($iscritto_id <= 0) {
            $this->rispondiJson(400, ['status' => 'error', 'message' => 'ID iscritto non valido.']);
            return;
        }

        // Verifica limite massimo di 4 team gestiti
        $iscrittoModel = new IscrittoGara();
        if ($is_gestito === 1) {
            $teamGestiti = $iscrittoModel->contaGestiti($gara_id);
            if ($teamGestiti >= 4) {
                $this->rispondiJson(400, ['status' => 'error', 'message' => 'Puoi gestire al massimo 4 team per gara.']);
            }
        }

        try {
            $result = $iscrittoModel->aggiornaGestito($iscritto_id, $is_gestito);
            
            if ($result) {
                $this->rispondiJson(200, [
                    'status' => 'success', 
                    'message' => $is_gestito ? 'Team aggiunto ai gestiti.' : 'Team rimosso dai gestiti.'
                ]);
            } else {
                $this->rispondiJson(500, ['status' => 'error', 'message' => 'Errore durante l\'aggiornamento.']);
            }
        } catch (Exception $e) {
            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Errore del server.']);
        }
    }

    /**
     * API JSON per aggiornare i parametri gara senza ricaricare la pagina setup.
     *
     * @param int $gara_id ID della gara
     * @return void
     */
    public function apiAggiornaParametri($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->rispondiJson(405, ['status' => 'error', 'message' => 'Metodo non consentito.']);
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            $this->rispondiJson(400, ['status' => 'error', 'message' => 'Payload JSON non valido.']);
        }

        $nome_gara = trim((string)($payload['nome_gara'] ?? ''));
        $data_evento = trim((string)($payload['data_evento'] ?? ''));
        $durata_minuti = (int)($payload['durata_minuti'] ?? 0);
        $min_stint = (int)($payload['min_stint'] ?? 0);
        $tempo_minimo_pit = (int)($payload['tempo_minimo_pit'] ?? 0);
        $durata_max_stint = (int)($payload['durata_max_stint'] ?? 0);
        $durata_min_stint = isset($payload['durata_min_stint']) && $payload['durata_min_stint'] !== ''
            ? (int)$payload['durata_min_stint']
            : null;
        $tempo_max_pilota = (int)($payload['tempo_max_pilota'] ?? 0);
        $tempo_min_pilota = (int)($payload['tempo_min_pilota'] ?? 0);
        $mio_team_id = isset($payload['mio_team_id']) && $payload['mio_team_id'] !== ''
            ? (int)$payload['mio_team_id']
            : null;

        if ($gara_id <= 0 || $nome_gara === '' || $data_evento === '') {
            $this->rispondiJson(422, ['status' => 'error', 'message' => 'Campi obbligatori mancanti.']);
        }

        $garaModel = new Gara();
        $ok = $garaModel->aggiorna($gara_id, [
            'nome_gara' => $nome_gara,
            'data_evento' => $data_evento,
            'durata_minuti' => $durata_minuti,
            'min_stint' => $min_stint,
            'tempo_minimo_pit' => $tempo_minimo_pit,
            'durata_max_stint' => $durata_max_stint,
            'durata_min_stint' => $durata_min_stint,
            'tempo_max_pilota' => $tempo_max_pilota,
            'tempo_min_pilota' => $tempo_min_pilota,
            'mio_team_id' => $mio_team_id
        ]);

        if (!$ok) {
            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Aggiornamento parametri fallito.']);
        }

        $this->rispondiJson(200, ['status' => 'success']);
    }

    /**
     * API: aggiorna nome e colore di una fila pit della gara (POST).
     *
     * Parametri attesi: file_pit_id, nome_colore (o nome_fila), colore_hex.
     *
     * @param int $gara_id ID della gara
     * @return void
     */
    public function apiAggiornaFila($gara_id) {
        $gara_id = (int)$gara_id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->rispondiJson(405, ['status' => 'error', 'message' => 'Metodo non consentito.']);
        }

        $file_pit_id = (int)($_POST['file_pit_id'] ?? 0);
        $nome_colore = trim((string)($_POST['nome_colore'] ?? $_POST['nome_fila'] ?? ''));
        $colore_hex = trim((string)($_POST['colore_hex'] ?? '#343a40'));

        if ($gara_id <= 0 || $file_pit_id <= 0 || $nome_colore === '') {
            $this->rispondiJson(422, ['status' => 'error', 'message' => 'Dati fila non validi.']);
        }

        $filePitModel = new FilePit();
        $ok = $filePitModel->aggiornaPerGara($file_pit_id, $gara_id, $nome_colore, $colore_hex);

        if (!$ok) {
            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Aggiornamento fila fallito.']);
        }

        $aggiornata = $filePitModel->ottieniPerId($file_pit_id);
        $this->rispondiJson(200, ['status' => 'success', 'data' => $aggiornata]);
    }

    /**
     * Aggiunge una fila pit (corsia box) alla gara.
     * 
     * @return void
     */
    public function aggiungiFilaPit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gara_id = (int)($_POST['gara_id'] ?? 0);
            $nome_colore = trim($_POST['nome_colore'] ?? '');
            $colore_hex = trim($_POST['colore_hex'] ?? '#343a40');
            $ordine = (int)($_POST['ordine'] ?? 0);

            if ($gara_id > 0 && $nome_colore !== '') {
                $filePitModel = new FilePit();
                $creato = $filePitModel->crea([
                    'gara_id' => $gara_id,
                    'nome_colore' => $nome_colore,
                    'colore_hex' => $colore_hex,
                    'ordine' => $ordine
                ]);

                if ($this->eRichiestaAjax()) {
                    if (!$creato) {
                        $this->rispondiJson(500, ['status' => 'error', 'message' => 'Impossibile aggiungere la fila pit.']);
                    }
                    $fileGara = $filePitModel->ottieniPerGara($gara_id);
                    $nuovaFila = null;
                    foreach (array_reverse($fileGara) as $fila) {
                        if ((string)$fila['nome_colore'] === $nome_colore
                            && (string)$fila['colore_hex'] === $colore_hex
                            && (int)$fila['ordine'] === $ordine) {
                            $nuovaFila = $fila;
                            break;
                        }
                    }
                    $this->rispondiJson(200, ['status' => 'success', 'data' => $nuovaFila]);
                }

                $_SESSION['success'] = "Fila Pit aggiunta.";
            } else {
                if ($this->eRichiestaAjax()) {
                    $this->rispondiJson(422, ['status' => 'error', 'message' => 'Nome fila richiesto.']);
                }
                $_SESSION['error'] = "Nome colore richiesto per aggiungere fila pit.";
            }
            header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Rimuove una fila pit.
     * 
     * @param int $id ID della fila pit
     * @param int $gara_id ID della gara (per redirect)
     * @return void
     */
    public function rimuoviFilaPit($id, $gara_id) {
        $filePitModel = new FilePit();
        $ok = $filePitModel->elimina($id);

        if ($this->eRichiestaAjax()) {
            if ($ok) {
                $this->rispondiJson(200, ['status' => 'success']);
            }
            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Impossibile rimuovere la fila pit.']);
        }

        $_SESSION['success'] = "Fila Pit rimossa.";
        header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
        exit;
    }

    /**
     * Aggiunge un pilota del team al roster della gara.
     * 
     * @return void
     */
    public function aggiungiPilotaGara() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gara_id = (int)($_POST['gara_id'] ?? 0);
            $pilota_id = (int)($_POST['pilota_id'] ?? 0);
            $team_id = (int)($_POST['team_id'] ?? 0);

            if ($gara_id > 0 && $pilota_id > 0 && $team_id > 0) {
                // Validazione: verifica che il team sia gestito per questa gara
                $iscrittoModel = new IscrittoGara();
                $teamGestito = $iscrittoModel->ottieniPerTeamEGara($gara_id, $team_id);
                if (!$teamGestito || $teamGestito['is_gestito'] != 1) {
                    if ($this->eRichiestaAjax()) {
                        $this->rispondiJson(422, ['status' => 'error', 'message' => 'Team non valido o non gestito.']);
                    }
                    $_SESSION['error'] = "Team non valido o non gestito.";
                    return;
                }

                // Validazione: verifica che il pilota non sia già iscritto a qualsiasi team in questa gara
                $pilotiGaraModel = new PilotiGara();
                $pilotaEsistente = $pilotiGaraModel->ottieniPilotaPerGara($gara_id, $pilota_id);
                if ($pilotaEsistente) {
                    if ($this->eRichiestaAjax()) {
                        $this->rispondiJson(422, ['status' => 'error', 'message' => 'Questo pilota è già iscritto a un team in questa gara.']);
                    }
                    $_SESSION['error'] = "Questo pilota è già iscritto a un team in questa gara.";
                    return;
                }

                $creato = $pilotiGaraModel->crea($gara_id, $pilota_id, $team_id);

                if ($this->eRichiestaAjax()) {
                    if (!$creato) {
                        $this->rispondiJson(500, ['status' => 'error', 'message' => 'Impossibile aggiungere il pilota.']);
                    }
                    $roster = $pilotiGaraModel->ottieniPerGara($gara_id);
                    $nuovoPilota = null;
                    foreach (array_reverse($roster) as $pilota) {
                        if ((int)$pilota['pilota_id'] === $pilota_id) {
                            $nuovoPilota = $pilota;
                            break;
                        }
                    }
                    $this->rispondiJson(200, ['status' => 'success', 'data' => $nuovoPilota]);
                }

                $_SESSION['success'] = "Pilota aggiunto al roster della gara.";
            } else {
                if ($this->eRichiestaAjax()) {
                    $this->rispondiJson(422, ['status' => 'error', 'message' => 'Pilota non selezionato.']);
                }
                $_SESSION['error'] = "Pilota non selezionato.";
            }
            header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Rimuove un pilota dal roster della gara.
     * 
     * @param int $id ID dell'associazione pilota_gara
     * @param int $gara_id ID della gara (per redirect)
     * @return void
     */
    public function rimuoviPilotaGara($id, $gara_id) {
        $pilotiGaraModel = new PilotiGara();
        $roster = $pilotiGaraModel->ottieniPerGara($gara_id);
        $recordDaEliminare = null;
        foreach ($roster as $r) {
            if ((int)$r['id'] === (int)$id) {
                $recordDaEliminare = $r;
                break;
            }
        }

        $ok = $pilotiGaraModel->elimina($id);

        if ($this->eRichiestaAjax()) {
            if ($ok) {
                $this->rispondiJson(200, ['status' => 'success', 'data' => $recordDaEliminare]);
            }
            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Impossibile rimuovere il pilota dal roster.']);
        }

        $_SESSION['success'] = "Pilota rimosso dal roster.";
        header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
        exit;
    }

    /**
     * Elabora il form di iscrizione assegnando un numero a un team per una gara.
     * Reindirizza alla pagina di setup al termine.
     * 
     * @return void
     */
    public function iscriviTeam() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gara_id = (int)($_POST['gara_id'] ?? 0);
            $team_id = (int)($_POST['team_id'] ?? 0);
            $numero_gara = trim($_POST['numero_gara'] ?? '');

            if ($gara_id > 0 && $team_id > 0 && $numero_gara !== '') {
                $iscrittoModel = new IscrittoGara();
                
                // Validazione integrità
                $errore = $iscrittoModel->esisteGia($gara_id, $team_id, $numero_gara);
                
                if ($errore === 'team_esistente') {
                    if ($this->eRichiestaAjax()) {
                        $this->rispondiJson(422, ['status' => 'error', 'message' => "Il team selezionato è già iscritto a questa gara."]);
                    }
                    $_SESSION['error'] = "Il team selezionato è già iscritto a questa gara.";
                } elseif ($errore === 'numero_esistente') {
                    if ($this->eRichiestaAjax()) {
                        $this->rispondiJson(422, ['status' => 'error', 'message' => "Il numero di gara $numero_gara è già stato assegnato a un altro team."]);
                    }
                    $_SESSION['error'] = "Il numero di gara $numero_gara è già stato assegnato a un altro team.";
                } else {
                    $creato = $iscrittoModel->crea([
                        'gara_id' => $gara_id,
                        'team_id' => $team_id,
                        'numero_gara' => $numero_gara
                    ]);

                    if ($this->eRichiestaAjax()) {
                        if (!$creato) {
                            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Impossibile iscrivere il team.']);
                        }
                        $iscrittiGara = $iscrittoModel->ottieniPerGara($gara_id);
                        $nuovaIscrizione = null;
                        foreach (array_reverse($iscrittiGara) as $iscrizione) {
                            if ((int)$iscrizione['team_id'] === $team_id && (string)$iscrizione['numero_gara'] === $numero_gara) {
                                $nuovaIscrizione = $iscrizione;
                                break;
                            }
                        }
                        $this->rispondiJson(200, ['status' => 'success', 'data' => $nuovaIscrizione]);
                    }

                    $_SESSION['success'] = "Team iscritto con successo!";
                }
            } else {
                if ($this->eRichiestaAjax()) {
                    $this->rispondiJson(422, ['status' => 'error', 'message' => 'Compila tutti i campi obbligatori.']);
                }
                $_SESSION['error'] = "Compila tutti i campi obbligatori.";
            }
        }
        
        $redirect_id = $gara_id ?? '';
        header('Location: ' . BASE_URL . '/gare/setup/' . $redirect_id);
        exit;
    }

    /**
     * Cancella un'iscrizione e ricarica il setup della gara.
     * 
     * @param int $id L'ID dell'iscrizione in iscritti_gara
     * @param int $gara_id L'ID della gara per il reindirizzamento
     * @return void
     */
    public function rimuoviIscrizione($id, $gara_id) {
        $iscrittoModel = new IscrittoGara();
        $recordDaEliminare = $iscrittoModel->ottieniPerId($id);
        $ok = $iscrittoModel->elimina($id);

        if ($this->eRichiestaAjax()) {
            if ($ok) {
                $teamModel = new Team();
                $team = null;
                if ($recordDaEliminare && isset($recordDaEliminare['team_id'])) {
                    $team = $teamModel->ottieniPerId($recordDaEliminare['team_id']);
                }
                $this->rispondiJson(200, [
                    'status' => 'success',
                    'data' => [
                        'id' => (int)$id,
                        'team_id' => isset($recordDaEliminare['team_id']) ? (int)$recordDaEliminare['team_id'] : null,
                        'numero_gara' => $recordDaEliminare['numero_gara'] ?? null,
                        'nome_team' => $team['nome_team'] ?? null
                    ]
                ]);
            }
            $this->rispondiJson(500, ['status' => 'error', 'message' => 'Impossibile rimuovere iscrizione.']);
        }

        header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
        exit;
    }

    /**
     * Mostra il form per modificare il numero di gara di un'iscrizione.
     * 
     * @param int $id L'ID dell'iscrizione
     * @return void
     */
    public function modificaIscrizione($id) {
        $iscrittoModel = new IscrittoGara();
        $iscrizione = $iscrittoModel->ottieniPerId($id);
        
        if (!$iscrizione) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }

        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($iscrizione['gara_id']);

        $teamModel = new Team();
        $team = $teamModel->ottieniPerId($iscrizione['team_id']);

        require_once BASE_PATH . '/app/Views/gare/modifica_iscrizione.php';
    }

    /**
     * Salva il nuovo numero di gara controllando che non sia già in uso.
     * 
     * @param int $id L'ID dell'iscrizione
     * @return void
     */
    public function aggiornaIscrizione($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuovo_numero = trim($_POST['numero_gara'] ?? '');
            
            $iscrittoModel = new IscrittoGara();
            $iscrizione = $iscrittoModel->ottieniPerId($id);

            if ($iscrizione && $nuovo_numero !== '') {
                $gara_id = $iscrizione['gara_id'];
                $team_id = $iscrizione['team_id'];

                $errore = $iscrittoModel->esisteGia($gara_id, $team_id, $nuovo_numero, $id);
                
                if ($errore === 'numero_esistente') {
                    $_SESSION['error'] = "Il numero di gara $nuovo_numero è già occupato.";
                    header('Location: ' . BASE_URL . '/gare/modificaIscrizione/' . $id);
                    exit;
                } else {
                    $iscrittoModel->aggiorna($id, $nuovo_numero);
                    $_SESSION['success'] = "Numero di gara aggiornato!";
                }
                
                header('Location: ' . BASE_URL . '/gare/setup/' . $gara_id);
                exit;
            }
        }
        
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * API endpoint per ottenere i team gestiti per il form piloti
     * 
     * @param int $gara_id ID della gara
     * @return void
     */
    public function apiTeamGestiti($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
            exit;
        }

        $iscrittoModel = new IscrittoGara();
        $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara_id);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $teamGestiti]);
        exit;
    }

    /**
     * API endpoint per restituire l'HTML del roster per team.
     */
    public function apiRosterTeam($gara_id) {
        // Permetti sempre le richieste API (semplificato per debug)
        header('Content-Type: application/json');

        try {
            // Recupera i dati necessari
            $pilotiGaraModel = new PilotiGara();
            $pilotiRoster = $pilotiGaraModel->ottieniPerGara($gara_id);
            
            $iscrittoModel = new IscrittoGara();
            $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara_id);

            // Genera l'HTML del roster
            ob_start();
            include BASE_PATH . '/app/Views/gare/_roster_team.php';
            $html = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'html' => $html]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Errore: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * API endpoint per restituire i piloti disponibili.
     */
    public function apiPilotiDisponibili($gara_id) {
        // Permetti sempre le richieste API (semplificato per debug)
        header('Content-Type: application/json');

        try {
            $pilotiGaraModel = new PilotiGara();
            $pilotiDisponibili = $pilotiGaraModel->ottieniNonIscritti($gara_id);

            echo json_encode(['status' => 'success', 'data' => $pilotiDisponibili]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Errore: ' . $e->getMessage()]);
            exit;
        }
    }
}
