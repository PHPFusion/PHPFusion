<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusions.php
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

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', [ LOCALE . LOCALESET . 'admin/infusions.php' ]);

$aidlink = fusion_get_aidlink();

$contents = [
    'post'     => 'pf_post',
    'view'     => 'pf_view',
    'button'   => 'pf_button',
    'js'       => 'pf_js',
    'settings' => TRUE,
    'link'     => ( $admin_link ?? '' ),
    'title'    => $locale['400'],
    //'description' => $locale['BN_001'],
    'actions'  => [ 'post' => [ 'savesettings' => 'settingsfrm', 'clearcache' => 'cachefrm' ] ],
];

function pf_post() {}

function loadInstalledInfusion()
: array {

    static $infs = [];

    if ( empty($infs) ) {
        $installed_list = [];

        $res = dbquery("SELECT inf_folder FROM " . DB_INFUSIONS);

        if ( dbrows($res) ) {

            while ( $rows = dbarray($res) ) {
                $installed_list[] = $rows['inf_folder'];
            }

            $temp = makefilelist(INFUSIONS, '.|..|index.php', TRUE, 'folders');

            foreach ( $temp as $folders ) {
                if ( ! empty($installed_list) && in_array($folders, $installed_list) ) {
                    $inf = Infusions::loadInfusion($folders);
                    if ( ! empty($inf) ) {
                        $infs[ $folders ] = $inf;
                    }
                }
            }
        }
    }

    return $infs;
}


function load_infusion()
: array {

    $infs = [];
    $temp = makefilelist(APPSTORE . 'infusions/', '.|..|index.php', TRUE, 'folders');
    foreach ( $temp as $folders ) {
        $inf = Infusions::loadInfusion($folders);
        if ( ! empty($inf) ) {
            $infs[ $folders ] = $inf;
        }
    }

    return $infs;
}

function pf_button() {}

