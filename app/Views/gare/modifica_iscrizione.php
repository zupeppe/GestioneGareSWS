<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Iscrizione - SWS Endurance Manager</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
    <style>
        .form-section { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { padding: 8px; width: 100%; max-width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .nav-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/gare/setup/<?php echo $gara['id']; ?>" class="nav-link">&larr; Torna al Setup Gara</a>
        
        <h1>Modifica Iscrizione</h1>
        <p>Gara: <strong><?php echo htmlspecialchars($gara['nome_gara']); ?></strong></p>
        <p>Team: <strong><?php echo htmlspecialchars($team['nome_team']); ?></strong></p>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <form action="<?php echo BASE_URL; ?>/gare/aggiornaIscrizione/<?php echo $iscrizione['id']; ?>" method="POST">
                <div class="form-group">
                    <label for="numero_gara">Nuovo Numero di Gara:</label>
                    <input type="number" id="numero_gara" name="numero_gara" value="<?php echo htmlspecialchars($iscrizione['numero_gara']); ?>" required>
                </div>
                <button type="submit" class="btn">Salva Modifica</button>
            </form>
        </div>
    </div>
</body>
</html>
