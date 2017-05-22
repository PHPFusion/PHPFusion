<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Block/block_admin.php
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
 * Class blockWidgetAdmin
 */
class blockWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $instance = NULL;
    private static $widget_data = array();

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
            'block_title' => form_sanitizer($_POST['block_title'], '', 'block_title'),
            'block_description' => form_sanitizer($_POST['block_description'], '', 'block_description'),
            'block_align' => form_sanitizer($_POST['block_align'], '', 'block_align'),
            'block_class' => form_sanitizer($_POST['block_class'], '', 'block_class'),
            'block_margin' => form_sanitizer($_POST['block_margin'], '', 'block_margin'),
            'block_padding' => form_sanitizer($_POST['block_padding'], '', 'block_padding'),
        );
        if (\defender::safe()) {
            return \defender::serialize(self::$widget_data);
        }
    }

    public function validate_delete() {
    }

    public function display_form_input() {
        $lang = file_exists(WIDGETS."block/locale/".LANGUAGE.".php") ? WIDGETS."block/locale/".LANGUAGE.".php" : WIDGETS."block/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);

        self::$widget_data = array(
            'block_title'       => '',
            'block_description' => '',
            'block_align'       => '',
            'block_class'       => '',
            'block_padding'     => '30px',
            'block_margin'      => '15px 0px',
        );

        if (!empty(self::$colData['page_content'])) {
            self::$widget_data = \defender::unserialize(self::$colData['page_content']);
        }

        echo form_text('block_title', $widget_locale['BLKW_0200'], self::$widget_data['block_title'], array('inline' => TRUE));
        echo form_textarea('block_description', $widget_locale['BLKW_0201'], self::$widget_data['block_description'], array('inline' => TRUE));
        echo form_select('block_align', $widget_locale['BLKW_0202'], self::$widget_data['block_align'],
                         array(
                             'inline' => TRUE,
                             'options' => array(
                                 '0'           => $widget_locale['BLKW_0203'],
                                 'text-left'   => $widget_locale['BLKW_0204'],
                                 'text-center' => $widget_locale['BLKW_0205'],
                                 'text-right'  => $widget_locale['BLKW_0206'],
                             )
                         )
        );
        echo form_text('block_class', $widget_locale['BLKW_0207'], self::$widget_data['block_class'], array('inline' => TRUE));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong><?php echo $widget_locale['BLKW_0218'] ?></strong><br/>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_text('block_margin', $widget_locale['BLKW_0219'], self::$widget_data['block_margin'],
                               array(
                                   'inline' => TRUE,
                                   'width' => '250px',
                               )
                );
                echo form_text('block_padding', $widget_locale['BLKW_0220'], self::$widget_data['block_padding'],
                               array(
                                   'inline' => TRUE,
                                   'width' => '250px',
                               )
                );
                ?>
            </div>
        </div>
        <?php
    }

    public function display_form_button() {
        $widget_locale = fusion_get_locale('', WIDGETS."/block/locale/".LANGUAGE.".php");
        echo form_button('save_widget', $widget_locale['BLKW_0221'], 'widget', array('class' => 'btn-primary'));
        echo form_button('save_and_close_widget', $widget_locale['BLKW_0222'], 'widget', array('class' => 'btn-success'));
    }

}