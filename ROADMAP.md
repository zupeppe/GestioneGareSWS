# Roadmap e Stato Progetto

## [COMPLETATO] Sprint 1: Fondamenta e Database
- [x] Creazione alberatura cartelle MVC.
- [x] Setup Front Controller (`index.php`) e `.htaccess`.
- [x] Configurazione connessione PDO (`Database.php`).
- [x] Creazione script SQL per le tabelle.
- [x] Model `Gara.php` e `HomeController.php` di base.

## [COMPLETATO] Sprint 2: Anagrafica e Setup Gara
- [x] CRUD per `piloti_mio_team` e `teams`.
- [x] Interfaccia per iscrivere i team e assegnare i numeri di gara.
- [x] Evoluzione Setup: Aggiunta gestione `durata_minuti`, roster piloti (`piloti_gara`) e corsie dei box (`file_pit_gara`).

## [COMPLETATO] Sprint 3: Gestione Stint (Backend)
- [x] Logica avvio/fine timer per pilota.
- [x] Salvataggio in `stint_mio_team`.

## [IN CORSO] Sprint 4: Modulo Spotter Pit (Mobile)
- [ ] Interfaccia per inserimento rapido cambio kart.
- [ ] Salvataggio log in `monitoraggio_pit`.
- [ ] Aggiornamento stato del kart in `kart_gara`.

## [DA FARE] Sprint 5: Dashboard Muretto (Desktop)
- [ ] Chiamate Ajax (Polling) per aggiornamento real-time.
- [ ] Integrazione dati timer stint + storico kart presi.