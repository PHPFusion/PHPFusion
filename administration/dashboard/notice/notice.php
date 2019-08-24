<?php
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
