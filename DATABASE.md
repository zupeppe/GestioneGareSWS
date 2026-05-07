# Schema Database (InnoDB)

- `gare`: id, nome_gara, data_evento, durata_minuti, stato (setup, in_corso, finita)
- `piloti_mio_team`: id, nome, cognome
- `teams`: id, nome_team
- `iscritti_gara`: id, gara_id, team_id, numero_gara
- `kart_gara`: id, gara_id, numero_kart, rating (0: ignoto, 1: scarso, 2: medio, 3: buono), ultima_fila, note
- `stint_mio_team`: id, gara_id, pilota_id, kart_id, minuto_ingresso, durata_minuti, note
- `monitoraggio_pit`: id, gara_id, iscritto_gara_id, kart_lasciato_id, kart_preso_id, fila_colore, timestamp
- `file_pit_gara`: id, gara_id, nome_colore, ordine
- `piloti_gara`: id, gara_id, pilota_id