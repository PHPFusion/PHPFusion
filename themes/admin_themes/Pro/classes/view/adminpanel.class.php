<?php

namespace PHPFusion\Pro\Classes\View;

use PHPFusion\Errors;
use PHPFusion\Pro\Classes\AdminHelper;

/*
 * Adminpanel
 */

class AdminPanel
{
	
	/* Helper object */
	private $helper;
	
	/* Constructs */
	/**
	 * @var Errors
	 */
	private $errorClass;
	
	public function __construct()
	{
		
		add_to_footer("<script src='" . ADMIN_THEMES . "Pro/pfpro.js'></script>");
		add_to_head("<link rel='stylesheet' type='text/css' href='" . ADMIN_THEMES . "Pro/acp_styles.css'>");
		echo "<noscript>";
		echo "<style>
        .pf-side-header .side-header-btn { display:none !important; }
        .pf-side-body {display:block!important;}
        .pf-admin-buttons {display:block !important;}
        </style>";
		echo "</noscript>";
		$this->helper = new AdminHelper();
		
	}
	
	private static $instance;
	
	public static function getInstance()
	: AdminPanel
	{
		
		if (empty(self::$instance)) {
			self::$instance = new AdminPanel();
		}
		
		return self::$instance;
	}
	
	
	/* Admin panel theme */
	public function viewTheme()
	{
		require_once ADMIN_THEMES . 'Pro/templates/theme.tpl.php';
		
		$userdata = fusion_get_userdata();
		$settings = fusion_get_settings();
		
		$this->errorClass = Errors::getInstance();
		$errors = $this->errorClass->getErrors();
		$new_errors = $this->errorClass->getNewErrors();
		
		$sitebanner = '';
		if (!empty($settings['sitebanner'])) {
			$sitebanner = str_replace('images/', IMAGES, fusion_get_settings('sitebanner'));
			$sitebanner = '<img src="' . IMAGES . $sitebanner . '" alt="">';
		}
		
		$info = [
			'admin_pages'          => $this->helper->getAdminPages(),
			'admin_sections'       => $this->helper->viewThemeAdminSections(),
			'admin_avatar'         => display_avatar($userdata, '30px', 'pf-nav-avatar', FALSE, 'img-circle'),
			'userdata'             => $userdata,
			'admin_breadcrumbs'    => $this->helper->getAdminBreadcrumbs(),
			'settings'             => fusion_get_settings(),
			'sitebanner'           => $sitebanner,
			'profile_uri'          => BASEDIR . 'profile.php?lookup=' . $userdata['user_id'],
			'api_url'              => fusion_get_locale('', [LOCALE . LOCALESET . 'api.php']),
			'signout_uri'          => FUSION_REQUEST . '&logout',
			'settings_uri'         => $this->helper->getSettingsURI(),
			'dashboard_uri'        => $this->helper->getDashboardURI(),
			'admin_notices'        => $this->helper->getAdminNotices(),
			'admin_buttons'        => fusion_filter_hook('pf_admin_buttons') ?? '',
			'admin_filters'        => fusion_filter_hook('pf_admin_filters')[0] ?? '',
			'admin_page_title'     => fusion_filter_hook('pf_admin_page_title')[0] ?? '',
			'admin_page_nav'       => fusion_filter_hook('pf_admin_left_nav')[0] ?? '', // this one will pass content
			'content'              => CONTENT,
			'main_width_class'     => fusion_filter_hook('pf_admin_full_width')[0] ?? '',
			'footer_errors'        => $this->footerErrors($errors, $new_errors),
			'new_errors'           => $new_errors,
		];

		admin_theme_tpl($info);
	}
	
	/**
	 * @param $errors
	 * @param $new_errors
	 *
	 * @return string
	 */
	private function footerErrors($errors, $new_errors)
	: string {
		
		if (iADMIN && checkrights("ERRO") && count($new_errors)) {
			$locale = fusion_get_locale();
			
			// Modal
			$modal = openmodal('tbody',
				$locale['ERROR_464'],
				['class' => 'modal-lg modal-center zindex-boost errorlogmodal', 'button_id' => 'footer_debug']);
			$modal .= $this->errorClass->getErrorLogs();
			$modal .= closemodal();
			add_to_footer($modal);
			
			return '<a id="footer_debug"><i class="far fa-bug"></i><span class="error-badge badge">' . count($new_errors) . '</span></a>';
		}
		
		return '';
	}
	
	/* Dashboard */
	public function viewDashboard()
	{
		
		$info = [
		];
//		echo fusion_render(__DIR__ . '/../../templates/', 'dashboard.twig', $info, TRUE);
	}
	
	/* Admin login */
	public function viewLogin()
	{
		require_once ADMIN_THEMES . 'Pro/templates/login.tpl.php';
		
		$sitebanner = str_replace('images/', IMAGES, fusion_get_settings('sitebanner'));
		
		$info = [
			'{%__OPENFORM__%}'       => openform('adminLoginfrm', 'POST'),
			'{%__CLOSEFORM__%}'      => closeform(),
			'{%__SETTINGS__%}'       => fusion_get_settings(),
			'{%__SITENAME__%}'       => fusion_get_settings("sitename"),
			'{%__SITEBANNER__%}'     => $sitebanner,
			'{%__PASSWORD_INPUT__%}' => form_text('admin_password',
				'Password',
				'',
				[
					'required' => TRUE,
					'type'     => 'password',
				]),
			'{%__BUTTON__%}'         => form_button('admin_login', 'Sign in', 'admin_login', ['class' => 'btn-dark w-100']),
		];
		
		echo strtr(admin_login(), $info);
	}
	
}
