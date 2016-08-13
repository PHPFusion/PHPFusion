<?php
// The admin file for widget
// The image path uses the image as base path
// No form tag

class carouselWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine {


    public function validate_input() {
        // This whole chunk goes into single column

        if (isset($_POST['save_widget'])) {
            // save your shit
            $data = array(
                'slider_title' => form_sanitizer($_POST['slider_title'], '', 'slider_title'),
                'slider_description' => form_sanitizer($_POST['slider_description'], '', 'slider_description'),
                'slider_link' => form_sanitizer($_POST['slider_link'], '', 'slider_link'),
                'slider_order' => form_sanitizer($_POST['slider_order'], 0, 'slider_order'),
            );
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

            $widget_data[] = $data;

            if (defender::safe()) {
                self::$widget_data = serialize($widget_data);
            }
        }

        return self::$widget_data;
    }


    public function display_input() {
        //print_p(self::$colData);
        // configure
        // when edit it will appear
        $tab_title['title'][] = "Current Slides";
        $tab_title['id'][] = "cur_slider";
        $tab_title['title'][] = "Add Slide";
        $tab_title['id'][] = "add_slider";

        $_GET['slider'] = isset($_GET['slider']) && in_array($_GET['slider'],
                                                             $tab_title['id']) ? $_GET['slider'] : $tab_title['id'][0];

        echo opentab($tab_title, $_GET['slider'], 'slider_tab', TRUE, '', 'slider');
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
                        <?php foreach (self::$widget_data as $slider) : ?>
                            <tr>
                                <td><?php echo $slider['slider_title'] ?></td>
                                <td><?php echo $slider['slider_image'] ?></td>
                                <td><?php echo $slider['slider_order'] ?></td>
                                <td>
                                    <a href="">Edit</a>
                                    <a href="">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-3">
                <strong>Background Image</strong><br/><i>Choose or upload new background</i>
            </div>
            <div class="col-xs-12 col-sm-9">
                <?php
                echo form_fileinput('slider_image_src', '', '', array(
                    'required' => TRUE,
                    'template' => 'modern',
                    'media' => TRUE,
                    'error_text' => 'Please select a valid background'
                ));
                ?>
            </div>
        </div>
        <?php
        echo form_text('slider_title', 'Slider Heading Title', '', array('inline' => TRUE));
        echo form_text('slider_description', 'Slider Description', '', array('inline' => TRUE));
        echo form_text('slider_link', 'Link URL', '', array('inline' => TRUE, 'type' => 'url'));
        echo form_button('save_widget', 'Save Slider', 'save_widget', array('class' => 'btn-primary'));
    }

    public function display_button() {
        echo form_button('save_and_close_widget', 'Close Widget', 'save_and_close_widget',
                         array('class' => 'btn-primary'));
    }

}