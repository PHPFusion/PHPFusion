<?php
// Slider Widget
// There is a form and there is a display
class carouselWidget extends \PHPFusion\Page\PageModel {

    private static $sliderData = array();

    private static $sliderOptions = array();

    /**
     * Display driver
     * @param $colData
     * @return string
     */
    public static function display_widget($colData) {
        if (!empty($colData['page_content'])) {

            self::$sliderData = unserialize($colData['page_content']);

            $default_slider_options = array(
                'slider_id' => '',
                'slider_height' => 300,
                'slider_navigation' => FALSE,
                'slider_indicator' => FALSE,
            );

            $slider_options = unserialize($colData['page_options']);

            $slider_options += $default_slider_options;

            if (empty($slider_options['slider_id'])) {
                $slider_options['slider_id'] = $colData['page_grid_id']."-".$colData['page_content_id']."-carousel";
            }

            self::$sliderOptions = $slider_options;

            return self::display_carousel();

        } else {
            return "No slides defined";
        }
    }

    /**
     * Copies bootstrap carousel
     */
    public static function display_carousel() {

        $slides_count = count(self::$sliderData);

        ob_start();
        ?>
        <div id="<?php echo self::$sliderOptions['slider_id'] ?>" class="carousel slide" data-ride="carousel">

            <?php if (self::$sliderOptions['slider_indicator'] == TRUE) : ?>
                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <?php for ($slider_counter = 0; $slider_counter < $slides_count; $slider_counter++) : ?>
                        <li data-target="#<?php echo self::$sliderOptions['slider_id'] ?>"
                            data-slide-to="<?php echo $slider_counter ?>" <?php echo($slider_counter == 0 ? 'class="active"' : '') ?>></li>
                    <?php endfor; ?>
                </ol>
            <?php endif; ?>

            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox" style="max-height: <?php echo self::$sliderOptions['slider_height'] ?>px">
                <?php
                for ($slider_counter = 0; $slider_counter < $slides_count; $slider_counter++) :
                    $slides = self::$sliderData[$slider_counter];
                    ?>
                    <div class="item <?php echo($slider_counter == 0 ? 'active' : '') ?>">
                        <img src="<?php echo IMAGES.$slides['slider_image_src'] ?>" alt="<?php echo $slides['slider_title'] ?>">

                        <div class="carousel-caption" style="display:block; top:0; padding-top:<?php echo $slides['slider_caption_offset'] ?>px;">
                            <?php echo(!empty($slides['slider_title']) ? "<h3 class='".$slides['slider_caption_align']."' style='font-size: ".$slides['slider_title_size']."px'>".$slides['slider_title']."</h3>" : '') ?>
                            <?php echo(!empty($slides['slider_description']) ? "<p class='".$slides['slider_caption_align']."'style='font-size: ".$slides['slider_desc_size']."px'>".$slides['slider_description']."</p>" : '') ?>
                            <?php echo(!empty($slides['slider_link']) ? "<div class='display-block ".$slides['slider_caption_align']."'>
                            <a href='".$slides['slider_link']."' class='btn btn-primary ".$slides['slider_btn_size']."'>
                            Read more..
                            </a></div>" : "") ?>
                        </div>
                    </div>
                <?php endfor; ?>

            </div>

            <?php if (self::$sliderOptions['slider_navigation'] == TRUE) : ?>
                <a class="left carousel-control" href="#<?php echo self::$sliderOptions['slider_id'] ?>" role="button" data-slide="prev">
                    <span class="icon-prev" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#<?php echo self::$sliderOptions['slider_id'] ?>" role="button" data-slide="next">
                    <span class="icon-next" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            <?php endif; ?>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return (string)$html;
    }


}
