<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: messages.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
if (!function_exists('render_inbox')) {
	function render_mailbox($info) {
		global $locale;
		opentable($locale['400']);
		?>
		<!---start_inbox_idx--->
		<div class="row m-t-20">
			<div class="hidden-xs hidden-sm col-md-3 col-lg-2">
				<a class='btn btn-sm btn-primary btn-block text-white'
				   href="<?php echo $info['button']['new']['link'] ?>">
					<?php echo $info['button']['new']['name'] ?>
				</a>
				<?php
				$i = 0;
				echo "<ul class='m-t-20'>\n";
				foreach ($info['folders'] as $key => $folderData) {
					echo "<li><a href='".$folderData['link']."' class='text-dark ".($_GET['folder'] == $key ? "strong" : '')."'>".$folderData['title'];
					if ($i < count($info['folders'])-1) {
						$total_key = $key."_total";
						echo "(".$info[$total_key].")";
					}
					echo "</a></li>\n";
					$i++;
				}
				echo "</ul>\n";
				?>
			</div>
			<div class="col-xs-12 col-md-9 col-lg-10">
				<!-- start inbox actions -->
				<?php if (!isset($_GET['msg_send'])) : ?>
					<div class="inbox_header m-b-20">
						<?php if (isset($_GET['msg_read'])) : ?>
							<a href="<?php echo $info['button']['back']['link'] ?>" class="btn btn-default">
								<i title="<?php echo $info['button']['back']['title'] ?>"
								   class="fa fa-long-arrow-left"></i>
							</a>
						<?php endif; ?>
						<?php echo $info['actions_form']; ?>
					</div>
				<?php endif; ?>
				<!-- end inbox actions -->
				<!-- start inbox body -->
				<?php
				switch ($_GET['folder']) {
					case "options": // display options form
						echo '<div class="list-group-item">'.$info['options_form'].'</div>';
						break;
					case "inbox":
						_inbox($info);
						break;
					default :
						_inbox($info);
				}
				?>
				<!-- end inbox body -->
			</div>
		</div>
		<!--end_inbox_idx--->
		<?php
		/*
		echo "<div class='overflow-hide'>\n";
		echo "<span class='channel_title'>\n";
		if (isset($_GET['msg_send']) && isnum($_GET['msg_send'])) {
			echo $locale['420']; // send private message
		} elseif (isset($_GET['msg_user'])) {
			echo $locale['444'].' '.$info['channel']; // all conversation with user name
		} else {
		}
		echo "</span>\n";
		echo "</div>";

		// action buttons
		if ($info['chat_rows'] && isset($_GET['msg_user']) or isset($_GET['msg_read'])) {
			echo "
<div class='msg_buttons_bar clearfix p-10'>\n";
			if (isset($_GET['msg_user']) && $_GET['folder'] == 'inbox' && !isset($_GET['msg_read'])) {
				echo "
	<div class='btn-group pull-right'>\n";
				if ($_GET['folder'] == "inbox") echo form_button('save_msg', $locale['412'], $locale['412'], array(
					'class' => 'btn btn-sm btn-default'
				));
				echo form_button('read_msg', $locale['414'], $locale['414'], array('class' => 'btn-sm btn-default'));
				echo form_button('unread_msg', $locale['415'], $locale['415'], array('class' => 'btn-sm btn-default'));
				echo form_button('delete_msg', $locale['416'], $locale['416'], array('class' => 'btn-sm btn-default'));
				echo "
	</div>
	\n";
				echo "
	<div class='btn-group'>\n";
				echo form_button('setcheck_all', $locale['410'], $locale['410'], array(
					'class' => 'btn-sm btn-default',
					'type' => 'button'
				));
				echo form_button('setcheck_none', $locale['411'], $locale['410'], array(
					'class' => 'btn-sm btn-default',
					'type' => 'button'
				));
				echo "
	</div>
	\n";
			}
		}
		echo "<div class='well text-center text-dark m-t-20'>".$locale['467']."</div>\n";
		if ($info['chat_rows'] > 20) echo "
		<div align='center' class='m-t-5'>\n".makepagenav($_GET['rowstart'], 20, $info['chat_rows'], 3, FUSION_SELF."?folder=".$_GET['folder']."&amp;")."\n
		</div>
		\n";
		*/
	}
}

