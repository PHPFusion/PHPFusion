<?php
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/admin_header.php';

$fb = new \PHPFusion\Infusions\Facebook_Connect\Facebook_Connect();
$fb->displaySettingsAdmin();

require_once THEMES.'templates/footer.php';