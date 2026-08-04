<?php

use PHPFusion\Core\Profile\PublicProfileEngine;

require_once __DIR__ . '/maincore.php';

if (!defined('iMEMBER') || !iMEMBER) {
    redirect(BASEDIR . 'index.php');
}

add_to_title('Edit public profile');
fusion_load_script(BASEDIR . 'assets/css/core-profile.css', 'css');
fusion_load_script(BASEDIR . 'assets/css/profile-global-sonner.css', 'css');
fusion_load_script(BASEDIR . 'assets/js/profile-global-sonner.js', 'js');
fusion_load_script(BASEDIR . 'assets/js/core-profile.js', 'js');

require_once THEMES . 'templates/header.php';

(new PublicProfileEngine())->renderEditor();

require_once THEMES . 'templates/footer.php';
