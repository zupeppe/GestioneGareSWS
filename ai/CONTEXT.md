# Contesto del Progetto: Gestione Gare SWS (Endurance)

## Scopo
Sviluppare una piattaforma web dedicata alla gestione in tempo reale delle strategie per gare di Go-Kart Endurance (SWS). Il sistema collega chi è in pista (Spotter) con chi decide la strategia (Muretto), eliminando i calcoli manuali e fornendo previsioni sui pit stop, sui cambi kart e sui "Jolly" strategici a disposizione.

## Tech Stack
- **Backend:** PHP 8+ (Pattern MVC Custom).
- **Database:** MySQL / MariaDB (Interazione tramite PDO con Prepared Statements).
- **Frontend:** HTML5, CSS3, Javascript Vanilla (Fetch API/Ajax per aggiornamenti asincroni).
- **Librerie esterne:** Nessuna. Architettura Zero-Dependency.

## Filosofia
1. **Mobile-First per la pista:** Il modulo 'Spotter' deve avere pulsanti giganti, testi leggibili sotto il sole e un flusso anti-errore.
2. **Matematica Infallibile per il Muretto:** La timeline degli stint è gestita "a cascata" calcolando i minuti a ritroso. Se si corregge un tempo nel passato, tutto il futuro si ricalcola automaticamente.
3. **Leggerezza e Velocità:** Niente framework pesanti. Il codice deve essere didatticamente pulito, commentato in italiano (Javadoc) e velocissimo.

## Stato Attuale
Il progetto ha completato la configurazione del database, l'anagrafica, la gestione avanzata degli stint (Sprint 3) e il modulo Spotter (Sprint 4). È in corso l'integrazione Real-Time (Sprint 5) e la fase di rifinitura UX/Bugfix.

## Cosa NON fare (Regole Ferree per AI)
- **NON** introdurre framework PHP (es. Laravel, Symfony) o ORM esterni (es. Doctrine).
- **NON** usare librerie JS frontend pesanti (React, Vue, jQuery) se non strettamente necessario e concordato.
- **NON** modificare la logica del "Motore del Tempo" in `StintMioTeam->ricalcolaTimeline` senza un ordine esplicito: è il cuore nevralgico del calcolo.
- **NON** omettere i blocchi `try/catch` e le transazioni PDO quando si manipolano più tabelle.