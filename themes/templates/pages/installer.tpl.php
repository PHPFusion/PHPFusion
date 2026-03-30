<?php

use PHPFusion\OutputHandler;

function __base_layout($row)
{
	$locale = fusion_get_locale();
	
	fusion_apply_hook('fusion_header_include', $custom_file ?? '');
	
	$head_tags = OutputHandler::$pageHeadTags;
	$core_css_files = fusion_filter_hook("fusion_core_styles");
	if (is_array($core_css_files)) {
		$core_css_files = array_filter($core_css_files);
		foreach ($core_css_files as $css_file) {
			if (is_file($css_file)) {
				$script = fusion_load_script($css_file, "css", TRUE);
				$head_tags .= $script;
			}
		}
	}
	
	$html = "<!DOCTYPE html>";
	$html .= "<html lang='" . $locale['setup_0011'] . "' dir='" . $locale['setup_0012a'] . "'>";
	$html .= "<head>";
	$html .= "<title>" . (isset($_GET['upgrade']) ? $locale['setup_0020'] : $locale['setup_0000']) . "</title>";
	$html .= "<meta charset='" . $locale['setup_0012'] . "'>";
	$html .= '<link rel="shortcut icon" href="' . IMAGES . 'favicons/favicon.ico">';
	$html .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
	$html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />";
	$html .= "<script src='" . INCLUDES . "jquery/jquery.min.js'></script>";
	$html .= $head_tags;
	$html .= "<link rel='stylesheet' href='" . THEMES . "templates/default.min.css?v=" . filemtime(THEMES . 'templates/default.min.css') . "'>";
	$html .= "<link rel='stylesheet' href='" . THEMES . "templates/pages/assets/install.css'>";
	$html .= "<link rel='stylesheet' href='" . INCLUDES . "fonts/font-awesome-5/css/all.min.css'>";
	$html .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">';
	$html .= '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
			<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>';
	$html .= "</head><body" . (isset($_GET['upgrade']) ? " class='upgrade'" : '') . ">";
	$html .= '<div class="bg-blobs">
				<div class="blob blob-pink"></div>
				<div class="blob blob-cyan"></div>
				<div class="blob blob-amber"></div>
			</div>
			<div class="container d-flex align-items-center justify-content-center vh-100">
				<div class="os-window w-100 d-flex flex-column overflow-hidden" style="max-width:1000px;">
					
					<div class="os-titlebar d-flex align-items-center px-3 border-bottom border-white-50 flex-shrink-0 position-relative">
						<div class="d-flex gap-2 position-absolute start-0 ms-3">
							<div class="traffic-light" style="background: #FF5F56;"></div>
							<div class="traffic-light" style="background: #FFBD2E;"></div>
							<div class="traffic-light" style="background: #27C93F;"></div>
						</div>
						<div class="w-100 text-center small fw-medium select-none text-dark">
							<span class="fw-bold">Install PHPFusion</span> <span class="text-secondary">(Build ' . PHPFUSION_VERSION . ')</span>
						</div>
					</div>
					<div class="d-flex flex-grow-1 overflow-hidden">
						<div class="os-sidebar d-none d-md-flex flex-column p-4 flex-shrink-0">
							<div class="text-center">
							<img class="fusion-ico" alt="" src="' . IMAGES . 'phpfusion-icon.png">
							</div>
							<nav class="step-nav d-flex flex-column gap-2">' . __nav() . '</nav>
							' . __button($row['steps'], TRUE) . '
						</div>
			
						<div class="flex-grow-1 p-4 p-md-5 d-flex flex-column content-scroll overflow-auto">
							<div class="mx-auto w-100 pb-4" style="max-width:600px;">';
	
	if (defined('RECOVERY_CONSOLE')) :
		
		$html .= "<div class='recovery-container mx-auto' style='max-width: 700px;'>
					<div class='text-center mb-5'>
						<div class='d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 mb-3' style='width: 64px; height: 64px;'>
							<iconify-icon icon='solar:shield-warning-bold-duotone' class='text-danger fs-1'></iconify-icon>
						</div>
						<h2 class='fw-bold text-dark tracking-tight'>{%__TITLE__%}</h2>
						<p class='text-muted small'>{%__DESCRIPTION__%}</p>
					</div>
					" . openform('setupform', 'post', FUSION_SELF . '?localeset=' . LANGUAGE,
						[
							'class' => 'd-flex flex-column gap-4',
						]) . "
					{%__CONTENT__%}
					" . __button($row['steps']) . "
				";
	else:
		
		$html .= '<div class="d-flex align-items-center justify-content-center rounded-4 bg-white bg-opacity-50 border border-white mb-3 shadow-sm" style="width: 48px; height: 48px;">
					' . __step_icon() . '
					</div>
					<h1 class="h4 fw-bold text-dark mb-2 tracking-tight">{%__TITLE__%}</h1>
					<p class="small text-muted mb-5 fw-light leading-relaxed">{%__DESCRIPTION__%}</p>
					' . openform('setupform', 'post', FUSION_SELF . '?localeset=' . LANGUAGE,
				[
					'class' => 'd-flex flex-column gap-4',
				]) . '
					{%__CONTENT__%}
					' . __button($row['steps']);
	endif;
	
	$html .= closeform().'
							</div>
						</div>
					</div>
				</div>
			</div>';
	
	$fusion_jquery_tags = OutputHandler::$jqueryCode;
	if (!empty($fusion_jquery_tags)) {
		$html .= "<script>$(function() {
            let container = $('.block-container');
            let diff_height = container.height() - $('body').height();
            if (diff_height > 1) {
            container.css({ 'margin-top' : diff_height+'px', 'margin-bottom' : diff_height/2+'px' });
            }
            " . $fusion_jquery_tags . "
            });</script>";
	}
	
	$html .= OutputHandler::$pageFooterTags;
	
	fusion_filter_hook('fusion_footer_include');
	
	$html .= "</body>";
	$html .= "</html>";
	
	return $html;
}

