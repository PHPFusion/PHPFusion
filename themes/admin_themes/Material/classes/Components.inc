<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Components.inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Material;
use PHPFusion\Admins;

class Components {
	private static $messages = array();

	public static function Sidebar() {
		echo '<aside class="sidebar fixed">';
			echo '<div class="header fixed hidden-xs hidden-sm hidden-md">';
				echo '<div class="pf-logo"></div>';
				echo '<div class="version">PHP Fusion 9</div>';
			echo '</div>';

			echo '<div class="sidebar-menu">';
				echo '<div id="searchBox" data-action="search-box" style="display: none;"><a href="#"><i class="fa fa-search"></i></a></div>';
				echo '<div class="search-box">';
					echo '<i class="fa fa-search input-search-icon"></i>';
					echo '<input type="text" id="search_box" name="search_box" class="form-control" placeholder="'.self::SetLocale('001').'"/>';
					echo '<ul id="search_result" style="display: none;"></ul>';
				echo '</div>';

				echo Admins::getInstance()->vertical_admin_nav(TRUE);
			echo '</div>';
		echo '</aside>';

		add_to_jquery("$('#search_box').bind('keyup', function(e) {
			var data = {
				'pagestring': $(this).val(),
				'url': '".$_SERVER['REQUEST_URI']."',
			};
			var sendData = $.param(data);
			$.ajax({
				url: '".MATERIAL."search.php".fusion_get_aidlink()."',
				dataType: 'html',
				method: 'get',
				data: sendData,
				success: function(e) {
					if ($('#search_box').val() == '') {
						$('#adl').show();
						$('#search_result').html(e).hide();
						$('#search_result li').html(e).hide();
					} else {
						if ($('body').hasClass('sidebar-sm')) {
							$('#adl').show();
						} else {
							$('#adl').hide();
						}

						$('#search_result').html(e).show();
					}
				}
			});
		});");
	}

	public static function TopMenu() {
		$sections  = Admins::getInstance()->getAdminSections();
		$locale    = fusion_get_locale();
		$aidlink   = fusion_get_aidlink();
		$userdata  = fusion_get_userdata();
		$languages = fusion_get_enabled_languages();
		$messages  = self::Messages();
		$messages  = !empty(count($messages)) ? '<span class="label label-danger messages">'.count($messages).'</span>' : '';

		echo '<div class="top-menu navbar fixed">';
			echo '<div class="toggleicon" data-action="togglemenu"><span></span></div>';
			echo '<div class="brand"><img src="'.IMAGES.'php-fusion-icon.png" alt="PHP Fusion 9"/> PHP Fusion 9</div>';
			echo '<div class="pull-right hidden-sm hidden-md hidden-lg home-xs"><a title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></div>';

			echo '<ul class="nav navbar-nav navbar-left hidden-xs hidden-sm hidden-md">';
				if (!empty($sections)) {
					$i = 0;

					foreach ($sections as $section_name) {
						$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && Admins::getInstance()->_isActive() == $i) ? ' class="active"' : '';
						echo '<li'.$active.'><a href="'.ADMIN.'index.php'.$aidlink.'&amp;pagenum='.$i.'" data-toggle="tooltip" data-placement="bottom" title="'.$section_name.'">'.Admins::getInstance()->get_admin_section_icons($i).'</a></li>';
						$i++;
					}
				}

			echo '</ul>';

			echo '<ul class="nav navbar-nav navbar-right hidden-xs">';
				if (count($languages) > 1) {
					echo '<li class="dropdown languages-switcher">';
						echo '<a class="dropdown-toggle pointer" data-toggle="dropdown" title="'.$locale['282'].'"><i class="fa fa-globe"></i><img class="current" src="'.BASEDIR.'locale/'.LANGUAGE.'/'.LANGUAGE.'.png" alt="'.translate_lang_names(LANGUAGE).'"/><span class="caret"></span></a>';
						echo '<ul class="dropdown-menu">';
							foreach ($languages as $language_folder => $language_name) {
								echo '<li><a class="display-block" href="'.clean_request('lang='.$language_folder, array('lang'), FALSE).'"><img class="m-r-5" src="'.BASEDIR.'locale/'.$language_folder.'/'.$language_folder.'-s.png" alt="'.$language_folder.'"/> '.$language_name.'</a></li>';
							}
						echo '</ul>';
					echo '</li>';
				}

				echo '<li class="dropdown user-s">';
					echo '<a href="#" class="dropdown-toggle pointer" data-toggle="dropdown">'.display_avatar($userdata, '30px', '', FALSE, 'avatar').' '.$locale['logged'].' <strong>'.$userdata['user_name'].'</strong><span class="caret"></span></a>';
					echo '<ul class="dropdown-menu" role="menu">';
						echo '<li><a class="display-block" href="'.BASEDIR.'edit_profile.php">'.$locale['UM080'].'</a></li>';
						echo '<li><a class="display-block" href="'.BASEDIR.'profile.php?lookup='.$userdata['user_id'].'">'.$locale['view'].' '.$locale['profile'].'</a></li>';
						echo '<li class="divider"></li>';
						echo '<li><a class="display-block" href="'.FUSION_REQUEST.'&amp;logout">'.$locale['admin-logout'].'</a></li>';
						echo '<li><a class="display-block" href="'.BASEDIR.'index.php?logout=yes">'.$locale['logout'].'</a></li>';
					echo '</ul>';
				echo '</li>';

				echo '<li><a title="'.$locale['settings'].'" href="'.ADMIN.'settings_main.php'.$aidlink.'"><i class="fa fa-cog"></i></a></li>';

				if (self::IsMobile()) {
					echo '<li><a title="'.$locale['message'].'" href="'.BASEDIR.'messages.php"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
				} else {
					echo '<li><a title="'.$locale['message'].'" href="#" data-action="messages"><i class="fa fa-envelope-o"></i>'.$messages.'</a></li>';
				}

				echo '<li><a title="'.fusion_get_settings('sitename').'" href="'.BASEDIR.'index.php"><i class="fa fa-home"></i></a></li>';
			echo '</ul>';
		echo '</div>';
	}

	public static function ThemeSettings() {
		echo '<aside id="theme-settings" class="hidden-xs">';
			echo '<a href="#" title="'.self::SetLocale('002').'" data-action="theme-settings" class="btn-theme-settings cogs-animation">';
				echo '<i class="fa fa-cog fa-spin"></i>';
				echo '<i class="fa fa-cog fa-spin fa-spin-reverse"></i>';
				echo '<i class="fa fa-cog fa-spin"></i>';
			echo '</a>';

			echo '<div class="settings-box">';
				echo '<h4>'.self::SetLocale('002').'</h4>';

				echo '<ul class="settings-menu">';
					$theme_settings = array(
						array('name' => 'hide-sidebar', 'title' => self::SetLocale('003')),
						array('name' => 'sidebar-sm', 'title' => self::SetLocale('004')),
						array('name' => 'fixedmenu', 'title' => self::SetLocale('005'), 'toggle' => 'on'),
						array('name' => 'fixedsidebar', 'title' => self::SetLocale('006'), 'toggle' => 'on'),
						array('name' => 'fixedfootererrors', 'title' => self::SetLocale('007'), 'toggle' => 'on'),
						array('name' => 'fullscreen', 'title' => self::SetLocale('008')),
					);

					foreach ($theme_settings as $setting) {
						echo '<li><a href="#" data-action="'.$setting['name'].'" id="'.$setting['name'].'">'.$setting['title'].'<div class="btn-toggle pull-right '.(!empty($setting['toggle']) ? $setting['toggle'] : '').'"></div></a></li>';
					}
				echo '</ul>';
			echo '</div>';
		echo '</aside>';
	}

	public static function MessagesBox() {
		$userdata = fusion_get_userdata();
		$messages = self::GetMessages();

		echo '<aside class="messages-box hidden-xs">';
			echo '<div class="button-group">';
				echo '<a href="#" id="messages-box-close">'.fusion_get_locale('close').'</a>';
				echo '<a href="'.BASEDIR.'messages.php?msg_send=new" class="new-message">'.self::SetLocale('011').'</a>';
			echo '</div>';
			echo '<h3 class="title">'.self::SetLocale('009').'</h3>';

			if (!empty($messages)) {
				echo '<ul>';
					foreach ($messages as $message) {
						echo '<li>';
							echo '<div class="message-block">';
								echo display_avatar($message['user'], '40px', '', FALSE, 'avatar m-r-5');
								echo '<div class="block">';
									echo '<span class="title">'.$message['user']['user_name'].' <small>'.$message['datestamp'].'</small></span>';
									echo '<br /><small>'.trim_text($message['title'], 20).'</small>';
									echo '<a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_read='.$message['link'].'" class="read-message">'.self::SetLocale('010').'</a>';
								echo '</div>';
							echo '</div>';
						echo '</li>';
					}
				echo '</ul>';
			} else {
				echo '<div class="no-messages">';
					echo '<i class="fa fa-envelope icon"></i><br />';
					echo self::SetLocale('012');
				echo '</div>';
			}
		echo '</aside>';
	}

	public static function SetLocale($lc = NULL) {
		$locale = array();

		if (file_exists(MATERIAL."locale/".LANGUAGE.".php")) {
			include MATERIAL."locale/".LANGUAGE.".php";
		} else {
			include MATERIAL."locale/English.php";
		}

		return $locale['material_'.$lc];
	}

	public static function Messages() {
		$userdata  = fusion_get_userdata();

		$result = dbquery("
			SELECT message_id, message_subject, message_from user_id, u.user_name, u.user_status, u.user_avatar, message_datestamp
			FROM ".DB_MESSAGES."
			INNER JOIN ".DB_USERS." u ON u.user_id=message_from
			WHERE message_to = '".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0'
			GROUP BY message_id
		");

		$msg_count = "message_to = '".$userdata['user_id']."' AND message_user='".$userdata['user_id']."'";

		if (dbcount("(message_id)", DB_MESSAGES, $msg_count)) {
			if (dbrows($result) > 0) {
				while ($data = dbarray($result)) {
					self::$messages[] = array(
						"link"      => $data['message_id'],
						"title"     => $data['message_subject'],
						"user"      => array(
							"user_id"     => $data['user_id'],
							"user_name"   => $data['user_name'],
							"user_status" => $data['user_status'],
							"user_avatar" => $data['user_avatar']
						),
						"datestamp" => timer($data['message_datestamp'])
					);
				}
			}
		}

		return self::$messages;
	}

	public static function GetMessages() {
		return self::$messages;
	}

	public static function IsMobile() {
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}
}
