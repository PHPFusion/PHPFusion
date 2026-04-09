<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Admins.php
| Author: Frederick MC Chan
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

/**
 * Class Admin
 * This class is called in templates/admin_header.php
 * Determine how to we set variables on 3rd party script
 */
class Admins
{
	
	private static $instance = NULL;
	
	/**
	 * Administration pages
	 *
	 * @var array
	 */
	private static $admin_pages = [];
	
	/**
	 * Locale
	 *
	 * @var array
	 */
	private static $locale = [];
	
	/**
	 * @var array - default section icons
	 */
	
	public $admin_section_icons = [
		'0' => 'squares-2x2',           // Dashboard (General)
		'1' => 'megaphone',             // News/Announcements (was microphone)
		'2' => 'users',                 // User Management
		'3' => 'cog-6-tooth',           // System Settings
		'4' => 'adjustments-horizontal', // Tools/Wrench (Advanced Settings)
		'5' => 'cube',                  // Infusions/Modules (was cubes)
		'6' => 'beaker',                // Development/Core (was atom-simple)
	];
	/**
	 * Default core administration pages
	 *
	 * @var array
	 */
	public $admin_page_icons = [
		'AD'   => 'user-group',           // Administrators
		'APWR' => 'key',                  // Admin Password Reset
		'B'    => 'no-symbol',            // Blacklist
		'BB'   => 'chat-bubble-bottom-center-text', // BB Codes
		'C'    => 'chat-bubble-left-right', // Comments
		'CP'   => 'document-text',        // Custom Pages
		'DB'   => 'server-stack',         // Database Backup
		'ERRO' => 'bug-ant',              // Error Log
		'FM'   => 'folder-open',          // Fusion File Manager
		'I'    => 'cpu-chip',             // Infusions (Plugins)
		'IM'   => 'photo',                // Images
		'LANG' => 'language',             // Language Settings
		'M'    => 'users',                // Members
		'MAIL' => 'envelope',             // Email Templates
		'MI'   => 'arrows-right-left',    // Migration Tool
		'P'    => 'window',               // Panels
		'PI'   => 'information-circle',   // PHP Info
		'PL'   => 'link',                 // Permalinks
		'ROB'  => 'cpu-chip',             // robots.txt (alt: eye)
		'S1'   => 'cog-6-tooth',          // Main Settings
		'S2'   => 'clock',                // Time and Date
		'S3'   => 'paint-brush',          // Theme Settings
		'S4'   => 'user-plus',            // Registration Settings
		'S6'   => 'adjustments-horizontal', // Miscellaneous Settings
		'S7'   => 'inbox-stack',          // PM Settings
		'S9'   => 'identification',       // User Management
		'S12'  => 'shield-check',         // Security Settings
		'SB'   => 'megaphone',            // Banners
		'SL'   => 'globe-alt',            // Site Links
		'SM'   => 'face-smile',           // Smileys
		'TS'   => 'sparkles',             // Theme Manager
		'U'    => 'arrow-up-circle',      // Upgrade
		'UF'   => 'table-cells',          // User Fields
		'UG'   => 'user-group',           // User Groups
		'UL'   => 'clipboard-document-list', // User Log
		'ST'   => 'archive-box',          // User Log (ST)
	];
	
	protected $settings_uri;
	
	protected $dashboard_uri;
	
	/**
	 * @var array
	 */
	private $admin_sections = [1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE, 6 => FALSE];
	
	/**
	 * @var array
	 */
	private $admin_page_link = [];
	
	/**
	 *    Constructor class. No Params
	 */
	private $current_page = '';
	
	private $comment_type = [];
	
	private $submit_type = [];
	
	private $submit_link = [];
	
	private $link_type = [];
	
	private $submit_data = [];
	
	private $folder_permissions = [];
	
	private $customfolders = [];
	
