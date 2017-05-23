<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
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
class featureboxWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $widget_data = array();

    private static $instance = NULL;

    public static function widgetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function exclude_return() {
    }

    public function validate_settings() {
    }

    public function validate_input() {

        self::$widget_data = array(
            'box_title'              => form_sanitizer($_POST['box_title'], '', 'box_title'),
            'box_description'        => form_sanitizer($_POST['box_description'], '', 'box_description'),
            'box_class'              => form_sanitizer($_POST['box_class'], '', 'box_class'),
            'box_icon_type'          => form_sanitizer($_POST['box_icon_type'], 0, 'box_icon_type'),
            'box_icon_margin_top'    => form_sanitizer($_POST['box_icon_margin_top'], '', 'box_icon_margin_top'),
            'box_icon_margin_bottom' => form_sanitizer($_POST['box_icon_margin_bottom'], '', 'box_icon_margin_bottom'),
            'box_padding'            => form_sanitizer($_POST['box_padding'], '', 'box_padding'),
            'box_link'               => form_sanitizer($_POST['box_link'], '', 'box_link'),
            'box_link_class'         => form_sanitizer($_POST['box_link_class'], '', 'box_link_class'),
            'box_link_margin_top'    => form_sanitizer($_POST['box_link_margin_top'], '', 'box_link_margin_top'),
            'box_link_margin_bottom' => form_sanitizer($_POST['box_link_margin_bottom'], '', 'box_link_margin_bottom'),
            'box_icon_class'         => '',
            'box_icon_size'          => '',
            'box_icon_color'         => '',
            'box_stacked_icon_class' => '',
            'box_stacked_icon_size'  => '',
            'box_stacked_icon_color' => '',
            'box_icon_src'           => ''
        );
        /**
         * Selector
         */
        if (!self::$widget_data['box_icon_type']) {
            $icon_type_data = array(
                'box_icon_class'         => form_sanitizer($_POST['box_icon_class'], '', 'box_icon_class'),
                'box_icon_size'          => form_sanitizer($_POST['box_icon_size'], '0', 'box_icon_size'),
                'box_icon_color'         => form_sanitizer($_POST['box_icon_color'], '', 'box_icon_color'),
                'box_stacked_icon_class' => form_sanitizer($_POST['box_stacked_icon_class'], '', 'box_stacked_icon_class'),
                'box_stacked_icon_size'  => form_sanitizer($_POST['box_stacked_icon_size'], '0', 'box_stacked_icon_size'),
                'box_stacked_icon_color' => form_sanitizer($_POST['box_stacked_icon_color'], '', 'box_stacked_icon_color'),
            );
            self::$widget_data = array_merge(self::$widget_data, $icon_type_data);
        } else {
            // must have uploaded or selected something
            if (!empty($_FILES['box_icon_src']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['box_icon_src'], '', 'box_icon_src');
                if (empty($upload['error'])) {
                    self::$widget_data['box_icon_src'] = $upload['image_name'];
                }
            } else {
                self::$widget_data['box_icon_src'] = form_sanitizer($_POST['box_icon_src-mediaSelector'], '', 'box_icon_src-mediaSelector');
            }
        }
        if (\defender::safe()) {
            return \defender::serialize(self::$widget_data);
        }
    }

    public function validate_delete() {
    }

    public function display_form_input() {
        $lang = file_exists(WIDGETS."featurebox/locale/".LANGUAGE.".php") ? WIDGETS."featurebox/locale/".LANGUAGE.".php" : WIDGETS."featurebox/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);


        self::$widget_data = array(
            'box_title'              => '',
            'box_description'        => '',
            'box_class'              => '',
            'box_icon_class'         => '',
            'box_icon_type'          => 0,
            'box_icon_src'           => '',
            'box_icon_size'          => '30',
            'box_icon_color'         => '',
            'box_stacked_icon_class' => '',
            'box_stacked_icon_size'  => '60',
            'box_stacked_icon_color' => '',
            'box_icon_margin_top'    => '15',
            'box_icon_margin_bottom' => '15',
            'box_padding'            => '30',
            'box_link'               => '',
            'box_link_class'         => '',
            'box_link_margin_top'    => '15',
            'box_link_margin_bottom' => '20',
        );

        if (!empty(self::$colData['page_content'])) {
            self::$widget_data = \defender::unserialize(self::$colData['page_content']);
        }

        echo form_text('box_title', $widget_locale['FBW_0200'], self::$widget_data['box_title'], array('inline' => TRUE));
        echo form_textarea('box_description', $widget_locale['FBW_0201'], self::$widget_data['box_description'], array('inline' => TRUE));
        echo form_text('box_class', $widget_locale['FBW_0230'], self::$widget_data['box_class'], array('inline' => TRUE));
        echo form_select('box_icon_type', $widget_locale['FBW_0202'], self::$widget_data['box_icon_type'],
            array(
                'options' => array(
                    0 => $widget_locale['FBW_0203'],
                    1 => $widget_locale['FBW_0204']
                ),
                'inline'  => TRUE,
            )
        );
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
        if (!file_exists(IMAGES.'featurebox')) {
            mkdir(IMAGES.'featurebox');
        }
        echo "<div id='featureboxImage' class='row' ".(self::$widget_data['box_icon_type'] == 0 ? " style='display:none;'" : "").">\n<div class='col-xs-12 col-sm-3'>\n";
        echo "<strong>".$widget_locale['FBW_0204']."</strong><br/><i>".$widget_locale['FBW_0205']."</i>";
        echo "</div><div class='col-xs-12 col-sm-9'>\n";
        echo form_fileinput('box_icon_src', '', self::$widget_data['box_icon_src'],
            array('inline' => TRUE, 'media' => TRUE, 'upload_path' => IMAGES."icon/", 'template' => 'modern'));
        echo "</div>\n</div>\n";

        echo "<div id='featureboxIcon' class='row' ".(self::$widget_data['box_icon_type'] == 1 ? " style='display:none;'" : "").">\n<div class='col-xs-12 col-sm-3'>\n";
        echo "<strong>".$widget_locale['FBW_0203']."</strong><br/><i>".$widget_locale['FBW_0206']."</i>";
        echo "</div><div class='col-xs-12 col-sm-9'>\n";
        echo form_text('box_icon_class', $widget_locale['FBW_0207'], self::$widget_data['box_icon_class'],
            array(
                'inline'   => TRUE,
                'required' => TRUE,
                'ext_tip'  => $widget_locale['FBW_0208']
            )
        );
        echo form_text('box_icon_size', $widget_locale['FBW_0209'], self::$widget_data['box_icon_size'],
            array(
                'inline'       => TRUE,
                'type'         => 'number',
                'append'       => TRUE,
                'append_value' => 'px',
                'width'        => '100px',
                'ext_tip'      => $widget_locale['FBW_0210']
            ));
        echo form_colorpicker('box_icon_color', $widget_locale['FBW_0211'], self::$widget_data['box_icon_color'], array('inline' => TRUE));
        echo form_text('box_stacked_icon_class', $widget_locale['FBW_0212'], self::$widget_data['box_stacked_icon_class'],
            array(
                'inline'  => TRUE,
                'ext_tip' => $widget_locale['FBW_0208']
            ));
        echo form_text('box_stacked_icon_size', $widget_locale['FBW_0213'], self::$widget_data['box_stacked_icon_size'],
            array(
                'inline'       => TRUE,
                'type'         => 'number',
                'append'       => TRUE,
                'append_value' => 'px',
                'width'        => '100px',
                'ext_tip'      => $widget_locale['FBW_0210']
            ));
        echo form_colorpicker('box_stacked_icon_color', $widget_locale['FBW_0214'], self::$widget_data['box_stacked_icon_color'],
            array('inline' => TRUE));
        echo "</div>\n</div>\n";
        echo form_text('box_link', $widget_locale['FBW_0215'], self::$widget_data['box_link'], array('inline' => TRUE, 'type' => 'url'));
        echo form_text('box_link_class', $widget_locale['FBW_0216'], self::$widget_data['box_link_class'], array('inline' => TRUE));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong><?php echo $widget_locale['FBW_0217'] ?></strong><br/>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_text('box_icon_margin_top', $widget_locale['FBW_0218'], self::$widget_data['box_icon_margin_top'],
                    array(
                        'inline'       => TRUE,
                        'type'         => 'number',
                        'append'       => TRUE,
                        'append_value' => 'px',
                        'width'        => '100px',
                        'ext_tip'      => $widget_locale['FBW_0219']
                    )
                );
                echo form_text('box_icon_margin_bottom', $widget_locale['FBW_0220'], self::$widget_data['box_icon_margin_bottom'],
                    array(
                        'inline'       => TRUE,
                        'type'         => 'number',
                        'append'       => TRUE,
                        'append_value' => 'px',
                        'width'        => '100px',
                        'ext_tip'      => $widget_locale['FBW_0221'],
                    ));
                echo form_text('box_padding', $widget_locale['FBW_0222'], self::$widget_data['box_padding'],
                    array(
                        'inline'       => TRUE,
                        'placeholder'  => "15px 30px 15px 30px",
                        'type'         => 'number',
                        'append'       => TRUE,
                        'append_value' => 'px',
                        'width'        => '100px',
                        'ext_tip'      => $widget_locale['FBW_0223']
                    )
                );
                echo form_text('box_link_margin_top', $widget_locale['FBW_0224'], self::$widget_data['box_link_margin_top'],
                    array(
                        'inline'       => TRUE,
                        'type'         => 'number',
                        'append'       => TRUE,
                        'append_value' => 'px',
                        'width'        => '100px',
                        'ext_tip'      => $widget_locale['FBW_0225']
                    ));
                echo form_text('box_link_margin_bottom', $widget_locale['FBW_0226'], self::$widget_data['box_link_margin_bottom'],
                    array(
                        'inline'       => TRUE,
                        'type'         => 'number',
                        'append'       => TRUE,
                        'append_value' => 'px',
                        'width'        => '100px',
                        'ext_tip'      => $widget_locale['FBW_0227'],
                    )
                );
                ?>
            </div>
        </div>
        <?php
    }

    public function display_form_button() {
        $widget_locale = fusion_get_locale('', WIDGETS."/featurebox/locale/".LANGUAGE.".php");
        echo form_button('save_widget', $widget_locale['FBW_0228'], 'widget', array('class' => 'btn-primary'));
        echo form_button('save_and_close_widget', $widget_locale['FBW_0229'], 'widget', array('class' => 'btn-success'));
    }

}