<?php
$g_id = isset($gara_id) ? $gara_id : (isset($gara['id']) ? $gara['id'] : null);
?>
<nav style="background: #212529; padding: 15px 20px; display: flex; gap: 20px; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.3); margin-bottom: 25px; border-radius: 0 0 8px 8px;">
    <a href="<?php echo BASE_URL; ?>/" style="color: white; text-decoration: none; font-weight: bold; font-size: 1.2em; display:flex; align-items:center; gap:5px;">
        <span style="font-size:1.4em;">&#8962;</span> Home
    </a>
    <?php if ($g_id): ?>
        <div style="width: 2px; height: 24px; background: #495057;"></div>
        <a href="<?php echo BASE_URL; ?>/muretto/index/<?php echo $g_id; ?>" style="color: #17a2b8; text-decoration: none; font-weight: bold; font-size: 1.2em;">Muretto Box</a>
        <a href="<?php echo BASE_URL; ?>/spotter/index/<?php echo $g_id; ?>" style="color: #ffc107; text-decoration: none; font-weight: bold; font-size: 1.2em;">Spotter Pit</a>
    <?php endif; ?>
</nav>
