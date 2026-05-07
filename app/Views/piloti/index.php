<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Piloti - SWS Endurance Manager</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
    <style>
        .form-section { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { padding: 8px; width: 100%; max-width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .nav-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/home/index" class="nav-link">&larr; Torna alla Home</a>
        <h1>Anagrafica Piloti (Mio Team)</h1>
        
        <div class="form-section">
            <h2>Aggiungi Nuovo Pilota</h2>
            <form action="<?php echo BASE_URL; ?>/piloti/store" method="POST">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="cognome">Cognome:</label>
                    <input type="text" id="cognome" name="cognome" required>
                </div>
                <button type="submit" class="btn">Salva Pilota</button>
            </form>
        </div>

        <h2>Lista Piloti</h2>
        <?php if (!empty($piloti)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($piloti as $pilota): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pilota['nome']); ?></td>
                            <td><?php echo htmlspecialchars($pilota['cognome']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/piloti/modifica/<?php echo $pilota['id']; ?>" class="btn" style="background:#ffc107; color:black; text-decoration:none; padding:5px 10px; font-size:0.9em;">Modifica</a>
                                <a href="<?php echo BASE_URL; ?>/piloti/elimina/<?php echo $pilota['id']; ?>" class="btn" style="background:#dc3545; text-decoration:none; padding:5px 10px; font-size:0.9em;" onclick="return confirm('Sei sicuro di voler eliminare questo pilota?');">Elimina</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun pilota inserito al momento.</p>
        <?php endif; ?>
    </div>
</body>
</html>