	private $icon_color
		= [
			'S1'   => '#D89217', // Main
			'S2'   => '#384146', // Time and Date
			'S3'   => '#A12669', // Theme Settings
			'S4'   => '#3F0760', // Registration
			'S6'   => '#092133', // Misc
			'S7'   => '#373C4F', // PM
			'S9'   => '#075571', // User Management
			'S12'  => '#01ADB5', // Security
			'LANG' => '#FD7115', // Languages
			
			'SB'   => '#0C419C', // system banner
			'BB'   => '#075571', // BBcode
			'DB'   => '#3A3F70', // DB
			'ERRO' => '#3E2F50', // Error logs
			'I'    => '#F30B4A', // Infusions
			'P'    => '#0E64A5',  // Panels
			'PL'   => '#012040', // Permalink
			'PI'   => '#5828A3', // Php Info
			'SL'   => '#571151', // Sitelinks
			'SM'   => '#232932', // Smileys
			'TS'   => '#308886', // Theme Settings
			'ROB'  => '#29485D', // Robots
			'MAIL' => '#C32CAD', // Email
			//'MAIL'=>'#2B2539',
			'AD'   => '#554279', // Administrator
			'APWR' => '#31485F', // Admin Password Reset
			'B'    => '#212141', //Blacklist
			'M'    => '#CB3C3C', //Members
			'MI'   => '#293F57', //Migration
			'U'    => '#262626', // Upgrade
			'UG'   => '#1A89AC', //User groups
			'UF'   => '#970848', // User fields
			'UL'   => '#F3C624', //User Logs,
			'ST'   => '#D89327',
		];
	
	public function __construct()
	{
		
		self::$locale = self::getAdminLocale();
	}
	
	public static function getAdminLocale()
	{
		
		if (empty(self::$locale)) {
			self::$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/main.php');
		}
		
		return self::$locale;
	}
	
	/**
	 * Add instance
	 *
	 * @return static
	 */
	public static function getInstance()
	: ?Admins
	{
		
		if (empty(self::$instance)) {
			self::$instance = new static();
		}
		
		return self::$instance;
	}
	
