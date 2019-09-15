<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_registration.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

class Settings_Registration {
    private $locale = [];
    private $settings = [];
    private $login = NULL;

    public function __construct() {
        pageAccess('S4');
        $this->settings = fusion_get_settings();
        $this->locale = fusion_get_locale("", [LOCALE.LOCALESET."admin/login.php", LOCALE.LOCALESET."admin/settings.php"]);
        $aidlink = fusion_get_aidlink();
        $admin = \PHPFusion\Admins::getInstance();
        $admin->addAdminPage("S4", "General and Policy", "S41", ADMIN."settings_registration.php".$aidlink);
        $admin->addAdminPage("S4", "Social Login Providers", "S42", ADMIN."settings_registration.php".$aidlink."&amp;ref=login");
        add_breadcrumb(['link' => ADMIN.'settings_registration.php'.$aidlink, 'title' => $this->locale['register_settings']]);

        if (get('ref')) {
            switch (get('ref')) {
                case "login":
                    $this->login = new \PHPFusion\LoginAuth();
                    $this->display_login_settings();
                    break;
                default:
            }
        } else {
            $this->display_general_settings();
        }
    }

    private function display_login_settings() {

        add_breadcrumb(['link' => ADMIN.'settings_registration.php'.fusion_get_aidlink().'&amp;ref=login', 'title' => $this->locale['login_000']]);
        $output = "";
        if (get('action') && !empty(get('driver'))) {
        	$driver = stripinput(get('driver'));
            switch (get('action')) {
                case "install":
                    $this->install_plugin($driver);
                    break;
                case "uninstall":
                    $this->uninstall_plugin($driver);
                    break;
                case "configure":
                    $output = $this->configure_plugin($driver);
                    break;
                default:
                    redirect(clean_request("", ['action', 'driver'], FALSE));
            }
        }

        opentable("Social Login Providers");
        // here we use Auth to read all login connectors
        echo openform('settingsform', 'post', FUSION_REQUEST);;
        if ($output) {
            echo $output;
        } else {

            echo "<p>Allow your visitors to social login and register to your website using their favorite web service. When registering with a social provider, the user's profile is automatically filled with the data retrieved from his social account. You can decide which social account fields will map with user fields through config variables.</p>";
            echo "<hr/>\n";
            echo "<table class='table'>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th class='min'></th>\n";
            echo "<th>".$this->locale['login_100']."</th>";
            echo "<th>Handle</th>";
            echo "<th>".$this->locale['login_101']."</th>";
            echo "<th>".$this->locale['login_102']."</th>";
            echo "<th>".$this->locale['login_103']."</th>";
            echo "<th>".$this->locale['login_104']."</th>";
            echo "</tr>\n";
            echo "</thead>\n<tbody>\n";
            $plugin_cache = $this->login->cache_files();
            if (!empty($plugin_cache)) {
                foreach ($plugin_cache as $plugin_name) {
                    $plugin = $this->login->get_login_file($plugin_name);
                    // we need a handler.
                    $button = "<a class='btn btn-default' href='".clean_request('action=install&driver='.$plugin['folder'], ['action', 'driver'], FALSE)."'>".$this->locale['login_107']."</a>";
                    $field_status = $this->locale['login_106'];
                    $field_config_status = $this->login->check_driver_config($plugin_name) ? $this->locale['login_109'] : $this->locale['login_110'];
                    if ($this->login->check_driver_status($plugin_name)) {
                        $field_status = $this->locale['login_105'];
                        $button = "<a class='btn btn-default active' onclick=\"if (!confirm('".$this->locale['login_122']."')) return false;\" href='".clean_request('action=uninstall&driver='.$plugin['folder'], ['action', 'driver'], FALSE)."'>".$this->locale['login_108']."</a>";
                        $field_config_status = "<a href='".clean_request("action=configure&driver=".$plugin['folder'], ['action', 'driver'], FALSE)."'>Configure</a>";
                    }
                    echo "<tr>\n";
                    echo "<td>$button</td>\n";
                    echo "<td><strong>".$plugin['name']."</strong><br/>".$plugin['description']."</td>\n";
                    echo "<td>".$plugin['handler']."</td>\n";
                    echo "<td>".$plugin['type']."</td>\n";
                    echo "<td>".$plugin['version']."</td>\n";
                    echo "<td>$field_status</td>\n";
                    echo "<td>$field_config_status</td>";
                    echo "</tr>\n";
                }
            }
            echo "</tbody>\n</table>\n";
        }
        closetable();

    }