function __nav()
{
	$locale = fusion_get_locale();
	$current_step = intval(INSTALLER_STEP);
	
	$steps = [
		1 => $locale['setup_0101'],  // introduction & license
		2 => $locale['setup_0102'], //system requirement
		3 => $locale['setup_0103'], // db settings
		4 => $locale['setup_1209'], // site configuration
		5 => $locale['setup_0104'], // primary user details
		6 => $locale['setup_0106'], // configure core system.
		7 => $locale['setup_1512'],
		8 => $locale['setup_0105'] // done
	];
	
	if (defined('RECOVERY_CONSOLE')) {
		$steps = [
			1 => $locale['setup_1002'],
		];
	}
	
	$html = '';
	foreach ($steps as $step_num => $step_title) {
		
		$is_active = ($current_step === $step_num);
		$is_completed = ($current_step > $step_num);
		
		if ($is_completed) {
			$html .= '<div class="d-flex align-items-center gap-3 p-2 rounded text-dark opacity-75">';
			$html .= ' <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 20px; height: 20px; font-size: 10px;">';
			$html .= '<iconify-icon icon="solar:check-read-linear"></iconify-icon>';
			$html .= '</div>';
		} elseif ($is_active) {
			$html .= '<div class="d-flex align-items-center gap-3 p-2 rounded bg-white bg-opacity-50 shadow-sm border border-white">';
			$html .= '<div class="rounded-circle bg-white border border-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 20px; height: 20px;">
                      	<div class="bg-primary rounded-circle" style="width: 6px; height: 6px;"></div>
                      </div>';
		} else {
			$html .= '<div class="d-flex align-items-center gap-3 p-2 text-muted opacity-50">';
			$html .= '<div class="rounded-circle border border-white bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 11px;">' . $step_num . '</div>';
		}
		
		$html .= '<span class="small">' . $step_title . '</span>
		</div>';
	}
	
	return $html;
}

function __step_icon()
{
	
	$steps_icons = [
		1 => 'solar:document-text-linear',
		2 => 'solar:shield-check-linear',
		3 => 'solar:database-linear',
		4 => 'solar:user-circle-linear',
		5 => 'solar:settings-minimalistic-linear',
		6 => 'solar:settings-minimalistic-linear',
		7 => 'solar:settings-minimalistic-linear',
		8 => 'solar:settings-minimalistic-linear',
	];
	
	if (isset($steps_icons[INSTALLER_STEP])) {
		return '<iconify-icon icon="' . $steps_icons[INSTALLER_STEP] . '" class="fs-4 text-primary"></iconify-icon>';
	}
	return '';
}

function __button($steps, $exit = FALSE)
{
	
	if ((string)INSTALLER_STEP == 6.1 && defined('RECOVERY_CONSOLE') && $exit) {
		
		$btn = openform('exitFrm', 'POST', FUSION_REQUEST, ['class' => 'w-100 mt-auto']);
		$btn .= '<button type="submit" name="reset" class="btn btn-macos-glass w-100 d-flex align-items-center" value="1">
						Back to Recovery Menu
				</button>';
		$btn .= closeform();
		return $btn;
	}
	
	elseif (INSTALLER_STEP == 8 && $exit == TRUE || defined('RECOVERY_CONSOLE') && $exit) {
		if (INSTALLER_STEP == 1) {
			$btn = openform('exitFrm', 'POST', FUSION_REQUEST, ['class' => 'w-100']);
			$btn .= '<button type="submit" name="reset" class="btn btn-macos-glass w-100 d-flex align-items-center" value="9">
					<iconify-icon icon="solar:logout-linear" class="fs-3 me-2" style="font-size:1.15rem !important;"></iconify-icon>
						Finish and Exit
					</button>';
			$btn .= closeform();
		} else {
			$btn = openform('exitFrm', 'POST', FUSION_REQUEST, ['class' => 'w-100 mt-auto']);
			$btn .= '<button type="submit" name="reset" class="btn btn-macos-glass w-100 d-flex align-items-center" value="1">
						Back to Recovery Menu
				</button>';
			$btn .= closeform();
		}

		
	} elseif (!empty($steps) && INSTALLER_STEP > 1 && INSTALLER_STEP < 8 && !defined('RECOVERY_CONSOLE')) {
		
		$btn = '<button type="submit" name="reset" class="btn btn-macos-glass" value="' . (INSTALLER_STEP - 1) . '">Go Back</button>';
	}
	
	if (!empty($steps) && INSTALLER_STEP < 8 || (defined('RECOVERY_CONSOLE') && !empty($steps) && !$exit)) {
		
		$btn = '<span></span>';
		foreach ($steps as $button_prop) :
			$btn .= '<button id="' . $button_prop['name'] . '" type="submit" name="' . $button_prop['name'] . '" value="' . $button_prop['value'] . '" class="btn btn-macos-primary">
				Continue
				<iconify-icon icon="solar:alt-arrow-right-linear" style="font-size: 1rem;"></iconify-icon>
			</button>';
		endforeach;
	}
	
	if (!empty($btn)) :
		return '<div class="mt-auto pt-4 d-flex justify-content-between align-items-center w-100 mx-auto" style="max-width:600px;">' . $btn . '</div>';
	endif;

//	}
	return '';
	
}


