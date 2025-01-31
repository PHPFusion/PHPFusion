<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bbcodes.php
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

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('BB');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/bbcodes.php");

global $p_data;

add_breadcrumb(['link' => ADMIN.'bbcodes.php'.fusion_get_aidlink(), 'title' => $locale['BBCA_400']]);

$allowed_sections = ['bbcode_form', 'bbcode_list'];
$sections = in_array(get('section'), $allowed_sections) ? get('section') : 'bbcode_list';

$tab_title['title'][] = $locale['BBCA_400a'];
$tab_title['id'][] = 'bbcode_list';
$tab_title['icon'][] = '';

$tab_title['title'][] = $locale['BBCA_401'];
$tab_title['id'][] = 'bbcode_form';
$tab_title['icon'][] = '';

opentable($locale['BBCA_400']);
echo opentab($tab_title, $sections, 'bbcode_list', TRUE, 'nav-tabs');
switch ($sections) {
    case "bbcode_form":
        bbcode_form();
        break;
    default:
        bbcode_list();
        break;
}
echo closetab();
closetable();

function bbcode_form() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET.'comments.php');
    $test_message = '';
    $smileys_checked = 0;

    if (check_post('post_test')) {
        $test_message = sanitizer('test_message', '', 'test_message');
        $smileys_checked = check_post('test_smileys') || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $test_message) ? 1 : 0;
        if (fusion_safe()) {
            openside($locale['BBCA_417']);
            if (!$smileys_checked) {
                echo parsesmileys(parseubb($test_message));
            } else {
                echo parseubb($test_message);
            }
            closeside();
        }
    }

    echo openform('input_form', 'post', FUSION_SELF.fusion_get_aidlink()."&section=bbcode_form");
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

