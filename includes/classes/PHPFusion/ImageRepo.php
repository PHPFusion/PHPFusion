<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ImageRepo.php
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
     * @var string[]
     */
    /**
     * All cached paths
     *
     * @var string[]
     */
    private static $image_paths = [];

    /**
     * The state of the cache
     *
     * @var boolean
     */
    private static $cached = FALSE;

    /**
     * Cache installed smiley images from database
     *
     * @return array|null
     */
    private static $smiley_cache = NULL;

    /**
     * Get all imagepaths
     *
     * @return string[]
     */
    public static function getImagePaths() {
        self::cache();

        return self::$image_paths;
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
        self::$image_paths += [
            //A
            //B
            //C
            //D
            "down"          => IMAGES."icons/down.png",
            //E
            //F
            //G
            //H
            //I
            "imagenotfound" => IMAGES."imagenotfound.jpg",
            //J
            //K
            //L
            "left"          => IMAGES."icons/left.png",
            //M
            //N
            "noavatar"      => IMAGES."avatars/no-avatar.jpg",
            //O
            //P
            "panel_on"      => IMAGES."icons/panel_on.gif",
            "panel_off"     => IMAGES."icons/panel_off.gif",
            //Q
            //R
            "right"         => IMAGES."icons/right.png",
            //S
            //T
            //U
            "up"            => IMAGES."icons/up.png",
            //V
            //W
            //X
            //Y
            //Z
        ];
        //</editor-fold>
        $installedTables = [
            'blog' => defined('BLOG_EXISTS'),
            'news' => defined('NEWS_EXISTS')
        ];

        $selects = "SELECT admin_image as image, admin_rights as name, 'ac_' as prefix FROM ".DB_ADMIN;
        $result = dbquery($selects);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $image = file_exists(ADMIN."images/".$data['image']) ? ADMIN."images/".$data['image'] : (file_exists(INFUSIONS.$data['image']) ? INFUSIONS.$data['image'] : ADMIN."images/infusion_panel.png");
                if (empty(self::$image_paths[$data['prefix'].$data['name']])) {
                    self::$image_paths[$data['prefix'].$data['name']] = $image;
                }
            }
        }

        //smiley
        foreach (cache_smileys() as $smiley) {
            // set image
            if (empty(self::$image_paths["smiley_".$smiley['smiley_text']])) {
                self::$image_paths["smiley_".$smiley['smiley_text']] = IMAGES."smiley/".$smiley['smiley_image'];
            }
        }

        $selects_ = [];
        if ($installedTables['blog']) {
            $selects_[] = "SELECT blog_cat_image as image, blog_cat_name as name, 'bl_' as prefix FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? " where ".in_group('blog_cat_language', LANGUAGE) : "");
        }

        if ($installedTables['news']) {
            $selects_[] = "SELECT news_cat_image as image, news_cat_name as name, 'nc_' as prefix FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? " where ".in_group('news_cat_language', LANGUAGE) : "");
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
                if (empty(self::$image_paths[$data['prefix'].$data['name']])) {
                    self::$image_paths[$data['prefix'].$data['name']] = $image;
                }
            }
        }
    }

    /**
     * Get the imagepath or the html "img" tag
     *
     * @param string $image The name of the image.
     * @param string $alt   "alt" attribute of the image
     * @param string $style "style" attribute of the image
     * @param string $title "title" attribute of the image
     * @param string $atts  Custom attributes of the image
     *
     * @return string The path of the image if the first argument is given,
     * but others not. Otherwise, the html "img" tag
     */
    public static function getImage($image, $alt = "", $style = "", $title = "", $atts = "") {
        self::cache();
        $url = self::$image_paths[$image] ?? IMAGES."imagenotfound.jpg";
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
        self::$image_paths[$name] = $path;
    }

    /**
     * Replace a part in each path
     *
     * @param string $source
     * @param string $target
     */
    public static function replaceInAllPath($source, $target) {
        self::cache();
        foreach (self::$image_paths as $name => $path) {
            self::$image_paths[$name] = str_replace($source, $target, $path);
        }
    }

    /**
     * Given a path, returns an array of all files
     *
     * @param string $path
     *
     * @return array
     */
    public static function getFileList($path) {
        $image_list = [];
        if (is_dir($path)) {
            $image_files = makefilelist($path, ".|..|index.php", TRUE);
            foreach ($image_files as $image) {
                $image_list[$image] = $image;
            }
        }

        return $image_list;
    }

    public static function cacheSmileys() {
        if (self::$smiley_cache === NULL) {
            self::$smiley_cache = [];
            $result = cdquery('smileys_cache',"SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS);
            while ($data = cdarray($result)) {
                self::$smiley_cache[] = [
                    'smiley_code'  => $data['smiley_code'],
                    'smiley_image' => $data['smiley_image'],
                    'smiley_text'  => $data['smiley_text']
                ];
            }
        }

        return self::$smiley_cache;
    }
}
