<?php

use PHPFusion\Infusions\Facebook_Connect\FacebookConnect;

require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/admin_header.php';

$fb = new FacebookConnect();
$fb->displaySettingsAdmin();

require_once THEMES.'templates/footer.php';