function pf_view() {

    $aidlink = fusion_get_aidlink();

    function view_listing() {

        $locale = fusion_get_locale();

        $aidlink = fusion_get_aidlink();

        echo '<h6>Built in integrations</h6>';

        if ( $infusion = loadInstalledInfusion() ) {

            echo '<div class="list-group">';

            foreach ( $infusion as $folder => $inf ) {

                $adminpanel = ! empty($inf['mlt_adminpanel'][ LANGUAGE ][0]) ? $inf['mlt_adminpanel'][ LANGUAGE ][0] : $inf['adminpanel'][0];

                $title = $inf['status'] > 0 ? '<a href="' . INFUSIONS . $inf['folder'] . '/' . $adminpanel['panel'] . $aidlink . '">' . $inf['title'] . '</a>' : $inf['title'];

                $image = '<img class="sys-icon" alt="' . $inf['name'] . '" src="' . $inf['image'] . '">';

                $description = $inf['description'];

                $status = ( $inf['status'] > 0 ? "<span class='label label-success'>" . $locale['415'] . "</span>" : "<span class='label label-default'>" . $locale['414'] . "</span>" );

                $version = ( ! empty($inf['version']) ? $inf['version'] : '' );

                //$button = form_button('infuse', $locale['401'], $inf['folder'], ['class' => 'btn-primary m-t-5 infuse', 'icon' => 'fa fa-magnet', 'input_id' => 'infuse_'.$i]);
                $button = '<a href="' . ADMIN . $aidlink . '&p=I&module=' . $folder . '" class="btn btn-default"><span>Configure & Install<i class="far fa-angle-right m-l-5"></i></span></a>';

                if ( $inf['status'] > 0 ) {

                    if ( $inf['status'] > 1 ) {

                        $button = '<a href="' . ADMIN_CURRENT_DIR . '&module=' . $folder . '" class="badge success text-uppercase">Upgrade available<i class="far fa-angle-right m-l-5"></i></a>';

                    }
                    else {

                        $button = '<a href="' . ADMIN_CURRENT_DIR . '&module=' . $folder . '" class="btn btn-danger"><span>Remove<i class="far fa-angle-right m-l-5"></i></span></a>';

                    }
                }

                $author_link = ( $inf['url'] ? "<a href='" . $inf['url'] . "' target='_blank'>" : "" ) . " " . ( ! empty($inf['developer']) ? $inf['developer'] : $locale['410'] ) . " " . ( $inf['url'] ? "</a>" : "" );

                $author_email = ( $inf['email'] ? "<a href='mailto:" . $inf['email'] . "'>" . $locale['409'] . "</a>" : '' );

                echo '
                <div class="list-group-item">
                    <div class="row equal-height">
                        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-9">           
                            <div class="flex gap-15 ac">' . $image . '<span class="expanded"><strong>' . $title . '</strong><br><small>' . $description . '</small></span></div>                
                        </div><div class="col-xs-12 col-sm-4 col-md-4 col-lg-3 ac">
                            <div class="text-right">' . $button . '</div>
                        </div>            
                    </div>
                </div>
                ';

            }

            echo '</div>';
        }
        else {

            //echo "<div class='list-group'>\n";
            //echo "<div class='list-group-item hidden-xs'>\n";
            //echo "<div class='row'>\n";
            //echo "<div class='col-sm-3 col-md-2 col-lg-2'><strong>".$locale['419']."</strong></div>\n";
            //echo "<div class='col-sm-7 col-md-5 col-lg-3'><strong>".$locale['400']."</strong></div>\n";
            //echo "<div class='col-sm-2 col-md-2 col-lg-2'><strong>".$locale['418']."</strong></div>\n";
            //echo "<div class='hidden-sm col-md-1 col-lg-1'><strong>".$locale['rights']."</strong></div>\n";
            //echo "<div class='hidden-sm col-md-2 col-lg-1'><strong>".$locale['420']."</strong></div>\n";
            //echo "<div class='hidden-sm hidden-md col-lg-3'><strong>".$locale['421']."</strong></div>\n";
            //echo "</div>\n</div>\n";
            echo "<div class='text-center'>" . $locale['417'] . '</div>';

        }
    }

    function view_notes($folder) {

        if ( is_file(INFUSIONS . $folder . '/locale/' . LOCALESET . '/readme.md') ) {

            require_once CLASSES . 'Parsedown.php';
            $file = file(INFUSIONS . $folder . '/locale/' . LOCALESET . '/readme.md');

            $md = new Parsedown();
            $output = '<h6>Release notes</h6>';
            foreach ( $file as $line ) {
                $output .= $md->parse($line);
            }
            echo $output;
        }
    }

    function view_infusion() {

        $aidlink = fusion_get_aidlink();

        if ( $folder = get('module') ) {

            if ( $action = get('action') ) {

                if ( $action == 'install' or $action == 'upgrade' ) {
                    Infusions::getInstance()->infuse($folder);
                }
                else if ( $action == 'defuse' ) {
                    Infusions::getInstance()->defuse($folder);
                }
                else {
                    fusion_stop();
                }

                if ( fusion_safe() ) {
                    cdreset('installed_infusions');
                    cdreset('adminpages');
                    cdreset('infsettings');
                    redirect(ADMIN . $aidlink . '&p=I');
                }

            }

            $current_inf = get_settings($folder);

            $inf = Infusions::loadInfusion($folder);

            // show the page.
            add_breadcrumb([ 'link' => ADMIN_CURRENT_DIR . '&module=' . $folder, 'title' => $inf['title'] ]);

            // Formats image
            $image = '<img class="sys-icon lg" alt="' . $inf['name'] . '" src="' . $inf['image'] . '">';

            // Formats button
            $button = '<a href="' . ADMIN_CURRENT_DIR . '&action=install&module=' . $folder . '" class="btn btn-success"><span>Install</span></a>';

            if ( $inf['status'] > 0 ) {

                if ( $inf['status'] > 1 ) {

                    $button = '<a href="' . ADMIN_CURRENT_DIR . '&action=upgrade&module=' . $folder . '"  class="btn btn-inverse"><span>Upgrade to version ' . $inf['version'] . '</span></a>';

                }
                else {

                    $button = '<a href="' . ADMIN_CURRENT_DIR . '&action=uninstall&module=' . $folder . '" class="btn btn-danger"><span>Remove</span></a>';

                }
            }

            // Formats current installed version
            $installed_version = 'Not installed';
            if ( ! empty($current_inf['version']) ) {
                $version = explode('.', $current_inf['version']);
                $installed_version = ( $version[0] ?? '0' );
                $installed_version .= '.' . ( $version[1] ?? '0' );
                $installed_version .= '.' . ( $version[2] ?? '0' );
            }

            // Supported
            $multilang_support = ( $inf['mlt'] ? '<span class="badge success">Supported</span>' : '<span class="badge">No</span>' );

            echo '
            <div class="infusion-module">
             <div class="flex gap-15 acs">' . $image . '<div class="display-block w-100">
            <h4 class="m-b-5">' . $inf['title'] . '</h4>' . $inf['description'] . '
            <div class="spacer-md"><dl>
                <dt>Author</dt><dd>' . $inf['developer'] . '</dd>
                <dt>Version</dt><dd>' . $inf['version'] . '</dd>
                <dt>Current Installed</dt><dd><span class="badge">' . $installed_version . '</span></dd>
                <dt>Multilingual Support</dt><dd>' . $multilang_support . '</dd>
            </dl></div></div>
            ' . $button . '
            </div></div>
            ';

            view_notes($folder);

        }
        else {
            admin_exit();
        }

    }


    if ( check_get('module') ) {

        view_infusion();
    }
    else {

        view_listing();
    }


}

function pf_js() {

    $locale = fusion_get_locale();

    return "$('.defuse').bind('click', function() {return confirm('" . $locale['412'] . "');});";
}

echo "<div class='text-right m-b-20'><a href='https://phpfusion.com/infusions/marketplace/' title='" . $locale['422'] . "' target='_blank'>" . $locale['422'] . "</a></div>";
