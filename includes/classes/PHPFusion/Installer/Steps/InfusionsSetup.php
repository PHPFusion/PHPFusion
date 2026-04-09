<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: InfusionsSetup.php
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

use PHPFusion\Installer\Infusions;
use PHPFusion\Installer\InstallCore;
use PHPFusion\Installer\Requirements;

class InfusionsSetup extends InstallCore {
    /**
     * @return array
     */
    public function view() {
        self::$connection = self::fusionGetConfig(BASEDIR.'config_temp.php');
		
        require_once(INCLUDES.'multisite_include.php');
        require_once(INCLUDES.'infusions_include.php');

        $validation = Requirements::getSystemValidation();

        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");
        
        if (isset($validation[3])) {
            if ($this->tableCheck()) {
                /*
                 * Use DB superadmin password.
                 */
                $userdata = fusion_get_user(1);
                $settings = fusion_get_settings();

                // User level, Admin Rights & User Group definitions
                define("iGUEST", $userdata['user_level'] == USER_LEVEL_PUBLIC ? 1 : 0);
                if (!defined('iMEMBER')) {
                    define("iMEMBER", $userdata['user_level'] <= USER_LEVEL_MEMBER ? 1 : 0);
                }
                define("iADMIN", $userdata['user_level'] <= USER_LEVEL_ADMIN ? 1 : 0);
                define("iSUPERADMIN", $userdata['user_level'] == USER_LEVEL_SUPER_ADMIN ? 1 : 0);
                define("iUSER", $userdata['user_level']);
                define("iUSER_RIGHTS", $userdata['user_rights']);
                define("iUSER_GROUPS", substr($userdata['user_groups'], 1));
                // Get enabled language settings
                //$enabled_languages = array_keys(fusion_get_enabled_languages());
                // If language change is initiated and if the selected language is valid
                if (isset($_GET['lang']) && valid_language($_GET['lang'])) {
                    $lang = stripinput($_GET['lang']);
                    set_language($lang);
                    $redirectPath = clean_request("", ["lang"], FALSE);
                    redirect($redirectPath);
                }

                // Main language detection procedure
                if (iMEMBER && valid_language($userdata['user_language'])) {
                    if (!defined('LANGUAGE')) {
                        define("LANGUAGE", $userdata['user_language']);
                        define("LOCALESET", $userdata['user_language']."/");
                    }
                } else {
                    $data = dbarray(dbquery("SELECT * FROM ".DB_LANGUAGE_SESSIONS." WHERE user_ip='".USER_IP."'"));
                    if (!empty($data['user_language'])) {
                        if (!defined('LANGUAGE')) {
                            define("LANGUAGE", $data['user_language']);
                            define("LOCALESET", $data['user_language']."/");
                        }
                    }
                }
                // Check if definitions have been set, if not set the default language to system language
                if (!defined("LANGUAGE") && !defined('LOCALESET')) {
                    define("LANGUAGE", $settings['locale']);
                    define("LOCALESET", $settings['locale']."/");
                }

                add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['INF_412']."');});");

                $inf_core = Infusions::getInstance();
                $inf_core::loadConfiguration();
                if (($folder = filter_input(INPUT_POST, 'infuse'))) {
                    $inf_core->infuse($folder);
                } else if ($folder = filter_input(INPUT_POST, 'defuse')) {
                    $inf_core->defuse($folder);
                }
                $content = "";
                $temp = opendir(INFUSIONS);
                $infs = [];
                while ($folder = readdir($temp)) {
                    if (!in_array($folder, ["..", "."]) && ($inf = Infusions::loadInfusion($folder))) {
                        $infs[] = $inf;
                    }
                }
                closedir($temp);
             
                $content = rendernotices(getnotices());
				if ($infs) {
					// row-cols-1: 1 card on mobile
					// row-cols-md-2: 2 cards on tablets/desktop
					// g-4: standard spacing between cards
					$content .= "<div class='row row-cols-1 row-cols-md-2 g-4'>\n";
		
					sort($infs);
					foreach ($infs as $inf) {
						$status_label = ($inf['status'] > 0 ? "bg-success" : "bg-secondary");
						$status_text = ($inf['status'] > 0 ? $locale['INF_415'] : $locale['INF_414']);
			
						$content .= "<div class='col'>\n";
						// .os-window for the glass blur effect
						$content .= "    <div class='os-window h-100 d-flex flex-column p-4'>\n";
			
						// Header
						$content .= "        <div class='d-flex justify-content-between align-items-center mb-3'>\n";
						$content .= "            <div>\n";
						$content .= "                <h5 class='fw-bold text-dark m-0'>" . $inf['name'] . "</h5>\n";
						$content .= "                <small class='text-muted opacity-75'>" . (!empty($inf['version']) ? "Version ".$inf['version'] : 'v1.0') . "</small>\n";
						$content .= "            </div>\n";
						$content .= "            <span class='badge ".$status_label." bg-opacity-10 text-dark border border-white border-opacity-50 px-3 py-2' style='font-size: 11px; backdrop-filter: blur(4px);'>" . $status_text . "</span>\n";
						$content .= "        </div>\n";
			
						// Description - Flexible height
						$content .= "        <p class='small text-muted mb-4 flex-grow-1' style='line-height: 1.6;'>" . trimlink($inf['description'], 120) . "</p>\n";
			
						// Info List (Developer/Email)
						$content .= "        <div class='mt-auto'>\n";
						$content .= "            <div class='d-flex align-items-center gap-2 mb-3'>\n";
						$content .= "                <div class='rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center' style='width: 32px; height: 32px;'>\n";
						$content .= "                    <iconify-icon icon='solar:user-circle-linear' class='text-primary'></iconify-icon>";
						$content .= "                </div>\n";
						$content .= "                <div class='small'>\n";
						$content .= "                    <span class='d-block fw-semibold text-dark'>" . (!empty($inf['developer']) ? $inf['developer'] : $locale['INF_410']) . "</span>\n";
						$content .= "                    " . ($inf['url'] ? "<a href='".$inf['url']."' target='_blank' class='text-primary text-decoration-none' style='font-size: 10px;'>Visit Website</a>" : "") . "\n";
						$content .= "                </div>\n";
						$content .= "            </div>\n";
			
						// Action Buttons
						if ($inf['status'] > 0) {
							if ($inf['status'] > 1) {
								$content .= form_button('infuse', $locale['INF_416'], $inf['folder'],
									['class' => 'btn-macos-primary w-100 infuse', 'icon' => 'solar:box-minimalistic-linear']);
							} else {
								$content .= form_button('defuse', $locale['INF_411'], $inf['folder'],
									['class' => 'btn-macos-glass w-100 text-danger defuse', 'icon' => 'solar:trash-bin-trash-linear']);
							}
						} else {
							$content .= form_button('infuse', $locale['INF_401'], $inf['folder'],
								['class' => 'btn-macos-primary w-100 infuse', 'icon' => 'solar:download-square-linear']);
						}
						$content .= "        </div>\n";
						$content .= "    </div>\n"; // End Card
						$content .= "</div>\n"; // End Col
					}
					$content .= "</div>\n"; // End Row
				} else {
					$content .= "<div class='os-window p-5 text-center'><p class='text-muted'>".$locale['INF_417']."</p></div>\n";
				}
                
                self::$step = [
                    1 => [
                        'class' => 'pull-right btn-success',
                        'name'  => 'step',
                        'label' => self::$locale['setup_0120'],
                        'value' => self::STEP_EXIT
                    ],
                ];
	
				return [
					'title' => $locale['setup_0105'],
					'description' => '',
					'content' => $content
				];

            } else {
                redirect(FUSION_REQUEST);
            }
        }

        
    
    }
}
