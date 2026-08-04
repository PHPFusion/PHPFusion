<?php

use PHPFusion\Core\Profile\PublicProfileEngine;

require_once __DIR__ . '/maincore.php';

$lookup = (int)get('lookup', FILTER_VALIDATE_INT);
if ($lookup <= 0) {
    redirect(BASEDIR . 'index.php');
}

add_to_title('Member profile');
fusion_load_script(BASEDIR . 'assets/css/core-profile.css', 'css');
fusion_load_script(BASEDIR . 'assets/css/profile-global-sonner.css', 'css');
fusion_load_script(BASEDIR . 'assets/js/profile-global-sonner.js', 'js');

require_once THEMES . 'templates/header.php';

if (!(new PublicProfileEngine())->renderPublic($lookup)) {
    addnotice('danger', 'This profile is not available.');
    redirect(BASEDIR . 'index.php');
}

require_once THEMES . 'templates/footer.php';
