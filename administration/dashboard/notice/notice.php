<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: notice.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

function display_notice_widget() {
    // do admin notices
    $adn = \PHPFusion\Installer\Batch_Core::getInstance()->getUpgradeNotice();
    if (!empty($adn)) {
        foreach($adn as $irights => $arn) {
            echo alert('<strong>'.$arn['title'].' Infusion.</strong> '.$arn['description'].'<div id="'.$irights.'-upi" class="m-t-5">'.openform('upgradeFrm', 'post').
                form_button('upgrade_infusion', 'Upgrade Infusion', $irights, ['class'=>'btn-primary']).
                closeform().'</div>', ['class'=>'']);
        }
    }
}

fusion_add_hook("dashboard_widgets", "admin_notices");
