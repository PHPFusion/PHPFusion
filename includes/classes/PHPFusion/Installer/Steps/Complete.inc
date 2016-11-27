<?php
namespace PHPFusion\Steps;

use PHPFusion\Installer\Install_Core;

class InstallerComplete extends Install_Core {

    public function __view() {
        $content = "<div class='m-b-20'><h4>".self::$locale['setup_1600']."</h4> ".self::$locale['setup_1601']."</div>\n";
        $content .= "<div class='well'>".self::$locale['setup_1602']."</div>\n";
        $content .= "<div class='m-b-20'><h4>".self::$locale['setup_1603']."</h4></div>\n";
        $content .= "<hr />\n";
        self::$step = array(
            1 => array(
                'name' => 'step',
                'class' => 'btn-success',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_EXIT
            )
        );

        return $content;
    }

}