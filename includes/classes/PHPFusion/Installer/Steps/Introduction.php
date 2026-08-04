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
class Introduction extends InstallCore {

    /**
     * @return string
     */
    public function view() {
        if ($mode = $this->recovery()) {
            return $mode;
        } else if ($mode = $this->index()) {
            return $mode;
        }
        return "";
    }

    /**
     * @return string
     */
    public function recovery() {

        // Reset connection session if any during the initialization step.
        session_remove("db_config_connection");

        if (self::$connection = self::fusionGetConfig(BASEDIR.'config_temp.php')) {
            $validation = Requirements::getSystemValidation();
            $current_version = fusion_get_settings('version');
            if (!empty($current_version)) {
                if (isset($validation[3]) && !empty($validation[3]['result'])) {
                    if (version_compare(self::BUILD_VERSION, $current_version, ">")) {
                        if (self::isRecoveryMode()) {
                            return $this->recoveryConsole((string)$current_version, TRUE);
                        }
                        return $this->stepUpgrade();
                    }

                    self::setInstallerMode(self::MODE_RECOVERY);
                    return $this->recoveryConsole((string)$current_version, FALSE);
                }
            }

            if (self::isRecoveryMode()) {
                return $this->recoveryUnavailable();
            }
        }

        if (self::isRecoveryMode()) {
            return $this->recoveryUnavailable();
        }

        return FALSE;

    }

    /**
     * @return string
     */
    private function stepUpgrade() {
        /*
         * Here we already have a working database, but config is not done so there will be errors.
         * Now I've already cured the config_temp.php to PF9 standard config_temp.php
         * All we need to do left is checking on the system, so we'll send to start with STEP2
         */
        $_GET['upgrade'] = TRUE;
        $_POST['license'] = TRUE;
        self::setInstallerMode(self::MODE_UPGRADE);
        $this->installerStep(self::STEP_INTRO);
        return $this->index();
    }

