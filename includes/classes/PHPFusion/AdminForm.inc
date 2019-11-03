<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminForm.inc
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
namespace PHPFusion;

use Defender\Token;
use PHPFusion\Form\CategoryMeta;
use PHPFusion\Form\FormFactory;
use PHPFusion\Interfaces\AdminFormSDK;

/**
 * Class AdminForm
 *
 * @package PHPFusion
 */
class AdminForm {

    /**
     * @var string
     */
    private $main_content = '';

    /**
     * @var string
     */
    private $side_content = '';

    /**
     * @var string
     */
    private $bottom_content = '';

    /**
     * @var null|AdminFormSDK
     */
    protected $api = NULL;

    /**
     * @var null|FormFactory
     */
    private $factory = NULL;

    /**
     * AdminForm constructor.
     *
     * @param AdminFormSDK $api
     *
     * @throws \Exception
     */
    public function __construct(AdminFormSDK $api) {

        if ($api instanceof AdminFormSDK) {

            $this->api = $api;

            $this->factory = new FormFactory($api);

            $this->factory->process_request();

            $this->content();

            $this->bottom_content();

            $this->side_content();

            $this->prepare_js();

            $this->factory->process_form_actions();

            $this->output();

        } else {
            echo 'Your current object requires the implementation of the \PHPFusion\Interfaces\AdminFormSDK interface.';
        }
    }

    /**
     *
     */
    private function __clone() {
        die('Cloning of this class is prohibited');
    }

    /**
     *
     */
    private function prepare_js() {
        add_to_footer("<script src='".INCLUDES."jquery/form/admin-form.js'></script>");
        //@todo: Add ajax callback for 'save' button.
        //@todo: disable trash button for empty document.
    }

    /**
     * @throws \Exception
     */
    public function content() {

        $html = "<div class='block'>\n";

        $html .= form_hidden('id', '', $this->factory->field_value('id'), ['type' => 'number']);

        $html .= form_text('title', $this->factory->field_prop('title', 'label'), $this->factory->field_value('title'), $this->factory->field_prop('title', 'options', []) + ['inner_class' => 'main']);

        $html .= form_textarea('description', $this->factory->field_prop('description', 'label'), $this->factory->field_value('description'), $this->factory->field_prop('description', 'options', []) + ['inner_class' => 'main', 'height' => '500px']);

        $html .= "</div>\n";

        $this->main_content = $html;

    }

    /**
     *
     */
    public function bottom_content() {

        $html = "<div class='block'>";
        $html .= $this->api->custom($this->factory->data);
        $html .= "</div>\n";

        $this->bottom_content = $html;
    }

    /**
     *
     */
    private function output() {

        $tpl = Template::getInstance('admin_post');

        $tpl->set_template(THEMES.'templates/global/admin/admin_forms.html');

        $tpl->set_tag('content', $this->main_content);

        if ($this->side_content) {
            $tpl->set_block('admin_side', ['content' => $this->side_content]);
        }

        if ($this->bottom_content) {
            $tpl->set_block('admin_bottom', ['content' => $this->bottom_content]);
        }

        echo openform($this->factory->form_properties['form_name'], 'post').$tpl->get_output().closeform();

    }

    /**
     * @throws \Exception
     */
    private function side_content() {

        $this->side_content .=
            $this->ui_meta().
            $this->ui_meta_categories().
            $this->ui_meta_tags();

    }

    // make it into a steam.

