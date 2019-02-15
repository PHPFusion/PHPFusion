<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminForm.inc
| Author: Frederick MC Chan
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

/**
 * Class AdminForm
 *
 * @package PHPFusion
 */
class AdminForm {

    private $main_content = '';

    private $side_content = '';

    private $bottom_content = '';

    private $api = NULL;

    private $data = array();

    public function __construct(object $api) {

        if ($api instanceof AdminFormSDK) {

            $this->api = $api;

            $this->process_request();

            $this->content();

            $this->bottom_content();

            $this->side_content();

            $this->output();

        } else {
            echo 'Your current object requires the implementation of the \PHPFusion\AdminFormSDK interface.';
        }
    }

    private function __clone() {
        die('Cloning of this class is prohibited');
    }

    /**
     * @param       $field_name
     * @param null  $key
     * @param array $default_output
     *
     * @return null
     */
    private function field_prop($field_name, $key = NULL, $default_output = []) {
        static $field;

        $_fields = $this->api->fields();

        if (empty($fields[$field_name])) {

            $field[$field_name]['label'] = '';

            $field[$field_name]['options'] = [
                'placeholder' => '',
                'required'    => TRUE,
                'type'        => 'text'
            ];

            if (!empty($_fields[$field_name])) {

                if (!empty($_fields[$field_name]['name'])) {
                    $field[$field_name]['name'] = $_fields[$field_name]['name'];
                }

                if (!empty($_fields[$field_name]['label'])) {
                    $field[$field_name]['label'] = $_fields[$field_name]['label'];
                }

                if (!empty($_fields[$field_name]['options'])) {
                    $field[$field_name]['options'] = $_fields[$field_name]['options'];
                }
            }
        }


        return $key === NULL ? $field[$field_name] : (isset($field[$field_name][$key]) ? $field[$field_name][$key] : $default_output);

    }

    private function process_request() {

        if (!empty($_POST)) {
            foreach($_POST as $keys => $val) {
                $data_keys = $this->field_prop($keys, 'name', '');
                if (in_array($keys, ['form_id', 'fusion_token'])) {
                    $this->data[$keys] = $val;
                } else if ($data_keys) {
                    if ($keys == 'startdate' || $keys == 'enddate') {
                        $keys = 'af_startdate';
                    }
                    $this->data[$data_keys] = form_sanitizer($val, '', $keys);
                } else if ($val) {
                    $this->data[$keys] = form_sanitizer($val, '', $keys);
                }
            }

        }

    }


    /**
     * Callback value of current field.
     * @param $field_name
     *
     * @return null|string
     * @throws \Exception
     */
    private function field_value($field_name) {

        $_data = $this->api->data();

        $_field = $this->api->fields();

        if (isset($_field[$field_name]['name'])) {

            $_callback_ref = $_field[$field_name]['name']; // ssl_id

            if (isset($this->data[$_callback_ref])) {

                return (string)$this->data[$_callback_ref];

            }

            if (isset($_data[$_callback_ref])) {

                return (string)$_data[$_callback_ref];
            }
        }

        return NULL;
    }



    public function content() {

        $html = "<div class='block'>\n";

        $html .= form_hidden('id', '', $this->field_value('id'), ['type'=>'number']);

        $html .= form_text('title', $this->field_prop('title', 'label'), $this->field_value('title'), $this->field_prop('title', 'options', []));

        $html .= form_textarea('description', $this->field_prop('description', 'label'), $this->field_value('description'), $this->field_prop('description', 'options', []));

        $html .= "</div>\n";

        $this->main_content = $html;

    }

    public function bottom_content() {

        $html = "<div class='block'>";

        $html .= $this->render_panel([
            'title' => '',
            'description' => $this->api->custom( !empty($this->data) ? $this->data : $this->api->data() ) // I'm a genius.
        ]);

        $html .= "</div>\n";

        $this->bottom_content = $html;

    }

