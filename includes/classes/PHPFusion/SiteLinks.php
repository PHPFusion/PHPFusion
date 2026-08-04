<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SiteLinks.php
| Author: Frederick MC Chan (Chan)
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

use PHPFusion\Rewrite\Router;

/**
 * Class SiteLinks
 * Navigational Bar
 *
 * @package PHPFusion
 */
class SiteLinks
{

	/**
	 * @param string $sep
	 * @param string $class
	 * @param array $options
	 *
	 * @return static
	 *
	 * A blank static is set up once for each available $options['id']
	 * If same instance exists, options can be mutated to alter the behavior of the menu
	 *
	 * Simple Usage: SiteLinks::setSublinks($sep, $class, $options)->showSubLinks();
	 *
	 * So in order to add a cart icon, we must declare at theme.
	 *
	 */
	const MENU_DEFAULT_ID = 'DefaultMenu';

	protected static $position_opts = [];

	private static $id = '';

	private static $instances = [];

	private static $primary_cache_data = [];

	private static $optional_cache_data = [];

	public $menu_options = [];

	/**
	 * Get Site Links Position Options
	 *
	 * @return array
	 */
	public static function getSiteLinksPosition($key = NULL)
	{

		if (empty(self::$position_opts)) {
			$res = dbquery("SELECT menu_id, menu_name FROM " . DB_SITE_MENUS . " ORDER BY menu_id");
			if (dbrows($res)) {
				while ($rows = dbarray($res)) {
					self::$position_opts[$rows['menu_id']] = $rows['menu_name'];
				}
			}
		}

		//			if (empty(self::$position_opts)) {
		//				self::$position_opts = [
		//					'1' => $locale['SL_0025'], // only css navigational panel
		//					'2' => $locale['SL_0026'], // both
		//					'3' => $locale['SL_0027'], // subheader
		//					'4' => $locale['custom'] . " ID",
		//				];
		//			}
		return $key === NULL ? self::$position_opts : (self::$position_opts[$key] ?? NULL);

	}

	/**
	 * Determine current site links icon type
	 *
	 * @param null $key
	 *
	 * @return mixed|string|string[]|null
	 */
	public static function getIconType($key = NULL)
	{

		static $icon_type;
		if (empty($icon_type)) {
			$icon_type = [
				'glyph' => 'Glyphicon',
				'svg'   => 'SVG',
			];
		}

		return $key === NULL ? $icon_type : ($icon_type[$key] ?? NULL);
	}

	/**
	 * Get Sitelinks SQL Row
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getSiteLinks($id)
	{

		$data = [];
		$link_query = "SELECT * FROM " . DB_SITE_LINKS . " " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id='$id'";
		$result = dbquery($link_query);
		if (dbrows($result) > 0) {
			$data = dbarray($result);
		}

		return $data;
	}

	/**
	 * Given a matching URL, fetch Sitelinks data
	 *
	 * @param string $url url to match (link_url) column
	 * @param string $key column data to output, blank for all
	 *
	 * @return array|bool
	 */
	public static function getCurrentSiteLinks($url = "", $key = NULL)
	{

		$url = stripinput($url);
		static $data = [];
		if (empty($data)) {
			if (!$url) {
				$url = FUSION_FILELINK;
			}
			$result = dbquery("SELECT * FROM " . DB_SITE_LINKS . " WHERE link_url='" . $url . "' AND link_language='" . LANGUAGE . "'");
			if (dbrows($result) > 0) {
				$data = dbarray($result);
			}
		}

		return $key === NULL ? $data : (isset($data[$key]) ? $data[$key] : NULL);
	}

	/**
	 * Link ID validation
	 *
	 * @param int $link_id
	 *
	 * @return int|null
	 */
	public static function verifySiteLink($link_id)
	{

		if (isnum($link_id)) {
			return dbcount("(link_id)", DB_SITE_LINKS, "link_id='" . intval($link_id) . "'");
		}

		return NULL;
	}

