<?php

namespace PHPFusion\Jupiter\Classes\View;

use PHPFusion\Jupiter\Classes\AdminHelper;

/*
 * Adminpanel
 */

class AdminPanel
{
	
	/* Helper object */
	private $helper;
	
	/* Constructs */
	public function __construct()
	{
		
		add_to_footer("<script src='" . ADMIN_THEMES . "Jupiter/pfjupiter.min.js?v=".
			filemtime(__DIR__.'/../../pfjupiter.min.js')."'></script>");
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
		require_once ADMIN_THEMES . 'Jupiter/templates/theme.tpl.php';
		
		$userdata = fusion_get_userdata();
		$settings = fusion_get_settings();
		
		$sitebanner = '';
		if ($sitebanner_path = $this->getSiteBannerPath()) {
			$sitebanner = '<img src="'.htmlspecialchars($sitebanner_path, ENT_QUOTES).'" alt="'.
				htmlspecialchars($settings['sitename'], ENT_QUOTES).'">';
		}
		
		$info = [
			'admin_pages'          => $this->helper->viewThemeAdminPages(),
			'admin_sections'       => $this->helper->viewThemeAdminSections(),
			'admin_avatar'         => display_avatar($userdata, '30px', 'pf-nav-avatar', FALSE, 'img-circle'),
			'userdata'             => $userdata,
			'admin_breadcrumbs'    => $this->helper->getAdminBreadcrumbs(),
			'settings'             => fusion_get_settings(),
			'sitebanner'           => $sitebanner,
			'profile_uri'          => BASEDIR . 'profile.php?lookup=' . $userdata['user_id'],
			'api_url'              => [
				'doc_uri'     => 'https://php-fusion.co.uk/docs/',
				'support_uri' => 'https://www.phpfusion.com/infusions/forum/index.php',
				'how_uri'     => 'https://www.phpfusion.com/infusions/wiki/index.php',
			],
			'signout_uri'          => FUSION_REQUEST.(str_contains(FUSION_REQUEST, '?') ? '&' : '?').'logout',
			'settings_uri'         => $this->helper->getSettingsURI(),
			'dashboard_uri'        => $this->helper->getDashboardURI(),
			'admin_notices'        => $this->helper->getAdminNotices(),
			'admin_buttons'        => fusion_filter_hook('pf_admin_buttons') ?? '',
			'admin_filters'        => fusion_filter_hook('pf_admin_filters')[0] ?? '',
			'admin_page_title'     => fusion_filter_hook('pf_admin_page_title')[0] ?? '',
			'admin_page_nav'       => fusion_filter_hook('pf_admin_left_nav')[0] ?? '', // this one will pass content
			'content'              => CONTENT,
			'main_width_class'     => fusion_filter_hook('pf_admin_full_width')[0] ?? '',
			'footer_errors'        => showfootererrors(),
		];

		admin_theme_tpl($info);
	}
	
