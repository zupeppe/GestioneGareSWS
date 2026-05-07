<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWS Endurance Manager</title>
   <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
<script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
</head>
<body>
    <div class="container">
        <h1>Benvenuto in SWS Endurance Manager</h1>
        <p>Dashboard principale per la gestione delle gare endurance.</p>
        
        <div class="form-section" style="background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h2>Crea Nuova Gara</h2>
            <form action="<?php echo BASE_URL; ?>/gare/store" method="POST">
                <div style="margin-bottom: 10px;">
                    <label for="nome_gara" style="font-weight:bold;">Nome Gara:</label><br>
                    <input type="text" id="nome_gara" name="nome_gara" style="width: 100%; max-width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="data_evento" style="font-weight:bold;">Data e Ora Evento:</label><br>
                    <input type="datetime-local" id="data_evento" name="data_evento" style="width: 100%; max-width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                </div>
                <button type="submit" class="btn" style="padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Salva Gara</button>
            </form>
        </div>
        
        <h2>Prossime Gare</h2>
        <?php if (!empty($gare)): ?>
            <ul>
                <?php foreach ($gare as $gara): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($gara['nome_gara']); ?></strong> 
                        - <?php echo htmlspecialchars($gara['data_evento']); ?> 
                        (Stato: <?php echo htmlspecialchars($gara['stato']); ?>)
                        <br>
                        <a href="<?php echo BASE_URL; ?>/gare/setup/<?php echo $gara['id']; ?>" class="btn" style="display:inline-block; margin-top:5px; background:#0056b3; color:white; text-decoration:none; padding:5px 10px; font-size:0.9em; border-radius:4px;">Setup Gara</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nessuna gara presente nel database al momento.</p>
        <?php endif; ?>
    </div>
    <div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>/piloti/index" class="btn" style="text-decoration:none;">Gestione Piloti</a>
    <a href="<?php echo BASE_URL; ?>/teams/index" class="btn" style="text-decoration:none;">Gestione Teams</a>
</div>
    <script src="/GestioneGareSWS/public/js/main.js"></script>
</body>
</html>
