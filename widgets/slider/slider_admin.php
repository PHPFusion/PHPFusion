<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Slider/slider_admin.php
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
 * Class carouselWidgetAdmin
 * To use WidgetAdminInterface - Widget SDK Standard Guidelines
 */
class carouselWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $widget_data = array();
    private static $exclude_return = array('slider', 'sliderAction', 'widgetAction', 'widgetKey');
    private static $slider_settings = [];
    private static $slider_content = [];
    private static $slider_tab = [];
    private static $slider_locale = [];
    private static $tab_active = '';
    private static $new_slider = FALSE;
    private static $default_slider_data = array(
        'slider_image_src' => '',
        'slider_title' => '',
        'slider_description' => '',
        'slider_link' => '',
        'slider_order' => '',
        'slider_caption_offset' => 100,
        'slider_caption_align' => 'left',
        'slider_title_size' => 30,
        'slider_desc_size' => 15,
        'slider_btn_size' => 'normal',
    );
    private static $default_slider_settings = array(
        'slider_id' => '',
        'slider_path' => 0,
        'slider_height' => '300',
        'slider_navigation' => TRUE,
        'slider_interval' => 200,
        'slider_indicator' => TRUE,
    );
    private static $widget_instance = NULL;

    public static function widgetInstance() {

        if (self::$widget_instance === NULL) {

            self::$widget_instance = new static();

            $lang = file_exists(WIDGETS."slider/locale/".LANGUAGE.".php") ? WIDGETS."slider/locale/".LANGUAGE.".php" : WIDGETS."slider/locale/English.php";
            self::$slider_locale = fusion_get_locale('', $lang);

            if (!empty(self::$colData['page_content'])) {

                self::$slider_content = \defender::unserialize(self::$colData['page_content']);

                // Delete
                if (isset($_GET['widgetAction']) && isset($_GET['widgetKey']) && isnum($_GET['widgetKey'])) {

                    if (isset(self::$slider_content[$_GET['widgetKey']])) {

                        switch ($_GET['widgetAction']) {

                            case 'del':
                                if (isset(self::$slider_content[$_GET['widgetKey']])) {

                                    unset(self::$slider_content[$_GET['widgetKey']]);

                                    if (!empty(self::$slider_content)) {
                                        $slider_arr = array_combine(range(1, count(self::$slider_content)), array_values(self::$slider_content));
                                    } else {
                                        $slider_arr = array_values(self::$slider_content);
                                    }

                                    if (!empty($slider_arr)) {
                                        $tmp_slider_arr = $slider_arr;
                                        foreach ($tmp_slider_arr as $key => $newData) {
                                            $newData['slider_order'] = $key;
                                            $slider_arr[$key] = $newData;
                                        }
                                    }

                                    self::$colData['page_content'] = \defender::serialize($slider_arr);
                                    dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'update');
                                    addNotice('success', self::$slider_locale['SLDW_0200']);
                                    redirect(clean_request('slider=cur_slider', array('widgetAction', 'widgetKey'), FALSE));
                                }

                                break;
                            case 'edit':
                                self::$slider_content = self::$slider_content[$_GET['widgetKey']];
                                break;
                        }
                    } else {
                        redirect(clean_request('slider=cur_slider', array('widgetAction', 'widgetKey'), FALSE));
                    }
                }
            }

            self::$slider_content += self::$default_slider_data;

            // Parse Slider Settings
            if (!empty(self::$colData['page_options'])) {
                self::$slider_settings = \defender::unserialize(self::$colData['page_options']);
            }
            self::$slider_settings += self::$default_slider_settings;

            // Tab Interface
            if (empty(self::$colData['page_options'])) {

                self::$new_slider = TRUE;
                self::$slider_tab['title'][2] = self::$slider_locale['SLDW_0406'];
                self::$slider_tab['id'][2] = "slider_settings";
                self::$tab_active = self::$slider_tab['id'][2];

            } else {

                self::$slider_tab['title'][0] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? self::$slider_locale['back'] : self::$slider_locale['SLDW_0300']);
                self::$slider_tab['id'][0] = "cur_slider";

                self::$slider_tab['title'][1] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? self::$slider_locale['SLDW_0301'] : self::$slider_locale['SLDW_0302']);
                self::$slider_tab['id'][1] = "slider_frm";

                self::$slider_tab['title'][2] = self::$slider_locale['SLDW_0303'];
                self::$slider_tab['id'][2] = "slider_settings";

                self::$tab_active = isset($_GET['slider']) && in_array($_GET['slider'],
                                                                       self::$slider_tab['id']) ? $_GET['slider'] : self::$slider_tab['id'][0];
            }

        }

        return self::$widget_instance;
    }

    public function exclude_return() {
        return self::$exclude_return;
    }

    public function validate_input() {

        $widget_data = array();

        if (!empty(self::$colData['page_content'])) {
            $widget_data = \defender::unserialize(self::$colData['page_content']);
        }

        $data = array(
            'slider_title' => form_sanitizer($_POST['slider_title'], '', 'slider_title'),
            'slider_description' => form_sanitizer($_POST['slider_description'], '', 'slider_description'),
            'slider_link' => form_sanitizer($_POST['slider_link'], '', 'slider_link'),
            'slider_order' => form_sanitizer($_POST['slider_order'], 0, 'slider_order'),
            'slider_caption_offset' => form_sanitizer($_POST['slider_caption_offset'], 0, 'slider_caption_offset'),
            'slider_caption_align' => form_sanitizer($_POST['slider_caption_align'], '', 'slider_caption_align'),
            'slider_title_size' => form_sanitizer($_POST['slider_title_size'], '', 'slider_title_size'),
            'slider_desc_size' => form_sanitizer($_POST['slider_desc_size'], '', 'slider_desc_size'),
            'slider_btn_size' => form_sanitizer($_POST['slider_btn_size'], '', 'slider_btn_size')
        );

        if ($data['slider_order'] == 0) {
            $data['slider_order'] = count($widget_data) + 1;
        }

        if (\defender::safe()) {
            if (!empty($_FILES['slider_image_src']['tmp_name'])) {
                $upload = form_sanitizer($_FILES['slider_image_src'], '', 'slider_image_src');
                if (empty($upload['error'])) {
                    $data['slider_image_src'] = $upload['image_name'];
                }
            } else {
                $data['slider_image_src'] = form_sanitizer($_POST['slider_image_src-mediaSelector'], '', 'slider_image_src-mediaSelector');
            }
        }

        // The new is always the last one
        if (!empty($widget_data)) {
            reset($widget_data);
            $count = 1;
            foreach ($widget_data as $key => $arrayOrder) {
                $widget_data[$key]['slider_order'] = $count;
                $count++;
            }
        }

        // Now merge
        if (isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit' && isset($_GET['widgetKey']) && isset($widget_data[$_GET['widgetKey']])) {
            $widget_data[$_GET['widgetKey']] = $data;
        } else {
            $new_widget_data[] = $data;
            $widget_data = array_merge_recursive($widget_data, $new_widget_data);
        }

        $widget_data = sorter($widget_data, 'slider_order');

        $widget_data = array_values($widget_data);
        $count = 1;
        foreach ($widget_data as $key => $arrayOrder) {
            $widget_data[$key]['slider_order'] = $count;
            $count++;
        }

        if (\defender::safe() && !empty($widget_data)) {
            $widget_data = \defender::serialize($widget_data);
            return $widget_data;
        }

    }

    public function validate_settings() {
        $widget_settings = array(
            'slider_id' => form_sanitizer($_POST['slider_id'], '', 'slider_id'),
            'slider_path' => form_sanitizer($_POST['slider_path'], '', 'slider_path'),
            'slider_height' => form_sanitizer($_POST['slider_height'], '', 'slider_height'),
            'slider_navigation' => form_sanitizer($_POST['slider_navigation'], 0, 'slider_navigation'),
            'slider_indicator' => form_sanitizer($_POST['slider_indicator'], 0, 'slider_indicator'),
            'slider_interval' => form_sanitizer($_POST['slider_interval'], 0, 'slider_interval')
        );
        if (defender::safe() && !empty($widget_settings)) {
            return \defender::serialize($widget_settings);
        }
    }

    public function validate_delete() {
    }

    /*
     * Slider Interface
     */
    public function display_form_input() {

        echo opentab(self::$slider_tab, self::$tab_active, 'slider_tabs', TRUE, 'm-t-20 nav-tabs', 'slider', ['widgetAction', 'widgetKey']);

        switch (self::$tab_active) {
            //default:
            case 'cur_slider':
                self::slider_content();
                break;
            case 'slider_settings':
                self::slider_options_form();
                break;
            default:
                //case 'slider_frm':
                self::slider_form();
        }

        echo closetab();
    }

    private function slider_content() {
        if (!empty(self::$colData['page_content'])) {

            self::$widget_data = \defender::unserialize(self::$colData['page_content']);

            if (!empty(self::$widget_data)) {
                ?>
                <table class="table table-responsive">
                    <thead>
                    <tr>
                        <th><?php echo self::$slider_locale['SLDW_0400'] ?></th>
                        <th><?php echo self::$slider_locale['SLDW_0401'] ?></th>
                        <th><?php echo self::$slider_locale['SLDW_0402'] ?></th>
                        <th><?php echo self::$slider_locale['SLDW_0403'] ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 0;
                    foreach (self::$widget_data as $slider) :
                        $edit_link = clean_request("slider=slider_frm&widgetAction=edit&widgetKey=$i",
                                                   array('widgetAction', 'widgetKey', 'slider'), FALSE);
                        $del_link = clean_request("slider=cur_slider&widgetAction=del&widgetKey=$i",
                                                  array('widgetAction', 'widgetKey', 'slider'), FALSE);
                        ?>
                        <tr>
                            <td><?php echo $slider['slider_title'] ?></td>
                            <td><?php echo $slider['slider_image_src'] ?></td>
                            <td><?php echo $slider['slider_order'] ?></td>
                            <td>
                                <a href="<?php echo $edit_link ?>">
                                    <?php echo self::$slider_locale['edit'] ?>
                                </a> - <a href="<?php echo $del_link ?>">
                                    <?php echo self::$slider_locale['delete'] ?>
                                </a>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    endforeach;
                    ?>
                    </tbody>
                </table>
                <?php

            } else {
                ?>
                <div class="text-center well"><?php echo self::$slider_locale['SLDW_0404'] ?></div>
                <?php
            }

        } else {
            ?>
            <div class="text-center well"><?php echo self::$slider_locale['SLDW_0404'] ?></div>
            <?php
        }
    }

    /*
     * Slider Settings
     */
    private function slider_options_form() {

        if (!empty(self::$colData['page_options'])) {
            echo "<div class='m-t-20'>\n";
            echo "<div class='well'>".self::$slider_locale['SLDW_0405']."</div>\n";
            echo "</div>\n";
            echo "<hr />\n";
        }

        // Folder options
        $image_options = array(
            0 => self::$slider_locale['SLDW_0535']
        );
        $options = makefilelist(IMAGES, '.|..|._DS_STORE', TRUE, 'folders', '.|..|._DS_STORE');
        if (!empty($options)) {
            foreach ($options as $folders) {
                $image_options[$folders] = "images/".$folders."/";
            }
        }

        echo form_text('slider_id', self::$slider_locale['SLDW_0500'], self::$slider_settings['slider_id'], array('inline' => TRUE)).
            form_select('slider_path', self::$slider_locale['SLDW_0534'], self::$slider_settings['slider_path'],
                        array('inline' => TRUE, 'options' => $image_options)).
            form_text('slider_height', self::$slider_locale['SLDW_0501'], self::$slider_settings['slider_height'],
                      array('inline' => TRUE, 'append' => TRUE, 'append_value' => 'px', 'type' => 'number', 'required' => TRUE, 'width' => '180px')).
            form_text('slider_interval', self::$slider_locale['SLDW_0603'], self::$slider_settings['slider_interval'],
                  array('inline' => TRUE, 'append' => TRUE, 'append_value' => 'ms', 'type' => 'number', 'required' => TRUE, 'width' => '180px'));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong><?php echo self::$slider_locale['SLDW_0502'] ?></strong>
                <br/><i><?php echo self::$slider_locale['SLDW_0304'] ?></i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $options = array(
                    0 => self::$slider_locale['SLDW_0503'],
                    1 => self::$slider_locale['SLDW_0504']
                );
                echo form_checkbox('slider_navigation', '', self::$slider_settings['slider_navigation'],
                                   array('type' => 'radio', 'options' => $options));
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-3"><?php echo self::$slider_locale['SLDW_0505'] ?><br/><i><?php echo self::$slider_locale['SLDW_0506'] ?></i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $options = array(
                    0 => self::$slider_locale['SLDW_0507'],
                    1 => self::$slider_locale['SLDW_0508']
                );
                echo form_checkbox('slider_indicator', '', self::$slider_settings['slider_indicator'],
                                   array('type' => 'radio', 'options' => $options));
                ?>
            </div>
        </div>
        <?php
    }

    /*
     * Slider Form
     */
    private function slider_form() {
        ?>
        <div class="row m-t-20">
            <div class="col-xs-12 col-sm-3">
                <strong><?php echo self::$slider_locale['SLDW_0510'] ?></strong><br/><i><?php echo self::$slider_locale['SLDW_0511'] ?></i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_fileinput('slider_image_src', '', self::$slider_content['slider_image_src'],
                                    array(
                                        'upload_path' => IMAGES.self::$slider_settings['slider_path']."/",
                                        'required'    => TRUE,
                                        'template'    => 'modern',
                                        'media'       => TRUE,
                                        'error_text'  => self::$slider_locale['SLDW_0512'],
                                        "max_width"   => 2000,
                                        "max_height"  => 1800,
                                        "max_byte"    => 150000000,
                                    )
                );
                ?>
            </div>
        </div>
        <?php
        echo form_text('slider_title', self::$slider_locale['SLDW_0513'], self::$slider_content['slider_title'], array('inline' => TRUE));
        echo form_textarea('slider_description', self::$slider_locale['SLDW_0514'], self::$slider_content['slider_description'], array('inline' => TRUE));
        echo form_text('slider_link', self::$slider_locale['SLDW_0515'], self::$slider_content['slider_link'], array('inline' => TRUE, 'type' => 'url'));
        echo form_text('slider_order', self::$slider_locale['SLDW_0516'], self::$slider_content['slider_order'],
                       array('inline' => TRUE, 'type' => 'number', 'width' => '100px'));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong><?php echo self::$slider_locale['SLDW_0517'] ?></strong><br/><i><?php echo self::$slider_locale['SLDW_0518'] ?></i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_text('slider_caption_offset', self::$slider_locale['SLDW_0519'], self::$slider_content['slider_caption_offset'],
                               array(
                                   'inline'       => TRUE,
                                   'type'         => 'number',
                                   'append'       => TRUE,
                                   'append_value' => 'px',
                                   'width'        => '100px',
                                   'ext_tip'      => self::$slider_locale['SLDW_0520'],
                                   'required'     => TRUE
                               ));
                $options = array(
                    'text-left'   => self::$slider_locale['SLDW_0521'],
                    'text-right'  => self::$slider_locale['SLDW_0522'],
                    'text-center' => self::$slider_locale['SLDW_0523']
                );
                echo form_select('slider_caption_align', self::$slider_locale['SLDW_0524'], self::$slider_content['slider_caption_offset'],
                                 array(
                                     'inline' => TRUE,
                                     'options' => $options
                                 )
                );
                echo form_text('slider_title_size', self::$slider_locale['SLDW_0525'], self::$slider_content['slider_title_size'],
                               array(
                                   'inline'       => TRUE,
                                   'type'         => 'number',
                                   'append'       => TRUE,
                                   'append_value' => 'px',
                                   'width'        => '100px',
                                   'ext_tip'      => self::$slider_locale['SLDW_0526'],
                                   'required'     => TRUE
                               )
                );
                echo form_text('slider_desc_size', self::$slider_locale['SLDW_0527'], self::$slider_content['slider_desc_size'],
                               array(
                                   'inline'       => TRUE,
                                   'type'         => 'number',
                                   'append'       => TRUE,
                                   'append_value' => 'px',
                                   'width'        => '100px',
                                   'ext_tip'      => self::$slider_locale['SLDW_0528'],
                                   'required'     => TRUE
                               )
                );
                $options = array(
                    0        => self::$slider_locale['SLDW_0529'],
                    'btn-sm' => self::$slider_locale['SLDW_0530'],
                    'btn-md' => self::$slider_locale['SLDW_0531'],
                    'btn-lg' => self::$slider_locale['SLDW_0532']
                );
                echo form_select('slider_btn_size', self::$slider_locale['SLDW_0533'], self::$slider_content['slider_btn_size'],
                                 array(
                                     'inline' => TRUE,
                                     'options' => $options
                                 )
                );
                ?>
            </div>
        </div>
        <?php
    }

    public function display_form_button() {

        switch (self::$tab_active) {
            case 'slider_settings':
                $input_value = 'settings';
                break;
            case 'slider_frm':
                $input_value = 'widget';
                break;
            default:
                $input_value = 'slider';
                break;
        }

        echo form_button('save_widget', self::$slider_locale['SLDW_0600'], $input_value, array('class' => 'btn-primary'));
        if (self::$new_slider === FALSE) {
            echo form_button('save_and_close_widget', self::$slider_locale['SLDW_0601'], $input_value, array('class' => 'btn-success'));
        }

    }
}