    private function install_plugin($plugin_name) {
        if (!dbcount("(login_name)", DB_LOGIN, "login_name=:pname", [":pname" => $plugin_name]) &&
            $plugin = $this->login->get_login_file($plugin_name)
        ) {
            $data = [
                'login_title'    => $plugin['name'],
                'login_name'     => $plugin['folder'],
                'login_type'     => $plugin['type'],
                'login_status'   => 1,
                'login_settings' => ''
            ];
            dbquery_insert(DB_LOGIN, $data, 'save');

            // Install the user fields.
            if (!empty($plugin['dbname']) && !empty($plugin['dbinfo'])) {
                $user_table = fieldgenerator(DB_USERS);
                if (!in_array($plugin['dbname'], $user_table)) {
                    SqlHandler::add_column(DB_USERS, $plugin['dbname'], $plugin['dbinfo']);
                }
            }
            addNotice('success', str_replace('{DRIVER_NAME}', $plugin['name'], $this->locale['login_120']));
            redirect(clean_request('', ['action', 'driver'], FALSE));
        }
    }

    private function uninstall_plugin($plugin_name) {
        if (dbcount("(login_name)", DB_LOGIN, "login_name=:pname", [":pname" => $plugin_name]) &&
            $plugin = $this->login->get_login_file($plugin_name)
        ) {
            // install the driver
            dbquery("DELETE FROM ".DB_LOGIN." WHERE login_name=:pname", [":pname" => $plugin_name]);
            if (!empty($plugin['dbname']) && !empty($plugin['dbinfo'])) {
                $user_table = fieldgenerator(DB_USERS);
                if (in_array($plugin['dbname'], $user_table)) {
                    SqlHandler::drop_column(DB_USERS, $plugin['dbname']);
                }
            }
            addNotice('success', str_replace('{DRIVER_NAME}', $plugin['name'], $this->locale['login_121']));
            redirect(clean_request('', ['action', 'driver'], FALSE));
        }
    }

    private function configure_plugin($plugin_name) {
        $html = "";
        $plugin = $this->login->get_login_file($plugin_name);
        if (!empty($plugin) && $pdata = $this->login->cache_driver($plugin_name)) {

            $page_title = str_replace('{USER_FIELD}', $plugin['name'], $this->locale['login_111']);
            \PHPFusion\OutputHandler::addToTitle($this->locale['global_201'].$this->locale['global_200'].$page_title);
            \PHPFusion\BreadCrumbs::getInstance('default')->addBreadCrumb([
                'link'  => FUSION_REQUEST,
                'title' => $page_title,
            ]);
            $settings_found = FALSE;
            $settings_method = '';
            // continue here, load settings
            if (!empty($plugin['settings_method'])) {
                // This is the class calling method
                if (is_array($plugin['settings_method']) && count($plugin['settings_method']) > 1) {
                    $settings_class = $plugin['settings_method'][0];
                    $settings_method = $plugin['settings_method'][1];
                    // Call the authentication method
                    if (is_callable($plugin['settings_method'])) {
                        $s = new $settings_class();
                        return $s->$settings_method();
                    }
                } else if (is_callable($plugin['settings_method'])) {
                    // Call the function calling method
                    return $settings_method();
                }
            }
            if ($settings_found === FALSE) {
                $html = "<div class='alert alert-warning strong'>".$this->locale['login_123']."</div>";
            }
            $html .= "<p><a class='strong' href='".clean_request('', ['action', 'driver'], FALSE)."'><i class='fas fa-caret-left m-r-10'></i>".$this->locale['login_124']."</a></p>";

        } else {
            addNotice("danger", $this->locale['login_126']);
        }

        return $html;
    }

