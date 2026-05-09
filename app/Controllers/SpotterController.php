<?php
namespace App\Controllers;

use App\Models\Gara;
use App\Models\IscrittoGara;
use App\Models\FilePit;
use App\Models\KartGara;
use App\Models\MonitoraggioPit;

/**
 * Classe SpotterController
 * 
 * Gestisce l'interfaccia mobile per gli operatori in pit lane
 * per registrare i cambi kart degli avversari.
 */
class SpotterController {
    
    /**
     * Mostra l'interfaccia mobile dello spotter.
     */
    public function index($gara_id) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);

        if (!$gara) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }

        $iscrittoModel = new IscrittoGara();
        $iscritti = $iscrittoModel->ottieniPerGara($gara_id);
        usort($iscritti, function($a, $b) {
            return (int)$a['numero_gara'] <=> (int)$b['numero_gara'];
        });

        $filePitModel = new FilePit();
        $filePit = $filePitModel->ottieniPerGara($gara_id);

        $kartModel = new KartGara();
        $kartModel->inizializzaGaraSeNecessario($gara_id, $iscritti, $filePit);

        // Stato delle file
        $statoFile = [];
        foreach ($filePit as $fila) {
            $statoFile[] = [
                'fila' => $fila['nome_colore'],
                'colore_hex' => $fila['colore_hex'] ?? '#343a40',
                'kart' => $kartModel->ottieniKartInFila($gara_id, $fila['nome_colore'])
            ];
        }

        // Stato dei team (quale kart hanno)
        $statoTeam = [];
        foreach ($iscritti as $iscritto) {
            $statoTeam[] = [
                'iscritto' => $iscritto,
                'kart' => $kartModel->ottieniKartAttualeTeam($gara_id, $iscritto['id'])
            ];
        }

        $monitoraggioModel = new MonitoraggioPit();
        $ultimiCambi = $monitoraggioModel->ottieniUltimiCambi($gara_id, 10);
        $ultimoCambio = $monitoraggioModel->ottieniUltimoCambio($gara_id);
        $ultimoAnnullato = $monitoraggioModel->ottieniUltimoAnnullato($gara_id);

        require_once BASE_PATH . '/app/Views/spotter/index.php';
    }

    /**
     * Inizializza una fila vuota con un kart.
     */
    public function inizializzaFila($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fila_nome = $_POST['fila_nome'] ?? null;
            $numero_kart = $_POST['numero_kart'] ?? null;

            if ($fila_nome && $numero_kart) {
                $kartModel = new KartGara();
                $kart_id = $kartModel->trovaOCrea($gara_id, $numero_kart);
                $kartModel->impostaFila($kart_id, $fila_nome);
                $_SESSION['success'] = "Fila {$fila_nome} inizializzata con il kart {$numero_kart}.";
            } else {
                $_SESSION['error'] = "Numero kart mancante.";
            }
            header('Location: ' . BASE_URL . '/spotter/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Esegue lo scambio logico dei kart tra team e fila.
     */
    public function registraSostituzione($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $iscritto_gara_id = $_POST['iscritto_gara_id'] ?? null;
            $fila_nome = $_POST['fila_nome'] ?? null;

            if ($iscritto_gara_id && $fila_nome) {
                $kartModel = new KartGara();
                
                // 1. Trova il kart che il team sta lasciando (quello in pista col team)
                $kart_lasciato = $kartModel->ottieniKartAttualeTeam($gara_id, $iscritto_gara_id);
                
                if (!$kart_lasciato) {
                    $numero_kart_lasciato = $_POST['numero_kart_lasciato'] ?? null;
                    if ($numero_kart_lasciato) {
                        $kart_lasciato_id = $kartModel->trovaOCrea($gara_id, $numero_kart_lasciato);
                        $kart_lasciato = $kartModel->ottieniPerId($kart_lasciato_id);
                    }
                }

                // 2. Trova il kart fermo nella fila
                $kart_preso = $kartModel->ottieniKartInFila($gara_id, $fila_nome);

                if ($kart_lasciato && $kart_preso) {
                    // Esegue lo scambio sul DB
                    if ($kartModel->scambiaPosizioni($kart_lasciato['id'], $kart_preso['id'], $fila_nome)) {
                        // Registra log
                        $monitoraggioModel = new MonitoraggioPit();
                        $monitoraggioModel->registraCambio([
                            'gara_id' => $gara_id,
                            'iscritto_gara_id' => $iscritto_gara_id,
                            'kart_lasciato_id' => $kart_lasciato['id'],
                            'kart_preso_id' => $kart_preso['id'],
                            'fila_colore' => $fila_nome
                        ]);
                        $_SESSION['success'] = "Cambio in Fila {$fila_nome} effettuato con successo.";
                    } else {
                        $_SESSION['error'] = "Errore durante lo scambio nel DB.";
                    }
                } else {
                    $_SESSION['error'] = "Non è stato possibile identificare i kart (es. la fila potrebbe essere vuota).";
                }
            } else {
                $_SESSION['error'] = "Seleziona un Team prima di cliccare sulla Fila.";
            }
            
            header('Location: ' . BASE_URL . '/spotter/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Aggiorna rapidamente il rating di un kart.
     */
    public function cambiaRating($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kart_id = $_POST['kart_id'] ?? null;
            $rating = (int)($_POST['rating'] ?? 0);
            
            if ($kart_id) {
                $kartModel = new KartGara();
                $kartModel->aggiornaRating($kart_id, $rating);
                $_SESSION['success'] = "Rating aggiornato.";
            }
        }
        header('Location: ' . BASE_URL . '/spotter/index/' . $gara_id);
        exit;
    }

    /**
     * Annulla l'ultimo cambio registrato.
     */
    public function annullaUltimoCambio($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monitoraggioModel = new MonitoraggioPit();
            $ultimoCambio = $monitoraggioModel->ottieniUltimoCambio($gara_id);

            if ($ultimoCambio) {
                $kartModel = new KartGara();
                $successo = $kartModel->annullaScambio(
                    $ultimoCambio['kart_lasciato_id'], 
                    $ultimoCambio['kart_preso_id'], 
                    $ultimoCambio['fila_colore']
                );

                if ($successo) {
                    $monitoraggioModel->annullaCambioDato($ultimoCambio['id']);
                    $_SESSION['success'] = "Ultimo cambio annullato e ripristinato con successo.";
                } else {
                    $_SESSION['error'] = "Errore durante il ripristino dei kart nel database.";
                }
            } else {
                $_SESSION['error'] = "Nessun cambio da annullare.";
            }
            
            header('Location: ' . BASE_URL . '/spotter/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }

    /**
     * Ripristina l'ultimo cambio annullato (Redo).
     */
    public function ripetiUltimoAnnullato($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monitoraggioModel = new MonitoraggioPit();
            $ultimoAnnullato = $monitoraggioModel->ottieniUltimoAnnullato($gara_id);

            if ($ultimoAnnullato) {
                $kartModel = new KartGara();
                // Rifacciamo lo scambio originale
                $successo = $kartModel->scambiaPosizioni(
                    $ultimoAnnullato['kart_lasciato_id'], 
                    $ultimoAnnullato['kart_preso_id'], 
                    $ultimoAnnullato['fila_colore']
                );

                if ($successo) {
                    $monitoraggioModel->ripristinaStatoCambio($ultimoAnnullato['id']);
                    $_SESSION['success'] = "Cambio ripristinato con successo.";
                } else {
                    $_SESSION['error'] = "Errore durante il ripristino del cambio.";
                }
            } else {
                $_SESSION['error'] = "Nessun cambio da ripristinare.";
            }
            
            header('Location: ' . BASE_URL . '/spotter/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }


    /**
     * Resetta tutti i kart a Ignoto.
     */
    public function resetRatingGara($gara_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kartModel = new KartGara();
            $kartModel->resetRatingGara($gara_id);
            $_SESSION['success'] = "Tutti i rating sono stati resettati a 'Ignoto'.";
            
            header('Location: ' . BASE_URL . '/spotter/index/' . $gara_id);
            exit;
        }
        header('Location: ' . BASE_URL . '/home/index');
        exit;
    }
}
