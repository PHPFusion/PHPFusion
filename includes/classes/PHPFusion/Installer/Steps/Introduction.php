<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Introduction.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Installer\Steps;

use PHPFusion\Installer\InstallCore;
use PHPFusion\Installer\Requirements;

/**
 * Class Introduction
 *
 * @package PHPFusion\Steps
 */
class Introduction extends InstallCore
{
	
	/**
	 * @return string
	 */
	public function view()
	{
		
		if ($mode = $this->recovery()) {
			
			return $mode;
			
		} else if ($mode = $this->index()) {
			
			return $mode;
		}
		return "";
	}
	
	/**
	 * @return string[]
	 */
	public function recovery()
	{
		
		// Reset connection session if any during the initialization step.
		session_remove('db_config_connection');
		
		if (self::$connection = self::fusionGetConfig(BASEDIR . 'config_temp.php')) {
			
			$validation = Requirements::getSystemValidation();
			
			if ($current_version = fusion_get_settings('version')) {
				
				if (isset($validation[3])) {
					
					if (version_compare(self::BUILD_VERSION, $current_version, ">")) {
						
						return $this->stepUpgrade();
						
					} else {
						
						return $this->recoveryConsole();
					}
				}
				die("Not a valid Super Administrator");
				
			} else {
				die("No table to upgrade or recover from");
			}
		}
		
		return [];
		
	}
	
	/**
	 * @return string
	 */
	private function stepUpgrade()
	{
		/*
		 * Here we already have a working database, but config is not done so there will be errors.
		 * Now I've already cured the config_temp.php to PF9 standard config_temp.php
		 * All we need to do left is checking on the system, so we'll send to start with STEP2
		 */
		$_GET['upgrade'] = TRUE;
		$_POST['license'] = TRUE;
		$this->installerStep(self::STEP_INTRO);
		
		return $this->index();
	}
	
