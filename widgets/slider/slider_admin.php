<?php
// The admin file for widget
// The image path uses the image as base path
// No form tag

class carouselWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine {

    private static $widget_data = array();

    public function validate_input() {

        if (isset($_POST['save_widget'])) {
            // save your shit
            self::$widget_data = array(
                'slider_title' => form_sanitizer($_POST['slider_title'], '', 'slider_title'),
                'slider_description' => form_sanitizer($_POST['slider_description'], '', 'slider_description'),
                'slider_link' => form_sanitizer($_POST['slider_link'], '', 'slider_link'),
            );
            if (defender::safe()) {
                if (!empty($_FILES['slider_image_src']['tmp_name'])) {
                    $upload = form_sanitizer($_FILES['slider_image_src'], '', 'slider_image_src');
                    if (empty($upload['error'])) {
                        self::$widget_data['slider_image_src'] = $upload['image_name'];
                    }
                } else {
                    self::$widget_data['slider_image_src'] = form_sanitizer($_POST['slider_image_src-mediaSelector'],
                                                                            '',
                                                                            'slider_image_src-mediaSelector');
                }
            }
            if (defender::safe()) {
                // execute save into the content column.
                // unserialize it.
                self::$widget_data = serialize(self::$widget_data);
            }
        }

        return self::$widget_data;
    }


    public function display_input() {
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
    }

}