# Linee Guida di Sicurezza

## Stato Attuale
Il sistema attualmente non ha un layer di autenticazione. Le query SQL sono messe in sicurezza tramite `Prepared Statements` (PDO) per prevenire SQL Injection.

## Roadmap Sicurezza (Sprint 6)
1. **Autenticazione:**
   - Hash delle password obbligatorio tramite `password_hash($pwd, PASSWORD_DEFAULT)`.
   - Controllo credenziali tramite `password_verify()`.
2. **Autorizzazione (RBAC - Role Based Access Control):**
   - **Admin:** Accesso a CRUD, Setup Gara.
   - **Strategist:** Accesso in lettura/scrittura solo a `/muretto`.
   - **Spotter:** Accesso in lettura/scrittura solo a `/spotter`.
3. **Protezione CSRF:**
   - Ogni form POST dovrà includere un `<input type="hidden" name="csrf_token" value="...">` validato lato server.
4. **Sanitizzazione:**
   - Qualsiasi output stampato nelle viste PHP dovrà passare per `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` per prevenire attacchi XSS.