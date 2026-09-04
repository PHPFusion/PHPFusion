<?php
/** Member-only Dynamics UI reference. This page never saves example values. */
require_once __DIR__.'/maincore.php';
if (!iMEMBER) {
    redirect(BASEDIR.'login.php');
}
require_once THEMES.'templates/header.php';
add_to_title('Dynamics UI');
fusion_load_script(DYNAMICS.'showcase/showcase.css', 'css');
fusion_load_script(DYNAMICS.'showcase/showcase.js');
require DYNAMICS.'showcase/view.php';
require_once THEMES.'templates/footer.php';