	/**
	 * Cache the Current Field Inputs within Login session.
	 *
	 * @param       $form_id
	 * @param       $form_type
	 * @param       $item_id
	 * @param array $callback_fields
	 * @param int $cache_time
	 *
	 * @return string
	 */
	public function requestCache($form_id, $form_type, $item_id, array $callback_fields = [], int $cache_time = 30000)
	: string {
		
		/*
		add_to_footer("<script src='".INCLUDES."jquery/confirm/jquery-confirm.min.js'></script>");
		add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/confirm/jquery-confirm.min.css'>");
		add_to_jquery("
			var input_fields = $('#$form_id').serialize();
			$.confirm({
			title: 'Restore Editing Session',
			content: 'There is a draft found for your previous editing session. Do you wish to restore?',
			buttons: {
				confirm: function () {
					$.ajax({
						url: '$remote',
						type: 'post',
						data: {
						'fusion_token': '$token',
						'aidlink': '".fusion_get_aidlink()."',
						'fields': input_fields,
						'form_id' : '$form_id',
						'item_id':'$item_id',
						'form_type':'$form_type',
						'callback':'read_cache'
						},
						dataType: 'json',
						success: function (e) {
							if (e.response == 200) {
								$.each(e.data, function(key, value) {
									$('#'+key).val( value );
								});
								setTimeout(function(e) {
									//$.alert('Your editing session has been restored');
									setTimeout( function(e) {UpdateAdminCache() }, $cache_time);
								}, 100);
							}
						},
						error: function(e) {
						}
				   });
				},
				cancel: function () {
					//$.alert('Canceled!');
				}
				/*somethingElse: {
					text: 'Something else',
					btnClass: 'btn-blue',
					keys: ['enter', 'shift'],
					action: function(){
						$.alert('Something else?');
					}
				}
			}
		});
		");*/
		$remote = fusion_get_settings('site_path') . "administration/includes/cache_update.php";
		$token = fusion_get_token($form_id, 5);
		add_to_jquery("
        function timedCacheRequest(timeout) {
            setTimeout(UpdateAdminCache, timeout);
        }
        function UpdateAdminCache(poll) {
            var input_fields = $('#$form_id').serialize();
            var ttl = '$cache_time';
            $.ajax({
            url: '$remote',
            type: 'post',
            dataType: 'html',
            data: {
                'fusion_token': '$token',
                'aidlink': '" . fusion_get_aidlink() . "',
                'fields': input_fields,
                'form_id' : '$form_id',
                'form_type':'$form_type',
                'item_id': '$item_id',
                'callback':'set_cache',
            },
            dataType: 'json',
            success: function (e) {
                console.log(e);
                console.log(poll);
                if (typeof poll === 'undefined') {
                    //console.log('we are starting a long poll now');
                    timedCacheRequest(ttl);
                }
            }
            });
        }
        // add abort long poll once we change fields.
        $('#" . $form_id . " :input').blur(function(e) {
            UpdateAdminCache(1);
        });
        ");
		if (!isset($_GET['autosave'])) {
			// not to pass prefix xhr for security reason.
			add_to_jquery("setTimeout( function(e) { UpdateAdminCache() }, $cache_time);");
		}
		
		$html = "";
		if (!empty($_SESSION['form_cache'][$form_id][$form_type][$item_id])) {
			
			$html .= "<label class='list-group-item text-normal spacer-xs p-15 m-b-10'>" . self::$locale['290'] . " <a href='" . clean_request('autosave=view',
					['autosave'],
					FALSE) . "'>" . self::$locale['291'] . "</a></label>";
			
			if (isset($_GET['autosave']) && $_GET['autosave'] == 'view') {
				
				$html .= "<div id='rev-window'>\n";
				$html .= fusion_get_function('openside',
					"<h4><i class='fas fa-thumbtack m-r-10'></i>" . self::$locale['292'] . "</h4>");
				$session = htmlspecialchars_decode($_SESSION['form_cache'][$form_id][$form_type][$item_id]);
				$session = descript($session);
				$session = str_replace('&#039;', "'", $session);
				parse_str($session, $data);
				unset($data['form_id']);
				unset($data['fusion_token']);
				
				$html .= "<dl id='restore_results' class='dl-horizontal'>\n";
				$fill_js = "";
				foreach ($data as $field_name => $value) {
					if (isset($callback_fields[$field_name])) {
						$html .= "<dt>" . $callback_fields[$field_name] . "</dt>\n";
						$html .= "<dd class='m-b-15'><samp>";
						$html .= nl2br(html_entity_decode($value));
						$html .= "</samp>" . form_hidden('s_' . $field_name,
								'',
								str_replace("'", '&#039;', $value)) . "</dd>\n";
						$fill_js .= "
                            var c = $('#s_$field_name').val();
                            $('#" . $field_name . "').val(c);
                        ";
					}
				}
				$html .= "</dl>\n";
				$html .= "<div class='text-right'>\n";
				$html .= "<button name='cancel_session' type='button' class='btn btn-default' value='cancel_session'>" . self::$locale['cancel'] . "</button>\n";
				$html .= "<button name='fill_session' type='button' class='btn btn-primary' value='fill_session'>" . self::$locale['293'] . "</button>\n";
				$html .= "</div>\n";
				$html .= fusion_get_function('closeside', '');
				$html .= "</div>\n";
				add_to_jquery("
                $('button[name^=\"fill_session\"]').on('click', function(e) {
                    $fill_js
                    $('#rev-window').hide();
                    UpdateAdminCache();
                });
                $('button[name^=\"cancel_session\"]').on('click', function(e) {
                    $('#rev-window').hide();
                    UpdateAdminCache();
                });
                ");
			}
		}
		
		return $html;
	}
	
	public function setAdmin()
	{
		
		self::$admin_pages = $this->getAdminPages();
		$this->admin_sections = array_filter(array_merge([
			0 => self::$locale['ac00'],
			1 => self::$locale['ac01'],
			2 => self::$locale['ac02'],
			3 => self::$locale['ac03'],
			4 => self::$locale['ac04'],
			5 => self::$locale['ac05'],
			6 => 'PHPFusion',
		], $this->admin_sections));
		$this->admin_sections = array_values($this->admin_sections);
		$this->current_page = $this->_currentPage();
		$this->settings_uri = ADMIN . fusion_get_aidlink() . '&s=settings';
		$this->dashboard_uri = ADMIN . fusion_get_aidlink() . '&s=dashboard';
	}
	
	/**
	 * @return array
	 */
	public function getAdminPages()
	{
		
		$aidlink = fusion_get_aidlink();
		
		$locale = fusion_get_locale();
		
		self::$admin_pages = array_filter(self::$admin_pages);
		
		if (empty(self::$admin_pages)) {
			
			$result = dbquery("SELECT * FROM " . DB_ADMIN . " WHERE admin_language='" . LANGUAGE . "' ORDER BY admin_page DESC, admin_id ASC, admin_title ASC");
			
			$rows = dbrows($result);
			
			if ($rows) {
				while ($data = dbarray($result)) {
					
					if (checkrights($data['admin_rights']) && $data['admin_link'] != "reserved") {
						
						if (is_file(ADMIN . $data['admin_link']) || is_file(ADMIN . 'contents/' . $data['admin_link']) || is_file($data['admin_link'])) {
							
							$data['admin_title'] = preg_replace("/&(?!(#\d+|\w+);)/", "&amp;", $data['admin_title']);
							
							$data['admin_description'] = isset($locale[$data['admin_rights'] . 'D']) && $locale[$data['admin_rights'] . 'D'] ? $locale[$data['admin_rights'] . 'D'] : ($locale[$data['admin_rights']] ?? '');
							
							$data['admin_icon_color'] = $this->icon_color[$data['admin_rights']] ?? '';
							
							$data['admin_icon'] = $this->admin_page_icons[$data['admin_rights']];

//                            $data['admin_icon'] = $this->custom_icons[ $data['admin_rights'] ] ?? 'fad fa-book';
							// check files
							
							$admin_link = $data['admin_link'] . $aidlink;
							
							if (is_file(ADMIN . 'contents/' . $data['admin_link'])) {
								$admin_link = ADMIN . $aidlink . '&p=' . strtolower($data['admin_rights']);
							} else if (is_file(APPS . $data['admin_link'])) {
								$admin_link = ADMIN . $aidlink . '&inf=' . strtolower($data['admin_rights']);
							}
							
							$data['admin_link'] = $admin_link;
							
							self::$admin_pages[$data['admin_page']][] = $data;
						}
						
					}
					
				}
			}
		}
		
		return self::$admin_pages;
	}
	
	/**
	 * Build a return that always synchronize with the DB_ADMIN url.
	 */
	public function _currentPage()
	{
		
		$path = $_SERVER['PHP_SELF'];
		if (defined('START_PAGE')) {
			$path_info = pathinfo(START_PAGE);
			if (stristr(FUSION_REQUEST, '/administration/')) {
				$path = $path_info['filename'] . '.php';
			} else {
				$path = '../' . $path_info['dirname'] . '/' . $path_info['filename'] . '.php';
			}
		}
		
		return $path;
	}
	
	/**
	 * @param $page - 0-5 is core section pages. 6 and above are free to use.
	 * @param $section_title - Section title
	 * @param $icons - Section Icons
	 */
	public function addAdminSection($page, $section_title, $icons)
	{
		
		$this->admin_sections[$page] = $section_title;
		$this->admin_section_icons[$page] = $icons;
		self::$admin_pages[$page] = [];
	}
	
	public function setAdminBreadcrumbs()
	{
		
		add_breadcrumb([
			'link'  => ADMIN . 'index.php' . fusion_get_aidlink() . '&amp;pagenum=0',
			'title' => self::$locale['ac10'],
		]);
		$acTab = (isset($_GET['pagenum']) && isnum($_GET['pagenum'])) ? $_GET['pagenum'] : $this->_isActive();
		if ($acTab != 0 && $acTab <= 5) {
			add_breadcrumb([
				'link'  => ADMIN . fusion_get_aidlink() . "&amp;pagenum=" . $acTab,
				'title' => self::$locale['ac0' . $acTab],
			]);
		}
		
	}
	
	/**
	 * Determine which section is currently active.
	 *
	 * @return int|string
	 */
	public function _isActive()
	{
		
		$active_key = 0;
		self::$admin_pages = $this->getAdminPages();
		if (empty($active_key) && !empty(self::$admin_pages)) {
			foreach (self::$admin_pages as $key => $data) {
				$link = [];
				foreach ($data as $arr => $admin_data) {
					$link[] = $admin_data['admin_link'];
				}
				$data_link = array_flip($link);
				if (isset($data_link[$this->_currentPage()])) {
					return $key;
				}
			}
		}
		
		return '0';
	}
	
	/**
	 * @param $rights
	 * @param $icons
	 */
	public function setAdminPageIcons($rights, $icons)
	{
		
		$this->admin_page_icons[$rights] = $icons;
	}
	
	public function getLinkType($type = NULL)
	{
		
		return ($type !== NULL ? ($this->link_type[$type] ?? NULL) : $this->link_type);
	}
	
	/**
	 * @param $type - link prefix
	 * @param $link - link url
	 */
	public function setLinkType($type, $link)
	{
		
		$this->link_type[$type] = $link;
	}
	
	/**
	 * Get Submit Type
	 *
	 * @param null $type submit stype prefix
	 *
	 * @return array|mixed|null
	 */
	public function getSubmitType($type = NULL)
	{
		
		return ($type !== NULL ? ($this->submit_type[$type] ?? NULL) : $this->submit_type);
	}
	
	/**
	 * @param $type - submissions prefix
	 * @param $title - title
	 */
	public function setSubmitType($type, $title)
	{
		
		$this->submit_type[$type] = $title;
	}
	
	public function getSubmitData($type = NULL)
	{
		
		return ($type !== NULL ? ($this->submit_data[$type] ?? NULL) : $this->submit_data);
	}
	
	/**
	 * @param $type - submissions prefix
	 * @param $options - array(infusion_name, link, submit_link, submit_locale, title,admin_link)
	 */
	public function setSubmitData($type, array $options = [])
	{
		
		defined(strtoupper($options['infusion_name']) . '_EXISTS') ? $this->submit_data[$type] = $options : NULL;
	}
	
	public function getSubmitLink($type = NULL)
	{
		
		return ($type !== NULL ? ($this->submit_link[$type] ?? NULL) : $this->submit_link);
	}
	
	/**
	 * @param string $link Admin submission url
	 * @param string $type Submissions stype prefix
	 */
	public function setSubmitLink($type, $link)
	{
		
		$this->submit_link[$type] = $link;
	}
	
	public function getCommentType($type = NULL)
	{
		
		return ($type !== NULL ? ($this->comment_type[$type] ?? NULL) : $this->comment_type);
	}
	
	/**
	 * @param $type - comment prefix
	 * @param $title - title
	 */
	public function setCommentType($type, $title)
	{
		
		$this->comment_type[$type] = $title;
	}
	
	/**
	 * @param $type - infusion_name
	 */
	public function getFolderPermissions($type = NULL)
	{
		
		return ($type !== NULL ? ($this->folder_permissions[$type] ?? NULL) : $this->folder_permissions);
	}
	
	/**
	 * @param $type - infusion_name
	 * @param $options - array(image_folder => TRUE or FALSE)
	 */
	public function setFolderPermissions($type, array $options = [])
	{
		
		defined(strtoupper($type) . '_EXISTS') ? $this->folder_permissions[$type] = $options : NULL;
	}
	
	/**
	 * @param null $rights
	 *
	 * @return array|null
	 */
	public function getCustomFolders($rights = NULL)
	{
		
		return ($rights !== NULL ? ($this->customfolders[$rights] ?? NULL) : $this->customfolders);
	}
	
	/**
	 * A custom folder that appears in the file manager
	 *
	 * @param $rights
	 * @param $options - setCustomFolder('N', [['path' => IMAGES_N, 'URL' => fusion_get_settings('siteurl').'infusions/news/images/', 'alias' => 'news']]);
	 */
	public function setCustomFolder($rights, $options = [])
	{
		
		$this->customfolders[$rights] = $options;
	}
	
	/**
	 * @return array
	 */
	public function getAdminPageLink()
	{
		
		return $this->admin_page_link;
	}
	
	/**
	 * @return mixed
	 */
	public function getCurrentPage()
	{
		
		return $this->current_page;
	}
	
	
	/**
	 * @return array
	 */
	public function getAdminSections()
	: array
	{
		return $this->admin_sections;
	}
	
	/**
	 * @param $page_number
	 *
	 * @return string
	 */
	public function get_admin_section_icons($page_number)
	{
		
		if (!empty($this->admin_section_icons[$page_number]) && $this->admin_section_icons[$page_number]) {
			return $this->admin_section_icons[$page_number];
		}
		
		return FALSE;
	}
	
	/**
	 * Replace admin page icons
	 *
	 * @param $page
	 * @param $icons
	 */
	public function setAdminSectionIcons($page, $icons)
	{
		
		if (isset($this->admin_section_icons[$page])) {
			$this->admin_section_icons[$page] = $icons;
		}
	}
	
	/**
	 * Get the administration page icons
	 *
	 * @param $admin_rights
	 *
	 * @return bool
	 */
	public function get_admin_icons($admin_rights)
	{
		
		// admin rights might not yield an icon & admin_icons override might not have the key.
		if (isset($this->admin_page_icons[$admin_rights]) && $this->admin_page_icons[$admin_rights]) {
			return $this->admin_page_icons[$admin_rights];
		}
		
		return FALSE;
	}
	
	/**
	 * Displays horizontal administration navigation
	 *
	 * @param bool $icon_only
	 *
	 * @return string
	 */
	public function horizontal_admin_nav($icon_only = FALSE)
	{
		
		$aidlink = fusion_get_aidlink();
		$html = "<ul class='admin-horizontal-link'>\n";
		foreach ($this->admin_sections as $i => $section_name) {
			$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && $this->_isActive() == $i) ? 1 : 0;
			$admin_text = $icon_only == FALSE ? " " . $section_name : "";
			$html .= "<li " . ($active ? "class='active'" : '') . "><a title='" . $section_name . "' href='" . ADMIN . $aidlink . "&amp;pagenum=$i'>" . $this->get_admin_section_icons($i) . $admin_text . "</a></li>\n";
		}
		$html .= "</ul>\n";
		
		return $html;
	}
	
}