    /**
     * @return string
     */
    private function index() {

        if (isset($_POST['step']) && $_POST['step'] == 1) {
            if (isset($_POST['license'])) {
                $_SESSION['step'] = self::STEP_PERMISSIONS;
                redirect(self::installerUrl());
            } else {
                redirect(self::installerUrl(['error' => 'license']));
            }
        }

        $content = "<h2 class='title'>".(isset($_GET['upgrade']) ? self::$locale['setup_0022'] : self::$locale['setup_0002'])."</h2>\n";
        $content .= "<p>".(isset($_GET['upgrade']) ? self::$locale['setup_0023'] : self::$locale['setup_0003'])."</p>\n";
        $content .= "<p>".self::$locale['setup_1001']."</p>\n";
        $content .= "<hr/>";

        $content .= "<h3>".self::$locale['setup_1000']."</h3>\n";
        $content .= form_select('localeset', '', LANGUAGE,
            [
                'options' => self::$locale_files,
            ]
        );
        if (isset($_GET['error']) && $_GET['error'] == 'license') {
            $content .= "<div class='alert alert-danger'>".self::$locale['setup_5000']."</div>\n";
        }
        $content .= form_checkbox('license', self::$locale['setup_0005'], '',
            [
                'reverse_label' => TRUE,
                'required'      => TRUE,
                'error_text'    => self::$locale['setup_5000']
            ]
        );

        add_to_jquery('
            const $license = $("#license");
            const syncLicenseState = function() {
                $("#step").prop("disabled", !$license.is(":checked"));
            };
            $license.on("change", syncLicenseState);
            syncLicenseState();
        ');

        add_to_jquery("
        $('#localeset').bind('change', function() {
            var value = $(this).val();
            document.location.href='".FUSION_SELF."?mode=".rawurlencode(self::getInstallerMode())."&localeset='+encodeURIComponent(value);
        });
        ");

        self::$step = [
            1 => [
                'name'  => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_INTRO
            ]
        ];

        return $content;
    }

    /**
     * @return string
     */
    private function recoveryConsole(string $current_version, bool $upgrade_available) {
        self::setInstallerMode(self::MODE_RECOVERY);

        $action = check_post('recovery_action') ? (string)post('recovery_action') : '';
        if ($action === 'resume') {
            $_SESSION['installer_recover_current_upgrade'] = !$upgrade_available;
            self::installerStep(self::STEP_PERMISSIONS);
            redirect(self::installerUrl(['session' => self::STEP_PERMISSIONS]));
        }

        if ($action === 'htaccess' && fusion_safe()) {
            require_once(INCLUDES.'htaccess_include.php');
            write_htaccess();
            addnotice('success', self::$locale['setup_1020']);
            self::installerStep(self::STEP_INTRO);
            redirect(self::installerUrl());
        }

        if ($action === 'reset') {
            if (!check_post('confirm_reset') || !fusion_safe()) {
                addnotice('danger', self::$locale['setup_1048']);
            } else {
                $coretables = \PHPFusion\Installer\Lib\CoreTables::get_core_tables(self::$localeset);
                $dropped = 0;
                foreach (array_keys($coretables) as $table) {
                    if (dbquery("DROP TABLE IF EXISTS ".self::$connection['db_prefix'].$table)) {
                        $dropped++;
                    }
                }
                if ($dropped === count($coretables)) {
                    @unlink(BASEDIR.'config_temp.php');
                    @unlink(BASEDIR.'config.php');
                    @unlink(BASEDIR.'.htaccess');
                    unset($_SESSION['installer_recover_current_upgrade']);
                    self::setInstallerMode(self::MODE_INSTALL);
                    self::installerStep(self::STEP_INTRO);
                    redirect(self::installerUrl([
                        'mode' => self::MODE_INSTALL,
                        'session' => self::STEP_INTRO,
                    ]));
                }
                addnotice('danger', self::$locale['setup_1049']);
            }
        }

        $maintenance_active = is_file(BASEDIR.'.maintenance');
        $config_ready = is_readable(BASEDIR.'config_temp.php');
        $log_files = glob(BASEDIR.'installer_*.errors.log') ?: [];
        usort($log_files, static function (string $left, string $right): int {
            return filemtime($left) <=> filemtime($right);
        });
        $latest_log = $log_files ? basename((string)end($log_files)) : self::$locale['setup_1039'];

        $content = '<div class="installer-page-heading">';
        $content .= '<span class="installer-eyebrow">'.self::$locale['setup_1021'].'</span>';
        $content .= '<h1 class="title">'.self::$locale['setup_1033'].'</h1>';
        $content .= '<p>'.self::$locale['setup_1034'].'</p>';
        $content .= '</div>';
        $content .= rendernotices(getnotices());

        $content .= '<dl class="installer-status-grid">';
        $statuses = [
            [self::$locale['setup_1035'], $current_version, TRUE],
            [self::$locale['setup_1036'], self::BUILD_VERSION, TRUE],
            [self::$locale['setup_1037'], $config_ready ? self::$locale['setup_1040'] : self::$locale['setup_1041'], $config_ready],
            [self::$locale['setup_1038'], $maintenance_active ? self::$locale['setup_1042'] : self::$locale['setup_1043'], TRUE],
            [self::$locale['setup_1044'], $latest_log, !$log_files],
        ];
        foreach ($statuses as [$label, $value, $healthy]) {
            $content .= '<div><dt>'.$label.'</dt><dd><span class="installer-status-dot '.($healthy ? 'is-ready' : 'is-warning').'"></span>'.$value.'</dd></div>';
        }
        $content .= '</dl>';

        $content .= '<div class="installer-recovery-callout">';
        $content .= '<div><strong>'.($upgrade_available ? self::$locale['setup_1045'] : self::$locale['setup_1046']).'</strong><p>'.($upgrade_available ? self::$locale['setup_1050'] : self::$locale['setup_1051']).'</p></div>';
        $content .= form_button('recovery_action', $upgrade_available ? self::$locale['setup_1052'] : self::$locale['setup_1053'], 'resume', ['class' => 'btn-primary']);
        $content .= '</div>';

        $content .= '<div class="installer-section-heading"><h2>'.self::$locale['setup_1054'].'</h2><p>'.self::$locale['setup_1055'].'</p></div>';
        $content .= '<div class="installer-recovery-actions">';
        $content .= $this->recoveryAction(self::$locale['setup_1017'], self::$locale['setup_1018'], form_button('step', self::$locale['setup_1019'], self::STEP_EXIT, ['class' => 'btn-success']));
        $content .= $this->recoveryAction(self::$locale['setup_1011'], self::$locale['setup_1012'], form_button('step', self::$locale['setup_1013'], self::STEP_TRANSFER, ['class' => 'btn-default']));
        $content .= $this->recoveryAction(self::$locale['setup_1008'], self::$locale['setup_1009'], form_button('step', self::$locale['setup_1010'], self::STEP_INFUSIONS, ['class' => 'btn-default']));
        $content .= $this->recoveryAction(self::$locale['setup_1014'], self::$locale['setup_1015'], form_button('recovery_action', self::$locale['setup_1014'], 'htaccess', ['class' => 'btn-default']));
        $content .= '</div>';

        $content .= '<details class="installer-danger-zone">';
        $content .= '<summary>'.self::$locale['setup_1047'].'</summary>';
        $content .= '<div><h2>'.self::$locale['setup_1004'].'</h2><p>'.self::$locale['setup_1005'].'</p>';
        $content .= '<label class="installer-confirm"><input id="confirm_reset" type="checkbox" name="confirm_reset" value="1"> <span>'.self::$locale['setup_1056'].'</span></label>';
        $content .= form_button('recovery_action', self::$locale['setup_1007'], 'reset', [
            'class'      => 'btn-danger',
            'input_id'   => 'installer-reset-button',
            'deactivate' => TRUE,
        ]);
        $content .= '</div></details>';

        self::$step = [];
        return $content;
    }

    private function recoveryAction(string $title, string $description, string $action): string {
        return '<article><div><h3>'.$title.'</h3><p>'.$description.'</p></div><div>'.$action.'</div></article>';
    }

    private function recoveryUnavailable(): string {
        self::setInstallerMode(self::MODE_RECOVERY);
        self::$step = [];

        $content = '<div class="installer-empty-state">';
        $content .= '<span class="installer-empty-icon" aria-hidden="true">!</span>';
        $content .= '<h1 class="title">'.self::$locale['setup_1057'].'</h1>';
        $content .= '<p>'.self::$locale['setup_1058'].'</p>';
        $content .= '<a class="installer-button btn-primary" href="'.self::installerUrl([
            'mode' => self::MODE_INSTALL,
            'session' => self::STEP_INTRO,
        ]).'">'.self::$locale['setup_1059'].'</a>';
        $content .= '</div>';

        return $content;
    }
}
