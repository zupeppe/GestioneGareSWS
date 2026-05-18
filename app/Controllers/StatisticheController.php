<?php
namespace App\Controllers;

require_once dirname(__DIR__, 2) . '/config/Database.php';

use App\Models\Gara;
use Database;
use PDO;

class StatisticheController {
    private $db;

    public function __construct() {
        $isLoggedIn = isset($_SESSION['utente']) && !empty($_SESSION['utente']);
        $ruolo = $isLoggedIn ? $_SESSION['utente']['ruolo'] : null;
        
        if (!$isLoggedIn || !in_array($ruolo, ['admin', 'team_manager'])) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
        
        $this->db = Database::getIstanza()->getConnessione();
    }

    public function index($gara_id) {
        $garaModel = new Gara();
        $gara = $garaModel->ottieniPerId($gara_id);
        
        if (!$gara) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        // Fetch monitoraggio_pit (solo attivi) ASC
        $sql = "SELECT m.*, ig.numero_gara, t.nome_team, kl.numero_kart AS num_kart_lasciato, kp.numero_kart AS num_kart_preso
                FROM monitoraggio_pit m
                JOIN iscritti_gara ig ON m.iscritto_gara_id = ig.id
                JOIN teams t ON ig.team_id = t.id
                JOIN kart_gara kl ON m.kart_lasciato_id = kl.id
                JOIN kart_gara kp ON m.kart_preso_id = kp.id
                WHERE m.gara_id = :gara_id AND m.stato = 'attivo'
                ORDER BY m.timestamp ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':gara_id' => $gara_id]);
        $cambi = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all kart_rating_history for this gara
        $sqlStorico = "SELECT krh.* FROM kart_rating_history krh
                       JOIN kart_gara kg ON krh.kart_id = kg.id
                       WHERE kg.gara_id = :gara_id
                       ORDER BY krh.data_cambio DESC";
        $stmtStorico = $this->db->prepare($sqlStorico);
        $stmtStorico->execute([':gara_id' => $gara_id]);
        $storicoRating = $stmtStorico->fetchAll(PDO::FETCH_ASSOC);

        // Fetch current karts to get their default/current rating
        $sqlKarts = "SELECT id, numero_kart, rating FROM kart_gara WHERE gara_id = :gara_id";
        $stmtKarts = $this->db->prepare($sqlKarts);
        $stmtKarts->execute([':gara_id' => $gara_id]);
        $karts = $stmtKarts->fetchAll(PDO::FETCH_ASSOC);
        $kartMap = [];
        foreach ($karts as $k) {
            $kartMap[$k['id']] = $k;
        }

        // Helper per trovare rating
        $getRatingForStint = function($kart_id, $endTime) use ($storicoRating) {
            foreach ($storicoRating as $r) {
                if ($r['kart_id'] == $kart_id && strtotime($r['data_cambio']) <= strtotime($endTime)) {
                    return $r['rating'];
                }
            }
            return 0; // Se non c'è storico prima della fine dello stint, il kart era nello stato iniziale (Ignoto = 0)
        };

        // Algoritmo calcolo stint
        $statoTeam = [];
        $stints = [];

        foreach ($cambi as $cambio) {
            $team_id = $cambio['iscritto_gara_id'];
            $oraCambio = $cambio['timestamp'];
            
            $inizioStint = $gara['data_evento'];
            if (isset($statoTeam[$team_id])) {
                 $inizioStint = $statoTeam[$team_id]['tempo_inizio'];
            }

            // Chiude lo stint del kart lasciato
            $stints[] = [
                'team_nome' => "N° " . $cambio['numero_gara'] . " - " . $cambio['nome_team'],
                'kart_id' => $cambio['kart_lasciato_id'],
                'numero_kart' => $cambio['num_kart_lasciato'],
                'inizio' => $inizioStint,
                'fine' => $oraCambio,
                'rating' => $getRatingForStint($cambio['kart_lasciato_id'], $oraCambio)
            ];

            // Inizia un nuovo stint per il kart preso
            $statoTeam[$team_id] = [
                'tempo_inizio' => $oraCambio,
                'kart_id' => $cambio['kart_preso_id'],
                'numero_kart' => $cambio['num_kart_preso']
            ];
        }

        // Gli stint attuali (non ancora chiusi)
        $now = date('Y-m-d H:i:s');
        foreach ($statoTeam as $team_id => $stato) {
            $team_nome = "Unknown";
            foreach($cambi as $c) {
                if ($c['iscritto_gara_id'] == $team_id) {
                    $team_nome = "N° " . $c['numero_gara'] . " - " . $c['nome_team'];
                    break;
                }
            }
            
            $stints[] = [
                'team_nome' => $team_nome,
                'kart_id' => $stato['kart_id'],
                'numero_kart' => $stato['numero_kart'],
                'inizio' => $stato['tempo_inizio'],
                'fine' => 'In Corso',
                'rating' => $getRatingForStint($stato['kart_id'], $now)
            ];
        }

        // Raggruppa per Team
        $stintsByTeam = [];
        foreach ($stints as $s) {
            $stintsByTeam[$s['team_nome']][] = $s;
        }

        // Raggruppa per Kart
        $stintsByKart = [];
        foreach ($stints as $s) {
            $stintsByKart[$s['numero_kart']][] = $s;
        }

        // Sort by numero_kart numeric
        uksort($stintsByKart, function($a, $b) {
            return (int)$a <=> (int)$b;
        });

        require_once BASE_PATH . '/app/Views/statistiche/index.php';
    }
}
