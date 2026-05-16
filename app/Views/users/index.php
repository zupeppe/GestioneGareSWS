<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Gestione Gare SWS</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #111827;
            color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        .card {
            background-color: #1f2937;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }
        h1, h2 {
            margin-top: 0;
            color: #f3f4f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #374151;
        }
        th {
            background-color: #374151;
            color: #d1d5db;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #9ca3af;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #374151;
            border-radius: 4px;
            background-color: #374151;
            color: #f9fafb;
            box-sizing: border-box;
        }
        button {
            padding: 0.75rem 1.5rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #2563eb;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-warning {
            background-color: #f59e0b;
        }
        .btn-warning:hover {
            background-color: #d97706;
        }
        .alert-success {
            background-color: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background-color: #ef4444;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    </style>
</head>
<body>

<?php require_once dirname(__DIR__) . '/layout/navbar.php'; ?>

<div class="container">
    <h1>Gestione Utenti</h1>

    <?php if (!empty($successo)): ?>
        <div class="alert-success"><?php echo htmlspecialchars($successo, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($errore)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($errore, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="grid-2">
        <div class="card">
            <h2>Crea Nuovo Utente</h2>
            <form action="<?php echo BASE_URL; ?>/user/create" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="ruolo">Ruolo</label>
                    <select id="ruolo" name="ruolo" required>
                        <option value="spotter">Spotter</option>
                        <option value="muretto">Muretto</option>
                        <option value="team_manager">Team Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit">Crea Utente</button>
            </form>
        </div>

        <div class="card">
            <h2>Reset Password</h2>
            <form action="<?php echo BASE_URL; ?>/user/resetPassword" method="POST">
                <div class="form-group">
                    <label for="user_id">Utente</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">-- Seleziona un utente --</option>
                        <?php foreach ($utenti as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($u['ruolo'], ENT_QUOTES, 'UTF-8'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="new_password">Nuova Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn-warning">Reset Password</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h2>Elenco Utenti</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Ruolo</th>
                    <th>Stato</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utenti as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($u['ruolo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $u['attivo'] ? 'Attivo' : 'Disattivo'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
