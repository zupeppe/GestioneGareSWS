<nav style="background:#1f2937; color:#f9fafb; padding:12px 16px; display:flex; gap:16px; align-items:center; flex-wrap:wrap; margin-bottom:16px;">
    <a href="<?php echo BASE_URL; ?>/" style="color:#f9fafb; text-decoration:none; font-weight:bold;">Home</a>
    <a href="<?php echo BASE_URL; ?>/muretto/index/<?php echo htmlspecialchars((string)($gara_id ?? ($gara['id'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">Muretto</a>
    <a href="<?php echo BASE_URL; ?>/spotter/index/<?php echo htmlspecialchars((string)($gara_id ?? ($gara['id'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>" style="color:#f9fafb; text-decoration:none; font-weight:bold;">Spotter Pit</a>
</nav>
