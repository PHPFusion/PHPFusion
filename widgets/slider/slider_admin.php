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

        if (isset($_POST['save_widget']) or isset($_POST['save_and_close_widget'])) {
            $data = array(
                'slider_title' => form_sanitizer($_POST['slider_title'], '', 'slider_title'),
                'slider_description' => form_sanitizer($_POST['slider_description'], '', 'slider_description'),
                'slider_link' => form_sanitizer($_POST['slider_link'], '', 'slider_link'),
                'slider_order' => form_sanitizer($_POST['slider_order'], 0, 'slider_order'),
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
                    $data['slider_image_src'] = form_sanitizer($_POST['slider_image_src-mediaSelector'],
                                                                            '',
                                                                            'slider_image_src-mediaSelector');
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
            // This is for new entries only
            if (isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit' && isset($_GET['key']) && isset($widget_data[$_GET['key']])) {
                $widget_data[$_GET['key']] = $data;
            } else {
                $new_widget_data[] = $data;
                $widget_data = array_merge_recursive($widget_data, $new_widget_data);
            }
            $widget_data = sorter($widget_data, 'slider_order');
            // Reindex the keys
            $widget_data = array_values($widget_data);
            $count = 1;
            foreach ($widget_data as $key => $arrayOrder) {
                $widget_data[$key]['slider_order'] = $count;
                $count++;
            }
            if (defender::safe() && !empty($widget_data)) {
                // sort according to slider order
                self::$widget_data = serialize($widget_data);
            }
        }
        return self::$widget_data;
    }


    public function display_input() {

        $tab_title['title'][] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? "Back" : "Current Slides");
        $tab_title['id'][] = "cur_slider";
        $tab_title['title'][] = ((isset($_GET['widgetAction']) && $_GET['widgetAction'] == 'edit') ? "Edit Slide" : "Add Slide");
        $tab_title['id'][] = "slider_frm";

        $_GET['slider'] = isset($_GET['slider']) && in_array($_GET['slider'],
                                                             $tab_title['id']) ? $_GET['slider'] : $tab_title['id'][0];

        echo opentab($tab_title, $_GET['slider'], 'slider_tab', TRUE, '', 'slider', array('widgetAction', 'widgetKey'));

        switch ($_GET['slider']) {
            case 'cur_slider':

                if (!empty(self::$colData['page_content'])) {

                    self::$widget_data = unserialize(self::$colData['page_content']);
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
                    // do the table
                } else {
                    ?>
                    <div class="text-center well">There are no slides defined</div>
                    <?php
                }
                break;
            default:
                self::slider_form();
                break;
        }
        echo closetab();
    }

    private function slider_form() {
        $curData = array(
            'slider_image_src' => '',
            'slider_title' => '',
            'slider_description' => '',
            'slider_link' => '',
            'slider_order' => '',
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

    }

    public function display_button() {
        if (isset($_GET['slider']) && $_GET['slider'] == 'slider_frm') {
            echo form_button('save_widget', 'Save Slider', 'save_widget', array('class' => 'btn-primary'));
            echo form_button('save_and_close_widget', 'Save and Close Widget', 'save_and_close_widget',
                             array('class' => 'btn-success'));
        }

    }
}