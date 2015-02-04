<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: site_links.php
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
require_once "../maincore.php";

if (!checkrights("SL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/sitelinks.php";


class SiteLinks_Admin {

	private $data = array(
		'link_name' => '',
		'link_url' => '',
		'link_icon' => '',
		'link_cat' => 0,
		'link_language' => LANGUAGE,
		'link_visibility' => 0,
		'link_order' => 0,
		'link_position' => 1,
		'link_window' => 0,
	);

	private $position_opts = array();
	private $language_opts = array();
	private $link_index = array();
	private $form_action = '';

	static function link_breadcrumbs($link_index) {
		global $aidlink;
		/* Make an infinity traverse */
		function breadcrumb_arrays($index, $id) {
			global $aidlink;
			$crumb = &$crumb;
			//$crumb += $crumb;
			if (isset($index[get_parent($index, $id)])) {
				$_name = dbarray(dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_id='".$id."'"));
				$crumb = array('link'=>FUSION_SELF.$aidlink."&amp;link_cat=".$_name['link_id'], 'title'=>$_name['link_name']);
				if (isset($index[get_parent($index, $id)])) {
					if (get_parent($index, $id) == 0) {
						return $crumb;
					}
					$crumb_1 = breadcrumb_arrays($index, get_parent($index, $id));
					$crumb = array_merge_recursive($crumb, $crumb_1); // convert so can comply to Fusion Tab API.
				}
			}
			return $crumb;
		}
		// then we make a infinity recursive function to loop/break it out.
		$crumb = breadcrumb_arrays($link_index, $_GET['link_cat']);
		// then we sort in reverse.
		if (count($crumb['title']) > 1)  { krsort($crumb['title']); krsort($crumb['link']); }
		// then we loop it out using Dan's breadcrumb.
		add_to_breadcrumbs(array('link'=>FUSION_SELF.$aidlink, 'title'=>'Site Link Index'));
		if (count($crumb['title']) > 1) {
			foreach($crumb['title'] as $i => $value) {
				add_to_breadcrumbs(array('link'=>$crumb['link'][$i], 'title'=>$value));
			}
		} elseif (isset($crumb['title'])) {
			add_to_breadcrumbs(array('link'=>$crumb['link'], 'title'=>$crumb['title']));
		}
		// hola!
	}

	public function __construct() {
		global $locale, $aidlink;
		$_GET['link_id'] = isset($_GET['link_id']) && isnum($_GET['link_id']) ? $_GET['link_id'] : 0;
		$_GET['link_cat'] = isset($_GET['link_cat']) && isnum($_GET['link_cat']) ? $_GET['link_cat'] : 0;
		$this->form_action = FUSION_SELF.$aidlink;
		$this->language_opts = fusion_get_enabled_languages();

		$this->position_opts = array(
			'1' => $locale['SL_0025'],
			'2' => $locale['SL_0026'],
			'3' => $locale['SL_0027']
		);

		$this->link_index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');
		self::link_breadcrumbs($this->link_index);

		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
		add_to_jquery("
		$('#site-links').sortable({
			handle : '.handle',
			placeholder: 'state-highlight',
			connectWith: '.connected',
			scroll: true,
			axis: 'y',
			update: function () {
				var ul = $(this),
					order = ul.sortable('serialize'),
					i = 0;
				$('#info').load('includes/site_links_updater.php".$aidlink."&' +order+ 'link_cat=".$_GET['link_cat']."');
				//console.log(order);
				ul.find('.num').each(function(i) {
					$(this).text(i+1);
				});
				ul.find('li').removeClass('tbl2').removeClass('tbl1');
				ul.find('li:odd').addClass('tbl2');
				ul.find('li:even').addClass('tbl1');
				window.setTimeout('closeDiv();',2500);
			}
		});
		");

		// post of quick form
		if (isset($_POST['link_quicksave'])) {
			$quick['link_id'] = isset($_POST['link_id']) ? form_sanitizer($_POST['link_id'], '0', 'link_id') : 0;
			$quick['link_icon'] = isset($_POST['link_icon']) ? form_sanitizer($_POST['link_icon'], '', 'link_icon') : '';
			$quick['link_cat'] = isset($_POST['link_cat']) ? form_sanitizer($_POST['link_cat'], '0', 'link_cat') : '';
			$quick['link_position'] = isset($_POST['link_position']) ? form_sanitizer($_POST['link_position'], '1', 'link_position') : 1;
			$quick['link_language'] = isset($_POST['link_language']) ? form_sanitizer($_POST['link_language'], LANGUAGE, 'link_language') : LANGUAGE;
			$quick['link_visibility'] = isset($_POST['link_visibility']) ? form_sanitizer($_POST['link_visibility'], '0', 'link_visibility') : 0;
			$quick['link_window'] = isset($_POST['link_window']) ? 1 : 0;
			if ($quick['link_id']) {
				$c_result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE link_id='".intval($quick['link_id'])."'");
				if (dbrows($c_result)) {
					$quick += dbarray($c_result);
					// update
					dbquery_insert(DB_SITE_LINKS, $quick,'update');
					redirect(FUSION_SELF.$aidlink);
				}
			}
		}


		// Delete Rows
		if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
			$data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"));
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'".$data['link_order']."'");
			$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=del");
		}

		// Update or Insert Rows
		elseif (isset($_POST['savelink'])) {
			$data['link_name'] = isset($_POST['link_name']) ? form_sanitizer($_POST['link_name'], '', 'link_name') : '';
			$data['link_url'] = isset($_POST['link_url']) ? form_sanitizer($_POST['link_url'], '', 'link_url') : '';
			$data['link_icon'] = isset($_POST['link_icon']) ? form_sanitizer($_POST['link_icon'], '', 'link_icon') : '';
			$data['link_cat'] = isset($_POST['link_cat']) ? form_sanitizer($_POST['link_cat'], '', 'link_cat') : 0;
			$data['link_language'] = isset($_POST['link_language']) ? form_sanitizer($_POST['link_language'], '', 'link_language') : LANGUAGE;
			$data['link_visibility'] = isset($_POST['link_visibility']) ? form_sanitizer($_POST['link_visibility'], '', 'link_visibility') : '0';
			$data['link_position'] = isset($_POST['link_position']) ? form_sanitizer($_POST['link_position'], '', 'link_position') : '0';
			$data['link_window'] = isset($_POST['link_window']) ? 1 : 0;
			$data['link_order'] = isset($_POST['link_order']) ? form_sanitizer($_POST['link_order'], '', 'link_order') : '0';

			if (self::verify_edit($data['link_id'])) {
				// edit
				// check old order.
				$old_order = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." WHERE link_id='".$data['link_id']."'"));
				if ($old_order > $data['link_order']) { // current order is shifting up. 6 to 3., 1,2,(3),3->4,4->5,5->6. where orders which is less than 6 but is more or equals current.
					$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order+1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['link_cat']."' AND link_order<'".$old_order['link_order']."' AND link_order>='".$data['link_order']."'");
				} elseif ($old_order < $data['link_order']) { // current order is shifting down. 3 to 6. 1,2,(3),3<-4,5,5<-(6),7. where orders which is more than old order, and less than current equals.
					$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['link_cat']."' AND link_order>'".$old_order['link_order']."' AND link_order<='".$data['link_order']."'");
				} // else no change.
				dbquery_insert(DB_SITE_LINKS, $data, 'update');
				if (!defined("FUSION_NULL")) redirect(FUSION_SELF.$aidlink."&amp;status=su");
			} else {
				// save
				if (!$data['link_order']) $data['link_order'] = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['link_cat']."'"), 0)+1;
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order+1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['link_cat']."' AND forum_order>='".$data['link_order']."'");
				dbquery_insert(DB_SITE_LINKS, $data, 'save');
				if (!defined("FUSION_NULL")) redirect(FUSION_SELF.$aidlink."&amp;status=sn");
			}
		}

		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
			$result = dbquery("SELECT link_name, link_url, link_visibility, link_order, link_position, link_window, link_language FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'");
			if (dbrows($result)) {
				$this->data = dbarray($result);
				$this->formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;link_id=".$_GET['link_id'];
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
	}

	static function getVisibility() {
		$visibility_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$visibility_opts[$user_group['0']] = $user_group['1'];
		}
		return $visibility_opts;
	}

	public function verify_edit($id = false) {
		if ($_GET['link_id'] && isset($_GET['action']) && $_GET['action'] == 'edit') {
			$id = ($id) ? $id : $_GET['link_id'];
			$verify = dbcount("(link_id)", DB_SITE_LINKS, "link_id='".intval($id)."'");
			if ($verify) return true;
		}
		return false;
	}

	static function getMessage() {
		global $locale;
		if (isset($_GET['status']) && !isset($message)) {
			if ($_GET['status'] == "sn") {
				$message = $locale['SL_0015'];
			} elseif ($_GET['status'] == "su") {
				$message = $locale['SL_0016'];
			} elseif ($_GET['status'] == "del") {
				$message = $locale['SL_0017'];
			}
			if ($message) {
				echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$message."</div></div>\n";
			}
		}
	}

	public function menu_listing() {
		global $locale, $aidlink;

		add_to_jquery("
		$('.actionbar').hide();
		$('tr').hover(
			function(e) { $('#blog-'+ $(this).data('id') +'-actions').show(); },
			function(e) { $('#blog-'+ $(this).data('id') +'-actions').hide(); }
		);
		");

		add_to_jquery("
			$('.qform').hide();
			$('.qedit').bind('click', function(e) {
				// ok now we need jquery, need some security at least.token for example. lets serialize.
				$.ajax({
					url: '".ADMIN."includes/sldata.php',
					dataType: 'json',
					type: 'post',
					data: { q: $(this).data('id'), token: '".$aidlink."' },
					success: function(e) {
						console.log(e.blog_id);
						$('#link_id').val(e.link_id);
						$('#link_name').val(e.link_name);
						$('#link_icon').val(e.link_icon);
						$('#link_position').select2('val', e.link_position);
						$('#link_language').select2('val', e.link_language);
						$('#link_visibility').select2('val', e.link_visibility);
						var length = e.link_window;
						if (e.link_window > 0) { $('#link_window').attr('checked', true);	} else { $('#link_window').attr('checked', false); }
					},
					error : function(e) {
					console.log(e);
					}
				});
				$('.qform').show();
				$('.list-result').hide();
			});
			$('#cancel').bind('click', function(e) {
				$('.qform').hide();
				$('.list-result').show();
			});
		");

		$result = dbquery("SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".intval($_GET['link_cat'])."' ORDER BY link_order");

		echo "<div class='m-t-20'>\n";
		echo "<table class='table table-responsive'>\n";
		echo "<tr>\n";
		echo "<th>\n</th>\n";
		echo "<th class='col-xs-12 col-sm-4 col-md-4 col-lg-4'>".$locale['SL_0050']."</th>\n";
		echo "<th>".$locale['SL_0070']."</th>";
		echo "<th>".$locale['SL_0071']."</th>";
		echo "<th>".$locale['SL_0072']."</th>";
		echo "<th>".$locale['SL_0051']."</th>";
		echo "<th>".$locale['SL_0052']."</th>";
		echo "<th>".$locale['SL_0073']."</th>";
		echo "</tr>\n";

		// Load form data. Then, if have data, show form.. when post, we use back this page's script.
		echo "<tr class='qform'>\n";
		echo "<td colspan='8'>\n";
		echo "<div class='list-group-item m-t-20 m-b-20'>\n";
		echo openform('quick_edit', 'quick_edit', 'post', FUSION_SELF.$aidlink, array('downtime'=>5, 'notice'=>0));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-5 col-md-12 col-lg-6'>\n";
		echo form_text($locale['SL_0020'], 'link_name', 'link_name', '', array('placeholder'=>'Link Title'));
		echo form_text($locale['SL_0030'], 'link_icon', 'link_icon', $this->data['link_icon'], array('max_length' => 100));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select($locale['global_ML100'], 'link_language', 'link_language', $this->language_opts, $this->data['link_language'], array('placeholder' => $locale['choose'], 'width'=>'100%'));
		echo form_select($locale['SL_0024'], 'link_position', 'link_position', $this->position_opts, $this->data['link_position'], array('width'=>'100%'));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-3'>\n";
		echo form_select($locale['SL_0022'], 'link_visibility', 'link_visibility', self::getVisibility(), $this->data['link_visibility'], array('placeholder' => $locale['choose'], 'width'=>'100%'));
		echo form_checkbox($locale['SL_0028'], 'link_window', 'link_window', $this->data['link_window']);
		echo form_hidden('', 'link_id', 'link_id', '', array('writable'=>1));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button($locale['cancel'], 'cancel', 'cancel', 'cancel', array('class'=>'btn btn-default m-r-10', 'type'=>'button'));
		echo form_button($locale['save'], 'link_quicksave', 'link_quicksave', 'save', array('class'=>'btn btn-primary'));
		echo "</div>\n";
		echo closeform();
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tbody id='site-links' class='connected'>\n";
		if (dbrows($result)>0) {
			$i= 0;
			while ($data = dbarray($result)) {
				$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				echo "<tr id='listItem_".$data['link_id']."' data-id='".$data['link_id']."' class='list-result ".$row_color."'>\n";
				echo "<td><input type='checkbox' value='".$data['link_id']."'></td>\n";
				echo "<td>\n";
				echo "<a class='text-dark' href='".FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$data['link_id']."'>".$data['link_name']."</a>\n";
				echo "<div class='actionbar text-smaller' id='blog-".$data['link_id']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;section=nform&amp;action=edit&amp;link_id=".$data['link_id']."'>".$locale['edit']."</a> |
				<a class='qedit pointer' data-id='".$data['link_id']."'>".$locale['qedit']."</a> |
				<a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;link_id=".$data['link_id']."' onclick=\"return confirm('".$locale['SL_0080']."');\">".$locale['delete']."</a> |
				";
				if (strstr($data['link_url'], "http://") || strstr($data['link_url'], "https://")) {
					echo "<a href='".$data['link_url']."'>".$locale['view']."</a>\n";
				} else {
					echo "<a href='".BASEDIR.$data['link_url']."'>".$locale['view']."</a>\n";
				}
				echo "</div>";
				echo "</td>\n";
				echo "<td><i class='".$data['link_icon']."'></i></td>\n";
				echo "<td>".($data['link_window'] ? $locale['yes'] : $locale['no'])."</td>\n";
				echo "<td>".$this->position_opts[$data['link_position']]."</td>\n";
				$visibility = self::getVisibility();
				echo "<td>".$visibility[$data['link_visibility']]."</td>\n";
				echo "<td class='num'>".$data['link_order']."</td>\n";
				echo "<td><i class='pointer handle fa fa-arrows' title='Move'></i></td>\n";
				echo "</tr>\n";
				$i++;
			}
		} else {
			echo "<tr>\n";
			echo "<td colspan='7' class='text-center'>".$locale['SL_0062']."</td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n";
		echo "</table>\n";
		echo "</div>\n";
	}

	public function menu_form() {
		global $locale, $aidlink;
		echo "<div class='m-t-20'>\n";
		echo openform('layoutform', 'layoutform', 'post', $this->form_action, array('downtime' => 10));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		echo form_text($locale['SL_0020'], 'link_name', 'link_names', $this->data['link_name'], array('max_length' => 100, 'required' => 1, 'error_text' => $locale['SL_0085'], 'inline'=>1));
		echo form_text('Link Icon', 'link_icon', 'link_icons', $this->data['link_icon'], array('max_length' => 100, 'inline'=>1));
		echo form_text($locale['SL_0021'], 'link_url', 'link_urls', $this->data['link_url'], array('required' => 1, 'error_text' => $locale['SL_0086'], 'inline'=>1));
		echo form_text($locale['SL_0023'], 'link_order', 'link_orders', $this->data['link_order'],  array('number' => 1, 'class' => 'pull-left', 'inline' => 1));
		echo form_select($locale['SL_0024'], 'link_position', 'link_positions', $this->position_opts, $this->data['link_position'], array('inline'=>1));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select_tree($locale['SL_0029'], "link_cat", "link_categorys", $this->data['link_cat'], array("parent_value" => $locale['parent'], 'width'=>'100%', 'query'=>"WHERE link_language='".LANGUAGE."'"), DB_SITE_LINKS, "link_name", "link_id", "link_cat");
		echo form_select($locale['global_ML100'], 'link_language', 'link_languages', $this->language_opts, $this->data['link_language'], array('placeholder' => $locale['choose'], 'width'=>'100%'));
		echo form_select($locale['SL_0022'], 'link_visibility', 'link_visibilitys', self::getVisibility(), $this->data['link_visibility'], array('placeholder' => $locale['choose'], 'width'=>'100%'));
		echo form_checkbox($locale['SL_0028'], 'link_window', 'link_windows', $this->data['link_window']);
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button($locale['SL_0040'], 'savelink', 'savelink', $locale['SL_0040'], array('class'=>'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}
}

$site_links = new SiteLinks_Admin();
$edit = $site_links->verify_edit();
$master_title['title'][] = $locale['SL_0001'];
$master_title['id'][] = 'links';
$master_title['icon'][] = '';
$master_title['title'][] = $edit ? $locale['SL_0011'] : $locale['SL_0010'];
$master_title['id'][] = 'nform';
$master_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';
$tab_active = tab_active($master_title, $edit ?  1 : 0, 1);
opentable($locale['SL_0012']);
echo "<div id='info'></div>\n";
echo opentab($master_title, $tab_active, 'link', FUSION_SELF);
echo opentabbody($master_title['title'][0], 'links', $tab_active, 1);
$site_links->menu_listing();
echo closetabbody();
echo opentabbody($master_title['title'][1], 'nform', $tab_active, 1);
$site_links->menu_form();
echo closetabbody();
echo closetab();
closetable();

require_once THEMES."templates/footer.php";
?>
