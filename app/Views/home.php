<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWS Endurance Manager</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            padding: 20px 0;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2em;
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(4px);
        }
        
        .section-title {
            font-size: 1.5em;
            font-weight: bold;
            margin: 0 0 20px 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        /* Dashboard Operativa */
        .race-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .race-card {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .race-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .race-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .race-title {
            font-weight: bold;
            font-size: 1.1em;
            color: #333;
        }
        
        .race-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
        }
        
        .status-setup { background: #ffc107; color: #000; }
        .status-in_corso { background: #28a745; }
        .status-finita { background: #6c757d; }
        
        .race-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .race-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .btn-multi { background: #007bff; color: white; }
        .btn-spotter { background: #28a745; color: white; }
        .btn-setup { background: #ffc107; color: #000; }
        
        /* Configurazione */
        .config-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-group input {
            width: 90%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-submit:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        }
        
        .btn-link {
            display: block;
            width: 100%;
            padding: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 10px;
        }
        
        .btn-link:hover {
            background: #5a6268;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108,117,125,0.3);
        }
        
        /* Anagrafica Generale */
        .anagrafica-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .anagrafica-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .anagrafica-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        
        .anagrafica-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        
        .anagrafica-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        
        .anagrafica-title {
            font-size: 1.3em;
            font-weight: bold;
            margin: 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/layout/navbar.php'; ?>
    
    <div class="main-container">
        <div class="header">
            <h1>SWS Endurance Manager</h1>
            <p>Dashboard principale per la gestione delle gare endurance</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Dashboard Operativa -->
            <div class="section">
                <h2 class="section-title">🏁 Dashboard Operativa</h2>
                <?php if (!empty($gare)): ?>
                    <div class="race-list">
                        <?php foreach ($gare as $gara): ?>
                            <div class="race-card">
                                <div class="race-header">
                                    <span class="race-title"><?php echo htmlspecialchars($gara['nome_gara']); ?></span>
                                    <?php 
                                    $ruolo_utente = $_SESSION['utente']['ruolo'] ?? '';
                                    $status_labels = [
                                        'setup' => 'Setup',
                                        'in_corso' => 'In Corso',
                                        'finita' => 'Finita'
                                    ];
                                    if (in_array($ruolo_utente, ['admin', 'team_manager'])): 
                                    ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/gare/cambiastato/<?php echo $gara['id']; ?>" style="margin: 0; display: inline-block;">
                                            <select name="stato" onchange="this.form.submit()" class="race-status status-<?php echo $gara['stato']; ?>" style="border:1px solid rgba(255,255,255,0.3); cursor:pointer; outline:none; font-family:inherit; appearance:none; padding-right: 15px;">
                                                <option value="setup" <?php echo $gara['stato'] === 'setup' ? 'selected' : ''; ?> style="color: black;">Setup</option>
                                                <option value="in_corso" <?php echo $gara['stato'] === 'in_corso' ? 'selected' : ''; ?> style="color: black;">In Corso</option>
                                                <option value="finita" <?php echo $gara['stato'] === 'finita' ? 'selected' : ''; ?> style="color: black;">Finita</option>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="race-status status-<?php echo $gara['stato']; ?>">
                                            <?php echo $status_labels[$gara['stato']] ?? $gara['stato']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="race-date">
                                    📅 <?php echo date('d/m/Y H:i', strtotime($gara['data_evento'])); ?>
                                </div>
                                <div class="race-actions">
                                    <a href="<?php echo BASE_URL; ?>/muretto/multi/<?php echo $gara['id']; ?>" class="btn-action btn-multi">👥 Muretto Multi-Team</a>
                                    <a href="<?php echo BASE_URL; ?>/spotter/index/<?php echo $gara['id']; ?>" class="btn-action btn-spotter">🏁 Spotter Pit</a>
                                    <a href="<?php echo BASE_URL; ?>/gare/setup/<?php echo $gara['id']; ?>" class="btn-action btn-setup">⚙️ Setup Gara</a>
                                </div>
                                
                                <!-- Individual Team Muretto Buttons -->
                                <?php
                                // Get managed teams for this race
                                $iscrittoModel = new \App\Models\IscrittoGara();
                                $teamGestiti = $iscrittoModel->ottieniGestitiPerGara($gara['id']);
                                if (!empty($teamGestiti)):
                                ?>
                                    <div class="team-muretto-buttons" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                                        <div style="font-size: 0.9em; font-weight: bold; color: #666; margin-bottom: 8px;">🎯 Muretto Team Singolo:</div>
                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                            <?php foreach ($teamGestiti as $team): ?>
                                                <a href="<?php echo BASE_URL; ?>/muretto/index/<?php echo $gara['id']; ?>/<?php echo $team['team_id']; ?>" 
                                                   class="btn-action" 
                                                   style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.8em; padding: 6px 12px;">
                                                    🏎️ <?php echo htmlspecialchars($team['nome_team']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Nessuna gara presente</h3>
                        <p>Crea la tua prima gara per iniziare a gestire le strategie endurance</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Configurazione -->
            <div class="section">
                <h2 class="section-title">⚙️ Configurazione</h2>
                <div class="config-section">
                    <div>
                        <h3 style="margin: 0 0 15px 0; color: #333;">Crea Nuova Gara</h3>
                        <form action="<?php echo BASE_URL; ?>/gare/store" method="POST">
                            <div class="form-group">
                                <label for="nome_gara">Nome Gara</label>
                                <input type="text" id="nome_gara" name="nome_gara" required placeholder="Es. 6 Ore di Monza">
                            </div>
                            <div class="form-group">
                                <label for="data_evento">Data e Ora Evento</label>
                                <input type="datetime-local" id="data_evento" name="data_evento" required>
                            </div>
                            <button type="submit" class="btn-submit">➕ Crea Gara</button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 style="margin: 0 0 15px 0; color: #333;">Gestione Gare</h3>
                        <a href="<?php echo BASE_URL; ?>/gare/list" class="btn-link">📋 Lista Gare Passate</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Anagrafica Generale -->
        <div class="section">
            <h2 class="section-title">👥 Anagrafica Generale</h2>
            <div class="anagrafica-grid">
                <a href="<?php echo BASE_URL; ?>/piloti/index" class="anagrafica-card">
                    <div class="anagrafica-icon">👨‍🏎️</div>
                    <h3 class="anagrafica-title">Gestione Piloti</h3>
                </a>
                <a href="<?php echo BASE_URL; ?>/teams/index" class="anagrafica-card">
                    <div class="anagrafica-icon">🏢</div>
                    <h3 class="anagrafica-title">Gestione Team</h3>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Imposta data di default a oggi + 1 settimana
        document.addEventListener('DOMContentLoaded', function() {
            const dataInput = document.getElementById('data_evento');
            if (dataInput && !dataInput.value) {
                const defaultDate = new Date();
                defaultDate.setDate(defaultDate.getDate() + 7);
                dataInput.value = defaultDate.toISOString().slice(0, 16);
            }
        });
    </script>
</body>
</html>