    private function display_general_settings() {

        if (post('savesettings')) {

            $inputData = [
                'login_method'        => sanitizer('login_method', '0', 'login_method'),
                'license_agreement'   => form_sanitizer($_POST['license_agreement'], '', 'license_agreement', TRUE),
                'enable_registration' => sanitizer('enable_registration', '0', 'enable_registration'),
                'email_verification'  => (post('email_verification') ? 1 : 0),
                'admin_activation'    => (post('admin_activation') ? 1 : 0),
                'display_validation'  => (post('display_validation') ? 1 : 0),
                'enable_terms'        => sanitizer('enable_terms', '0', 'enable_terms'),
                'license_lastupdate'  => ($_POST['license_agreement'] != $this->settings['license_agreement'] ? time() : $this->settings['license_lastupdate'])
            ];

            if (\Defender::safe()) {
                foreach ($inputData as $settings_name => $settings_value) {
                    $data = [
                        'settings_name'  => $settings_name,
                        'settings_value' => $settings_value
                    ];
                    dbquery_insert(DB_SETTINGS, $data, 'update', ['primary_key' => 'settings_name']);
                }
                addNotice('success', $this->locale['900']);
                redirect(FUSION_REQUEST);
            }
        }

        opentable("General and Policy Settings");
        echo openform('registrationfrm', 'post');
        echo "<p>".$this->locale['register_description']."</p>\n<hr/>";
        echo "<div class='".grid_row()."'>\n<div class='".grid_column_size(100, 20)."'>\n";
        echo "<h4 class='m-0'>".$this->locale['register_settings']."</h4>";
        echo "</div>\n<div class='".grid_column_size(100, 80)."'>\n";
        echo form_checkbox('enable_registration', $this->locale['551'], $this->settings['enable_registration'], ['reverse_label' => TRUE]);
        echo form_checkbox('email_verification', $this->locale['552'], $this->settings['email_verification'], ['reverse_label' => TRUE]);
        echo form_checkbox('admin_activation', $this->locale['557'], $this->settings['admin_activation'], ['reverse_label' => TRUE]);
        echo form_checkbox('display_validation', $this->locale['553'], $this->settings['display_validation'], ['reverse_label' => TRUE]);
        echo "</div>\n</div>\n";
        echo "<hr/>\n";
        echo "<div class='".grid_row()."'>\n";
        echo "<div class='".grid_column_size(100, 20)."'>\n";
        echo "<h4 class='m-0'>Login Behaviors</h4>";
        echo "</div>\n<div class='".grid_column_size(100, 80)."'>\n";
        $opts = ['0' => $this->locale['global_101'], '1' => $this->locale['699e'], '2' => $this->locale['699b']];
        echo form_select('login_method', $this->locale['699'], $this->settings['login_method'], ['options' => $opts]);
        echo "</div>\n</div>\n";
        echo "<hr/>\n";

        echo form_select('enable_terms', $this->locale['558'], $this->settings['enable_terms'], ['options' => [
            0 => $this->locale['disable'],
            1 => $this->locale['enable'],
        ]]);
        echo \PHPFusion\UserFieldsQuantum::quantum_multilocale_fields('license_agreement', $this->locale['559'], $this->settings['license_agreement'], [
            'form_name' => 'registrationfrm',
            'input_id'  => 'enable_license_agreement',
            'autosize'  => !$this->settings['tinymce_enabled'] ? FALSE : TRUE,
            'type'      => ($this->settings['tinymce_enabled'] ? 'tinymce' : 'html'),
            'function'  => 'form_textarea'
        ]);

        echo form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success']);
        echo closeform();
        closetable();
    }

}

new Settings_Registration();

require_once THEMES.'templates/footer.php';
