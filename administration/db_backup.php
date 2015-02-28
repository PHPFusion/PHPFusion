<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
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

if (!checkrights("DB") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) redirect("../index.php");

// Unstrip text
function stripsiteinput($text) {
	$search = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
	$replace = array("", "", "", "", "", "", "", "", "");
	$text = str_replace($search, $replace, $text);
	return $text;
}

if (isset($_POST['btn_create_backup'])) {
	if (!check_admin_pass(isset($_POST['user_admin_password']) ? stripinput($_POST['user_admin_password']) : "")) {
		redirect(FUSION_SELF.$aidlink."&status=pw");
	}
	$db_tables = $_POST['db_tables'];
	if (count($db_tables)) {
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
				$num_fields= mysql_num_fields($result);
				for ($i = 0; $i < $num_fields; $i++) {
					$column_list .= (($column_list != "") ? ", " : "")."`".mysql_field_name($result, $i)."`";
				}
			}
			while ($row = dbarraynum($result)) {
				$dump = "INSERT INTO `$table` ($column_list) VALUES (";
				for ($i = 0; $i < $num_fields; $i++) {
					$dump .= ($i > 0) ? ", " : "";
					if (!isset($row[$i])) {
						$dump .= "NULL";
					} elseif ($row[$i] == "0" || $row[$i] != ""){
						$type = mysql_field_type($result, $i);
						if ($type == "tinyint" || $type == "smallint" || $type == "mediumint" || $type == "int" || $type == "bigint"|| $type == "timestamp") {
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
		$object->use_resume = false;
		if ($_POST['backup_type'] == ".gz") {
 			$object->use_resume = false;
			$object->set_mime("application/x-gzip gz tgz");
			$object->set_bydata(gzencode($contents, 9));
			$object->set_filename($file.".gz");
		} else {
 			$object->use_resume = false;
			$object->set_mime("text/plain");
			$object->set_bydata($contents);
			$object->set_filename($file);
		}
		$object->download();
		exit;
	}
	redirect(FUSION_SELF.$aidlink);
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/db-backup.php";

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "pw") {
		$message = $locale['global_182'];
	}
	if ($message) {	echo "<div id='close-message'><div class='admin-message'>".$message."</div></div>\n"; }
}

if (!isset($_POST['btn_do_restore']) && (!isset($_GET['action']) || $_GET['action'] != "restore")) {
	$backup_files = makefilelist(ADMIN."db_backups/", ".|..|index.php", true);
	if (is_array($backup_files) && count($backup_files) > 0) {
		for ($i = 0; $i < count($backup_files); $i++) {
			@unlink(ADMIN."db_backups/".$backup_files[$i]);
		}
	}
}

if (isset($_POST['btn_do_restore'])) {
	if (!check_admin_pass(isset($_POST['user_admin_password']) ? stripinput($_POST['user_admin_password']) : "")) {
		redirect(FUSION_SELF.$aidlink."&status=pw");
	}
	$result = gzfile(ADMIN."db_backups/".$_POST['file']);
	if ((preg_match("/# Database Name: `(.+?)`/i", $result[2], $tmp1)) && (preg_match("/# Table Prefix: `(.+?)`/i", $result[3], $tmp2))) {
		$restore_tblpre = stripinput($_POST['restore_tblpre']);
		$inf_dbname = $tmp1[1];
		$inf_tblpre = $tmp2[1];
		$result = array_slice($result, 7);
		$results = preg_split("/;$/m", implode("",$result));
		if (count($_POST['list_tbl']) > 0) {
			foreach ($results as $result){
				$result = html_entity_decode($result, ENT_QUOTES);
				if (preg_match("/^DROP TABLE IF EXISTS `(.*?)`/im",$result,$tmp)) {
					$tbl = $tmp[1];
					if (in_array($tbl, $_POST['list_tbl'])) {
						$result = preg_replace("/^DROP TABLE IF EXISTS `$inf_tblpre(.*?)`/im","DROP TABLE IF EXISTS `$restore_tblpre\\1`",$result);
						mysql_unbuffered_query($result);
					}
				}
				if (preg_match("/^CREATE TABLE `(.*?)`/im",$result,$tmp)) {
					$tbl = $tmp[1];
					if (in_array($tbl, $_POST['list_tbl'])) {
						$result = preg_replace("/^CREATE TABLE `$inf_tblpre(.*?)`/im","CREATE TABLE `$restore_tblpre\\1`",$result);
						mysql_unbuffered_query($result);
					}
				}
			}
		}
		if (count($_POST['list_ins'])) {
			foreach($results as $result){
				if (preg_match("/INSERT INTO `(.*?)`/i",$result,$tmp)) {
					$ins = $tmp[1];
					if (in_array($ins, $_POST['list_ins'])) {
						$result = preg_replace("/INSERT INTO `$inf_tblpre(.*?)`/i","INSERT INTO `$restore_tblpre\\1`",$result);
						mysql_unbuffered_query($result);
					}
				}
			}
		}
		@unlink(ADMIN."db_backups/temp.txt");
		redirect(FUSION_SELF.$aidlink);
	} else {
		opentable($locale['400']);
		echo "<div style='text-align:center'>".$locale['401']."<br /><br />".$locale['402']."<br /><br />\n";
		echo "<form action='".FUSION_SELF.$aidlink."' name='frm_info' method='post'>\n";
		echo "<input class='button' type='submit' name='btn_cancel' style='width:100px;' value='".$locale['403']."' />\n";
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
	$info_dbname = ""; $info_date = ""; $info_tblpref = ""; $info_tbls = array(); $info_ins_cnt = array(); $info_inserts = array();
	foreach ($backup_data as $resultline) {
		if (preg_match_all("/^# Database Name: `(.*?)`/", $resultline, $resultinfo)) { $info_dbname = $resultinfo[1][0]; }
		if (preg_match_all("/^# Table Prefix: `(.*?)`/", $resultline, $resultinfo)) { $info_tblpref = $resultinfo[1][0]; }
		if (preg_match_all("/^# Date: `(.*?)`/", $resultline, $resultinfo)) { $info_date = $resultinfo[1][0]; }
		if (preg_match_all("/^CREATE TABLE `(.+?)`/i", $resultline, $resultinfo)) { $info_tbls[] = $resultinfo[1][0]; }
		if (preg_match_all("/^INSERT INTO `(.+?)`/i", $resultline, $resultinfo)) {
			if (!in_array($resultinfo[1][0], $info_inserts)) { $info_inserts[] = $resultinfo[1][0]; }
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
	echo "<form name='restoreform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
	echo "<table align='center' cellspacing='0' cellpadding='0'>\n<tr>\n";
	echo "<td colspan='2' class='tbl2'>".$locale['430']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'>".$locale['431']." ".$backup_name."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'>".$locale['414']." ".$info_dbname."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'>".$locale['432']." ".$info_date."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td colspan='2' class='tbl'>".$locale['415']." <input class='textbox' type='text' name='restore_tblpre' value='".$info_tblpref."' style='width:150px' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td valign='top' class='tbl'>".$locale['433']."<br />\n";
	echo "<select name='list_tbl[]' id='list_tbl' size='".$maxrows."' class='textbox' style='width:180px;' multiple='multiple'>".$table_opt_list."</select>\n";
	echo "<div style='text-align:center'>".$locale['435']." [<a href=\"javascript:void(0)\" onclick=\"javascript:tableSelectAll()\">".$locale['436']."</a>]\n";
	echo "[<a href=\"javascript:void(0)\" onclick=\"javascript:tableSelectNone()\">".$locale['437']."</a>]</div></td>\n";
	echo "<td valign='top' class='tbl'>".$locale['434']."<br />\n";
	echo "<select name='list_ins[]' id='list_ins' size='".$maxrows."' class='textbox' style='width:180px;' multiple='multiple'>".$insert_opt_list."</select>\n";
	echo "<div style='text-align:center'>".$locale['435']." [<a href=\"javascript:void(0)\" onclick=\"javascript:populateSelectAll()\">".$locale['436']."</a>]\n";
	echo "[<a href=\"javascript:void(0)\" onclick=\"javascript:populateSelectNone()\">".$locale['437']."</a>]</div></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><hr />".$locale['460']." <span style='color:#ff0000'>*</span>\n";
	echo "<input type='password' name='user_admin_password' value='' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'>\n";
	echo "<input type='hidden' name='file' value='$file' />\n";
	echo "<input class='button' type='submit' name='btn_do_restore' style='width:100px;' value='".$locale['438']."' />\n";
	echo "<input class='button' type='submit' name='btn_cancel' style='width:100px;' value='".$locale['439']."' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
}else{
	$table_opt_list = "";
	$result = dbquery("SHOW tables");
	while ($row = dbarraynum($result)) {
		$table_opt_list .= "<option value='".$row[0]."'";
		if (preg_match("/^".DB_PREFIX."/i", $row[0])){
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
	echo "<form action='".FUSION_SELF.$aidlink."' name='backupform' method='post'>\n";
	echo "<table align='center' cellspacing='0' cellpadding='0'>\n<tr>\n";
	echo "<td valign='top'>\n";
	echo "<table align='center' cellspacing='0' cellpadding='0'>\n<tr>\n";
	echo "<td colspan='2' class='tbl2' align='left'>".$locale['451']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['414']."</td>\n";
	echo "<td class='tbl'>".$db_name."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['415']."</td>\n";
	echo "<td class='tbl'>".$db_prefix."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['452']."</td>\n";
	echo "<td class='tbl'>".parsebytesize(get_database_size(), 2, false)." (".get_table_count()." ".$locale['419'].")</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['453']."</td>\n";
	echo "<td class='tbl'>".parsebytesize(get_database_size($db_prefix), 2, false)." (".get_table_count($db_prefix)." ".$locale['419'].")</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='left' colspan='2' class='tbl2'>".$locale['454']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['431']." <span style='color:#ff0000'>*</span></td>\n";
	echo "<td class='tbl'><input type='text' name='backup_filename' value='backup_".stripsiteinput($settings['sitename'])."_".date('Y-m-d-Hi')."' class='textbox' style='width:200px;' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['455']."</td>\n";
	echo "<td class='tbl'><select name='backup_type' class='textbox' style='width:150px;'>\n";
	if (function_exists("gzencode")){
		echo "<option value='.gz' selected='selected'>.sql.gz ".$locale['456']."</option>\n";
	}
	echo "<option value='.sql'>.sql</option>\n";
	echo "</select></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='right' class='tbl'>".$locale['460']." <span style='color:#ff0000'>*</span></td>\n";
	echo "<td class='tbl'><input type='password' name='user_admin_password' value='' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><br /><span style='color:#ff0000'>*</span> ".$locale['461']."</td>\n";
	echo "</tr>\n</table>\n</td>\n";
	echo "<td valign='top'>\n";
	echo "<table border='0' cellpadding='0' cellspacing='0'>\n<tr>\n";
	echo "<td class='tbl2'>".$locale['457']."</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl'>\n";
	echo "<select name='db_tables[]' id='tablelist' size='17' class='textbox' style='margin:5px 0px' multiple='multiple'>".$table_opt_list."</select>\n";
	echo "<div style='text-align:center'>".$locale['435']." [<a href=\"javascript:void(0)\" onclick=\"javascript:backupSelectCore()\">".$locale['458']."</a>]\n";
	echo "[<a href=\"javascript:void(0)\" onclick=\"javascript:backupSelectAll()\">".$locale['436']."</a>]\n";
	echo "[<a href=\"javascript:void(0)\" onclick=\"javascript:backupSelectNone()\">".$locale['437']."</a>]</div>\n";
	echo "</td>\n</tr>\n</table>\n</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' colspan='2' class='tbl'><hr />\n";
	echo "<input type='submit' name='btn_create_backup' value='".$locale['459']."' class='button' style='width:100px;' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();

	opentable($locale['480']);
	$file_types = (function_exists("gzencode")) ? ".gz " : ""; // added
	echo "<form name='restore' method='post' action='".FUSION_SELF.$aidlink."&amp;action=restore' enctype='multipart/form-data'>\n";
	echo "<div style='text-align:center'>".$locale['431']." <input type='file' name='upload_backup_file' class='textbox' /><br />\n";// edited
	echo $locale['440']." ".$file_types.".sql<br /><br />\n"; // added
	echo "<input class='button' type='submit' name='restore' style='width:100px;' value='".$locale['438']."' />\n";
	echo "</div>\n</form>\n";
	closetable();
}

function get_database_size($prefix = ""){
	global $db_name;
	$db_size = 0;
	$result = dbquery("SHOW TABLE STATUS FROM `".$db_name."`");
	while ($row = dbarray($result)) {
		if (!isset($row['Type'])) { $row['Type'] = ""; }
		if (!isset($row['Engine'])) { $row['Engine'] = ""; }
		if ((preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Type'])) || (preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Engine'])) && (preg_match("/^".$prefix."/", $row['Name']))) {
			$db_size += $row['Data_length'] + $row['Index_length'];
		}
	}
	return $db_size;
}

function get_table_count($prefix = ""){
	global $db_name;
	$tbl_count = 0;
	$result = dbquery("SHOW TABLE STATUS FROM `".$db_name."`");
	while ($row = dbarray($result)) {
		if (!isset($row['Type'])) { $row['Type'] = ""; }
		if (!isset($row['Engine'])) { $row['Engine'] = ""; }
		if ((preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Type'])) || (preg_match('/^(MyISAM|ISAM|HEAP|InnoDB)$/i', $row['Engine'])) && (preg_match("/^".$prefix."/", $row['Name']))) {
			$tbl_count++;
		}
	}
	return $tbl_count;
}

function gzcompressfile($source, $level = false) {
	$dest = $source.".gz";
	$mode = "wb".$level;
	$error = false;
	if ($fp_out = gzopen($dest, $mode)) {
		if ($fp_in = fopen($source, "rb")) {
			while (!feof($fp_in)) {
				gzputs($fp_out, fread($fp_in, 1024 * 512));
			}
			fclose($fp_in);
		} else {
			$error = true;
		}
		gzclose($fp_out);
	} else {
		$error = true;
	}
	if ($error) {
		return false;
	} else {
		return $dest;
	}
}

require_once THEMES."templates/footer.php";
?>