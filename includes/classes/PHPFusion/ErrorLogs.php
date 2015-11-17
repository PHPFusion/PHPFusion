<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ErrorLogs.php
| Author: Hans Kristian Flaatten (Starefossen)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;
if (!defined("IN_FUSION")) { die("Access Denied"); }

class ErrorLogs {
	public $no_notice = 0;
	public $compressed = 0;
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
		include LOCALE.LOCALESET."admin/errors.php";
		$this->locale += $locale;
		$this->error_status = filter_input(INPUT_POST, 'error_status', FILTER_VALIDATE_INT, array('min_range' => 0,
			'max_range' => 2));
		$this->posted_error_id = filter_input(INPUT_POST, 'error_id', FILTER_VALIDATE_INT);
		$this->delete_status = filter_input(INPUT_POST, 'delete_status', FILTER_VALIDATE_INT, array('min_range' => 0,
			'max_range' => 2));
		$this->rowstart = filter_input(INPUT_GET, 'rowstart', FILTER_VALIDATE_INT) ? : 0;
		$this->error_id = filter_input(INPUT_GET, 'error_id', FILTER_VALIDATE_INT);
		if (isnum($this->error_status) && $this->posted_error_id) {
			dbquery("UPDATE ".DB_ERRORS." SET error_status='".$this->error_status."' WHERE error_id='".$this->posted_error_id."'");
			redirect(FUSION_REQUEST);
		}
		if (isset($_POST['delete_entries']) && isnum($this->delete_status)) {
			dbquery("DELETE FROM ".DB_ERRORS." WHERE error_status='".$_POST['delete_status']."'");
			redirect(FUSION_REQUEST);
		}
		if (isset($_POST['delete_all_logs'])) {
			dbquery("DELETE FROM ".DB_ERRORS);
			redirect(FUSION_REQUEST);
		}
		$result = dbquery("SELECT * FROM ".DB_ERRORS." ORDER BY error_timestamp DESC LIMIT ".$this->rowstart.",20");
		while ($data = dbarray($result)) {
			$this->errors[] = $data;
		}
		$this->rows = $this->errors ? dbcount('(error_id)', DB_ERRORS) : 0;
		static::errorjs();
	}

	// Setting maximum number of folders for an URL

	static function errorjs() {
		global $aidlink;
		if (checkrights("ERRO") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] == iAUTH) {
		// Show the "Apply"-button only when javascript is disabled"
		add_to_jquery("
		$('.change_status').hide();
		$('a[href=#top]').click(function(){
			jQuery('html, body').animate({scrollTop:0}, 'slow');
			return false;
		});
		$('.move_error_log').bind('click', function() {
			var form = $('#error_logform');
			var data = {
				'aidlink' : '".$aidlink."',
				'error_id' : $(this).data('id'),
				'error_type' : $(this).data('type')
			};
			var sendData = form.serialize() + '&' + $.param(data);
			$.ajax({
				url: '".ADMIN."includes/error_logs_updater.php',
				dataType: 'json',
				method : 'post',
				type: 'json',
				data: sendData,
				success: function(e) {
					//console.log(e);
					if (e.status == 'OK') {
						var target_group_add  = $('tr#rmd-'+e.fusion_error_id+' > td > a.e_status_'+ e.to);
						var target_group_remove = $('tr#rmd-'+e.fusion_error_id+' > td > a.e_status_'+ e.from)
						target_group_add.addClass('active');
						target_group_remove.removeClass('active');
					}
					else if (e.status == 'RMD') {
						 $('tr#rmd-'+e.fusion_error_id).html('');
					}
				},
				error : function(e) {
					console.log('fail');
				}
			});
		});
		");
		}
	}

	// wrap codes

	public function show_footer_logs() {
		global $aidlink;
		$locale = $this->locale;
		if ($this->errors) {
			echo openform('error_logform', 'post', FUSION_REQUEST, array('class' => 'text-right'));
			echo form_button('delete_all_logs', $locale['delete'], $locale['453'], array('class' => 'btn-block btn-primary',
				'icon' => 'fa fa-bitbucket fa-lg m-r-10'));
			echo closeform();
			?>
			<table class='table table-responsive'>
				<?php foreach ($this->errors as $i => $data) {
					$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
					?>
					<tr <?php echo "id='rmd-".$data['error_id']."'" ?>>
						<td class='col-xs-6 <?php echo $row_color ?>'>
							<a href='<?php echo ADMIN."errors.php".$aidlink."&amp;error_id=".$data['error_id'] ?>#file'
							   title='<?php echo stripslashes($data['error_file']) ?>'>
								<?php echo self::getMaxFolders(stripslashes($data['error_file']), 2) ?></a><br/>
							<span><?php echo $data['error_message'] ?></span><br/>
							<span class='strong'><?php echo $locale['415']." ".$data['error_line'] ?></span><br/>
							<span class='text-smaller'><?php echo timer($data['error_timestamp']) ?></span><br/>
						</td>
						<td class='<?php echo $row_color ?>'>
							<div class='btn-group'>
								<?php echo self::getGitsrc($data['error_file'], $data['error_line']); ?>
							</div>
						</td>
						<td class='<?php echo $row_color ?>'><?php echo self::get_errorTypes($data['error_level']); ?></td>
						<td class='<?php echo $row_color ?>' style='white-space:nowrap;'>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='0'
																				class='btn <?php echo $data['error_status'] == 0 ? 'active' : ''; ?> e_status_0 button btn-default move_error_log'><?php echo $locale['450'] ?></a>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='1'
																				class='btn <?php echo $data['error_status'] == 1 ? 'active' : ''; ?> e_status_1 button btn-default move_error_log'><?php echo $locale['451'] ?></a>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='2'
																				class='btn <?php echo $data['error_status'] == 2 ? 'active' : ''; ?> e_status_2 button btn-default move_error_log'><?php echo $locale['452'] ?></a>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='999'
																				class='btn e_status_999 button btn-default move_error_log'><?php echo $locale['delete'] ?></a>
						</td>
					</tr>
				<?php } ?>
			</table>
		<?php } else { ?>
			<div style='text-align:center'><br/>
				<?php echo $locale['418'] ?><br/><br/>
			</div>
		<?php
		}
	}

	// Print code

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

	/**
	 * Returns the HTML link pointing to the line of a file's latest version on github
	 * @param string $file The absolute path from the server's root.
	 * @param int    $line_number
	 * @return string
	 */
	public static function getGitsrc($file, $line_number) {
		$repository_address = "https://github.com/php-fusion/PHP-Fusion/blob/";
		$version = "9.00";
		// Strip slashes and convert backslashes to forward slashes for browsers
		$file_path = substr(str_replace('\\', '/', stripslashes($file)), strlen(FUSION_ROOT_DIR));
		return "<a class='btn btn-default' href='".$repository_address.$version."/".$file_path."#L".$line_number."' target='new_window'><i class='fa fa-git'></i></a>";
	}

	static function get_errorTypes($type) {
		$locale = '';
		include LOCALE.LOCALESET."errors.php";
		$error_types = array(1 => array("E_ERROR", $locale['E_ERROR']),
			2 => array("E_WARNING", $locale['E_WARNING']),
			4 => array("E_PARSE", $locale['E_PARSE']),
			8 => array("E_NOTICE", $locale['E_NOTICE']),
			16 => array("E_CORE_ERROR", $locale['E_CORE_ERROR']),
			32 => array("E_CORE_WARNING", $locale['E_CORE_WARNING']),
			64 => array("E_COMPILE_ERROR", $locale['E_COMPILE_ERROR']),
			128 => array("E_COMPILE_WARNING", $locale['E_COMPILE_WARNING']),
			256 => array("E_USER_ERROR", $locale['E_USER_ERROR']),
			512 => array("E_USER_WARNING", $locale['E_USER_WARNING']),
			1024 => array("E_USER_NOTICE", $locale['E_USER_NOTICE']),
			2047 => array("E_ALL", $locale['E_ALL']),
			2048 => array("E_STRICT", $locale['E_STRICT']));
		if (isset($error_types[$type])) return $error_types[$type][1];
		return FALSE;
	}

	public function show_error_log() {
		global $aidlink;
		$locale = $this->locale;
		$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;
		$tab_title['title'][0] = 'Errors';
		$tab_title['id'][0] = 'errors-list';
		$tab_title['icon'][0] = 'fa fa-bug m-r-10';
		if ($this->error_id) {
			$tab_title['title'][1] = 'Error File';
			$tab_title['id'][1] = 'error-file';
			$tab_title['icon'][1] = 'fa fa-medkit m-r-10';
			$tab_title['title'][2] = 'Source File';
			$tab_title['id'][2] = 'src-file';
			$tab_title['icon'][2] = 'fa fa-stethoscope m-r-10';
		}
		$tab_active = tab_active($tab_title, $this->error_id ? 1 : 0);
		echo opentab($tab_title, $tab_active, 'error_tab');
		echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
		?>
		<div class='m-t-20'>
			<?php self::show_error_logs(); ?>
		</div>
		<?php echo closetabbody();
		if ($this->error_id) {
			// dump 1 and 2
			add_to_head("<link rel='stylesheet' href='".THEMES."templates/errors.css' type='text/css' media='all' />");
			define('no_debugger', 1);
			$data = dbarray(dbquery("SELECT * FROM ".DB_ERRORS." WHERE error_id='".$this->error_id."' LIMIT 1"));
			if (!$data) redirect(FUSION_SELF.$aidlink);
			$thisFileContent = is_file($data['error_file']) ? file($data['error_file']) : array();
			$line_start = max($data['error_line']-10, 1);
			$line_end = min($data['error_line']+10, count($thisFileContent));
			$output = implode("", array_slice($thisFileContent, $line_start-1, $line_end-$line_start+1));
			$pageFilePath = BASEDIR.$data['error_page'];
			$pageContent = is_file($pageFilePath) ? file_get_contents($pageFilePath) : '';
			//echo "<a class='btn btn-default m-b-20 pull-right' href='#top' title='".$locale['422']."'>".$locale['422']."</a>\n";
			add_to_jquery("
			$('#error_status_sel').bind('change', function(e) { this.form.submit();	});
			");
			echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active); ?>

			<div class='m-t-20'>
				<h2><?php echo $data['error_message'] ?></h2>

				<h3 style='border-bottom:0;' class='display-inline'><label
						class='label label-success'><?php echo $locale['415']." ".number_format($data['error_line']); ?></label>
				</h3>

				<div class='display-inline text-lighter'><strong><?php echo $locale['419'] ?></strong>
					-- <?php echo self::getMaxFolders(stripslashes($data['error_file']), 3); ?></div>

				<div class='m-t-10'>
					<div class='display-inline-block m-r-20'><i class='fa fa-file-code-o m-r-10'></i><strong
							class='m-r-10'><?php echo $locale['411'] ?></strong> -- <a
							href='<?php echo FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;error_id=".$data['error_id'] ?>#page'
							title='<?php echo $data['error_page'] ?>'>
							<?php echo self::getMaxFolders($data['error_page'], 3); ?></a>
					</div>
					<span class='text-lighter'>generated by</span>

					<div class='alert alert-info display-inline-block p-t-0 p-b-0 text-smaller'>
						<strong><?php echo $locale['412']."-".$locale['416'] ?>
							<?php echo $data['error_user_level']; ?>
							-- <?php echo $locale['417']." ".$data['error_user_ip'] ?></strong>
					</div>
					<span class='text-lighter'><?php echo lcfirst($locale['on']) ?></span>

					<div class='alert alert-info display-inline-block p-t-0 p-b-0 text-smaller'><strong
							class='m-r-10'><?php echo showdate("longdate", $data['error_timestamp']) ?></strong></div>
				</div>
				<div class='m-t-10 display-inline-block' style='width:300px'>
					<?php
					echo openform('logform', 'post', FUSION_SELF.$aidlink."&amp;rowstart=".$_GET['rowstart']."&amp;error_id=".$data['error_id']."#file", array('max_tokens' => 1));
					echo form_hidden('error_id', '', $data['error_id']);
					echo form_select('error_status', $locale['mark_as'], $data['error_status'], array("inline" => TRUE,
						"options" => self::get_logTypes()));
					echo closeform();
					?>
				</div>
			</div>
			<div class='m-t-10'>
				<?php openside('') ?>
				<table class='table table-responsive'>
					<tr>
						<td colspan='4' class='tbl2'><strong><?php echo $locale['421'] ?></strong>
							(<?php echo $locale['415']." ".$line_start." - ".$line_end ?>)
						</td>
					</tr>
					<tr>
						<td colspan='4'><?php echo self::printCode($output, $line_start, $data['error_line'], array('time' => $data['error_timestamp'],
								'text' => $data['error_message'])) ?></td>
					</tr>
				</table>
				<?php closeside() ?>
			</div>
			<?php
			echo closetabbody();
			echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
			?>
			<div class='m-t-10'>
				<?php openside('') ?>
				<table class='table table-responsive'>
					<tr>
						<td class='tbl2'><a name='page'></a>
							<strong><?php echo $locale['411'] ?>
								: <?php echo self::getMaxFolders($data['error_page'], 2) ?></strong>
						</td>
					</tr>
					<tr>
						<td><?php echo self::printCode($pageContent, "1") ?></td>
					</tr>
				</table>
				<?php closeside() ?>
			</div>
			<?php
			echo closetabbody();
			echo closetab();
		}
	}

	public function show_error_logs() {
		global $aidlink;
		$locale = $this->locale;
		?>
		<div class='row'>
			<div class='col-xs-12 col-sm-12 col-md-9'>
				<?php if ($this->errors) : ?>
				<a name='top'></a>
				<table class='table table-responsive center'>
					<tr>
						<th class='col-xs-5'><?php echo $locale['410'] ?></th>
						<th>Options</th>
						<th><?php echo $locale['454'] ?></th>
						<th class='col-xs-4'><?php echo $locale['414'] ?></th>
					</tr>
					<?php foreach ($this->errors as $i => $data) {
					$row_color = ($i%2 == 0 ? "tbl1" : "tbl2"); ?>
					<tr <?php echo "id='rmd-".$data['error_id']."'" ?>>
                        <td class='<?php echo $row_color ?>'>
							<a href='<?php echo FUSION_SELF.$aidlink."&amp;rowstart=".$this->rowstart."&amp;error_id=".$data['error_id'] ?>#file'
							   title='<?php echo stripslashes($data['error_file']) ?>'>
								<?php echo self::getMaxFolders(stripslashes($data['error_file']), 2) ?></a><br/>
							<small><?php echo $data['error_message'] ?></small>
							<br/>
							<span class='strong'><?php echo $locale['415']." ".$data['error_line'] ?></span><br/>
							<small><?php echo timer($data['error_timestamp']) ?></small>
						</td>
						<td class='<?php echo $row_color ?>'>
							<div class='btn-group'>
								<?php echo self::getGitsrc($data['error_file'], $data['error_line']); ?>
							</div>
						</td>
						<td class='<?php echo $row_color ?>'><?php echo self::get_errorTypes($data['error_level']); ?></td>
						<td id='ecmd_<?php echo $data['error_id'] ?>' class='<?php echo $row_color ?>' style='white-space:nowrap;'>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='0'
																				class='btn <?php echo $data['error_status'] == 0 ? 'active' : ''; ?> e_status_0 button btn-default move_error_log'><?php echo $locale['450'] ?></a>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='1'
																				class='btn <?php echo $data['error_status'] == 1 ? 'active' : ''; ?> e_status_1 button btn-default move_error_log'><?php echo $locale['451'] ?></a>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='2'
																				class='btn <?php echo $data['error_status'] == 2 ? 'active' : ''; ?> e_status_2 button btn-default move_error_log'><?php echo $locale['452'] ?></a>
							<a <?php echo "data-id='".$data['error_id']."'"; ?> data-type='999'
																				class='btn e_status_999 button btn-default move_error_log'><?php echo $locale['delete'] ?></a>
						</td>
					</tr>
					<?php } ?>
				</table>
				<?php else : ?>
					<div style='text-align:center'><br/>
						<?php echo $locale['418'] ?><br/><br/>
					</div>
				<?php
				endif;
				if ($this->rows > 20) : ?>
				<div
					style='margin-top:5px;text-align:center;'><?php echo makepagenav($this->rowstart, 20, $this->rows, 3, FUSION_SELF.$aidlink."&amp;") ?></div>
			<?php endif; ?>
			</div>
			<div class='col-xs-12 col-sm-12 col-md-3'>
			<?php
			echo openform('error_logform', 'post', FUSION_REQUEST, array('max_tokens' => 1));
			openside('');
			echo form_select('delete_status', $locale['440'], '', array('allowclear' => TRUE,
				'options' => self::get_logTypes(),
				'width' => '100%'));
			echo form_button('delete_entries', $locale['453'], $locale['453'], array('class' => 'btn-primary'));
			closeside();
			echo closeform();
			?>
			</div>
		</div>
	<?php
	}

	// @todo: need some love on the html.

	static function get_logTypes() {
		global $locale;
		return array('0' => $locale['450'],
			'1' => $locale['451'],
			'2' => $locale['452']);
	}

	static function printCode($source_code, $starting_line, $error_line = "", array $error_message = array()) {
		global $locale;
		if (is_array($source_code)) {
			return FALSE;
		}
		$error_message = array('time' => !empty($error_message['time']) ? $error_message['time'] : time(),
			'text' => !empty($error_message['text']) ? $error_message['text'] : $locale['na'],);
		$source_code = explode("\n", str_replace(array("\r\n", "\r"), "\n", $source_code));
		$line_count = $starting_line;
		$formatted_code = "";
		$error_message = "<div class='panel panel-default m-10'><div class='panel-heading'><i class='fa fa-bug'></i> Line ".$error_line." -- ".timer($error_message['time'])."</div><div class='panel-body strong required'>".$error_message['text']."</div>\n";
		foreach ($source_code as $code_line) {
			$code_line = self::codeWrap($code_line, 145);
			$line_class = ($line_count == $error_line ? "err_tbl-error-line" : "err_tbl1");
			$formatted_code .= "<tr>\n<td class='err_tbl2' style='text-align:right;width:1%;'>".$line_count."</td>\n";
			if (preg_match('#<\?(php)?[^[:graph:]]#', $code_line)) {
				$formatted_code .= "<td class='".$line_class."'>".str_replace(array('<code>',
																				  '</code>'), '', highlight_string($code_line, TRUE))."</td>\n</tr>\n";
			} else {
				$formatted_code .= "<td class='".$line_class."'>".preg_replace('#(&lt;\?php&nbsp;)+#', '', str_replace(array('<code>',
																														   '</code>'), '', highlight_string('<?php '.$code_line, TRUE)))."
				</td>\n</tr>\n";
				if ($line_count == $error_line) {
					$formatted_code .= "<tr>\n<td colspan='2'>".$error_message."</td></tr>\n";
				}
			}
			$line_count++;
		}
		return "<table class='err_tbl-border center' cellspacing='0' cellpadding='0'>".$formatted_code."</table>";
	}

	// @todo: need some love on the html.

	static function codeWrap($code, $maxLength = 150) {
		$lines = explode("\n", $code);
		$count = count($lines);
		for ($i = 0; $i < $count; ++$i) {
			preg_match('`^\s*`', $code, $matches);
			$lines[$i] = wordwrap($lines[$i], $maxLength, "\n$matches[0]\t", TRUE);
		}
		return implode("\n", $lines);
	}
}
