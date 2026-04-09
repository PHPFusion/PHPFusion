<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bbcodes.php
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

$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'admin/bbcodes.php']);

$aidlink = fusion_get_aidlink();

$contents = [
    'post'     => 'pf_post',
    'view'     => 'pf_view',
    'button'   => 'pf_button',
    'js'       => 'pf_js',
    'settings' => TRUE,
    'link'     => ($admin_link ?? ''),
    'title'    => 'Content Editor',
    //'description' => $locale['BN_001'],
    'actions'  => ['post' => ['savesettings', 'clearcache'], 'post_form' => 'settingsform'],
];

function pf_post() {

}




function display_bbcode() {

    $locale = fusion_get_locale();

    /**
     * @param $bbcode
     *
     * @return array
     */
    function load_bbcode($bbcode): array {

        $settings = fusion_get_settings();

        $aidlink = fusion_get_aidlink();

        $check_path = __DIR__.'/../includes/bbcodes/images/';

        $cached_bbcodes = cache_bbcode();

        $bbcodes = [];

        $_BBCODES_ = [];

        if (preg_match("/_include.php/i", $bbcode)) {

            $bbcode = explode("_", $bbcode)[0];

            if (is_file(INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php")) {

                // Status
                $enabled = in_array($bbcode, $cached_bbcodes);

                if (is_file(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {

                    $locale = fusion_get_locale('', LOCALE.LOCALESET."bbcodes/".$bbcode.".php");

                } else if (is_file(LOCALE."English/bbcodes/".$bbcode.".php")) {

                    $locale = fusion_get_locale('', LOCALE."English/bbcodes/".$bbcode);
                }

                // Image
                $img_path = INCLUDES.'bbcodes/images/';

                $bbcode_attr = ['.svg', '.png', '.gif', '.jpg'];

                $bbcode_image = '-';

                foreach ($bbcode_attr as $attr) {

                    if (is_file($img_path.$bbcode.$attr)) {

                        $bbcode_image = "<img src='".$img_path.$bbcode.$attr."' alt='".$bbcode."' title='".$bbcode."' style='border:0; ".($attr == '.svg' ? 'width: 24px; height: 24px;' : '')."' />\n";

                        break;
                    }
                }


                include INCLUDES."bbcodes/".$bbcode."_bbcode_include_var.php";

                // Ordering
                $enabled_bbcodes = array_flip($cached_bbcodes);

                $bbcodes[$enabled_bbcodes[$bbcode] ?? bbcode_order($enabled_bbcodes)] = [
                    'title'       => ucwords($bbcode),
                    'image'       => $bbcode_image,
                    'description' => $__BBCODE__[0]['description'] ?? 'N/A',
                    'usage'       => $__BBCODE__[0]['usage'] ?? 'N/A',
                    'enabled'     => $enabled,
                    'link_url'    => FUSION_SELF.$aidlink.'&action='.($enabled ? 'disable' : 'enable').'&bbcode='.$bbcode,
                    'link_title'  => $enabled ? 'Disable' : 'Enable',
                ];

            }

        }

        return $bbcodes;

    }

    /**
     * @param array $value
     *
     * @return int|mixed
     */
    function bbcode_order(array $value) {
        static $count;
        if (!$count) {
            $count = count($value);
        }
        $count++;

        return $count;


    }

    $data = [];
    if (!empty($bbcode_folder = makefilelist(INCLUDES."bbcodes/", '.|..|index.php|.js'))) {

        foreach ($bbcode_folder as $bbcode) {
            if ($bbcode = load_bbcode($bbcode)) {
                $data += $bbcode;
            };
        }

        if (!empty($data)) {
            ksort($data);

            echo '<table id="'.fusion_table('bbcode', [
                'ordering'=>FALSE,
                ]).'" class="table"><thead><tr>';
            echo '<th>Status</th>';
            echo '<th>'.$locale['BBCA_403'].'</th>';
            echo '<th>'.$locale['BBCA_405'].'</th>';
            echo '<th>'.$locale['BBCA_406'].'</th>';
            echo '<th>'.$locale['BBCA_407'].'</th>';

            echo '</tr></thead><tbody>';

            foreach ($data as $order => $cdata) {

                echo '<tr>';
                echo '<td><a class="btn btn-'.($cdata['enabled'] == TRUE ? 'default': 'primary').'" href="'.$cdata['link_url'].'"><span>'.$cdata['link_title'].'</span></a></td>';
                echo '<td><div style="display:flex;gap:15px;">'.$cdata['image'].'<span>'.$cdata['title'].'</span></div></td>';
                echo '<td>'.$cdata['description'].'</td>';
                echo '<td>'.$cdata['usage'].'</td>';
                echo '<td>'.($order + 1).'</td>';
                echo '</tr>';

            }

            echo '</tbody></table>';
        }

    }


}

function pf_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    opentable('Content Editor');

    openside(); // html or markdown
    echo form_checkbox('bbcode_enabled', 'Enable BBcodes', $settings['bbcode_enabled'], ['toggle' => TRUE]);
    closeside();
    echo '<h6>BBcode</h6>';
    openside();
    display_bbcode();
    closeside();
    closetable();

}


function pf_button() {

}



function bbcode_list() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET.'comments.php');
    $test_message = '';
    $smileys_checked = 0;

    if (isset($_POST['post_test'])) {
        $test_message = form_sanitizer($_POST['test_message'], '', 'test_message');
        $smileys_checked = isset($_POST['test_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si",
            $test_message) ? 1 : 0;
        if (\defender::safe()) {
            openside($locale['BBCA_417']);
            if (!$smileys_checked) {
                echo parsesmileys(parseubb($test_message));
            } else {
                echo parseubb($test_message);
            }
            closeside();
        }
    }

    echo openform('input_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=bbcode_list");
    echo form_textarea('test_message', $locale['BBCA_418a'], $test_message, [
        'required'   => TRUE,
        'error_text' => $locale['BBCA_418b'],
        'type'       => 'bbcode'
    ]);

    echo '<div class="row">';
    echo "<div class='col-xs-6 col-md-6 text-right'>\n";
    echo form_checkbox('test_smileys', $locale['BBCA_418'], $smileys_checked, [
        'type'          => 'checkbox',
        'reverse_label' => TRUE
    ]);
    echo "</div>\n";
    echo "<div class='col-xs-6 col-md-6 text-left'>\n";
    echo form_button('post_test', $locale['BBCA_401'], $locale['BBCA_401'], ['class' => 'btn-primary']);
    echo "</div>\n";
    echo "</div>\n";
    echo closeform();
}

function bbcode_action() {

    $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");
    $aidlink = fusion_get_aidlink();
    $available_bbcodes = [];
    $enabled_bbcodes = [];
    $textarea_name = "";
    $inputform_name = "";
    $__BBCODE__ = [];

    if ((isset($_GET['action']) && $_GET['action'] == "mup") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order=:bbcodeorder", [':bbcodeorder' => intval($_GET['order'])]));
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $data['bbcode_id']]);
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id=:bbcode", [':bbcode' => $_GET['bbcode_id']]);
        add_notice('info', $locale['BBCA_430']);
        redirect(clean_request('', ['section', 'action', 'bbcode_id', 'order'], FALSE));

    } else if ((isset($_GET['action']) && $_GET['action'] == "mdown") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {

        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order=:bbcodeorder", [':bbcodeorder' => intval($_GET['order'])]));
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $data['bbcode_id']]);
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id=:bbcode", [':bbcode' => $_GET['bbcode_id']]);
        add_notice('info', $locale['BBCA_431']);
        redirect(clean_request('', ['section', 'action', 'bbcode_id', 'order'], FALSE));

    } else if (isset($_GET['enable']) && preg_match("/^!?([a-z0-9_-]){1,50}$/i",
            $_GET['enable']) && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include_var.php") && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include.php")) {
        if (substr($_GET['enable'], 0, 1) != '!') {
            $data2 = dbarray(dbquery("SELECT MAX(bbcode_order) AS xorder FROM ".DB_BBCODES));
            $order = ($data2['xorder'] == 0 ? 1 : ($data2['xorder'] + 1));
            dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '".$order."')");
        } else {
            $result2 = dbcount("(bbcode_id)", DB_BBCODES);
            if (!empty($result2)) {
                dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1");
            }
            dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '1')");
        }
        add_notice('info', $locale['BBCA_432']);
        redirect(clean_request('', ['section', 'enable'], FALSE));

    } else if (isset($_GET['disable']) && isnum($_GET['disable'])) {
        dbquery("DELETE FROM ".DB_BBCODES." WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $_GET['disable']]);
        $result = dbquery("SELECT bbcode_order FROM ".DB_BBCODES." ORDER BY bbcode_order");
        $order = 1;
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=:norder WHERE bbcode_order=:bbcodeorder", [':norder' => $order, ':bbcodeorder' => $data['bbcode_order']]);
            $order++;
        }
        add_notice('warning', $locale['BBCA_433']);
        redirect(clean_request('', ['section', 'disable'], FALSE));
    }

}

