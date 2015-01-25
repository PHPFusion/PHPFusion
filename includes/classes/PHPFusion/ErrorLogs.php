<?php

namespace PHPFusion;

class ErrorLogs {

	private $error_status = '';
	private $posted_error_id = '';
	private $delete_status = '';
	private $rows = 0;
	private $rowstart = '';
	private $error_id = '';
	private $errors = array();
	private $locale = array();

	public function __construct() {
		global $locale;
		add_to_head("<link rel='stylesheet' href='".THEMES."templates/errors.css' type='text/css' media='all' />");
		include LOCALE.LOCALESET."admin/errors.php";
		$this->locale += $locale;
		$this->error_status = filter_input(INPUT_POST, 'error_status', FILTER_VALIDATE_INT, array('min_range' => 0, 'max_range' => 2));
		$this->posted_error_id = filter_input(INPUT_POST, 'error_id', FILTER_VALIDATE_INT);
		$this->delete_status = filter_input(INPUT_POST, 'delete_status', FILTER_VALIDATE_INT, array('min_range' => 0, 'max_range' => 2));
		$this->rowstart = filter_input(INPUT_GET, 'rowstart', FILTER_VALIDATE_INT) ? : 0;
		$this->error_id = filter_input(INPUT_GET, 'error_id', FILTER_VALIDATE_INT);

		if (isnum($this->error_status) && $this->posted_error_id) {
			dbquery("UPDATE ".DB_ERRORS." SET error_status='".$this->error_status."' WHERE error_id='".$this->posted_error_id."'");
		}
		if (isset($_POST['delete_entries']) && isnum($this->delete_status)) {
			dbquery("DELETE FROM ".DB_ERRORS." WHERE error_status='".$_POST['delete_status']."'");
		}

		$result = dbquery("SELECT * FROM ".DB_ERRORS." ORDER BY error_timestamp DESC LIMIT ".$this->rowstart.",20");
		while ($data = dbarray($result)) {
			$this->errors[] = $data;
		}

		$this->rows = $this->errors ? dbcount('(error_id)', DB_ERRORS) : 0;
		static::errorjs();
	}

	// Setting maximum number of folders for an URL
	static function getMaxFolders($url, $level = 2) {
		$return = "";
		$tmpUrlArr = explode("/", $url);
		if (count($tmpUrlArr) > $level) {
			$tmpUrlArr = array_reverse($tmpUrlArr);
			for ($i = 0; $i < $level; $i++) {
				$return = $tmpUrlArr[$i].($i > 0 ? "/".$return : "");
			}
		} else {
			$return = implode("/", $tmpUrlArr);
		}
		return $return;
	}

	// wrap codes
	static function codeWrap($code, $maxLength = 150) {
		$lines = explode("\n", $code);
		$count = count($lines);
		for ($i = 0; $i < $count; ++$i) {
			preg_match('`^\s*`', $code, $matches);
			$lines[$i] = wordwrap($lines[$i], $maxLength, "\n$matches[0]\t", TRUE);
		}
		return implode("\n", $lines);
	}

	// Print code
	static function printCode($source_code, $starting_line, $error_line = "") {
		if (is_array($source_code)) {
			return FALSE;
		}
		$source_code = explode("\n", str_replace(array("\r\n", "\r"), "\n", $source_code));
		$line_count = $starting_line;
		$formatted_code = "";
		foreach ($source_code as $code_line) {
			$code_line = self::codeWrap($code_line, 145);
			$line_class = ($line_count == $error_line ? "err_tbl-error-line" : "err_tbl1");
			$formatted_code .= "<tr>\n<td class='err_tbl2' style='text-align:right;width:1%;'>".$line_count."</td>\n";
			$line_count++;
			if (preg_match('#<\?(php)?[^[:graph:]]#', $code_line)) {
				$formatted_code .= "<td class='".$line_class."'>".str_replace(array('<code>', '</code>'), '', highlight_string($code_line, TRUE))."</td>\n</tr>\n";
			} else {
				$formatted_code .= "<td class='".$line_class."'>".preg_replace('#(&lt;\?php&nbsp;)+#', '', str_replace(array('<code>', '</code>'), '', highlight_string('<?php '.$code_line, TRUE)))."</td>\n</tr>\n";
			}
		}
		return "<table class='err_tbl-border center' cellspacing='0' cellpadding='0'>".$formatted_code."</table>";
	}