function _inbox($info) {
	if (isset($_GET['msg_read']) && isset($info['items'][$_GET['msg_read']])) : // read view
		$data = $info['items'][$_GET['msg_read']];
		// do we want to customize this in Arise, Atom, Patriot, Ddraig, etc?
		echo '
		<h4>'.$data['message']['message_header'].'</h4>
			<div class="clearfix m-b-20">
				<div class="pull-left">'.display_avatar($data, "40px").'</div>
				<div class="overflow-hide">
					'.profile_link($data['user_id'], $data['user_name'], $data['user_status']).'<br/>
					'.showdate("shortdate", $data['message_datestamp']).timer($data['message_datestamp']).'
				</div>
			</div>
			'.$data['message_message'].'
			<hr/>
			'.$info['reply_form'];
	elseif (isset($_GET['msg_send'])) : // send new message form
		echo $info['reply_form'];
	else : // display view
		if (!empty($info['items'])) {
			$unread = array();
			$read = array();
			foreach ($info['items'] as $message_id => $messageData) {
				if ($messageData['message_read']) {
					$read[$message_id] = $messageData;
				} else {
					$unread[$message_id] = $messageData;
				}
			}
			echo '<h5><a data-target="#unread_inbox" class="pointer text-dark" data-toggle="collapse">
			<i class="fa fa-caret-down"></i> Unread</a></h5>
			<div id="unread_inbox" class="collapse in">';
			if (!empty($unread)) {
				echo '<table id="unread_tbl" class="table table-responsive table-hover">';
				foreach ($unread as $id => $messageData) {
					echo "<tr>\n";
					echo "<td>".form_checkbox("pmID", "", $id, array(
							"input_id" => "pmID-".$id,
							"value" => $id,
							"class" => "checkbox m-b-0"
						))."</td>\n";
					echo "<td class='col-xs-2'><strong>".$messageData['contact_user']['user_name']."</strong></td>\n";
					echo "<td class='col-xs-7'><strong><a href='".$messageData['message']['link']."'>".$messageData['message']['name']."</a></strong></td>\n";
					echo "<td>".date("d M", $messageData['message_datestamp'])."</td>\n";
					echo "</tr>\n";
				}
				echo '</table>';
			} else {
				echo '<div class="well text-center">No unread messages</div>';
			}
			echo '</div>';
			echo '<h5><a data-target="#read_inbox" class="pointer text-dark" data-toggle="collapse">
				<i class="fa fa-caret-down"></i> Read</a></h5>
				<div id="read_inbox" class="collapse in">';
			if (!empty($read)) {
				echo '<table id="read_tbl"  class="table table-responsive table-hover">';
				foreach ($read as $id => $messageData) {
					echo "<tr>\n";
					echo "<td>".form_checkbox("pmID", "", $id, array(
							"input_id" => "pmID-".$id,
							"value" => $id,
							"class" => "checkbox m-b-0"
						))."</td>\n";
					echo "<td class='col-xs-2'>".$messageData['contact_user']['user_name']."</td>\n";
					echo "<td class='col-xs-7'><a href='".$messageData['message']['link']."'>".$messageData['message']['name']."</a></td>\n";
					echo "<td>".date("d M", $messageData['message_datestamp'])."</td>\n";
					echo "</tr>\n";
				}
			}
			echo '</table>';
			echo '</div>';
		} else {
			echo "<div class='well text-center'>".$info['no_item']."</div>\n";
		}
	endif;
}