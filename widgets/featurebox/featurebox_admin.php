<?php

/**
 * Class featureboxWidgetAdmin
 */
class featureboxWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine {

    private static $widget_data = array();

    public function exclude_return() {
    }

    public function validate_input() {

        if (isset($_POST['save_widget']) or isset($_POST['save_and_close_widget'])) {

            self::$widget_data = array(
                'box_title' => form_sanitizer($_POST['box_title'], '', 'box_title'),
                'box_description' => form_sanitizer($_POST['box_description'], '', 'box_description'),
                'box_icon_class' => form_sanitizer($_POST['box_icon_class'], '', 'box_icon_class'),
                'box_icon_size' => form_sanitizer($_POST['box_icon_size'], '0', 'box_icon_size'),
                'box_icon_color' => form_sanitizer($_POST['box_icon_color'], '', 'box_icon_color'),
                'box_stacked_icon_class' => form_sanitizer($_POST['box_stacked_icon_class'], '',
                                                           'box_stacked_icon_class'),
                'box_stacked_icon_size' => form_sanitizer($_POST['box_stacked_icon_size'], '0',
                                                          'box_stacked_icon_size'),
                'box_stacked_icon_color' => form_sanitizer($_POST['box_stacked_icon_color'], '',
                                                           'box_stacked_icon_color'),
                'box_icon_margin_top' => form_sanitizer($_POST['box_icon_margin_top'], '', 'box_icon_margin_top'),
                'box_icon_margin_bottom' => form_sanitizer($_POST['box_icon_margin_bottom'], '',
                                                           'box_icon_margin_bottom'),
                'box_padding' => form_sanitizer($_POST['box_padding'], '', 'box_padding'),
                'box_link' => form_sanitizer($_POST['box_link'], '', 'box_link'),
                'box_link_class' => form_sanitizer($_POST['box_link_class'], '', 'box_link_class'),
                'box_link_margin_top' => form_sanitizer($_POST['box_link_margin_top'], '', 'box_link_margin_top'),
                'box_link_margin_bottom' => form_sanitizer($_POST['box_link_margin_bottom'], '',
                                                           'box_link_margin_bottom'),
            );

            if (defender::safe() && !empty(self::$widget_data)) {
                // sort according to slider order
                self::$widget_data = serialize(self::$widget_data);
            }
        }

        return self::$widget_data;
    }


    public function display_input() {
        self::$widget_data = unserialize(self::$colData['page_content']);
        self::featurebox_form();
    }

    private function featurebox_form() {
        self::$widget_data = array(
            'box_title' => '',
            'box_description' => '',
            'box_icon_class' => '',
            'box_icon_size' => '',
            'box_icon_color' => '',
            'box_stacked_icon_class' => '',
            'box_stacked_icon_size' => '',
            'box_stacked_icon_color' => '',
            'box_icon_margin_top' => '',
            'box_icon_margin_bottom' => '',
            'box_padding' => '',
            'box_link' => '',
            'box_link_class' => '',
            'box_link_margin_top' => '',
            'box_link_margin_bottom' => '',
        );

        if (!empty(self::$colData['page_content']) && isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit' && isset($_GET['widgetKey'])) {
            self::$widget_data = unserialize(self::$colData['page_content']);
        }

        echo form_text('box_title', 'Box Title', self::$widget_data['box_title'], array('inline' => TRUE));
        echo form_text('box_description', 'Box Description', self::$widget_data['box_description'],
                       array('inline' => TRUE));
        echo form_text('box_icon_class', 'Box Icon', self::$widget_data['box_description'], array(
            'inline' => TRUE, 'ext_tip' => 'Please refer to your svg icon code. i.e. Font-Awesome: "fa fa-thumbs-up-o"'
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

    public function display_button() {
        echo form_button('save_widget', 'Save Feature Box', 'save_widget', array('class' => 'btn-primary'));
        echo form_button('save_and_close_widget', 'Save and Close Widget', 'save_and_close_widget',
                         array('class' => 'btn-success'));
    }

}