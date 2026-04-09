<?php

use PHPFusion\Infusions\Profile_Home\ProfileAdmin;

require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/admin_header.php';
$pf = new ProfileAdmin();
$pf->viewAdmin();

require_once THEMES.'templates/footer.php';
