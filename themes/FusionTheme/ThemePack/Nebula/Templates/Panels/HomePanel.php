<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: HomePanel.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace ThemePack\Nebula\Templates\Panels;

/**
 * Class HomePanel
 * @package Nebula\Templates\Panels
 */

class HomePanel extends \HomePanel {

    private static $headerData = array();
    private static $list_limit = 4;
    private static $content = array();

    public static function display_page($info) {
        self::$headerData = array(
            'popular' => self::$popular_content,
            'latest' => self::$latest_content,
            'featured' => self::$featured_content,
        );
		echo "<div class='row'>\n";
		$keys = array_keys(self::$headerData);
		foreach ($keys as $key) :
			echo "<div class='col-xs-12 col-sm-4 text-left'>\n";
			echo self::display_header($key);
			echo "</div>\n";
		endforeach;
		echo "</div>\n";

        if (!empty($info)) :
            echo "<div class='row'>\n";
			foreach ($info as self::$content) :
				//echo floor(12 / count($info))
				echo "<div class='col-xs-12 col-sm-6 col-md-4'>\n";
				echo self::display_content();
				echo "</div>\n";
			endforeach;
            echo "</div>\n";
        endif;
    }

    /**
     * @param $mode - latest, popular, featured
     */
    private static function display_header($mode) {
        $label = array(
            'latest' => self::$locale['home_0004'],
            'popular' => self::$locale['home_0005'],
            'featured' => self::$locale['home_0006'],
        );
        $data = self::$headerData[$mode][0];
		echo "<div class='panel panel-home'>\n";
		echo "<figure>";
		if (!empty($data['image'])) :
			echo "<img class='center-xy' src='".$data['image']."' alt='".$data['title']."'>";
		endif;
		echo "</figure>\n";
		echo "<div class='panel-body'>\n";
		echo "<h4>".$label[$mode]."</h4>\n";
		echo "<p><a href='".$data['url']."' title='".$data['title']."'>".trim_text($data['title'], 70)."</a></p>\n";
		echo "<p>".trim_text($data['content'], 500)."</p>\n";
		echo $data['meta'];
		echo "</div>\n";
		echo "<div class='panel-footer'><a href='".$data['url']."'>".self::$locale['home_0108']." <span class='fa fa-caret-right pull-right'></span></a></div>\n";
		echo "</div>\n";
    }

    private static function display_content() {
        echo "<div class='panel panel-home'>\n";
        echo "<div class='panel-heading'>".self::$content['blockTitle']."</div>\n";
		self::$list_limit = 4;
		if (!empty(self::$content['data'])) :
			echo "<ul class='panel-body'>\n";
			foreach (self::$content['data'] as $data) :
				echo "<li>";
				if ($data['image']) :
                    echo "<figure><a href='".$data['url']."' title='".$data['title']."'><img class='center-xy' src='".$data['image']."' alt='".$data['title']."'></a></figure>\n";
				endif;
                echo "<div class='list-body'>";
				echo "<a href='".$data['url']."' title='".$data['title']."'><div>".trim_text($data['content'], 50)."</div></a>";
                echo "</div>";
                echo "</li>\n";
				self::$list_limit--;
				if (self::$list_limit === 0) {
					break;
				}
			endforeach;
                echo "</ul>\n";
            else:
                echo "<div class='panel-body'>".self::$content['norecord']."</div>\n";
		endif;
		echo "<div class='panel-footer'>";
		echo "<a href='".(empty(self::$content['data']) ? "": $data['url'])."'>".self::$locale['home_0108']." <span class='fa fa-caret-right pull-right'></span></a>";
		echo "</div>\n";
		echo "</div>\n";
    }

}
