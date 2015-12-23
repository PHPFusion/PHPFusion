<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/maintenance.php
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

/**
 * Displays the maintenance page
 * @param array $info - Form fields
 */
if (!function_exists("display_maintenance")) {
    function display_maintenance(array $info)
    {
        ?>
        <section class="maintenance container">
            <?php
            $notices = getNotices();
            if ($notices) echo renderNotices($notices);
            ?>
            <div class="m-t-20 jumbotron text-center">
                <img src='<?php echo fusion_get_settings("sitebanner")?>' alt='<?php echo fusion_get_settings("sitename")?>' />
                <?php
                echo "<h1><strong>".fusion_get_settings("sitename")."</strong></h1>\n";
                $message = fusion_get_settings("maintenance_message");
                if (!empty($message)) {
                    echo "<h1 class='m-b-20'>".stripslashes(nl2br(fusion_get_settings("maintenance_message")))."</h1>\n";
                }
                if (!empty($info)) {
                    ?>
                    <hr/>
                    <div class="well clearfix m-t-20 p-20 p-b-0">
                        <?php echo $info['open_form']; ?>
                        <div class="col-xs-12 col-sm-4">
                            <?php echo $info['user_name']; ?>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <?php echo $info['user_pass']; ?>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <?php echo $info['login_button']; ?>
                        </div>
                    </div>
                    <?php echo $info['close_form'];
                }
                ?>
            </div>
            <div class="text-center">
                <?php echo showcopyright(); ?>
                <?php echo showcounter(); ?>
                <?php echo showMemoryUsage(); ?>
            </div>
        </section>
        <?php
    }
}
