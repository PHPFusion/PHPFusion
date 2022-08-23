<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: db_backup.php
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
pageaccess('DB');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/db-backup.php');

add_breadcrumb(['link' => ADMIN.'db_backup.php'.fusion_get_aidlink(), 'title' => $locale['BACK_450']]);

$tabs['title'][] = $locale['BACK_450'];
$tabs['id'][] = 'backup_db';

$tabs['title'][] = $locale['BACK_480'];
$tabs['id'][] = 'restore_db';

$sections = check_get('section') && in_array(get('section'), $tabs['id']) ? get('section') : $tabs['id'][0];

opentable($locale['BACK_450']);
echo opentab($tabs, $sections, 'database_tab', TRUE, 'nav-tabs', 'section', ['action', 'section']);
switch ($sections) {
    case 'backup_db':
        backup_form();
        break;
    case 'restore_db':
        restore_form();
        break;
    default:
        redirect(clean_request('', ['section'], FALSE));
}
echo closetab();
closetable();

function backup_form() {
    global $db_name, $db_prefix, $db_driver;

    $locale = fusion_get_locale();

    if (check_post('btn_create_backup')) {
        ini_set('max_execution_time', 0);
        if (!ini_get('safe_mode')) {
            set_time_limit(600);
        }

        $db_tables = post(['db_tables']);
        if (count($db_tables) && fusion_safe()) {
            $crlf = "\n";
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

                $db = \PHPFusion\Database\DatabaseFactory::getConnection();
                $result = $db->query("SELECT * FROM $table");
                $column_list = '';
                $num_fields = '';

                if ($result && dbrows($result)) {
                    echo $crlf."#".$crlf."# Table Data for `".$table."`".$crlf."#".$crlf;

                    $num_fields = $db->countColumns($result);
                    for ($i = 0; $i < $num_fields; $i++) {
                        if (!empty($db_driver) && $db_driver === 'pdo' && extension_loaded('pdo_mysql')) {
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
                            $type = get_sql_field_type($table, $i);
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
                                $replace_array = ['\\\\', '\\\'', '\0', '\n', '\r', '\Z'];
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

            $file = sanitizer('backup_filename', '', 'backup_filename');
            $ext = sanitizer('backup_type', '.sql', 'backup_type');

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

    echo openform('backupform', 'post', FUSION_REQUEST);
    echo "<div class='row'>";

    echo '<div class="col-xs-12 col-sm-6">';
    openside($locale['BACK_451']);
    echo '<div class="m-b-5"><strong>'.$locale['BACK_414'].'</strong> '.$db_name.'</div>';
    echo '<div class="m-b-5"><strong>'.$locale['BACK_415'].'</strong> '.$db_prefix.'</div>';
    echo '<div class="m-b-5"><strong>'.$locale['BACK_452'].'</strong> '.parsebytesize(get_database_size()).' ('.get_table_count().' '.$locale['BACK_419'].')</div>';
    echo '<div class="m-b-5"><strong>'.$locale['BACK_453'].'</strong> '.parsebytesize(get_database_size($db_prefix)).' ('.get_table_count($db_prefix).' '.$locale['BACK_419'].')</div>';
    closeside();

    openside($locale['BACK_454']);
    echo form_text('backup_filename', $locale['BACK_431'], "backup_".stripsiteinput(fusion_get_settings('sitename'))."_".date('Y-m-d-Hi')."", [
        'required'   => TRUE,
        'error_text' => $locale['BACK_481b']
    ]);

    $opts = [];
    if (function_exists("gzencode")) {
        $opts['.gz'] = ".sql.gz ".$locale['BACK_456'];
    }
    $opts['.sql'] = ".sql";
    echo form_select('backup_type', $locale['BACK_455'], '', [
        'options'     => $opts,
        'placeholder' => $locale['choose']
    ]);

    closeside();
    echo '</div>';

    echo '<div class="col-xs-12 col-sm-6">';
    openside($locale['BACK_457']);

    $table_opt_list = "";
    $result = dbquery("SHOW tables");
    while ($row = dbarraynum($result)) {
        $table_opt_list .= "<option value='".$row[0]."'";
        if (preg_match("/^".DB_PREFIX."/i", $row[0])) {
            $table_opt_list .= " selected='selected'";
        }
        $table_opt_list .= ">".$row[0]."</option>\n";
    }

    add_to_jquery("
        function backupSelectCore(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=(document.backupform.elements['db_tables[]'].options[i].text).match(/^$db_prefix/i);}}
        function backupSelectAll(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=true;}}
        function backupSelectNone(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=false;}}

        $('#backupSelectCore').on('click', function (e) {e.preventDefault();backupSelectCore()});
        $('#backupSelectAll').on('click', function (e) {e.preventDefault();backupSelectAll()});
        $('#backupSelectNone').on('click', function (e) {e.preventDefault();backupSelectNone()});
    ");

    echo "<select name='db_tables[]' id='tablelist' size='20' style='width:100%' class='form-control textbox' multiple='multiple'>".$table_opt_list."</select>\n";

    echo "<div class='text-center m-t-10' style='text-align:center'><strong>".$locale['BACK_435']."</strong>\n";
    echo "<div class='btn-group'>\n";
    echo "<a class='btn btn-default' href='#' id='backupSelectCore'>".$locale['BACK_458']."</a>\n";
    echo "<a class='btn btn-default' href='#' id='backupSelectAll'>".$locale['BACK_436']."</a>\n";
    echo "<a class='btn btn-default' href='#' id='backupSelectNone'>".$locale['BACK_437']."</a>\n";
    echo "</div>";
    echo "</div>";
    closeside();
    echo '</div>';

    echo "</div>"; // .row
    echo form_button('btn_create_backup', $locale['BACK_459'], $locale['BACK_459'], ['class' => 'btn-primary m-t-10']);
    echo closeform();
}

function restore_form() {
    $locale = fusion_get_locale();

    if (!check_post('btn_do_restore') && (!check_get('action') || get('action') != "restore")) {
        $backup_files = makefilelist(ADMIN."db_backups/", ".|..|index.php");
        if (is_array($backup_files)) {
            foreach ($backup_files as $file) {
                @unlink(ADMIN."db_backups/".$file);
            }
        }
    }

    if (check_post('btn_do_restore')) {
        $result = gzfile(ADMIN."db_backups/".stripinput(post('backup_file')));

        if ((preg_match("/# Database Name: `(.+?)`/i", $result[2], $tmp1)) &&
            (preg_match("/# Table Prefix: `(.+?)`/i", $result[3], $tmp2)) &&
            !defined('FUSION_NULL')) {
            $restore_tblpre = sanitizer('restore_tblpre', '', 'restore_tblpre');
            $inf_tblpre = $tmp2[1];
            $result = array_slice($result, 7);
            $results = preg_split("/;$/m", implode("", $result));

            if (count(post('list_tbl')) > 0) {
                foreach ($results as $result) {
                    $result = html_entity_decode($result, ENT_QUOTES, $locale['charset']);
                    if (preg_match("/^DROP TABLE IF EXISTS `(.*?)`/im", $result, $tmp)) {
                        $tbl = $tmp[1];
                        if (in_array($tbl, post('list_tbl'))) {
                            $result = preg_replace("/^DROP TABLE IF EXISTS `$inf_tblpre(.*?)`/im", "DROP TABLE IF EXISTS `$restore_tblpre\\1`", $result);
                        }
                    }
                    if (preg_match("/^CREATE TABLE `(.*?)`/im", $result, $tmp)) {
                        $tbl = $tmp[1];
                        if (in_array($tbl, post('list_tbl'))) {
                            $result = preg_replace("/^CREATE TABLE `$inf_tblpre(.*?)`/im", "CREATE TABLE `$restore_tblpre\\1`", $result);

                            dbquery($result);
                        }
                    }
                }
            }
            if (count(post('list_ins'))) {
                foreach ($results as $result) {
                    if (preg_match("/INSERT INTO `(.*?)`/i", $result, $tmp)) {
                        $ins = $tmp[1];
                        if (in_array($ins, post('list_ins'))) {
                            $result = preg_replace("/INSERT INTO `$inf_tblpre(.*?)`/i", "INSERT INTO `$restore_tblpre\\1`", $result);

                            dbquery($result);

                        }
                    }
                }
            }
            addnotice('success', $locale['BACK_404']);
            redirect(FUSION_SELF.fusion_get_aidlink());
        } else {
            echo openform("frm_info", "post", clean_request('section=restore_db', ['action', 'section'], FALSE));
            echo "<h4>".$locale['BACK_400']."</h4>\n";
            echo $locale['BACK_401']."<br /><br />".$locale['BACK_402'];
            echo form_button('btn_cancel', $locale['BACK_403'], $locale['BACK_403'], ['class' => 'btn-default spacer-xs']);
            echo closeform();
        }
    } else if (check_get('action') && get('action') == "restore") {
        $backup_data = [];
        $backup_name = '';
        $file = '';

        if (!empty($_FILES['upload_backup_file']) && is_uploaded_file($_FILES['upload_backup_file']['tmp_name'])) {
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
        foreach ($info_tbls as $info_tbl) {
            $table_opt_list .= "<option value='$info_tbl' selected='selected'>".$info_tbl."</option>\n";
        }
        $insert_ins_cnt = array_count_values($info_ins_cnt);
        $insert_opt_list = "";
        sort($info_inserts);
        foreach ($info_inserts as $info_insert) {
            $insert_opt_list .= "<option value='".$info_insert."' selected='selected'>".$info_insert." (".$insert_ins_cnt[$info_insert].")</option>";
        }
        $maxrows = max(count($info_tbls), count($info_inserts));

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

        echo "<h4>".$locale['BACK_400']."</h4>";

        openside($locale['BACK_430']);
        echo openform('confirm_restore_frm', 'post', FUSION_REQUEST, ['max_tokens' => 30]);

        echo '<div class="m-b-5"><strong>'.$locale['BACK_431'].'</strong> '.$backup_name.'</div>';
        echo '<div class="m-b-5"><strong>'.$locale['BACK_414'].'</strong> '.$info_dbname.'</div>';
        echo '<div class="m-b-5"><strong>'.$locale['BACK_432'].'</strong> '.$info_date.'</div>';

        echo form_text('restore_tblpre', $locale['BACK_415'], $info_tblpref, ['required' => TRUE]);
        echo form_hidden('backup_file', '', $file);

        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-6">';
        echo "<label for='list_tbl'>".$locale['BACK_433']."</label>";
        echo "<select name='list_tbl[]' id='list_tbl' size='".$maxrows."' class='form-control display-block textbox' style='width:100%;' multiple='multiple'>".$table_opt_list."</select>";
        echo "<div class='btn-group m-t-10' style='text-align:center'>\n";
        echo "<a class='btn btn-default' href='#' id='tableSelectAll'>".$locale['BACK_436']."</a>";
        echo "<a class='btn btn-default' href='#' id='tableSelectNone'>".$locale['BACK_437']."</a>";
        echo "</div>";
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-6">';

        echo "<label for='list_ins'>".$locale['BACK_434']."</label>\n";
        echo "<select name='list_ins[]' id='list_ins' size='".$maxrows."' class='form-control display-block textbox' style='width:100%;' multiple='multiple'>".$insert_opt_list."</select>";
        echo "<div class='btn-group m-t-10' style='text-align:center;'>";
        echo "<a class='btn btn-default' href='#' id='populateSelectAll'>".$locale['BACK_436']."</a>";
        echo "<a class='btn btn-default' href='#' id='populateSelectNone'>".$locale['BACK_437']."</a>";
        echo "</div>";

        echo '</div>';
        echo '</div>';

        echo '<div class="m-t-10">';
        echo form_button('btn_do_restore', $locale['BACK_438'], $locale['BACK_438'], ['class' => 'btn-primary m-r-10']);
        echo form_button('btn_cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-default']);
        echo '</div>';
        echo closeform();

        closeside();
    } else {
        $file_types = function_exists('gzencode') ? ",.gz" : "";
        echo openform('restoreform', 'post', clean_request('section=restore_db&action=restore', ['action', 'section'], FALSE), ['enctype' => TRUE]);

        echo form_fileinput("upload_backup_file", $locale['BACK_431'], "", [
            'inline'      => FALSE,
            'type'        => 'object',
            'valid_ext'   => '.sql'.$file_types,
            'template'    => 'modern',
            'ext_tip'     => $locale['BACK_440'].' .sql'.$file_types,
            'upload_path' => ADMIN.'db_backups/'
        ]);

        echo form_button('restore', $locale['BACK_438'], $locale['BACK_438'], ['class' => 'btn-primary spacer-sm',]);
        echo closeform();
    }
}

function get_sql_field_type($table, $i) {
    $new_data = [];

    $result = dbquery("SHOW COLUMNS FROM ".$table);
    while ($data = dbarray($result)) {
        $new_data[] = $data;
    }

    return $new_data[$i]['Type'];
}

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

require_once THEMES.'templates/footer.php';
