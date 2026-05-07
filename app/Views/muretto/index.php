<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muretto Box - SWS Endurance Manager</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        .muretto-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header-gara { background: #343a40; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .header-gara h1 { margin: 0; font-size: 2em; }
        
        .box-section { padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .box-attivo { background: #fff3cd; border: 2px solid #ffeeba; }
        .box-libero { background: #d4edda; border: 2px solid #c3e6cb; }
        
        .pilota-attivo-nome { font-size: 3em; font-weight: bold; margin: 10px 0; color: #856404; }
        .minuto-text { font-size: 1.5em; color: #666; }
        
        .form-inline { display: inline-flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .form-inline select, .form-inline input { padding: 10px; font-size: 1.2em; border: 1px solid #ccc; border-radius: 4px; }
        
        .btn-enorme { font-size: 1.5em; padding: 15px 30px; font-weight: bold; cursor: pointer; border: none; border-radius: 5px; color: white; }
        .btn-verde { background: #28a745; }
        .btn-verde:hover { background: #218838; }
        .btn-rosso { background: #dc3545; }
        .btn-rosso:hover { background: #c82333; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; font-size: 1.1em; }
        th { background-color: #0056b3; color: white; }
        .nav-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #0056b3; }
    </style>
</head>
<body>
    <div class="muretto-container">
        <a href="<?php echo BASE_URL; ?>/home/index" class="nav-link">&larr; Torna alla Home</a>
        
        <div class="header-gara">
            <h1><?php echo htmlspecialchars($gara['nome_gara']); ?> - MURETTO BOX</h1>
            <p style="font-size: 1.2em; margin-top: 5px;">Durata Totale: <strong><?php echo htmlspecialchars($gara['durata_minuti']); ?> minuti</strong></p>
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

        <?php if ($stintAttivo): ?>
            <!-- SEZIONE STINT ATTIVO -->
            <div class="box-section box-attivo">
                <h2 style="margin:0; color: #856404;">PILOTA IN PISTA</h2>
                <div class="pilota-attivo-nome">
                    <?php echo htmlspecialchars($stintAttivo['cognome'] . ' ' . $stintAttivo['nome']); ?>
                </div>
                <div class="minuto-text">
                    Entrato a: <strong>-<?php echo htmlspecialchars($stintAttivo['minuto_ingresso']); ?> min</strong>
                </div>
                
                <form action="<?php echo BASE_URL; ?>/muretto/termina/<?php echo $gara['id']; ?>" method="POST" class="form-inline" style="margin-top: 30px;">
                    <input type="hidden" name="stint_id" value="<?php echo $stintAttivo['id']; ?>">
                    <label for="minuto_uscita" style="font-size: 1.2em; font-weight:bold;">Esce a (min):</label>
                    <!-- Imposta il max per evitare che il minuto uscita sia maggiore di quello di ingresso -->
                    <input type="number" id="minuto_uscita" name="minuto_uscita" required style="width: 120px;" max="<?php echo $stintAttivo['minuto_ingresso']; ?>">
                    <button type="submit" class="btn-enorme btn-rosso">TERMINA STINT</button>
                </form>
            </div>
        <?php else: ?>
            <!-- SEZIONE BOX (LIBERO) -->
            <div class="box-section box-libero">
                <h2 style="margin:0; color: #155724; font-size: 2em;">BOX PRONTO - NESSUNO IN PISTA</h2>
                
                <form action="<?php echo BASE_URL; ?>/muretto/inizia/<?php echo $gara['id']; ?>" method="POST" class="form-inline" style="margin-top: 20px;">
                    <label for="pilota_id" style="font-size: 1.2em; font-weight:bold;">Pilota che sale:</label>
                    <select id="pilota_id" name="pilota_id" required>
                        <option value="">-- Seleziona Pilota --</option>
                        <?php foreach ($roster as $pilota): ?>
                            <option value="<?php echo $pilota['pilota_id']; ?>">
                                <?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="minuto_ingresso" style="font-size: 1.2em; font-weight:bold; margin-left: 10px;">Entra a (min):</label>
                    <input type="number" id="minuto_ingresso" name="minuto_ingresso" required style="width: 120px;">
                    
                    <button type="submit" class="btn-enorme btn-verde">INIZIA STINT</button>
                </form>
            </div>
        <?php endif; ?>

        <hr style="margin: 40px 0;">

        <h2>Riepilogo Piloti (Minuti Guidati)</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome Pilota</th>
                    <th>Minuti Totali Guidati (Stint Chiusi)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roster as $pilota): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($pilota['minuti_guidati']); ?> minuti</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($roster)): ?>
                    <tr><td colspan="2" style="text-align:center;">Nessun pilota nel roster. Aggiungi piloti dal Setup Gara.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
