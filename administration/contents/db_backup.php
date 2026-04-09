<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: db_backup.php
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

use PHPFusion\Database\DatabaseFactory;

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/db-backup.php');

$contents = [
    'post'        => 'pf_backup',
    'view'        => 'pf_view',
    'button'      => '',
    'js'          => 'pf_js',
    'link'        => ($admin_link ?? ''),
    'settings'    => TRUE,
    'title'       => $locale['450'],
    'description' => '',
    //'actions'     => ['post' => 'pfbackup', 'post_form' => 'settingsform', ''],
];

$settings = fusion_get_settings();


if (!isset($_POST['btn_do_restore']) && (!isset($_GET['action']) || $_GET['action'] != "restore")) {
    $backup_files = makefilelist(ADMIN."db_backups/", ".|..|index.php", TRUE);
    if (is_array($backup_files)) {
        foreach ($backup_files as $file) {
            @unlink(ADMIN."db_backups/".$file);
        }
    }
}

execute_backup();
execute_restore();

function pf_view() {

    $locale = fusion_get_locale();

    echo '<h6>Migration Options</h6>';

    backup_form();

    restore_form();
}

function backup_form() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();
    global $db_name, $db_prefix;

    $table_opt_list = "";
    $result = dbquery("SHOW tables");
    while ($row = dbarraynum($result)) {
        $table_opt_list .= "<option value='".$row[0]."'";
        if (preg_match("/^".DB_PREFIX."/i", $row[0])) {
            $table_opt_list .= " selected='selected'";
        }
        $table_opt_list .= ">".$row[0]."</option>";
    }

    add_to_jquery("
            function backupSelectCore(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=(document.backupform.elements['db_tables[]'].options[i].text).match(/^$db_prefix/i);}}
            function backupSelectAll(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=true;}}
            function backupSelectNone(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=false;}}

            $('#backupSelectCore').on('click', function (e) {e.preventDefault();backupSelectCore()});
            $('#backupSelectAll').on('click', function (e) {e.preventDefault();backupSelectAll()});
            $('#backupSelectNone').on('click', function (e) {e.preventDefault();backupSelectNone()});
        ");

    openside('Export your content<small>Download all your content and settings', TRUE);
    echo openform('backupform', 'POST');
    echo "<div class='row'>";
    echo '<div class="col-xs-12 col-sm-6">';

    echo '<h6>'.$locale['451'].'</h6>';

    echo '<div class="well text-smaller">';
    echo '<div class="row"><div class="col-xs-6">'.$locale['414'].'</div><div class="col-xs-6">'.$db_name.'</div></div>';
    echo '<div class="row"><div class="col-xs-6">'.$locale['415'].'</div><div class="col-xs-6">'.$db_prefix.'</div></div>';
    echo '<div class="row"><div class="col-xs-6">'.$locale['452'].'</div><div class="col-xs-6">'.parsebytesize(get_database_size(), 2, FALSE).' ( '.format_word(get_table_count(), 'table|tables').' )</div></div>';
    echo '<div class="row"><div class="col-xs-6">'.$locale['453'].'</div><div class="col-xs-6">'.parsebytesize(get_database_size($db_prefix), 2, FALSE).' ( '.format_word(get_table_count($db_prefix), 'table|tables').' )</div></div>';
    echo '</div>';


    echo form_text('backup_filename', $locale['431'], "backup_".stripsiteinput($settings['sitename'])."_".date('Y-m-d-Hi')."", [
        'required'   => 1,
        'error_text' => $locale['481b']
    ]);

    $opts = [];
    if (function_exists("gzencode")) {
        $opts['.gz'] = ".sql.gz ".$locale['456'];
    }
    $opts['.sql'] = ".sql";
    echo form_select('backup_type', $locale['455'], '', ['options' => $opts, 'placeholder' => $locale['choose'], 'width'=>'100%', 'inner_width'=>'100%']);


    echo '</div>';

    // Second column
    echo '<div class="col-xs-12 col-sm-6">';

    echo '<h6>'.$locale['457'].'</h6>';
    echo "<select name='db_tables[]' id='tablelist' size='20' style='width:100%;' class='textbox' multiple='multiple'>".$table_opt_list."</select>";
    echo "<div class='btn-group' style='display: inline-block;margin-top:15px;'>";
    echo "<a class='btn btn-default' href='#' id='backupSelectCore'>".$locale['458']."</a>";
    echo "<a class='btn btn-default' href='#' id='backupSelectAll'>".$locale['436']."</a>";
    echo "<a class='btn btn-default' href='#' id='backupSelectNone'>".$locale['437']."</a>";
    echo "</div>";
    echo '</div>';

    echo "</div>"; // .row
    echo form_button('btn_create_backup', $locale['459'], $locale['459'], ['class' => 'btn-success m-t-10']);
    echo closeform();
    closeside();





}

