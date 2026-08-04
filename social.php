<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: social.php
| Author: Core Development Team
+--------------------------------------------------------*/

require_once __DIR__.'/maincore.php';

if (!iMEMBER || !db_exists(DB_SOCIAL)) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';

(new PHPFusion\Social\SocialController((int) fusion_get_userdata('user_id')))->display();

require_once THEMES.'templates/footer.php';
