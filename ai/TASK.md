# Task Attuali e Futuri

# Task Attuali e Futuri

# Task Sprint 6: Sicurezza e Autenticazione

- [ ] **Database:** Creare tabella `utenti` (id, username, password_hash, ruolo). Ruoli: `admin`, `team_manager`, `muretto`, `spotter`.
- [ ] **Auth:** Login/Logout con `password_verify`. Admin di default: `admin` / `123qweASD`.
- [ ] **Gestione Utenti:** Creare una vista accessibile SOLO dall'Admin per creare utenti e resettare password.
- [ ] **Controllo Ruoli (RBAC):**
    - `admin`: Accesso totale (inclusa gestione utenti).
    - `team_manager`: Accesso a tutto il software (Gare, Muretti, Spotter, Anagrafiche) TRANNE gestione utenti/password.
    - `muretto`: Accesso a Muretti, Spotter e Home. Bloccato su Setup Gara e Anagrafiche.
    - `spotter`: Accesso ESCLUSIVO alla pagina Spotter e Home.
    
## Sprint 7: Test e Deploy (Futuro)
- [ ] Simulazione gara completa multi-dispositivo.
- [ ] Preparazione ambiente di produzione.