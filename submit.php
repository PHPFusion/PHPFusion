<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submit.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Admins;

require_once __DIR__.'/maincore.php';
if ( !iMEMBER ) {
    redirect( BASEDIR.'index.php' );
}
require_once THEMES.'templates/header.php';
$modules = Admins::getInstance()->getSubmitData();

$stype = get( 'stype' );
if ( !isset( $modules[ $stype ] ) ) {
    $stype = '';
}
if ( !empty( $modules ) && $stype ) {
    require_once $modules[ $stype ]['link'];
} else {
    redirect( BASEDIR.'index.php' );
}

require_once THEMES.'templates/footer.php';