	/**
	 * @return array
	 */
	private function index()
	{
		
		if (post('step') == 1) {
			
			if (check_post('license')) {
				session_set('installer_step', self::STEP_PERMISSIONS);
				redirect(FUSION_SELF . "?localeset=" . LANGUAGE . '&step=' . self::STEP_PERMISSIONS);
			} else {
				session_set('installer_step', self::STEP_INTRO);
				redirect(FUSION_SELF . "?error=license&localeset=" . LANGUAGE);
			}
		}
		
		$content = form_select('localeset', self::$locale['setup_1000'], LANGUAGE,
			[
				'options'          => self::$locale_files,
				'select2_disabled' => TRUE,
			]
		);
		
		if (get('error') == 'license') {
			$content .= "<div class='alert alert-danger'>" . self::$locale['setup_5000'] . "</div>\n";
		}
		
		$content .= '<div class="mb-4">
        <label class="form-label small fw-semibold text-dark mb-2 ms-1 opacity-75">Terms of Service</label>
        <textarea class="form-control os-input content-scroll" rows="6" readonly style="font-size: 13px; line-height: 1.6; resize: none;">
		' . file_get_contents(BASEDIR . 'LICENSE') . '
        </textarea>
    </div>';
		
		$content .= form_checkbox('license', self::$locale['setup_0005'], '',
			[
				'class'       => 'p-0',
				'label_class' => 'form-label text-dark',
				'required'    => TRUE,
				'error_text'  => self::$locale['setup_5000'],
			]
		);
		
		
		add_to_jquery('
		$("button[name=\"step\"]").attr("disabled", true);
		$("#license").on("click", function() {
			if ($(this).is(":checked")) {
				$("button[name=\"step\"]").attr("disabled", false);
			} else {
				$("button[name=\"step\"]").attr("disabled", true);
			}
		});
		
		$("#localeset").bind("change", function() {
			var value = $(this).val();
			document.location.href="' . FUSION_SELF . '?localeset="+value;
		});
        ');
		
		self::$step = [
			1 => [
				'name'  => 'step',
				'label' => self::$locale['setup_0121'],
				'value' => self::STEP_INTRO,
			],
		];
		
		return [
			'title'       => check_get('upgrade') ? self::$locale['setup_0022'] : self::$locale['setup_0002'],
			'description' => "<p>" . (check_get('upgrade') ? self::$locale['setup_0023'] : self::$locale['setup_0003']) . "</p>" .
				"<p>" . self::$locale['setup_1001'] . "</p>",
			'content'     => $content,
		];
		
	}
	
	/**
	 * @return string[]
	 */
	private function recoveryConsole()
	{
		define('RECOVERY_CONSOLE', true);
		
		if (check_post("htaccess")) {
			
			require_once(INCLUDES . 'htaccess_include.php');
			write_htaccess();
			addnotice('success', self::$locale['setup_1020']);
			$this->installerStep(self::STEP_INTRO);
			redirect(FUSION_SELF . "?localeset=" . LANGUAGE);
			
		}
		
		if (check_post("uninstall")) {
			$coretables = \PHPFusion\Installer\Lib\CoreTables::get_core_tables(self::$localeset);
			$i = 0;
			foreach (array_keys($coretables) as $table) {
				$result = dbquery("DROP TABLE IF EXISTS " . self::$connection['db_prefix'] . $table);
				if ($result) {
					$i++;
					usleep(600);
					//continue;
				}
			}
			@unlink(BASEDIR . 'config_temp.php');
			@unlink(BASEDIR . 'config.php');
			@unlink(BASEDIR . '.htaccess');
			// go back to the installer
			$_SESSION['step'] = self::STEP_INTRO;
			addnotice('danger', "<strong>" . self::$locale['setup_0125'] . "</strong>");
			$content .= rendernotices(getnotices());
			if ($i == count($coretables)) {
				redirect(filter_input(INPUT_SERVER, 'REQUEST_URI'), 6);
			}
		} else {

			$content = "<div class='d-flex flex-column gap-3'>";

			// --- ACTION ITEM: Primary Admin (Primary Utility) ---
			$content .= self::renderRecoveryCard(
				'solar:user-speak-rounded-linear',
				self::$locale['setup_1011'],
				self::$locale['setup_1012'],
				form_button('step', self::$locale['setup_1013'], self::STEP_TRANSFER, ['class' => 'btn-macos-primary btn-sm'])
			);

			// --- ACTION ITEM: Infusions (Core System) ---
			$content .= self::renderRecoveryCard(
				'solar:box-minimalistic-linear',
				self::$locale['setup_1008'],
				self::$locale['setup_1009'],
				form_button('step', self::$locale['setup_1010'], self::STEP_INFUSIONS, ['class' => 'btn-macos-primary btn-sm'])
			);

			// --- ACTION ITEM: Rebuild .htaccess ---
			if (isset(self::$connection['db_prefix'])) {
				$content .= self::renderRecoveryCard(
					'solar:document-text-linear',
					self::$locale['setup_1014'],
					self::$locale['setup_1015'],
					form_button('htaccess', self::$locale['setup_1014'], 'htaccess', ['class' => 'btn-macos-primary btn-sm'])
				);
			}

			// --- ACTION ITEM: Exit (Safe Action) ---
//			$content .= self::renderRecoveryCard(
//				'solar:logout-linear',
//				self::$locale['setup_1017'],
//				self::$locale['setup_1018'],
//				form_button('step', self::$locale['setup_1019'], self::STEP_EXIT, ['class' => 'btn-macos-glass text-success btn-sm']),
//				'border-success'
//			);

			// --- ACTION ITEM: Uninstall (Danger Zone) ---
			$content .= "<div class='flex-grow-0 p-3 p-3 transition-all border-opacity-25'>
				<div class='d-flex align-items-start gap-3'>
					<div class='text-danger pt-1'><iconify-icon icon='solar:bomb-minimalistic-linear' class='fs-3'></iconify-icon></div>
					<div class='flex-grow-1'>
						<h6 class='fw-bold text-danger mb-1'>".self::$locale['setup_1004']."</h6>
						<p class='small text-muted mb-3'>".self::$locale['setup_1005']."</p>
						<div class='p-2 mb-3'>
							<p class='small text-danger m-0 fw-medium'><iconify-icon icon='solar:danger-triangle-linear' class='me-1'></iconify-icon> ".self::$locale['setup_1006']."</p>
						</div>
						
						".form_button('uninstall', self::$locale['setup_1007'], 'uninstall', [

							'class' => 'btn-macos-glass',
							'iconify' => 'solar:trash-bin-trash-bold'
						])."
					</div>
				</div>
			</div>";
			
			$content .= "</div>"; // End gap-3
			$content .= "</div>"; // End container
		}
		
		return [
			'title'       => self::$locale['setup_1002'],
			'description' => self::$locale['setup_1003'],
			'content'     => $content,
		];
	}
	
	private static function renderRecoveryCard($icon, $title, $desc, $button, $extraClass = '') {
		return "
    <div class='p-3 transition-all $extraClass'>
        <div class='d-flex align-items-center justify-content-between gap-3'>
            <div class='d-flex align-items-center gap-3'>
                <div class='rounded-circle bg-white bg-opacity-50 d-flex align-items-center justify-content-center border border-white' style='min-width:42px;width: 42px; height: 42px;'>
                    <iconify-icon icon='$icon' class='text-primary fs-4'></iconify-icon>
                </div>
                <div>
                    <h6 class='fw-bold m-0 text-dark'>$title</h6>
                    <p class='small text-muted m-0 opacity-75'>$desc</p>
                </div>
            </div>
            <div class='flex-shrink-0'>
                $button
            </div>
        </div>
    </div>";
	}
}
