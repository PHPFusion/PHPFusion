<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: SiteLinks.php
| Author: Frederick MC Chan (hien)
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

class SiteLinks {
	private $data = array('link_id' => 0,
		'link_name' => '',
		'link_url' => '',
		'link_icon' => '',
		'link_cat' => 0,
		'link_language' => LANGUAGE,
		'link_visibility' => 0,
		'link_order' => 0,
		'link_position' => 1,
		'link_window' => 0,);
	private $position_opts = array();
	private $language_opts = array();
	private $link_index = array();
	private $form_action = '';

	/**
	 * For Administration panel only
	 * @param $link_index
	 */
	static function link_breadcrumbs($link_index) {
		global $aidlink, $locale;
		/* Make an infinity traverse */
		function breadcrumb_arrays($index, $id) {
			global $aidlink;
			$crumb = & $crumb;
			//$crumb += $crumb;
			if (isset($index[get_parent($index, $id)])) {
				$_name = dbarray(dbquery("SELECT link_id, link_name FROM ".DB_SITE_LINKS." WHERE link_id='".$id."'"));
				$crumb = array('link' => FUSION_SELF.$aidlink."&amp;link_cat=".$_name['link_id'],
					'title' => $_name['link_name']);
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
		if (count($crumb['title']) > 1) {
			krsort($crumb['title']);
			krsort($crumb['link']);
		}
		// then we loop it out using Dan's breadcrumb.
		add_breadcrumb(array('link' => FUSION_SELF.$aidlink, 'title' => $locale['SL_0001']));
		if (count($crumb['title']) > 1) {
			foreach ($crumb['title'] as $i => $value) {
				add_breadcrumb(array('link' => $crumb['link'][$i], 'title' => $value));
			}
		} elseif (isset($crumb['title'])) {
			add_breadcrumb(array('link' => $crumb['link'], 'title' => $crumb['title']));
		}
	}

	public function __construct() {
		$this->language_opts = fusion_get_enabled_languages();
		$this->link_index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');
	}

	/**
	 * Sanitization
	 */
	private function AdminInstance() {
		global $locale, $aidlink;
		if (empty($locale['SL_0025'])) {
			include LOCALE.LOCALESET."admin/sitelinks.php";
		}
		$_GET['link_id'] = isset($_GET['link_id']) && isnum($_GET['link_id']) ? $_GET['link_id'] : 0;
		$_GET['link_cat'] = isset($_GET['link_cat']) && isnum($_GET['link_cat']) ? $_GET['link_cat'] : 0;
		$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';
		$this->position_opts = array(
			'1' =>  $locale['SL_0025'], // only css navigational panel
			'2' => $locale['SL_0026'], // both
			'3' => $locale['SL_0027'] // subheader
		);
		self::link_breadcrumbs($this->link_index); // must move this out.
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

		switch ($_GET['action']) {
			case 'edit':
				$this->data = self::load_sitelinks($_GET['link_id']);
				if (!$this->data['link_id']) redirect(FUSION_SELF.$aidlink);
				$this->formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;section=nform&amp;link_id=".$_GET['link_id']."&amp;link_cat=".$_GET['link_cat'];
				break;
			case 'delete':
				$result = self::delete_sitelinks($_GET['link_id']);
				if ($result) {
					addNotice("success", $locale['SL_0017']);
					redirect(FUSION_SELF.$aidlink);
				}
				break;
			default:
				$this->form_action = FUSION_SELF.$aidlink."&amp;section=nform";
				break;
		}

	}

	/**
	 * SQL Delete Site Link Action
	 * @param $link_id
	 * @return bool|mixed|null|PDOStatement|resource
	 */
	public static function delete_sitelinks($link_id) {
		$result = NULL;
		if (isnum($link_id) && self::verify_edit($link_id)) {
			$data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"));
			$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'".$data['link_order']."'");
			if ($result) $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
			return $result;
		}
		return $result;
	}

	/**
	 * Site Link Loader
	 * @param $link_id
	 * @return array
	 */
	public static function load_sitelinks($link_id) {
		$array = array();
		if (isnum($link_id) && self::verify_edit($link_id)) {
			$result = dbquery("SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'");
			if (dbrows($result)) {
				return (array)dbarray($result);
			}
			return $array;
		}
	}

	/**
	 * Given a URL, get sitelinks
	 * @param string $url - url to match (link_url) column
	 * @param string $column - column data to output, blank for all
	 * @return array|bool
	 */
	public static function get_current_SiteLinks($url = "", $key = NULL) {
		$url = stripinput($url);
		static $data = array();
		if (empty($data)) {
			if (!$url) {
				$pathinfo = pathinfo($_SERVER['PHP_SELF']);
				$url = str_replace(fusion_get_settings("site_path"), "", $pathinfo['dirname']).'/'.$pathinfo['basename'];
			}
			$result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE link_url='".$url."' AND link_language='".LANGUAGE."'");
			if (dbrows($result)>0) {
				$data = dbarray($result);
			}
		}
		return $key === NULL ? $data : (isset($data[$key]) ? $data[$key] : NULL);
	}

	/**
	 * Get Group Array
	 * @return array
	 */
	static function getVisibility() {
		$visibility_opts = array();
		$user_groups = getusergroups();
		while (list($key, $user_group) = each($user_groups)) {
			$visibility_opts[$user_group['0']] = $user_group['1'];
		}
		return $visibility_opts;
	}

	/**
	 * Link ID validation
	 * @param $link_id
	 * @return bool|string
	 */
	public static function verify_edit($link_id) {
		if (isnum($link_id)) {
			return dbcount("(link_id)", DB_SITE_LINKS, "link_id='".intval($link_id)."'");
		}
		return FALSE;
	}

	/**
	 * Form for Listing Menu
	 */
	public function menu_listing() {
		global $locale, $aidlink;
		$this->AdminInstance();
		add_to_jquery("
			$('.actionbar').hide();
			$('tr').hover(
				function(e) { $('#blog-'+ $(this).data('id') +'-actions').show(); },
				function(e) { $('#blog-'+ $(this).data('id') +'-actions').hide(); }
			);
			$('.qform').hide();
			$('.qedit').bind('click', function(e) {
				// ok now we need jquery, need some security at least.token for example. lets serialize.
				$.ajax({
					url: '".ADMIN."includes/sldata.php',
					dataType: 'json',
					type: 'post',
					data: { q: $(this).data('id'), token: '".$aidlink."' },
					success: function(e) {
						$('#link_id').val(e.link_id);
						$('#link_name').val(e.link_name);
						$('#link_icon').val(e.link_icon);
						$('#ll_position').select2('val', e.link_position);
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
		echo "<table class='table table-striped table-responsive'>\n";
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

		if (isset($_POST['link_quicksave'])) {
			$this->data = array(
				"link_id"=> form_sanitizer($_POST['link_id'], 0, "link_id"),
				"link_name"=> form_sanitizer($_POST['link_name'], "", "link_name"),
				"link_icon"=> form_sanitizer($_POST['link_icon'], "", "link_icon"),
				"link_language"=> form_sanitizer($_POST['link_language'], "", "link_language"),
				"link_position"=> form_sanitizer($_POST['link_position'], "", "link_position"),
				"link_visibility"=> form_sanitizer($_POST['link_visibility'], "", "link_visibility"),
				"link_window"=> isset($_POST['link_window']) ? true : false,
			);
			if (\defender::safe()) {
				dbquery_insert(DB_SITE_LINKS, $this->data, "update");
				addNotice("success", $locale['SL_0016']);
				redirect(FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$_GET['link_cat']);
			}
		}
		echo "<tr class='qform'>\n";
		echo "<td colspan='8'>\n";
		echo "<div class='list-group-item m-t-20 m-b-20'>\n";
		echo openform('quick_edit', 'post', FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$_GET['link_cat']);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-5 col-md-12 col-lg-6'>\n";
		echo form_hidden("link_id", "", $this->data['link_id']);
		echo form_text('link_name', $locale['SL_0020'], '', array('placeholder' => 'Link Title'));
		echo form_text('link_icon', $locale['SL_0030'], $this->data['link_icon'], array('max_length' => 100));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('link_language', $locale['global_ML100'], $this->data['link_language'], array('options' => $this->language_opts,
			'input_id' => 'sitelinks_language',
			'width' => '100%'));
		echo form_select('link_position', $locale['SL_0024'], $this->data['link_position'], array('options' => $this->position_opts,
			'input_id' => 'll_position',
			'width' => '100%'));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-3'>\n";
		echo form_select('link_visibility', $locale['SL_0022'], $this->data['link_visibility'], array('options' => self::getVisibility(),
			'input_id' => 'sitelinks_visibility',
			'width' => '100%'));
		echo form_checkbox('link_window', $locale['SL_0028'], $this->data['link_window'], array('input_id' => 'll_window'));
		echo "</div>\n";
		echo "</div>\n";
		echo "<div class='m-t-10 m-b-10'>\n";
		echo form_button('cancel', $locale['cancel'], 'cancel', array('class' => 'btn btn-default m-r-10',
			'type' => 'button'));
		echo form_button('link_quicksave', $locale['save'], 'save', array('class' => 'btn btn-primary'));
		echo "</div>\n";
		echo closeform();

		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tbody id='site-links' class='connected'>\n";
		if (dbrows($result) > 0) {
			$i = 0;
			while ($data = dbarray($result)) {
				//$row_color = ($i%2 == 0 ? "tbl1" : "tbl2");
				echo "<tr id='listItem_".$data['link_id']."' data-id='".$data['link_id']."' class='list-result '>\n"; //".$row_color."
				//echo "<td><input type='checkbox' value='".$data['link_id']."'></td>\n";
				echo "<td></td>\n";
				echo "<td>\n";
				echo "<a class='text-dark' href='".FUSION_SELF.$aidlink."&amp;section=links&amp;link_cat=".$data['link_id']."'>".$data['link_name']."</a>\n";
				echo "<div class='actionbar text-smaller' id='blog-".$data['link_id']."-actions'>
				<a href='".FUSION_SELF.$aidlink."&amp;section=nform&amp;action=edit&amp;link_id=".$data['link_id']."&amp;link_cat=".$data['link_cat']."'>".$locale['edit']."</a> |
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

	/**
	 * Site Links Form
	 */
	public function menu_form() {
		global $locale, $aidlink;
		fusion_confirm_exit();
		$this->AdminInstance();
		if (isset($_POST['savelink'])) {

			$data = array(
				"link_id" =>	form_sanitizer($_POST['link_id'], 0, 'link_id'),
				"link_cat" => form_sanitizer($_POST['link_cat'], 0, 'link_cat'),
				"link_name" =>  form_sanitizer($_POST['link_name'], '', 'link_name'),
				"link_url" 	=>	form_sanitizer($_POST['link_url'], '', 'link_url'),
				"link_icon" => form_sanitizer($_POST['link_icon'], '', 'link_icon'),
				"link_language" => form_sanitizer($_POST['link_language'], '', 'link_language'),
				"link_visibility" => form_sanitizer($_POST['link_visibility'], '', 'link_visibility'),
				"link_position" =>	form_sanitizer($_POST['link_position'], '', 'link_position'),
				"link_order" => form_sanitizer($_POST['link_order'], '', 'link_order')
			);

			if (!$data['link_order']) {
				$data['link_order'] = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_cat='".$data['link_cat']."'"), 0)+1;
			}

			if (\defender::safe()) {
				if (self::verify_edit($data['link_id'])) {
					dbquery_order(DB_SITE_LINKS, $data['link_order'], "link_order", $data['link_id'], "link_id", $data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "update");
					dbquery_insert(DB_SITE_LINKS, $data, 'update');
					addNotice("success", $locale['SL_0016']);
					redirect(FUSION_SELF.$aidlink."&amp;link_cat=".$data['link_cat']);
				} else {
					dbquery_order(DB_SITE_LINKS, $data['link_order'], "link_order", $data['link_id'], "link_id", $data['link_cat'], "link_cat", multilang_table("SL"), "link_language", "save");
					dbquery_insert(DB_SITE_LINKS, $data, 'save');
					addNotice("success", $locale['SL_0015']);
					redirect(FUSION_SELF.$aidlink."&amp;link_cat=".$data['link_cat']);
				}
			}
		}

		echo "<div class='m-t-20'>\n";
		echo openform('linkform', 'post', $this->form_action, array('max_tokens' => 1));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-8'>\n";
		echo form_hidden('link_id', '', $this->data['link_id']);
		echo form_text('link_name', $locale['SL_0020'], $this->data['link_name'], array('max_length' => 100,
			'required' => TRUE,
			'error_text' => $locale['SL_0085'],
			'inline' => TRUE));
		echo form_text('link_icon', 'Link Icon', $this->data['link_icon'], array('max_length' => 100,
			'inline' => TRUE));
		echo form_text('link_url', $locale['SL_0021'], $this->data['link_url'], array('required' => TRUE,
			'error_text' => $locale['SL_0086'],
			'inline' => TRUE));
		echo form_text('link_order', $locale['SL_0023'], $this->data['link_order'], array('number' => TRUE,
			'class' => 'pull-left',
			'inline' => TRUE));
		echo form_select('link_position', $locale['SL_0024'], $this->data['link_position'], array('options' => $this->position_opts,
			'inline' => TRUE));
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-4'>\n";
		openside('');
		echo form_select_tree("link_cat", $locale['SL_0029'], $this->data['link_cat'], array('input_id' => 'link_categorys',
											"parent_value" => $locale['parent'],
											'width' => '100%',
											'query' => (multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : ''),
											'disable_opts' => $this->data['link_id'],
											'hide_disabled' => 1), DB_SITE_LINKS, "link_name", "link_id", "link_cat");
		echo form_select('link_language', $locale['global_ML100'], $this->data['link_language'], array('options' => $this->language_opts,
			'placeholder' => $locale['choose'],
			'width' => '100%'));
		echo form_select('link_visibility', $locale['SL_0022'], $this->data['link_visibility'], array('options' => self::getVisibility(),
			'placeholder' => $locale['choose'],
			'width' => '100%'));
		echo form_checkbox('link_window', $locale['SL_0028'], $this->data['link_window']);
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button('savelink', $locale['SL_0040'], $locale['SL_0040'], array('class' => 'btn-primary'));
		echo closeform();
		echo "</div>\n";
	}
}
