<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: db_backup.php
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
pageAccess('DB');
require_once THEMES."templates/admin_header.php";

/**
 * Class db_backup
 */
class db_backup {
    private $locale = [];

    private function execute_backup() {
        global $db_name, $db_prefix, $pdo_enabled;
        if (isset($_POST['btn_create_backup'])) {
            ini_set('max_execution_time', 0);
            set_time_limit(600);
            if (!check_admin_pass(isset($_POST['user_admin_password']) ? form_sanitizer($_POST['user_admin_password'], '', 'user_admin_password') : "")) {
                defender::stop();
            }
            $db_tables = $_POST['db_tables'];
            if (count($db_tables) && defender::safe()) {
                $crlf = "\n";
                ob_start();
                @ob_implicit_flush(0);
                echo "#----------------------------------------------------------".$crlf;
                echo "# PHP-Fusion SQL Data Dump".$crlf;
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
                    $result = dbquery("SELECT * FROM $table");
                    if ($result && dbrows($result)) {
                        echo $crlf."#".$crlf."# Table Data for `".$table."`".$crlf."#".$crlf;
                        $column_list = "";
                        $num_fields = $result->columnCount();
                        for ($i = 0; $i < $num_fields; $i++) {
                            $column_meta = $result->getColumnMeta($i);
                            $column_list .= (($column_list != "") ? ", " : "")."`".$column_meta['name']."`";
                            unset($column_meta);
                        }
                    }
                    while ($row = dbarraynum($result)) {
                        $dump = "INSERT INTO `$table` ($column_list) VALUES (";
                        for ($i = 0; $i < $num_fields; $i++) {
                            $dump .= ($i > 0) ? ", " : "";
                            if (!isset($row[$i])) {
                                $dump .= "NULL";
                            } elseif ($row[$i] == "0" || $row[$i] != "") {
                                $type = $this->GetSqlFieldType($table, $i);
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
                                    $search_array = array('\\', '\'', "\x00", "\x0a", "\x0d", "\x1a");
                                    $replace_array = array('\\\\', '\\\'', '\0', '\n', '\r', '\Z');
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
                $file = $file.$ext;
                require_once INCLUDES."class.httpdownload.php";
                $object = new \PHPFusion\httpdownload;
                $object->use_resume = FALSE;
                if ($ext == ".gz") {
                    $object->set_mime("application/x-gzip gz tgz");
                    $object->set_bydata(gzencode($contents, 9));
                    $object->set_filename($file);
                } else {
                    $object->set_mime("text/plain");
                    $object->set_bydata($contents);
                    $object->set_filename($file);
                }
                $object->download();
                exit;
            }
        }
    }

    public function __display() {

        if (!isset($_POST['btn_do_restore']) && (!isset($_GET['action']) || $_GET['action'] != "restore")) {
            $backup_files = makefilelist(ADMIN."db_backups/", ".|..|index.php", TRUE);
            if (is_array($backup_files)) {
                foreach ($backup_files as $file) {
                    @unlink(ADMIN."db_backups/".$file);
                }
            }
        }

        $this->execute_backup();
        $this->locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/db-backup.php');
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'db_backup.php'.fusion_get_aidlink(), 'title' => $this->locale['450']]);

        $tab['title'][] = $this->locale['450'];
        $tab['id'][] = 'backup_db';

        $tab['title'][] = $this->locale['480'];
        $tab['id'][] = 'restore_db';

        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $tab['id']) ? $_GET['section'] : $tab['id'][0];
        opentable($this->locale['450']);
        echo opentab($tab, $_GET['section'], 'database_tab', TRUE, 'nav-tabs m-b-20', 'section', ['action', 'section']);
        switch ($_GET['section']) {
            case 'backup_db':
                $this->backup_form();
                break;
            case 'restore_db':
                $this->restore_form();
                break;
            default:
                redirect(clean_request('', ['section'], FALSE));
        }
        echo closetab();
        closetable();
    }

