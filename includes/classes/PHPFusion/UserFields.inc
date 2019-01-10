<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: UserFields.inc
| Author: Hans Kristian Flaatten (Starefossen)
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

use PHPFusion\UserFields\Account_Profile;
use PHPFusion\UserFields\Public_Profile;

require_once THEMES."templates/global/profile.php";

/**
 * Class UserFields
 *
 * @package PHPFusion
 */
class UserFields extends UserFieldsQuantum {

    public $display_terms = 0;
    public $display_validation = 0;
    public $errors = [];
    public $form_action = FUSION_REQUEST; // changed in API 1.02
    public $form_name = "userfieldsform";
    public $post_name;
    public $post_value;
    public $inline_field = TRUE;
    public $is_admin_panel = TRUE;

    public $show_admin_options = FALSE;
    public $show_admin_password = TRUE;
    public $show_avatar_input = TRUE;

    public $base_request = FALSE; // new in API 1.02 - turn fusion_self to fusion_request - 3rd party pages. Turn this on if you have more than one $_GET pagination str.
    public $skip_password = FALSE;
    public $registration = FALSE;
    public $user_data = [
        "user_id"             => '',
        "user_name"           => '',
        "user_password"       => '',
        "user_admin_password" => '',
        "user_email"          => '',
        'user_hide_email'     => 0,
        "user_language"       => LANGUAGE,
        'user_timezone'       => 'Europe/London',
        'user_reputation'     => 0,
        'user_joined' => TIME,
    ];
    /* Quantum Fields Extensions */
    public $system_title = '';
    public $admin_rights = '';
    public $locale_file = '';
    public $category_db = '';
    public $field_db = '';
    public $plugin_folder = '';
    public $plugin_locale_folder = '';
    public $debug = FALSE;
    public $method;
    public $paginate = TRUE;
    public $admin_mode = FALSE;
    public $user_name_change = FALSE;

    protected $info = [
        'terms'               => '',
        'validate'            => '',
        'user_avatar'         => '',
        'user_admin_password' => '',
    ];

    public $options = [];
    private $default_options = [
        'btn_post_class' => 'btn-primary spacer-sm',
        'btn_class'      => 'btn btn-primary',
    ];

    private static $instance = NULL;

