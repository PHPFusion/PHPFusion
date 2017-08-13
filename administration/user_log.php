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
require_once "../maincore.php";
pageAccess('UL');
require_once THEMES."templates/admin_header.php";
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
$orderbyArray = array(
    "userlog_timestamp" => $locale['UL_002'],
    "user_name"         => $locale['UL_003'],
    "userlog_field"     =>$locale['UL_004']
);

$exprArray = array("DESC" => $locale['UL_019'], "ASC" => $locale['UL_018']);
if (isset($_POST) && !empty($_POST)) {
    if (isset($_POST['orderby']) && in_array($_POST['orderby'], $orderbyArray)) {
        $orderby = $_POST['orderby'];
        $dbOrder = "ORDER BY ".$_POST['orderby'];
        if (isset($_POST['expr']) && in_array($_POST['expr'], $exprArray)) {
            $expr = $_POST['expr'];
            $dbOrder .= " ".$_POST['expr'];
        }
    }
    if (isset($_POST['user'])) {
        if (isnum($_POST['user'])) {
            $user = $_POST['user'];
            $dbWhere = "userlog_user_id='".$_POST['user']."'";
        } elseif ($_POST['user'] != "") {
            $user = trim(stripinput($_POST['user']));
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
if (isset($_POST['delete']) && isnum($_POST['delete'])) {
    $time = time() - $_POST['delete'] * 24 * 60 * 60;
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_timestamp<".$time);
    addNotice('info', sprintf($locale['UL_005'], $_POST['delete']));
    redirect(FUSION_SELF.fusion_get_aidlink());
}

if (isset($_GET['delete']) && isnum($_GET['delete'])) {
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_id='".$_GET['delete']."'");
    addNotice('info', $locale['UL_006']);
    redirect(FUSION_SELF.fusion_get_aidlink());
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

echo openside();
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
    echo form_text('user', $locale['UL_009'], $user, [
        'inline' => TRUE
    ]);
    echo form_select('userField', $locale['UL_010'], $userField, array(
        'options'     => userFieldOptions(),
        'placeholder' => $locale['choose'],
        'allowclear'  => 1,
        'inline'      => TRUE
    ));
    echo form_button('submit', $locale['UL_011'], $locale['UL_011'], ['class' => 'btn-primary']);
    echo closeform();
echo closeside();

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
        echo "<div class='table-responsive'><table class='table table-striped'>\n";
            echo "<thead>\n<tr>\n";
                echo "<th width:100px;'>".$locale['UL_002']."</th>\n";
                echo "<th width:150px;'>".$locale['UL_003']."</th>\n";
                echo "<th width:140px;'>".$locale['UL_004']."</th>\n";
                echo "<th width:160px;'>".$locale['UL_012']."</th>\n";
                echo "<th width:160px;'>".$locale['UL_013']."</th>\n";
                echo "<th width:160px;'>".$locale['UL_014']."</th>\n";
            echo "</tr>\n</thead>\n";

            echo "<tbody>\n";
            while ($data = dbarray($result)) {
                echo "<tr>\n";
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
    } else {
        echo "<div class='well text-center'>".$locale['UL_015']."</div>\n";
    }
    if ($rows > 20) {
        echo "<div class='m-t-5 text-center'>\n".makepagenav($_GET['rowstart'], 20, $rows, 3,
                                                                          FUSION_SELF.$getString."&amp;")."\n</div>\n";
    }
}

echo openside('', 'm-t-20');
    echo openform('userlog_delete', 'post', FUSION_REQUEST);
    echo form_text('delete', $locale['UL_016'], '', [
        'max_length'  => 3,
        'type'        => 'number',
        'placeholder' => $locale['UL_017'],
        'inline'      => TRUE
    ]);
    echo form_button('submit', $locale['UL_011'], $locale['UL_011'], ['class' => 'btn-primary']);
    echo closeform();
echo closeside();

closetable();

require_once THEMES."templates/footer.php";
