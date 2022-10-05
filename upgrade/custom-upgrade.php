<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: custom-upgrade.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Installer\Infusions;

require_once __DIR__.'/../maincore.php';
require_once TEMPLATES.'header.php';

if (!iSUPERADMIN) {
    die('SuperAdmin Access Only');
}

$upgrade_file_path = '9.20.00.upgrade.inc';

class CustomUpgrade extends Infusions {

    public function __construct($filename) {

        $batch = \PHPFusion\Installer\Batch::getInstance();

        if ($upgrade_container = $batch::loadUpgrade(__DIR__, $filename)) {
            $upgrades_found = 0;
            $start = check_post('start');

            if (!empty($upgrade_container)) {
                foreach ($upgrade_container as $callback_method => $upgrades) {
                    if (!empty($upgrades)) {
                        $method = $callback_method.'_infuse';

                        if (is_callable([$this, $method])) {
                            $upgrades_found = $upgrades_found + 1;
                            // will return any error encountered
                            if ($start) {
                                $this->$method([$callback_method => $upgrades]);
                            } else {

                                echo '<kbd class="text-success display-inline-block p-5 mb-3" style="background:rgba(0,0,0,.15);">>> <strong>'.$method.'</strong> -- Finding Entries..</kbd>';
                                echo '<code class="display-block mb-4">';
                                print_p($upgrades);
                                echo '</code><hr/>';
                            }
                        }
                    }
                }

                if ($start) {
                    addnotice('success', '>> Upgrade has been completed.');

                    echo '<kbd class="display-inline-block mb-3">UPGRADE COMPLETED! Please verify that the functions have executed correctly by looking through your new database values.<br/>
                    The actual SQL that was executed during the upgrade is shown as above. You can copy them down for future references.</kbd>';
                } else if (!$upgrades_found) {
                    echo '<kbd class="display-inline-block mb-3">WARNINGS: The upgrade system did not find anything that needs to be upgraded. Upgrade is stopped.</kbd>';
                } else {
                    echo '<kbd class="display-inline-block mb-3">Please check the functions and entries that will be executed during upgrade is correct!</kbd>';
                }

                echo openform('upgradeFrm', 'POST');
                echo '<div class="spacer-sm">';
                echo form_button('start', $start ? "Upgrade Completed" : "Upgrade PHPFusion", 'start', ['class' => 'btn-primary', "deactivate" => $start || !$upgrades_found]);
                if ($start) {
                    echo "<a href='".FUSION_SELF."' class='btn btn-success'>Reboot</a>";
                }
                echo '</div>';
                echo closeform();
            }
        }
    }
}

echo "<div class='container spacer-lg'><div class='jumbotron'>";
echo '<h3 class="mb-4">PHPFusion CMS version. 9 Custom Upgrade</h3>';
echo '<div class="mb-4 text-warning">The custom upgrade console is intended for core developers use only. Please proceed with caution:</div>';
new CustomUpgrade($upgrade_file_path);
echo "</div></div>";

require_once TEMPLATES.'footer.php';
