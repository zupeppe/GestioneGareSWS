# Architettura del Sistema

Il progetto usa un pattern **MVC (Model-View-Controller)** personalizzato con un singolo punto di ingresso (Front Controller).

## Struttura Cartelle
- `/app/Controllers`: Gestiscono l'input HTTP, chiamano i Model e passano i dati alle Viste.
- `/app/Models`: Gestiscono la logica di business e l'interazione con il database (PDO).
- `/app/Views`: File HTML/PHP per la UI. La logica complessa qui è proibita.
- `/app/Core`: File di base dell'architettura (es. Router, `TimeHelper.php`).
- `/config`: Configurazione del database (`Database.php`).
- `/public`: Document Root del server web. Contiene `index.php`, CSS, JS e assets. Tutte le richieste passano da qui tramite `.htaccess`.

## Moduli Principali e Flussi
1. **Anagrafica (Piloti & Team):** Moduli base (CRUD) per popolare il database.
2. **Setup Gara:** Assegna team a una gara, definisce durata, roster piloti e corsie dei box (`file_pit_gara`).
3. **Muretto (Strategia):** Basato su `StintMioTeam`. Calcola i tempi in formato HH:MM, ricalcola i tempi a cascata tenendo conto dei tempi di pit stop (`tempo_minimo_pit`). Calcola i "Jolly" residui.
4. **Spotter (Pit Lane):** Gestisce lo scambio dei kart (`KartGara`) nelle file dei box. Usa transazioni PDO per prevenire sdoppiamenti e supporta lo storico delle azioni in `MonitoraggioPit` (Soft Delete per Undo/Redo).