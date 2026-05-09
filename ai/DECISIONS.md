# Architectural Decision Record (ADR)

- **Calcolo del Tempo (A ritroso vs Assoluto):** Si è deciso di basare la matematica sugli Stint sui *minuti di gara a ritroso* invece del timestamp assoluto (DATETIME). *Motivo:* Lingua standard delle gare endurance.
- **Ricalcolo a Cascata della Timeline:** Modificare la durata di un vecchio stint sposta in avanti o indietro l'ingresso di tutti gli stint successivi. *Motivo:* Rende il sistema auto-riparante e previene errori matematici al muretto in caso di digitazione errata.
- **Undo nello Spotter tramite Soft Delete:** Se un'assegnazione kart viene annullata, il record in `monitoraggio_pit` non viene cancellato, ma il campo `stato` diventa `annullato`. *Motivo:* Permette il "Redo" e mantiene uno storico intatto di ciò che è accaduto in corsia box.
- **Zero-Dependency (Niente Framework):** *Motivo:* Massima compatibilità di hosting, zero tempi di build, facilità di debug didattico e controllo totale sulle query SQL.