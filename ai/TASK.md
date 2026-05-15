# Task Attuali e Futuri

# Task Attuali e Futuri

## Sprint 5.3: Multi-Team e Home Redesign (ATTUALE)
- [ ] **Database:** Aggiungere colonna `is_gestito` (BOOLEAN) alla tabella `iscritti_gara` per marcare quali team sono della nostra scuderia.
- [ ] **Setup Gara:** Permettere di selezionare fino a 4 team come "gestiti" tramite checkbox o selezione multipla.
- [ ] **Muretto Multi-Team:** - Creare vista `muretto/multi.php` con layout a colonne (una colonna per team).
    - Permettere di gestire stint/cambi separatamente per ogni team nella stessa pagina.
- [ ] **Home Page Redesign:** - Layout schematico con "Nuova Gara", "Gare in Corso" e "Anagrafiche".
    - Link grafici chiari per Gestione Piloti e Gestione Team.

## Sprint 6: Sicurezza e Autenticazione (ATTUALE)
- [ ] Creare tabella `utenti` nel database (campi: id, username, password_hash, ruolo).
- [ ] Implementare LoginController e vista di login.
- [ ] Creare middleware/filtro per proteggere le rotte in base al ruolo (Admin, Muretto, Spotter).

## Sprint 7: Test e Deploy (Futuro)
- [ ] Simulazione gara completa multi-dispositivo.
- [ ] Preparazione ambiente di produzione.