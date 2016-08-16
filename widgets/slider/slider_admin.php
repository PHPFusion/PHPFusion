<?php

/**
 * Class carouselWidgetAdmin
 */
class carouselWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine {

    private static $widget_data = array();

    public function exclude_return() {
        return array('slider', 'sliderAction', 'widgetAction', 'widgetKey');
    }

    public function validate_input() {

        $widget_data = array();

        if (!empty(self::$colData['page_content'])) {
            $widget_data = unserialize(self::$colData['page_content']);
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

        if (defender::safe()) {
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
        if (isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit' && isset($_GET['key']) && isset($widget_data[$_GET['key']])) {
            $widget_data[$_GET['key']] = $data;
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

        if (defender::safe() && !empty($widget_data)) {
            self::$widget_data = serialize($widget_data);
        }

        return self::$widget_data;
    }

    public function validate_settings() {

        $widget_settings = array(
            'slider_id' => form_sanitizer($_POST['slider_id'], '', 'slider_id'),
            'slider_height' => form_sanitizer($_POST['slider_height'], '', 'slider_height'),
            'slider_navigation' => form_sanitizer($_POST['slider_navigation'], 0, 'slider_navigation'),
            'slider_indicator' => form_sanitizer($_POST['slider_indicator'], 0, 'slider_indicator')
        );

        return serialize($widget_settings);
    }

    public function display_input() {

        if (isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'del' && isset($_GET['widgetKey']) && isnum($_GET['widgetKey'])) {
            if (!empty(self::$colData['page_content'])) {
                self::$widget_data = unserialize(self::$colData['page_content']);
                if (isset(self::$widget_data[$_GET['widgetKey']])) {
                    unset(self::$widget_data[$_GET['widgetKey']]);
                    $new_array = array_values(self::$widget_data);
                    self::$colData['page_content'] = serialize($new_array);
                    dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'update');
                    addNotice('success', "Slider Updated");
                }
            }
            redirect(clean_request('', array('widgetKey', 'widgetAction'), FALSE));
        }

        $tab_title['title'][] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? "Back" : "Current Slides");
        $tab_title['id'][] = "cur_slider";
        $tab_title['title'][] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? "Edit Slide" : "Add Slide");
        $tab_title['id'][] = "slider_frm";
        $tab_title['title'][] = "Slider Settings";
        $tab_title['id'][] = "slider_settings";

        $_GET['slider'] = isset($_GET['slider']) && in_array($_GET['slider'], $tab_title['id']) ? $_GET['slider'] : $tab_title['id'][0];

        echo opentab($tab_title, $_GET['slider'], 'slider_tab', TRUE, '', 'slider', array('widgetAction', 'widgetKey'));

        switch ($_GET['slider']) {
            case 'cur_slider':

                if (!empty(self::$colData['page_content'])) {

                    self::$widget_data = unserialize(self::$colData['page_content']);

                    if (!empty(self::$widget_data)) {

                        ?>
                        <table class="table table-responsive">
                            <thead>
                            <tr>
                                <th>Slider Title</th>
                                <th>Slider Image</th>
                                <th>Order</th>
                                <th>Actions</th>
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
                                        <a href="<?php echo $edit_link ?>">Edit</a> - <a href="<?php echo $del_link ?>">Delete</a>
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
                        <div class="text-center well">There are no slides defined</div>
                        <?php
                    }

                } else {
                    ?>
                    <div class="text-center well">There are no slides defined</div>
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
        $curData = array(
            'slider_id' => "",
            'slider_height' => '300',
            'slider_navigation' => TRUE,
            'slider_indicator' => TRUE,
        );

        if (!empty(self::$colData['page_options'])) {
            $curData = unserialize(self::$colData['page_options']);
        }

        echo "<div class='well'>Configure the properties of the slider</div>";
        echo form_text('slider_id', 'Slider HTML ID', $curData['slider_id'], array('inline' => TRUE)).
            form_text('slider_height', 'Slider Height', $curData['slider_height'],
                      array('inline' => TRUE, 'append' => TRUE, 'append_value' => 'px', 'type' => 'number', 'required' => TRUE, 'width' => '180px'));
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3"><strong>Slider Navigation</strong><br/><i>Slider Controls Settings</i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $options = array(
                    0 => 'Do not display Navigation Control',
                    1 => 'Show display Navigation Control'
                );
                echo form_checkbox('slider_navigation', '', $curData['slider_navigation'], array('type' => 'radio', 'options' => $options));
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-3">Slider Indicator<br/><i>Slider Indicator Settings</i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $options = array(
                    0 => 'Do not display Slider Indicator',
                    1 => 'Display Slider Indicator'
                );
                echo form_checkbox('slider_indicator', '', $curData['slider_indicator'], array('type' => 'radio', 'options' => $options));
                ?>
            </div>
        </div>
        <?php
    }