	/* Admin login */
	public function viewLogin()
	{

		$userdata = fusion_get_userdata();
		
		add_to_jquery('$("#admin_password").focus();');
		add_to_head("
			<style>
			.lockscreen-wrapper {
				display: flex;
				flex-direction: column;
				gap:4rem;
				height:100%;
				width:100%;
				align-items:center;
				justify-content: center;
			}
			.lockscreen-wrapper .tw-card {width:50%; max-width:400px;}
			@media screen and (max-width:800px) {
				.lockscreen-wrapper .tw-card { width:80%; }
			}
			</style>
			");
		?>
		<div class="lockscreen-bg"></div>
		<div class="lockscreen-wrapper">
			<div class="tw-card">
				<div class="tw-card-body <?= framework_css('text-center') ?>">
					<div class="lockscreen-logo">
						<img src="<?= IMAGES ?>elite-x.avif" style="width:100px;" alt="Elite X">
					</div>
					<div class="tw-h1 <?= framework_css('mt-3') ?>">System Login</div>
					<?php
					$form_action = FUSION_SELF . fusion_get_aidlink() == ADMIN . 'index.php' . fusion_get_aidlink() . '&amp;pagenum=0'
						? FUSION_SELF . fusion_get_aidlink() . '&amp;pagenum=0'
						: FUSION_REQUEST;

					echo openform('admin-login-form', 'post', $form_action, ['class' => framework_css('lockscreen-credentials')]);
					echo form_text('admin_password', 'Administration Password', '', [
						//'placeholder' => $locale['ALT_007'],
						'type'     => 'password',
						'required' => TRUE,
						'floating' => TRUE,
						'class'    => framework_css('mb-3'),
						'ext_tip' => 'Your computer will explode after 3 attempts'
					]);
					?>
					<div id="admin-login-notice" class="tw-alert tw-alert-danger <?= framework_css('d-none text-start') ?>" role="alert"
						 aria-live="assertive"></div>
					<?php

					echo form_button('admin_login', "Login", 'admin_login', [
						'class' => 'tw-btn-primary ' . framework_css('w-100'),
					]);
					echo closeform();
					?>
					<div class="m-t-20 <?= framework_css('text-center') ?>">
						<a href="<?= BASEDIR . fusion_get_settings('opening_page') ?>">Back to homepage</a>
					</div>
				</div>
			</div>
			<div class="lockscreen-footer <?= framework_css('text-center') ?>">
				<div class="m-b-20 display-flex gap-15 <?= framework_css('justify-content-center') ?>">
					<a href="#">Help and feedback</a>
					<a href="#">Terms of use</a>
					<a href="#">Privacy and cookies</a>
				</div>
				<small>Sage Academy Sdn Bhd &copy; <?= date('Y') ?><br>Please make sure you are <strong>NOT</strong> using a public
					computer from this point onwards. Only log in from your trusted device only.</small>
			</div>
		</div>
		
		<?php
		$locale = fusion_get_locale();
		$endpoint = BASEDIR . 'api/index.php?api=admin-login';
		$generic_error = $locale['error_request'];
		$hidden_class = framework_css('d-none');
		add_to_jquery("
				const adminLoginForm = document.getElementById('admin-login-form');
				const adminLoginNotice = document.getElementById('admin-login-notice');
				const adminLoginHiddenClass = " . json_encode($hidden_class) . ";

				if (adminLoginForm && adminLoginNotice) {
					adminLoginForm.addEventListener('submit', async function (event) {
						event.preventDefault();

						if (adminLoginForm.dataset.submitting === 'true') {
							return;
						}

						const submitButton = adminLoginForm.querySelector('[name=\"admin_login\"]');
						const passwordInput = adminLoginForm.querySelector('[name=\"admin_password\"]');
						const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

						adminLoginForm.dataset.submitting = 'true';
						adminLoginNotice.classList.add(adminLoginHiddenClass);
						adminLoginNotice.replaceChildren();

						if (submitButton) {
							submitButton.disabled = true;
							submitButton.innerHTML = '<span class=\"jupiter-button-spinner\" aria-hidden=\"true\"></span>' + submitButton.textContent;
						}

						try {
							const response = await fetch(" . json_encode($endpoint) . ", {
								method: 'POST',
								body: new FormData(adminLoginForm),
								credentials: 'same-origin',
								headers: {
									'Accept': 'application/json',
									'X-Requested-With': 'XMLHttpRequest'
								}
							});
							const data = await response.json();

							if (!response.ok || !data.success) {
								const tokenInput = adminLoginForm.querySelector('[name=\"fusion_token\"]');
								if (tokenInput && data.token) {
									tokenInput.value = data.token;
								}

								throw new Error(data.message || " . json_encode($generic_error) . ");
							}

							if (!data.redirect) {
								throw new Error(" . json_encode($generic_error) . ");
							}

							window.location.assign(data.redirect);
						} catch (error) {
							adminLoginNotice.textContent = error.message || " . json_encode($generic_error) . ";
							adminLoginNotice.classList.remove(adminLoginHiddenClass);

							if (passwordInput) {
								passwordInput.value = '';
								passwordInput.focus();
							}
						} finally {
							delete adminLoginForm.dataset.submitting;

							if (submitButton) {
								submitButton.disabled = false;
								submitButton.innerHTML = originalButtonHtml;
							}
						}
					});
				}
			");
	}

	private function getSiteBannerPath(): string
	{
		$sitebanner = (string)fusion_get_settings('sitebanner');
		if ($sitebanner === '') {
			return '';
		}

		if (str_starts_with($sitebanner, 'images/')) {
			return IMAGES.substr($sitebanner, strlen('images/'));
		}

		if (!preg_match('#^(?:https?:)?//#', $sitebanner) &&
			!str_starts_with($sitebanner, '/') &&
			!str_starts_with($sitebanner, BASEDIR)) {
			return IMAGES.$sitebanner;
		}

		return $sitebanner;
	}
	
}