    private function restore_form() {
        global $pdo_enabled;

        if (isset($_POST['btn_do_restore'])) {

            $result = gzfile(ADMIN."db_backups/".stripinput($_POST['backup_file']));

            if ((preg_match("/# Database Name: `(.+?)`/i", $result[2], $tmp1)) && (preg_match("/# Table Prefix: `(.+?)`/i", $result[3],
                    $tmp2)) && !defined('FUSION_NULL')
            ) {
                $restore_tblpre = form_sanitizer($_POST['restore_tblpre'], '', 'restore_tblpre');
                $inf_dbname = $tmp1[1];
                $inf_tblpre = $tmp2[1];
                $result = array_slice($result, 7);
                $results = preg_split("/;$/m", implode("", $result));

                if (count($_POST['list_tbl']) > 0) {
                    foreach ($results as $result) {
                        $result = html_entity_decode($result, ENT_QUOTES, $this->locale['charset']);
                        if (preg_match("/^DROP TABLE IF EXISTS `(.*?)`/im", $result, $tmp)) {
                            $tbl = $tmp[1];
                            if (in_array($tbl, $_POST['list_tbl'])) {
                                $result = preg_replace("/^DROP TABLE IF EXISTS `$inf_tblpre(.*?)`/im", "DROP TABLE IF EXISTS `$restore_tblpre\\1`", $result);
                                $rct1 = dbquery($result);
                            }
                        }
                        if (preg_match("/^CREATE TABLE `(.*?)`/im", $result, $tmp)) {
                            $tbl = $tmp[1];
                            if (in_array($tbl, $_POST['list_tbl'])) {
                                $result = preg_replace("/^CREATE TABLE `$inf_tblpre(.*?)`/im", "CREATE TABLE `$restore_tblpre\\1`", $result);
                                if ($pdo_enabled == "1") {
                                    $rct2 = dbquery($result);
                                } else {
                                    mysql_unbuffered_query($result);
                                }
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
                                if ($pdo_enabled == "1") {
                                    $rct3 = dbquery($result);
                                } else {
                                    mysql_unbuffered_query($result);
                                }
                            }
                        }
                    }
                }
                addNotice("success", $this->locale['404']);
                redirect(FUSION_SELF.fusion_get_aidlink());
            } else {

                echo openform("frm_info", "post", clean_request('section=restore_db', ['action', 'section'], FALSE));
                echo "<h4>".$this->locale['400']."</h4>\n";
                echo "<div class='text-center list-group-item'>\n";
                echo $this->locale['401']."<br /><br />".$this->locale['402'];
                echo "</div>\n";
                echo form_button('btn_cancel', $this->locale['403'], $this->locale['403'], array('class' => 'btn-default spacer-xs'));
                echo closeform();
            }
        } elseif (isset($_GET['action']) && $_GET['action'] == "restore") {

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
            $info_tbls = array();
            $info_ins_cnt = array();
            $info_inserts = array();
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
                $table_opt_list .= "<option value='$info_tbl' selected='selected'>".$info_tbl."</option>\n";
            }
            $insert_ins_cnt = array_count_values($info_ins_cnt);
            $insert_opt_list = "";
            sort($info_inserts);
            foreach ($info_inserts as $key => $info_insert) {
                $insert_opt_list .= "<option value='".$info_insert."' selected='selected'>".$info_insert." (".$insert_ins_cnt[$info_insert].")</option>";
            }
            $maxrows = max(count($info_tbls), count($info_inserts));

            echo "<h4>".$this->locale['400']."</h4>";

            echo "<script type='text/javascript'>\n<!--\n";
            echo "function tableSelectAll(){for(i=0;i<document.restoreform.elements['list_tbl[]'].length;i++){document.restoreform.elements['list_tbl[]'].options[i].selected=true;}}\n";
            echo "function tableSelectNone(){for(i=0;i<document.restoreform.elements['list_tbl[]'].length;i++){document.restoreform.elements['list_tbl[]'].options[i].selected=false;}}\n";
            echo "function populateSelectAll(){for(i=0;i<document.restoreform.elements['list_ins[]'].length;i++){document.restoreform.elements['list_ins[]'].options[i].selected=true;}}\n";
            echo "function populateSelectNone(){for(i=0;i<document.restoreform.elements['list_ins[]'].length;i++){document.restoreform.elements['list_ins[]'].options[i].selected=false;}}\n";
            echo "//-->\n</script>\n";

            echo openform('confirm_restore_frm', 'post', FUSION_REQUEST, ['max_tokens' => 30]);
            echo "<div class='table-responsive'><table class='table'>\n<tbody>\n<tr>\n";
            echo "<td colspan='2' class='tbl2'><strong>".$this->locale['430']."</strong></td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td colspan='2' class='tbl'><strong>".$this->locale['431']."</strong> ".$backup_name."</td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td colspan='2' class='tbl'><strong>".$this->locale['414']."</strong> ".$info_dbname."</td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td colspan='2' class='tbl'><strong>".$this->locale['432']."</strong> ".$info_date."</td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td colspan='2' class='tbl'>\n";
            echo form_text('restore_tblpre', $this->locale['415'], $info_tblpref, array('required' => 1, 'error_text' => ''));
            echo form_hidden('backup_file', '', $file);
            echo "</td>\n</tr>\n<tr>\n";
            echo "<td valign='top' class='tbl'><strong>".$this->locale['433']."</strong><br />\n";
            echo "<select name='list_tbl[]' id='list_tbl' size='".$maxrows."' class='display-block textbox' style='width:100%;' multiple='multiple'>".$table_opt_list."</select>\n";
            echo "<div class='btn-group m-t-10' style='text-align:center'>\n";
            echo "<a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"javascript:tableSelectAll()\">".$this->locale['436']."</a>\n";
            echo "<a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"javascript:tableSelectNone()\">".$this->locale['437']."</a></div></td>\n";
            echo "<td valign='top' class='tbl'><strong>".$this->locale['434']."</strong><br />\n";
            echo "<select name='list_ins[]' id='list_ins' size='".$maxrows."' class='display-block textbox' style='width:100%;' multiple='multiple'>".$insert_opt_list."</select>\n";
            echo "<div class='btn-group m-t-10' style='text-align:center'><a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"javascript:populateSelectAll()\">".$this->locale['436']."</a>\n";
            echo "<a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"javascript:populateSelectNone()\">".$this->locale['437']."</a></div></td>\n";
            echo "</tr>\n<tr>\n";
            echo "<td align='center' colspan='2' class='tbl'>\n";
            echo "</tr>\n</tbody>\n</table>\n</div>";
            echo form_button('btn_do_restore', $this->locale['438'], $this->locale['438'], array('class' => 'btn-primary m-r-10'));
            echo form_button('btn_cancel', $this->locale['439'], $this->locale['439'], array('class' => 'btn-default'));
            echo closeform();

        } else {

            $file_types = (function_exists("gzencode")) ? ".gz " : ""; // added
            echo openform('restore', 'post', clean_request('action=restore', ['action'], FALSE), array('enctype' => 1, 'class' => 'spacer-xs'));
            echo "<div class='list-group-item'>\n";
            echo form_fileinput("upload_backup_file", $this->locale['431'], "", array(
                'inline'    => FALSE,
                'type'      => "object",
                "valid_ext" => $file_types,
                'template'  => 'modern',
            ));
            echo "<small>".$this->locale['440']." ".$file_types.".sql</small>\n"; // added
            echo "</div>\n";
            echo form_button('restore', $this->locale['438'], $this->locale['438'], array('class' => 'btn-primary spacer-sm',));
            echo closeform();

        }

    }

    private function backup_form() {
        global $db_name, $db_prefix;

        $table_opt_list = "";
        $result = dbquery("SHOW tables");
        while ($row = dbarraynum($result)) {
            $table_opt_list .= "<option value='".$row[0]."'";
            if (preg_match("/^".DB_PREFIX."/i", $row[0])) {
                $table_opt_list .= " selected='selected'";
            }
            $table_opt_list .= ">".$row[0]."</option>\n";
        }

        echo "<script type='text/javascript'>\n<!--\n";
        echo "function backupSelectCore(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=(document.backupform.elements['db_tables[]'].options[i].text).match(/^$db_prefix/i);}}\n";
        echo "function backupSelectAll(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=true;}}\n";
        echo "function backupSelectNone(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=false;}}\n";
        echo "//-->\n</script>\n";

        echo openform('backupform', 'post', FUSION_REQUEST);
        echo "<div class='row'>";

        echo '<div class="col-xs-12 col-sm-6">';
        echo "<div class='table-responsive'><table cellspacing='0' cellpadding='0' class='table'>\n<tbody>\n<tr>\n";
        echo "<td colspan='2' class='tbl2' align='left'><strong>".$this->locale['451']."</strong></td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td align='right' class='tbl'><strong>".$this->locale['414']."</strong></td>\n";
        echo "<td class='tbl'>".$db_name."</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td align='right' class='tbl'><strong>".$this->locale['415']."</strong></td>\n";
        echo "<td class='tbl'>".$db_prefix."</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td align='right' class='tbl'><strong>".$this->locale['452']."</strong></td>\n";
        echo "<td class='tbl'>".parsebytesize($this->get_database_size(), 2, FALSE)." (".$this->get_table_count()." ".$this->locale['419'].")</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td align='right' class='tbl'><strong>".$this->locale['453']."</strong></td>\n";
        echo "<td class='tbl'>".parsebytesize($this->get_database_size($db_prefix), 2, FALSE)." (".$this->get_table_count($db_prefix)." ".$this->locale['419'].")</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td align='left' colspan='2' class='tbl2'><strong>".$this->locale['454']."</strong></td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td align='right' class='tbl'><label for='backup_filename'>".$this->locale['431']." <span class='required'>*</span>\n</td>\n";
        echo "<td class='tbl'>\n";
        echo form_text('backup_filename', '', "backup_".$this->stripsiteinput(fusion_get_settings('sitename'))."_".date('Y-m-d-Hi')."", array(
            'required'   => 1,
            'error_text' => $this->locale['481b']
        ));
        echo "</tr>\n<tr>\n";
        echo "<td align='right' class='tbl'><label for='backup_type'>".$this->locale['455']."</label></td>\n";
        echo "<td class='tbl'>\n";
        $opts = array();
        if (function_exists("gzencode")) {
            $opts['.gz'] = ".sql.gz ".$this->locale['456'];
        }
        $opts['.sql'] = ".sql";
        echo form_select('backup_type', '', '', array('options' => $opts, 'placeholder' => $this->locale['choose']));
        echo "</td>\n</tr>\n<tr>\n";
        echo "<td align='center' colspan='2' class='tbl'><br /><span style='color:#ff0000'>*</span> ".$this->locale['461']."</td>\n";
        echo "</tr>\n</tbody>\n</table>\n</div>";
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-6">';
        echo "<div class='table-responsive'><table border='0' cellpadding='0' cellspacing='0' class='table'>\n<tbody>\n<tr>\n";
        echo "<td class='tbl2'><strong>".$this->locale['457']."</strong></td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl'>\n";
        echo "<select name='db_tables[]' id='tablelist' size='20' style='width:100%' class='textbox' multiple='multiple'>".$table_opt_list."</select>\n";
        echo "<div class='text-center m-t-10' style='text-align:center'><strong>".$this->locale['435']."</strong>\n";
        echo "<div class='btn-group'>\n";
        echo "<a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"javascript:backupSelectCore()\">".$this->locale['458']."</a>\n";
        echo "<a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"javascript:backupSelectAll()\">".$this->locale['436']."</a>\n";
        echo "<a class='btn btn-default' a href=\"javascript:void(0)\" onclick=\"javascript:backupSelectNone()\">".$this->locale['437']."</a>\n";
        echo "</div>\n";
        echo "</div>\n";
        echo "</td>\n</tr>\n</tbody>\n</table>\n</div>";
        echo '</div>';

        echo "</div>"; // .row
        echo form_button('btn_create_backup', $this->locale['459'], $this->locale['459'], array('class' => 'btn-primary m-t-10'));
        echo closeform();
    }

    private function stripsiteinput($text) {
        $search = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
        $replace = array("", "", "", "", "", "", "", "", "");
        $text = str_replace($search, $replace, $text);

        return $text;
    }

    private function get_database_size($prefix = "") {
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

    private function get_table_count($prefix = "") {
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

    private function gzcompressfile($source, $level = FALSE) {
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
    }

    private function GetSqlFieldType($table, $i) {
        $result = dbquery("SHOW COLUMNS FROM ".$table);
        while ($data = dbarray($result)) {
            $new_data[] = $data;
        }

        return $new_data[$i]['Type'];
    }
}

$backup_admin = new db_backup();
$backup_admin->__display();
require_once THEMES."templates/footer.php";