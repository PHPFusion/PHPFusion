<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
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
class carouselWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $widget_data = array();
    private static $exclude_return = array('slider', 'sliderAction', 'widgetAction', 'widgetKey');

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
            'slider_height' => form_sanitizer($_POST['slider_height'], '', 'slider_height'),
            'slider_navigation' => form_sanitizer($_POST['slider_navigation'], 0, 'slider_navigation'),
            'slider_indicator' => form_sanitizer($_POST['slider_indicator'], 0, 'slider_indicator')
        );
        if (defender::safe() && !empty($widget_settings)) {
            return \defender::serialize($widget_settings);
        }
    }

    public function validate_delete() {
    }

    public function display_form_input() {

        $widget_locale = fusion_get_locale('', WIDGETS."slider/locale/".LANGUAGE.".php");

        if (isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'del' && isset($_GET['widgetKey']) && isnum($_GET['widgetKey'])) {
            if (!empty(self::$colData['page_content'])) {
                self::$widget_data = \defender::unserialize(self::$colData['page_content']);
                if (isset(self::$widget_data[$_GET['widgetKey']])) {
                    unset(self::$widget_data[$_GET['widgetKey']]);
                    $new_array = array_values(self::$widget_data);
                    self::$colData['page_content'] = serialize($new_array);
                    dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'update');
                    addNotice('success', $widget_locale['0200']);
                }
            }
            redirect(clean_request('', array('widgetKey', 'widgetAction'), FALSE));
        }

        $tab_title['title'][] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? $widget_locale['back'] : $widget_locale['0300']);
        $tab_title['id'][] = "cur_slider";
        $tab_title['title'][] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? $widget_locale['0301'] : $widget_locale['0302']);
        $tab_title['id'][] = "slider_frm";
        $tab_title['title'][] = $widget_locale['0303'];
        $tab_title['id'][] = "slider_settings";

        $_GET['slider'] = isset($_GET['slider']) && in_array($_GET['slider'], $tab_title['id']) ? $_GET['slider'] : $tab_title['id'][0];

        echo opentab($tab_title, $_GET['slider'], 'slider_tab', TRUE, '', 'slider', array('widgetAction', 'widgetKey'));

        switch ($_GET['slider']) {
            case 'cur_slider':

                if (!empty(self::$colData['page_content'])) {

                    self::$widget_data = \defender::unserialize(self::$colData['page_content']);

                    if (!empty(self::$widget_data)) {

                        ?>
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th><?php echo $widget_locale['0400'] ?></th>
                                <th><?php echo $widget_locale['0401'] ?></th>
                                <th><?php echo $widget_locale['0402'] ?></th>
                                <th><?php echo $widget_locale['0403'] ?></th>
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
                                            <?php echo $widget_locale['edit'] ?>
                                        </a> - <a href="<?php echo $del_link ?>">
                                            <?php echo $widget_locale['delete'] ?>
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
                        <div class="text-center well"><?php echo $widget_locale['0404'] ?></div>
                        <?php
                    }

                } else {
                    ?>
                    <div class="text-center well"><?php echo $widget_locale['0404'] ?></div>
                    <?php
                }
                break;
            case 'slider_settings':
                self::slider_options_form();
                break;
            default:
                self::slider_form();
                break;
        }
        echo closetab();
    }

    private function slider_options_form() {

        $widget_locale = fusion_get_locale('', WIDGETS."slider/locale/".LANGUAGE.".php");

        $curData = array(
            'slider_id' => "",
            'slider_height' => '300',
            'slider_navigation' => TRUE,
            'slider_indicator' => TRUE,
        );

        if (!empty(self::$colData['page_options'])) {
            $curData = \defender::unserialize(self::$colData['page_options']);
        }

        echo "<div class='well'>".$widget_locale['0405']."</div>";

        echo form_text('slider_id', $widget_locale['0500'], $curData['slider_id'], array('inline' => TRUE)).
            form_text('slider_height', $widget_locale['0501'], $curData['slider_height'],
                      array('inline' => TRUE, 'append' => TRUE, 'append_value' => 'px', 'type' => 'number', 'required' => TRUE, 'width' => '180px'));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3"><strong><?php echo $widget_locale['0502'] ?></strong><br/><i>Slider Controls Settings</i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $options = array(
                    0 => $widget_locale['0503'],
                    1 => $widget_locale['0504']
                );
                echo form_checkbox('slider_navigation', '', $curData['slider_navigation'], array('type' => 'radio', 'options' => $options));
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-3"><?php echo $widget_locale['0505'] ?><br/><i><?php echo $widget_locale['0506'] ?></i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $options = array(
                    0 => $widget_locale['0507'],
                    1 => $widget_locale['0508']
                );
                echo form_checkbox('slider_indicator', '', $curData['slider_indicator'], array('type' => 'radio', 'options' => $options));
                ?>
            </div>
        </div>
        <?php
    }

    private function slider_form() {

        $widget_locale = fusion_get_locale('', WIDGETS."slider/locale/".LANGUAGE.".php");

        $curData = array(
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
        if (!empty(self::$colData['page_content']) && isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit' && isset($_GET['widgetKey'])) {
            self::$widget_data = \defender::unserialize(self::$colData['page_content']);
            if (isset(self::$widget_data[$_GET['widgetKey']])) {
                $curData = self::$widget_data[$_GET['widgetKey']];
            } else {
                redirect(clean_request('slider=cur_slider', array('widgetAction', 'widgetKey'), FALSE));
            }
        }
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong><?php echo $widget_locale['0510'] ?></strong><br/><i><?php echo $widget_locale['0511'] ?></i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_fileinput('slider_image_src', '', $curData['slider_image_src'],
                                    array(
                                        'upload_path' => IMAGES,
                                        'required' => TRUE,
                                        'template' => 'modern',
                                        'media' => TRUE,
                                        'error_text' => $widget_locale['0512']
                                    )
                );
                ?>
            </div>
        </div>
        <?php
        echo form_text('slider_title', $widget_locale['0513'], $curData['slider_title'], array('inline' => TRUE));
        echo form_text('slider_description', $widget_locale['0514'], $curData['slider_description'], array('inline' => TRUE));
        echo form_text('slider_link', $widget_locale['0515'], $curData['slider_link'], array('inline' => TRUE, 'type' => 'url'));
        echo form_text('slider_order', $widget_locale['0516'], $curData['slider_order'],
                       array('inline' => TRUE, 'type' => 'number', 'width' => '100px'));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3"><strong><?php echo $widget_locale['0517'] ?></strong><br/><i><?php echo $widget_locale['0518'] ?></i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_text('slider_caption_offset', $widget_locale['0519'], $curData['slider_caption_offset'],
                               array(
                                   'inline' => TRUE,
                                   'type' => 'number',
                                   'append' => TRUE,
                                   'append_value' => 'px',
                                   'width' => '100px',
                                   'ext_tip' => $widget_locale['0520'],
                                   'required' => TRUE
                ));
                $options = array(
                    'text-left' => $widget_locale['0521'],
                    'text-right' => $widget_locale['0522'],
                    'text-center' => $widget_locale['0523']
                );
                echo form_select('slider_caption_align', $widget_locale['0524'], $curData['slider_caption_offset'],
                                 array(
                                     'inline' => TRUE,
                                     'options' => $options
                                 )
                );
                echo form_text('slider_title_size', $widget_locale['0525'], $curData['slider_title_size'],
                               array(
                                   'inline' => TRUE,
                                   'type' => 'number',
                                   'append' => TRUE,
                                   'append_value' => 'px',
                                   'width' => '100px',
                                   'ext_tip' => $widget_locale['0526'],
                                   'required' => TRUE
                               )
                );
                echo form_text('slider_desc_size', $widget_locale['0527'], $curData['slider_desc_size'],
                               array(
                                   'inline' => TRUE,
                                   'type' => 'number',
                                   'append' => TRUE,
                                   'append_value' => 'px',
                                   'width' => '100px',
                                   'ext_tip' => $widget_locale['0528'],
                                   'required' => TRUE
                               )
                );
                $options = array(
                    0 => $widget_locale['0529'],
                    'btn-sm' => $widget_locale['0530'],
                    'btn-md' => $widget_locale['0531'],
                    'btn-lg' => $widget_locale['0532']
                );
                echo form_select('slider_btn_size', $widget_locale['0533'], $curData['slider_btn_size'],
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
        $widget_locale = fusion_get_locale('', WIDGETS."slider/locale/".LANGUAGE.".php");
        if (isset($_GET['slider']) && ($_GET['slider'] == 'slider_frm' || $_GET['slider'] == 'slider_settings')) {
            $input_value = ($_GET['slider'] == 'slider_settings' ? 'settings' : 'widget');
            echo form_button('save_widget', $widget_locale['0600'], $input_value, array('class' => 'btn-primary'));
            echo form_button('save_and_close_widget', $widget_locale['0601'], $input_value, array('class' => 'btn-success'));
        }
    }
}