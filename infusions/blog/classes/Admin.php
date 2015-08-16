<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/Admin.php
| Author: Frederick Mc Chan (Hien)
| Dev Scope: To merge Categories and Blog into a single interface.
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Blog;
if (!defined("IN_FUSION")) {
	die("Access Denied");
}use PHPFusion\ImageRepo;
use PHPFusion\QuantumFields;

class Admin {
	// do category here
	private $blogCatData = array(
		'blog_cat_id' => 0, 'blog_cat_parent' => 0, 'blog_cat_name' => '', 'blog_cat_image' => '',
		'blog_cat_language' => ''
	);
	private $edit_category = FALSE;
	private $formaction = '';
	private $catIndex = array();

	public function __construct() {
		global $aidlink;
		$_GET['cat_id'] = isset($_GET['cat_id']) && Functions::validate_blogCat($_GET['cat_id']) ? $_GET['cat_id'] : '';
		$_GET['parent_id'] = isset($_GET['parent_id']) && Functions::validate_blogCat($_GET['parent_id']) ? $_GET['parent_id'] : 0;
		$this->catIndex = Functions::get_blogCatsIndex();
		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'edit_cat':
					$this->blogCatData = Functions::get_blogCatData($_GET['cat_id']);
					$this->formaction = INFUSIONS."blog/blog/blog_cats.php".$aidlink."&amp;section=blog-form&amp;action=edit_cat&amp;cat_id=".$_GET['cat_id'];
					break;
				case 'delete_cat':
					self::delete_blogCat();
					break;
				case 'default':
					$this->formaction = ADMIN."blog_cats.php".$aidlink."&amp;section=blog-form";
			}
		}
		self::set_blogcatDB();
	}

	private function delete_blogCat() {
		global $aidlink, $locale;
		if (Functions::validate_blogCat($_GET['cat_id'])) {
			$result = dbquery("DELETE FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".$_GET['cat_id']."'");
			addNotice('warning', $locale['424']);
		} else {
			addNotice('warning', $locale['422']."-<span class='small'>".$locale['423']."</span>");
		}
		redirect(FUSION_SELF.$aidlink);
	}

	private function set_blogcatDB() {
		global $defender, $aidlink, $locale;
		if (isset($_POST['save_cat'])) {
			$this->blogCatData = array(
				'blog_cat_id' => form_sanitizer($_POST['blog_cat_id'], '', 'blog_cat_id'),
				'blog_cat_name' => form_sanitizer($_POST['blog_cat_name'], '', 'blog_cat_name', 1),
				'blog_cat_image' => stripinput($_POST['blog_cat_image']),
				'blog_cat_parent' => form_sanitizer($_POST['blog_cat_parent'], '', 'blog_cat_parent'),
				'blog_cat_language' => form_sanitizer($_POST['blog_cat_language'], '', 'blog_cat_language'),
			);
			$this->blogCatData['blog_cat_language'] = str_replace('|', '.', $this->blogCatData['blog_cat_language']);
			if ($this->blogCatData['blog_cat_name']) {
				self::check_duplicated_names();
				if (Functions::validate_blogCat($this->blogCatData['blog_cat_id'])) {
					dbquery_insert(DB_BLOG_CATS, $this->blogCatData, 'update');
					if (!defined('FUSION_NULL')) {
						addNotice('info', $locale['421']);
						redirect(FUSION_SELF.$aidlink.(isset($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : ''));
					}
				} else {
					dbquery_insert(DB_BLOG_CATS, $this->blogCatData, 'save');
					if (!defined('FUSION_NULL')) {
						addNotice('success', $locale['420']);
						redirect(FUSION_SELF.$aidlink.(isset($_GET['parent_id']) ? "&amp;parent_id=".$_GET['parent_id'] : ''));
					}
				}
			} else {
				$defender->stop();
				addNotice('danger', $locale['461']);
			}
		}
	}

	private function check_duplicated_names() {
		global $defender, $locale;
		$get_blog_name = dbquery("SELECT blog_cat_name FROM ".DB_BLOG_CATS."");
		if (!empty($get_blog_name)) {
			while ($data = dbarray($get_blog_name)) {
				if (QuantumFields::is_serialized($data['blog_cat_name'])) {
					$check_name = unserialize($this->blogCatData['blog_cat_name']);
					$blog_cat_name = unserialize($data['blog_cat_name']);
					/**
					 * English => value.
					 */
					if (in_array($check_name, $blog_cat_name)) {
						$defender->stop();
						$defender->addNotice($locale['461']);
						break;
					}
				} else {
					if ($this->blogCatData['blog_cat_name'] == $data['blog_cat_name']) {
						$defender->stop();
						$defender->addNotice($locale['461']);
						break;
					}
				}
			}
		}
	}

	public function display_blogcat_list() {
		global $locale, $aidlink;
		add_breadcrumb(array('link' => ADMIN.'blog_cats.php'.$aidlink, 'title' => 'Blogs'));
		$index = $this->catIndex;
		self::make_breads($this->catIndex);
		add_to_jquery("
				$('.actionbar').hide();
				$('tr').hover(
					function(e) { $('#bc-'+ $(this).data('id') +'-actions').show(); },
					function(e) { $('#bc-'+ $(this).data('id') +'-actions').hide(); }
				);
			");
		?>
		<table class='table table-responsive table-hover m-t-10'>
			<tr>
				<th>Category Name</th>
				<th>Sub Categories</th>
				<th>Languages Enabled</th>
				<th>Image</th>
				<th>ID</th>
			</tr>
			<?php
			$cat_data = Functions::get_blogCat();

			if (!empty($cat_data[$_GET['parent_id']])) {
				echo "<tbody>";
				$i = 0;
				foreach ($cat_data[$_GET['parent_id']] as $arr => $data) {
					$subcats = get_child($index, $data['blog_cat_id']);
					$subcats = !empty($subcats) ? count($subcats) : 0;
					echo "<tr id='listItem_".$data['blog_cat_id']."' data-id='".$data['blog_cat_id']."' class='list-result'>\n";
					echo "<td class='col-xs-4'>\n";
					echo "<a class='text-dark' href='".FUSION_SELF.$aidlink."&amp;parent_id=".$data['blog_cat_id']."'>".QuantumFields::parse_label($data['blog_cat_name'])."</a>\n";
					echo "<div class='actionbar text-smaller' id='bc-".$data['blog_cat_id']."-actions'>
					<a href='".FUSION_SELF.$aidlink."&amp;section=blog-form&amp;action=edit_cat&amp;cat_id=".$data['blog_cat_id']."'>".$locale['edit']."</a> |
					<a class='delete' href='".FUSION_SELF.$aidlink."&amp;action=delete_cat&amp;cat_id=".$data['blog_cat_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['delete']."</a>
					</div>\n";
					echo "</td>\n";
					echo "<td><span class='badge'>".$subcats."</span></td>";
					echo "<td>".str_replace('.', ', ', $data['blog_cat_language'])."</td>";
					echo "<td class='col-xs-2'>\n";
					echo "<img style='width:50px;' src='".get_image("bl_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' class='img-rounded'/>";
					echo "</td>\n";
					echo "<td>".$data['blog_cat_id']."</td>";
					echo "</tr>";
					$i++;
				}
				echo "</tbody>\n";
			} else {
				echo "<tr><td class='text-center' colspan='6'>".$locale['435']."</td></tr>\n";
			}
			?>
		</table>
	<?php
	}

	public function display_blogcat_form() {
		global $locale;
		echo openform('addcat', 'post', $this->formaction, array('max_tokens' => 1, 'class' => 'm-t-20'));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		if (multilang_table("BL")) {
			echo QuantumFields::quantum_multilocale_fields('blog_cat_name', $locale['430'], $this->blogCatData['blog_cat_name'], array(
				'required' => 1, 'class' => 'm-b-5', 'error_text' => $locale['460']
			));
		} else {
			echo form_text('blog_cat_name', $locale['430'], $this->blogCatData['blog_cat_name'], array(
				'required' => 1, 'error_text' => $locale['460']
			));
		}
		echo "</div><div class='col-xs-12 col-sm-4'>\n";
		openside('');
		$this->blogCatData['blog_cat_parent'] = isset($_GET['parent_id']) ? $_GET['parent_id'] : $this->blogCatData['blog_cat_parent'];
		echo form_select_tree("blog_cat_parent", $locale['437'], $this->blogCatData['blog_cat_parent'], array(
			"disable_opts" => array($this->blogCatData['blog_cat_id']), "hide_disabled" => 1
		), DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
		echo form_select('blog_cat_image', $locale['431'], $this->blogCatData['blog_cat_image'], array("options" => ImageRepo::getFileList(IMAGES_BC)));
		echo form_hidden('blog_cat_id', '', $this->blogCatData['blog_cat_id']);
		closeside();
		echo form_button('save_cat', $locale['432'], $locale['432'], array('class' => 'btn-primary m-b-20'));
		openside('');
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-12'>\n";
		echo "<label>Languages</label>\n";
		echo "</div><div class='col-xs-12 col-sm-12'>\n";
		foreach (fusion_get_enabled_languages() as $lang) {
			if (!$this->blogCatData['blog_cat_language']) {
				$check = 1;
			} else {
				$this->blogCatData['blog_cat_language'] = (is_array($this->blogCatData['blog_cat_language'])) ? $this->blogCatData['blog_cat_language'] : fusion_get_enabled_languages();
				$check = in_array($lang, $this->blogCatData['blog_cat_language']) ? 1 : 0;
			}
			echo form_checkbox("blog_cat_language[]", $lang, $check, array(
				'input_id' => "blog_cat_lang-".$lang, 'inline' => 1, 'class' => 'display-inline', 'width' => "100px",
				'value' => $lang
			));
		}
		echo "</div>\n";
		echo "</div>\n";
		closeside();
		echo "</div>\n";
		echo "</div>\n";
		echo form_button('save_cat', $locale['432'], $locale['432'], array('class' => 'btn-primary'));
	}

	/**
	 * @param $blog_index
	 */
	private function make_breads($index) {
		global $aidlink, $locale;
		function breadcrumb_arrays($index, $id) {
			global $aidlink;
			$crumb = array(
				'link' => array(), 'title' => array()
			);
			if (isset($index[get_parent($index, $id)])) {
				$_name = dbarray(dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($id)."'"));
				$crumb = array(
					'link' => array(FUSION_SELF.$aidlink."&amp;parent_id=".$_name['blog_cat_id']),
					'title' => array(QuantumFields::parse_label($_name['blog_cat_name']))
				);
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

		$crumb = breadcrumb_arrays($index, $_GET['parent_id']);
		for ($i = count($crumb['title'])-1; $i >= 0; $i--) {
			add_breadcrumb(array('link' => $crumb['link'][$i], 'title' => $crumb['title'][$i]));
		}
	}
}
