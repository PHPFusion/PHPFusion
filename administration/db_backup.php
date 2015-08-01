<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: db_backup.php
| Author: Nick Jones (Digitanium)
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
include LOCALE.LOCALESET."admin/db-backup.php";
add_breadcrumb(array('link' => ADMIN.'db_backup.php'.$aidlink, 'title' => $locale['450']));
function stripsiteinput($text) {
	$search = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
	$replace = array("", "", "", "", "", "", "", "", "");
	$text = str_replace($search, $replace, $text);
	return $text;
}

if (isset($_POST['btn_create_backup'])) {
	$backup_file_name = form_sanitizer($_POST['backup_filename'], '', 'backup_filename');
	if (!check_admin_pass(isset($_POST['user_admin_password']) ? form_sanitizer($_POST['user_admin_password'], '', 'user_admin_password') : "")) {
		$defender->stop();
	}
	$db_tables = $_POST['db_tables'];
	if (count($db_tables) && !defined('FUSION_NULL')) {
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
				if ($pdo_enabled == "1") {
					$column_list = "";
					$num_fields = $result->columnCount();
					for ($i = 0; $i < $num_fields; $i++) {
						$column_meta = $result->getColumnMeta($i);
						$column_list .= (($column_list != "") ? ", " : "")."`".$column_meta['name']."`";
						unset($column_meta);
					}
				} else {
					$num_fields = mysql_num_fields($result);
					for ($i = 0; $i < $num_fields; $i++) {
						$column_list .= (($column_list != "") ? ", " : "")."`".mysql_field_name($result, $i)."`";
					}
				}
			}
			while ($row = dbarraynum($result)) {
				$dump = "INSERT INTO `$table` ($column_list) VALUES (";
				for ($i = 0; $i < $num_fields; $i++) {
					$dump .= ($i > 0) ? ", " : "";
					if (!isset($row[$i])) {
						$dump .= "NULL";
					} elseif ($row[$i] == "0" || $row[$i] != "") {
						if ($pdo_enabled == "1") {
							$type = GetSqlFieldType($table, $i);
						} else {
							$type = mysql_field_type($result, $i);
						}
						if (substr($type, 0, 7) == "tinyint" || substr($type, 0, 8) == "smallint" || substr($type, 0, 9) == "mediumint" || substr($type, 0, 3) == "int" || substr($type, 0, 6) == "bigint" || substr($type, 0, 9) == "timestamp") {
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
		$file = stripinput($_POST['backup_filename']).".sql";
		require_once INCLUDES."class.httpdownload.php";
		$object = new httpdownload;
		$object->use_resume = FALSE;
		if ($_POST['backup_type'] == ".gz") {
			$object->use_resume = FALSE;
			$object->set_mime("application/x-gzip gz tgz");
			$object->set_bydata(gzencode($contents, 9));
			$object->set_filename($file.".gz");
		} else {
			$object->use_resume = FALSE;
			$object->set_mime("text/plain");
			$object->set_bydata($contents);
			$object->set_filename($file);
		}
		$object->download();
		exit;
	}
}
if (!isset($_POST['btn_do_restore']) && (!isset($_GET['action']) || $_GET['action'] != "restore")) {
	$backup_files = makefilelist(ADMIN."db_backups/", ".|..|index.php", TRUE);
	if (is_array($backup_files)) {
		foreach ($backup_files as $file) {
			@unlink(ADMIN."db_backups/".$files);
		}
	}
}
if (isset($_POST['btn_do_restore'])) {
	if (!check_admin_pass(isset($_POST['user_admin_password']) ? stripinput($_POST['user_admin_password']) : "")) {
		$defender->stop();
	}
	$table_pre = form_sanitizer($_POST['restore_tblpre'], '', 'restore_tblpre');
	$result = gzfile(ADMIN."db_backups/".$_POST['file']);
	if ((preg_match("/# Database Name: `(.+?)`/i", $result[2], $tmp1)) && (preg_match("/# Table Prefix: `(.+?)`/i", $result[3], $tmp2)) && !defined('FUSION_NULL')) {
		$restore_tblpre = stripinput($_POST['restore_tblpre']);
		$inf_dbname = $tmp1[1];
		$inf_tblpre = $tmp2[1];
		$result = array_slice($result, 7);
		$results = preg_split("/;$/m", implode("", $result));
		if (count($_POST['list_tbl']) > 0) {
			foreach ($results as $result) {
				$result = html_entity_decode($result, ENT_QUOTES);
				if (preg_match("/^DROP TABLE IF EXISTS `(.*?)`/im", $result, $tmp)) {
					$tbl = $tmp[1];
					if (in_array($tbl, $_POST['list_tbl'])) {
						$result = preg_replace("/^DROP TABLE IF EXISTS `$inf_tblpre(.*?)`/im", "DROP TABLE IF EXISTS `$restore_tblpre\\1`", $result);
						if ($pdo_enabled == "1") {
							$rct1 = dbconnection()->prepare($result, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE));
							$rct1->execute();
						} else {
							mysql_unbuffered_query($result);
						}
					}
				}
				if (preg_match("/^CREATE TABLE `(.*?)`/im", $result, $tmp)) {
					$tbl = $tmp[1];
					if (in_array($tbl, $_POST['list_tbl'])) {
						$result = preg_replace("/^CREATE TABLE `$inf_tblpre(.*?)`/im", "CREATE TABLE `$restore_tblpre\\1`", $result);
						if ($pdo_enabled == "1") {
							$rct2 = dbconnection()->prepare($result, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE));
							$rct2->execute();
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
							$rct3 = dbconnection()->prepare($result, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE));
							$rct3->execute();
						} else {
							mysql_unbuffered_query($result);
						}
					}
				}
			}
		}
		@unlink(ADMIN."db_backups/temp.txt");
		//redirect(FUSION_SELF.$aidlink);
	} else {
		opentable($locale['400']);
		echo "<div style='text-align:center'>".$locale['401']."<br /><br />".$locale['402']."<br /><br />\n";
		echo "<form action='".FUSION_SELF.$aidlink."' name='frm_info' method='post'>\n";
		echo form_button('btn_cancel', $locale['403'], $locale['403'], array('class' => 'btn-primary'));
		echo "</form>\n</div>\n";
		closetable();
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
		redirect(FUSION_SELF.$aidlink);
	}
	$info_dbname = "";
	$info_date = "";
	$info_tblpref = "";
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
	$table_opt_list = "";
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
	opentable($locale['400']);
	echo "<script type='text/javascript'>\n<!--\n";
	echo "function tableSelectAll(){for(i=0;i<document.restoreform.elements['list_tbl[]'].length;i++){document.restoreform.elements['list_tbl[]'].options[i].selected=true;}}\n";
	echo "function tableSelectNone(){for(i=0;i<document.restoreform.elements['list_tbl[]'].length;i++){document.restoreform.elements['list_tbl[]'].options[i].selected=false;}}\n";
	echo "function populateSelectAll(){for(i=0;i<document.restoreform.elements['list_ins[]'].length;i++){document.restoreform.elements['list_ins[]'].options[i].selected=true;}}\n";
	echo "function populateSelectNone(){for(i=0;i<document.restoreform.elements['list_ins[]'].length;i++){document.restoreform.elements['list_ins[]'].options[i].selected=false;}}\n";
	echo "//-->\n</script>\n";
	echo openform('restoreform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
	echo "<table align='center' cellspacing='0' cellpadding='0' class='table table-responsive'>\n<tbody>\n<tr>\n";
	echo "<td colspan='2' class='tbl2'><strong>".$locale['430']."</strong></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'><strong>".$locale['431']."</strong> ".$backup_name."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'><strong>".$locale['414']."</strong> ".$info_dbname."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'><strong>".$locale['432']."</strong> ".$info_date."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'>\n";
	echo form_text('restore_tblpre', $locale['415'], $info_tblpref, array('required' => 1, 'error_text' => ''));
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td valign='top' class='tbl'><strong>".$locale['433']."</strong><br />\n";
	echo "<select name='list_tbl[]' id='list_tbl' size='".$maxrows."' class='display-block textbox' style='width:100%;' multiple='multiple'>".$table_opt_list."</select>\n";
	echo "<div class='btn-group m-t-10' style='text-align:center'>\n";
	echo "<a class='btn btn-primary' href=\"javascript:void(0)\" onclick=\"javascript:tableSelectAll()\">".$locale['436']."</a>\n";
	echo "<a class='btn btn-primary' href=\"javascript:void(0)\" onclick=\"javascript:tableSelectNone()\">".$locale['437']."</a></div></td>\n";
	echo "<td valign='top' class='tbl'><strong>".$locale['434']."</strong><br />\n";
	echo "<select name='list_ins[]' id='list_ins' size='".$maxrows."' class='display-block textbox' style='width:100%;' multiple='multiple'>".$insert_opt_list."</select>\n";
	echo "<div class='btn-group m-t-10' style='text-align:center'><a class='btn btn-primary' href=\"javascript:void(0)\" onclick=\"javascript:populateSelectAll()\">".$locale['436']."</a>\n";
	echo "<a class='btn btn-primary' href=\"javascript:void(0)\" onclick=\"javascript:populateSelectNone()\">".$locale['437']."</a></div></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'>\n";
	echo form_text('user_admin_password', $locale['460'], '', array('type' => 'password',
		'required' => 1,
		'error_text' => $locale['460b'],
		'inline' => 1));
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input type='hidden' name='file' value='$file' />\n";
	echo form_button('btn_do_restore', $locale['438'], $locale['438'], array('class' => 'btn-primary m-r-10'));
	echo form_button('btn_cancel', $locale['439'], $locale['439'], array('class' => 'btn-primary m-r-10'));
	echo "</tr>\n</tbody>\n</table>\n";
	echo closeform();
	closetable();
} else {
	$table_opt_list = "";
	$result = dbquery("SHOW tables");
	while ($row = dbarraynum($result)) {
		$table_opt_list .= "<option value='".$row[0]."'";
		if (preg_match("/^".DB_PREFIX."/i", $row[0])) {
			$table_opt_list .= " selected='selected'";
		}
		$table_opt_list .= ">".$row[0]."</option>\n";
	}
	opentable($locale['450']);
	echo "<script type='text/javascript'>\n<!--\n";
	echo "function backupSelectCore(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=(document.backupform.elements['db_tables[]'].options[i].text).match(/^$db_prefix/);}}\n";
	echo "function backupSelectAll(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=true;}}\n";
	echo "function backupSelectNone(){for(i=0;i<document.backupform.elements['db_tables[]'].length;i++){document.backupform.elements['db_tables[]'].options[i].selected=false;}}\n";
	echo "//-->\n</script>\n";
	echo openform('backupform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
	echo "<table align='center' cellspacing='0' cellpadding='0' class='table table-responsive'>\n<tbody>\n<tr>\n";
	echo "<td valign='top'>\n";
	echo "<table cellspacing='0' cellpadding='0' class='table table-responsive'>\n<tbody>\n<tr>\n";
	echo "<td colspan='2' class='tbl2' align='left'><strong>".$locale['451']."</strong></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><strong>".$locale['414']."</strong></td>\n";
	echo "<td class='tbl'>".$db_name."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><strong>".$locale['415']."</strong></td>\n";
	echo "<td class='tbl'>".$db_prefix."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><strong>".$locale['452']."</strong></td>\n";
	echo "<td class='tbl'>".parsebytesize(get_database_size(), 2, FALSE)." (".get_table_count()." ".$locale['419'].")</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><strong>".$locale['453']."</strong></td>\n";
	echo "<td class='tbl'>".parsebytesize(get_database_size($db_prefix), 2, FALSE)." (".get_table_count($db_prefix)." ".$locale['419'].")</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='left' colspan='2' class='tbl2'><strong>".$locale['454']."</strong></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><label for='backup_filename'>".$locale['431']." <span class='required'>*</span>\n</td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('backup_filename', '', "backup_".stripsiteinput($settings['sitename'])."_".date('Y-m-d-Hi')."", array('required' => 1,
		'error_text' => $locale['481b']));
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><label for='backup_type'>".$locale['455']."</label></td>\n";
	echo "<td class='tbl'>\n";
	$opts = array();
	if (function_exists("gzencode")) {
		$opts['.gz'] = ".sql.gz ".$locale['456'];
	}
	$opts['.sql'] = ".sql";
	echo form_select('backup_type', '', '', array('options' => $opts, 'placeholder' => $locale['choose']));
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'><label for='user_admin_password'>".$locale['460']."</label> <span class='required'>*</span></td>\n";
	echo "<td class='tbl'>\n";
	echo form_text('user_admin_password', '', '', array('type' => 'password',
		'required' => 1,
		'error_text' => $locale['460b']));
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><br /><span style='color:#ff0000'>*</span> ".$locale['461']."</td>\n";
	echo "</tr>\n</tbody>\n</table>\n</td>\n";
	echo "<td valign='top'>\n";
	echo "<table border='0' cellpadding='0' cellspacing='0' class='table table-responsive'>\n<tbody>\n<tr>\n";
	echo "<td class='tbl2'><strong>".$locale['457']."</strong></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>\n";
	echo "<select name='db_tables[]' id='tablelist' size='20' style='width:100%' class='textbox' multiple='multiple'>".$table_opt_list."</select>\n";
	echo "<div class='text-center m-t-10' style='text-align:center'><strong>".$locale['435']."</strong>\n";
	echo "<div class='btn-group'>\n";
	echo "<a class='btn btn-primary' href=\"javascript:void(0)\" onclick=\"javascript:backupSelectCore()\">".$locale['458']."</a>\n";
	echo "<a class='btn btn-primary' href=\"javascript:void(0)\" onclick=\"javascript:backupSelectAll()\">".$locale['436']."</a>\n";
	echo "<a class='btn btn-primary' a href=\"javascript:void(0)\" onclick=\"javascript:backupSelectNone()\">".$locale['437']."</a>\n";
	echo "</div>\n";
	echo "</div>\n";
	echo "</td>\n</tr>\n</tbody>\n</table>\n";
	echo "</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>";
	echo form_button('btn_create_backup', $locale['459'], $locale['459'], array('class' => 'btn-primary'));
	echo "</td>\n</tr>\n</tbody>\n</table>\n</form>\n";
	closetable();
	opentable($locale['480']);
	$file_types = (function_exists("gzencode")) ? ".gz " : ""; // added
	echo openform('restore', 'post', FUSION_SELF.$aidlink."&amp;action=restore", array('enctype' => 1));
	echo "<table class='table table-responsive'>\n<tbody>\n<tr>\n";
	echo "<td class='tbl'>\n<label for='upload_backup_file'>".$locale['431']."</label>\n</td>\n<td class='tbl'>\n";
	echo "<input type='file' name='upload_backup_file' class='textbox' />\n"; // edited
	echo "<small>".$locale['440']." ".$file_types.".sql</small>\n"; // added
	echo "</td>\n</tr>\n<tr>\n<td colspan='2' class='tbl'>\n";
	echo form_button('restore', $locale['438'], $locale['438'], array('class' => 'btn-primary'));
	echo "</td>\n</tr>\n</tbody>\n</table>\n";
	echo closeform();
	closetable();
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
		if ((preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Type'])) || (preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Engine'])) && (preg_match("/^".$prefix."/", $row['Name']))) {
			$db_size += $row['Data_length']+$row['Index_length'];
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
		if ((preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Type'])) || (preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Engine'])) && (preg_match("/^".$prefix."/", $row['Name']))) {
			$tbl_count++;
		}
	}
	return $tbl_count;
}

function gzcompressfile($source, $level = FALSE) {
	$dest = $source.".gz";
	$mode = "wb".$level;
	$error = FALSE;
	if ($fp_out = gzopen($dest, $mode)) {
		if ($fp_in = fopen($source, "rb")) {
			while (!feof($fp_in)) {
				gzputs($fp_out, fread($fp_in, 1024*512));
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

function GetSqlFieldType($table, $i) {
	$result = dbquery("SHOW COLUMNS FROM ".$table);
	while ($data = dbarray($result)) {
		$new_data[] = $data;
	}
	return $new_data[$i]['Type'];
}

require_once THEMES."templates/footer.php";
