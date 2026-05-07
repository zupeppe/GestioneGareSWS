<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Pilota - SWS Endurance Manager</title>
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
        <a href="<?php echo BASE_URL; ?>/piloti/index" class="nav-link">&larr; Torna alla Lista Piloti</a>
        <h1>Modifica Pilota</h1>
        
        <div class="form-section">
            <form action="<?php echo BASE_URL; ?>/piloti/aggiorna/<?php echo $pilota['id']; ?>" method="POST">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($pilota['nome']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="cognome">Cognome:</label>
                    <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($pilota['cognome']); ?>" required>
                </div>
                <button type="submit" class="btn">Salva Modifiche</button>
            </form>
        </div>
    </div>
</body>
</html>
