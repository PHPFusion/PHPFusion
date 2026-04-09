<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_main.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');

$settings = fusion_get_settings();

fusion_load_script(ADMIN_THEMES.'Pro/gsap.min.js');

$contents = [
    'post'        => '',
    'view'        => 'pf_view',
    'button'      => '',
    'js'          => 'pf_js',
    'link'        => ($admin_link ?? ''),
    'title'       => 'Page Error',
    'description' => '',
    'actions'     => []
];

function pf_view() {

    echo '<div class="text-center">';
    echo '<svg id="mainSVG" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600">
	<defs><path id="gridBox" d="M281,328.39l30-109.16M518.73,328.89,281,328.39m30-109.16H489m0,0,29.74,109.66" fill="none" stroke-miterlimit="10" stroke-width="0.75" />
    <filter id="glow" x="-100%" y="-100%" width="250%" height="250%">
			<feGaussianBlur stdDeviation="20" result="coloredBlur" />
			<feOffset dx="0" dy="0" result="offsetblur"></feOffset>
			<feFlood id="glowAlpha" flood-color="#3F21E9" flood-opacity="1"></feFlood>
			<feComposite in2="offsetblur" operator="in"></feComposite>
			<feMerge>
				<feMergeNode />
				<feMergeNode in="SourceGraphic"></feMergeNode>
			</feMerge>
		</filter>
		<filter id="glow2" x="-100%" y="-100%" width="250%" height="250%">
			<feGaussianBlur stdDeviation="12" result="coloredBlur" />
			<feOffset dx="0" dy="0" result="offsetblur"></feOffset>
			<feFlood id="glowAlpha" flood-color="#3F21E9" flood-opacity="1"></feFlood>
			<feComposite in2="offsetblur" operator="in"></feComposite>
			<feMerge>
				<feMergeNode />
				<feMergeNode in="SourceGraphic"></feMergeNode>
			</feMerge>
		</filter>
		<filter id="goo" color-interpolation-filters="sRGB">
			<feGaussianBlur in="SourceGraphic" stdDeviation="5" result="blur" />
			<feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 100 -9" result="cm" />
			<feBlend />
		</filter>
		<filter id="bevel" color-interpolation-filters="sRGB" filterUnits="objectBoundingBox" x="-10%" y="-10%" width="150%" height="150%">
			<feGaussianBlur in="SourceAlpha" stdDeviation="3" result="blur" />
			<feSpecularLighting in="blur" surfaceScale="5" specularConstant="0.75" specularExponent="80" result="specOut" lighting-color="#4356D7">
				<fePointLight x="-5000" y="10000" z="10000" />
			</feSpecularLighting>
			<feComposite in="specOut" in2="SourceAlpha" operator="in" result="specOut2" />
			<feComposite in="SourceGraphic" in2="specOut2" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litPaint" />
		</filter>
		<g id="container" filter="url(#goo)" />
		<radialGradient id="blobGrad" cx="397.6124" cy="190" r="22.6488" gradientUnits="userSpaceOnUse">
			<stop offset="0.4" style="stop-color:#FFF;" />
			<stop offset="1" style="stop-color:#591520;" />
		</radialGradient>
		<linearGradient id="refGrad" x1="400" y1="393.43" x2="400" y2="274.67" gradientUnits="userSpaceOnUse">
			<stop offset="0" />
			<stop offset="0.5" stop-color="#fff" />
		</linearGradient>
	</defs>
	<mask id="blobRefMask">
		<rect class="refGradRect" y="204.67" width="800" height="288.76" fill="url(#refGrad)" />
	</mask>
	<g fill="#4356D7" mask="url(#blobRefMask)">
		<use xlink:href="#container" id="reflection" filter="url(#bevel)" />
	</g>
	<g filter="url(#glow2)">
		<g filter="url(#glow)" stroke="#4A63F5" stroke-miterlimit="10" fill="none" stroke-width="2">
			<use class="gridBox" xlink:href="#gridBox" y="50" opacity="0.3" />
			<use class="gridBox" xlink:href="#gridBox" y="20" opacity="0.6" />
			<use class="gridBox" xlink:href="#gridBox" />
			<path id="ring" stroke="none" d="M397.61,281.93a39.25,39.25,0,0,1-8.35-.88,27.13,27.13,0,0,1-6.72-2.35,13.46,13.46,0,0,1-4.38-3.43,6,6,0,0,1-1.4-4.11,6.54,6.54,0,0,1,1.87-4A14.76,14.76,0,0,1,383.2,264a28.44,28.44,0,0,1,6.53-2.12,40.19,40.19,0,0,1,15.76,0A28.53,28.53,0,0,1,412,264a14.83,14.83,0,0,1,4.56,3.21,6.56,6.56,0,0,1,1.88,4,6.06,6.06,0,0,1-1.41,4.11,13.42,13.42,0,0,1-4.37,3.43,27.36,27.36,0,0,1-6.73,2.35,39.25,39.25,0,0,1-8.35.88m0-35.5a96.57,96.57,0,0,0-19.05,1.85,69.64,69.64,0,0,0-15.86,5.12,36.54,36.54,0,0,0-11.15,7.73,16.24,16.24,0,0,0-4.7,9.72,14.56,14.56,0,0,0,3.27,10,32.23,32.23,0,0,0,10.59,8.43,66.06,66.06,0,0,0,16.42,5.8,98.17,98.17,0,0,0,41,0,66.06,66.06,0,0,0,16.42-5.8,32.13,32.13,0,0,0,10.59-8.43,14.46,14.46,0,0,0,3.27-10,16.22,16.22,0,0,0-4.69-9.72,36.72,36.72,0,0,0-11.16-7.73,69.48,69.48,0,0,0-15.86-5.12,96.49,96.49,0,0,0-19.05-1.85" fill="#DFE3FF" />
		</g>
	</g><g id="wrapper" filter="url(#bevel)" opacity="0.76">
		<use xlink:href="#container" fill="url(#blobGrad)" />
	</g></svg>';
    echo '<h4 class="strong text-white">You have found our code making secret. The coffee brewing machine.</h4>';
    echo 'Anyway, you are not suppose to be here. and certainly not in the correct place.';
    echo '</div>';

}

function pf_js() {
    return 'animate_fusionpro404();';
}
