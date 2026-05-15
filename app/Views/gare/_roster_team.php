<?php 
// Passa la variabile gara_id dal contesto del controller
$gara_id = isset($gara_id) ? $gara_id : (isset($_GET['gara_id']) ? (int)$_GET['gara_id'] : 0);
?>

<?php if (empty($teamGestiti)): ?>
    <p style="font-size: 0.9em; color: #6c757d;">Nessun team configurato. Configura almeno un team per visualizzare il roster.</p>
<?php else: ?>
    <?php 
    // Raggruppa piloti per team
    $pilotiPerTeam = [];
    $pilotiSenzaTeam = [];
    
    foreach ($pilotiRoster as $pilota) {
        if ($pilota['team_id'] && $pilota['nome_team']) {
            $teamId = $pilota['team_id'];
            if (!isset($pilotiPerTeam[$teamId])) {
                $pilotiPerTeam[$teamId] = [
                    'nome_team' => $pilota['nome_team'],
                    'piloti' => []
                ];
            }
            $pilotiPerTeam[$teamId]['piloti'][] = $pilota;
        } else {
            $pilotiSenzaTeam[] = $pilota;
        }
    }
    ?>
    
    <?php foreach ($teamGestiti as $team): ?>
        <div class="team-roster-section" style="margin-bottom: 25px; border: 1px solid #ddd; border-radius: 5px; padding: 15px; background: #f8f9fa;">
            <h3 style="margin: 0 0 15px 0; color: #0056b3; font-size: 1.2em;">
                🏁 <?php echo htmlspecialchars($team['nome_team']); ?>
            </h3>
            
            <?php 
            $teamId = $team['team_id'];
            $pilotiTeam = isset($pilotiPerTeam[$teamId]) ? $pilotiPerTeam[$teamId]['piloti'] : [];
            ?>
            
            <?php if (empty($pilotiTeam)): ?>
                <p style="font-size: 0.9em; color: #6c757d; margin: 0;">Nessun pilota assegnato a questo team.</p>
            <?php else: ?>
                <table style="width: 100%; margin: 0; background: white;">
                    <thead>
                        <tr>
                            <th style="padding: 8px; text-align: left;">Pilota</th>
                            <th style="padding: 8px; text-align: center; width: 100px;">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pilotiTeam as $pilota): ?>
                            <tr id="pilota-row-<?php echo (int)$pilota['id']; ?>" 
                                data-associazione-id="<?php echo (int)$pilota['id']; ?>" 
                                data-pilota-id="<?php echo (int)$pilota['pilota_id']; ?>" 
                                data-nome-pilota="<?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                                <td style="padding: 8px;">
                                    <?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <button type="button" class="btn btn-danger btn-sm js-rimuovi-pilota" 
                                            data-url="<?php echo BASE_URL; ?>/gare/rimuoviPilotaGara/<?php echo (int)$pilota['id']; ?>/<?php echo (int)$gara_id; ?>"
                                            style="padding: 4px 8px; font-size: 0.8em;">
                                        Rimuovi
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <?php if (!empty($pilotiSenzaTeam)): ?>
        <div class="team-roster-section" style="margin-bottom: 25px; border: 1px solid #dc3545; border-radius: 5px; padding: 15px; background: #f8d7da;">
            <h3 style="margin: 0 0 15px 0; color: #721c24; font-size: 1.2em;">
                Piloti senza team
            </h3>
            <p style="font-size: 0.9em; margin: 0 0 10px 0; color: #721c24;">
                Questi piloti non sono assegnati a nessun team gestito:
            </p>
            <table style="width: 100%; margin: 0; background: white;">
                <thead>
                    <tr>
                        <th style="padding: 8px; text-align: left;">Pilota</th>
                        <th style="padding: 8px; text-align: center; width: 100px;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pilotiSenzaTeam as $pilota): ?>
                        <tr id="pilota-row-<?php echo (int)$pilota['id']; ?>" 
                            data-associazione-id="<?php echo (int)$pilota['id']; ?>" 
                            data-pilota-id="<?php echo (int)$pilota['pilota_id']; ?>" 
                            data-nome-pilota="<?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td style="padding: 8px;">
                                <?php echo htmlspecialchars($pilota['cognome'] . ' ' . $pilota['nome']); ?>
                            </td>
                            <td style="padding: 8px; text-align: center;">
                                <button type="button" class="btn btn-danger btn-sm js-rimuovi-pilota" 
                                        data-url="<?php echo BASE_URL; ?>/gare/rimuoviPilotaGara/<?php echo (int)$pilota['id']; ?>/<?php echo (int)$gara_id; ?>"
                                        style="padding: 4px 8px; font-size: 0.8em;">
                                    Rimuovi
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <?php if (empty($pilotiRoster)): ?>
        <p style="font-size: 0.9em; color: #6c757d; margin: 10px 0;">Nessun pilota nel roster.</p>
    <?php endif; ?>
<?php endif; ?>
