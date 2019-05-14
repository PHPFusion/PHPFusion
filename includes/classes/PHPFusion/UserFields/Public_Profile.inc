<?php

namespace PHPFusion\UserFields;

use PHPFusion\UserFields;
use PHPFusion\UserFieldsQuantum;

class Public_Profile extends UserFieldsQuantum {

    public $post_name;
    public $user_data = [];
    public $inline_field = FALSE;
    public $registration = FALSE;
    public $display_validation = FALSE;
    public $display_terms = FALSE;
    public $options = [];
    public $plugin_folder = '';
    public $plugin_locale_folder = '';
    public $user_name_change = FALSE;
    public $is_admin_panel = FALSE;
    private $locale;

    public function __construct() {
        $this->locale = fusion_get_locale();
    }

    public function get_info() {
        $settings = fusion_get_settings();
        $info = [];

        if (iADMIN || $this->user_name_change) {
            $user_name = post("user_name") ?: $this->user_data['user_name'];
            $info['user_name'] = form_text('user_name', $this->locale['u127'], $user_name, [
                'max_length' => 30,
                'required'   => 1,
                'error_text' => $this->locale['u122'],
                'inline'     => $this->inline_field
            ]);
        }

        // Field exist only in edit profile
        if ($this->registration === FALSE) {

            // Avatar Field
            if (isset($this->user_data['user_avatar']) && $this->user_data['user_avatar'] != "") {
                $info['user_avatar'] = "
                <div class='row'>\n
                <div class='col-xs-12 col-sm-3'>".$this->locale['u185']."</div>
                <div class='col-xs-12 col-sm-9'>
                    <div class='p-l-10'>
                    <label for='user_avatar_upload'>".display_avatar($this->user_data, '150px', '', FALSE, 'img-thumbnail')."</label>\n<br />\n".form_checkbox("delAvatar", $this->locale['u187'], '', ['reverse_label' => TRUE])."
                    </div>
                </div></div>
                ";
                add_to_jquery("
                $('#delAvatar').bind('click', function(e) {
                    var checked= $(this).prop('checked');
                    if (checked) {
                        if (confirm(\"Are you sure you are to remove your current existing avatar?\")) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                });
                ");
            } else {
                $upload_settings = [
                    "disable_fileinput" => FALSE,
                    'upload_path'       => IMAGES."avatars/",
                    'input_id'          => 'user_avatar_upload',
                    'valid_ext'         => ".jpg,.png,.gif,.svg",
                    'type'              => 'image',
                    'max_byte'          => $settings['avatar_filesize'],
                    'max_height'        => $settings['avatar_width'],
                    'max_width'         => $settings['avatar_height'],
                    'inline'            => TRUE,
                    'thumbnail'         => 0,
                    'width'             => '100%',
                    'class'             => 'm-t-10 m-b-0',
                    "error_text"        => $this->locale['u180'],
                    "template"          => "thumbnail",
                    'croppie'           => TRUE,
                    "croppie_resize"    => TRUE,
                    "croppie_zoom"      => TRUE,
                    'ext_tip'           => sprintf($this->locale['u184'], parsebytesize($settings['avatar_filesize']), $settings['avatar_width'], $settings['avatar_height'])
                ];

                $info['user_avatar'] = form_fileinput('user_avatar', $this->locale['u185'], '', $upload_settings);

            }
            // Real name change button
            $info['name_change_button'] = form_button('update_name', 'Update Name', 'update_name', ['class' => $this->options['btn_post_class']]);

            // User Reputation
            if ($this->is_admin_panel === TRUE && infusion_exists('forum')) {

                $user_reputation = post("user_reputation", FILTER_VALIDATE_INT) ?: $this->user_data['user_reputation'];
                $info['user_reputation'] = form_text('user_reputation', $this->locale['u210'], $user_reputation, [
                        'type'   => 'number',
                        'inline' => $this->inline_field
                    ]
                );
            }

            // Quantum
            $this->load_fields();
            if ($this->is_admin_panel === TRUE) {
                // admin edit profile
                $info += $this->get_user_fields();
            } else {
                $info['user_fields'] = '';
                foreach ($this->fields as $cat_id => $field_rows) {
                    $info['user_fields'] .= "<h4>".$this->get_section_title($cat_id)."</h4>";
                    if (!empty($field_rows)) {
                        foreach ($field_rows as $fields) {
                            $options = [
                                'show_title' => TRUE,
                                'inline'     => $this->inline_field,
                                'required'   => (bool)$fields['field_required']
                            ];
                            if ($fields['field_type'] == 'file') {
                                $options += [
                                    'plugin_folder' => $this->plugin_folder,
                                ];
                            }
                            $output = $this->display_fields($fields, $this->user_data, $this->method, $options);
                            $info['user_fields'] .= $output;
                        }
                    }
                }
            }
        } else {
            $info += $this->get_user_fields();

            if ($this->display_validation == 1 && $this->is_admin_panel === FALSE) {
                $info['validate'] = $this->renderValidation();
            }

            if ($this->display_terms == 1 && $this->is_admin_panel === FALSE)
                $info['terms'] = $this->renderTerms();
        }

        return (array)$info;
    }

    public function get_output_info() {
        return $this->get_user_fields();
    }

    private function get_section_title($cat_id) {
        static $section_titles = '';

        if (!empty($this->page) && empty($section_titles)) {
            $section_titles = flatten_array($this->page);
        }

        if (!empty($section_titles)) {
            foreach ($section_titles as $cat) {
                if ($cat['field_cat_id'] == $cat_id) {
                    return self::parse_label($cat['field_cat_name']);
                }
            }
        }
        return NULL;
    }


    /**
     * Fetch User Fields Array to templates
     * Toggle with class string method - input or display
     * output to array
     */
    private function get_user_fields() {
        $fields = [];
        $category = [];
        $item = [];
        $this->callback_data = $this->user_data;

        switch ($this->method) {
            case 'input':
                $info['user_field'][0]['fields']['user_id'] = form_hidden('user_id', '', $this->user_data['user_id']);
                break;
            case 'display':
                $info['user_field'] = [];
                break;
        }

        if ($this->is_admin_panel === FALSE) {

            $field_cats = UserFields::getInstance()->get_user_field_cats();
            $section = get("section", FILTER_VALIDATE_INT);
            $index_page_id = isset($field_cats[$section]) ?: 1;
            $registration_cond = ($this->registration === TRUE && $this->is_admin_panel === FALSE ? ' AND field.field_registration=:field_register' : '');
            $registration_bind = ($this->registration === TRUE && $this->is_admin_panel === FALSE ? [':field_register' => 1] : []);

            $query = "SELECT field.*, cat.field_cat_id, cat.field_cat_name, cat.field_parent, root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
                  FROM ".DB_USER_FIELDS." field
                  INNER JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
                  INNER JOIN ".DB_USER_FIELD_CATS." root on (cat.field_parent = root.field_cat_id)
                  WHERE (cat.field_cat_id=:index00 OR root.field_cat_id=:index01) $registration_cond
                  ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order
                  ";
            $bind = [
                ':index00' => $index_page_id,
                ':index01' => $index_page_id,
            ];
            $bind = $bind + $registration_bind;
            $result = dbquery($query, $bind);
            $rows = dbrows($result);
            if ($rows != '0') {

                while ($data = dbarray($result)) {
                    if ($data['field_cat_id']) {
                        $category[$data['field_parent']][$data['field_cat_id']] = UserFieldsQuantum::parse_label($data['field_cat_name']);
                    }
                    if ($data['field_cat']) {
                        $item[$data['field_cat']][] = $data;
                    }
                }

                if (isset($category[$index_page_id])) {
                    foreach ($category[$index_page_id] as $cat_id => $cat) {
                        if ($this->registration || $this->method == 'input') {
                            if (isset($item[$cat_id])) {
                                $fields['user_field'][$cat_id]['title'] = $cat;
                                foreach ($item[$cat_id] as $field_id => $field) {
                                    $field_output = $this->display_fields($field, $this->user_data, $this->method, !empty($field['field_config']) ? $field['field_config'] : []);
                                    $fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output;
                                    $fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                                }
                            }
                        } else {
                            // Display User Fields
                            if (isset($item[$cat_id])) {
                                $fields['user_field'][$cat_id]['title'] = $cat;
                                foreach ($item[$cat_id] as $field_id => $field) {
                                    // Outputs array
                                    $field_output = $this->display_fields($field, $this->callback_data, $this->method);
                                    //$fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output; // relational to the category
                                    $fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                                    if (!empty($field_output)) {
                                        $fields['user_field'][$cat_id]['fields'][$field['field_id']] = array_merge($field, $field_output);
                                    }
                                }
                            }
                        }
                    }
                }
            }

        } else {

            $query = "SELECT field.*, 
                  cat.field_cat_id, cat.field_cat_name, cat.field_parent, root.field_cat_id AS page_id, root.field_cat_name AS page_name, root.field_cat_db, root.field_cat_index
                  FROM ".DB_USER_FIELDS." field
                  INNER JOIN ".DB_USER_FIELD_CATS." cat ON (cat.field_cat_id = field.field_cat)
                  INNER JOIN ".DB_USER_FIELD_CATS." root ON (cat.field_parent = root.field_cat_id)                   
                  ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order";
            $result = dbquery($query);

            $rows = dbrows($result);
            $m_category = array();
            if ($rows != '0') {
                while ($data = dbarray($result)) {
                    if ($data['field_cat_id']) {
                        $m_category[$data['field_parent']][$data['field_cat_id']] = UserFieldsQuantum::parse_label($data['field_cat_name']);
                    }
                    if ($data['field_cat']) {
                        $item[$data['field_cat']][] = $data;
                    }
                }
                if (!empty($m_category)) {
                    foreach ($m_category as $m_cat_id => $category) {
                        // cat is array.
                        foreach ($category as $cat_id => $cat) {
                            if ($this->registration || $this->method == 'input') {
                                if (isset($item[$cat_id])) {
                                    $fields['user_field'][$m_cat_id][$cat_id]['title'] = $cat;
                                    foreach ($item[$cat_id] as $field_id => $field) {
                                        $options = [
                                            'show_title' => TRUE,
                                            'inline'     => $this->inline_field,
                                            'required'   => (bool)$field['field_required']
                                        ];
                                        if ($field['field_type'] == 'file') {
                                            $options += [
                                                'plugin_folder'        => $this->plugin_folder,
                                                'plugin_locale_folder' => $this->plugin_locale_folder,
                                                'inline'               => $this->inline_field,
                                            ];
                                        }
                                        $field_output = $this->display_fields($field, $this->user_data, $this->method, $options);
                                        $fields['user_field'][$m_cat_id][$cat_id]['fields'][$field['field_id']] = $field_output;
                                        //$fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                                    }
                                }
                            } else {
                                // Display User Fields
                                if (isset($item[$cat_id])) {
                                    $fields['user_field'][$cat_id]['title'] = $cat;
                                    foreach ($item[$cat_id] as $field_id => $field) {
                                        // Outputs array
                                        $field_output = $this->display_fields($field, $this->callback_data, $this->method);
                                        $fields['user_field'][$m_cat_id][$cat_id]['fields'][$field['field_id']] = $field_output; // relational to the category
                                        //$fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                                        if (!empty($field_output)) {
                                            $fields['user_field'][$m_cat_id][$cat_id]['fields'][$field['field_id']] = array_merge($field, $field_output);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return (array)$fields;
    }

    /**
     * Display Captcha
     *
     * @return string
     */
    private function renderValidation() {
        $this->locale = fusion_get_locale();
        $_CAPTCHA_HIDE_INPUT = FALSE;
        $html = "<div class='form-group m-t-20'>\n";
        $html .= "<label for='captcha_code' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0'>\n".$this->locale['u190']." <span class='required'>*</span></label>\n";
        $html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>";
        include INCLUDES."captchas/".fusion_get_settings("captcha")."/captcha_display.php";
        $html .= display_captcha([
            'captcha_id' => 'captcha_userfields',
            'input_id'   => 'captcha_code_userfields',
            'image_id'   => 'captcha_image_userfields'
        ]);
        if (!$_CAPTCHA_HIDE_INPUT) {
            $html .= form_text('captcha_code', '', '', [
                'inline'           => 1,
                'required'         => 1,
                'autocomplete_off' => 1,
                'width'            => '200px',
                'class'            => 'm-t-15',
                'placeholder'      => $this->locale['u191']
            ]);
        }
        $html .= "</div>\n";
        $html .= "</div>\n";

        return (string)$html;
    }

    /**
     * Display Terms of Agreement Field
     *
     * @return string
     */
    private function renderTerms() {
        $this->locale = fusion_get_locale();
        $agreement = strtr($this->locale['u193'], [
                '[LINK]'  => "<a href='".BASEDIR."print.php?type=T' id='license_agreement'><strong>",
                '[/LINK]' => "</strong></a>"
            ]
        );

        $modal = openmodal('license_agreement', $this->locale['u192'], ['button_id' => 'license_agreement']);
        $modal .= parse_textarea(fusion_get_settings('license_agreement'));
        $modal_content = '<p class="pull-left">'.$this->locale['u193a'].' '.ucfirst(showdate('shortdate', fusion_get_settings('license_lastupdate'))).'</p>';
        $modal_content .= '<button type="button" id="agree" class="btn btn-success" data-dismiss="modal">'.$this->locale['u193b'].'</button>';
        $modal .= modalfooter($modal_content, TRUE);
        $modal .= closemodal();
        add_to_footer($modal);
        add_to_jquery('
        $("#agree").on("click", function() {
            $("#register").attr("disabled", false).removeClass("disabled");
            $("#agreement").attr("checked", true);
        });
        ');

        $html = "<div class='form-group clearfix'>";
        $html .= "<label class='control-label col-xs-12 col-sm-3 p-l-0'>".$this->locale['u192']."</label>";
        $html .= "<div class='col-xs-12 col-sm-9'>\n";
        $html .= form_checkbox('agreement', $agreement, '', ["required" => TRUE, "reverse_label" => TRUE]);
        $html .= "</div>\n</div>\n";
        add_to_head("<script type='text/javascript'>$(function() {
        $('#agreement').bind('click', function() {
            let regBtn = $('#register');
            if ($(this).is(':checked')) {
                regBtn.attr('disabled', false).removeClass('disabled');
            } else {
                regBtn.attr('disabled', true).addClass('disabled');
            }
        });
        });</script>");

        return $html;
    }

}
