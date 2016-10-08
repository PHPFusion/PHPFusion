<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: includes/septenary.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer:
| Craig (http://www.phpfusionmods.co.uk),
| Chan (Lead developer of PHP-Fusion)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

class SeptenaryTheme extends SeptenaryComponents {

    /**
     * Object Theme Factory
     * @return $this
     */
    public static $septenary_instance = NULL;
    protected static $locale = array();
    private $left_html = "";
    private $top_html = "";
    private $upper_html = "";
    private $lower_html = "";
    private $bottom_html = "";

    /**
     * Make Instance
     * @return null|static
     */
    public static function Factory() {
        if (self::$septenary_instance === NULL) {
            self::$septenary_instance = new static();
            self::$septenary_instance->set_locale();
        }

        return self::$septenary_instance;
    }

    /**
     * Render the theme layout
     */
    public function render_page() {
        $this->displayHeader();
        // Header - something fancy for login page.
        if (FUSION_SELF !== 'login.php') {
            $this->displayContent();
        }
        $this->displayFooter();
    }

    /**
     * Render the theme content
     */
    public function displayContent() {

        // Septenary Theme Functions
        add_handler("theme_output");
        $this->setHeader();

        $this->open_grid('section-3', 1);
        echo (!empty(AU_CENTER) || $this->top_html) ? "<div class='au-content'>".$this->top_html.AU_CENTER."</div>\n" : '';
        echo "<div class='row'>\n";
        if (!empty(LEFT) || !empty(RIGHT) || !empty($this->left_html)) {
            echo "<div class='hidden-xs col-sm-3 col-md-3 col-lg-3 leftbar'>\n";
            echo RIGHT.LEFT.$this->left_html;
            echo "</div>\n";
        }
        echo "<div class='".self::col_span()." main-content'>\n";
        // Get all notices, we also include notices that are meant to be displayed on all pages
        echo renderNotices(getNotices(array('all', FUSION_SELF)));
        echo $this->upper_html.U_CENTER;
        echo CONTENT;
        echo $this->lower_html.L_CENTER;
        echo "</div>\n";
        echo (!empty(BL_CENTER) || $this->bottom_html) ? "<div class='bl-content'>".$this->bottom_html.BL_CENTER."</div>\n" : '';
        echo "</div>\n";
        $this->close_grid(1);
    }

    /**
     * Adds Theme Javascript and Meta header
     */
    private function setHeader() {

        if (FUSION_SELF !== "maintenance.php" && FUSION_SELF !== "go.php") {

            add_to_head("<script src='".THEME."includes/search.js'></script>");
            add_to_head("<meta name='viewport' content='width=device-width, initial-scale=1'>
			<!--[if lt IE 8]>
			<div style=' clear: both; text-align:center; position: relative;'>
			<a href='http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode'>
			<img src='http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg' border='0' height='42' width='820' alt='You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today.' />
			</a>
			</div>
			<![endif]-->
			<!--[if lt IE 9]>
			<script src='".THEME."js/html5.js'></script>
			<script src='".THEME."js/css3-mediaqueries.js'></script>
			<![endif]-->
			");
        }
    }

    /**
     * Injection of left bar html
     * @param $html
     */
    public function set_left_html($html) {
        $this->left_html .= $html;
    }

    /**
     * Injection of AU_CENTER
     * @param $html
     */
    public function set_top_html($html) {
        $this->left_html .= $html;
    }

    /**
     * Injection of U_CENTER
     * @param $html
     */
    public function set_upper_html($html) {
        $this->upper_html .= $html;
    }

    /**
     * Injection of L_CENTER
     * @param $html
     */
    public function set_lower_html($html) {
        $this->lower_html .= $html;
    }

    /**
     * Injection of BL_CENTER
     * @param $html
     */
    public function set_bottom_html($html) {
        $this->bottom_html .= $html;
    }
}