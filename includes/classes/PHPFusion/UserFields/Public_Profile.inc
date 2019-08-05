<?php

namespace PHPFusion\UserFields;

use PHPFusion\UserFieldsInput;
use PHPFusion\UserFieldsQuantum;

class Public_Profile extends UserFieldsQuantum {

    public $post_name;
    public $user_data = [];
    public $profile_id = 0;
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

    /**
     * Get user field cats.
     *
     * @param int $id
     *
     * @return array|null
     */
    public function get_category($id = 0) {

        static $section = [];
        $aid = get("aid") ? fusion_get_aidlink() : "";
        if (empty($section)) {
            $result = dbquery("SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_parent=:fparent ORDER BY field_cat_order ASC", [
                ':fparent' => 0
            ]);
            if (dbrows($result)) {
                $i = 0;
                while ($data = dbarray($result)) {
                    if ($this->profile_id) {
                        $link = clean_request($aid.'&section='.$data['field_cat_id'].'&lookup='.$this->profile_id, ['section'], FALSE);
                    } else {
                        $link = clean_request($aid.'&section='.$data['field_cat_id'], ['section'], FALSE);
                    }
                    $section[$data['field_cat_id']] = [
                        'id'     => $data['field_cat_id'],
                        'active' => ($this->section_id == $data['field_cat_id']) ? 1 : (!$this->section_id && $i == 0 ? 1 : 0),
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


    public function getInfo() {

        $info = [
            'section'    => $this->get_category(),
            'section_id' => $this->section_id,
        ];

        if (iADMIN || $this->user_name_change) {
            $user_name = post("user_name") ?: $this->user_data['user_name'];
            $info['user_name_input'] = form_text('user_name', $this->locale['u127'], $user_name, [
                'max_length' => 30,
                'required'   => 1,
                'error_text' => $this->locale['u122'],
                'inline'     => $this->inline_field
            ]);
        }

        // Field exist only in edit profile
        if ($this->registration === FALSE) {

            $info['user_avatar'] = $this->userAvatarStudio();
            // Real name change button
            $info['name_change_button'] = form_button('update_name', 'Update Name', 'update_name', ['class' => $this->options['btn_post_class']]);
            // User Reputation
            if ($this->is_admin_panel === TRUE && infusion_exists('forum')) {
                $user_reputation = post("user_reputation", FILTER_VALIDATE_INT) ?: $this->user_data['user_reputation'];
                $info['user_reputation'] = form_text('user_reputation', $this->locale['u210'], $user_reputation, [
                        'type'        => 'number',
                        'inline'      => $this->inline_field,
                        'width'       => '150px',
                        'inner_width' => '150px'
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
                    $section_title = $this->get_section_title($cat_id);
                    $info['user_fields'] .= "<h4>".$section_title."</h4>";
                    $field_output = '';
                    if (!empty($field_rows)) {
                        foreach ($field_rows as $fields) {
                            $options = [
                                'show_title' => TRUE,
                                'inline'     => $this->inline_field,
                                'required'   => (bool)$fields['field_required']
                            ];
                            if ($fields['field_type'] == 'file') {
                                $options += ['plugin_folder' => $this->plugin_folder];
                            }
                            $output = $this->display_fields($fields, $this->user_data, $this->method, $options);
                            $info['user_fields'] .= $output;
                            $field_output .= $output;
                        }
                    }
                    $info['fields'][$cat_id] = [
                        'title'  => $section_title,
                        'fields' => $field_output
                    ];
                }
            }
        } else {
            // This is registration
            $info += $this->get_user_fields();
            if ($this->display_validation == 1 && $this->is_admin_panel === FALSE) {
                $info['validate'] = $this->renderValidation();
            }
            if ($this->display_terms == 1 && $this->is_admin_panel === FALSE) {
                $info['terms'] = $this->renderTerms();
            }
        }

        return (array)$info;
    }

    private $section_id = 0;

    public function get_SectionId() {
        return $this->section_id;
    }

    public function get_OutputInfo() {
        $this->section_id = get("section");
        $info = [
                'section'    => $this->get_category(),
                'section_id' => $this->section_id,
            ] + $this->get_user_fields();
        $current_section = [['id' => 1]];
        if (!empty($info['section'])) {
            $current_section = current($info['section']);
        }
        $this->info['section_id'] = isset($this->info['section'][$this->section_id]) ? $this->section_id : $current_section['id'];
        return $info;
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
     * Edit profile change avatar form
     *
     * @return string
     * @throws \ReflectionException
     */
    private function userAvatarStudio() {
        $settings = fusion_get_settings();

        $settings['avatar_studio'] = 1;

        // Save input for avatar studio or custom upload
        if (post('user_hash')) {
            // check user password
            $uf_input = new UserFieldsInput();
            $uf_input->user_data = $this->user_data;
            if ($uf_input->check_user_hash()) {
                if (!empty($_FILES['uav_input']['tmp_name'])) {
                    $avatar = form_sanitizer($_FILES['uav_input'], '', 'uav_input');
                    if (isset($avatar['error']) && $avatar['error'] == 0) {
                        // success in uploading.
                        if (!empty($avatar['image_name'])) {
                            // delete the old avatar from the database
                            if (is_file(BASEDIR.'images/avatars/'.$this->user_data['user_avatar'])) {
                                unlink(BASEDIR.'images/avatars/'.$this->user_data['user_avatar']);
                            }
                            dbquery("UPDATE ".DB_USERS." SET user_avatar=:filename WHERE user_id=:uid", [
                                ':filename' => $avatar['image_name'],
                                ':uid'      => $this->user_data['user_id']
                            ]);
                            addNotice('success', 'Your avatar has been updated');
                        }
                    }
                    //redirect(FUSION_REQUEST);
                } else if ($settings['avatar_studio'] == 1) {
                    // avatar studio functions
                    $avatar = sanitizer('avatar_studio', '', 'avatar_studio');
                    if (is_file(BASEDIR.'images/avatars/studio/'.$avatar)) {

                        require_once INCLUDES.'photo_functions_include.php';

                        // delete the old avatar from the database
                        if (is_file(BASEDIR.'images/avatars/'.$this->user_data['user_avatar'])) {
                            unlink(BASEDIR.'images/avatars/'.$this->user_data['user_avatar']);
                        }

                        $image_name = stripfilename(substr($avatar, 0, strrpos($avatar, ".")));
                        if ($avatar != "" && !preg_match("/[^a-zA-Z0-9_-]/", $avatar)) {
                            $image_name = $avatar;
                        }
                        $image_ext = strtolower(strrchr($avatar, '.'));
                        $avatar_filename = filename_exists(BASEDIR.'images/avatars/', $image_name.$image_ext);

                        copy(BASEDIR.'images/avatars/studio/'.$avatar, BASEDIR.'images/avatars/'.$avatar_filename);

                        dbquery("UPDATE ".DB_USERS." SET user_avatar=:filename WHERE user_id=:uid", [
                            ':filename' => $avatar_filename,
                            ':uid'      => $this->user_data['user_id']
                        ]);
                        addNotice('success', 'Your avatar has been updated');
                    }
                }
            } else {
                addNotice('danger', 'Account security error');
            }
            redirect(FUSION_REQUEST);
        }

        $avatar_settings = [
            'upload_path'     => IMAGES."avatars/",
            'valid_ext'       => ".jpg,.png,.gif,.svg",
            'type'            => 'image',
            'max_byte'        => $settings['avatar_filesize'],
            'max_height'      => $settings['avatar_width'],
            'max_width'       => $settings['avatar_height'],
            'inline'          => FALSE,
            'thumbnail'       => FALSE,
            'width'           => '100%',
            "error_text"      => $this->locale['u180'],
            "template"        => "modern",
            'croppie'         => TRUE,
            "croppie_resize"  => TRUE,
            "croppie_zoom"    => TRUE,
            'default_preview' => IMAGES.'avatars/no-avatar.jpg',
            "crop_box_width"  => 250, // 300 px default
            "crop_box_height" => 250,
            'placeholder'     => '<h4 class="strong m-t-5 m-b-5">Click or drag photo here</h4><small>'.sprintf($this->locale['u184'], parsebytesize($settings['avatar_filesize']), $settings['avatar_width'], $settings['avatar_height']),
            'icon'            => '',
        ];

        $info['user_avatar'] = '<div class="form-group clearfix">';
        $info['user_avatar'] .= '<label for="uav" class="control-label '.grid_column_size(100, 40, 20).'">'.$this->locale['u185'].':</label>';
        $info['user_avatar'] .= '<div class="'.grid_column_size(100, 40, 30).'">';
        $info['user_avatar'] .= display_avatar($this->user_data, '70px', FALSE, '', 'img-circle').'<a href="#uav" class="open-uav m-l-15">Change</a>';
        $info['user_avatar'] .= '</div></div>';

        // Modal
        $avatar_header = '<ul class="nav nav-pills m-b-0" role="tablist" style="font-size:16px;">';
        $avatar_header .= '<li role="presentation" class="active"><a aria-controls="avatar-studio" role="tab" href="#avatar-studio" data-toggle="tab">Avatar Studio</a></li>';
        $avatar_header .= '<li role="presentation"><a aria-controls="avatar-studio" role="tab" href="#avatar-upload" data-toggle="tab">Upload your own Avatar</a></li>';
        $avatar_header .= '</ul>';
        $avatar_media = '<div class="tab-content p-0" style="border:0;">';
        if ($settings['avatar_studio'] == 1) {
            $avatars = makefilelist(IMAGES.'avatars/studio/');
            $user_studio_val = '';
            if (!empty($avatars)) {
                $avatar_media .= '<div role="tabpanel" class="tab-pane active" id="avatar-studio">';
                $avatar_media .= '<div class="uav-media column-4">';
                foreach ($avatars as $avatar_file) {
                    $current_file = $this->user_data['user_avatar'] == $avatar_file ? ' active' : '';
                    $user_studio_val = $current_file ? $this->user_data['user_avatar'] : '';
                    $avatar_media .= '<div data-file="'.$avatar_file.'" class="avatar-selector m-b-20'.$current_file.'"><img src="'.IMAGES.'avatars/studio/'.$avatar_file.'" class="img-responsive"/></div>';
                }
                $avatar_media .= form_hidden('avatar_studio', '', $user_studio_val);
                $avatar_media .= '</div>';
                $avatar_media .= '</div>';
            }
            add_to_jquery("
                $('#avatar-studio .avatar-selector').bind('click', function( e ) {
                    let as_file = $(this).data('file');
                    if (as_file) {
                        $('.avatar-selector').removeClass('active');
                        $(this).addClass('active');
                        $('#avatar_studio').val( as_file );
                    }
                });
                $('button[name=\"confirm_avatar\"]').bind('click', function() {
                    $('#avatar_studio_frm').submit();
                });
                ");
        }

        $avatar_media .= '<div role="tabpanel" class="tab-pane" id="avatar-upload">';
        $avatar_media .= form_fileinput('uav_input', '', '', $avatar_settings);
        $avatar_media .= form_hidden('user_id', '', $this->user_data['user_id']);
        $avatar_media .= form_hidden('user_hash', '', $this->user_data['user_password']);
        $avatar_media .= '</div>';

        $modal = openmodal('uav', $avatar_header, ['button_class' => 'open-uav']);
        $modal .= openform('avatar_studio_frm', 'post', FORM_REQUEST, ['enctype' => TRUE]);
        $modal .= $avatar_media;
        $modal .= closeform();
        $modal .= modalfooter(form_button('cancel_uav', 'Cancel', 'cancel_uav', ['data' => ['dismiss' => 'modal']]).form_button('confirm_avatar', 'Confirm', 'confirm_avatar', ['class' => 'btn-primary']));
        $modal .= closemodal();

        add_to_footer($modal);

        return $info['user_avatar'];
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

            $field_cats = $this->get_category();

            $section_id = $this->get_SectionId();

            $index_page_id = isset($field_cats[$section_id]['id']) ? $field_cats[$section_id]['id'] : 1;

            $uf_cond = '';
            $uf_bind = [];
            if ($this->registration === TRUE && $this->is_admin_panel === FALSE) {
                $uf_cond = 'f.field_registration=:field_register';
                $uf_bind = [":field_register" => '1'];
            }


            $uf_query = "SELECT f.*, c.* FROM
            ".DB_USER_FIELDS." f
            INNER JOIN ".DB_USER_FIELD_CATS." c ON c.field_cat_id=f.field_cat
            ".($uf_cond ? "WHERE $uf_cond" : '')." ORDER BY c.field_cat_order, f.field_order";

            $result = dbquery($uf_query, $uf_bind);

            if (dbrows($result)) {

                while ($data = dbarray($result)) {

                    if ($data['field_cat_id']) {
                        $cat_name = UserFieldsQuantum::parse_label($data['field_cat_name']);
                        $category[$data['field_parent']][$data['field_cat_id']] = $cat_name;
                        $fields['user_field_extended'][$data['field_cat_id']] ['title'] = $cat_name;
                    }

                    if ($data['field_cat']) {
                        $item[$data['field_cat']][$data['field_id']] = $data;
                        $item_output = $this->display_fields($data, $this->user_data, $this->method, !empty($field['field_config']) ? $field['field_config'] : []);
                        $item[$data['field_cat']][$data['field_id']]['field_output'] = $item_output;
                        if (!empty($item_output)) {
                            $fields['user_field_extended'][$data['field_cat_id']]['fields'][$data['field_name']] = $item_output;
                        }

                    }
                }

                if (isset($category[$index_page_id])) {
                    foreach ($category[$index_page_id] as $cat_id => $cat) {
                        if (isset($item[$cat_id])) {
                            $fields['user_field'][$cat_id]['title'] = $cat;
                            foreach ($item[$cat_id] as $field_id => $field) {
                                $fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field['field_output'];
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
            $m_category = [];
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
     * @throws \ReflectionException
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
