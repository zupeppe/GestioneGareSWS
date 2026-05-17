<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Dati - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        .admin-section { margin-bottom: 40px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .admin-section h2 { margin-top: 0; color: #0056b3; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #343a40; color: white; }
        .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-danger:hover { background: #c82333; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        form.inline-form { margin: 0; padding: 0; display: inline-block; }
    </style>
</head>
<body style="background-color: #f4f6f9;">
    <?php require_once BASE_PATH . '/app/Views/layout/navbar.php'; ?>
    
    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 20px;">
        <h1 style="margin-bottom: 30px;">🛠️ Gestione Dati di Sistema (Admin)</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <h2>Gare</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nome Gara</th>
                        <th>Data Evento</th>
                        <th>Stato</th>
                        <th style="width: 100px; text-align: center;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gare as $gara): ?>
                        <tr>
                            <td><?php echo (int)$gara['id']; ?></td>
                            <td><?php echo htmlspecialchars($gara['nome_gara']); ?></td>
                            <td><?php echo htmlspecialchars($gara['data_evento']); ?></td>
                            <td>
                                <?php if ($gara['stato'] === 'setup'): ?>
                                    <span style="background: #6c757d; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.85em;">Setup</span>
                                <?php elseif ($gara['stato'] === 'in_corso'): ?>
                                    <span style="background: #28a745; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.85em;">In Corso</span>
                                <?php else: ?>
                                    <span style="background: #dc3545; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.85em;">Finita</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <form action="<?php echo BASE_URL; ?>/admindati/eliminagara/<?php echo (int)$gara['id']; ?>" method="POST" class="inline-form" onsubmit="return confirm('Azione irreversibile. Procedere con l\'eliminazione della gara? Tutti i dati collegati (stint, piloti gara, iscritti, ecc.) verranno persi.');">
                                    <button type="submit" class="btn-danger">Elimina</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($gare)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #6c757d;">Nessuna gara presente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h2>Team (Scuderie e Avversari)</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Nome Team</th>
                        <th style="width: 100px; text-align: center;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?php echo (int)$team['id']; ?></td>
                            <td><?php echo htmlspecialchars($team['nome_team']); ?></td>
                            <td style="text-align: center;">
                                <?php if (!empty($team['cancellato'])): ?>
                                    <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold;">Cancellato</span>
                                <?php else: ?>
                                    <form action="<?php echo BASE_URL; ?>/admindati/eliminateam/<?php echo (int)$team['id']; ?>" method="POST" class="inline-form" onsubmit="return confirm('Azione irreversibile. Procedere con la disattivazione del team?');">
                                        <button type="submit" class="btn-danger">Elimina</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($teams)): ?>
                        <tr><td colspan="3" style="text-align: center; color: #6c757d;">Nessun team presente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h2>Piloti (Mio Team)</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Cognome</th>
                        <th>Nome</th>
                        <th style="width: 100px; text-align: center;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($piloti as $pilota): ?>
                        <tr>
                            <td><?php echo (int)$pilota['id']; ?></td>
                            <td><?php echo htmlspecialchars($pilota['cognome']); ?></td>
                            <td><?php echo htmlspecialchars($pilota['nome']); ?></td>
                            <td style="text-align: center;">
                                <?php if (!empty($pilota['cancellato'])): ?>
                                    <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold;">Cancellato</span>
                                <?php else: ?>
                                    <form action="<?php echo BASE_URL; ?>/admindati/eliminapilota/<?php echo (int)$pilota['id']; ?>" method="POST" class="inline-form" onsubmit="return confirm('Azione irreversibile. Procedere con la disattivazione del pilota?');">
                                        <button type="submit" class="btn-danger">Elimina</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($piloti)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #6c757d;">Nessun pilota presente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
