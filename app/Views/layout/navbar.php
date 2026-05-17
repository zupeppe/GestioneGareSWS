<nav style="background:#1f2937; color:#f9fafb; padding:12px 16px; display:flex; gap:16px; align-items:center; flex-wrap:wrap; margin-bottom:16px; justify-content:space-between;">
    <div style="display:flex; gap:16px; align-items:center;">
        <a href="<?php echo BASE_URL; ?>/public/" style="color:#f9fafb; text-decoration:none; font-weight:bold;">🏠 Home</a>
        
        <?php 
        $current_path = $_SERVER['REQUEST_URI'] ?? '';
        $gara_id_param = htmlspecialchars((string)($gara_id ?? ($gara['id'] ?? '')), ENT_QUOTES, 'UTF-8');
        $ruolo = $_SESSION['utente']['ruolo'] ?? null;
        ?>
        
        <?php if ($gara_id_param): ?>
            <a href="<?php echo BASE_URL; ?>/spotter/index/<?php echo $gara_id_param; ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">🏁 Spotter Pit</a>
            
            <?php if (in_array($ruolo, ['admin', 'team_manager', 'muretto'])): ?>
                <a href="<?php echo BASE_URL; ?>/muretto/multi/<?php echo $gara_id_param; ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">👥 Muretto Multi-Team</a>
            <?php endif; ?>
            
            <?php if (in_array($ruolo, ['admin', 'team_manager'])): ?>
                <a href="<?php echo BASE_URL; ?>/gare/setup/<?php echo $gara_id_param; ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">⚙️ Setup Gara</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($ruolo === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>/user/index" style="color:#f9fafb; text-decoration:none; font-weight:bold;">👥 Gestione Utenti</a>
            <a href="<?php echo BASE_URL; ?>/admindati" style="color:#f9fafb; text-decoration:none; font-weight:bold;">🛠️ Gestione Dati</a>
        <?php endif; ?>
    </div>
    
    <div style="display:flex; gap:16px; align-items:center;">
        <?php if (isset($_SESSION['utente'])): ?>
            <span style="color:#9ca3af;">👤 <?php echo htmlspecialchars($_SESSION['utente']['username'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($_SESSION['utente']['ruolo'], ENT_QUOTES, 'UTF-8'); ?>)</span>
            <a href="<?php echo BASE_URL; ?>/auth/logout" style="color:#ef4444; text-decoration:none; font-weight:bold;">🚪 Logout</a>
        <?php endif; ?>
    </div>
</nav>