	static function errorjs() {
		// Show the "Apply"-button only when javascript is disabled"
		add_to_jquery("
		$('.change_status').hide();
		$('a[href=#top]').click(function(){
			jQuery('html, body').animate({scrollTop:0}, 'slow');
			return false;
		});
		");
	}

	public function add_breadcrumb() {
		global $aidlink;
		add_to_breadcrumbs(array('link'=>ADMIN."errors.php".$aidlink, 'title'=>$this->locale['400']));;
	}

	// @todo: need some love on the html.
	public function show_error_logs() {
		global $aidlink;
		$locale = $this->locale;
		opentable($locale['400']);
		if ($this->errors) : ?>
			<a name='top'></a>
			<table cellpadding='0' cellspacing='1' class='tbl-border center' style='width:90%;'>
				<tr>
					<td class='tbl1' colspan='3' style='text-align:center;'>
						<form name='delete_form' action='<?php echo FUSION_SELF.$aidlink ?>' method='post'>
							<?php echo $locale['440'] ?>
							<select name='delete_status' class='textbox'>
								<option>---</option>
								<option value='0'><?php echo $locale['450'] ?></option>
								<option value='1'><?php echo $locale['451'] ?></option>
								<option value='2'><?php echo $locale['452'] ?></option>
							</select>
							<input type='submit' class='button' name='delete_entries' value='<?php echo $locale['453'] ?>' style='margin-left:5px;' />
						</form>
					</td>
				</tr>
				<tr>
					<td class='tbl2' style='font-weight:bold;'><?php echo $locale['410'] ?></td>
					<td class='tbl2' style='font-weight:bold;width:5%;'><?php echo $locale['413'] ?></td>
					<td class='tbl2' style='text-align:center;width:5%;font-weight:bold;'><?php echo $locale['414'] ?></td>
				</tr>
				<?php foreach ($this->errors as $i => $data) {
					$row_color = ($i%2 == 0 ? "tbl1" : "tbl2"); ?>
					<tr>
						<td class='<?php echo $row_color ?>'>
							<a href='<?php echo FUSION_SELF.$aidlink."&amp;rowstart=".$this->rowstart."&amp;error_id=".$data['error_id'] ?>#file' title='<?php echo stripslashes($data['error_file']) ?>'>
								<?php echo self::getMaxFolders(stripslashes($data['error_file']), 2) ?></a><br />
							<span class='small2'><?php echo $data['error_message']." ".$locale['415']." ".$data['error_line'] ?></span>
						</td>
						<td class='<?php echo $row_color ?>' style='white-space:nowrap;'><?php echo showdate("longdate", $data['error_timestamp']) ?></td>
						<td class='<?php echo $row_color ?>' style='white-space:nowrap;'>
							<form action='<?php echo FUSION_SELF.$aidlink."&amp;rowstart=".$this->rowstart ?>' method='post'>
								<input type='hidden' name='error_id' value='<?php echo $data['error_id'] ?>' />
								<select name='error_status' class='textbox' onchange='this.form.submit();'>
									<option value='0' <?php echo ($data['error_status'] == 0 ? "selected='selected'" : "") ?>><?php echo $locale['450'] ?></option>
									<option value='1' <?php echo ($data['error_status'] == 1 ? "selected='selected'" : "") ?>><?php echo $locale['451'] ?></option>
									<option value='2' <?php echo ($data['error_status'] == 2 ? "selected='selected'" : "") ?>><?php echo $locale['452'] ?></option>
								</select>
								<input type='submit' class='button change_status' value='<?php echo $locale['453'] ?>' style='margin-left:5px;' />
							</form>
						</td>
					</tr>
				<?php
				} ?>
			</table>
		<?php else : ?>
			<div style='text-align:center'><br />
				<?php echo $locale['418'] ?><br /><br />
			</div>
		<?php endif;
		if ($this->rows > 20) : ?>
			<div style='margin-top:5px;text-align:center;'><?php echo makepagenav($this->rowstart, 20, $this->rows, 3, FUSION_SELF.$aidlink."&amp;") ?></div>
		<?php endif;
		closetable();
	}

	// @todo: need some love on the html.
	public function show_error_notice() {
		global $aidlink;
		$locale = $this->locale;
		if ($this->error_id) {
			$data = dbarray(dbquery("SELECT * FROM ".DB_ERRORS." WHERE error_id='".$this->error_id."' LIMIT 1"));
			if (!$data) {
				redirect(FUSION_SELF.$aidlink);
			}
			$thisFileContent = is_file($data['error_file']) ? file($data['error_file']) : array();
			$line_start = max($data['error_line']-10, 1);
			$line_end = min($data['error_line']+10, count($thisFileContent));
			opentable($locale['401']." ".self::getMaxFolders(stripslashes($data['error_file']), 3));
			$output = implode("", array_slice($thisFileContent, $line_start-1, $line_end - $line_start + 1));
			$pageFilePath = BASEDIR.substr($data['error_page'], strlen(fusion_get_settings('site_path')));
			$pageContent = is_file($pageFilePath) ? file_get_contents($pageFilePath) : '';
			?>
			<table cellpadding='0' cellspacing='1' class='tbl-border center' style='border-collapse:collapse;width:90%;'>
				<tr>
					<td colspan='4' class='tbl2'><a name='file'></a><strong><?php echo $locale['420'] ?></strong></td>
				</tr>
				<tr>
					<td class='tbl2 err_tbl-error' style='width:5%;white-space:nowrap;'><?php echo $locale['410'] ?></td>
					<td class='tbl1 err_tbl-error'><?php echo $data['error_message'] ?></td>
					<td class='tbl2 err_tbl-error' style='width:5%;white-space:nowrap;'><?php echo $locale['415'] ?></td>
					<td class='tbl1 err_tbl-error'><?php echo $data['error_line'] ?></td>
				</tr>
				<tr>
					<td class='tbl2' style='width:5%;white-space:nowrap;'><?php echo $locale['419'] ?></td>
					<td class='tbl1'><strong><?php echo self::getMaxFolders(stripslashes($data['error_file']), 3) ?></strong></td>
					<td class='tbl2' style='width:5%;white-space:nowrap;'><?php echo $locale['411'] ?>:</td>
					<td class='tbl1'>
						<a href='<?php echo FUSION_SELF.$aidlink."&amp;rowstart=".$rowstart."&amp;error_id=".$data['error_id'] ?>#page' title='<?php echo $data['error_page'] ?>'>
							<?php echo self::getMaxFolders($data['error_page'], 3) ?></a>
					</td>
				</tr>
				<tr>
					<td class='tbl2' style='width:5%;white-space:nowrap;'><?php echo $locale['412']."-".$locale['416'] ?></td>
					<td class='tbl1'><?php echo $data['error_user_level'] ?></td>
					<td class='tbl2' style='width:5%;white-space:nowrap;'><?php echo $locale['417'] ?></td>
					<td class='tbl1'><?php echo $data['error_user_ip'] ?></td>
				</tr>
				<tr>
					<td class='tbl2' style='width:5%;white-space:nowrap;'><?php echo $locale['413'] ?>:</td>
					<td class='tbl1'><?php echo showdate("longdate", $data['error_timestamp']) ?></td>
					<td class='tbl2' style='width:5%;white-space:nowrap;'><?php echo $locale['414'] ?>:</td>
					<td class='tbl1'>
						<form action='<?php echo FUSION_SELF.$aidlink."&amp;rowstart=".$rowstart."&amp;error_id=".$data['error_id'] ?>#file' method='post'>
							<input type='hidden' name='error_id' value='<?php echo $data['error_id'] ?>' />
							<select name='error_status' class='textbox' onchange='this.form.submit();'>
								<option value='0' <?php echo ($data['error_status'] == 0 ? "selected='selected'" : "") ?>><?php echo $locale['450'] ?></option>
								<option value='1' <?php echo ($data['error_status'] == 1 ? "selected='selected'" : "") ?>><?php echo $locale['451'] ?></option>
								<option value='2' <?php echo ($data['error_status'] == 2 ? "selected='selected'" : "") ?>><?php echo $locale['452'] ?></option>
							</select>
							<input type='submit' class='button change_status' value='<?php echo $locale['453'] ?>' style='margin-left:5px;' />
						</form>
					</td>
				</tr>
				<tr>
					<td colspan='4' class='tbl1' style='text-align:center;font-weight:bold;'>
						<hr />
						<a href='#top' title='<?php echo $locale['422'] ?>'><?php echo $locale['422'] ?></a>
					</td>
				</tr>
				<tr>
					<td colspan='4' class='tbl2'><strong><?php echo $locale['421'] ?></strong> (<?php echo $locale['415']." ".$line_start." - ".$line_end ?>)</td>
				</tr>
				<tr>
					<td colspan='4'><div style='max-height:600px;overflow:auto;'><?php echo self::printCode($output, $line_start, $data['error_line']) ?></div>
					</td>
				</tr>
				<tr>
					<td colspan='4' class='tbl1' style='text-align:center;font-weight:bold;'>
						<hr />
						<a href='#top' title='<?php echo $locale['422'] ?>'><?php echo $locale['422'] ?></a>
					</td>
				</tr>
				<tr>
					<td colspan='4' class='tbl2'><a name='page'></a>
						<strong><?php echo $locale['411'] ?>: <?php echo self::getMaxFolders($data['error_page'], 2) ?></strong></td>
				</tr>
				<tr>
					<td colspan='4'><div style='max-height:500px;overflow:auto;'><?php echo self::printCode($pageContent, "1") ?></div>
					</td>
				</tr>
			</table>
			<div style='margin-top:5px;text-align:center;font-weight:bold;'>
				<a href='#top' title='<?php echo $locale['422'] ?>'><?php echo $locale['422'] ?></a>
			</div>
			<?php
			closetable();
		}
	}
}


?>