<nav style="background:#1f2937; color:#f9fafb; padding:12px 16px; display:flex; gap:16px; align-items:center; flex-wrap:wrap; margin-bottom:16px;">
    <a href="<?php echo BASE_URL; ?>/" style="color:#f9fafb; text-decoration:none; font-weight:bold;">🏠 Home</a>
    
    <?php 
    // Determina se siamo nel muretto di un team specifico
    $current_path = $_SERVER['REQUEST_URI'] ?? '';
    $is_muretto_team = strpos($current_path, '/muretto/index/') !== false && isset($gara_id);
    $gara_id_param = htmlspecialchars((string)($gara_id ?? ($gara['id'] ?? '')), ENT_QUOTES, 'UTF-8');
    ?>
    
    <?php if ($gara_id_param): ?>
        <a href="<?php echo BASE_URL; ?>/muretto/multi/<?php echo $gara_id_param; ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">👥 Muretto Multi-Team</a>
        <a href="<?php echo BASE_URL; ?>/spotter/index/<?php echo $gara_id_param; ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">🏁 Spotter Pit</a>
        <a href="<?php echo BASE_URL; ?>/gare/setup/<?php echo $gara_id_param; ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">⚙️ Setup Gara</a>
    <?php endif; ?>
</nav>
