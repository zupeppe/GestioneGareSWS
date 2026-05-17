<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muretto Box - <?php echo htmlspecialchars($gara['nome_gara']); ?><?php echo isset($teamSelezionato) ? ' - ' . htmlspecialchars($teamSelezionato['nome_team']) : ''; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        .muretto-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header-gara { background: #343a40; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .header-gara h1 { margin: 0; font-size: 2em; }
        
        .box-section { padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .box-attivo { background: #fff3cd; border: 2px solid #ffeeba; }
        .box-libero { background: #d4edda; border: 2px solid #c3e6cb; }
        
        .pilota-attivo-nome { font-size: 3em; font-weight: bold; margin: 10px 0; color: #856404; }
        
        .form-inline { display: inline-flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .form-inline select, .form-inline input { padding: 10px; font-size: 1.2em; border: 1px solid #ccc; border-radius: 4px; }
        
        .btn-enorme { font-size: 1.5em; padding: 15px 30px; font-weight: bold; cursor: pointer; border: none; border-radius: 5px; color: white; }
        .btn-verde { background: #28a745; }
        .btn-verde:hover { background: #218838; }
        .btn-rosso { background: #dc3545; }
        .btn-rosso:hover { background: #c82333; }
        .btn-piccolo { padding: 5px 10px; font-size: 0.9em; background: #0056b3; color: white; border: none; border-radius: 3px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; font-size: 1.1em; }
        th { background-color: #0056b3; color: white; }
        .nav-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #0056b3; }
        
        .pannello-superiore { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .dati-globali, .pannello-strategia { background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 1.2em; border: 1px solid #ccc; }
        .pannello-strategia { text-align: left; }
        
        @media (max-width: 768px) { .pannello-superiore { grid-template-columns: 1fr; } }
        .riga-allerta { background-color: #f8d7da !important; color: #721c24; border: 2px solid #f5c6cb; }
        .table-warning { background-color: #fff3cd !important; color: #856404; border: 2px solid #ffeeba; }
        
        .rating-badge { display: inline-block; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 1.1em; min-width: 60px; text-align: center; border: 2px solid black; }
        .rating-0 { background: #e9ecef; color: #000; }
        .rating-1 { background: #dc3545; color: #fff; }
        .rating-2 { background: #ffc107; color: #000; }
        .rating-3 { background: #28a745; color: #fff; }
        .rating-4 { background: #28a745; color: #fff; }
        .rating-5 { background: #28a745; color: #fff; }
        
        .stint-cancellato { background: #f8d7da !important; opacity: 0.7; }
        .stint-cancellato td { text-decoration: line-through; color: #721c24; }
        .btn-cancella { background: #dc3545; color: white; padding: 3px 8px; font-size: 0.8em; border: none; border-radius: 3px; cursor: pointer; margin-right: 5px; }
        .btn-cancella:hover { background: #c82333; }
        .btn-ripristina { background: #28a745; color: white; padding: 3px 8px; font-size: 0.8em; border: none; border-radius: 3px; cursor: pointer; }
        .btn-ripristina:hover { background: #218838; }
        .sezione-cancellati { margin-top: 30px; border: 2px dashed #dc3545; border-radius: 5px; padding: 15px; background: #fff5f5; }
        .sezione-cancellati h3 { color: #dc3545; margin-top: 0; }
        .conferma-cancellazione { background: #fff; border: 2px solid #dc3545; border-radius: 5px; padding: 15px; margin: 10px 0; }
        .conferma-cancellazione input { padding: 8px; border: 1px solid #ccc; border-radius: 3px; margin-right: 10px; }
        
        <?php
        function getRatingBadge($rating) {
            $r = (int)$rating;
            $class = 'rating-0'; $text = 'Ignoto';
            if ($r === 1) { $class = 'rating-1'; $text = 'Scarso'; }
            elseif ($r === 2) { $class = 'rating-2'; $text = 'Medio'; }
            elseif ($r === 3) { $class = 'rating-3'; $text = 'Buono'; }
            elseif ($r === 4) { $class = 'rating-4'; $text = '💣 Bomba'; }
            elseif ($r === 5) { $class = 'rating-5'; $text = '🏆 Best Lap'; }
            return "<span class=\"rating-badge $class\">$text</span>";
        }
        ?>
    </style>
</head>
<body>
    <?php require_once dirname(__DIR__) . '/layout/navbar.php'; ?>
    <div class="muretto-container">
        <div class="header-gara">
            <h1><?php echo htmlspecialchars($gara['nome_gara']); ?> - MURETTO BOX<?php echo isset($teamSelezionato) ? ' - ' . htmlspecialchars($teamSelezionato['nome_team']) : ''; ?></h1>
        </div>
        
        <?php
        $durata_min_stint_gara = $gara['durata_min_stint'] ?? null;
        $tenuta_min_kart = ($durata_min_stint_gara === null || (int)$durata_min_stint_gara === 0)
            ? 'N.D.'
            : htmlspecialchars(\App\Core\TimeHelper::daMinutiaHHMM((int)$durata_min_stint_gara), ENT_QUOTES, 'UTF-8');
        $tenuta_max_kart = htmlspecialchars(
            \App\Core\TimeHelper::daMinutiaHHMM((int)($gara['durata_max_stint'] ?? 0)),
            ENT_QUOTES,
            'UTF-8'
        );
        ?>
        <div class="pannello-superiore">
            <div class="dati-globali" id="refresh-dati-generali">
                <h3 style="margin-top:0;">Dati Generali</h3>
                <div style="margin-bottom: 5px;">Tempo di Gara Residuo: <strong><?php echo htmlspecialchars($tempoResiduoHHMM); ?></strong></div>
                <div style="margin-bottom: 5px;">Soste Effettuate: <strong><?php echo htmlspecialchars($strategia['pit_fatti']); ?> / <?php echo htmlspecialchars($strategia['pit_minimi']); ?> minime</strong></div>
                <div style="margin-bottom: 5px;">Tenuta Min Kart: <strong><?php echo $tenuta_min_kart; ?></strong></div>
                <div style="margin-bottom: 5px;">Tenuta Max Kart: <strong><?php echo $tenuta_max_kart; ?></strong></div>
                <?php if ($gara['tempo_min_pilota'] > 0 || $gara['tempo_max_pilota'] > 0): ?>
                    <hr style="margin: 10px 0; border-top: 1px solid #ddd;">
                    <div style="margin-bottom: 5px;">
                        <strong>Limiti Pilota:</strong>
                        <?php if ($gara['tempo_min_pilota'] > 0): ?>
                            <span style="color: #28a745;"> Min: <?php echo htmlspecialchars(\App\Core\TimeHelper::daMinutiaHHMM($gara['tempo_min_pilota'])); ?></span>
                        <?php endif; ?>
                        <?php if ($gara['tempo_max_pilota'] > 0): ?>
                            <span style="color: #dc3545;"> Max: <?php echo htmlspecialchars(\App\Core\TimeHelper::daMinutiaHHMM($gara['tempo_max_pilota'])); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="pannello-strategia" id="refresh-strategia" style="border-left: 5px solid <?php echo $strategia['colore_strategia']; ?>;">
                <h3 style="margin-top:0;">Pannello Strategia</h3>
                <div style="margin-bottom: 5px;">Pit obbligatori rimanenti: <strong><?php echo htmlspecialchars($strategia['pit_rimanenti_obbligatori']); ?></strong></div>
                <div style="margin-bottom: 5px;">Tempo Max Copribile: <strong><?php echo htmlspecialchars(\App\Core\TimeHelper::daMinutiaHHMM($strategia['tempo_massimo_copribile'])); ?></strong></div>
                <div style="margin-bottom: 5px;">Media Stint Ideale: <strong><?php echo htmlspecialchars($strategia['media_stint_formattata']) ?></strong></div>
                <div>
                    Stato: <strong style="color: <?php echo $strategia['colore_strategia']; ?>;"><?php echo htmlspecialchars($strategia['stato_strategia']); ?></strong>
                    <?php if ($strategia['stato_strategia'] === 'OK'): ?>
                        <br><span style="font-size: 0.9em;">(Pit "Jolly" a disposizione: <strong><?php echo $strategia['jolly_disponibili']; ?></strong>)</span>
                    <?php else: ?>
                        <br><span style="font-size: 0.9em; color: #dc3545;">(Pit extra necessari: <strong><?php echo $strategia['pit_extra_necessari']; ?></strong>)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold;">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div id="refresh-pilota-pista">
        <?php if ($stintAttivo): ?>
            <!-- SEZIONE STINT ATTIVO -->
            <div class="box-section box-attivo">
                <h2 style="margin:0; color: #856404;">PILOTA IN PISTA</h2>
                <div class="pilota-attivo-nome">
                    <?php echo htmlspecialchars($stintAttivo['cognome'] . ' ' . $stintAttivo['nome']); ?>
                </div>
                
                <div id="nostro-kart-container" style="margin-top: 15px; display: <?php echo $nostro_kart ? 'block' : 'none'; ?>;">
                    <h3 style="margin:0; color: #333;">KART ATTUALE:</h3>
                    <div id="nostro-kart-badge" style="font-size: 2em; margin-top: 10px;">
                        <?php 
                        if ($nostro_kart) {
                            echo getRatingBadge($nostro_kart['rating'] ?? 0); 
                        }
                        ?>
                    </div>
                </div>

                <div style="font-size: 1.2em; color: #666; margin-top: 20px;">
                    Iniziato a: <strong><?php echo htmlspecialchars(\App\Core\TimeHelper::daMinutiaHHMM($stintAttivo['minuto_ingresso'])); ?></strong>
                </div>
                
                <form action="<?php echo BASE_URL; ?>/muretto/termina/<?php echo $gara['id']; ?><?php echo isset($teamSelezionato) ? '/' . $teamSelezionato['team_id'] : ''; ?>" method="POST" class="form-inline" style="margin-top: 30px;">
                    <input type="hidden" name="stint_id" value="<?php echo $stintAttivo['id']; ?>">
                    <?php if (isset($teamSelezionato)): ?>
                        <input type="hidden" name="team_id" value="<?php echo $teamSelezionato['team_id']; ?>">
                    <?php endif; ?>
                    <label for="durata" style="font-size: 1.2em; font-weight:bold;">Tempo in Pista (HH:MM):</label>
                    <input type="text" id="durata" name="durata" required style="width: 150px;" placeholder="Es. 01:15" pattern="[0-9]{2}:[0-9]{2}">
                    <button type="submit" class="btn-enorme btn-rosso">TERMINA STINT</button>
                    <a
                        href="<?php echo BASE_URL; ?>/muretto/annullaStintAttivo/<?php echo $gara['id']; ?>/<?php echo $stintAttivo['id']; ?><?php echo isset($teamSelezionato) ? '?team_id=' . $teamSelezionato['team_id'] : ''; ?>"
                        style="display:inline-block; font-size:1em; padding:10px 15px; background:#6c757d; color:white; text-decoration:none; border-radius:5px; font-weight:bold;"
                        onclick="return confirm('Sicuro di voler annullare questo stint?');"
                    >
                        Annulla Inserimento
                    </a>
                </form>
            </div>
        <?php else: ?>
            <!-- SEZIONE BOX (LIBERO) -->
            <div class="box-section box-libero">
                <h2 style="margin:0; color: #155724; font-size: 2em;">BOX PRONTO - NESSUNO IN PISTA</h2>
                
                <form action="<?php echo BASE_URL; ?>/muretto/inizia/<?php echo $gara['id']; ?><?php echo isset($teamSelezionato) ? '/' . $teamSelezionato['team_id'] : ''; ?>" method="POST" class="form-inline" style="margin-top: 20px;">
                    <?php if (isset($teamSelezionato)): ?>
                        <input type="hidden" name="team_id" value="<?php echo $teamSelezionato['team_id']; ?>">
                    <?php endif; ?>
                    <label for="pilota_id" style="font-size: 1.2em; font-weight:bold;">Pilota che sale:</label>
                    <select id="pilota_id" name="pilota_id" required>
                        <option value="">-- Seleziona Pilota --</option>
                        <?php foreach ($roster as $pilota): ?>
                            <option value="<?php echo $pilota['pilota_id']; ?>">
                                <?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-enorme btn-verde">INIZIA STINT</button>
                </form>
            </div>
        <?php endif; ?>
        </div>

        <!-- SEZIONE ROSTER PILOTI -->
        <div id="refresh-roster-piloti">
            <h2>Roster Piloti</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pilota</th>
                        <th>Totale Guidato</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roster as $pilota): 
                        $tempoTotale = $tempiTotaliPiloti[$pilota['pilota_id']] ?? 0;
                        $tempoTotaleHHMM = \App\Core\TimeHelper::daMinutiaHHMM($tempoTotale);
                        
                        // Determina il colore in base ai limiti
                        $coloreTesto = '';
                        $iconaStato = '';
                        $statoTesto = '';
                        
                        if ($gara['tempo_max_pilota'] > 0) {
                            $minutiRimanentiMax = $gara['tempo_max_pilota'] - $tempoTotale;
                            if ($minutiRimanentiMax <= 0) {
                                $coloreTesto = 'color: #dc3545; font-weight: bold;'; // Rosso
                                $statoTesto = 'SUPERATO LIMITE MAX';
                            } elseif ($minutiRimanentiMax <= 10) {
                                $coloreTesto = 'color: #ff8c00; font-weight: bold;'; // Arancione
                                $statoTesto = 'AVVICINATO LIMITE MAX';
                            }
                        }
                        
                        if ($gara['tempo_min_pilota'] > 0 && $tempoTotale >= $gara['tempo_min_pilota']) {
                            $iconaStato = ' ✓';
                            if ($statoTesto === '') $statoTesto = 'In Regola';
                        }
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?></strong></td>
                            <td style="<?php echo $coloreTesto; ?>">
                                <?php echo htmlspecialchars($tempoTotaleHHMM); ?>
                                <?php echo $iconaStato; ?>
                            </td>
                            <td>
                                <?php if ($stintAttivo && (int)$stintAttivo['pilota_id'] === (int)$pilota['pilota_id']): ?>
                                    <span style="color: #856404; font-weight: bold;">IN PISTA</span>
                                <?php else: ?>
                                    <span style="color: #666;"><?php echo htmlspecialchars($statoTesto ?: 'Sotto Limite Min'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($roster)): ?>
                        <tr><td colspan="3">Nessun pilota nel roster.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr style="margin: 40px 0;">

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <!-- Colonna 1: Timeline (70%) -->
            <div style="flex: 2; min-width: 350px;" id="refresh-storico-stint">
                <h2>Storico Stint (Timeline a Cascata)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>N° Stint</th>
                            <th>Pilota</th>
                            <th>Ingresso (HH:MM)</th>
                            <th>Tempo in Pista (HH:MM)</th>
                            <th>Uscita (HH:MM)</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $numero_stint = 1;
                        $is_primo = true;
                        foreach ($tuttiStint as $stint): 
                            $ingressoHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['minuto_ingresso']);
                            
                            $durata_minuti = $stint['durata_minuti'];
                            $alert_class = '';
                            if ($durata_minuti !== null) {
                                $durataHHMM = \App\Core\TimeHelper::daMinutiaHHMM($durata_minuti);
                                $uscitaHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['minuto_ingresso'] + $durata_minuti);
                                if ($gara['durata_max_stint'] > 0 && $durata_minuti > $gara['durata_max_stint']) {
                                    $alert_class = 'riga-allerta';
                                } elseif (!empty($gara['durata_min_stint']) && $durata_minuti < $gara['durata_min_stint']) {
                                    $alert_class = 'table-warning';
                                }
                            } else {
                                $durataHHMM = 'In Corso';
                                $uscitaHHMM = '-';
                            }
                        ?>
                            <tr class="<?php echo $durata_minuti === null ? 'box-attivo' : $alert_class; ?>" style="<?php echo $durata_minuti === null ? 'background-color: #fff3cd;' : ''; ?>">
                                <td><?php echo $numero_stint++; ?></td>
                                <td><strong><?php echo htmlspecialchars($stint['cognome'] . ' ' . $stint['nome']); ?></strong></td>
                                <td>
                                    <?php if ($is_primo): ?>
                                        <form action="<?php echo BASE_URL; ?>/muretto/aggiornaPrimoIngresso/<?php echo $gara['id']; ?><?php echo isset($teamSelezionato) ? '/' . $teamSelezionato['team_id'] : ''; ?>" method="POST" style="display:inline-flex; gap:5px; justify-content:center; align-items:center;">
                                            <input type="hidden" name="stint_id" value="<?php echo $stint['id']; ?>">
                                            <?php if (isset($teamSelezionato)): ?>
                                                <input type="hidden" name="team_id" value="<?php echo $teamSelezionato['team_id']; ?>">
                                            <?php endif; ?>
                                            <input type="text" name="minuto_ingresso_hhmm" value="<?php echo htmlspecialchars($ingressoHHMM); ?>" required style="width: 80px; padding: 5px; text-align:center;" pattern="[0-9]{2}:[0-9]{2}">
                                            <button type="submit" class="btn-piccolo">Applica</button>
                                        </form>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($ingressoHHMM); ?>
                                        <div style="font-size:0.8em; color:#666;">(+<?php echo htmlspecialchars((int)round($gara['tempo_minimo_pit']/60)); ?>m pit)</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($durata_minuti !== null): ?>
                                        <form action="<?php echo BASE_URL; ?>/muretto/modificaDurata/<?php echo $gara['id']; ?><?php echo isset($teamSelezionato) ? '/' . $teamSelezionato['team_id'] : ''; ?>" method="POST" style="display:inline-flex; gap:5px; justify-content:center; align-items:center;">
                                            <input type="hidden" name="stint_id" value="<?php echo $stint['id']; ?>">
                                            <?php if (isset($teamSelezionato)): ?>
                                                <input type="hidden" name="team_id" value="<?php echo $teamSelezionato['team_id']; ?>">
                                            <?php endif; ?>
                                            <input type="text" name="durata" value="<?php echo htmlspecialchars($durataHHMM); ?>" required style="width: 80px; padding: 5px; text-align:center;" pattern="[0-9]{2}:[0-9]{2}">
                                            <button type="submit" class="btn-piccolo">Aggiorna</button>
                                        </form>
                                        <?php if($alert_class === 'riga-allerta'): ?>
                                            <div style="font-size:0.8em; font-weight:bold; margin-top:4px;">SUPERATO MAX STINT!</div>
                                        <?php elseif($alert_class === 'table-warning'): ?>
                                            <div style="font-size:0.8em; font-weight:bold; margin-top:4px;">SOTTO MIN STINT!</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #856404; font-weight:bold;">In Corso</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($uscitaHHMM); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($durata_minuti !== null): ?>
                                        <button type="button" class="btn-cancella" onclick="mostraConfermaCancellazione(<?php echo $stint['id']; ?>, '<?php echo htmlspecialchars($stint['cognome'] . ' ' . $stint['nome']); ?>', '<?php echo htmlspecialchars($ingressoHHMM); ?>')">
                                            Cancella
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.8em;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            $is_primo = false;
                        endforeach; 
                        ?>
                        <?php if (empty($tuttiStint)): ?>
                            <tr><td colspan="6">Nessuno stint registrato per questa gara.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sezione Stint Cancellati -->
            <?php if (!empty($stintCancellati)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #fff5f5; border: 2px dashed #dc3545; border-radius: 8px;">
                    <h3 style="color: #dc3545; margin: 0 0 15px 0;">🗑️ Stint Cancellati</h3>
                    <p style="color: #721c24; font-size: 0.9em; margin: 0 0 15px 0;">Questi stint sono stati cancellati ma possono essere ripristinati.</p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8d7da;">
                                <th style="padding: 8px; text-align: left; border: 1px solid #dc3545;">N° Stint</th>
                                <th style="padding: 8px; text-align: left; border: 1px solid #dc3545;">Pilota</th>
                                <th style="padding: 8px; text-align: left; border: 1px solid #dc3545;">Ingresso (HH:MM)</th>
                                <th style="padding: 8px; text-align: left; border: 1px solid #dc3545;">Tempo in Pista (HH:MM)</th>
                                <th style="padding: 8px; text-align: left; border: 1px solid #dc3545;">Uscita (HH:MM)</th>
                                <th style="padding: 8px; text-align: center; border: 1px solid #dc3545;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $numero_stint_cancellato = 1;
                            foreach ($stintCancellati as $stint): 
                                $ingressoHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['minuto_ingresso']);
                                $durata_minuti = $stint['durata_minuti'];
                                
                                if ($durata_minuti !== null) {
                                    $durataHHMM = \App\Core\TimeHelper::daMinutiaHHMM($durata_minuti);
                                    $uscitaHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['minuto_ingresso'] + $durata_minuti);
                                } else {
                                    $durataHHMM = 'N/D';
                                    $uscitaHHMM = 'N/D';
                                }
                            ?>
                                <tr style="background: #f8d7da; opacity: 0.8;">
                                    <td style="padding: 8px; border: 1px solid #dc3545;"><?php echo $numero_stint_cancellato++; ?></td>
                                    <td style="padding: 8px; border: 1px solid #dc3545; text-decoration: line-through;">
                                        <strong><?php echo htmlspecialchars($stint['cognome'] . ' ' . $stint['nome']); ?></strong>
                                    </td>
                                    <td style="padding: 8px; border: 1px solid #dc3545;"><?php echo htmlspecialchars($ingressoHHMM); ?></td>
                                    <td style="padding: 8px; border: 1px solid #dc3545;"><?php echo htmlspecialchars($durataHHMM); ?></td>
                                    <td style="padding: 8px; border: 1px solid #dc3545;"><?php echo htmlspecialchars($uscitaHHMM); ?></td>
                                    <td style="padding: 8px; text-align: center; border: 1px solid #dc3545;">
                                        <form action="<?php echo BASE_URL; ?>/muretto/ripristinaStint/<?php echo $stint['id']; ?>/<?php echo $gara['id']; ?><?php echo isset($teamSelezionato) ? '/' . $teamSelezionato['team_id'] : ''; ?>" method="POST" style="display:inline;">
                                            <button type="submit" class="btn-ripristina" style="background: #28a745; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;" onclick="return confirm('Ripristinare questo stint?')">Ripristina</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Colonna 2: Radar Avversari (30%) -->
            <div style="flex: 1; min-width: 250px;" id="refresh-radar">
                <h2 style="color: #0056b3;">File Box (Pit Lane)</h2>
                <div id="file-pit-container" style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($kart_in_fila as $colore => $dati): ?>
                        <div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center;" id="fila-<?php echo $dati['fila']['id']; ?>">
                            <div style="font-weight: bold; font-size: 1.1em; display:flex; align-items:center; gap: 8px;">
                                <span style="display:inline-block; width:15px; height:15px; background:<?php echo htmlspecialchars($dati['fila']['colore_hex']); ?>; border-radius:50%; border:1px solid #333;"></span>
                                <?php echo htmlspecialchars($colore); ?>
                            </div>
                            <div class="fila-rating-cell">
                                <?php if ($dati['kart']): ?>
                                    <?php echo getRatingBadge($dati['kart']['rating']); ?>
                                <?php else: ?>
                                    <span style="color: #888; font-style: italic;">Vuota</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($kart_in_fila)): ?>
                        <div style="color: #888; font-style: italic;">Nessuna fila configurata.</div>
                    <?php endif; ?>
                </div>

                <h2 style="color: #0056b3; margin-top: 40px;">Radar Avversari (Live)</h2>
                <table id="radar-table" style="font-size: 0.9em;">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Team</th>
                            <th>Kart</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($avversari_kart as $avv): ?>
                            <tr id="avv-row-<?php echo $avv['iscritto']['id']; ?>">
                                <td style="font-weight: bold; font-size: 1.1em;"><?php echo htmlspecialchars($avv['iscritto']['numero_gara']); ?></td>
                                <td><?php echo htmlspecialchars($avv['iscritto']['nome_team']); ?></td>
                                <td class="avv-rating-cell">
                                    <?php echo getRatingBadge($avv['kart']['rating'] ?? 0); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($avversari_kart)): ?>
                            <tr><td colspan="3">Nessun team iscritto.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    
    <!-- Modale Conferma Cancellazione -->
    <div id="modal-conferma-cancellazione" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; min-width: 400px;">
            <h3 style="color: #dc3545; margin-top: 0;">⚠️ Conferma Cancellazione Stint</h3>
            <p id="testo-conferma"></p>
            <div class="conferma-cancellazione">
                <form id="form-cancellazione" method="POST">
                    <label>Digitare <strong>CONFERMA</strong> per procedere:</label><br>
                    <input type="text" name="conferma" placeholder="CONFERMA" required>
                    <input type="hidden" name="stint_id" id="cancellazione-stint-id">
                    <?php if (isset($teamSelezionato)): ?>
                        <input type="hidden" name="team_id" value="<?php echo $teamSelezionato['team_id']; ?>">
                    <?php endif; ?>
                    <div style="margin-top: 15px;">
                        <button type="submit" class="btn-cancella">Cancella Stint</button>
                        <button type="button" class="btn-piccolo" onclick="chiudiModaleCancellazione()">Annulla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function aggiornaSezioneDaHtml(documentoRemoto, sezioneId) {
            const locale = document.getElementById(sezioneId);
            const remota = documentoRemoto.getElementById(sezioneId);
            if (!locale || !remota) {
                return;
            }

            // Non rimpiazziamo la sezione se l'utente sta scrivendo dentro un form/input.
            const attivo = document.activeElement;
            if (attivo && locale.contains(attivo)) {
                return;
            }

            locale.innerHTML = remota.innerHTML;
        }

        function pollingPaginaMuretto() {
            fetch(window.location.href)
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    const parser = new DOMParser();
                    const documentoRemoto = parser.parseFromString(html, 'text/html');
                    aggiornaSezioneDaHtml(documentoRemoto, 'refresh-dati-generali');
                    aggiornaSezioneDaHtml(documentoRemoto, 'refresh-strategia');
                    aggiornaSezioneDaHtml(documentoRemoto, 'refresh-pilota-pista');
                    aggiornaSezioneDaHtml(documentoRemoto, 'refresh-roster-piloti');
                    aggiornaSezioneDaHtml(documentoRemoto, 'refresh-storico-stint');
                    aggiornaSezioneDaHtml(documentoRemoto, 'refresh-radar');
                })
                .catch(function (errore) {
                    console.error('Errore polling muretto:', errore);
                });
        }

        function mostraConfermaCancellazione(stintId, nomePilota, ingressoHHMM) {
            document.getElementById('testo-conferma').innerHTML = 
                'Sei sicuro di voler cancellare questo stint?<br><br>' +
                '<strong>Pilota:</strong> ' + nomePilota + '<br>' +
                '<strong>Ingresso:</strong> ' + ingressoHHMM + '<br><br>' +
                'Questa azione è reversibile: potrai ripristinare lo stint in seguito.';
            
            document.getElementById('cancellazione-stint-id').value = stintId;
            
            // Imposta l'action del form
            var action = '<?php echo BASE_URL; ?>/muretto/cancellaStint/' + stintId + '/<?php echo $gara['id']; ?><?php echo isset($teamSelezionato) ? '/' . $teamSelezionato['team_id'] : ''; ?>';
            document.getElementById('form-cancellazione').action = action;
            
            // Mostra il modale
            document.getElementById('modal-conferma-cancellazione').style.display = 'block';
        }

        function chiudiModaleCancellazione() {
            document.getElementById('modal-conferma-cancellazione').style.display = 'none';
            document.getElementById('form-cancellazione').reset();
        }

        // Chiudi il modale se si clicca fuori
        window.onclick = function(event) {
            var modal = document.getElementById('modal-conferma-cancellazione');
            if (event.target == modal) {
                chiudiModaleCancellazione();
            }
        }

        setInterval(pollingPaginaMuretto, 5000);
    </script>
</body>
</html>