function restore_form() {

    $locale = fusion_get_locale();

    if (isset($_GET['action']) && $_GET['action'] == "restore") {
        $backup_data = [];
        $backup_name = '';
        $file = '';

        if (is_uploaded_file($_FILES['upload_backup_file']['tmp_name'])) {
            $temp_rand = rand(1000000, 9999999);
            $temp_hash = substr(md5($temp_rand), 8, 8);
            $file = "temp_".$temp_hash.".txt";
            $backup_name = $_FILES['upload_backup_file']['name'];
            move_uploaded_file($_FILES['upload_backup_file']['tmp_name'], ADMIN."db_backups/".$file);
            $backup_data = gzfile(ADMIN."db_backups/".$file);
        } else {
            redirect(FUSION_SELF.fusion_get_aidlink());
        }

        $info_dbname = '';
        $info_date = '';
        $info_tblpref = '';
        $info_tbls = [];
        $info_ins_cnt = [];
        $info_inserts = [];
        foreach ($backup_data as $resultline) {
            if (preg_match_all("/^# Database Name: `(.*?)`/", $resultline, $resultinfo)) {
                $info_dbname = $resultinfo[1][0];
            }
            if (preg_match_all("/^# Table Prefix: `(.*?)`/", $resultline, $resultinfo)) {
                $info_tblpref = $resultinfo[1][0];
            }
            if (preg_match_all("/^# Date: `(.*?)`/", $resultline, $resultinfo)) {
                $info_date = $resultinfo[1][0];
            }
            if (preg_match_all("/^CREATE TABLE `(.+?)`/i", $resultline, $resultinfo)) {
                $info_tbls[] = $resultinfo[1][0];
            }
            if (preg_match_all("/^INSERT INTO `(.+?)`/i", $resultline, $resultinfo)) {
                if (!in_array($resultinfo[1][0], $info_inserts)) {
                    $info_inserts[] = $resultinfo[1][0];
                }
                $info_ins_cnt[] = $resultinfo[1][0];
            }
        }
        $table_opt_list = '';
        sort($info_tbls);
        foreach ($info_tbls as $key => $info_tbl) {
            $table_opt_list .= "<option value='$info_tbl' selected='selected'>".$info_tbl."</option>";
        }
        $insert_ins_cnt = array_count_values($info_ins_cnt);
        $insert_opt_list = "";
        sort($info_inserts);
        foreach ($info_inserts as $key => $info_insert) {
            $insert_opt_list .= "<option value='".$info_insert."' selected='selected'>".$info_insert." (".$insert_ins_cnt[$info_insert].")</option>";
        }
        $maxrows = max(count($info_tbls), count($info_inserts));

        echo "<h4>".$locale['400']."</h4>";

        add_to_jquery("
                function tableSelectAll(){for(i=0;i<document.restoreform.elements['list_tbl[]'].length;i++){document.restoreform.elements['list_tbl[]'].options[i].selected=true;}}
                function tableSelectNone(){for(i=0;i<document.restoreform.elements['list_tbl[]'].length;i++){document.restoreform.elements['list_tbl[]'].options[i].selected=false;}}
                function populateSelectAll(){for(i=0;i<document.restoreform.elements['list_ins[]'].length;i++){document.restoreform.elements['list_ins[]'].options[i].selected=true;}}
                function populateSelectNone(){for(i=0;i<document.restoreform.elements['list_ins[]'].length;i++){document.restoreform.elements['list_ins[]'].options[i].selected=false;}}

                $('#tableSelectAll').on('click', function () {tableSelectAll()});
                $('#tableSelectNone').on('click', function () {tableSelectNone()});
                $('#populateSelectAll').on('click', function () {populateSelectAll()});
                $('#populateSelectNone').on('click', function () {populateSelectNone()});
            ");

        echo openform('confirm_restore_frm', 'post', FUSION_REQUEST, ['max_tokens' => 30]);
        echo "<div class='table-responsive'><table class='table'><tbody><tr>";
        echo "<td colspan='2' class='tbl2'><strong>".$locale['430']."</strong></td>";
        echo "</tr><tr>";
        echo "<td colspan='2'><strong>".$locale['431']."</strong> ".$backup_name."</td>";
        echo "</tr><tr>";
        echo "<td colspan='2'><strong>".$locale['414']."</strong> ".$info_dbname."</td>";
        echo "</tr><tr>";
        echo "<td colspan='2'><strong>".$locale['432']."</strong> ".$info_date."</td>";
        echo "</tr><tr>";
        echo "<td colspan='2'>";
        echo form_text('restore_tblpre', $locale['415'], $info_tblpref, ['required' => 1, 'error_text' => '']);
        echo form_hidden('backup_file', '', $file);
        echo "</td></tr><tr>";
        echo "<td valign='top'><strong>".$locale['433']."</strong><br />";
        echo "<select name='list_tbl[]' id='list_tbl' size='".$maxrows."' class='display-block textbox' style='width:100%;' multiple='multiple'>".$table_opt_list."</select>";
        echo "<div class='btn-group m-t-10' style='text-align:center;'>";
        echo "<a class='btn btn-default' href='#' id='tableSelectAll'>".$locale['436']."</a>";
        echo "<a class='btn btn-default' href='#' id='tableSelectNone'>".$locale['437']."</a></div></td>";
        echo "<td valign='top'><strong>".$locale['434']."</strong><br />";
        echo "<select name='list_ins[]' id='list_ins' size='".$maxrows."' class='display-block textbox' style='width:100%;' multiple='multiple'>".$insert_opt_list."</select>";
        echo "<div class='btn-group m-t-10' style='text-align:center;'><a class='btn btn-default' href='#' id='populateSelectAll'>".$locale['436']."</a>";
        echo "<a class='btn btn-default' href='#' id='populateSelectNone'>".$locale['437']."</a></div></td>";
        echo "</tr><tr>";
        echo "<td colspan='2' class='tbl text-center'>";
        echo "</tr></tbody></table></div>";
        echo form_button('btn_do_restore', $locale['438'], $locale['438'], ['class' => 'btn-primary m-r-10']);
        echo form_button('btn_cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default']);
        echo closeform();

    } else {

        $file_types = (function_exists("gzencode")) ? ".gz " : ""; // added

        openside('Import Content<small>Import content from another PHPFusion installation. '.$locale['440'].' '.$file_types.'.sql</small>',
            openform('restore', 'post', clean_request('action=restore', ['action'], FALSE), [
                'enctype' => TRUE,
                'inline'  => TRUE,
            ]).
            form_fileinput("upload_backup_file", '', "", [
                'inline'    => FALSE,
                'type'      => "object",
                "valid_ext" => $file_types,
                'template'  => 'modern',
            ]).
            form_button('restore', $locale['438'], $locale['438'], ['class' => 'btn-primary side-header-btn']).
            closeform()
        );
        closeside();

    }
}

