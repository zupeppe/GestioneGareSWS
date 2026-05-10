<?php
require_once 'config/Database.php';

try {
    $db = Database::getIstanza()->getConnessione();
    
    // Controlla se i nuovi campi esistono
    $stmt = $db->query("DESCRIBE gare");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Struttura tabella gare:\n";
    echo "========================\n";
    
    $has_tempo_max_pilota = false;
    $has_tempo_min_pilota = false;
    
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        
        if ($col['Field'] === 'tempo_max_pilota') $has_tempo_max_pilota = true;
        if ($col['Field'] === 'tempo_min_pilota') $has_tempo_min_pilota = true;
    }
    
    echo "\nVerifica campi nuovi:\n";
    echo "===================\n";
    echo "tempo_max_pilota: " . ($has_tempo_max_pilota ? "ESISTE" : "MANCANTE") . "\n";
    echo "tempo_min_pilota: " . ($has_tempo_min_pilota ? "ESISTE" : "MANCANTE") . "\n";
    
    if (!$has_tempo_max_pilota || !$has_tempo_min_pilota) {
        echo "\nERRORE: I campi non sono stati aggiunti al database!\n";
        echo "Esegui questo SQL per aggiungerli:\n\n";
        echo "ALTER TABLE gare ADD COLUMN tempo_max_pilota INT DEFAULT 0 COMMENT 'Tempo massimo di guida per pilota (minuti)';\n";
        echo "ALTER TABLE gare ADD COLUMN tempo_min_pilota INT DEFAULT 0 COMMENT 'Tempo minimo di guida per pilota (minuti)';\n";
    }
    
} catch (Exception $e) {
    echo "ERRORE: " . $e->getMessage() . "\n";
}
?>