    private function output() {

        if (!empty($_POST) && !empty($this->data)) {

            $id_field = $this->field_prop('id', 'name', '');

            if (!empty($id_field)) {

                if (!empty($this->data[ $id_field ])) {

                    $this->api->update($this->data);

                } else {

                    $this->api->save($this->data);

                }

            } else {
                addNotice('danger', 'Error. Primary id field has to be defined.');
            }

        }

        $tpl = Template::getInstance('admin_post');

        $tpl->set_template(THEMES.'templates/global/admin/admin_forms.html');

        $tpl->set_tag('content', $this->main_content);

        if ($this->side_content) {
            $tpl->set_block('admin_side', ['content' => $this->side_content]);
        }

        if ($this->bottom_content) {
            $tpl->set_block('admin_bottom', ['content' => $this->bottom_content]);
        }


        echo openform('inputform', 'post').$tpl->get_output().closeform();

    }

    private function side_content() {

        $this->side_content .= $this->ui_meta();

    }


    // make it into a steam.
    private function ui_meta() {

        $_field = $this->api->fields();

        $html = form_button('save_draft', 'Save Draft', 'save_draft', ['class' => 'btn-xs']);

        if (!empty($_field['status'])) {

            $status_options = [
                '0' => 'Draft',
                '2' => 'Pending Review',
                '1' => 'Published',
            ];
            $value = $this->field_value('status');

            $display_value = $value ? $status_options[$value] : $status_options[0];

            $html .= "<div class='meta-block spacer-xs'>\n";

            $html .= "<i class='fas fa-map-pin fa-fw'></i> Status: <strong id='status-display'>$display_value</strong> <a href='#' class='admin-form-edit-link' data-target='#publish-status'>Edit</a>";

            $html .= "<div id='publish-status' style='display: none;'>\n";

            $html .= form_select('af_status', '', $value, [
                'select2_disabled' => TRUE,
                'options'          => $status_options,
                'class'            => 'm-b-0',
                'inner_width'      => '58%',
                'stacked'          =>
                    form_button('confirm_status', 'OK', 'confirm_status', [
                        'class' => 'btn-xs ok',
                        'data'  => [
                            'display'   => '#status-display',
                            'container' => '#publish-status',
                            'source'    => '#af_status',
                            'input'     => '#status',
                        ],
                        'type'  => 'button'
                    ]).
                    form_button('cancel_status', 'Cancel', 'cancel_status', [
                        'data'  => ['container' => '#publish-status'],
                        'class' => 'btn-link cancel p-0',
                        'type' => 'button'
                    ])]);

            $html .= form_hidden('status', '', $value, ['type'=>'number']);

            $html .= "</div>";

            $html .= "</div>\n";
        }

        if (!empty($_field['visibility'])) {

            $value = $this->field_value('visibility');

            $visibility_options = [
                    USER_LEVEL_PASSWORD => 'Password Protected',
                    USER_LEVEL_PRIVATE  => 'Private',
                ] + fusion_get_groups();

            $display_value = $visibility_options[0];

            $html .= "<div class='meta-block m-b-5'>\n";

            $html .= "<i class='fas fa-eye fa-fw'></i> Access: <strong id='visibility-display'>$display_value</strong> <a  href='#' class='admin-form-edit-link' data-target='#publish-visibility'>Edit</a>";

            $html .= "<div id='publish-visibility' style='display: none;'>\n";

            $html .= form_checkbox('af_visibility', '', $value, [
                'type'    => 'radio',
                'class'   => 'm-b-0',
                'options' => $visibility_options
            ]);

            $html .= "<div id='visibility-password' style='display: none;' >".form_text('af_password', '', '', ['type' => 'password', 'class' => 'form-group-sm', 'placeholder' => 'Enter password'])."</div>";

            $html .= form_button('confirm_visibility', 'Confirm', 'confirm_visibility', [
                    'class' => 'btn-xs ok',
                    'data'  => [
                        'display'   => '#visibility-display',
                        'container' => '#publish-visibility',
                        'source'    => 'input[name^="af_visibility"]',
                        'input'     => '#visibility',
                    ],
                    'type'  => 'button'
                ]).form_button('cancel_visibility', 'Cancel', 'cancel_status', [
                    'data'  => ['container' => '#publish-visibility'],
                    'class' => 'btn-link btn-xs cancel',
                    'type' => 'button'
                ]);

            $html .= form_hidden('visibility', '', $value);

            $html .= "</div>\n";

            $html .= "</div>\n";

        }

        if (!empty($_field['startdate'])) {

            $value = $this->field_value('startdate');

            $input_value = $value;

            $display_value = 'Immediately';

            if ($value) {

                $datetime = new \DateTime();

                $datetime->setTimestamp($value);

                $input_value = $datetime->format('d-m-Y H:i');

                $display_value = $input_value;
            }

            $enddate_append_options = (!empty($_field['enddate']) ? [
                'join_to_id'   => 'af_enddate',
                'join_from_id' => 'af_startdate'
            ] : []);

            $html .= "<div class='meta-block spacer-xs'>\n";

            $html .= "<i class='fas fa-calendar fa-fw'></i> Publish: <strong id='startdate-display'>$display_value</strong> <a  href='#' class='admin-form-edit-link' data-target='#publish-startdate'>Edit</a>";

            $html .= "<div id='publish-startdate' style='display: none;'>\n";

            $html .= form_datepicker('af_startdate', '', $value, [
                'class'           => 'form-group-sm m-t-5',
                'date_format_js'  => 'DD-MM-YYYY HH:mm',
                'date_format_php' => 'd-m-Y H:i',
                'stacked'         => "<div class='spacer-xs'>".
                    form_button('confirm_startdate', 'Confirm', 'confirm_visibility', [
                        'class' => 'btn-xs ok',
                        'data'  => [
                            'display'   => '#startdate-display',
                            'container' => '#publish-startdate',
                            'source'    => '#af_startdate',
                            'input'     => '#startdate',
                        ],
                        'type'  => 'button'
                    ])
                    .form_button('cancel_startdate', 'Cancel', 'cancel_status', [
                            'class' => 'btn-link btn-xs cancel',
                            'data'  => ['container' => '#publish-startdate'],
                            'type' => 'button'
                        ] + $enddate_append_options)."</div>"
            ]);

            $html .= form_hidden('startdate', '', $input_value, ['type'=>'timestamp']);

            $html .= "</div>\n";

            $html .= "</div>\n";

        }

        if (!empty($_field['enddate'])) {

            $value = $this->field_value('enddate');

            $input_value = $value;

            $display_value = 'Not specified';

            if ($value) {

                $datetime = new \DateTime();

                $datetime->setTimestamp($value);

                $input_value = $datetime->format('d-m-Y H:i');

                $display_value = $input_value;
            }

            $startdate_append_options = (!empty($_field['startdate']) ? [
                'join_to_id'   => 'af_startdate',
                'join_from_id' => 'af_enddate'
            ] : []);

            $html .= "<div class='meta-block spacer-xs'>\n";

            $html .= "<i class='fas fa-calendar fa-fw'></i> Unpublish: <strong id='enddate-display'>$display_value</strong> <a  href='#' class='admin-form-edit-link' data-target='#publish-enddate'>Edit</a>";

            $html .= "<div id='publish-enddate' style='display: none;'>\n";

            $html .= form_datepicker('af_enddate', '', $value, [
                    'class'           => 'form-group-sm m-t-5',
                    'date_format_js'  => 'DD-MM-YYYY HH:mm',
                    'date_format_php' => 'd-m-Y H:i',
                    'stacked'         => "<div class='spacer-xs'>".
                        form_button('confirm_enddate', 'Confirm', 'confirm_visibility', [
                            'class' => 'btn-xs ok',
                            'data'  => [
                                'display'   => '#enddate-display',
                                'container' => '#publish-enddate',
                                'source'    => '#af_enddate',
                                'input'     => '#enddate',
                            ],
                            'type'  => 'button'
                        ])
                        .form_button('cancel_enddate', 'Cancel', 'cancel_status', [
                            'class' => 'btn-link btn-xs cancel',
                            'data'  => ['container' => '#publish-enddate'],
                            'type' => 'button'
                        ])."</div>"
                ] + $startdate_append_options);

            $html .= form_hidden('enddate', '', $input_value, ['type'=>'timestamp']);

            $html .= "</div>\n";

            $html .= "</div>\n";

        }


        add_to_jquery("
        
        $('.meta-block .cancel').bind('click', function (ev) {
            var container;
            if ( container = $(this).data('container') ) {
                $(container).hide();                    
            } else {
                   alert('Container has to be defined.');            
            }
        });
                        
        $('.meta-block > .admin-form-edit-link').bind('click', function(ev) {
        
            ev.preventDefault();
        
            var target = $(this).data('target');
        
            if ( $(target).hasClass('on') ) {
                $(target).slideUp();
                $(target).removeClass('on');
            } else {
                $(target).slideDown();
                $(target).addClass('on');
            }
        });
        
        $('.meta-block .ok').bind('click', function(ev) {
        
            var container, source_input, input_text, input_value, hidden_input;
            
            ev.preventDefault();
            
            if ( source_input = $(this).data('source') ) { // source input field
            
                var source_type = $(source_input).prop('type');
                
                console.log(source_type);
                
                if (source_type == 'select-one') {
                    
                    var input_text = $(source_input + ' option:selected').text(); // source input text for source input
                    
                    var input_value = $(source_input).val(); // source input value for source input
                    
                } else if (source_type == 'radio') {
                    
                    var input_text = $(source_input + ':checked').closest('label').text(); // source input text for source input                    
                    
                    var input_value = $(source_input + ':checked').val(); // source input value for source input
                    
                } else if ( source_type == 'text') {
                    
                    // @todo: If checked on PHP Time is the value, then the text value must be - Immediately
                    
                    var input_text = $(source_input).val(); // source input text for source input                    
                    
                    var input_value = $(source_input).val(); // source input value for source input                    
                                     
                }
                
                if ( post_display = $(this).data('display') ) { // the display value container
                                    
                    $(post_display).text( input_text );
                    
                } else {
                    alert('Display container has to be defined.');
                }
            
                if ( hidden_input = $(this).data('input') ) { // the hidden input we need to fill
                    
                    $(hidden_input).val( input_value );
                                                
                } else {
                    alert('Input value field has to be defined.');
                }
                
                if ( container = $(this).data('container') ) {  // the wrapper container
                    
                    $(container).hide();
                    
                } else {
                    alert('Container has to be defined.');
                }
                                                
            } else {

                alert('Input value field has to be defined.');

            }
                        
        }); 
        
        $('input[name^=\"af_visibility\"]').bind('click', function(ev) {
            if ( $(this).val() == ".USER_LEVEL_PASSWORD." ) {
                $('#visibility-password').show();
            } else {
                $('#visibility-password').hide();
            }
        });
        
        ");

        $fhtml = form_button('remove_entry', 'Move to Trash', 'remove_entry', ['class' => 'text-danger']);

        $fhtml .= form_button('save_entry', 'Save', 'publish', ['class' => 'btn-primary']);

        return $this->render_panel([
            'title'       => 'Publish Options',
            'description' => $html,
            'footer'      => $fhtml
        ]);
    }

    function ui_categories() {
    }

    function ui_tags() {
    }

    function ui_image() {
    }

    function ui_gallery() {
    }

    /**
     * @param array $content
     *
     * @return mixed
     */
    private function render_panel(array $content) {
        global $fusion_steam;

        return $fusion_steam->load('blocks')->box($content);
    }

}