    public static function get_instance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return (object)self::$instance;
    }

    /**
     * Check whether a user field is available/installed
     *
     * @param $field_name
     *
     * @return bool
     */
    public static function check_user_field($field_name) {
        static $list;
        $result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS);
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $list[] = $data['field_name'];
            }
        }

        return in_array($field_name, $list) ? TRUE : FALSE;
    }

    /**
     * Get user field cats.
     *
     * @param int $id
     *
     * @return array|null
     */
    public static function get_user_field_cats($id = 0) {

        static $section = array();
        $aid = isset($_GET['aid']) ? fusion_get_aidlink() : '';

        if (empty($section)) {
            $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:field_parent ORDER BY field_cat_order ASC", [':field_parent' => 0]);
            if (dbrows($result)) {
                $i = 0;
                while ($data = dbarray($result)) {
                    if (isset($_GET['lookup'])) {
                        $link = clean_request($aid.'&section='.$data['field_cat_id'].'&lookup='.$_GET['lookup'], ['section'], FALSE);
                    } else {
                        $link = clean_request($aid.'&section='.$data['field_cat_id'], ['section'], FALSE);
                    }
                    $section[$data['field_cat_id']] = [
                        'id'     => $data['field_cat_id'],
                        'active' => (isset($_GET['section']) && $_GET['section'] == $data['field_cat_id']) ? 1 : (!isset($_GET['section']) && $i == 0 ? 1 : 0),
                        'link'   => $link,
                        'name'   => ucwords(UserFieldsQuantum::parse_label($data['field_cat_name'])),
                        'icon'   => $data['field_cat_class']
                    ];
                    $i++;
                }
            }
        }

        return empty($id) ? (array)$section : (isset($section[$id]) ? (array)$section[$id] : NULL);
    }

    /**
     * @param string $key
     *
     * @return array|null
     */
    private function get_settings_sections($key = '') {
        $aid = isset($_GET['aid']) ? fusion_get_aidlink() : '';
        $section['acc_settings'] = [
            'id' => 'acc_settings',
            'active' => (isset($_GET['section']) && $_GET['section'] == 'acc_settings' ? 1 : (!isset($_GET['section']) ? 1 : 0) ),
            'link' => clean_request($aid.'section=acc_settings&lookup='.$this->user_data['user_id'], ['section'], FALSE),
            'name' => 'Account Settings',
        ];
        $section['preferences'] = [
            'id' => 'preferences',
            'active' => (isset($_GET['section']) && $_GET['section'] == 'preferences' ? 1 : 0),
            'link' => clean_request($aid.'section=preferences&lookup='.$this->user_data['user_id'], ['section'], FALSE),
            'name' => 'Preferences',
        ];
        $section['security'] = [
            'id' => 'security',
            'active' => (isset($_GET['section']) && $_GET['section'] == 'security' ? 1 : 0),
            'link' => clean_request($aid.'section=security&lookup='.$this->user_data['user_id'], ['section'], FALSE),
            'name' => 'Security',
        ];

        return empty($key) ? (array)$section : (isset($section[$key]) ? (array)$section[$key] : NULL);
    }

    private static $pages = [];
    protected static $additional_pages = [];

    /**
     * Custom add files into the edit profile page.
     * @param $key
     * @param $title
     * @param $file
     */
    public function add_pages($key, $title, $file) {
        self::$additional_pages[$key] = [
            'title' => $title,
            'file' => is_file($file) ? $file : ''
        ];
    }

    public static function get_pages() {
        if (empty(self::$pages)) {
            self::$pages = [
                'pu_profile' => ['title'=>'Public Profile'], // Public Profile
                'se_profile' => ['title'=>'Settings'], // Private Profile
            ];
        }

        return self::$pages = array_merge(self::$pages, self::$additional_pages);
    }

    public static function get_current_page() {
        $all_pages = self::get_pages();
        return isset($_GET['ref']) && isset($all_pages[$_GET['ref']]) ? $_GET['ref'] : 'pu_profile';
    }

    /**
     * Returns all fields array
     * @return array
     */
    public function get_input_info() {
        $this->method = 'input';
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        // user id
        $user_id = isset($_GET['lookup']) && isnum($_GET['lookup']) ? $_GET['lookup'] : fusion_get_userdata('user_id');

        if ($this->registration) $user_id = 0;

        $this->info = [
            'section'              => self::get_user_field_cats(),
            'pages'                => $this->get_pages(),
            'user_id'              => form_hidden('user_id', '', $user_id),
            'name'                 => $this->user_data['user_name'],
            'user_name'            => '',
            'joined_date'          => showdate('longdate', $this->user_data['user_joined']),
            'email'                => $this->user_data['user_email'],
            'user_password'        => '',
            'user_admin_password'  => '',
            'user_email'           => '',
            'user_hide_email'      => '',
            'user_avatar'          => '',
            'user_reputation'      => '',
            'validate'             => '',
            'terms'                => '',
            'user_close_message'   => '',
            'custom_page'          => FALSE,
            'user_password_verify' => (iADMIN && checkrights("M")) ? "" : form_text('user_password_verify', $locale['u135a'], '',
                [
                    'type'             => 'password',
                    'autocomplete_off' => 1,
                    'inline'           => TRUE,
                    'max_length'       => 64,
                    'error_text'       => $locale['u133']
                ]
            )
        ];

        $this->info['current_page'] = self::get_current_page();
        $this->options += $this->default_options;

        if ($this->registration) {
            // registration
            // bound in page 1 only.
            $_GET['section'] = 1;
            $class = new Public_Profile();
            $class->user_data = $this->user_data;
            $class->post_name = $this->post_name;
            $class->options = $this->options;
            $class->user_name_change = TRUE;
            $class->registration = $this->registration;
            $class->is_admin_panel = $this->is_admin_panel;
            $class->display_validation = $this->display_validation;
            $class->display_terms = $this->display_terms;
            $class->plugin_folder = $this->plugin_folder;
            $class->plugin_locale_folder = $this->plugin_locale_folder;
            $class->inline_field = $this->inline_field;
            $this->info = array_merge($this->info, $class->get_info());

            $class = new Account_Profile();
            $class->user_data = $this->user_data;
            $class->options = $this->options;
            $class->is_admin_panel = $this->is_admin_panel;
            $class->registration = $this->registration;
            $class->post_name = $this->post_name;
            $class->plugin_folder = $this->plugin_folder;
            $class->user_name_change = TRUE;
            $class->inline_field = $this->inline_field;
            $this->info = array_merge($this->info, $class->get_info());

        } else {
            // Edit Profile

            // edit profile in administration
            if ($this->is_admin_panel === TRUE) {

                $_GET['section'] = 1;

                $class = new Public_Profile();
                $class->user_data = $this->user_data;
                $class->post_name = $this->post_name;
                $class->options = $this->options;
                $class->user_name_change = $this->user_name_change;
                $class->registration = $this->registration;
                $class->display_validation = $this->display_validation;
                $class->display_terms = $this->display_terms;
                $class->plugin_folder = $this->plugin_folder;
                $class->plugin_locale_folder = $this->plugin_locale_folder;
                $class->inline_field = $this->inline_field;
                $class->is_admin_panel = TRUE;
                $this->info = array_merge($this->info, $class->get_info());

                $class = new Account_Profile();
                $class->user_data = $this->user_data;
                $class->options = $this->options;
                $class->registration = $this->registration;
                $class->post_name = $this->post_name;
                $class->user_name_change = $this->user_name_change;
                $class->show_admin_password = $this->show_admin_password;
                $class->inline_field = $this->inline_field;
                $class->is_admin_panel = TRUE;
                $this->info = array_merge($this->info, $class->get_info());

            } else {

                // Notice to tell that there are email pending for verification.
                if ($settings['email_verification']) {
                    $result = dbquery("SELECT user_email FROM ".DB_EMAIL_VERIFY." WHERE user_id=:selfid", [
                        ':selfid' => fusion_get_userdata('user_id')
                    ]);
                    if (dbrows($result)) {
                        $data = dbarray($result);
                        addNotice('success', sprintf($locale['u200'], $data['user_email'])."\n<br />\n".$locale['u201']);
                    }
                }

                switch ($this->info['current_page']) {
                    default:
                        // Custom page content
                        if (isset($this->info['pages'][$this->info['current_page']]['file'])) {
                            $this->info['custom_page'] = TRUE;
                            $this->info['title'] = $this->info['pages'][$this->info['current_page']]['title'];
                            $this->info['page_content'] = 'There are no page content yet.';

                            $user_fields = '';
                            $user_fields_meta = '';
                            $user_fields_section = [];
                            $user_fields_title = '';
                            if (is_file($this->info['pages'][$this->info['current_page']]['file'])) {
                                include $this->info['pages'][$this->info['current_page']]['file'];
                            }
                            $section = NULL;
                            if (!empty($user_fields_section)) {
                                $section = isset($_GET['section']) ? $_GET['section'] : $user_fields_section['id'][0];
                            }
                            // Custom title
                            if ($user_fields) {
                                if ($user_fields_title) {
                                    $this->info['title'] = $user_fields_title;
                                }
                                if ($user_fields_meta) {
                                    add_to_meta('keywords', $user_fields_meta);
                                }
                                // Custom content
                                if (!empty($user_fields_section)) {
                                    $this->info['section'] = $user_fields_section;
                                }
                                $this->info['page_content'] = $user_fields;
                            }
                            add_to_title($locale['global_201'].$this->info['title']);
                            break;
                        }
                    case 'pu_profile':
                        $this->info['section'] = $this->get_user_field_cats();
                        $section = isset($_GET['section']) && isset($this->info['section'][$_GET['section']]) ? $_GET['section'] : 1;
                        $field_cats = $this->get_user_field_cats($section);
                        if (isset($field_cats['name'])) {
                            $this->info['title'] = $field_cats['name'];
                        }

                        $class = new Public_Profile();
                        $class->user_data = $this->user_data;
                        $class->post_name = $this->post_name;
                        $class->options = $this->options;
                        $class->registration = $this->registration;
                        $class->display_validation = $this->display_validation;
                        $class->display_terms = $this->display_terms;
                        $class->plugin_folder = $this->plugin_folder;
                        $class->plugin_locale_folder = $this->plugin_locale_folder;
                        $class->inline_field = $this->inline_field;
                        $this->info['user_hash'] = form_hidden('user_hash', '', $this->user_data['user_password']);
                        $this->info = array_merge($this->info, $class->get_info());

                        break;

                    case 'se_profile':

                        $this->info['section'] = $this->get_settings_sections();
                        $section = isset($_GET['section']) && isset($this->info['section'][$_GET['section']]) ? $_GET['section'] : 'acc_settings';
                        $field_cats = $this->get_settings_sections($section);
                        if (isset($field_cats['name'])) {
                            $this->info['title'] = $field_cats['name'];
                        }

                        $class = new Account_Profile();
                        $class->user_data = $this->user_data;
                        $class->options = $this->options;
                        $class->registration = $this->registration;
                        $class->post_name = $this->post_name;
                        $class->user_name_change = $this->user_name_change;
                        $class->show_admin_password = (iADMIN ? TRUE : FALSE);
                        $class->inline_field = $this->inline_field;
                        $this->info = array_merge($this->info, $class->get_info());
                        break;
                }

            }
        }

        $this->info += [
            'register'  => $this->registration,
            'current_section' => ($this->paginate && !$this->registration) ? $section : '',
            'openform'  => openform($this->form_name, 'post', FUSION_REQUEST, [ 'enctype' => ($this->show_avatar_input ? TRUE : FALSE) ]),
            'closeform' => closeform(),
            'button'    => $this->renderButton(),
        ];

        return (array)$this->info;
    }

    /**
     * Display Input Fields
     *
     * @param array $input
     *
     * Template IDs     Registration    core_user_registration_form
     *                  Edit Profile    core_edit_profile_form
     *
     * @return string|void
     */
    public function display_input(array $input = []) {
        $this->info = $this->get_input_info();
        return ($this->registration ? display_register_form($this->info) : display_profile_form($this->info));
    }


    private function renderButton() {
        $disabled = $this->display_terms ? TRUE : FALSE;
        $this->options += $this->default_options;
        $html = (!$this->skip_password) ? form_hidden('user_hash', '', $this->user_data['user_password']) : '';
        $html .= form_button($this->post_name, $this->post_value, $this->post_value,
            [
                "deactivate" => $disabled,
                "class"      => $this->options['btn_post_class']
            ]);

        return (string)$html;
    }

    /***
     * Fetch profile output data
     * - Display Profile (View)
     */
    public function display_profile_output() {

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();

        // Add User to Groups
        if (iADMIN && checkrights("UG") && isset($_GET['lookup']) && $_GET['lookup'] !== fusion_get_userdata('user_id')) {
            if ((isset($_POST['add_to_group'])) && (isset($_POST['user_group']) && isnum($_POST['user_group']))) {
                $lookup = stripinput($_GET['lookup']);
                $user_group = stripinput($_POST['user_group']);
                if (!preg_match("(^\.{$user_group}$|\.{$user_group}\.|\.{$user_group}$)", $this->user_data['user_groups'])) {
                    $userdata = [
                        'user_groups' => $this->user_data['user_groups'].".".$user_group,
                        'user_id'     => $lookup
                    ];
                    dbquery_insert(DB_USERS, $userdata, 'update');
                }
                if (defined('ADMIN_PANEL') && isset($_GET['step']) && $_GET['step'] == "view") {
                    redirect(ADMIN."members.php".fusion_get_aidlink()."&amp;step=view&amp;user_id=".$this->user_data['user_id']);
                } else {
                    redirect(BASEDIR."profile.php?lookup=".$lookup);
                }
            }
        }

        $this->info['section'] = $this->get_user_field_cats();
        $this->info['user_id'] = $this->user_data['user_id'];
        $this->info['user_name'] = $this->user_data['user_name'];

        $current_section = [['id' => 1]];
        if (!empty($this->info['section'])) {
            $current_section = current($this->info['section']);
        }
        $_GET['section'] = isset($_GET['section']) && isset($this->info['section'][$_GET['section']]) ? $_GET['section'] : $current_section['id'];

        if (empty($this->user_data['user_avatar']) && !file_exists(IMAGES."avatars/".$this->user_data['user_avatar'])) {
            $this->user_data['user_avatar'] = get_image('noavatar');
        }

        $this->info['core_field']['profile_user_avatar'] = [
            'title'  => $locale['u186'],
            'value'  => $this->user_data['user_avatar'],
            'status' => $this->user_data['user_status']
        ];

        // user name
        $this->info['core_field']['profile_user_name'] = [
            'title' => $locale['u068'],
            'value' => $this->user_data['user_name']
        ];

        // user level
        $this->info['core_field']['profile_user_level'] = [
            'title' => $locale['u063'],
            'value' => getgroupname($this->user_data['user_level'])
        ];

        // user email
        if (iADMIN || $this->user_data['user_hide_email'] == 0) {
            $this->info['core_field']['profile_user_email'] = [
                'title' => $locale['u064'],
                'value' => hide_email($this->user_data['user_email'], fusion_get_locale("UM061a"))
            ];
        }

        // user joined
        $this->info['core_field']['profile_user_joined'] = [
            'title' => $locale['u066'],
            'value' => showdate("longdate", $this->user_data['user_joined'])
        ];

        // Last seen
        $this->info['core_field']['profile_user_visit'] = [
            'title' => $locale['u067'],
            'value' => $this->user_data['user_lastvisit'] ? showdate("longdate", $this->user_data['user_lastvisit']) : $locale['u042']
        ];

        // user status
        if (iADMIN && $this->user_data['user_status'] > 0) {
            $this->info['core_field']['profile_user_status'] = [
                'title' => $locale['u055'],
                'value' => getuserstatus($this->user_data['user_status'])
            ];

            if ($this->user_data['user_status'] == 3) {
                $this->info['core_field']['profile_user_reason'] = [
                    'title' => $locale['u056'],
                    'value' => $this->user_data['suspend_reason']
                ];
            }
        }

        // IP
         if (iADMIN && checkrights('M')) {
            $this->info['core_field']['profile_user_ip'] = [
                'title' => $locale['u049'],
                'value' => $this->user_data['user_ip']
            ];
        }

        // Groups - need translating.
        $this->info['core_field']['profile_user_group']['title'] = $locale['u057'];
        $this->info['core_field']['profile_user_group']['value'] = '';
        $user_groups = strpos($this->user_data['user_groups'], ".") == 0 ? substr($this->user_data['user_groups'], 1) : $this->user_data['user_groups'];
        $user_groups = explode(".", $user_groups);
        $user_groups = array_filter($user_groups);
        $group_info = [];
        if (!empty($user_groups)) {
            for ($i = 0; $i < count($user_groups); $i++) {
                if ($group_name = getgroupname($user_groups[$i])) {
                    $group_info[] = [
                        'group_url'  => BASEDIR."profile.php?group_id=".$user_groups[$i],
                        'group_name' => $group_name
                    ];
                }
            }
            $this->info['core_field']['profile_user_group']['value'] = $group_info; // is array
        }

        if (iMEMBER && fusion_get_userdata('user_id') != $this->user_data['user_id']) {

            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR."messages.php?msg_send=".$this->user_data['user_id']
            ];

            if (checkrights('M') && fusion_get_userdata('user_level') <= USER_LEVEL_ADMIN && $this->user_data['user_id'] != '1') {
                $groups_cache = cache_groups();
                $user_groups_opts = [];
                $this->info['user_admin'] = [
                    'user_edit_title'     => $locale['u069'],
                    'user_edit_link'      => ADMIN."members.php".$aidlink."&amp;ref=edit&amp;lookup=".$this->user_data['user_id'],
                    'user_ban_title'      => $this->user_data['user_status'] == 1 ? $locale['u074'] : $locale['u070'],
                    'user_ban_link'       => ADMIN."members.php".$aidlink."&amp;action=".($this->user_data['user_status'] == 1 ? 2 : 1)."&amp;lookup=".$this->user_data['user_id'],
                    'user_suspend_title'  => $locale['u071'],
                    'user_suspend_link'   => ADMIN."members.php".$aidlink."&amp;action=3&amp;lookup=".$this->user_data['user_id'],
                    'user_delete_title'   => $locale['u072'],
                    'user_delete_link'    => ADMIN."members.php".$aidlink."&amp;ref=delete&amp;lookup=".$this->user_data['user_id'],
                    'user_delete_onclick' => "onclick=\"return confirm('".$locale['u072']."');\"",
                    'user_susp_title'     => $locale['u054'],
                    'user_susp_link'      => ADMIN."members.php".$aidlink."&amp;ref=log&amp;lookup=".$this->user_data['user_id']
                ];
                if (count($groups_cache) > 0) {
                    foreach ($groups_cache as $group) {
                        if (!preg_match("(^{$group['group_id']}|\.{$group['group_id']}\.|\.{$group['group_id']}$)", $this->user_data['user_groups'])) {
                            $user_groups_opts[$group['group_id']] = $group['group_name'];
                        }
                    }
                    if (iADMIN && checkrights("UG") && !empty($user_groups_opts)) {
                        $submit_link = BASEDIR."profile.php?lookup=".$this->user_data['user_id'];
                        if (defined('ADMIN_PANEL') && isset($_GET['step']) && $_GET['step'] == "view") {
                            $submit_link = ADMIN."members.php".$aidlink."&amp;step=view&amp;user_id=".$this->user_data['user_id']."&amp;lookup=".$this->user_data['user_id'];
                        }
                        $this->info['group_admin']['ug_openform'] = openform("admin_grp_form", "post", $submit_link);
                        $this->info['group_admin']['ug_closeform'] = closeform();
                        $this->info['group_admin']['ug_title'] = $locale['u061'];
                        $this->info['group_admin']['ug_dropdown_input'] = form_select("user_group", '', "", ["options" => $user_groups_opts, "width" => "100%", "inner_width" => "100%", "inline" => FALSE, 'class' => 'm-0']);
                        $this->info['group_admin']['ug_button'] = form_button("add_to_group", $locale['u059'], $locale['u059']);
                    }
                }
            }
        }

        // Now, we need to list the user profile profile.
        $class = new Public_Profile();
        $class->user_data = $this->user_data;
        $class->post_name = $this->post_name;
        $class->options = $this->options;
        $class->registration = $this->registration;
        $class->display_validation = $this->display_validation;
        $class->display_terms = $this->display_terms;
        $class->plugin_folder = $this->plugin_folder;
        $class->plugin_locale_folder = $this->plugin_locale_folder;
        $class->inline_field = $this->inline_field;
        $class->method = $this->method;
        $output_info = $class->get_output_info();

        $this->info = array_merge($this->info, $output_info);

        // Display Template
        echo display_user_profile($this->info);
    }

    /*
     * Render Listing Functions
     */
    protected static function list_func($user_id, $list, $selected_fields) {
        $html = "<tr>\n
                <td class='p-10'>\n".$list[$user_id]['checkbox']."</td>\n
                <td>".$list[$user_id]['user_name']."</td>\n
                <td class='no-break'>\n".$list[$user_id]['user_level']."</td>\n
                <td>\n".$list[$user_id]['user_email']."</td>\n";
        foreach ($selected_fields as $column) {
            $html .= "<td>".(!empty($list[$user_id][$column]) ? $list[$user_id][$column] : "-")."</td>\n";
        }

        return (string)$html;
    }

    /**
     * Get User Data of the current page.
     *
     * @param $key
     *
     * @return array|null
     */
    public function getUserData($key = NULL) {
        static $userData = [];
        if (empty($userData)) {
            $userData = $this->user_data;
        }

        return $key === NULL ? $userData : (isset($userData[$key]) ? $userData[$key] : NULL);
    }

}
