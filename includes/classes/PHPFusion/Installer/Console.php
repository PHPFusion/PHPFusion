<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Console.php
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
namespace PHPFusion\Installer;

use PHPFusion\OutputHandler;

class Console extends InstallCore {

    private static $console_instance = NULL;

    /**
     * @return null|static
     */
    public static function getConsoleInstance() {
        if (self::$console_instance == NULL) {
            self::$console_instance = new static();
        }

        return self::$console_instance;
    }

    /**
     * @param $content
     *
     * @return string
     */
    public function getView($content) {
        $steps = [
            '1' => self::$locale['setup_0101'],
            '2' => self::$locale['setup_0102'],
            '3' => self::$locale['setup_0103'],
            '5' => self::$locale['setup_0106'],
            '6' => self::$locale['setup_0105']
        ];

        if (self::isRecoveryMode()) {
            $steps['1'] = self::$locale['setup_1021'];
        }

        $current_stage = $this->getCurrentStage();
        $mode_label = self::isRecoveryMode()
            ? self::$locale['setup_1021']
            : (self::isUpgradeMode() ? self::$locale['setup_0020'] : self::$locale['setup_0000']);
        $mode_class = self::getInstallerMode();

        $html = '<div class="installer-viewport">';
        $html .= '<main class="installer-shell" aria-labelledby="installer-title">';
        $html .= openform('setupform', 'post', self::installerUrl(), [
            'class'   => 'installer-form',
            'form_id' => 'setupform',
        ]);
        $html .= '<header class="installer-header">';
        $html .= '<div class="installer-brand">';
        $html .= '<span class="installer-brand-mark" aria-hidden="true">'.$this->icon('mark').'</span>';
        $html .= '<span><strong id="installer-title">PHPFusion X</strong><small>'.self::$locale['setup_1022'].'</small></span>';
        $html .= '</div>';
        $html .= '<div class="installer-header-meta">';
        $html .= '<span class="installer-mode installer-mode-'.$mode_class.'">'.$this->icon(self::isRecoveryMode() ? 'lifebuoy' : 'sparkle').$mode_label.'</span>';
        if (is_file(BASEDIR.'config_temp.php') && !self::isRecoveryMode()) {
            $html .= '<a class="installer-quiet-link" href="'.self::installerUrl([
                'mode' => self::MODE_RECOVERY,
                'session' => self::STEP_INTRO,
            ]).'">'.$this->icon('lifebuoy').self::$locale['setup_1023'].'</a>';
        }
        $html .= '</div>';
        $html .= '</header>';

        $html .= '<div class="installer-layout">';
        $html .= '<aside class="installer-sidebar" aria-label="'.self::$locale['setup_1024'].'">';
        $html .= '<div class="installer-progress-copy"><span>'.self::$locale['setup_1025'].'</span><strong>'.sprintf(self::$locale['setup_1026'], $current_stage, count($steps)).'</strong></div>';
        $html .= '<ol class="installer-steps">';
        $position = 0;
        foreach ($steps as $key => $value) {
            $position++;
            $state = $position < $current_stage ? 'complete' : ($position === $current_stage ? 'current' : 'upcoming');
            $html .= '<li class="is-'.$state.'"'.($state === 'current' ? ' aria-current="step"' : '').'>';
            $html .= '<span class="installer-step-marker">'.($state === 'complete' ? $this->icon('check') : $position).'</span>';
            $html .= '<span><strong>'.$value.'</strong><small>'.($state === 'complete' ? self::$locale['setup_1027'] : ($state === 'current' ? self::$locale['setup_1028'] : self::$locale['setup_1029'])).'</small></span>';
            $html .= '</li>';
        }
        $html .= '</ol>';
        $html .= '</aside>';

        $html .= '<section class="installer-workspace">';
        $html .= '<div class="installer-content" role="region" aria-live="polite">';
        $html .= $content;

        if (self::$localeset) {
            $html .= form_hidden('localeset', self::$localeset);
        }

        $html .= '</div>';

        if (self::$step) {
            $html .= '<footer class="installer-actions">';
            foreach (self::$step as $button_prop) {
                $default_class['class'] = 'btn-primary';
                $button_prop += $default_class;
                $html .= form_button($button_prop['name'], $button_prop['label'], $button_prop['value'], [
                    'class' => 'installer-button '.$button_prop['class']
                ]);
            }
            $html .= '</footer>';
        }

        $html .= '</section>';
        $html .= '</div>';
        $html .= closeform();
        $html .= '<footer class="installer-shell-footer">';
        $html .= '<div class="installer-footer-build"><span>'.self::$locale['setup_1031'].'</span><span>'.self::$locale['setup_1030'].' <code>'.self::BUILD_VERSION.'</code></span></div>';
        $html .= '<span class="installer-footer-security">'.$this->icon('lock').self::$locale['setup_1032'].'</span>';
        $html .= '</footer>';
        $html .= '</main>';
        $html .= '</div>';

        return $html;
    }

