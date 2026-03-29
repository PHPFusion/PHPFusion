<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Permissions.php
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

class Permissions extends InstallCore {
 
	/**
     * @return array
     */
    public function view() {

    	if (check_post('reset')) {
    		session_set('installer_step', self::STEP_INTRO);
    		redirect(FUSION_REQUEST);
		}
	
		$content = '<div class="mb-0">
        <div class="rounded-3 overflow-hidden border border-white border-opacity-50 shadow-sm">
            <table class="table table-borderless mb-0" style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(5px);">
                <thead class="bg-white bg-opacity-25 border-bottom border-white border-opacity-50">
                    <tr>
                        <th class="small fw-bold px-3 py-2">Requirement</th>
                        <th class="small fw-bold text-end px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="small">
				';
	
	    $system_health = 10;
        $system_requirements = Requirements::getSystemRequirements();
        
        foreach ($system_requirements as $test) {
         
        	// Depending on the severability, anything more than red, we cannot continue further
        	$status_css = '';
        	$status = '<span class="text-success d-inline-flex align-items-center gap-1 text-end">
					<iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon>
					Pass
					</span>';
        	
            if (isset($test['severability'])) {
                $system_health = $system_health - intval($test['severability']);
				$status_css = $test['severability'] > 5 ? 'bg-alert' : 'bg-warning';
                $status = '<span class="d-inline-flex align-items-center gap-1 cursor-help"
							  style="cursor:help;"
							  data-bs-toggle="tooltip"
							  data-bs-html="true"
							  data-bs-placement="top"
							  title="<b>Problem:</b>'.$test['description'].'">
							<iconify-icon icon="solar:danger-circle-bold" class="me-1"></iconify-icon> Failed</span>';
            }
            
			$subcontent = '';
			if (!empty($test['sub'])) {
				
                $warned_content = '';
                $checked_status = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1">All Writable</span>';
                foreach ($test['sub'] as $key => $value) {

                        if (isset($value['severability'])) :
							$sub_class = 'text-danger';
							$value = '<iconify-icon icon="solar:danger-circle-linear" class="me-1"></iconify-icon> Read-Only';
                        else:
							$value = '<iconify-icon icon="solar:check-read-linear" class="me-1"></iconify-icon> Writable';
                        	$sub_class = 'text-secondary';
						endif;
						
                        $warned_content .= '<tr class="border-bottom border-white border-opacity-10">
                            <td class="py-1 text-muted">'.$key.'</td>
                            <td class="py-1 text-end '.$sub_class.' fw-medium">'.$value.'</td>
                        </tr>';
                        
                        $checked_status = '<span class="badge bg-danger-subtle bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1">Problems</span>';
                }
                
				$subcontent .= '<tr class="border-bottom border-white border-opacity-25">
					<td colspan="2" class="p-0">
						<div class="px-3 py-3">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<span class="text-dark fw-medium">Directory & File Permissions</span>
								'.$checked_status.'
							</div>
							<div class="rounded-3 overflow-hidden border border-white border-opacity-25 mt-2">
								<table class="table table-sm mb-0" style="background: rgba(255, 255, 255, 0.1); font-size: 12px;">
									<tbody>
										'.$warned_content.'
									</tbody>
								</table>
							</div>
						</div>
					</td>
				</tr>';
			}
			
            $content .= '<tr class="border-bottom border-white border-opacity-25">
                    <td class="px-3 py-2 text-dark opacity-75">'.$test['title'].'</td>
                    <td class="px-3 py-2 fw-semibold text-end '.$status_css.'">'.$status.'</td>
                    </tr>';
			$content .= $subcontent;
            
        }
        
        $content .= '</tbody></table></div></div>';
        
        // can proceed
        if ($system_health > 6) {
            self::$step = [
                1 => [
                    'name'  => 'step',
                    'label' => self::$locale['setup_0121'],
                    'value' => self::STEP_DB_SETTINGS_FORM
                ]
            ];
        } else {
            self::$step = [
                1 => [
                    'name'  => 'step',
                    'type'  => 'tryagain',
                    'label' => self::$locale['setup_0122'],
                    'value' => self::STEP_DB_SETTINGS_FORM
                ]
            ];
            $content .= form_hidden('license', '', '1');
        }
		?>
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			// Initialize all elements with data-bs-toggle="tooltip"
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl)
			})
		});
		</script>
		<?php
		
        return [
			'title'       => self::$locale['setup_1106'],
			'description' => '',
			'content'     => $content,
		];
    
    }
}
