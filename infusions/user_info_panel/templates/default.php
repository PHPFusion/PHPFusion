<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_info_panel/templates/default.php
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
 * Default template for User Info Panel
 */
if (!function_exists('display_user_info_panel')) {
    /**
     * Default User Info Panel Template
     *
     * @param array $info
     */
    function display_user_info_panel(array $info = array()) {
        if (iMEMBER) : ?>

            {%openside%}
            <!---member-->
            <div class='clearfix'>
                <div class='uip_avatar text-center'>
                    <div class='pull-left m-r-10'>{%user_avatar%}</div>
                </div>
                <h4 class='uip_username'><strong>{%user_name%}</strong></h4>
                <span>{%user_level%}</span><br/>
                <span>{%user_reputation_icon%}{%user_reputation%}</span>
            </div>
            <div class='user_pm_notice'>{%user_pm_notice%}</div>
            <div class='user_pm_progressbar'>{%user_pm_progressbar%}</div>
            <!-- <div class='submissions m-t-10'>{%submit%}</div> -->
            <div id='navigation-user' class="m-t-10">
                <strong>{%user_nav_title%}</strong><br/>
                <ul class='block'>
                    <li><a href='{%edit_profile_link%}'>{%edit_profile_title%} <i class='pull-right fa fa-user-circle-o fa-pull-right'></i></a></li>
                    <li><a href='{%pm_link%}'>{%pm_title%} <i class='fa fa-envelope-o fa-pull-right'></i></a></li>
                    <?php if ($info['forum_exists']) : ?>
                        <li><a href='{%track_link%}'>{%track_title%} <i class='fa fa-commenting-o fa-pull-right'></i></a></li>
                    <?php endif; ?>
                    <li><a href='{%member_link%}'>{%member_title%} <i class='fa fa-users fa-pull-right'></i></a></li>
                    <?php if (iADMIN) : ?>
                        <li><a href='{%acp_link%}'>{%acp_title%} <i class='fa fa-dashboard fa-pull-right'></i></a></li>
                    <?php endif; ?>
                </ul>
                <?php if (!empty($info['submissions'])) : ?>
                <ul class='block'>
                    <li>
                        <a data-toggle='collapse' data-parent='#navigation-user' href='#collapse'><?php echo fusion_get_locale('UM089') ?> <i class='fa fa-cloud-upload pull-right'></i></a>
                            <ul id='collapse' class='panel-collapse collapse block m-l-10'>
                                <?php
                                foreach ($info['submissions'] as $modules) {
                                    ?>
                                    <li>
                                        <a class='side pl-l-15' href='<?php echo $modules['link'] ?>'><?php echo $modules['title'] ?></a>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
            <div class='spacer-xs'><a class='btn btn-block btn-primary' href='{%logout_link%}'>{%logout_title%}</a></div>
            {%closeside%}

        <?php else : ?>

            <!---guest-->
            {%openside%}
            <div class='spacer-xs'>
                {%login_name_field%}
                {%login_pass_field%}
                {%login_remember_field%}
                {%login_submit%}
                <div class='spacer-xs'>{%registration_%}</div>
                <div class='spacer-xs'>{%lostpassword_%}</div>
            </div>
            {%closeside%}

        <?php endif;
    }
}
