<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.loginpanel.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Inspire;

/**
 * Class Viewer
 *
 * @package Inspire
 */
class LoginPanel extends Helper {

    private static $breadcrumb_shown = FALSE;

    public function __construct() {

        parent::__construct();

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $userdata = fusion_get_userdata();

        echo renderNotices(getNotices(['all', FUSION_SELF]));
        if (!fusion_safe()) {
            setNotice('danger', $locale['global_182']);
        }
        $form_action = (FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_REQUEST);

        $info = [
            'form'      => [
                'openform'       => openform('adminloginform', 'post', $form_action),
                'closeform'      => closeform(),
                'admin_password' => form_text('admin_password', '', '', [
                    'callback_check'   => 'check_admin_pass',
                    'placeholder'      => $locale['281'],
                    'autocomplete_off' => 1,
                    'type'             => 'password',
                    'required'         => TRUE
                ]),
                'admin_remember' => form_checkbox('admin_remember', 'Remember me', '', [
                    'reverse_label' => TRUE,
                ]),
                'submit'         => form_button('admin_login', $locale['login'], 'Sign in', ['class' => 'btn-primary'])
            ],
            'version'   => $locale['version'].fusion_get_settings('version'),
            'locale'    => $locale,
            'aidlink'   => fusion_get_aidlink(),
            'copyright' => showcopyright(),
        ];
        echo fusion_render(THEMES.'admin_themes/Inspire/templates/', 'login.twig', $info, TRUE);
    }

    public function adminPanel() {

        $this->do_interface_js();

        $collapsed = isset($_COOKIE['acpState']) && $_COOKIE['acpState'] == 0 ? ' collapsed' : '';
        ?>
        <section id="devlpr" class="adminPanel">
            <div class="left_menu collapsed">
                <div>
                    <div class="menu-icon">
                    </div>
                </div>
                <div class="menu">
                    <?php $this->left_nav(); ?>
                </div>
            </div>
            <div class="app_menu<?php echo $collapsed; ?>">
                <div class="app_list">
                    <?php $this->app_nav() ?>
                </div>
            </div>

            <div id="main_content" class="content<?php echo $collapsed; ?>">
                <div class="sub_menu">
                    <?php $this->display_admin_pages() ?>
                </div>
                <!--				<header class="header affix mm-collapsed" data-spy="affix" data-offset-top="10">-->
                <header class="header mm-collapsed">
                    <?php $this->adminHeader() ?>
                </header>
                <div class="content mm-collapsed">
                    <?php
                    $notices = getNotices();
                    if (!empty($notices)) :
                        ?>
                        <div class="admin-notices">
                            <?php echo renderNotices($notices); ?>
                        </div>
                    <?php
                    endif;
                    echo CONTENT; ?>
                    <div class="copyright-wrapper">
                        <div class="copyright">
                            <?php echo showcopyright('', TRUE) ?>
                        </div>
                    </div>
                </div>
                <span class="main_content_overlay"></span>
            </div>
        </section>
        <footer>
            <ul>
                <li>Genesis Admin Theme by PHPFusion CMS.</li>
                <?php
                $errors = showFooterErrors();
                if ($errors) {
                    echo "<li>".$errors."</li>\n";
                }
                ?>
                <?php
                if (fusion_get_settings("rendertime_enabled")) : ?>
                    <li><?php echo showrendertime() ?></li>
                    <li><?php echo showMemoryUsage() ?></li>
                    <li><?php echo self::$locale['copyright'].showdate("%Y", time())." - ".fusion_get_settings("sitename") ?></li>
                <?php endif; ?>
                <li class="pull-right">PHP-Fusion CMS v.<?php echo fusion_get_settings('version') ?></li>
            </ul>
        </footer>
        <?php
    }

    /**
     * Javascript for Interface
     */
    private function do_interface_js() {
        //add_to_jquery("
        //$('#search_app').bind('keyup', function(e) {
        //    var data = {
        //        'appString' : $(this).val(),
        //        'mode' : 'html',
        //        'url' : '".$_SERVER['REQUEST_URI']."',
        //    };
        //    var sendData = $.param(data);
        //    $.ajax({
        //        url: '".THEMES."admin_themes/Genesis/acp_request.php".$this->get_aidlink()."',
        //        dataType: 'html',
        //        method : 'get',
        //        type: 'json',
        //        data: sendData,
        //        success: function(e) {
        //            $('.app_page_list').hide();
        //            $('#main_content').addClass('open');
        //            $('ul#app_search_result').html(e).show();
        //        },
        //        error : function(e) {
        //            console.log('fail');
        //        }
        //    });
        //});
        //
        //
        //");

    }

}
