<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muretto Multi-Team - <?php echo htmlspecialchars($gara['nome_gara']); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .multi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            max-width: 100%;
        }
        
        .team-column {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 5px solid #007bff;
        }
        
        .team-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .team-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .team-number {
            font-size: 1.5em;
            color: #007bff;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .mini-section {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .mini-section h4 {
            margin: 0 0 8px 0;
            font-size: 1em;
            color: #495057;
        }
        
        .pilot-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .pilot-info:last-child {
            border-bottom: none;
        }
        
        .pilot-name {
            font-weight: bold;
            color: #333;
        }
        
        .pilot-time {
            color: #666;
            font-size: 0.9em;
        }
        
        .btn-mini {
            padding: 8px 12px;
            font-size: 0.9em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-start {
            background: #28a745;
            color: white;
        }
        
        .btn-stop {
            background: #dc3545;
            color: white;
        }
        
        .btn-view {
            background: #007bff;
            color: white;
        }
        
        .strategia-info {
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .jolly-count {
            font-weight: bold;
            color: #28a745;
        }
        
        .no-teams {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        @media (max-width: 768px) {
            .multi-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php require_once dirname(__DIR__) . '/layout/navbar.php'; ?>
    
    <a href="<?php echo BASE_URL; ?>/home/index" class="back-link">← Torna alla Home</a>
    
    <h1 style="text-align: center; margin-bottom: 20px;">Muretto Multi-Team</h1>
    <h2 style="text-align: center; margin-bottom: 30px; color: #666;"><?php echo htmlspecialchars($gara['nome_gara']); ?></h2>
    <?php if (!empty($teamData)): ?>
        <h3 style="text-align: center; margin-bottom: 40px; color: #007bff;">
            Team Gestiti: <?php echo implode(', ', array_map(function($data) { 
                return htmlspecialchars($data['team']['nome_team']); 
            }, $teamData)); ?>
        </h3>
    <?php endif; ?>
    
    <?php if (empty($teamData)): ?>
        <div class="no-teams">
            <h3>Nessun team gestito configurato</h3>
            <p>Vai al setup della gara per configurare i team che vuoi gestire.</p>
            <a href="<?php echo BASE_URL; ?>/gare/setup/<?php echo $gara['id']; ?>" class="btn-mini btn-view">Vai al Setup</a>
        </div>
    <?php else: ?>
        <div class="multi-container">
            <?php foreach ($teamData as $data): ?>
                <div class="team-column">
                    <div class="team-header">
                        <h3 class="team-title"><?php echo htmlspecialchars($data['team']['nome_team']); ?></h3>
                        <div class="team-number">N° <?php echo htmlspecialchars($data['team']['numero_gara']); ?></div>
                    </div>
                    
                    <!-- Pilota in pista -->
                    <div class="mini-section">
                        <h4>Pilota in pista</h4>
                        <?php if ($data['stintAttivo']): ?>
                            <div class="pilot-info">
                                <span class="pilot-name"><?php echo htmlspecialchars($data['stintAttivo']['cognome'] . ' ' . $data['stintAttivo']['nome']); ?></span>
                                <span class="pilot-time">Minuto <?php echo $data['stintAttivo']['minuto_ingresso']; ?></span>
                            </div>
                            <div style="text-align: center; margin-top: 10px;">
                                <a href="<?php echo BASE_URL; ?>/muretto/termina/<?php echo $data['gara']['id']; ?>/<?php echo $data['stintAttivo']['id']; ?>" 
                                   class="btn-mini btn-stop">Termina Stint</a>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; color: #666; padding: 10px;">
                                Nessun pilota in pista
                            </div>
                            <div style="text-align: center; margin-top: 10px;">
                                <a href="<?php echo BASE_URL; ?>/muretto/inizia/<?php echo $data['gara']['id']; ?>" 
                                   class="btn-mini btn-start">Inizia Stint</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Strategia -->
                    <div class="mini-section">
                        <h4>Strategia</h4>
                        <div class="strategia-info">
                            <div>Pit residui: <strong><?php echo $data['strategia']['pit_rimanenti_obbligatori']; ?></strong></div>
                            <div>Tempo max copribile: <strong><?php echo \App\Core\TimeHelper::daMinutiaHHMM($data['strategia']['tempo_massimo_copribile']); ?></strong></div>
                            <div>Jolly residui: <span class="jolly-count"><?php echo $data['strategia']['jolly_disponibili']; ?></span></div>
                            <div>Stato: <strong style="color: <?php echo $data['strategia']['colore_strategia']; ?>;"><?php echo htmlspecialchars($data['strategia']['stato_strategia']); ?></strong></div>
                        </div>
                    </div>
                    
                    <!-- Tempi Piloti -->
                    <div class="mini-section">
                        <h4>Tempi Piloti</h4>
                        <?php foreach ($data['roster'] as $pilota): ?>
                            <div class="pilot-info">
                                <span class="pilot-name"><?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?></span>
                                <span class="pilot-time">
                                    <?php 
                                    $tempoTotale = $data['tempiTotaliPiloti'][$pilota['pilota_id']] ?? 0;
                                    echo \App\Core\TimeHelper::daMinutiaHHMM($tempoTotale);
                                    ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Azioni -->
                    <div class="mini-section" style="text-align: center;">
                        <a href="<?php echo BASE_URL; ?>/muretto/index/<?php echo $data['gara']['id']; ?>/<?php echo $data['team']['team_id']; ?>" 
                           class="btn-mini btn-view" style="width: 100%;">Muretto Completo</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <script>
        // Polling ogni 5 secondi per aggiornare i dati
        setInterval(() => {
            window.location.reload();
        }, 5000);
    </script>
</body>
</html>
