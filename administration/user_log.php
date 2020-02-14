<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_log.php
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
require_once __DIR__.'/../maincore.php';
pageAccess('UL');
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/user_log.php");

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'administrators.php'.fusion_get_aidlink(), 'title' => $locale['UL_001']]);

if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
    $_GET['rowstart'] = 0;
}

// Set default values
$dbOrder = "ORDER BY userlog_timestamp DESC";
$dbWhere = "";
$dbWhereCount = "";
$getString = $aidlink;
$orderby = "userlog_timestamp";
$expr = "DESC";
$user = "";
$userField = "";
$orderbyArray = [
    'userlog_timestamp' => $locale['UL_002'],
    'user_name'         => $locale['UL_003'],
    'userlog_field'     => $locale['UL_004']
];

$exprArray = ["DESC" => $locale['UL_019'], "ASC" => $locale['UL_018']];
if (isset($_POST) && !empty($_POST)) {
    if (isset($_POST['orderby']) && in_array($_POST['orderby'], $orderbyArray)) {
        $orderby = form_sanitizer($_POST['orderby'], 'DESC', 'orderby');
        $dbOrder = "ORDER BY ".$orderby;
        if (isset($_POST['expr']) && in_array($_POST['expr'], $exprArray)) {
            $expr = form_sanitizer($_POST['expr'], '', 'expr');
            $dbOrder .= " ".$expr;
        }
    }
    if (isset($_POST['user'])) {
        $user = form_sanitizer($_POST['user'], '', 'user');

        if (isnum($user)) {
            $dbWhere = "userlog_user_id='".$user."'";
        } else if ($_POST['user'] != "") {
            $user = trim(stripinput($user));
            $dbWhere = "user_name LIKE '".$user."%'";
        }
    }

    if (isset($_POST['userField']) && $_POST['userField'] != "---" && $_POST['userField'] != "") {
        $userField = trim(stripinput($_POST['userField']));
        $dbWhere .= ($dbWhere != "" ? " AND userlog_field='".$userField."'" : "userlog_field='".$userField."'");
    }
    $dbWhereCount = $dbWhere;
    $dbWhere = ($dbWhere != "" ? "WHERE ".$dbWhere : "");
    // build get string
    $getString .= "&amp;orderby=".$orderby."&amp;expr=".$expr."&amp;user=".$user."&amp;userField=".$userField;
}

// End $_GET Vars
if (isset($_POST['log_id'])) {
    if (isset($_POST['table_action']) && isset($_POST['log_id'])) {
        $input = (isset($_POST['log_id'])) ? explode(",", form_sanitizer($_POST['log_id'], "", "log_id")) : "";
        if (!empty($input)) {
            foreach ($input as $log_id) {
                dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_id=:logid", [':logid' => $log_id]);
            }
        }
    }

    addNotice('info', $locale['UL_006']);
    redirect(clean_request('', ['delete'], FALSE));
}

if (isset($_POST['daydelete']) && isnum($_POST['daydelete'])) {
    $delete = form_sanitizer($_POST['daydelete'], 0, 'daydelete');
    $bind = [
        ':time' => time() - $delete * 24 * 60 * 60,
    ];
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_timestamp<:time", $bind);
    addNotice('info', sprintf($locale['UL_005'], $delete));
    redirect(clean_request('', ['delete'], FALSE));
}

if (isset($_GET['delete']) && isnum($_GET['delete'])) {
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_id=:delete", [':delete' => $_GET['delete']]);
    addNotice('info', $locale['UL_006']);
    redirect(clean_request('', ['delete'], FALSE));
}

function userFieldOptions() {
    $locale = fusion_get_locale();
    $options['user_name'] = $locale['UL_003'];
    $options['user_email'] = $locale['UL_007'];
    $result = dbquery("SELECT field_name, field_title FROM ".DB_USER_FIELDS." WHERE field_log='1'");
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $options[$data['field_name']] = $data['field_title'];
        }
    }

    return $options;
}

opentable($locale['UL_001']);

openside();
echo openform('userlog_search', 'post', FUSION_REQUEST);
echo form_hidden('aid', '', iAUTH);
echo form_select('orderby', $locale['UL_008'], $orderby, [
    'options'    => $orderbyArray,
    'placholder' => $locale['choose'],
    'inline'     => TRUE
]);
echo form_select('expr', ' ', $orderby, [
    'options'    => $exprArray,
    'placholder' => $locale['choose'],
    'inline'     => TRUE
]);
echo form_user_select("user", $locale['UL_009'], '', [
    'max_select'  => 1,
    'inline'      => TRUE,
    'inner_width' => '100%',
    'width'       => '100%',
    'allow_self'  => TRUE,
]);
echo form_select('userField', $locale['UL_010'], $userField, [
    'options'     => userFieldOptions(),
    'placeholder' => $locale['choose'],
    'allowclear'  => 1,
    'inline'      => TRUE
]);
echo form_button('submit', $locale['UL_011'], $locale['UL_011'], ['class' => 'btn-primary']);
echo closeform();
closeside();

