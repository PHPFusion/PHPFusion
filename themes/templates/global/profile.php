<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: profile.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }
/**
 * Profile edit form
 */
if (!function_exists('render_userform')) {
	add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>\n");
	function render_userform($info) {
		// page navigation
		$tab_title = array();
		$endnav = "";
		if (isset($info['section']) && count($info['section'])>1 && !defined("ADMIN_PANEL")) {
			$i = 1;
			$tab_title['title'][0] = '';
			$tab_title['id'][0] = '';
			$tab_title['icon'][0] = '';
			foreach ($info['section'] as $page_section) {
				$tab_title['title'][$i] = $page_section['name'];
				$tab_title['id'][$i] = $i;
				$tab_title['icon'][$i] = '';
				$i++;
			}
			$tab_active = tab_active($tab_title, $_GET['profiles'], 1);
			echo opentab($tab_title, $tab_active, 'profile', 1);
			echo opentabbody($tab_title['title'][$_GET['profiles']], $_GET['profiles'], $tab_active, 1);
			$endnav 	= closetabbody();
			$endnav 	.= closetab();
		}

		echo "<div id='register_form' class='row m-t-20'>\n";
		echo "<div class='col-xs-12 col-sm-12' style='padding:0 40px;'>\n";
		echo $info['openform'];
		echo $info['user_name'];
		echo $info['user_email'];
		echo $info['user_hide_email'];
		echo $info['user_avatar'];
		echo $info['user_password'];
		if (iADMIN) echo $info['user_admin_password'];
		if (isset($info['user_field']))	echo $info['user_field'];
		echo isset($info['validate']) ? $info['validate'] : '';
		echo isset($info['terms']) ? $info['terms'] : '';
		echo $info['button'];
		echo $info['closeform'];
		echo "</div>\n</div>\n";
		echo $endnav;
	}
}

/**
 * Profile display
 */
if (!function_exists('render_userprofile')) {
	add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>\n");
	function render_userprofile($info) {
		// Basic User Information
		$basic_info = isset($info['core_field']) ? $info['core_field'] : array();
		//User Fields Module Information
		$field_info = $info['user_field'];
		$user_info = '';
		$user_avatar = '';
		$user_name = '';
		$user_level = '';
		foreach ($basic_info as $field_id => $data) {
			if ($field_id == 'profile_user_avatar') {
				$avatar['user_avatar'] = $data['value'];
				$avatar['user_status'] = $data['status'];
				$user_avatar = "<img src='".$data['value']."' style='width:50px;' alt='".$info['core_field']['profile_user_name']['value']."'/>\n";
			} elseif ($field_id == 'profile_user_name') {
				$user_name = "<h4>".$data['value']."</h4>\n";
				$user_name .= "<hr/>\n";
			} elseif ($field_id == 'profile_user_level') {
				$user_info .= "
				<div id='".$field_id."' class='m-b-5 row'>
					<span class='col-xs-12 col-sm-3'>".$data['title']."</span>
					<div class='col-xs-12 col-sm-9 profile_text overflow-hide'>".$data['value']."</div>
				</div>\n";
			} else {
				$user_info .= $data['value'] ? "
					<div id='".$field_id."' class='m-b-5 row'>
					<span class='col-xs-12 col-sm-3'>".$data['title']."</span>
					<div class='col-xs-12 col-sm-9 profile_text'>".$data['value']."</div>
					</div>\n" : '';
			}
		}

		$user_field = '';
		foreach ($field_info as $field_cat_id => $category_data) {

			$user_field .= $category_data['title'];
			$user_field .= "<div class='list-group-item'>";
			if (isset($category_data['fields'])) {
				foreach ($category_data['fields'] as $field_id => $field_data) {
					$user_field .= "<div id='".$field_id."' class='m-b-5 row'>
					<span class='col-xs-12 col-sm-3'>".$field_data['title']."</span>
					<div class='col-xs-12 col-sm-9 profile_text'>".$field_data['value']."</div>
					</div>\n";
				}
			}
			$user_field .= "</div>\n";
		}

		// buttons
		$user_buttons = '';
		if (!empty($info['buttons'])) {
			$user_buttons = "<div class='btn-group m-t-10 m-b-10 col-sm-offset-3'>";
			foreach($info['buttons'] as $buttons) {
				$user_buttons .= "<a class='btn btn-sm button btn-default' href='".$buttons['link']."'>".$buttons['name']."</a>";
			}
			$user_buttons .= "</div>\n";
		}
		?>		
		<section id='user-profile' class='row'>
			<div class='col-xs-12 col-sm-3 col-lg-2'>
				<ul class='profile_link_nav m-t-20'>
					<?php foreach ($info['section'] as $page_section) {	?>
						<li <?php echo $page_section['active'] ? "class='active'" : ''  ?> 
							<a href='<?php echo $page_section['link'] ?>'><?php echo $page_section['name'] ?></a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<div class='col-xs-12 col-sm-9 col-lg-10'>
				<?php echo $user_name; ?>
				<div class='clearfix m-t-10'>
					<div class='pull-left m-r-20'><?php echo $user_avatar ?></div>
					<div class='overflow-hide'>
						<?php
						echo $user_level;
						echo $user_info;
						echo $user_buttons;
						echo $user_field;
						?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