// Add Migrate users here
function get_database_size($prefix = "") {
    global $db_name;
    $db_size = 0;
    $result = dbquery("SHOW TABLE STATUS FROM `".$db_name."`");
    while ($row = dbarray($result)) {
        if (!isset($row['Type'])) {
            $row['Type'] = "";
        }
        if (!isset($row['Engine'])) {
            $row['Engine'] = "";
        }
        if ((preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Type'])) || (preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i',
                $row['Engine'])) && (preg_match("/^".$prefix."/i",
                $row['Name']))
        ) {
            $db_size += $row['Data_length'] + $row['Index_length'];
        }
    }

    return $db_size;
}

function get_table_count($prefix = "") {
    global $db_name;
    $tbl_count = 0;
    $result = dbquery("SHOW TABLE STATUS FROM `".$db_name."`");
    while ($row = dbarray($result)) {
        if (!isset($row['Type'])) {
            $row['Type'] = "";
        }
        if (!isset($row['Engine'])) {
            $row['Engine'] = "";
        }
        if ((preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Type'])) || (preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i',
                $row['Engine'])) && (preg_match("/^".$prefix."/i",
                $row['Name']))
        ) {
            $tbl_count++;
        }
    }

    return $tbl_count;
}

function stripsiteinput($text) {
    $search = ["&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " "];
    $replace = ["", "", "", "", "", "", "", "", ""];
    return str_replace($search, $replace, $text);
}

function execute_restore() {
    $locale = fusion_get_locale();
    if (isset($_POST['btn_do_restore'])) {

        $result = @gzfile(ADMIN."db_backups/".stripinput($_POST['backup_file']));

        if ((preg_match("/# Database Name: `(.+?)`/i", $result[2], $tmp1)) && (preg_match("/# Table Prefix: `(.+?)`/i", $result[3],
                $tmp2)) && !defined('FUSION_NULL')
        ) {
            $restore_tblpre = form_sanitizer($_POST['restore_tblpre'], '', 'restore_tblpre');
            $inf_tblpre = $tmp2[1];
            $result = array_slice($result, 7);
            $results = preg_split("/;$/m", implode("", $result));

            if (count($_POST['list_tbl']) > 0) {
                foreach ($results as $result) {
                    $result = html_entity_decode($result, ENT_QUOTES, $locale['charset']);
                    if (preg_match("/^DROP TABLE IF EXISTS `(.*?)`/im", $result, $tmp)) {
                        $tbl = $tmp[1];
                        if (in_array($tbl, $_POST['list_tbl'])) {
                            $result = preg_replace("/^DROP TABLE IF EXISTS `$inf_tblpre(.*?)`/im", "DROP TABLE IF EXISTS `$restore_tblpre\\1`", $result);
                        }
                    }
                    if (preg_match("/^CREATE TABLE `(.*?)`/im", $result, $tmp)) {
                        $tbl = $tmp[1];
                        if (in_array($tbl, $_POST['list_tbl'])) {
                            $result = preg_replace("/^CREATE TABLE `$inf_tblpre(.*?)`/im", "CREATE TABLE `$restore_tblpre\\1`", $result);

                            dbquery($result);
                        }
                    }
                }
            }
            if (count($_POST['list_ins'])) {
                foreach ($results as $result) {
                    if (preg_match("/INSERT INTO `(.*?)`/i", $result, $tmp)) {
                        $ins = $tmp[1];
                        if (in_array($ins, $_POST['list_ins'])) {
                            $result = preg_replace("/INSERT INTO `$inf_tblpre(.*?)`/i", "INSERT INTO `$restore_tblpre\\1`", $result);

                            dbquery($result);

                        }
                    }
                }
            }
            add_notice("success", $locale['404']);
            redirect(FUSION_SELF.fusion_get_aidlink());

        } else {
            echo openform("frm_info", "post", clean_request('section=restore_db', ['action', 'section'], FALSE));
            echo "<h4>".$locale['400']."</h4>";
            echo $locale['401']."<br /><br />".$locale['402'];
            echo form_button('btn_cancel', $locale['403'], $locale['403'], ['class' => 'btn-default spacer-xs']);
            echo closeform();
        }
    }
}

function execute_backup() {
    global $db_name, $db_prefix, $db_driver, $pdo_enabled;

    if (isset($_POST['btn_create_backup'])) {
        ini_set('max_execution_time', 0);
        if (!ini_get('safe_mode')) {
            set_time_limit(600);
        }

        if (!check_admin_pass(isset($_POST['user_admin_password']) ? form_sanitizer($_POST['user_admin_password'], '', 'user_admin_password') : "")) {
            defender::stop();
        }
        $db_tables = $_POST['db_tables'];
        if (count($db_tables) && defender::safe()) {
            $crlf = "";
            ob_start();
            @ob_implicit_flush(0);
            echo "#----------------------------------------------------------".$crlf;
            echo "# PHPFusion SQL Data Dump".$crlf;
            echo "# Database Name: `".$db_name."`".$crlf;
            echo "# Table Prefix: `".$db_prefix."`".$crlf;
            echo "# Date: `".date("d/m/Y H:i")."`".$crlf;
            echo "#----------------------------------------------------------".$crlf;
            dbquery('SET SQL_QUOTE_SHOW_CREATE=1');
            foreach ($db_tables as $table) {
                if (!ini_get('safe_mode')) {
                    @set_time_limit(1200);
                }
                dbquery("OPTIMIZE TABLE $table");
                echo $crlf."#".$crlf."# Structure for Table `".$table."`".$crlf."#".$crlf;
                echo "DROP TABLE IF EXISTS `$table`;$crlf";
                $row = dbarraynum(dbquery("SHOW CREATE TABLE $table"));
                echo $row[1].";".$crlf;

                $db = DatabaseFactory::getConnection();
                $result = $db->query("SELECT * FROM $table");
                $column_list = '';
                $num_fields = '';

                if ($result && dbrows($result)) {
                    echo $crlf."#".$crlf."# Table Data for `".$table."`".$crlf."#".$crlf;

                    $num_fields = $db->countColumns($result);
                    for ($i = 0; $i < $num_fields; $i++) {
                        if ((!empty($db_driver) && $db_driver === 'pdo' || !empty($pdo_enabled) && $pdo_enabled === 1)) {
                            $column_meta = $result->getColumnMeta($i);
                            $column_list .= (($column_list != "") ? ", " : "")."`".$column_meta['name']."`";
                        } else {
                            $column_meta = $result->fetch_field();
                            $column_list .= (($column_list != "") ? ", " : "")."`".$column_meta->name."`";
                        }
                        unset($column_meta);
                    }
                }

                while ($row = dbarraynum($result)) {
                    $dump = "INSERT INTO `$table` ($column_list) VALUES (";
                    for ($i = 0; $i < $num_fields; $i++) {
                        $dump .= ($i > 0) ? ", " : "";
                        if (!isset($row[$i])) {
                            $dump .= "NULL";
                        } else if ($row[$i] == "0" || $row[$i] != "") {
                            $type = GetSqlFieldType($table, $i);
                            if (substr($type, 0, 7) == "tinyint" || substr($type, 0, 8) == "smallint" || substr($type, 0,
                                    9) == "mediumint" || substr($type, 0,
                                    3) == "int" || substr($type,
                                    0,
                                    6) == "bigint" || substr($type,
                                    0,
                                    9) == "timestamp"
                            ) {
                                $dump .= $row[$i];
                            } else {
                                $search_array = ['\\', '\'', "\x00", "\x0a", "\x0d", "\x1a"];
                                $replace_array = ['\\\\', '\\\'', '\0', '', '\r', '\Z'];
                                $row[$i] = str_replace($search_array, $replace_array, $row[$i]);
                                $dump .= "'$row[$i]'";
                            }
                        } else {
                            $dump .= "''";
                        }
                    }
                    $dump .= ");";
                    echo $dump.$crlf;
                }
            }
            $contents = ob_get_contents();
            ob_end_clean();

            $file = form_sanitizer($_POST['backup_filename'], '', 'backup_filename');
            $ext = form_sanitizer($_POST['backup_type'], '.sql', 'backup_type');

            require_once INCLUDES."class.httpdownload.php";
            $object = new \PHPFusion\httpdownload;
            $object->use_resume = FALSE;
            if ($ext == ".gz") {
                $object->set_mime("application/x-gzip gz tgz");
                $object->set_bydata(gzencode($contents, 9));
                $object->set_filename($file.'.sql'.$ext);
            } else {
                $object->set_mime("text/plain");
                $object->set_bydata($contents);
                $object->set_filename($file.$ext);
            }

            $object->download();
            exit;
        }
    }
}

function GetSqlFieldType($table, $i) {
    $new_data = [];

    $result = dbquery("SHOW COLUMNS FROM ".$table);
    while ($data = dbarray($result)) {
        $new_data[] = $data;
    }

    return $new_data[$i]['Type'];
}


class DbBackupAdministration {


    /*private function gzcompressfile($source, $level = FALSE) {
        $dest = $source.".gz";
        $mode = "wb".$level;
        $error = FALSE;
        if ($fp_out = gzopen($dest, $mode)) {
            if ($fp_in = fopen($source, "rb")) {
                while (!feof($fp_in)) {
                    gzputs($fp_out, fread($fp_in, 1024 * 512));
                }
                fclose($fp_in);
            } else {
                $error = TRUE;
            }
            gzclose($fp_out);
        } else {
            $error = TRUE;
        }
        if ($error) {
            return FALSE;
        } else {
            return $dest;
        }
    }*/


}

