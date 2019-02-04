<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: loginPanel.php
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
namespace Artemis\Viewer;

use Artemis\Model\Resource;

class loginPanel extends resource {

    function __construct() {

        parent::__construct();

        $locale = self::get_locale();

        $aidlink = self::get_aidlink();

        $userdata = self::get_userdata();

        echo renderNotices(getNotices(['all', FUSION_SELF]));
        ?>
        <section id="devlpr" class="login_page">
            <div class="login_bg">
                <div class="login_wrapper">
                    <div class="login_logo">
                        <img alt="<?php echo fusion_get_settings("sitename") ?>"
                             src="<?php echo IMAGES."php-fusion-logo.png" ?>"/>

                        <h2>Artemis</h2>

                        <h3><?php echo $locale['280'] ?></strong></h3>
                    </div>
                    <div class="login_panel">
                        <?php
                        if (!\defender::safe()) {
                            setNotice('danger', $locale['global_182']);
                        }

                        $form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_REQUEST; //FUSION_SELF."?".FUSION_QUERY;
                        echo openform('admin-login-form', 'post', $form_action);

                        ?>
                        <div class="clearfix m-b-20">
                            <div class="pull-left m-r-10">
                                <?php echo display_avatar($userdata, '70px', "", FALSE, "img-rounded"); ?>
                            </div>
                            <div class="overflow-hide">
                                <table>
                                    <tr>
                                        <td class="p-l-15 va-middle text-left">
                                            <h2><?php echo $locale['welcome'].", ".$userdata['user_name']; ?></h2>
                                            <?php echo getuserlevel($userdata['user_level']) ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="text-left">
                            <?php
                            echo form_text('admin_password', $locale['global_102'], "", [
                                'callback_check'   => 'check_admin_pass',
                                'placeholder'      => $locale['281'],
                                'autocomplete_off' => 1,
                                'type'             => 'password',
                                'required'         => TRUE
                            ]);
                            echo form_button('admin_login', $locale['login'], 'Sign in',
                                ['class' => 'btn-primary btn-block']);
                            echo closeform();
                            ?>
                        </div>
                    </div>

                    <small class='text-alt'><?php echo $locale['version'].fusion_get_settings('version') ?></small>
                    <div class="copyright-note clearfix m-t-10">
                        <?php echo showcopyright() ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}
