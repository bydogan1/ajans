    <!-- Favicon -->
    <?php if (!empty($settings['favicon'])): ?>
    <link rel="icon" href="<?php echo SITE_URL . '/uploads/settings/' . $settings['favicon']; ?>" type="image/x-icon">
    <?php else: ?>
    <link rel="icon" href="<?php echo SITE_URL; ?>/favicon.ico" type="image/x-icon">
    <?php endif; ?> 