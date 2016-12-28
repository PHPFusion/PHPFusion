<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_log.php
| Author: gh0st2k
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
include LOCALE.LOCALESET."admin/user_log.php";
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'administrators.php'.fusion_get_aidlink(), 'title' => $locale['100']]);
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
    $locale['102'] => "userlog_timestamp",
    $locale['103'] => "user_name",
    $locale['104'] => "userlog_field"
);
$exprArray = array("DESC", "ASC");
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
    echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".sprintf($locale['118'], $_POST['delete'])."</div></div>\n";
}
if (isset($_GET['delete']) && isnum($_GET['delete'])) {
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_id='".$_GET['delete']."'");
    echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['119']."</div></div>\n";
}
function orderbyOptions() {
    global $orderbyArray;
    $options = array();
    foreach ($orderbyArray AS $key => $value) {
        $options[$value] = $key;
    }

    return $options;
}

function exprOptions() {
    global $exprArray;
    $options = array();
    foreach ($exprArray AS $value) {
        $options[$value] = $value;
    }

    return $options;
}

function userFieldOptions() {
    $locale = fusion_get_locale();
    $options['user_name'] = $locale['103'];
    $options['user_email'] = $locale['103a'];
    $result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS." WHERE field_log='1'");
    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $options[$data['field_name']] = $data['field_name'];
        }
    }

    return $options;
}

opentable($locale['100']);
echo openform('userlog_search', 'post', FUSION_SELF.$aidlink);
echo form_hidden('aid', '', iAUTH);
echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border center'>\n<tbody>\n";
echo "<tr>\n";
echo "<td class='tbl1' align='left'><label for='orderby'>".$locale['107']."</label></td>\n";
echo "<td class='tbl1' style='align:right;'>\n";
echo form_select('orderby', '', $orderby, array(
    'options' => orderbyOptions(),
    'placholder' => $locale['choose'],
    'class' => 'pull-right'
));
echo form_select('expr', '', $orderby, array('options' => exprOptions(), 'placholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1' align='left'><label for='user'>".$locale['108']."</label></td>\n";
echo "<td class='tbl1' align='right'>\n";
echo form_text('user', '', $user);
echo "</td>\n</tr>\n<tr>\n";
echo "<td class='tbl1'><label for='userField'>".$locale['115']."</label></td>";
echo "<td class='tbl1'>\n";
echo form_select('userField', '', $userField, array(
    'options' => userFieldOptions(),
    'placeholder' => $locale['choose'],
    'allowclear' => 1
));
echo "</tr>\n<tr>\n";
echo "<td class='tbl' align='left'></td>\n<td class='tbl' align='right'>\n";
echo form_button('submit', $locale['109'], $locale['109'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
echo "<br />";
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
        echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border center'>\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th class='tbl2' style='white-space:nowrap; width:100px;'>".$locale['102']."</th>\n";
        echo "<th class='tbl2' style='white-space:nowrap; width:150px;'>".$locale['103']."</th>\n";
        echo "<th class='tbl2' style='white-space:nowrap; width:140px;'>".$locale['104']."</th>\n";
        echo "<th class='tbl2' style='white-space:nowrap; width:160px;'>".$locale['105']."</th>\n";
        echo "<th class='tbl2' style='white-space:nowrap; width:160px;'>".$locale['106']."</th>\n";
        echo "<th class='tbl2' style='white-space:nowrap; width:160px;'>".$locale['117']."</th>\n";
        echo "</tr>\n</thead>\n<tbody>\n";
        $i = 1;
        while ($data = dbarray($result)) {
            $class = ($i % 2 ? "tbl1" : "tbl2");
            echo "<tr>\n";
            echo "<td class='".$class."'>".showdate("shortdate", $data['userlog_timestamp'])."</td>\n";
            echo "<td class='".$class."'>".profile_link($data['userlog_user_id'], $data['user_name'], $data['user_status'])."</td>\n";
            echo "<td class='".$class."'>".$data['userlog_field']."</td>\n";
            echo "<td class='".$class."'>".trimlink($data['userlog_value_old'], 100)."</td>\n";
            echo "<td class='".$class."'>".trimlink($data['userlog_value_new'], 100)."</td>\n";
            echo "<td class='".$class."'><a href='".FUSION_SELF.$getString."&amp;delete=".$data['userlog_id']."'>".$locale['116']."</a></td>\n";
            echo "</tr>\n";
            $i++;
        }
        echo "</tbody>\n</table>\n";
    } else {
        echo "<center>".$locale['112']."</center>\n";
    }
    if ($rows > 20) {
        echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $rows, 3,
                                                                          FUSION_SELF.$getString."&amp;")."\n</div>\n";
    }
}
echo "<br />";
echo "<form action='".FUSION_SELF.$aidlink."' method='post'>\n";
echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border center'>\n<tbody>\n";
echo "<tr>\n";
echo "<td class='tbl' width='50%'><label for='delete'>".$locale['110'].":</label></td>\n";
echo "<td class='tbl1' align='right'>\n";
echo form_text('delete', '', '', array(
    'max_length' => 3,
    'number' => 1,
    'placeholder' => $locale['111'],
    'inline' => 1,
    'width' => '100px'
));
echo form_button('submit', $locale['109'], $locale['109'], array('class' => 'btn-primary'));
echo "</td>\n";
echo "</tr>\n";
echo "</tbody>\n</table>\n";
echo "</form>\n";
closetable();
require_once THEMES."templates/footer.php";
