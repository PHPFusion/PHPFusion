<?php
// Slider Widget
// There is a form and there is a display
class carouselWidget extends \PHPFusion\Page\PageModel {

    private static $sliderData = array();

    /**
     * Display driver
     * @param $colData
     * @return string
     */
    public static function display_widget($colData) {
        if (!empty($colData['page_content'])) {
            self::$sliderData = unserialize($colData['page_content']);

            return self::display_carousel();
        } else {
            return "No slides defined";
        }
    }

    /**
     * Copies bootstrap carousel
     */
    public static function display_carousel() {

        // To provide a settings for a single instance of carousel

        // These need to go options -
        $slider_id = self::$colData['page_grid_id']."-".self::$colData['page_content_id']."-carousel";
        $carousel_height = 300;
        $navigation = TRUE;
        $indicator = TRUE;

        // These need to go extra parameter forms on the sliders
        $caption_offset = 100;
        $caption_align = 'left';
        switch ($caption_align) {
            case 'left':
                $caption_class = "text-left";
                break;
            case 'right':
                $caption_class = "text-right";
                break;
            case 'center':
                $caption_class = "text-center";
                break;
        }
        $caption_title_size = 30;
        $caption_description_size = 15;


        $slides_count = count(self::$sliderData);
        ob_start();
        ?>
        <div id="<?php echo $slider_id ?>" class="carousel slide" data-ride="carousel">
            <?php if ($indicator) : ?>
                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <?php for ($slider_counter = 0; $slider_counter < $slides_count; $slider_counter++) : ?>
                        <li data-target="#<?php echo $slider_id ?>"
                            data-slide-to="<?php echo $slider_counter ?>" <?php echo($slider_counter == 0 ? 'class="active"' : '') ?>></li>
                    <?php endfor; ?>
                </ol>
            <?php endif; ?>
            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox" style="max-height: <?php echo $carousel_height ?>px">
                <?php for ($slider_counter = 0; $slider_counter < $slides_count; $slider_counter++) : ?>

                    <?php
                    $slides = self::$sliderData[$slider_counter];
                    ?>
                    <div class="item <?php echo($slider_counter == 0 ? 'active' : '') ?>">
                        <img src="<?php echo IMAGES.$slides['slider_image_src'] ?>" alt="<?php echo $slides['slider_title'] ?>">

                        <div class="carousel-caption" style="display:block; top:0; padding-top:<?php echo $caption_offset ?>px;">
                            <?php echo($slides['slider_title'] ? "<h3 class='$caption_class' style='font-size: ".$caption_title_size."px'>".$slides['slider_title']."</h3>" : '') ?>
                            <?php echo($slides['slider_description'] ? "<p class='$caption_class' style='font-size: ".$caption_description_size."px'>".$slides['slider_description']."</p>" : '') ?>
                        </div>
                    </div>
                <?php endfor; ?>

            </div>

            <?php if ($navigation == TRUE) : ?>
                <a class="left carousel-control" href="#<?php echo $slider_id ?>" role="button" data-slide="prev">
                    <span class="icon-prev" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#<?php echo $slider_id ?>" role="button" data-slide="next">
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
