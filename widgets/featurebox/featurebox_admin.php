<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Featurebox/featurebox_admin.php
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
/**
 * Class featureboxWidgetAdmin
 */
class featureboxWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $widget_data = array();

    public function exclude_return() {
    }

    public function validate_settings() {
    }

    public function validate_input() {

        self::$widget_data = array(
            'box_title' => form_sanitizer($_POST['box_title'], '', 'box_title'),
            'box_description' => form_sanitizer($_POST['box_description'], '', 'box_description'),
            'box_icon_type' => form_sanitizer($_POST['box_icon_type'], '', 'box_icon_type'),
            'box_icon_margin_top' => form_sanitizer($_POST['box_icon_margin_top'], '', 'box_icon_margin_top'),
            'box_icon_margin_bottom' => form_sanitizer($_POST['box_icon_margin_bottom'], '',
                                                       'box_icon_margin_bottom'),
            'box_padding' => form_sanitizer($_POST['box_padding'], '', 'box_padding'),
            'box_link' => form_sanitizer($_POST['box_link'], '', 'box_link'),
            'box_link_class' => form_sanitizer($_POST['box_link_class'], '', 'box_link_class'),
            'box_link_margin_top' => form_sanitizer($_POST['box_link_margin_top'], '', 'box_link_margin_top'),
            'box_link_margin_bottom' => form_sanitizer($_POST['box_link_margin_bottom'], '',
                                                       'box_link_margin_bottom'),
            'box_icon_class' => '',
            'box_icon_size' => '',
            'box_icon_color' => '',
            'box_stacked_icon_class' => '',
            'box_stacked_icon_size' => '',
            'box_stacked_icon_color' => '',
            'box_icon_src' => ''
        );

        /**
         * Selector
         */
        if (self::$widget_data['box_icon_type'] == 0) {
            self::$widget_data += array(
                'box_icon_class' => form_sanitizer($_POST['box_icon_class'], '', 'box_icon_class'),
                'box_icon_size' => form_sanitizer($_POST['box_icon_size'], '0', 'box_icon_size'),
                'box_icon_color' => form_sanitizer($_POST['box_icon_color'], '', 'box_icon_color'),
                'box_stacked_icon_class' => form_sanitizer($_POST['box_stacked_icon_class'], '',
                                                           'box_stacked_icon_class'),
                'box_stacked_icon_size' => form_sanitizer($_POST['box_stacked_icon_size'], '0',
                                                          'box_stacked_icon_size'),
                'box_stacked_icon_color' => form_sanitizer($_POST['box_stacked_icon_color'], '',
                                                           'box_stacked_icon_color'),
            );
        } else {
            // must have uploaded or selected something
            if (!empty($_FILES['box_icon_src']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['box_icon_src'], '', 'box_icon_src');
                if (empty($upload['error'])) {
                    self::$widget_data['box_icon_src'] = $upload['image_name'];
                }
            } else {
                self::$widget_data['box_icon_src'] = form_sanitizer($_POST['box_icon_src-mediaSelector'], '',
                                                                    'box_icon_src-mediaSelector');
            }
        }

        if (defender::safe()) {
            return (string)serialize(self::$widget_data);
        }

        return (string)serialize(self::$widget_data);
    }

    // There should be a settings..?
    public function display_form_input() {
        self::featurebox_form();
    }

    private function featurebox_form() {

        $widget_locale = fusion_get_locale('', WIDGETS."/featurebox/locale/".LANGUAGE.".php");

        self::$widget_data = array(
            'box_title' => '',
            'box_description' => '',
            'box_icon_class' => '',
            'box_icon_type' => 0,
            'box_icon_src' => '',
            'box_icon_size' => '30',
            'box_icon_color' => '',
            'box_stacked_icon_class' => '',
            'box_stacked_icon_size' => '60',
            'box_stacked_icon_color' => '',
            'box_icon_margin_top' => '15',
            'box_icon_margin_bottom' => '15',
            'box_padding' => '30',
            'box_link' => '',
            'box_link_class' => '',
            'box_link_margin_top' => '15',
            'box_link_margin_bottom' => '20',
        );

        if (!empty(self::$colData['page_content'])) {
            self::$widget_data = unserialize(self::$colData['page_content']);
        }

        echo form_text('box_title', 'Box Title', self::$widget_data['box_title'], array('inline' => TRUE));
        echo form_text('box_description', 'Box Description', self::$widget_data['box_description'],
                       array('inline' => TRUE));

        echo form_select('box_icon_type', 'Box Icon Type', self::$widget_data['box_icon_type'],
                         array(
                             'options' => array(0 => 'CSS Format', 1 => 'Image'),
                             'inline' => TRUE,
                         ));

        add_to_jquery("
        $('#box_icon_type').bind('change', function(e) {
            var icon_type = $(this).val();
            if (icon_type == 0) {
                $('#featureboxImage').hide();
                $('#featureboxIcon').slideDown();
            } else {
                $('#featureboxIcon').hide();
                $('#featureboxImage').slideDown();
            }
        });
        ");

        if (!file_exists(IMAGES.'icon')) {
            mkdir(IMAGES.'icon');
        }

        echo "<div id='featureboxImage' class='row' ".(self::$widget_data['box_icon_type'] == 0 ? " style='display:none;'" : "").">\n<div class='col-xs-12 col-sm-3'>\n";
        echo "<strong>Image Icon</strong><br/><i>Please choose or upload your icon image</i>";
        echo "</div><div class='col-xs-12 col-sm-9'>\n";
        echo form_fileinput('box_icon_src', '', self::$widget_data['box_icon_src'], array(
            'inline' => TRUE, 'media' => TRUE, 'upload_path' => IMAGES."icon/", 'template' => 'modern'
        ));
        echo "</div>\n</div>\n";


        echo "<div id='featureboxIcon' class='row' ".(self::$widget_data['box_icon_type'] == 1 ? " style='display:none;'" : "").">\n<div class='col-xs-12 col-sm-3'>\n";
        echo "<strong>CSS Icon</strong><br/><i>Please fill in the relevant column</i>";
        echo "</div><div class='col-xs-12 col-sm-9'>\n";
        echo form_text('box_icon_class', 'Box Icon', self::$widget_data['box_icon_class'], array(
            'inline' => TRUE, 'required' => TRUE,
            'ext_tip' => 'Please refer to your svg icon code. i.e. Font-Awesome: "fa fa-thumbs-up-o"'
        ));
        echo form_text('box_icon_size', 'Box Icon Size', self::$widget_data['box_icon_size'], array(
            'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px', 'width' => '100px',
            'ext_tip' => 'Pixels. Negative number not allowed'
        ));
        echo form_colorpicker('box_icon_color', 'Box Icon Color', self::$widget_data['box_icon_color'],
                              array('inline' => TRUE));
        echo form_text('box_stacked_icon_class', 'Box Stacked Icon', self::$widget_data['box_stacked_icon_class'],
                       array(
                           'inline' => TRUE,
                           'ext_tip' => 'Please refer to your svg icon code. i.e. Font-Awesome: "fa fa-thumbs-up-o"'
                       ));
        echo form_text('box_stacked_icon_size', 'Box Stacked Icon Size', self::$widget_data['box_stacked_icon_size'],
                       array(
                           'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px',
                           'width' => '100px', 'ext_tip' => 'Pixels. Negative number not allowed'
                       ));
        echo form_colorpicker('box_stacked_icon_color', 'Box Stacked Icon Color',
                              self::$widget_data['box_stacked_icon_color'], array('inline' => TRUE));
        echo "</div>\n</div>\n";


        echo form_text('box_link', 'Box Link', self::$widget_data['box_link'],
                       array('inline' => TRUE, 'type' => 'url'));
        echo form_text('box_link_class', 'Box Link Class', self::$widget_data['box_link_class'],
                       array('inline' => TRUE));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong>Box Elements Spacing</strong><br/><i>Units in Pixels. (Negative number not allowed)</i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_text('box_icon_margin_top', 'Icon Margin Top', self::$widget_data['box_icon_margin_top'],
                               array(
                                   'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px',
                                   'ext_tip' => 'Specify top margin between icon and the border', 'width' => '100px'
                               ));
                echo form_text('box_icon_margin_bottom', 'Icon Margin Bottom',
                               self::$widget_data['box_icon_margin_bottom'], array(
                                   'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px',
                                   'ext_tip' => 'Specify bottom margin between icon and the title', 'width' => '100px'
                               ));
                echo form_text('box_padding', 'Box Padding', self::$widget_data['box_padding'], array(
                    'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px',
                    'ext_tip' => 'Specify distance of top, left, right, bottom of the feature box', 'width' => '100px'
                ));
                echo form_text('box_link_margin_top', 'Box Link Margin Top', self::$widget_data['box_link_margin_top'],
                               array(
                                   'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px',
                                   'ext_tip' => 'Specify the top margin between link and the description',
                                   'width' => '100px'
                               ));
                echo form_text('box_link_margin_bottom', 'Box Link Margin Bottom',
                               self::$widget_data['box_link_margin_bottom'], array(
                                   'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px',
                                   'ext_tip' => 'Specify the bottom margin between link and the border',
                                   'width' => '100px'
                               ));
                ?>
            </div>
        </div>
        <?php
    }

    public function display_form_button() {
        echo form_button('save_widget', 'Save Feature Box', 'widget', array('class' => 'btn-primary'));
        echo form_button('save_and_close_widget', 'Save and Close Widget', 'widget', array('class' => 'btn-success'));
    }

}