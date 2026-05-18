<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche Gara</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #111827;
            color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header-title {
            color: #f3f4f6;
            margin-top: 0;
        }
        .tabs {
            display: flex;
            border-bottom: 2px solid #374151;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            color: #9ca3af;
            border-bottom: 3px solid transparent;
        }
        .tab.active {
            color: #3b82f6;
            border-bottom: 3px solid #3b82f6;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .card {
            background-color: #1f2937;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #374151;
        }
        th {
            background-color: #374151;
            color: #d1d5db;
        }
        .rating-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.9em; min-width: 60px; text-align: center; border: 1px solid black; }
        .rating-0 { background: #e9ecef; color: #000; }
        .rating-1 { background: #dc3545; color: #fff; }
        .rating-2 { background: #ffc107; color: #000; }
        .rating-3 { background: #28a745; color: #fff; }
        .rating-5 { background: #6b21a8; color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.8); }

        <?php
        function getRatingBadge($rating) {
            $r = (int)$rating;
            $class = 'rating-0'; $text = 'Ignoto';
            if ($r === 1) { $class = 'rating-1'; $text = 'Scarso'; }
            elseif ($r === 2) { $class = 'rating-2'; $text = 'Medio'; }
            elseif ($r === 3) { $class = 'rating-3'; $text = 'Buono'; }
            elseif ($r === 5) { $class = 'rating-5'; $text = 'Best Lap'; }
            return "<span class=\"rating-badge $class\">$text</span>";
        }
        
        function formatDuration($inizio, $fine) {
            if ($fine === 'In Corso') return 'In Corso';
            $t1 = strtotime($inizio);
            $t2 = strtotime($fine);
            $diff = $t2 - $t1;
            if ($diff < 0) return "Errore Date";
            $h = floor($diff / 3600);
            $m = floor(($diff % 3600) / 60);
            $s = $diff % 60;
            return sprintf("%02d:%02d:%02d", $h, $m, $s);
        }
        ?>
    </style>
</head>
<body>

<?php require_once dirname(__DIR__) . '/layout/navbar.php'; ?>

<div class="container">
    <h1 class="header-title">Statistiche Gara: <?php echo htmlspecialchars($gara['nome_gara']); ?></h1>

    <div class="tabs">
        <div class="tab active" onclick="switchTab(event, 'team')">Ricerca per Team</div>
        <div class="tab" onclick="switchTab(event, 'kart')">Ricerca per Kart</div>
    </div>

    <!-- TAB TEAM -->
    <div id="tab-team" class="tab-content active">
        <?php if (empty($stintsByTeam)): ?>
            <div class="card">Nessun dato registrato.</div>
        <?php else: ?>
            <?php foreach ($stintsByTeam as $teamNome => $stints): ?>
            <div class="card">
                <h2 style="margin-top:0; color:#60a5fa;"><?php echo htmlspecialchars($teamNome); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Kart Usato</th>
                            <th>Inizio Stint</th>
                            <th>Fine Stint</th>
                            <th>Durata</th>
                            <th>Rating al rilascio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stints as $s): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($s['numero_kart']); ?></strong></td>
                            <td><?php echo date('H:i:s', strtotime($s['inizio'])); ?></td>
                            <td><?php echo $s['fine'] === 'In Corso' ? '<span style="color:#10b981;">In Corso</span>' : date('H:i:s', strtotime($s['fine'])); ?></td>
                            <td><?php echo formatDuration($s['inizio'], $s['fine']); ?></td>
                            <td><?php echo getRatingBadge($s['rating']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- TAB KART -->
    <div id="tab-kart" class="tab-content">
        <?php if (empty($stintsByKart)): ?>
            <div class="card">Nessun dato registrato.</div>
        <?php else: ?>
            <div class="card" style="margin-bottom: 20px;">
                <label for="kartSelector"><strong>Seleziona Kart:</strong></label>
                <select id="kartSelector" onchange="filterKart()" style="padding: 10px; border-radius: 4px; background: #374151; color: white; border: none; font-size: 1.1em; margin-left: 10px;">
                    <option value="all">Tutti i Kart</option>
                    <?php foreach (array_keys($stintsByKart) as $nk): ?>
                        <option value="kart-box-<?php echo htmlspecialchars($nk); ?>">Kart N° <?php echo htmlspecialchars($nk); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php foreach ($stintsByKart as $numeroKart => $stints): ?>
            <div class="card kart-box" id="kart-box-<?php echo htmlspecialchars($numeroKart); ?>">
                <h2 style="margin-top:0; color:#f59e0b;">Storico Kart N° <?php echo htmlspecialchars($numeroKart); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Guidato da (Team)</th>
                            <th>Da (Ora)</th>
                            <th>A (Ora)</th>
                            <th>Durata</th>
                            <th>Rating Rilevato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stints as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['team_nome']); ?></td>
                            <td><?php echo date('H:i:s', strtotime($s['inizio'])); ?></td>
                            <td><?php echo $s['fine'] === 'In Corso' ? '<span style="color:#10b981;">In Corso</span>' : date('H:i:s', strtotime($s['fine'])); ?></td>
                            <td><?php echo formatDuration($s['inizio'], $s['fine']); ?></td>
                            <td><?php echo getRatingBadge($s['rating']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function switchTab(event, tabId) {
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

function filterKart() {
    let selector = document.getElementById('kartSelector');
    let selectedId = selector.value;
    
    document.querySelectorAll('.kart-box').forEach(box => {
        if (selectedId === 'all') {
            box.style.display = 'block';
        } else {
            if (box.id === selectedId) {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
    });
}
</script>

</body>
</html>
