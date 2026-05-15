<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Gara - SWS Endurance Manager</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
    <style>
        .form-section { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-section h2 { margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { padding: 8px; width: 100%; max-width: 300px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .nav-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #0056b3; }
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid-container { grid-template-columns: 1fr; } }
        .autosave-status { font-size: 0.85em; color: #6c757d; margin-top: 8px; min-height: 18px; }
        .autosave-status.success { color: #198754; }
        .autosave-status.error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container" id="setup-gara-root" data-gara-id="<?php echo (int)$gara['id']; ?>" data-base-url="<?php echo BASE_URL; ?>">
        <a href="<?php echo BASE_URL; ?>/home/index" class="nav-link">&larr; Torna alla Home</a>
        
        <h1>Setup Gara: <?php echo htmlspecialchars($gara['nome_gara']); ?></h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if ($haStintAttivi): ?>
            <div style="background: #fff3cd; border: 2px solid #ffeaa7; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <h3 style="color: #856404; margin: 0 0 10px 0;">⚠️ ATTENZIONE: Gara in Corso</h3>
                <p style="color: #856404; margin: 0 0 15px 0;">
                    Ci sono stint attivi in questa gara. Per motivi di sicurezza, le modifiche al setup sono bloccate per evitare situazioni spiacevoli.
                </p>
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                    <p style="color: #721c24; margin: 0; font-weight: bold;">
                        🛡️ Protezione Attiva: Non è possibile modificare il roster piloti, i team o altri parametri mentre la gara è in corso.
                    </p>
                </div>
                <script>
                // Funzione per sbloccare con conferma
                function sbloccaSetup() {
                    if (confirm('⚠️ CONFERMA CRITICA\n\nStai per sbloccare il setup della gara con stint attivi.\n\nQuesta operazione è RISCHIOSA e può causare:\n• Corruzione dei dati della gara\n• Perdita di informazioni sugli stint\n• Problemi nella timeline\n\nSei ASSOLUTAMENTE sicuro di voler continuare?\n\nConsigliato: NO - Termina prima gli stint attivi.')) {
                        if (confirm('🚨 ULTIMO AVVISO\n\nHai scelto di continuare nonostante i rischi.\n\nQuesta azione potrebbe rendere la gara instabile.\n\nVuoi davvero procedere?')) {
                            // Rimuovi il blocco
                            const bloccoDiv = document.querySelector('.setup-bloccato');
                            if (bloccoDiv) {
                                bloccoDiv.style.display = 'none';
                            }
                            // Abilita tutti i form
                            const forms = document.querySelectorAll('form');
                            forms.forEach(form => {
                                const inputs = form.querySelectorAll('input, select, button, textarea');
                                inputs.forEach(input => {
                                    input.disabled = false;
                                    input.style.opacity = '1';
                                });
                            });
                            // Rimuovi l'avviso
                            const avviso = document.querySelector('.avviso-gara-corso');
                            if (avviso) {
                                avviso.style.display = 'none';
                            }
                        }
                    }
                }
                </script>
                <button type="button" onclick="sbloccaSetup()" style="background: #dc3545; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    🚨 Sblocca Setup (Rischioso)
                </button>
            </div>
        <?php endif; ?>

        <div class="grid-container <?php echo $haStintAttivi ? 'setup-bloccato' : ''; ?>">
            <!-- SEZIONE 1: Parametri Gara -->
            <div class="form-section avviso-gara-corso" style="<?php echo $haStintAttivi ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
                <h2>1. Parametri Gara</h2>
                <form action="<?php echo BASE_URL; ?>/gare/aggiornaParametri" method="POST" id="form-parametri-gara">
                    <input type="hidden" name="gara_id" value="<?php echo $gara['id']; ?>">
                    <div class="form-group">
                        <label for="nome_gara">Nome Gara:</label>
                        <input type="text" id="nome_gara" name="nome_gara" value="<?php echo htmlspecialchars($gara['nome_gara']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="data_evento">Data Evento:</label>
                        <input type="datetime-local" id="data_evento" name="data_evento" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($gara['data_evento']))); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="durata_minuti">Durata (Minuti):</label>
                        <input type="number" id="durata_minuti" name="durata_minuti" value="<?php echo htmlspecialchars($gara['durata_minuti']); ?>" min="0" required>
                    </div>
                    
                    <hr style="margin: 20px 0;">
                    <h3 style="margin-top:0;">Regolamento Sportivo</h3>
                    <div class="form-group">
                        <label for="min_stint">Pit Stop Minimi Obbligatori:</label>
                        <input type="number" id="min_stint" name="min_stint" value="<?php echo htmlspecialchars($gara['min_stint'] ?? 0); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="tempo_minimo_pit">Tempo Minimo Pit (minuti fermi ai box):</label>
                        <input type="number" id="tempo_minimo_pit" name="tempo_minimo_pit" value="<?php echo htmlspecialchars($gara['tempo_minimo_pit'] ?? 0); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="durata_max_stint">Durata Max Stint (minuti guida):</label>
                        <input type="number" id="durata_max_stint" name="durata_max_stint" value="<?php echo htmlspecialchars($gara['durata_max_stint'] ?? 0); ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="durata_min_stint">Durata Min Stint (minuti) [Opzionale]:</label>
                        <input type="number" id="durata_min_stint" name="durata_min_stint" value="<?php echo htmlspecialchars($gara['durata_min_stint'] ?? ''); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="tempo_max_pilota">Tempo Max per Pilota (minuti totali):</label>
                        <input type="number" id="tempo_max_pilota" name="tempo_max_pilota" value="<?php echo htmlspecialchars($gara['tempo_max_pilota'] ?? 0); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="tempo_min_pilota">Tempo Min per Pilota (minuti totali):</label>
                        <input type="number" id="tempo_min_pilota" name="tempo_min_pilota" value="<?php echo htmlspecialchars($gara['tempo_min_pilota'] ?? 0); ?>" min="0">
                    </div>
                    
                    <button type="button" class="btn" style="background:#0056b3;" id="btn-salva-parametri-manuale">Salvataggio Automatico Attivo</button>
                    <div class="autosave-status" id="autosave-status">Modifica un campo per salvare automaticamente.</div>
                </form>
            </div>

            <!-- SEZIONE 2: Roster Piloti Team -->
            <div class="form-section avviso-gara-corso" style="<?php echo $haStintAttivi ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
                <h2>2. Roster Piloti Team</h2>
                <form action="<?php echo BASE_URL; ?>/gare/aggiungiPilotaGara" method="POST" id="form-aggiungi-pilota">
                    <input type="hidden" name="gara_id" value="<?php echo $gara['id']; ?>">
                    <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                        <div style="flex-grow: 1;">
                            <label for="pilota_id">Aggiungi Pilota al Roster:</label>
                            <select id="pilota_id" name="pilota_id" required>
                                <option value="">-- Seleziona Pilota --</option>
                                <?php foreach ($pilotiDisponibili as $pilota): ?>
                                    <option value="<?php echo $pilota['id']; ?>">
                                        <?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="autosave-status" id="autosave-piloti-status"></div>
                        </div>
                        <div>
                            <label for="team_id">Team:</label>
                            <select id="team_id" name="team_id" required>
                                <option value="">-- Seleziona Team --</option>
                                <?php foreach ($teamGestiti as $team): ?>
                                    <option value="<?php echo $team['team_id']; ?>">
                                        <?php echo htmlspecialchars($team['nome_team']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="autosave-status" id="autosave-team-status"></div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" onclick="openModal('modal-nuovo-pilota')" style="background:#6c757d; height: 35px; line-height: 15px;">+ Nuovo</button>
                        </div>
                    </div>
                    <button type="button" class="btn" id="btn-aggiungi-roster-pilota">Aggiungi pilota</button>
                </form>

                <!-- Visualizzazione Piloti per Team -->
                <div id="roster-per-team" style="margin-top: 20px;">
                    <?php include BASE_PATH . '/app/Views/gare/_roster_team.php'; ?>
                </div>
            </div>

            <!-- SEZIONE 3: Configurazione Box -->
            <div class="form-section avviso-gara-corso" style="<?php echo $haStintAttivi ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
                <h2>3. Configurazione Box</h2>
                <form action="<?php echo BASE_URL; ?>/gare/aggiungiFilaPit" method="POST" id="form-aggiungi-fila-pit">
                    <input type="hidden" name="gara_id" value="<?php echo $gara['id']; ?>">
                    <div class="form-group" style="display:flex; gap:10px; align-items:flex-end;">
                        <div style="flex:1;">
                            <label for="nome_colore">Nome Fila:</label>
                            <input type="text" id="nome_colore" name="nome_colore" placeholder="Es. Rossa, Blu..." required>
                        </div>
                        <div>
                            <label for="colore_hex">Colore:</label>
                            <input type="color" id="colore_hex" name="colore_hex" value="#343a40" style="padding:0; height:35px; width:50px; border:1px solid #ccc; border-radius:4px; cursor:pointer;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ordine">Ordine (Opzionale):</label>
                        <input type="number" id="ordine" name="ordine" value="0">
                    </div>
                    <button type="button" class="btn" id="btn-aggiungi-fila-pit">Aggiungi fila</button>
                </form>

                <table style="margin-top: 10px; <?php echo empty($filePit) ? 'display:none;' : ''; ?>" id="tabella-file-pit">
                    <thead><tr><th>Fila</th><th>Ordine</th><th>Azioni</th></tr></thead>
                    <tbody id="tbody-file-pit">
                        <?php foreach ($filePit as $fp): ?>
                            <tr id="fila-row-<?php echo (int)$fp['id']; ?>" data-fila-id="<?php echo (int)$fp['id']; ?>">
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span class="js-fila-anteprima-colore" style="display:inline-block; width:15px; height:15px; background:<?php echo htmlspecialchars($fp['colore_hex']); ?>; border-radius:50%; vertical-align:middle; border:1px solid #333;"></span>
                                        <input type="text" class="js-fila-nome-input" value="<?php echo htmlspecialchars($fp['nome_colore']); ?>" maxlength="120" style="padding:6px 8px; flex:1; min-width:100px; max-width:220px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                                        <input type="color" class="js-fila-colore-input" value="<?php echo htmlspecialchars($fp['colore_hex']); ?>" title="Colore fila" aria-label="Colore fila" style="padding:0; height:32px; width:44px; border:1px solid #ccc; border-radius:4px; cursor:pointer;">
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($fp['ordine']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/gare/rimuoviFilaPit/<?php echo $fp['id']; ?>/<?php echo $gara['id']; ?>" class="btn btn-danger js-rimuovi-fila" style="text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Rimuovi</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="font-size: 0.9em; margin-top: 10px; <?php echo !empty($filePit) ? 'display:none;' : ''; ?>" id="empty-file-pit">Nessuna fila configurata.</p>
            </div>

            <!-- SEZIONE 4: Iscrizione Team (Avversari) -->
            <div class="form-section avviso-gara-corso" style="<?php echo $haStintAttivi ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
                <h2>4. Iscrizione Team alla Gara</h2>
                <form action="<?php echo BASE_URL; ?>/gare/iscriviTeam" method="POST" id="form-iscrivi-team">
                    <input type="hidden" name="gara_id" value="<?php echo $gara['id']; ?>">
                    
                    <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                        <div style="flex-grow: 1;">
                            <label for="team_id">Team:</label>
                            <select id="team_id" name="team_id" required>
                                <option value="">-- Seleziona un Team --</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team['id']; ?>">
                                        <?php echo htmlspecialchars($team['nome_team']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" onclick="openModal('modal-nuovo-team')" style="background:#6c757d; height: 35px; line-height: 15px;">+ Nuovo</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="numero_gara">Numero di Gara (Kart/Team):</label>
                        <input type="number" id="numero_gara" name="numero_gara" required>
                    </div>
                    
                    <button type="button" class="btn" id="btn-aggiungi-iscrizione-team">Aggiungi iscrizione</button>
                </form>
            </div>
        </div>

        <hr>

        <h2>Riepilogo Team Iscritti</h2>
        <table id="tabella-iscritti" style="<?php echo empty($iscritti) ? 'display:none;' : ''; ?>">
            <thead>
                <tr>
                    <th>Numero Gara</th>
                    <th>Nome Team</th>
                    <th>Gestito</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody id="tbody-iscritti">
                <?php foreach ($iscritti as $iscritto): ?>
                    <tr id="iscrizione-row-<?php echo (int)$iscritto['id']; ?>" data-iscrizione-id="<?php echo (int)$iscritto['id']; ?>" data-team-id="<?php echo (int)$iscritto['team_id']; ?>" data-nome-team="<?php echo htmlspecialchars($iscritto['nome_team'], ENT_QUOTES, 'UTF-8'); ?>" data-numero-gara="<?php echo htmlspecialchars($iscritto['numero_gara'], ENT_QUOTES, 'UTF-8'); ?>">
                        <td><?php echo htmlspecialchars($iscritto['numero_gara']); ?></td>
                        <td><?php echo htmlspecialchars($iscritto['nome_team']); ?></td>
                        <td>
                            <input type="checkbox" 
                                   class="checkbox-gestito" 
                                   data-iscritto-id="<?php echo (int)$iscritto['id']; ?>" 
                                   <?php echo ($iscritto['is_gestito'] == 1) ? 'checked' : ''; ?>>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/gare/modificaIscrizione/<?php echo $iscritto['id']; ?>" class="btn" style="background:#ffc107; color:black; text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Modifica</a>
                            <a href="<?php echo BASE_URL; ?>/gare/rimuoviIscrizione/<?php echo $iscritto['id']; ?>/<?php echo $gara['id']; ?>" class="btn btn-danger js-rimuovi-iscrizione" style="text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px; color:white;">Rimuovi</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p id="empty-iscritti" style="<?php echo !empty($iscritti) ? 'display:none;' : ''; ?>">Nessun team ancora iscritto a questa gara.</p>
    </div>

    <!-- Modale Nuovo Team -->
    <div id="modal-nuovo-team" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modal-nuovo-team')">&times;</span>
            <h2>Nuovo Team Rapido</h2>
            <form action="<?php echo BASE_URL; ?>/teams/store" method="POST" id="form-modal-nuovo-team">
                <input type="hidden" name="redirect_to" value="/gare/setup/<?php echo $gara['id']; ?>">
                <div class="form-group">
                    <label for="nome_team_modale">Nome Team:</label>
                    <input type="text" id="nome_team_modale" name="nome_team" required style="width: 100%; box-sizing: border-box;">
                </div>
                <button type="button" class="btn" id="btn-modal-salva-team">Crea team</button>
            </form>
        </div>
    </div>
    <!-- Modale Nuovo Pilota -->
    <div id="modal-nuovo-pilota" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modal-nuovo-pilota')">&times;</span>
            <h2>Nuovo Pilota Rapido</h2>
            <form action="<?php echo BASE_URL; ?>/piloti/store" method="POST" id="form-modal-nuovo-pilota">
                <input type="hidden" name="redirect_to" value="/gare/setup/<?php echo $gara['id']; ?>">
                <div class="form-group">
                    <label for="nome_pilota_modale">Nome:</label>
                    <input type="text" id="nome_pilota_modale" name="nome" required style="width: 100%; box-sizing: border-box;">
                </div>
                <div class="form-group">
                    <label for="cognome_pilota_modale">Cognome:</label>
                    <input type="text" id="cognome_pilota_modale" name="cognome" required style="width: 100%; box-sizing: border-box;">
                </div>
                <button type="button" class="btn" id="btn-modal-salva-pilota">Crea pilota</button>
            </form>
        </div>
    </div>
    <script src="<?php echo BASE_URL; ?>/public/js/gare-setup.js"></script>
</body>
</html>