    /**
     * @return mixed
     * @throws \Exception
     */
    private function ui_meta() {
        $html = '';

        $_field = $this->factory->field;

        if (!empty($_field['status'])) {

            $status_options = [
                '0' => 'Draft',
                '2' => 'Pending Review',
                '1' => 'Published',
            ];

            if (!empty($_field['status']['options']['options'])) {

                $status_options = $_field['status']['options']['options'];

            } else {

                $html = form_button('save_draft', 'Save Draft', 'save_draft', ['class' => 'btn-xs']);
            }

            $value = $this->factory->field_value('status');

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
                        'type'  => 'button'
                    ])]);

            $html .= form_hidden('status', '', $value, ['type' => 'number']);

            $html .= "</div>";

            $html .= "</div>\n";
        }

        if (!empty($_field['visibility'])) {

            $value = $this->factory->field_value('visibility');

            $visibility_options = [
                    USER_LEVEL_PASSWORD => 'Password Protected',
                    USER_LEVEL_PRIVATE  => 'Private',
                ] + fusion_get_groups();

            if (!empty($_field['visibility']['options']['options'])) {
                $visibility_options = $_field['visibility']['options']['options'];
            }

            $display_value = $visibility_options[0];
            if (isset($visibility_options[$value])) {
                $display_value = $visibility_options[$value];
            }

            $html .= "<div class='meta-block m-b-5'>\n";

            $html .= "<i class='fas fa-eye fa-fw'></i> Access: <strong id='visibility-display'>$display_value</strong> <a  href='#' class='admin-form-edit-link' data-target='#publish-visibility'>Edit</a>";

            $html .= "<div id='publish-visibility' style='display: none;'>\n";

            $html .= form_checkbox('af_visibility', '', $value, [
                'type'    => 'radio',
                'class'   => 'm-b-0',
                'options' => $visibility_options
            ]);

            $html .= "<div id='visibility-password' style='display: none;' >".form_text('af_password', '', '', ['type' => 'password', 'class' => 'form-group-sm', 'placeholder' => 'Enter password'])."</div>";

            $html .= form_button('confirm_visibility', 'OK', 'confirm_visibility', [
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
                    'type'  => 'button'
                ]);

            $html .= form_hidden('visibility', '', $value);

            $html .= "</div>\n";

            $html .= "</div>\n";

        }

        if (!empty($_field['startdate'])) {

            $value = $this->factory->field_value('startdate');

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
                            'type'  => 'button'
                        ] + $enddate_append_options)."</div>"
            ]);

            $html .= form_hidden('startdate', '', $input_value, ['type' => 'timestamp']);

            $html .= "</div>\n";

            $html .= "</div>\n";

        }

        if (!empty($_field['enddate'])) {

            $value = $this->factory->field_value('enddate');

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
                            'type'  => 'button'
                        ])."</div>"
                ] + $startdate_append_options);

            $html .= form_hidden('enddate', '', $input_value, ['type' => 'timestamp']);

            $html .= "</div>\n";

            $html .= "</div>\n";

        }

        $fhtml = '';
    
        // Remove
        if (!empty($this->factory->get_remove_prop('name')) &&  !empty($this->factory->data[$this->factory->field['id']['name']])) { // what is the primary column?

            $fhtml = form_button($this->factory->get_remove_prop('name'),
                $this->factory->get_remove_prop('label'),
                $this->factory->get_remove_prop('value'),
                $this->factory->get_remove_prop('options')
            );
        }
    
        // Save
        $fhtml .= form_button($this->factory->get_save_prop('name'),
            $this->factory->get_save_prop('label'),
            $this->factory->get_save_prop('value'),
            $this->factory->get_save_prop('options') ? $this->factory->get_save_prop('options') : []);

        $button[] = "#".$this->factory->get_save_prop('name');
    
        // Save and Close
        if (!empty($this->factory->form_properties['back_link'])) {
            $fhtml .= form_button($this->factory->get_save_close_prop('name'),
                $this->factory->get_save_close_prop('label'),
                $this->factory->get_save_close_prop('value'),
                $this->factory->get_save_close_prop('options'));
            $button[] = "#".$this->factory->get_save_close_prop('name');
        }

        return $this->render_panel([
            'title'       => 'Publish Options',
            'description' => $html,
            'footer'      => $fhtml
        ]);

    }

    /**
     * Category Meta
     * @return mixed
     * @throws \Exception
     */
    function ui_meta_categories() {
        if ( $this->factory->categories ) {
            return (string)$this->render_panel( [
                'title'       => 'Categories',
                'description' => CategoryMeta::getInstance( $this->factory )->displayMeta()
            ] );
        }
        return '';
    }

    /**
     * @return mixed
     */
    function ui_meta_tags() {
        if ( $this->factory->tags ) {
            return (string)$this->render_panel( [
                'title'       => 'Tags',
                'description' => Form\TagsMeta::getInstance( $this->factory )->displayMeta()
            ] );
        }
        return '';
    }

    /**
     *
     */
    function ui_image() {
    }

    /**
     *
     */
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
