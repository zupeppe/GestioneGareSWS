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
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { padding: 8px; width: 100%; max-width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .nav-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/home/index" class="nav-link">&larr; Torna alla Home</a>
        
        <h1>Setup Gara: <?php echo htmlspecialchars($gara['nome_gara']); ?></h1>
        <p><strong>Data Evento:</strong> <?php echo htmlspecialchars($gara['data_evento']); ?></p>
        
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
        
        <div class="form-section">
            <h2>Iscrivi Team alla Gara</h2>
            <form action="<?php echo BASE_URL; ?>/gare/iscriviTeam" method="POST">
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
                
                <button type="submit" class="btn">Aggiungi Iscrizione</button>
            </form>
        </div>

        <hr>

        <h2>Team Iscritti</h2>
        <?php if (!empty($iscritti)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Numero Gara</th>
                        <th>Nome Team</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($iscritti as $iscritto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($iscritto['numero_gara']); ?></td>
                            <td><?php echo htmlspecialchars($iscritto['nome_team']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/gare/modificaIscrizione/<?php echo $iscritto['id']; ?>" class="btn" style="background:#ffc107; color:black; text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Modifica</a>
                                <a href="<?php echo BASE_URL; ?>/gare/rimuoviIscrizione/<?php echo $iscritto['id']; ?>/<?php echo $gara['id']; ?>" class="btn btn-danger" style="text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px; color:white;" onclick="return confirm('Sicuro di voler rimuovere questo team dalla gara?');">Rimuovi</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun team ancora iscritto a questa gara.</p>
        <?php endif; ?>
    </div>

    <!-- Modale Nuovo Team -->
    <div id="modal-nuovo-team" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modal-nuovo-team')">&times;</span>
            <h2>Nuovo Team Rapido</h2>
            <form action="<?php echo BASE_URL; ?>/teams/store" method="POST">
                <input type="hidden" name="redirect_to" value="/gare/setup/<?php echo $gara['id']; ?>">
                <div class="form-group">
                    <label for="nome_team_modale">Nome Team:</label>
                    <input type="text" id="nome_team_modale" name="nome_team" required style="width: 100%; box-sizing: border-box;">
                </div>
                <button type="submit" class="btn">Crea Team e Torna al Setup</button>
            </form>
        </div>
    </div>
</body>
</html>
