<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Spotter Pit Lane</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        body { background-color: #f4f7f6; font-family: sans-serif; margin: 0; padding: 0; }
        .spotter-container { padding: 15px; max-width: 100%; box-sizing: border-box; }
        .header { background: #343a40; color: white; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 1.8em; }
        
        .section-title { font-size: 1.4em; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; }
        
        /* Select Enorme */
        .select-team { width: 100%; padding: 20px; font-size: 1.6em; border: 3px solid #0056b3; border-radius: 10px; background-color: #fff; margin-bottom: 20px; box-sizing: border-box; }
        
        /* Bottoni File */
        .fila-buttons { display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 15px; }
        .btn-fila { flex: 1; min-width: 140px; padding: 25px 10px; font-size: 1.5em; font-weight: bold; color: white; border: none; border-radius: 10px; cursor: pointer; text-transform: uppercase; box-shadow: 0 4px 6px rgba(0,0,0,0.1); box-sizing: border-box; text-align: center; text-shadow: 1px 1px 3px rgba(0,0,0,0.8); }
        .btn-fila:active { transform: translateY(2px); box-shadow: 0 2px 3px rgba(0,0,0,0.1); }
        
        /* Box Stato */
        .box-stato { background: white; border-radius: 8px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-size: 1.2em; border-left: 5px solid #ccc; }
        .rating-badge { display: inline-block; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 1.1em; min-width: 60px; text-align: center; border: 2px solid black; }
        .rating-0 { background: #e9ecef; color: #000; }
        .rating-1 { background: #dc3545; color: #fff; }
        .rating-2 { background: #ffc107; color: #000; }
        .rating-3 { background: #28a745; color: #fff; }

        /* Tabella Team */
        .team-row { background: white; padding: 10px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .team-info { font-size: 1.2em; font-weight: bold; flex: 1; min-width: 200px; }
        .team-rating-form { display: flex; gap: 10px; align-items: center; margin-top: 5px; }
        .team-rating-form select { padding: 10px; font-size: 1em; border-radius: 4px; border: 1px solid #ccc; background: white; }
        .numero-kart-lasciato-wrap { margin-top: 10px; display: none; }
        .numero-kart-lasciato-wrap input { width: 100%; padding: 12px; font-size: 1.1em; border: 2px solid #dc3545; border-radius: 8px; box-sizing: border-box; }
        
        .nav-link { display: inline-block; margin-bottom: 15px; font-size: 1.2em; text-decoration: none; color: #0056b3; font-weight: bold; }
        
        .alert { padding: 15px; font-size: 1.2em; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        
        /* Helper func */
        <?php
        function getRatingBadge($rating) {
            $r = (int)$rating;
            $class = 'rating-0'; $text = 'Ignoto';
            if ($r === 1) { $class = 'rating-1'; $text = 'Scarso'; }
            elseif ($r === 2) { $class = 'rating-2'; $text = 'Medio'; }
            elseif ($r === 3) { $class = 'rating-3'; $text = 'Buono'; }
            return "<span class=\"rating-badge $class\">$text</span>";
        }
        function getRatingText($rating) {
            $r = (int)$rating;
            if ($r === 1) return 'Scarso';
            if ($r === 2) return 'Medio';
            if ($r === 3) return 'Buono';
            return 'Ignoto';
        }
        ?>
    </style>
</head>
<body>
    <?php require_once dirname(__DIR__) . '/layout/navbar.php'; ?>
    <div class="spotter-container">
        <div class="header">
            <h1>Spotter Pit Lane</h1>
            <div style="font-size: 1.1em; margin-top: 5px;"><?php echo htmlspecialchars($gara['nome_gara']); ?></div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- 1. PANNELLO AZIONE -->
        <h2 class="section-title">Azione (Sostituzione Rapida)</h2>
        <form action="<?php echo BASE_URL; ?>/spotter/registraSostituzione/<?php echo $gara['id']; ?>" method="POST" id="form-sostituzione">
            <select name="iscritto_gara_id" id="select-team" class="select-team" required>
                <option value="">-- Seleziona Team --</option>
                <?php foreach ($iscritti as $iscritto): 
                    $ha_kart = false;
                    foreach($statoTeam as $st) {
                        if($st['iscritto']['id'] == $iscritto['id'] && $st['kart']) {
                            $ha_kart = true; break;
                        }
                    }
                ?>
                    <option value="<?php echo $iscritto['id']; ?>" data-ha-kart="<?php echo $ha_kart ? '1' : '0'; ?>">
                        N° <?php echo htmlspecialchars($iscritto['numero_gara']); ?> - <?php echo htmlspecialchars($iscritto['nome_team']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="numero-kart-lasciato-wrap" id="numero-kart-lasciato-wrap">
                <label for="numero_kart_lasciato" style="display:block; margin-bottom:5px; font-weight:bold; color:#721c24;">
                    Team senza kart precedente: inserisci il numero kart lasciato
                </label>
                <input type="text" name="numero_kart_lasciato" id="numero_kart_lasciato" value="" placeholder="Es. 27" inputmode="numeric">
            </div>
        </form>
            
        <div class="fila-buttons">
            <?php foreach ($statoFile as $sf): 
                $nome_fila = $sf['fila'];
                $kart_rating = $sf['kart']['rating'] ?? 0;
                $colore_hex = htmlspecialchars($sf['colore_hex'] ?? '#343a40');
            ?>
                <?php if ($sf['kart']): ?>
                    <button type="button" class="btn-fila" style="background-color: <?php echo $colore_hex; ?>;" onclick="inviaSostituzione('<?php echo htmlspecialchars($nome_fila); ?>');">
                        <div>Fila <?php echo htmlspecialchars($nome_fila); ?></div>
                        <div style="font-size: 0.9em; margin-top: 12px; opacity: 1; text-transform: none; text-shadow: none;">
                            Kart: <?php echo getRatingBadge($kart_rating); ?>
                        </div>
                    </button>
                <?php else: ?>
                    <div style="flex: 1; min-width: 140px; padding: 15px; border: 2px dashed <?php echo $colore_hex; ?>; border-radius: 10px; text-align: center; background: white;">
                        <div style="font-weight: bold; color: <?php echo $colore_hex; ?>; margin-bottom: 10px;">Fila <?php echo htmlspecialchars($nome_fila); ?> Vuota</div>
                        <form action="<?php echo BASE_URL; ?>/spotter/inizializzaFila/<?php echo $gara['id']; ?>" method="POST" style="display:flex; flex-direction:column; gap:5px;">
                            <input type="hidden" name="fila_nome" value="<?php echo htmlspecialchars($nome_fila); ?>">
                            <input type="number" name="numero_kart" placeholder="N° Kart" required style="padding:8px; width:100%; box-sizing:border-box; font-size:1.1em; border:1px solid #ccc; border-radius:4px;">
                            <button type="submit" style="padding:10px; background:<?php echo $colore_hex; ?>; color:white; border:none; border-radius:4px; font-weight:bold; font-size:1em; cursor:pointer;">Inizializza Fila</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- 3. LISTA TEAM E RATING -->
        <h2 class="section-title">Stato Kart in Pista (Team)</h2>
        <?php foreach ($statoTeam as $st): ?>
            <div class="team-row">
                <div class="team-info">
                    <span style="color:#666;">N° <?php echo htmlspecialchars($st['iscritto']['numero_gara']); ?></span> 
                    <?php echo htmlspecialchars($st['iscritto']['nome_team']); ?>
                </div>
                <div class="team-rating-form">
                    <?php echo getRatingBadge($st['kart']['rating'] ?? 0); ?>
                    <?php if ($st['kart']): ?>
                        <form action="<?php echo BASE_URL; ?>/spotter/cambiaRating/<?php echo $gara['id']; ?>" method="POST" style="margin:0;">
                            <input type="hidden" name="kart_id" value="<?php echo $st['kart']['id']; ?>">
                            <select name="rating" onchange="this.form.submit()">
                                <option value="0" <?php echo ($st['kart']['rating']==0)?'selected':''; ?>>Ignoto</option>
                                <option value="1" <?php echo ($st['kart']['rating']==1)?'selected':''; ?>>Scarso</option>
                                <option value="2" <?php echo ($st['kart']['rating']==2)?'selected':''; ?>>Medio</option>
                                <option value="3" <?php echo ($st['kart']['rating']==3)?'selected':''; ?>>Buono</option>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- 4. STRUMENTI DI EMERGENZA -->
        <h2 class="section-title" style="color: #dc3545; margin-top: 40px;">Strumenti di Emergenza</h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 30px;">
            <?php if (!empty($ultimoCambio)): 
                $testoUndo = "N° " . $ultimoCambio['numero_gara'] . " - " . $ultimoCambio['nome_team'] . " in Fila " . $ultimoCambio['fila_colore'];
            ?>
                <form action="<?php echo BASE_URL; ?>/spotter/annullaUltimoCambio/<?php echo $gara['id']; ?>" method="POST" style="flex: 1; min-width: 250px;" onsubmit="return confirm('Sicuro di voler ANNULLARE il cambio:\n<?php echo addslashes($testoUndo); ?> ?');">
                    <button type="submit" class="btn-fila" style="background-color: #fd7e14; color: white; font-size: 1.2em; padding: 15px; width: 100%;">
                        &#9100; Annulla Cambio (Undo)
                    </button>
                </form>
            <?php else: ?>
                <button type="button" class="btn-fila" style="background-color: #ccc; color: white; font-size: 1.2em; padding: 15px; width: 100%; flex: 1; min-width: 250px; cursor: not-allowed;" disabled>
                    Nessun Cambio da Annullare
                </button>
            <?php endif; ?>
            
            <?php if (!empty($ultimoAnnullato)): 
                $testoRedo = "N° " . $ultimoAnnullato['numero_gara'] . " - " . $ultimoAnnullato['nome_team'] . " in Fila " . $ultimoAnnullato['fila_colore'];
            ?>
                <form action="<?php echo BASE_URL; ?>/spotter/ripetiUltimoAnnullato/<?php echo $gara['id']; ?>" method="POST" style="flex: 1; min-width: 250px;" onsubmit="return confirm('Sicuro di voler RIPRISTINARE il cambio:\n<?php echo addslashes($testoRedo); ?> ?');">
                    <button type="submit" class="btn-fila" style="background-color: #28a745; color: white; font-size: 1.2em; padding: 15px; width: 100%;">
                        &#10555; Ripristina Cambio (Redo)
                    </button>
                </form>
            <?php else: ?>
                <button type="button" class="btn-fila" style="background-color: #ccc; color: white; font-size: 1.2em; padding: 15px; width: 100%; flex: 1; min-width: 250px; cursor: not-allowed;" disabled>
                    Nessun Cambio da Ripristinare
                </button>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>/spotter/resetRatingGara/<?php echo $gara['id']; ?>" method="POST" style="flex: 1; min-width: 250px;" onsubmit="return confirm('ATTENZIONE: Stai per resettare TUTTI i kart allo stato IGNOTO. Sei assolutamente sicuro?');">
                <button type="submit" class="btn-fila" style="background-color: #dc3545; color: white; font-size: 1.2em; padding: 15px; width: 100%;">
                    &#9888; Reset Rating (Tutti Ignoto)
                </button>
            </form>
        </div>

    </div>

    <script>
        function inviaSostituzione(filaNome) {
            const form = document.getElementById('form-sostituzione');
            const select = document.getElementById('select-team');
            const inputKartLasciato = document.getElementById('numero_kart_lasciato');
            
            if(!select.value) {
                alert("Seleziona un Team prima di cliccare sulla Fila.");
                return;
            }

            const option = select.options[select.selectedIndex];
            const haKart = option.getAttribute('data-ha-kart') === '1';
            if (!haKart && !inputKartLasciato.value.trim()) {
                alert("Inserisci il numero del kart lasciato per il team selezionato.");
                inputKartLasciato.focus();
                return;
            }

            // Aggiungiamo il campo fila_nome
            const inputFila = document.createElement('input');
            inputFila.type = 'hidden';
            inputFila.name = 'fila_nome';
            inputFila.value = filaNome;
            form.appendChild(inputFila);
            
            form.submit();
        }

        function aggiornaCampoKartLasciato() {
            const select = document.getElementById('select-team');
            const wrap = document.getElementById('numero-kart-lasciato-wrap');
            const input = document.getElementById('numero_kart_lasciato');
            const option = select.options[select.selectedIndex];
            const haKart = option && option.getAttribute('data-ha-kart') === '1';

            if (select.value && !haKart) {
                wrap.style.display = 'block';
                input.required = true;
            } else {
                wrap.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }

        document.getElementById('select-team').addEventListener('change', aggiornaCampoKartLasciato);
        aggiornaCampoKartLasciato();
    </script>
</body>
</html>
