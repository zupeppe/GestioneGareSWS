<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\Team;
use App\Models\IscrittoGara;
use App\Models\FilePit;
use App\Models\PilotiGara;

/**
 * Classe GareController
 * 
 * Gestisce la logica di creazione gara e setup delle iscrizioni.
 */
class GareController {
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
     * Aggiunge una fila pit (corsia box) alla gara.
     * 
     * @return void
     */
    public function aggiungiFilaPit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gara_id = $_POST['gara_id'] ?? null;
            $nome_colore = trim($_POST['nome_colore'] ?? '');
            $colore_hex = trim($_POST['colore_hex'] ?? '#343a40');
            $ordine = (int)($_POST['ordine'] ?? 0);

            if ($gara_id && $nome_colore !== '') {
                $filePitModel = new FilePit();
                $filePitModel->crea([
                    'gara_id' => $gara_id,
                    'nome_colore' => $nome_colore,
                    'colore_hex' => $colore_hex,
                    'ordine' => $ordine
                ]);
                $_SESSION['success'] = "Fila Pit aggiunta.";
            } else {
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
        $filePitModel->elimina($id);
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
            $gara_id = $_POST['gara_id'] ?? null;
            $pilota_id = $_POST['pilota_id'] ?? null;

            if ($gara_id && $pilota_id) {
                $pilotiGaraModel = new PilotiGara();
                $pilotiGaraModel->crea($gara_id, $pilota_id);
                $_SESSION['success'] = "Pilota aggiunto al roster della gara.";
            } else {
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
        $pilotiGaraModel->elimina($id);
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
            $gara_id = $_POST['gara_id'] ?? null;
            $team_id = $_POST['team_id'] ?? null;
            $numero_gara = trim($_POST['numero_gara'] ?? '');

            if ($gara_id && $team_id && $numero_gara !== '') {
                $iscrittoModel = new IscrittoGara();
                
                // Validazione integrità
                $errore = $iscrittoModel->esisteGia($gara_id, $team_id, $numero_gara);
                
                if ($errore === 'team_esistente') {
                    $_SESSION['error'] = "Il team selezionato è già iscritto a questa gara.";
                } elseif ($errore === 'numero_esistente') {
                    $_SESSION['error'] = "Il numero di gara $numero_gara è già stato assegnato a un altro team.";
                } else {
                    $iscrittoModel->crea([
                        'gara_id' => $gara_id,
                        'team_id' => $team_id,
                        'numero_gara' => $numero_gara
                    ]);
                    $_SESSION['success'] = "Team iscritto con successo!";
                }
            } else {
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
        $iscrittoModel->elimina($id);

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
}
