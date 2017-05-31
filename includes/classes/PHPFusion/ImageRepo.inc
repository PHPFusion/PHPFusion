<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ImageRepo.inc
| Author: Takács Ákos (Rimelek)
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

/**
 * A class to handle imagepaths
 */
class ImageRepo {
    // Flaws: Not having images in the theme will break the site. Even the files format are different. Developers have no options for CSS buttons.
    // If we change this now, it will break all the themes on main site repository. Only solution is to address this in a new version to force deprecate old themes.
    /**
     *
     * @var string[]
     */
    /**
     * All cached paths
     *
     * @var string[]
     */
    private static $imagePaths = array();

    /**
     * The state of the cache
     *
     * @var boolean
     */
    private static $cached = FALSE;

    /**
     * Get all imagepaths
     *
     * @return string[]
     */
    public static function getImagePaths() {
        self::cache();

        return self::$imagePaths;
    }

    /**
     * Fetch and cache all off the imagepaths
     */
    private static function cache() {
        if (self::$cached) {
            return;
        }
        self::$cached = TRUE;
        //<editor-fold desc="imagePaths">
        // You need to + sign it, so setImage will work.
        self::$imagePaths += array(
            //A
            //B
            //C
            //D
            "down" => THEMES."admin_themes/".fusion_get_settings('admin_theme')."/images/down.png",
            //E
            //F
            //G
            //H
            //I
            "imagenotfound" => IMAGES."imagenotfound.jpg",
            //J
            //K
            //L
            "left" => THEMES."admin_themes/".fusion_get_settings('admin_theme')."/images/left.png",
            //M
            //N
            "noavatar" => IMAGES."avatars/no-avatar.jpg",
            "noavatar50" => IMAGES."avatars/noavatar50.png",
            "noavatar100" => IMAGES."avatars/noavatar100.png",
            "noavatar150" => IMAGES."avatars/noavatar150.png",
            //O
            //P
            "panel_on" => IMAGES."panel_on.gif",
            "panel_off" => IMAGES."panel_off.gif",
            //Q
            //R
            "right" => THEMES."admin_themes/".fusion_get_settings('admin_theme')."/images/right.png",
            //S
            //T
            //U
            "up" => THEMES."admin_themes/".fusion_get_settings('admin_theme')."/images/up.png",
            //V
            //W
            //X
            //Y
            //Z
        );
        //</editor-fold>
        $installedTables = array(
            'blog' => infusion_exists('blog'),
            'news' => infusion_exists('news')
        );

        $selects = "SELECT admin_image as image, admin_rights as name, 'ac_' as prefix FROM ".DB_ADMIN;
        $result = dbquery($selects);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $image = file_exists(ADMIN."images/".$data['image']) ? ADMIN."images/".$data['image'] : (file_exists(INFUSIONS.$data['image']) ? INFUSIONS.$data['image'] : ADMIN."images/infusion_panel.png");
                if (empty(self::$imagePaths[$data['prefix'].$data['name']])) {
                    self::$imagePaths[$data['prefix'].$data['name']] = $image;
                }
            }
        }

        //smiley
        foreach (cache_smileys() as $smiley) {
            // set image
            if (empty(self::$imagePaths["smiley_".$smiley['smiley_text']])) {
                self::$imagePaths["smiley_".$smiley['smiley_text']] = fusion_get_settings('siteurl')."images/smiley/".$smiley['smiley_image'];
            }
        }

        $selects_ = [];
        if ($installedTables['blog']) {
            $selects_[] = "SELECT blog_cat_image as image, blog_cat_name as name, 'bl_' as prefix FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? " where blog_cat_language='".LANGUAGE."'" : "");
        }

        if ($installedTables['news'] && column_exists(DB_NEWS_CATS, 'news_cat_language', FALSE)) {
            $selects_[] = "SELECT news_cat_image as image, news_cat_name as name, 'nc_' as prefix FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? " where news_cat_language='".LANGUAGE."'" : "");
        }

        if (!empty($selects_)) {
            $union = implode(' union ', $selects_);
            $result = dbquery($union);
            while ($data = dbarray($result)) {
                switch ($data['prefix']) {
                    case 'nc_':
                    default :
                        $image = file_exists(INFUSIONS.'news/news_cats/'.$data['image']) ? INFUSIONS.'news/news_cats/'.$data['image'] : IMAGES."imagenotfound.jpg";
                        break;
                    case 'bl_':
                        $image = file_exists(INFUSIONS.'blog/blog_cats/'.$data['image']) ? INFUSIONS.'blog/blog_cats/'.$data['image'] : IMAGES."imagenotfound.jpg";
                        break;
                }
                // Set image
                if (empty(self::$imagePaths[$data['prefix'].$data['name']])) {
                    self::$imagePaths[$data['prefix'].$data['name']] = $image;
                }
            }
        }
    }

    /**
     * Get the imagepath or the html "img" tag
     *
     * @param string $image The name of the image.
     * @param string $alt "alt" attribute of the image
     * @param string $style "style" attribute of the image
     * @param string $title "title" attribute of the image
     * @param string $atts Custom attributes of the image
     * @return string The path of the image if the first argument is given,
     * but others not. Otherwise the html "img" tag
     */
    public static function getImage($image, $alt = "", $style = "", $title = "", $atts = "") {
        self::cache();
        $url = isset(self::$imagePaths[$image]) ? self::$imagePaths[$image] : IMAGES."imagenotfound.jpg";
        if ($style) {
            $style = " style='$style'";
        }
        if ($title) {
            $title = " title='".$title."'";
        }

        return ($alt or $style or $title or $atts)
            ? "<img src='".$url."' alt='".$alt."'".$style.$title." ".$atts." />" :
            $url;
    }

    /**
     * Set a path of an image
     *
     * @param string $name
     * @param string $path
     */
    public static function setImage($name, $path) {
        self::$imagePaths[$name] = $path;
    }

    /**
     * Replace a part in each path
     *
     * @param string $source
     * @param string $target
     */
    public static function replaceInAllPath($source, $target) {
        self::cache();
        foreach (self::$imagePaths as $name => $path) {
            self::$imagePaths[$name] = str_replace($source, $target, $path);
        }
    }

    /**
     * Given a path, returns an array of all files
     * @param $path
     * @return array
     */
    public static function getFileList($path) {
        $image_list = array();
        if (is_dir($path)) {
            $image_files = makefilelist($path, ".|..|index.php", TRUE);
            foreach ($image_files as $image) {
                $image_list[$image] = $image;
            }
        }

        return (array)$image_list;
    }

}
