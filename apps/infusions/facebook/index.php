<?php


use PHPFusion\Infusions\Facebook\Facebook_Connect;

require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/admin_header.php';

$fb = new Facebook_Connect();
$fb->displaySettingsAdmin();

require_once THEMES.'templates/footer.php';