// at least validate token.
if (!defined('FUSION_NULL')) {
    $result = dbquery("SELECT SQL_CALC_FOUND_ROWS userlog_id, userlog_user_id, userlog_field, userlog_value_old, userlog_value_new, userlog_timestamp, user_name, user_status
                   FROM ".DB_USER_LOG."
                   LEFT JOIN ".DB_USERS." ON userlog_user_id=user_id
                   ".$dbWhere."
                   ".$dbOrder."
                   LIMIT ".$_GET['rowstart'].",20");
    $rows = dbresult(dbquery("SELECT FOUND_ROWS()"), 0);
    if (dbrows($result)) {
        echo "<div class='table-responsive'><table id='log-table' class='table table-striped'>\n";
        echo "<thead>\n<tr>\n";
        echo "<th></th>\n";
        echo "<th>".$locale['UL_002']."</th>\n";
        echo "<th style='width: 150px;'>".$locale['UL_003']."</th>\n";
        echo "<th style='width: 140px;'>".$locale['UL_004']."</th>\n";
        echo "<th style='width: 160px;'>".$locale['UL_012']."</th>\n";
        echo "<th style='width: 160px;'>".$locale['UL_013']."</th>\n";
        echo "<th style='width: 160px;'>".$locale['UL_014']."</th>\n";
        echo "</tr>\n</thead>\n";

        echo "<tbody>\n";
        echo openform('userlog_table', 'post', FUSION_REQUEST);
        echo form_hidden('table_action', '', '');
        while ($data = dbarray($result)) {
            echo "<tr>";
            echo "<td>".form_checkbox("log_id[]", "", "", ["value" => $data['userlog_id'], "class" => "m-0"])."</td>\n";
            echo "<td>".showdate("shortdate", $data['userlog_timestamp'])."</td>\n";
            echo "<td>".profile_link($data['userlog_user_id'], $data['user_name'], $data['user_status'])."</td>\n";
            echo "<td>".$data['userlog_field']."</td>\n";
            echo "<td>".trimlink($data['userlog_value_old'], 100)."</td>\n";
            echo "<td>".trimlink($data['userlog_value_new'], 100)."</td>\n";
            echo "<td><a href='".FUSION_SELF.$getString."&amp;delete=".$data['userlog_id']."'>".$locale['delete']."</a></td>\n";
            echo "</tr>\n";
        }

        echo "</tbody>\n";
        echo "</table>\n</div>";
        echo "<div class='clearfix display-block'>\n";
        echo "<div class='display-inline-block pull-left m-r-20'>".form_checkbox('check_all', $locale['UL_020'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE])."</div>";
        echo "<div class='display-inline-block'><a class='btn btn-danger btn-sm' onclick=\"run_admin('delete', '#table_action', '#userlog_table');\"><i class='fa fa-fw fa-trash-o'></i> ".$locale['delete']."</a></div>";
        echo "</div>\n";
        echo closeform();
        add_to_jquery("
            $('#check_all').bind('click', function() {
                if ($(this).is(':checked')) {
                    $('input[name^=log_id]:checkbox').prop('checked', true);
                    $('#log-table tbody tr').addClass('active');
                } else {
                    $('input[name^=log_id]:checkbox').prop('checked', false);
                     $('#log-table tbody tr').removeClass('active');
                }
            });
        ");
    } else {
        echo "<div class='well text-center'>".$locale['UL_015']."</div>\n";
    }

    if ($rows > 20) {
        echo "<div class='m-t-5 text-center'>\n".makepagenav($_GET['rowstart'], 20, $rows, 3, FUSION_SELF.$getString."&amp;")."\n</div>\n";
    }

}

openside('', 'm-t-20');
echo openform('userlog_delete', 'post', FUSION_REQUEST);
echo form_text('daydelete', $locale['UL_016'], '', [
    'max_length'  => 3,
    'type'        => 'number',
    'placeholder' => $locale['UL_017'],
    'inline'      => TRUE
]);
echo form_button('submit', $locale['UL_011'], $locale['UL_011'], ['class' => 'btn-primary']);
echo closeform();
closeside();

closetable();

require_once THEMES.'templates/footer.php';