    private function getCurrentStage(): int {
        $step = (string)INSTALLATION_STEP;
        if ($step === (string)self::STEP_PERMISSIONS) {
            return 2;
        }
        if ($step === (string)self::STEP_DB_SETTINGS_FORM || $step === (string)self::STEP_DB_SETTINGS_SAVE) {
            return 3;
        }
        if ($step === (string)self::STEP_PRIMARY_ADMIN_FORM || $step === (string)self::STEP_TRANSFER) {
            return 4;
        }
        if ($step === (string)self::STEP_INFUSIONS) {
            return 5;
        }

        return 1;
    }

    private function icon(string $name): string {
        $icons = [
            'mark' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7.5 9.5 12 5 16.5"/><path d="M19 7.5 14.5 12l4.5 4.5"/><path d="m14 4-4 16"/></svg>',
            'check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>',
            'lifebuoy' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="m5.64 5.64 4.24 4.24M14.12 14.12l4.24 4.24M18.36 5.64l-4.24 4.24M9.88 14.12l-4.24 4.24"/></svg>',
            'sparkle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.1 3.4a6 6 0 0 1-3.8 3.8L4 11.3l3.1 1.1a6 6 0 0 1 3.8 3.8L12 20l1.1-3.8a6 6 0 0 1 3.8-3.8l3.1-1.1-3.1-1.1a6 6 0 0 1-3.8-3.8L12 3Z"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>',
        ];

        return $icons[$name] ?? '';
    }

    /**
     * Need to replace more things.
     *
     * @return string
     */
    public function getLayout() {
        $asset_path = __DIR__.'/assets/installer.css';
        $script_path = __DIR__.'/assets/installer.js';
        $page_title = self::isRecoveryMode()
            ? self::$locale['setup_1021'].' · PHPFusion X'
            : (self::isUpgradeMode() ? self::$locale['setup_0020'] : self::$locale['setup_0000']);
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='".self::$locale['setup_0011']."' dir='".self::$locale['setup_0012a']."'>\n";
        $html .= "<head>\n";
        $html .= "<title>".$page_title."</title>\n";
        $html .= "<meta charset='".self::$locale['setup_0012']."'>";
        $html .= '<link rel="shortcut icon" href="'.IMAGES.'favicons/favicons.ico">';
        $html .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
        $html .= "<meta name='theme-color' content='#09090b'>\n";
        $html .= "<script src='".INCLUDES."jquery/jquery.min.js'></script>\n";
        $html .= "<script src='".INCLUDES."bootstrap/bootstrap3/js/bootstrap.min.js'></script>\n";
        $html .= "<link rel='stylesheet' href='".THEMES."templates/styles/default.min.css?v=".filemtime(THEMES.'templates/styles/default.min.css')."'>\n";
        $html .= "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap3/css/bootstrap.min.css'>\n";
        if (self::$locale['setup_0012a'] == 'rtl') {
            $html .= "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap3/css/bootstrap-rtl.min.css'>";
        }
        $html .= "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css'>\n";
        $html .= OutputHandler::$pageHeadTags;

        $core_css_files = fusion_filter_hook("fusion_core_styles");
        if (is_array($core_css_files)) {
            $core_css_files = array_filter($core_css_files);
            foreach ($core_css_files as $css_file) {
                if (is_file($css_file)) {
                    $script = fusion_load_script($css_file, "css", TRUE);
                    $html .= $script;
                }
            }
        }
        $html .= "<link rel='stylesheet' href='".INCLUDES."classes/PHPFusion/Installer/assets/installer.css?v=".(is_file($asset_path) ? filemtime($asset_path) : '1')."'>\n";

        $html .= "</head>\n<body class='fusion-installer installer-mode-".self::getInstallerMode()."'>\n";
        $html .= "{%content%}";
        $fusion_jquery_tags = OutputHandler::$jqueryCode;
        if (!empty($fusion_jquery_tags)) {
            ksort($fusion_jquery_tags);
            $fusion_jquery_tags = implode('', $fusion_jquery_tags);
            $html .= "<script>$(function() {".$fusion_jquery_tags."});\n</script>\n";
        }
        $html .= OutputHandler::$pageFooterTags;
        $html .= "<script src='".INCLUDES."classes/PHPFusion/Installer/assets/installer.js?v=".(is_file($script_path) ? filemtime($script_path) : '1')."' defer></script>\n";
        $html .= "</body>\n";
        $html .= "</html>\n";

        return $html;
    }

}
