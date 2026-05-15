<?php
require_once 'config/Database.php';

// Simula una chiamata API per aggiornare i parametri
$gara_id = 1; // ID della prima gara
$dati = [
    'nome_gara' => 'Test Update',
    'data_evento' => '2026-06-06 12:00:00',
    'durata_minuti' => 780,
    'min_stint' => 18,
    'tempo_minimo_pit' => 2,
    'durata_max_stint' => 60,
    'durata_min_stint' => 3,
    'tempo_max_pilota' => 120, // Test: 2 ore
    'tempo_min_pilota' => 60,  // Test: 1 ora
    'mio_team_id' => 2
];

// Simula il metodo del controller
require_once 'app/Models/Gara.php';
$garaModel = new App\Models\Gara();

echo "Test aggiornamento gara ID: $gara_id\n";
echo "Dati da aggiornare:\n";
print_r($dati);

try {
    $result = $garaModel->aggiorna($gara_id, $dati);
    echo "\nRisultato aggiornamento: " . ($result ? "SUCCESSO" : "FALLITO") . "\n";
    
    // Verifica lettura
    $garaAggiornata = $garaModel->ottieniPerId($gara_id);
    echo "\nDati dopo aggiornamento:\n";
    echo "tempo_max_pilota: " . $garaAggiornata['tempo_max_pilota'] . "\n";
    echo "tempo_min_pilota: " . $garaAggiornata['tempo_min_pilota'] . "\n";
    
} catch (Exception $e) {
    echo "\nERRORE: " . $e->getMessage() . "\n";
}
?>
