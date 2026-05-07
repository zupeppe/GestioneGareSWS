<?php
namespace App\Core;

/**
 * Helper per la gestione delle conversioni di tempo.
 */
class TimeHelper {
    
    /**
     * Converte una stringa nel formato HH:MM in minuti totali (intero).
     * 
     * @param string|null $hhmm Il tempo nel formato HH:MM
     * @return int I minuti totali calcolati, o 0 se input nullo o invalido.
     */
    public static function daHHMMaMinuti($hhmm) {
        if (empty($hhmm)) {
            return 0;
        }

        $parti = explode(':', $hhmm);
        if (count($parti) !== 2) {
            return 0;
        }

        $ore = (int)$parti[0];
        $minuti = (int)$parti[1];

        return ($ore * 60) + $minuti;
    }

    /**
     * Converte i minuti totali (intero) in una stringa nel formato HH:MM.
     * 
     * @param int|null $minuti I minuti totali
     * @return string Il tempo nel formato HH:MM, o vuoto se null.
     */
    public static function daMinutiaHHMM($minuti) {
        if ($minuti === null || $minuti === '') {
            return '';
        }

        $minuti = (int)$minuti;
        if ($minuti < 0) {
            $minuti = 0;
        }

        $ore = floor($minuti / 60);
        $minRimanenti = $minuti % 60;

        return sprintf('%02d:%02d', $ore, $minRimanenti);
    }
}
