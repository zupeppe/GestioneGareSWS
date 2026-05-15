<?php
require_once 'config/Database.php';

$db = Database::getIstanza()->getConnessione();

try {
    $sql = 'ALTER TABLE stint_mio_team ADD COLUMN cancellato TINYINT(1) DEFAULT 0 NOT NULL COMMENT "0=attivo, 1=cancellato soft"';
    $db->exec($sql);
    echo "✅ Campo cancellato aggiunto alla tabella stint_mio_team\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✅ Campo cancellato già esistente\n";
    } else {
        echo "❌ Errore: " . $e->getMessage() . "\n";
    }
}
?>