    private function slider_form() {
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
            self::$widget_data = unserialize(self::$colData['page_content']);
            if (isset(self::$widget_data[$_GET['widgetKey']])) {
                $curData = self::$widget_data[$_GET['widgetKey']];
            } else {
                redirect(clean_request('slider=cur_slider', array('widgetAction', 'widgetKey'), FALSE));
            }
        }
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong>Background Image</strong><br/><i>Choose or upload new background</i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                $sliderPath = IMAGES;
                echo form_fileinput('slider_image_src', '', $curData['slider_image_src'], array(
                    'upload_path' => $sliderPath,
                    'required' => TRUE,
                    'template' => 'modern',
                    'media' => TRUE,
                    'error_text' => 'Please select a valid background'
                ));
                ?>
            </div>
        </div>
        <?php
        echo form_text('slider_title', 'Slider Heading Title', $curData['slider_title'], array('inline' => TRUE));
        echo form_text('slider_description', 'Slider Description', $curData['slider_description'],
                       array('inline' => TRUE));
        echo form_text('slider_link', 'Link URL', $curData['slider_link'], array('inline' => TRUE, 'type' => 'url'));
        echo form_text('slider_order', 'Order', $curData['slider_order'],
                       array('inline' => TRUE, 'type' => 'number', 'width' => '100px'));

        // more options
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3"><strong>Slide Attributes</strong><br/><i>Configure appearance for this slide</i></div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_text('slider_caption_offset', 'Caption Top Offset', $curData['slider_caption_offset'], array(
                    'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px', 'width' => '100px',
                    'ext_tip' => 'Offset position of captions from top border of the slide', 'required' => TRUE
                ));
                $options = array(
                    'text-left' => "Left",
                    'text-right' => "Right",
                    'text-center' => "Center"
                );
                echo form_select('slider_caption_align', 'Caption Text Alignment', $curData['slider_caption_offset'],
                                 array('inline' => TRUE, 'options' => $options));
                echo form_text('slider_title_size', 'Caption Title Size', $curData['slider_title_size'], array(
                    'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px', 'width' => '100px',
                    'ext_tip' => 'Size of title in px. Negative number not allowed.', 'required' => TRUE
                ));
                echo form_text('slider_desc_size', 'Caption Description Size', $curData['slider_desc_size'], array(
                    'inline' => TRUE, 'type' => 'number', 'append' => TRUE, 'append_value' => 'px', 'width' => '100px',
                    'ext_tip' => 'Size of description in px. Negative number not allowed.', 'required' => TRUE
                ));
                $options = array(
                    0 => 'Normal Size',
                    'btn-sm' => 'Small Size',
                    'btn-md' => 'Medium Size',
                    'btn-lg' => 'Large Size'
                );
                echo form_select('slider_btn_size', 'Link Button Size', $curData['slider_btn_size'], array('inline' => TRUE, 'options' => $options));
                ?>
            </div>
        </div>
        <?php
    }

    public function display_form_button() {
        if (isset($_GET['slider']) && ($_GET['slider'] == 'slider_frm' || $_GET['slider'] == 'slider_settings')) {
            echo form_button('save_widget', 'Save Slider', ($_GET['slider'] == 'slider_settings' ? 'settings' : 'widget'),
                             array('class' => 'btn-primary'));
            echo form_button('save_and_close_widget', 'Save and Close Widget', ($_GET['slider'] == 'slider_settings' ? 'settings' : 'widget'),
                             array('class' => 'btn-success'));
        }
    }


}