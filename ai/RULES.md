# Regole di Sviluppo - SWS Endurance Manager

## Architettura e Stack Tecnologico
- **Pattern:** MVC (Model-View-Controller) rigoroso.
- **Backend:** PHP puro (OOP). NON utilizzare framework esterni.
- **Struttura File Rigorosa:** Massima separazione delle responsabilità per garantire leggibilità:
  - **CSS:** Tutti i fogli di stile devono risiedere esclusivamente nella cartella `public/css/`.
  - **JS:** Tutti gli script client-side e le chiamate Ajax devono risiedere esclusivamente nella cartella `public/js/`.
  - **PHP (Logica):** Tutta la logica di business e di accesso ai dati deve essere divisa in funzioni e metodi nelle cartelle `app/Controllers/` e `app/Models/`.
  - **Viste (HTML):** I file all'interno di `app/Views/` devono essere estremamente snelli e facili da leggere. Devono contenere un markup HTML pulito, limitando l'uso di PHP al solo output delle variabili (es. `echo`) o a cicli essenziali. La logica complessa è severamente vietata nelle Viste.
- **Database:** MySQL. Utilizzare esclusivamente `PDO` per le connessioni e i prepared statements (sicurezza anti SQL-Injection).
- **Ambiente:** Sviluppo su XAMPP, produzione su hosting condiviso (Apache, gestione tramite `.htaccess`).

## Stile del Codice
- **Modularità:** Organizzare il codice privilegiando l'uso di funzioni e metodi brevi, chiari e con una singola responsabilità.
- **Lingua:** Nomi di file, variabili, classi, metodi e commenti devono essere in **Italiano**.
- **Documentazione (Stile Javadoc):** Ogni funzione, metodo e classe deve essere obbligatoriamente preceduta da un blocco di commento in stile Javadoc. Il commento deve spiegare chiaramente lo scopo, documentare i parametri (`@param`) e il valore di ritorno (`@return`) in italiano.
- **Commenti In-line:** Aggiungere commenti all'interno del codice solo per spiegare i passaggi algoritmici salienti o la logica di business non immediatamente ovvia.
- **Sicurezza:** Validare sempre gli input utente e sanitizzare gli output.
- **Frontend Mobile-First:** Le viste dedicate al "Pit" devono avere bottoni molto grandi e interfacce essenziali, adatte all'uso rapido e sotto stress da smartphone.