	/**
	 * SQL Delete Site Link Action
	 *
	 * @param int $link_id
	 *
	 * @return bool|mixed|null|resource
	 */
	public static function deleteSiteLink($link_id)
	{

		if (isnum($link_id)) {
			$data = dbarray(dbquery("SELECT link_order FROM " . DB_SITE_LINKS . " " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_id='" . $_GET['link_id'] . "'"));
			$result = dbquery("UPDATE " . DB_SITE_LINKS . " SET link_order=link_order-1 " . (multilang_table("SL") ? "WHERE link_language='" . LANGUAGE . "' AND" : "WHERE") . " link_order>'" . $data['link_order'] . "'");
			if ($result) {
				$result = dbquery("DELETE FROM " . DB_SITE_LINKS . " WHERE link_id='" . $_GET['link_id'] . "'");
			}

			return $result;
		}

		return NULL;
	}

	/**
	 * Get Group Array
	 *
	 * @return array
	 */
	public static function getLinkVisibility()
	{

		static $visibility_opts = [];
		$user_groups = getusergroups();
		foreach ($user_groups as $user_group) {
			$visibility_opts[$user_group['0']] = $user_group['1'];
		}

		return $visibility_opts;
	}

	/**
	 * Calling the SiteLinks instance with Custom Parameters
	 *
	 * @param array $options
	 *
	 * @return static
	 */
	public static function setSubLinks(array $options = [])
	{

		/*
		 * If set an ID, it will re-run the class to create a new object again.
		 */
		$options += [
			'id'                   => self::MENU_DEFAULT_ID,
			'container'            => FALSE,
			'container_fluid'      => FALSE,
			'responsive'           => TRUE,
			'navbar_class'         => defined('BOOTSTRAP4') ? 'navbar-expand-lg navbar-light' : 'navbar-default',
			'nav_class'            => defined('BOOTSTRAP4') ? 'navbar-nav ml-auto primary' : '',
			'additional_nav_class' => '',
			'item_class'           => defined('BOOTSTRAP4') ? 'nav-item' : '', // $class
			'locale'               => [],
			'separator'            => '', // $sep
			'links_per_page'       => '',
			'grouping'             => '',
			'show_banner'          => FALSE,
			'banner'               => '',
			'show_header'          => FALSE,
			'custom_header'        => '',
			'language_switcher'    => FALSE,
			'searchbar'            => FALSE,
			'search_icon'          => 'fa fa-search',
			'searchbar_btn_class'  => 'btn-primary',
			'caret_icon'           => defined('BOOTSTRAP4') ? '' : 'caret',
			'link_position'        => [2, 3],
			'html_pre_content'     => '',
			'html_content'         => '',
			'html_post_content'    => '',
		];

		if (!isset(self::$instances[$options['id']]->menu_options)) {

//			$options['locale'] += fusion_get_locale();

			if (!$options['links_per_page']) {
				$options['links_per_page'] = fusion_get_settings('links_per_page');
			}

			if (empty($options['grouping'])) {
				$options['grouping'] = fusion_get_settings('links_grouping');
			}

			if (!isset($options['callback_data']) && empty($options['callback_data'])) {
				$options['callback_data'] = self::getSiteLinksData(['link_position' => ['link_position']]);
			}

			$options['banner'] = fusion_get_settings('sitebanner') && $options['show_banner'] == TRUE ? "<img src='" . BASEDIR . fusion_get_settings("sitebanner") . "' alt='" . fusion_get_settings("sitename") . "'/>" : fusion_get_settings("sitename");

			$pageInfo = pathinfo($_SERVER['REQUEST_URI']);
			$start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/") . "/" : "";
			$site_path = ltrim(fusion_get_settings("site_path"), "/");
			$start_page = str_replace([$site_path, '\/'], ['', ''], $start_page);
			$start_page .= $pageInfo['basename'];

			if (fusion_get_settings("site_seo") && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
				$filepath = Router::getRouterInstance()->getFilePath();
				$start_page = $filepath;
			}

			$options['start_page'] = $start_page;

			self::$instances[$options['id']] = self::getInstance($options['id']);

			self::$id = $options['id'];

			self::$instances[$options['id']]->menu_options = $options;
		}

		return self::$instances[$options['id']];
	}

	/**
	 * Fetches Site Links Hierarchy Data - for a less support complexity
	 *
	 * @param array $options
	 * - join
	 * - link_position (array)
	 * - condition
	 * - group
	 * - order
	 *
	 * @return array
	 */
	public static function getSiteLinksData(array $options = [])
	{

		$default_position = [1, 2];

		/*
		 * $options['link_position'] - accepts either string (e.g., '1,2,3') or array
		 */
		$link_position = '';
		if (!empty($options['link_position'])) {
			$link_position = $options['link_position'];

			// If it's an array, convert it to a comma-separated string for FIND_IN_SET
			if (is_array($link_position)) {
				$link_position = implode(',', $link_position);
			}

			// Sanitize the string to ensure only numbers and commas remain (important for security)
			// Assuming $link_position is safe to use as is, but sanitization is recommended
			$link_position = preg_replace('/[^0-9,]/', '', $link_position);
		}

		// --- Determine the POSITION Condition ---

		$position_condition_sql = '';

		if (!empty($link_position)) {
			// Use FIND_IN_SET for string input (e.g., '1,2,3')
			// NOTE: This assumes link_position in the DB is a single number, not a list itself.
			$position_condition_sql = "FIND_IN_SET(sl.link_position, '{$link_position}')";
		} else {
			// Use the default position array with standard OR logic
			$position_condition_sql = 'sl.link_position=' . implode(' OR sl.link_position=', $default_position);
		}

		$default_link_filter = [
			'join'               => '',
			// Use the dynamically generated condition
			'position_condition' => "({$position_condition_sql})",
			'condition'          => (multilang_table("SL") ? " AND link_language='" . LANGUAGE . "'" : "") . " AND " . groupaccess('link_visibility') . " AND link_status=1",
			'group'              => '',
			'order'              => "link_cat ASC, link_order ASC",
		];

		// Merge the default filters with any user-provided options
		$options += $default_link_filter;

		$query_replace = "";

		if (!empty($options)) {
			$query_replace = "SELECT
            sl.* " . (!empty($options['select']) ? ", " . $options['select'] : '') . "
            FROM
               " . DB_SITE_LINKS . " sl
            {$options['join']}
            WHERE {$options['position_condition']} {$options['condition']}
            " . (!empty($options['group']) ? " GROUP BY " . $options['group'] . " " : "") . "
            ORDER BY {$options['order']}";
		}

		return dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "", $query_replace);
	}

	/**
	 * @param string $id
	 *
	 * @return static
	 */
	public static function getInstance($id = self::MENU_DEFAULT_ID)
	{

		self::$id = $id;
		if (isset(self::$instances[$id])) {
			// $c = \debug_backtrace();

			return self::$instances[$id];
		} else {
			return self::$instances[$id] = new static();
		}
	}

	/**
	 * Add a link to primary menu
	 *
	 * @param int $link_id
	 * @param string $link_name
	 * @param int $link_cat
	 * @param string $link_url
	 * @param string $link_icon
	 * @param bool|FALSE $link_active
	 * @param bool|FALSE $link_title
	 * @param bool|FALSE $link_disabled
	 * @param bool|FALSE $link_window
	 * @param string $link_class
	 */
	public static function addMenuLink($link_id, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '')
	{

		self::$primary_cache_data[self::$id][$link_cat][$link_id] = [
			'link_id'       => $link_id,
			'link_name'     => $link_name,
			'link_cat'      => $link_cat,
			'link_url'      => $link_url,
			'link_icon'     => $link_icon,
			'link_active'   => $link_active,
			'link_title'    => $link_title,
			'link_disabled' => $link_disabled,
			'link_window'   => $link_window,
			'link_class'    => $link_class,
		];
	}

	/**
	 * Add a link to secondary menu
	 *
	 * @param int $link_id
	 * @param string $link_name
	 * @param int $link_cat
	 * @param string $link_url
	 * @param string $link_icon
	 * @param bool|FALSE $link_active
	 * @param bool|FALSE $link_title
	 * @param bool|FALSE $link_disabled
	 * @param bool|FALSE $link_window
	 * @param string $link_class
	 */
	public static function addOptionalMenuLink($link_id, $link_name, $link_cat = 0, $link_url = '', $link_icon = '', $link_active = FALSE, $link_title = FALSE, $link_disabled = FALSE, $link_window = FALSE, $link_class = '')
	{

		self::$optional_cache_data[self::$id][$link_cat][$link_id] = [
			'link_id'       => $link_id,
			'link_name'     => $link_name,
			'link_cat'      => $link_cat,
			'link_url'      => $link_url,
			'link_icon'     => $link_icon,
			'link_active'   => $link_active,
			'link_title'    => $link_title,
			'link_disabled' => $link_disabled,
			'link_window'   => $link_window,
			'link_class'    => $link_class,
		];
	}

	/**
	 * Init
	 */
	private static function setLinks()
	{

		$primary_cache = (isset(self::$primary_cache_data[self::$id])) ? self::$primary_cache_data[self::$id] : [];

		$secondary_cache = (isset(self::$optional_cache_data[self::$id])) ? self::$optional_cache_data[self::$id] : [];
		if (!empty(self::getMenuParam('callback_data')) && is_array(self::getMenuParam('callback_data'))) {
			if (isset($primary_cache)) {

				self::replaceMenuParam('callback_data', array_replace_recursive((array)self::getMenuParam('callback_data'), $primary_cache));
			}
		} else {
			self::replaceMenuParam('callback_data', $primary_cache);
		}

		if (!empty(self::getMenuParam('additional_data') && is_array(self::getMenuParam('additional_data')))) {
			if (isset($secondary_cache)) {
				self::replaceMenuParam('additional_data', array_replace_recursive((array)self::getMenuParam('additional_data'), $secondary_cache));
			}
		} else {
			self::replaceMenuParam('additional_data', $secondary_cache);
		}

		// Change hierarchy data when grouping is activated
		if (self::getMenuParam('grouping')) {

			$callback_data = (array)self::getMenuParam('callback_data');

			if (!empty($callback_data[0])) {

				if (count($callback_data[0]) > self::getMenuParam('links_per_page')) {

					$more_index = 9 * 10000000;
					$base_data = $callback_data[0];
					$data[$more_index] = array_slice($base_data, self::getMenuParam('links_per_page'), 9, TRUE);

					$data[0] = array_slice($base_data, 0, self::getMenuParam('links_per_page'), TRUE);

					$more[$more_index] = [
						"link_id"         => $more_index,
						"link_cat"        => 0,
						"link_name"       => fusion_get_locale('global_700'),
						"link_url"        => "#",
						"link_icon"       => "",
						"link_visibility" => 0,
						"link_position"   => 2,
						"link_window"     => 0,
						"link_order"      => self::getMenuParam('links_per_page'),
						"link_language"   => LANGUAGE,
						"link_drop_style" => "default",
					];

					$data[0] = $data[0] + $more;

					$data = $data + $callback_data;

					self::replaceMenuParam('callback_data', $data);
				}
			}
		}
	}

	/**
	 * Render navigation for Tabler Framework
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function showSubLinks($id = 0)
	{

		$locale = (array)self::getMenuParam('locale');
		$res = '';

		if (empty($id)) {

			self::setLinks();
			$navClass = self::getMenuParam('nav_class') ?: "navbar-nav";
			$callback_data = self::getMenuParam('callback_data');

			if (function_exists('fusion_render_framework_component') && fusion_framework_active()) {
				$framework_nav = fusion_render_framework_component('showsublinks', [
					'id'                     => self::getMenuParam('id'),
					'container'              => self::getMenuParam('container'),
					'container_fluid'        => self::getMenuParam('container_fluid'),
					'responsive'             => self::getMenuParam('responsive'),
					'navbar_class'           => self::getMenuParam('navbar_class'),
					'nav_class'              => self::getMenuParam('nav_class'),
					'additional_nav_class'   => self::getMenuParam('additional_nav_class'),
					'show_header'            => self::getMenuParam('show_header'),
					'show_banner'            => self::getMenuParam('show_banner'),
					'banner'                 => self::getMenuParam('banner'),
					'custom_header'          => self::getMenuParam('custom_header'),
					'language_switcher'      => self::getMenuParam('language_switcher'),
					'searchbar'              => self::getMenuParam('searchbar'),
					'primary_callback_nav'   => $callback_data,
					'secondary_callback_nav' => (array)self::getMenuParam('additional_data'),
					'html_pre_content'       => self::getMenuParam('html_pre_content'),
					'html_content'           => self::getMenuParam('html_content'),
					'html_post_content'      => self::getMenuParam('html_post_content'),
				]);

				if ($framework_nav !== '') {
					return $framework_nav;
				}
			}

			// Outer container for Tabler navbar
			$res .= "<header id='" . self::getMenuParam('id') . "' class='navbar navbar-expand-md " . self::getMenuParam('navbar_class') . "'>";

			// Container or fluid
			if (self::getMenuParam('container')) {
				$res .= "<div class='container-xl'>";
			} else if (self::getMenuParam('container_fluid')) {
				$res .= "<div class='container-fluid'>";
			}

			// Brand + mobile toggle
			if (self::getMenuParam('show_header')) {
				$res .= "<!-- Navbar Header Start -->";

				// Tabler hamburger button
				if (self::getMenuParam('responsive')) {
					$res .= "
					<button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#" . self::getMenuParam('id') . "_menu'>
						<span class='navbar-toggler-icon'></span>
                    </button>";
				}

				// Branding
				if (self::getMenuParam('show_banner') === TRUE) {
					$res .= "<a class='navbar-brand' href='" . BASEDIR . fusion_get_settings('opening_page') . "'>
                    " . self::getMenuParam('banner') . "</a>";
				} elseif ($custom_banner = self::getMenuParam('custom_banner')) {
					$res .= $custom_banner;
				} else {
					$res .= "<a class='navbar-brand' href='" . BASEDIR . fusion_get_settings('opening_page') . "'>
					". fusion_get_settings("sitename") . "
					</a>";
				}
				$res .= "<!-- Navbar Header End -->";
			}

			$res .= self::getMenuParam('custom_header');

			// Responsive container
			$res .= "<div class='collapse navbar-collapse' id='" . self::getMenuParam('id') . "_menu'>";

			// Nav class
			$res .= "<ul class='{$navClass} me-auto'>";

			// Primary links
			$res .= "<!-- Menu Item Start -->";
			$res .= $this->showMenuLinks($id, $callback_data);
			$res .= "<!-- Menu Item End -->";
			$res .= "</ul>";

			// --- RIGHT SIDE: language switcher / search / additional menu ---
			if (self::getMenuParam('language_switcher') || self::getMenuParam('searchbar') || !empty(self::getMenuParam('additional_data'))) {
				$rightClass = self::getMenuParam('additional_nav_class') ?: "navbar-nav ms-auto";
				$res .= "<ul class='$rightClass'>";
				// Additional links
				$res .= $this->showMenuLinks($id, (array)self::getMenuParam('additional_data'));

				// --- Language Switcher ---
				if (self::getMenuParam('language_switcher')) {
					$langs = fusion_get_enabled_languages();

					if (count($langs) > 1) {
						$switch = fusion_get_language_switch();
						$current = $switch[LANGUAGE];

						$res .= "
                        <li class='nav-item dropdown'>
                        <a class='nav-link dropdown-toggle' data-bs-toggle='dropdown'>
                            <img src='{$current['language_icon_s']}' class='me-2' />
                            " . translate_lang_names(LANGUAGE) . "
                        </a>
                        <div class='dropdown-menu dropdown-menu-end'>";
						foreach ($switch as $lang) {
							$res .= "
                            <a class='dropdown-item' href='{$lang['language_link']}'>
                                <img src='{$lang['language_icon_s']}' class='me-2'>
                                {$lang['language_name']}
                            </a>";
						}
						$res .= "</div></li>";
					}
				}

				// --- Searchbar ---
				if (self::getMenuParam('searchbar')) {
					$res .= "
                            <li class='nav-item dropdown'>
                            <a class='nav-link' data-bs-toggle='dropdown'>
                                <i class='" . self::getMenuParam('search_icon') . "'></i>
                            </a>
                            <div class='dropdown-menu dropdown-menu-end p-3' style='min-width:250px'>
                                " . openform('searchform', 'post', FUSION_ROOT . BASEDIR . 'search.php?stype=all') . "
                                " . form_text('stext') . "
                                " . closeform() . "
                            </div>
                        </li>";
				}

				$res .= "</ul>";
			}

			$res .= self::getMenuParam('html_post_content');

			// Close containers
			$res .= "</div>"; // collapse
			if (self::getMenuParam('container') || self::getMenuParam('container_fluid')) {
				$res .= "</div>";
			}

			$res .= "</header>";
		}

		return $res;
	}

	public function showFooterLinks($id = 0)
	{

		$res = '';

		if (empty($id)) {

			self::setLinks();
			$settings = fusion_get_settings();
			$callback_data = self::getMenuParam('callback_data');


			// Get the root links (links with link_cat = 0).
			// This is now accessed directly via $callback_data[0]
			$root_links = $callback_data[0] ?? [];

			// Start the main outer container matching the target HTML

			$res .= '<div class="footer-divider"></div>';
			$res .= '<div class="footer-upper-layout">';

			// --- Special Column 1: Logo and Main Links ---

			// Since your debug data only contains the 'Search' link at the root,
			// we must hardcode the Logo column based on the original template,
			// and assume the main links (Sign Up, Pricing) are *not* dynamically generated
			// if they aren't in the $callback_data.

			// If the intent is to output the logo column *always* regardless of dynamic links:
			$res .= '<div class="footer-upper-group">';
			$res .= '<div class="footer-subgroup">';
			// Logo/Branding (Using the assumed 'banner' for the full SVG block)
			$res .= self::getMenuParam('banner');

			$res .= "<ul class='navbar-nav mt-4'>";
			$res .= "<li class='nav-item'><a class='nav-link nav-link-sp' href='" . BASEDIR . "register.php'>Sign Up</a></li>";
			$res .= "<li class='nav-item'><a class='nav-link nav-link-sp' href='" . BASEDIR . $settings['opening_page'] . "'>Pricing</a></li>";
			$res .= "<li class='nav-item'><a class='nav-link nav-link-sp' href='" . BASEDIR . $settings['opening_page'] . "'>Support</a></li>";
			$res .= "<li class='nav-item'><a class='nav-link nav-link-sp' href='" . BASEDIR . $settings['opening_page'] . "'>Contact Us</a></li>";
			$res .= "</ul>";
			$res .= '</div>';


			// The links like 'Sign Up', 'Pricing' must be either hardcoded here,
			// or come from a specific link ID. Since we have no dynamic data for them,
			// we'll output an empty list placeholder or hardcode if needed.
			// We will *skip* calling showMenuLinks for the 'main' context as there's no dynamic parent for it.

			$res .= '</div>'; // close footer-upper-group (Logo/Main Links)

			// --- Subsequent Columns: PRODUCTS, SOLUTIONS, etc. (Iterate through all root links) ---

			// Iterate over ALL root-level links to create the dynamic column structure
			foreach ($root_links as $column_data) {

				$column_id = $column_data['link_id'];
				$column_name = htmlspecialchars(strtoupper($column_data['link_name']));
				$context = strtolower($column_data['link_name']);

				// The column itself is generated even if it has no children, but the content will be empty

				$res .= "<div class='footer-upper-group'>";
				$res .= "<div class='h3 footer-group-heading'>{$column_name}</div>";

				// Apply wrappers based on context (e.g., Solutions)
				// $start_wrapper = '';
				// $end_wrapper = '';
				// if ( $column_name === 'SOLUTIONS' ) {
				//     $start_wrapper = '<div class="mb-60 t-mb-0">';
				//     $end_wrapper = '</div>';
				// }
				//
				// $res .= $start_wrapper;

				// Render the children (the links below the heading) if they exist
				if (isset($callback_data[$column_id])) {
					$res .= $this->showFooterMenuLinks($column_id, $callback_data, FALSE, $context);
				}

				// $res .= $end_wrapper;

				$res .= '</div>'; // close footer-upper-group
			}

			// Close the main layout containers
			$res .= '</div>'; // close footer-upper-layout

		}

		return $res;
	}

	private function showFooterMenuLinks($id, $data, $skip_list = FALSE, $context = 'default')
	{

		$res = '';

		if (!empty($data[$id])) {

			$default_link_data = [
				// ... (default_link_data remains the same)
				"link_id"          => 0,
				"link_name"        => "",
				"link_description" => "",
				"link_cat"         => 0,
				"link_url"         => "",
				"link_icon"        => "",
				"link_class"       => '',
				"link_active"      => FALSE,
				"link_title"       => FALSE,
				"link_disabled"    => FALSE,
				"link_window"      => FALSE,
				"link_drop_style"  => 'default',
			];

			$list_wrapper_class = 'navbar-nav footer-link-list w-list-unstyled';

			// Determine if we are rendering the column headings for the Education Centres (Column 2)
			$res .= '<ul role="list" class="' . $list_wrapper_class . '">';

			foreach ($data[$id] as $link_data) {

				$link_data += $default_link_data;

				$itemlink = '';
				if (!empty($link_data['link_url']) && $link_data['link_url'] != '#') {
					$itemlink = preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])
						? " href='{$link_data['link_url']}'"
						: " href='" . BASEDIR . $link_data['link_url'] . "'";
					$itemlink = str_replace('%aidlink%', fusion_get_aidlink(), $itemlink);
				}

				$has_child = isset($data[$link_data['link_id']]);

				$li_class = 'navbar-item footer-link-list-item';

				$link_class = 'footer-link nav-link';

				// Apply 'is-main' class if the link is from the hardcoded first column (not needed with current data structure)
				// if ($context === 'main') {
				// 	$link_class = 'footer-link nav-link is-main';
				// }

				// --- EDUCATION CENTRES Subgroup Logic (Maps, Search, Navigation headings) ---
				if ($has_child) {

					// This is a sub-group heading (Tuition Centres, Schools, Talents)
					$res .= '<div class="footer-subgroup">';
					$res .= '<div class="nav-item dropdown-header mb-3">' . htmlspecialchars($link_data['link_name']) . '</div>';

					// Recursively render children (the actual links)
					$res .= $this->showMenuLinks($link_data['link_id'], $data);

					$res .= '</div>'; // close footer_subgroup

				} else {
					// --- Standard List Item Logic (Used in Contact, Company, and links inside Education sub-groups) ---

					// Start LI
					$res .= "<li class='{$li_class}'>";

					// Check for the special 'Careers' link with the tag (Link ID 10)

					// Standard Link A tag
					$res .= "<a {$itemlink} class='{$link_class}'" .
						($link_data['link_window'] ? " target='_blank'" : "") .
						">";
					$res .= htmlspecialchars($link_data['link_name']);
					$res .= "</a>";

					// End LI
					$res .= "</li>";
				}
			}

			// Close the outer list wrapper for contexts that opened it
			$res .= "</ul>";
		}

		return $res;
	}


	/**
	 * @param mixed $key
	 *
	 * @return string
	 */
	public static function getMenuParam($key = FALSE)
	{

		if ($key) {

			return !empty(self::$instances[self::$id]->menu_options[$key]) ? self::$instances[self::$id]->menu_options[$key] : '';
		}

		return self::$instances[self::$id]->menu_options;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public static function replaceMenuParam($key, $value)
	{

		self::$instances[self::$id]->menu_options[$key] = $value;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public static function setMenuParam($key, $value)
	{

		self::$instances[self::$id]->menu_options[$key] = (is_bool($value)) ? $value : self::getMenuParam($key) . $value;
	}

	/**
	 * @param array $data
	 * @param int $link_id
	 *
	 * @return array
	 */
	private function getSubLinksUrl($data, $link_id)
	{

		$linkRef = [];
		if (isset($data[$link_id])) {
			foreach ($data[$link_id] as $link) {
				$linkRef[$link['link_id']] = $link['link_url'];
				if (isset($data[$link['link_id']])) {
					$linkRef = array_merge_recursive($linkRef, $this->getSubLinksUrl($data, $link['link_id']));
				}
			}
		}

		return $linkRef;
	}

	private static $link_instances = [];

	/**
	 * @return array
	 */
	private function getLinkInstance()
	{

		if (empty(self::$link_instances)) {
			$linkInstance = BreadCrumbs::getInstance();
			$linkInstance->showHome(FALSE);
			$linkInstance->setLastClickable();
			self::$link_instances = $linkInstance->toArray();
		}

		return self::$link_instances;
	}

	/**
	 * Recursively generates HTML menu items with optional dropdowns.
	 *
	 * This function loops through the menu data and outputs <li> elements,
	 * handling active states, disabled links, headers, icons, and nested dropdowns.
	 * It supports multiple dropdown styles compatible with Tabler UI, including mega menu.
	 *
	 * @param int $id Parent menu ID to render from.
	 * @param array $data Array of menu items, keyed by parent ID.
	 * @param string $linkclass CSS class for top-level <a> links (default: 'nav-link').
	 * @param bool $dropdown Internal flag to indicate a dropdown menu (default: FALSE).
	 * @param string $dropdown_style Tabler dropdown style for child <ul> (default: 'default').
	 *                                Supported styles:
	 *                                - 'default' : standard dropdown menu.
	 *                                - 'arrow'   : dropdown with arrow indicator.
	 *                                - 'card'    : dropdown appears as a card.
	 *                                - 'dark'    : dark-themed dropdown menu.
	 *                                - 'mega'    : wide mega menu (full-width or multi-column, CSS required).
	 *
	 * @return string HTML string of <li> elements with optional nested <ul> for children.
	 *
	 * @notes
	 * - Items with 'link_name' set to "---" or "===" render as a divider.
	 * - Items without 'link_url' are rendered as 'no-link'.
	 * - Items with 'link_title' set to TRUE are rendered as dropdown headers.
	 * - Active states are calculated automatically based on current URL, start page, or breadcrumb references.
	 * - Parent items of active children will also receive 'current-link active' classes.
	 * - The function is recursive and supports any level of submenu nesting.
	 * - The $dropdown_style is passed down recursively to all child menus.
	 */
	private function keepforActiveLogic($id, $data, $linkclass = 'nav-link', $dropdown = FALSE, $dropdown_style = 'default')
	{

		$res = '';

		// Tabler dropdown style mapping
		$style_class = match ($dropdown_style) {
			'arrow' => 'dropdown-menu dropdown-menu-arrow',
			'card' => 'dropdown-menu dropdown-menu-card',
			'dark' => 'dropdown-menu dropdown-menu-dark',
			'mega' => 'dropdown-menu dropdown-menu-mega',
			default => 'dropdown-menu'
		};

		if (!empty($data[$id])) {
			$i = 0;

			$default_link_data = [
				"link_id"         => 0,
				"link_name"       => "",
				"link_cat"        => 0,
				"link_url"        => "",
				"link_icon"       => "",
				"link_class"      => $linkclass,
				"link_active"     => '',
				"link_title"      => FALSE,
				"link_disabled"   => FALSE,
				"link_window"     => FALSE,
				"link_drop_style" => 'default',
			];

			foreach ($data[$id] as $link_id => $link_data) {
				$li_class = [];
				$link_data += $default_link_data;

				if (!empty(self::getMenuParam('item_class')) && !$dropdown) {
					$li_class[] = self::getMenuParam('item_class');
				}

				if (empty($link_data['link_url'])) {
					$li_class[] = "no-link";
				}

				if ($link_data['link_disabled']) {
					$li_class[] = "disabled";
				} else {
					if ($link_data['link_title'] == TRUE) {
						$li_class[] = "dropdown-header";
					}
				}

				// Calculate active state
				$secondary_active = FALSE;
				if (!is_bool($link_data['link_active'])) {
					if (!stristr($link_data['link_url'], "?")) {
						if (defined('IN_PERMALINK')) {
							if (Router::getRouterInstance()->getFilePath() == $link_data['link_url']) {
								$secondary_active = TRUE;
							}
						} else {
							$data_link_url = $link_data['link_url'];
							if (stristr($link_data['link_url'], "index.php")) {
								$data_link_url = str_replace("index.php", "", $data_link_url);
							}
							$request_uri = str_replace('//', '/', $_SERVER['REQUEST_URI']);
							$url = parse_url(htmlspecialchars_decode($request_uri));
							$url['path'] = !empty($url['path']) ? $url['path'] : '';
							$current_url = str_replace(fusion_get_settings('site_path'), "", $url['path']);
							if (stristr($url['path'], "index.php")) {
								$current_url = str_replace("index.php", "", $current_url);
							}
							if ($data_link_url == $current_url) {
								$secondary_active = TRUE;
							}
						}
					}

					if (self::getMenuParam('start_page') !== $link_data['link_url']) {
						$linkRef = $this->getSubLinksUrl($data, $link_data['link_id']);

						$linkRefURI = !empty($linkRef) ? array_flip($linkRef) : [];

						$reference = $this->getLinkInstance();
						if (!empty($reference)) {
							$uri = parse_url(htmlspecialchars_decode($link_data['link_url']));

							$uriQuery = [];
							if (!empty($uri['query'])) {
								parse_str($uri['query'], $uriQuery);
							}

							foreach ($reference as $refData) {

								if (stristr($refData['link'], '../')) {
									$refData['link'] = str_replace(str_repeat('../', substr_count($refData['link'], '../')), '', $refData['link']);
								}

								if (!empty($refData['link']) && $link_data['link_url'] !== "index.php") {

									if (!empty($refData['link']) && isset($linkRefURI[$refData['link']])) {
										$secondary_active = TRUE;
										break;
									}

									if (!empty($link_data['link_url']) && stristr($refData['link'], $link_data['link_url'])) {
										$secondary_active = TRUE;
										break;
									}

									if (!empty($link_data['link_url']) && stristr($link_data['link_url'], '?')) {
										$ref_uri = parse_url(htmlspecialchars_decode($refData['link']));
										if (!empty($uri['query']) && !empty($ref_uri['query'])) {
											parse_str($ref_uri['query'], $ref_uriQuery);
											if (count($ref_uriQuery) == count($uriQuery) && array_diff_assoc($uriQuery, $ref_uriQuery) == array_diff_assoc($ref_uriQuery, $uriQuery)) {
												$secondary_active = TRUE;
												break;
											}
										}
									}
								}
								if ($secondary_active) {
									break;
								}
							}
						}
					}
				}

				if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {
					$link_data['link_name'] = fusion_get_settings('link_bbcode') ? parseubb($link_data['link_name']) : $link_data['link_name'];
					$link_data["link_name"] = html_entity_decode($link_data["link_name"], ENT_QUOTES);

					$link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : '');
					$link_is_active = $link_data['link_active'] || $secondary_active ||
						strtr(FUSION_REQUEST, [fusion_get_settings('site_path') => '', '&amp;' => '&']) == str_replace('../', '', $link_data['link_url']) ||
						self::getMenuParam('start_page') == $link_data['link_url'] ||
						fusion_get_settings('site_path') . self::getMenuParam('start_page') == $link_data['link_url'] ||
						(self::getMenuParam('start_page') == fusion_get_settings("opening_page") && $i == 0 && $id === 0);

					if ($link_is_active && $link_data['link_url'] !== '#') {
						$li_class[] = "current-link active";
					}

					$itemlink = !empty($link_data['link_url'] && $link_data['link_url'] !== '#') ? " href='" . BASEDIR . $link_data['link_url'] . "'" : '';
					if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url']) || (BASEDIR !== '' && stristr($link_data['link_url'], BASEDIR))) {
						$itemlink = " href='" . $link_data['link_url'] . "' ";
					}
					$itemlink = str_replace('%aidlink%', fusion_get_aidlink(), $itemlink);

					$has_child = isset($data[$link_id]);
					$l_1 = $l_2 = "";

					if ($has_child) {
						$link_class = " class='" . $link_data['link_class'] . " dropdown-toggle'";
						$l_1 = " id='ddlink" . $link_data['link_id'] . "' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false' role='presentation'";
						$l_1 .= (empty($id) ? " data-submenu " : "");
						$l_2 = (empty($id) ? "<i class='" . self::getMenuParam('caret_icon') . "'></i>" : "");
						$li_class[] = (!empty($id) ? "dropdown-submenu" : "dropdown");
					} else {
						$link_class = (!empty($link_data['link_class']) ? " class='" . $link_data['link_class'] . "'" : '');
					}

					$li_class = array_filter($li_class);

					$res .= "<li" . (!empty($li_class) ? " class='" . implode(" ", $li_class) . "'" : '') . " role='presentation'>" . self::getMenuParam('seperator');
					$res .= ($itemlink ? "<a" . $l_1 . $itemlink . $link_target . $link_class . " role='menuitem'>" : '');
					$res .= (!empty($link_data['link_icon']) ? "<i class='" . $link_data['link_icon'] . " m-r-5'></i>" : '');
					$res .= $link_data['link_name'] . " " . $l_2;
					$res .= ($itemlink ? "</a>" : '');

					if ($has_child) {

						$res .= "<ul id='menu-" . $link_data['link_id'] . "' aria-labelledby='ddlink" . $link_data['link_id'] . "' class='" . $style_class . "'>";
						if (!empty($link_data['link_url']) && $link_data['link_url'] !== "#") {
							$res .= "<li" . (!$itemlink ? " class='no-link'" : '') . " role='presentation'>" . self::getMenuParam('seperator');
							$res .= ($itemlink ? "<a " . $itemlink . $link_target . strtr($link_class, ['nav-link' => 'dropdown-item', 'dropdown-toggle' => '']) . " role='menuitem'>" : '');
							$res .= (!empty($link_data['link_icon']) ? "<i class='" . $link_data['link_icon'] . " m-r-5'></i>" : '');
							$res .= $link_data['link_name'];
							$res .= ($itemlink ? "</a>" : '');
							$res .= "</li>";
						}

						// Recursively pass dropdown style
						$res .= $this->showMenuLinks($link_data['link_id'], $data, 'dropdown-item', TRUE, $link_data['link_d_style']);
						$res .= "</ul>";
					}

					$res .= "</li>";
				} else {
					$res .= "<li class='divider' role='separator'></li>";
				}
				$i++;
			}
		}

		return $res;
	}

	private function showMenuLinks($id, $data, $skip_list = FALSE, $show_description = FALSE)
	{

		$res = '';

		if (!empty($data[$id])) {

			$default_link_data = [
				"link_id"          => 0,
				"link_name"        => "",
				"link_description" => "",
				"link_cat"         => 0,
				"link_url"         => "",
				"link_icon"        => "",
				"link_class"       => $skip_list ? 'dropdown-item' : "nav-link",
				"link_active"      => FALSE,
				"link_title"       => FALSE,
				"link_disabled"    => FALSE,
				"link_window"      => FALSE,
				"link_drop_style"  => 'default', // 'default', 'mega', 'arrow', etc
			];

			foreach ($data[$id] as $link_data) {

				$link_data += $default_link_data;

				$li_class = [];

				if (!$skip_list) {
					$li_class[] = 'nav-item';
				}

				if ($link_data['link_disabled']) {
					$li_class[] = 'disabled';
				}

				if ($link_data['link_url'] == '#') {
					$li_class[] = 'dropdown-header';
				}

				$has_child = isset($data[$link_data['link_id']]);

				$is_subtitle = (empty($link_data['link_url']) || $link_data['link_url'] == '#');

				if ($has_child && !$skip_list) {
					$li_class[] = 'dropdown';
				}

				if ($link_data['link_active']) {
					$li_class[] = 'active';
				}

				// Build link href
				$itemlink = '';
				if (!$is_subtitle) {
					$itemlink = preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])
						? " href='{$link_data['link_url']}'"
						: " href='" . BASEDIR . $link_data['link_url'] . "'";
					$itemlink = str_replace('%aidlink%', fusion_get_aidlink(), $itemlink);
				}

				// Determine link classes
				$link_class = $link_data['link_class'];
				if ($has_child && !$skip_list) {
					$link_class .= ' dropdown-toggle';
				}

				// Start LI
				if (!$skip_list) {
					$res .= "<li" . (!empty($li_class) ? " class='" . implode(" ", $li_class) . "'" : '') . ">";
				}

				if (!$is_subtitle) {
					// Build link tag

					$res .= "<a id='ddlink{$link_data['link_id']}' class='{$link_class}'" .
						" role='" . ($has_child ? "button" : "menuitem") . "'" .
						" data-bs-toggle='" . ($has_child ? "dropdown" : "") . "'" .
						" data-bs-auto-close='outside'" .
						" aria-expanded='false'" .
						$itemlink .
						($link_data['link_window'] ? " target='_blank'" : "") .
						">";
				}

				if ($skip_list) {
					$res .= "<div class='d-flex flex-column gap-2'>";
					$res .= "<div class='d-flex align-items-center gap-2'>";
				}

				if (!empty($link_data['link_icon'])) {
					$icon = '';
					if ($link_data['link_icon_type'] == 'glyph') {

						$icon = "<i class='{$link_data['link_icon']}'></i>";

					} else if ($link_data['link_icon_type'] == 'svg') {

						$icon = ImageRepo::getSVG($link_data['link_icon']);
					}

					if ($icon) {
						$res .= "<span class='nav-link-icon d-md-none d-lg-inline-block'>{$icon}</span>";
					}
				}

				$res .= "<span class='nav-link-title'>" . htmlspecialchars($link_data['link_name']) . "</span>";

				if ($skip_list) {
					$res .= "</div>";
				}

				if ($show_description && $link_data['link_description']) {
					$res .= "<div class='nav-link-desc text-break'>" . htmlspecialchars($link_data['link_description']) . "</div>";
				}

				if ($skip_list) {
					$res .= "</div>";
				}

				if (!$is_subtitle) {
					$res .= "</a>";
				}


				// Render children
				if ($has_child) {
					$children = $data[$link_data['link_id']];

					$style = $link_data['link_drop_style'] ?? 'default';

					if ($style === 'mega') {
						// Mega menu
						$countChildren = count($children);
						$numCols = min(4, $countChildren);
						$perCol = (int)ceil($countChildren / $numCols);
						$chunks = array_chunk($children, $perCol);

						$res .= "<div id='menu-{$link_data['link_id']}' aria-labelledby='ddlink{$link_data['link_id']}' class='mega dropdown-menu'>";
						$res .= "<div class='d-flex container w-100'>";

						foreach ($chunks as $chunk) {
							$res .= "<div class='col-xl-3 col-md-6 col-xs-12 columns'>";

							$defaults_child = [
								'link_disabled' => FALSE,
								'link_active'   => FALSE,
							];

							foreach ($chunk as $child_data) {
								// Render this level's <a> or header first
								$child_data += $defaults_child;

								$cli_class[] = 'nav-item';

								if ($child_data['link_disabled']) {
									$cli_class[] = 'disabled';
								}

								$cli_class[] = ($child_data['link_url'] == '#') ? 'dropdown-header' : 'dropdown-item ';

								$c_has_child = isset($data[$child_data['link_id']]);

								if ($child_data['link_active']) {
									$cli_class[] = 'active';
								}

								// Build link href
								$child_link = "";
								if (!empty($child_data['link_url']) && $child_data['link_url'] !== '#') {
									$child_link = preg_match("!^(ht|f)tp(s)?://!i", $child_data['link_url'])
										? " href='{$child_data['link_url']}'"
										: " href='" . BASEDIR . $child_data['link_url'] . "'";
									$child_link = str_replace('%aidlink%', fusion_get_aidlink(), $child_link);
								}

								// Determine link classes
								$child_link_class = $child_data['link_class'] ?? '' . implode(' ', $cli_class);
								// see if we need this because we can use default.
								$child_toggle_attr = '';
								if ($c_has_child) {

									/**
									 * Links are by default shown by default inside a mega menu, however:
									 * 1. If `link_drop_style` is not 'show', then hide it.
									 * 2. If `link_url` is not '#' then hide it as '#' value is a dropdown-header and cannot be clicked.
									 * If the links are hidden, there are no way to show them, so they must be shown by default.
									 * Otherwise, show all the links in a mega menu
									 */
									$default_container_start = '';
									$default_container_end = '';
									// Do not trigger the wrap if it's a dropdown header.
									if ($child_data['link_url'] != '#') {
										if ($child_data['link_drop_style'] != 'show') {
											$res .= "<div class='dropend'>";
											$default_container_start = "<div class='dropdown-menu'>";
											$default_container_end = "</div>";
											$child_toggle_attr = " data-bs-toggle='dropdown' data-bs-auto-close='outside' role='button' aria-expanded='false'";
											$child_link_class .= ' dropdown-toggle';
										}
									}
								}

								$res .= "<a class='{$child_link_class}' {$child_link}{$child_toggle_attr}>";

								if (!empty($child_data['link_icon'])) {
									if ($child_data['link_icon_type'] == 'glyph') {
										$res .= "<i class='{$child_data['link_icon']}'></i> ";
									} else if ($child_data['link_icon_type'] == 'svg') {
										$res .= ImageRepo::getSVG($child_data['link_icon']);
									}
								}

								$res .= htmlspecialchars($child_data['link_name']);

								if ($child_data['link_description']) {
									$res .= "<div class='nav-link-desc'>" . htmlspecialchars($child_data['link_description']) . "</div>";
								}

								$res .= "</a>";

								// Then render **children of this child** recursively, if any
								// Need to think of some styling ...
								if ($c_has_child) {
									$res .= $default_container_start ?? '';
									$res .= $this->showMenuLinks($child_data['link_id'], $data, TRUE, show_description: TRUE);
									$res .= $default_container_end ?? '';
								}

								if ($c_has_child) {
									if ($child_data['link_url'] != '#') {
										if ($child_data['link_drop_style'] != 'show') {
											$res .= "</div>";
										}
									}
								}


							}

							$res .= "</div>";
						}

						$res .= "</div>";

					} else {
						// Standard dropdown
						$style_class = match ($style) {
							'arrow' => 'dropdown-menu dropdown-menu-arrow',
							'card' => 'dropdown-menu dropdown-menu-card',
							'dark' => 'dropdown-menu dropdown-menu-dark',
							default => 'dropdown-menu'
						};

						$res .= "<ul id='menu-{$link_data['link_id']}' class='{$style_class}'>";
						foreach ($children as $child_data) {
							$child_id = $child_data['link_id'];
							$res .= $this->showMenuLinks($child_id, $data);
						}
						$res .= "</ul>";
					}
				}

				if (!$skip_list) {
					$res .= "</li>";
				}
			}
		}

