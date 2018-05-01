<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Slider/slider.php
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
 * Class carouselWidget
 * Display driver of carousel widget
 */
class carouselWidget extends \PHPFusion\Page\PageModel implements \PHPFusion\Page\WidgetInterface {

    private static $sliderData = [];
    private static $sliderOptions = [];

    public function display_widget($colData) {
        if (!empty($colData['page_content'])) {

            self::$sliderData = \defender::unserialize($colData['page_content']);

            $default_slider_options = [
                'slider_id'         => '',
                'slider_height'     => 300,
                'slider_navigation' => FALSE,
                'slider_indicator'  => FALSE,
                'slider_interval'   => 0,
            ];

            $slider_options = \defender::unserialize($colData['page_options']);
            if (!empty($slider_options)) {
                $slider_options += $default_slider_options;
            } else {
                $slider_options = $default_slider_options;
            }

            if (empty($slider_options['slider_id'])) {
                $slider_options['slider_id'] = $colData['page_grid_id']."-".$colData['page_content_id']."-carousel";
            }

            self::$sliderOptions = $slider_options;

            return self::display_carousel();

        } else {

            return fusion_get_locale('SLDW_0404', WIDGETS."slider/locale".LANGUAGE.".php");
        }
    }

    /**
     * Copies bootstrap 3 carousel HTML markup with PHP Implementations
     */
    public static function display_carousel() {
        $slides_count = count(self::$sliderData);

        ob_start();
        ?>
        <div id="<?php echo self::$sliderOptions['slider_id'] ?>" class="carousel slide carousel-fade"
             data-ride="carousel" data-interval="false">
            <?php if (self::$sliderOptions['slider_indicator'] == TRUE) : ?>
                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <?php for ($slider_counter = 0; $slider_counter < $slides_count; $slider_counter++) : ?>
                        <li data-target="#<?php echo self::$sliderOptions['slider_id'] ?>"
                            data-slide-to="<?php echo $slider_counter ?>" <?php echo($slider_counter == 0 ? 'class="active"' : '') ?>></li>
                    <?php endfor; ?>
                </ol>
                <!-- //Indicators -->
            <?php endif; ?>
            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox"
                 style="height: <?php echo self::$sliderOptions['slider_height'] ?>px; max-height: <?php echo self::$sliderOptions['slider_height'] ?>px">
                <?php
                for ($slider_counter = 0; $slider_counter < $slides_count; $slider_counter++) :
                    $slides = self::$sliderData[$slider_counter];
                    ?>
                    <div class="item <?php echo($slider_counter == 0 ? 'active' : '') ?>">
                        <img
                                src="<?php echo IMAGES.(!empty(self::$sliderOptions['slider_path']) ? self::$sliderOptions['slider_path']."/" : '').$slides['slider_image_src'] ?>"
                                alt="<?php echo $slides['slider_title'] ?>">
                        <div class="carousel-caption"
                             style="display:block; top:0; padding-top:<?php echo $slides['slider_caption_offset'] ?>px;">
                            <?php echo(!empty($slides['slider_title']) ? "<h3 class='".$slides['slider_caption_align']."'  style='font-size: ".$slides['slider_title_size']."px'>".$slides['slider_title']."</h3>" : '') ?>
                            <?php echo(!empty($slides['slider_description']) ? "<p class='".$slides['slider_caption_align']."' style='font-size: ".$slides['slider_desc_size']."px'>".self::get_sliderDescription($slides['slider_description'])."</p>" : '') ?>
                            <?php echo(!empty($slides['slider_link']) ? "<div class='display-block ".$slides['slider_caption_align']."'>
                            <a href='".$slides['slider_link']."' class='btn btn-primary ".$slides['slider_btn_size']."'>".fusion_get_locale('SLDW_0602', WIDGETS."slider/locale".LANGUAGE.".php")."</a></div>" : "") ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            <!-- //Wrapper for slides -->
            <?php if (self::$sliderOptions['slider_navigation'] == TRUE) : ?>
                <!-- Navigation for slides -->
                <a class="left carousel-control" href="#<?php echo self::$sliderOptions['slider_id'] ?>" role="button"
                   data-slide="prev">
                    <span class="icon-prev" aria-hidden="true"></span>
                    <span class="sr-only"><?php echo fusion_get_locale('previous') ?></span>
                </a>
                <a class="right carousel-control" href="#<?php echo self::$sliderOptions['slider_id'] ?>" role="button"
                   data-slide="next">
                    <span class="icon-next" aria-hidden="true"></span>
                    <span class="sr-only"><?php echo fusion_get_locale('next') ?></span>
                </a>
                <!-- //Navigation for slides -->
            <?php endif; ?>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        if (self::$sliderOptions['slider_interval']) {
            add_to_jquery("$('#".self::$sliderOptions['slider_id']."').carousel({ interval: ".self::$sliderOptions['slider_interval']." });");
        }

        return (string)$html;
    }

    /**
     * Fetches Description
     * @param $description - Running script inside a carousel - {eval} and {/eval} tag.
     * @return string
     */
    private static function get_sliderDescription($description) {
        if (fusion_get_settings('allow_php_exe') && stristr(html_entity_decode($description), '{eval}')) {
            $description = stripslashes(html_entity_decode(str_replace(['{eval}', '{/eval}'], ['', ''], $description)));
            $html = "<div class='carousel_code overflow-hide'>\n";
            $html .= eval($description);
            $html .= "</div>\n";

            return (string)$html;
        }

        return (string)nl2br(parse_textarea($description));
    }

}
