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
        
        <h2>Prossime Gare</h2>
        <?php if (!empty($gare)): ?>
            <ul>
                <?php foreach ($gare as $gara): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($gara['nome_gara']); ?></strong> 
                        - <?php echo htmlspecialchars($gara['data_evento']); ?> 
                        (Stato: <?php echo htmlspecialchars($gara['stato']); ?>)
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