		return $res;

	}

	public static function getDropdownStyles($key = NULL)
	{

		static $styles;
		if (empty($styles)) {
			$styles = [
				'default'   => 'Default style',
				'dark'      => 'Dark style',
				'Mega'      => 'Megamenu style',
				'mega-dark' => 'Dark Megamenu style',
				'card'      => 'Card style',
				'show'      => 'Show style',
			];
		}

		return $key === NULL ? $styles : ($styles[$key] ?? NULL);
	}

	/**
	 * Given a matching URL, fetch Sitelinks data
	 *
	 * @param string $url url to match (link_url) column
	 * @param string $key column data to output, blank for all
	 *
	 * @return array|bool
	 * @deprecated use getCurrentSiteLinks()
	 */
	public static function get_current_SiteLinks($url = "", $key = NULL)
	{

		return self::getCurrentSiteLinks($url, $key);
	}

	/**
	 * Render navigation for Tabler Framework (full method, updated markup)
	 *
	 * @param int $id
	 * @return string
	 */
	public function showVerticalLinks($id = 0)
	{

		$res = '';

		if (empty($id)) {

			self::setLinks();

			$navClass = self::getMenuParam('nav_class') ?: "navbar-nav";
			$callback_data = self::getMenuParam('callback_data');

			// Nav class
			$res .= "<ul class='{$navClass} flex-column'>";

			// Brand + mobile toggle
			if (self::getMenuParam('show_header')) {
				$res .= "<!-- Navbar Header Start -->";

				// Tabler hamburger button
				if (self::getMenuParam('responsive')) {
					$res .= "
                    <button class='navbar-toggler' type='button' data-bs-toggle='collapse'
                        data-bs-target='#" . self::getMenuParam('id') . "_menu'>
                        <span class='navbar-toggler-icon'></span>
                    </button>";
				}

				// Branding
				if (self::getMenuParam('show_banner') === TRUE) {
					$res .= "<a class='navbar-brand' href='" . BASEDIR . fusion_get_settings('opening_page') . "'>"
						. self::getMenuParam('banner') . "</a>";
				} else {
					$res .= "<a class='navbar-brand' href='" . BASEDIR . fusion_get_settings('opening_page') . "'>"
						. fusion_get_settings("sitename") . "</a>";
				}

				$res .= "<!-- Navbar Header End -->";
			}

			$res .= self::getMenuParam('custom_header');

			// Primary links
			$res .= "<!-- Menu Item Start -->";
			$res .= $this->showSideMenuLinks($id, (array)$callback_data);
			$res .= "<!-- Menu Item End -->";

			$res .= "<li class='copyr mt-auto pt-5 pb-2 px-3'>
				".date('Y')." &copy; All rights reserved
				<span class='strong me-2'>Sage Academy Sdn Bhd, Sage Labs</span>
				<div class='w-100 low-opacity'>Build alpha-1.15.00</div>
			</li>";
			$res .= "</ul>";

			$res .= self::getMenuParam('html_post_content');

		}

		return $res;
	}


	/**
	 * Recursively render menu links using the specified Tabler-style markup while preserving logic.
	 *
	 * @param int|string $id
	 * @param array $data
	 * @param bool $skip_list When true this is rendering inner (mega) lists and should render inline items
	 * @param bool $show_description
	 *
	 * @return string
	 */
	private function showSideMenuLinks($id, array $data, $skip_list = FALSE, $show_description = FALSE)
	{
		$res = '';

		if (!empty($data[$id])) {

			$default_link_data = [
				"link_id"          => 0,
				"link_name"        => "",
				"link_description" => "",
				"link_cat"         => 0,
				"link_url"         => "",
				"link_icon"        => "",
				"link_icon_type"   => "glyph", // ensure this exists
				"link_class"       => $skip_list ? 'dropdown-item' : "nav-link",
				"link_active"      => FALSE,
				"link_title"       => FALSE,
				"link_disabled"    => FALSE,
				"link_window"      => FALSE,
				"link_drop_style"  => 'default', // 'default', 'mega', 'arrow', etc
			];

			foreach ($data[$id] as $link_data) {

				// Merge with defaults
				$link_data = $link_data + $default_link_data;

				// Reset li_class for each top-level link
				$li_class = [];

				if (!$skip_list) {
					$li_class[] = 'nav-item';
				}

				if ($link_data['link_disabled']) {
					$li_class[] = 'disabled';
				}

				if ($link_data['link_url'] == '#') {
					$li_class[] = 'dropdown-header';
				}

				$has_child = isset($data[$link_data['link_id']]);

				$is_subtitle = (empty($link_data['link_url']) || $link_data['link_url'] == '#');

				if ($has_child && !$skip_list) {
					$li_class[] = 'dropdown';
				}

				// Build link href
				$itemlink = '';
				if (!$is_subtitle) {
					$itemlink = preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])
						? " href='" . $link_data['link_url'] . "'"
						: " href='" . BASEDIR . $link_data['link_url'] . "'";
					$itemlink = str_replace('%aidlink%', fusion_get_aidlink(), $itemlink);
				}

				// Determine link classes
				$link_class = $link_data['link_class'];

				if ($link_data['link_active'] || self::isLinkActive($link_data['link_url'])) {
					$link_class .= ' active';
				}

				if ($has_child && !$skip_list) {
					$link_class .= ' dropdown-toggle';
				}

				// Start LI (top-level or inside UL)
				if (!$skip_list) {
					// Need nav-item
					$res .= "<li" . (!empty($li_class) ? " class='" . implode(" ", $li_class) . "'" : '') . ">";
				}

				// Render <a> for non-subtitle
				if (!$is_subtitle) {
					$res .= "<a id='ddlink{$link_data['link_id']}' class='{$link_class}'"
						. ($has_child ? " role='button' data-bs-toggle='dropdown' aria-expanded='false'" : " role='menuitem'")
						. $itemlink
						. ($link_data['link_window'] ? " target='_blank'" : "")
						. ">";
				}

				// If skip_list is true we are rendering a compact inner block (but markup still follows nav-item/nav-link pattern)
				// Icon
				if (!empty($link_data['link_icon'])) {
					$icon_html = '';
					if (($link_data['link_icon_type'] ?? 'glyph') == 'glyph') {
						$icon_html = "<i class='{$link_data['link_icon']}'></i>";
					} else if (($link_data['link_icon_type'] ?? '') == 'svg') {
						// ImageRepo::getSVG may return raw SVG string
						$icon_html = ImageRepo::getSVG($link_data['link_icon']);
					}
					if ($icon_html) {
						$res .= "<span class='nav-icon'>{$icon_html}</span>";
					}
				}

				// Title text
				if ($is_subtitle) {

					$res .= "<div class='nav-heading'>" . $link_data['link_name'] . "</div>";
					$res .= "<hr class='mx-5 nav-line mb-1'>";

				} else {

					$res .= "<span class='text'>" . $link_data['link_name'] . "</span>";
				}

				// Optional description (when asked)
				if ($show_description && $link_data['link_description']) {
					$res .= "<div class='nav-link-desc text-break'>" . $link_data['link_description'] . "</div>";
				}

				if (!$is_subtitle) {
					$res .= "</a>";
				}

				// Render children, depending on drop style
				if ($has_child) {
					$children = $data[$link_data['link_id']];
					$style = $link_data['link_drop_style'] ?? 'default';

					if ($style === 'mega') {
						// Mega menu rendering (keeps grid but uses requested nav-item/nav-link markup for items)
						$countChildren = count($children);
						$numCols = min(4, max(1, (int)ceil($countChildren / 6))); // heuristic: up to 6 items per col
						$perCol = (int)ceil($countChildren / $numCols);
						$chunks = array_chunk($children, $perCol);

						$res .= "<div id='menu-{$link_data['link_id']}' aria-labelledby='ddlink{$link_data['link_id']}' class='mega dropdown-menu'>";
						$res .= "<div class='d-flex container w-100'>";

						foreach ($chunks as $chunk) {
							$res .= "<div class='col-xl-3 col-md-6 col-xs-12 columns'>";

							foreach ($chunk as $child_data) {
								// Ensure defaults for child level
								$child_defaults = $default_link_data;
								$child_data = $child_data + $child_defaults;

								// Build child classes properly
								$cli_class = ['nav-item'];
								if ($child_data['link_disabled']) {
									$cli_class[] = 'disabled';
								}

								// dropdown-header vs dropdown-item not used visually in this layout: treat '#' as header
								$is_child_header = ($child_data['link_url'] == '#');
								$child_link_class = 'nav-link';
								if ($is_child_header) {
									// render header as plain title
									$res .= "<div class='nav-heading'>" . $child_data['link_name'] . "</div>";
									if ($child_data['link_description']) {
										$res .= "<div class='nav-link-desc text-break'>" . $child_data['link_description'] . "</div>";
									}
								} else {
									// Determine link href for child
									$child_link = "";
									if (!empty($child_data['link_url']) && $child_data['link_url'] !== '#') {
										$child_link = preg_match("!^(ht|f)tp(s)?://!i", $child_data['link_url'])
											? " href='{$child_data['link_url']}'"
											: " href='" . BASEDIR . $child_data['link_url'] . "'";
										$child_link = str_replace('%aidlink%', fusion_get_aidlink(), $child_link);
									}

									// If child has its own children, we may wrap it in a dropend
									$c_has_child = isset($data[$child_data['link_id']]);
									$child_toggle_attr = '';
									$default_container_start = '';
									$default_container_end = '';

									if ($c_has_child) {
										// For mega menus, links are shown by default, but if link_drop_style indicates hiding, we'll create a dropend wrapper
										if ($child_data['link_url'] != '#') {
											if (($child_data['link_drop_style'] ?? '') != 'show') {
												$res .= "<div class='dropend'>";
												$default_container_start = "<div class='dropdown-menu'>";
												$default_container_end = "</div>";
												$child_toggle_attr = " data-bs-toggle='dropdown' data-bs-auto-close='outside' role='button' aria-expanded='false'";
												$child_link_class .= ' dropdown-toggle';
											}
										}
									}

									// Render child item
									$res .= "<a class='{$child_link_class}' {$child_link}{$child_toggle_attr}>";
									// Icon
									if (!empty($child_data['link_icon'])) {
										if (($child_data['link_icon_type'] ?? 'glyph') == 'glyph') {
											$res .= "<span class='nav-icon'><i class='{$child_data['link_icon']}'></i></span>";
										} else {
											$res .= "<span class='nav-icon'>" . ImageRepo::getSVG($child_data['link_icon']) . "</span>";
										}
									}
									// Text
									$res .= "<span class='text'>" . $child_data['link_name'] . "</span>";

									if ($child_data['link_description']) {
										$res .= "<div class='nav-link-desc'>" . $child_data['link_description'] . "</div>";
									}
									$res .= "</a>";

									// Then recursively render grandchildren if present
									if ($c_has_child) {
										$res .= $default_container_start;
										$res .= $this->showMenuLinks($child_data['link_id'], $data, TRUE, TRUE);
										$res .= $default_container_end;
									}

									if ($c_has_child) {
										if ($child_data['link_url'] != '#') {
											if (($child_data['link_drop_style'] ?? '') != 'show') {
												$res .= "</div>"; // close dropend
											}
										}
									}
								} // end child header vs normal
							} // end foreach chunk

							$res .= "</div>";
						} // end foreach chunk

						$res .= "</div></div>"; // close mega container

					} else {
						// Standard dropdown rendering using the requested markup
						$style_class = match ($style) {
							'arrow' => 'dropdown-menu dropdown-menu-arrow',
							'card' => 'dropdown-menu dropdown-menu-card',
							'dark' => 'dropdown-menu dropdown-menu-dark',
							default => 'dropdown-menu',
						};

						// We use flex-column to match layout preference
						$style_class .= ' flex-column';

						$res .= "<ul id='menu-{$link_data['link_id']}' class='{$style_class}'>";

						foreach ($children as $child_data) {
							$child_id = $child_data['link_id'];

							// For each child we want an li.nav-item > a.nav-link layout
							// But we will call showMenuLinks recursively to preserve deeper nesting and logic.
							// Create a temporary wrapper to ensure li.nav-item structure; showMenuLinks will output li if needed.
							// We'll directly build the child markup here to ensure markup parity.

							// Merge defaults and build child link
							$child_defaults = $default_link_data;
							$child_data = $child_data + $child_defaults;

							$child_li_classes = ['nav-item'];

							if ($child_data['link_disabled']) {
								$child_li_classes[] = 'disabled';
							}

							if ($child_data['link_active']) {
								$child_li_classes[] = 'active show';
							}

							if ($child_data['link_url'] == '#') {
								$child_li_classes[] = 'dropdown-header';
							}

							$res .= "<li class='" . implode(' ', $child_li_classes) . "'>";

							// Build child href
							$child_link = "";
							if (!empty($child_data['link_url']) && $child_data['link_url'] !== '#') {
								$child_link = preg_match("!^(ht|f)tp(s)?://!i", $child_data['link_url'])
									? " href='{$child_data['link_url']}'"
									: " href='" . BASEDIR . $child_data['link_url'] . "'";
								$child_link = str_replace('%aidlink%', fusion_get_aidlink(), $child_link);
							}

							$child_link_class = 'nav-link';
							if (isset($data[$child_data['link_id']])) {
								$child_link_class .= ' dropdown-toggle';
							}

							$res .= "<a class='{$child_link_class}'{$child_link}" . (isset($data[$child_data['link_id']]) ? " role='button' data-bs-toggle='dropdown' aria-expanded='false'" : " role='menuitem'") . ($child_data['link_window'] ? " target='_blank'" : "") . ">";

							// Icon
							if (!empty($child_data['link_icon'])) {
								if (($child_data['link_icon_type'] ?? 'glyph') == 'glyph') {
									$res .= "<span class='nav-icon'><i class='{$child_data['link_icon']}'></i></span>";
								} else {
									$res .= "<span class='nav-icon'>" . ImageRepo::getSVG($child_data['link_icon']) . "</span>";
								}
							}

							// Text
							$res .= "<span class='text'>" . htmlspecialchars($child_data['link_name']) . "</span>";

							if ($child_data['link_description']) {
								$res .= "<div class='nav-link-desc text-break'>" . htmlspecialchars($child_data['link_description']) . "</div>";
							}

							$res .= "</a>";

							// Recursively render grandchildren if any
							if (isset($data[$child_data['link_id']])) {
								$res .= $this->showMenuLinks($child_data['link_id'], $data, TRUE, TRUE);
							}

							$res .= "</li>";
						}

						$res .= "</ul>";
					} // end standard dropdown vs mega
				} // end if has_child

				if (!$skip_list) {
					$res .= "</li>";
				}
			}
		}

		return $res;
	}

	public static function isLinkActive($link_url)
	{
		// 1. Clean the relative pathing
		$normalized_link = str_replace('../', '', $link_url);

		$link_parts = parse_url($normalized_link);
		$current_parts = parse_url($_SERVER['REQUEST_URI']);

		// 2. Normalize Paths (Force both to be /path/to/page)
		$link_path = '/' . trim($link_parts['path'] ?? '', '/');
		$current_path = '/' . trim($current_parts['path'] ?? '', '/');

		if ($link_path !== $current_path) {
			return FALSE;
		}

		// 3. Convert Query Strings to Arrays
		$link_query_array = [];
		if (!empty($link_parts['query'])) {
			// This creates ['page' => 'programs']
			parse_str(htmlspecialchars_decode($link_parts['query']), $link_query_array);
		}

		$current_query_array = [];
		if (!empty($current_parts['query'])) {
			// This creates ['page' => 'programs', 'action' => 'new', ...]
			parse_str(htmlspecialchars_decode($current_parts['query']), $current_query_array);
		}

//		print_p($link_query_array);
//		print_p($current_query_array);
		// 4. Comparison Logic
		// We check if every requirement in the link exists in the current URL
		foreach ($link_query_array as $key => $value) {
			if (!isset($current_query_array[$key]) || $current_query_array[$key] != $value) {
				return FALSE;
			}
		}

		return TRUE;
	}
}