function bbcode_list() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");
    $aidlink = fusion_get_aidlink();
    $available_bbcodes = [];
    $enabled_bbcodes = [];
    $textarea_name = "";
    $inputform_name = "";

    if ((check_get('action') && get('action') == "mup") && (check_get('bbcode_id') && get('bbcode_id', FILTER_SANITIZE_NUMBER_INT))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order=:bbcodeorder", [':bbcodeorder' => get('order', FILTER_SANITIZE_NUMBER_INT)]));
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $data['bbcode_id']]);
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id=:bbcode", [':bbcode' => get('bbcode_id')]);
        cdreset('bbcodes_cache');

        addnotice('info', $locale['BBCA_430']);
        redirect(clean_request('', ['section', 'action', 'bbcode_id', 'order'], FALSE));

    } else if ((check_get('action') && get('action') == "mdown") && (check_get('bbcode_id') && get('bbcode_id', FILTER_SANITIZE_NUMBER_INT))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order=:bbcodeorder", [':bbcodeorder' => get('order', FILTER_SANITIZE_NUMBER_INT)]));
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $data['bbcode_id']]);
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id=:bbcode", [':bbcode' => get('bbcode_id')]);
        cdreset('bbcodes_cache');
        addnotice('info', $locale['BBCA_431']);
        redirect(clean_request('', ['section', 'action', 'bbcode_id', 'order'], FALSE));

    } else if (check_get('enable') && preg_match("/^!?([a-z0-9_-]){1,50}$/i", get('enable'))
        && file_exists(INCLUDES."bbcodes/".get('enable')."_bbcode_include_var.php") && file_exists(INCLUDES."bbcodes/".get('enable')."_bbcode_include.php")
    ) {
        if (substr(get('enable'), 0, 1) != '!') {
            $data2 = dbarray(dbquery("SELECT MAX(bbcode_order) AS xorder FROM ".DB_BBCODES));
            $order = ($data2['xorder'] == 0 ? 1 : ($data2['xorder'] + 1));
            dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".get('enable')."', '".$order."')");
        } else {
            $result2 = dbcount("(bbcode_id)", DB_BBCODES);
            if (!empty($result2)) {
                dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1");
            }
            dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".get('enable')."', '1')");
        }
        cdreset('bbcodes_cache');
        addnotice('info', $locale['BBCA_432']);
        redirect(clean_request('', ['section', 'enable'], FALSE));

    } else if (check_get('disable') && get('disable', FILTER_SANITIZE_NUMBER_INT)) {

        dbquery("DELETE FROM ".DB_BBCODES." WHERE bbcode_id=:bbcodeid", [':bbcodeid' => get('disable')]);

        $result = dbquery("SELECT bbcode_order FROM ".DB_BBCODES." ORDER BY bbcode_order");
        $order = 1;
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=:norder WHERE bbcode_order=:bbcodeorder", [':norder' => $order, ':bbcodeorder' => $data['bbcode_order']]);
            $order++;
        }
        cdreset('bbcodes_cache');
        addnotice('success', $locale['BBCA_433']);
        redirect(clean_request('', ['section', 'disable'], FALSE));
    }

    // Add Seperator
    if (get('new') == 'sep') {
        $data = [
            'bbcode_id'    => 0,
            'bbcode_name'  => '---',
            'bbcode_order' => 0,
        ];
        dbquery_insert(DB_BBCODES, $data, 'save');
        // re-sort all links
        $dresult = dbquery("SELECT bbcode_id FROM ".DB_BBCODES." ORDER BY bbcode_order");
        if (dbrows($dresult)) {
            $i = 1;
            while ($data = dbarray($dresult)) {
                $data['bbcode_order'] = $i;
                dbquery_insert(DB_BBCODES, $data, 'update');
                $i++;
            }
        }
        redirect(clean_request('', ['new'], FALSE));
    }

    $bbcode_folder = makefilelist(INCLUDES."bbcodes/", '.|..|index.php|.js');
    if (!empty($bbcode_folder)) {
        foreach ($bbcode_folder as $bbcode_folders) {
            if (preg_match("/_include.php/i", $bbcode_folders)) {
                $bbcode_name = explode("_", $bbcode_folders);
                $available_bbcodes[] = $bbcode_name[0];
            }
        }
    }

    $bbcode_desc = '';
    $bbcode_usage = '';

    $result = dbquery("SELECT * FROM ".DB_BBCODES." ORDER BY bbcode_order");
    sort($available_bbcodes);
    if (dbrows($result)) {

        //echo '<a class="btn btn-default" href="'.clean_request('new=sep', ['new'], FALSE).'">'.$locale['BBCA_500'].'</a>';

        echo '<h4>'.$locale['BBCA_402'].'</h4>';
        $fusion_table_id = fusion_table('bbcode', [
            'row_reorder'         => TRUE,
            'row_reorder_url'     => ADMIN.'includes/?api=bbcodes-order',
            'row_reorder_success' => $locale['BBCA_444'],
            'row_reorder_failed'  => $locale['BBCA_445'],
            'ordering'            => FALSE,
            'pagination'          => FALSE,
            'fixed_header'        => TRUE,
        ]);

        echo "<table id='$fusion_table_id' class='table table-bordered table-striped'><thead><tr>";
        echo "<th></th>";

        echo "<th>".$locale['BBCA_403']."</th>\n";
        echo "<th>".$locale['BBCA_404']."</th>\n";
        echo "<th>".$locale['BBCA_405']."</th>\n";
        echo "<th>".$locale['BBCA_406']."</th>\n";
        echo "<th>".$locale['BBCA_407']."</th>\n";
        echo "</tr>\n</thead>\n<tbody>\n";

        $i = 1;
        $numrows = dbcount("(bbcode_id)", DB_BBCODES);
        while ($data = dbarray($result)) {
            $up_down = "";
            if ($numrows != 1) {
                $up = $data['bbcode_order'] - 1;
                $down = $data['bbcode_order'] + 1;
                if ($i == 1) {
                    $up_down = " <a href='".FUSION_SELF.$aidlink."&action=mdown&bbcode_id=".$data['bbcode_id']."&order=$down'><i class='fa fa-lg fa-angle-down' title='".$locale['BBCA_408']."'></i></a>\n";
                } else {
                    $up_down = " <a href='".FUSION_SELF.$aidlink."&action=mup&bbcode_id=".$data['bbcode_id']."&order=$up'><i class='fa fa-lg fa-angle-up' title='".$locale['BBCA_409']."'></i></a>\n";
                    if ($i < $numrows) {
                        $up_down .= " <a href='".FUSION_SELF.$aidlink."&action=mdown&bbcode_id=".$data['bbcode_id']."&order=$down'><i class='fa fa-lg fa-angle-down' title='".$locale['BBCA_408']."'></i></a>\n";
                    }
                }
            }
            $i++;

            $enabled_bbcodes[] = $data['bbcode_name'];

            $__BBCODE__ = [];
            $img_path = INCLUDES.'bbcodes/images/';
            $bbcode_attr = ['.svg', '.png', '.gif', '.jpg'];
            $bbcode_image = '-';

            if (file_exists(LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php")) {
                $locale = fusion_get_locale('', LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php");
            } else if (file_exists(LOCALE."English/bbcodes/".$data['bbcode_name'].".php")) {
                $locale = fusion_get_locale('', LOCALE."English/bbcodes/".$data['bbcode_name'].".php");
            }

            if (file_exists(INCLUDES."bbcodes/".$data['bbcode_name']."_bbcode_include_var.php")) {
                include INCLUDES."bbcodes/".$data['bbcode_name']."_bbcode_include_var.php";

                if (isset($__BBCODE__[0]['image'])) {
                    $image_name = $__BBCODE__[0]['image'];
                    if (is_file($img_path.$image_name)) {
                        $bbcode_image = "<img src='".$img_path.$image_name."' alt='".$data['bbcode_name']."' title='".$data['bbcode_name']."' style='width:18px;height:18px;'>";
                    }
                } else {
                    foreach ($bbcode_attr as $attr) {
                        if (file_exists($img_path.$data['bbcode_name'].$attr)) {
                            $bbcode_image = "<img src='".$img_path.$data['bbcode_name'].$attr."' alt='".$data['bbcode_name']."' title='".$data['bbcode_name']."' style='width:18px;height:18px;'>";
                            break;
                        }
                    }
                }

                $bbcode_desc = $__BBCODE__[0]['description'];
                $bbcode_usage = $__BBCODE__[0]['usage'];

                unset ($__BBCODE__);
            } else if ($data['bbcode_name'] == '---') {
                $bbcode_image = '';
                $bbcode_desc = $locale['BBCA_501'];
                $bbcode_usage = $locale['BBCA_502'];
            }

            echo "<tr data-id='".$data['bbcode_id']."'>\n";
            echo "<td class='text-center'><a class='btn btn-default' href='".FUSION_SELF.$aidlink."&disable=".$data['bbcode_id']."'>".$locale['BBCA_410']."</a></td>\n";
            echo "<td>".ucwords($data['bbcode_name'])."</td>\n";
            echo "<td class='text-center hidden-xs'>".$bbcode_image."</td>\n";
            echo "<td>$bbcode_desc</td>\n";
            echo "<td>$bbcode_usage</td>\n";
            //echo "<td class='text-center'>".$data['bbcode_order']."</td>\n";
            echo "<td class='text-center'>".$up_down."</td>\n";

            echo "</tr>\n";

        }
        echo "</tbody>\n</table>\n";
    } else {
        echo "<div class='text-center'>".$locale['BBCA_411']."</div>\n";
    }

    $enabled = dbcount("(bbcode_id)", DB_BBCODES);
    echo '<h4>'.$locale['BBCA_413'].'</h4>';
    if (count($available_bbcodes) != $enabled) {
        echo "<div class='table-responsive'><table class='table table-bordered table-striped'>\n<thead>\n<tr>\n";
        echo "<th>".$locale['BBCA_403']."</th>\n";
        echo "<th class='hidden-xs'>".$locale['BBCA_404']."</th>\n";
        echo "<th>".$locale['BBCA_405']."</th>\n";
        echo "<th>".$locale['BBCA_406']."</th>\n";
        echo "<th></th>\n";
        echo "</tr>\n</thead>\n<tbody>\n";

        $img_path = INCLUDES.'bbcodes/images/';
        $bbcode_attr = ['.svg', '.png', '.gif', '.jpg'];
        $bbcode_image = '-';

        foreach ($available_bbcodes as $available_bbcode) {
            if (!in_array($available_bbcode, $enabled_bbcodes)) {
                foreach ($bbcode_attr as $attr) {
                    if (file_exists($img_path.$available_bbcode.$attr)) {
                        $bbcode_image = "<img src='".$img_path.$available_bbcode.$attr."' alt='".$available_bbcode."' style='width:18px;height:18px;'>";
                        break;
                    }
                }

                $__BBCODE__ = [];
                if (file_exists(LOCALE.LOCALESET."bbcodes/".$available_bbcode.".php")) {
                    $locale = fusion_get_locale('', LOCALE.LOCALESET."bbcodes/".$available_bbcode.".php");
                } else if (file_exists(LOCALE."English/bbcodes/".$available_bbcode.".php")) {
                    $locale = fusion_get_locale('', LOCALE."English/bbcodes/".$available_bbcode.".php");
                }

                include INCLUDES."bbcodes/".$available_bbcode."_bbcode_include_var.php";
                echo "<tr>\n";
                echo "<td>".ucwords($available_bbcode)."</td>\n";
                echo "<td class='text-center hidden-xs'>".$bbcode_image."</td>\n";
                echo "<td>".$__BBCODE__[0]['description']."</td>\n";
                echo "<td>".$__BBCODE__[0]['usage']."</td>\n";
                echo "<td class='text-center'><a href='".FUSION_SELF.$aidlink."&enable=".$available_bbcode."'>".$locale['BBCA_414']."</a></td>\n";
                echo "</tr>\n";
                unset ($__BBCODE__);
            }
        }
        echo "</tbody>\n</table>\n";
        echo "</div>\n";
    } else {
        echo "<div class='text-center'>".$locale['BBCA_416']."</div>\n";
    }
}

require_once THEMES.'templates/footer.php';
