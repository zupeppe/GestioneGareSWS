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
        
        .dati-globali { display: flex; justify-content: space-around; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 1.3em; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="muretto-container">
        <a href="<?php echo BASE_URL; ?>/home/index" class="nav-link">&larr; Torna alla Home</a>
        
        <div class="header-gara">
            <h1><?php echo htmlspecialchars($gara['nome_gara']); ?> - MURETTO BOX</h1>
        </div>
        
        <div class="dati-globali">
            <div>Tempo di Gara Residuo: <strong><?php echo htmlspecialchars($tempoResiduoHHMM); ?></strong></div>
            <div>Soste Effettuate: <strong><?php echo htmlspecialchars($sosteEffettuate); ?></strong></div>
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
                <div style="font-size: 1.2em; color: #666;">
                    Iniziato a: <strong><?php echo htmlspecialchars(\App\Core\TimeHelper::daMinutiaHHMM($stintAttivo['minuto_ingresso'])); ?></strong>
                </div>
                
                <form action="<?php echo BASE_URL; ?>/muretto/termina/<?php echo $gara['id']; ?>" method="POST" class="form-inline" style="margin-top: 30px;">
                    <input type="hidden" name="stint_id" value="<?php echo $stintAttivo['id']; ?>">
                    <label for="durata" style="font-size: 1.2em; font-weight:bold;">Tempo in Pista (HH:MM):</label>
                    <input type="text" id="durata" name="durata" required style="width: 150px;" placeholder="Es. 01:15" pattern="[0-9]{2}:[0-9]{2}">
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
                    <button type="submit" class="btn-enorme btn-verde">INIZIA STINT</button>
                </form>
            </div>
        <?php endif; ?>

        <hr style="margin: 40px 0;">

        <h2>Storico Stint (Timeline a Cascata)</h2>
        <table>
            <thead>
                <tr>
                    <th>N° Stint</th>
                    <th>Pilota</th>
                    <th>Ingresso</th>
                    <th>Tempo in Pista</th>
                    <th>Uscita</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $numero_stint = 1;
                foreach ($tuttiStint as $stint): 
                    $ingressoHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['minuto_ingresso']);
                    
                    if ($stint['durata_minuti'] !== null) {
                        $durataHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['durata_minuti']);
                        $uscitaHHMM = \App\Core\TimeHelper::daMinutiaHHMM($stint['minuto_ingresso'] + $stint['durata_minuti']);
                    } else {
                        $durataHHMM = 'In Corso';
                        $uscitaHHMM = '-';
                    }
                ?>
                    <tr style="<?php echo $stint['durata_minuti'] === null ? 'background-color: #fff3cd;' : ''; ?>">
                        <td><?php echo $numero_stint++; ?></td>
                        <td><strong><?php echo htmlspecialchars($stint['cognome'] . ' ' . $stint['nome']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ingressoHHMM); ?></td>
                        <td>
                            <?php if ($stint['durata_minuti'] !== null): ?>
                                <form action="<?php echo BASE_URL; ?>/muretto/modificaDurata/<?php echo $gara['id']; ?>" method="POST" style="display:inline-flex; gap:5px; justify-content:center; align-items:center;">
                                    <input type="hidden" name="stint_id" value="<?php echo $stint['id']; ?>">
                                    <input type="text" name="durata" value="<?php echo htmlspecialchars($durataHHMM); ?>" required style="width: 80px; padding: 5px; text-align:center;" pattern="[0-9]{2}:[0-9]{2}">
                                    <button type="submit" class="btn-piccolo">Aggiorna</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #856404; font-weight:bold;">In Corso</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($uscitaHHMM); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($tuttiStint)): ?>
                    <tr><td colspan="5">Nessuno stint registrato per questa gara.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
