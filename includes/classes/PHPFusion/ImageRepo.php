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
     * We will go with Font Awesome
     *
     * @var string[]
     */
    private static $glyphicons = [
        'plus'              => 'fa-regular fa-plus',
        'minus'             => 'fa-regular fa-minus',
        'up'                => 'fa-regular fa-up',
        'down'              => 'fa-regular fa-down',
        'left'              => 'fa-regular fa-left',
        'right'             => 'fa-regular fa-right',
        'caret-up'          => 'fa-regular fa-caret-up',
        'caret-down'        => 'fa-regular fa-caret-down',
        'caret-left'        => 'fa-regular fa-caret-left',
        'caret-right'       => 'fa-regular fa-caret-right',
        'apply'             => 'fa-regular fa-check',
        'cancel'            => 'fa-regular fa-ban',
        'reset'             => 'fa-regular fa-rotate-left',
        'reply'             => 'fa-regular fa-reply',
        'forward'           => 'fa-regular fa-share-from-square',
        'first'             => 'fa-regular fa-angle-left',
        'last'              => 'fa-regular fa-angle-right',
        'next'              => 'fa-regular fa-angle-double-right',
        'previous'          => 'fa-regular fa-angle-double-left',
        'edit'              => 'fa-regular fa-edit',
        'delete'            => 'fa-regular fa-trash',
        'view'              => 'fa-regular fa-eye',
        'more'              => 'fa-regular fa-ellipsis-v',
        'filter'            => 'fa-regular fa-filters',
        'asc'               => 'fa-regular fa-sort-up',
        'desc'              => 'fa-regular fa-sort-down',
        'move'              => 'fa-regular fa-up-down-left-right',
        'maximize'          => 'fa-regular fa-maximize',
        'minimize'          => 'fa-regular fa-down-left-and-up-right-to-center',
        'user'              => 'fa-regular fa-user',
        'admin'             => 'fa-regular fa-user-secret',
        'user-profile'      => 'fa-regular id-badge',
        'user-groups'       => 'fa-regular fa-users-rectangle',
        'user-active'       => 'fa-regular fa-user-check',
        'user-joined'       => 'fa-regular fa-calendar-circle-user',
        'user-banned'       => 'fa-regular fa-user-xmark',
        'user-inactive'     => 'fa-regular fa-user-clock',
        'forum-post'        => 'fa-regular fa-messages',
        'forum-spam'        => 'fa-regular fa-message-xmark',
        'forum-sticky'      => 'fa-regular fa-message-arrow-up',
        'forum-question'    => 'fa-regular fa-message-question',
        'forum-answer'      => 'fa-regular fa-message-check',
        'forum-quote'       => 'fa-regular fa-message-quote',
        'forum-attachments' => 'fa-regular fa-message-image',
        'forum-warning'     => 'fa-regular fa-message-exclamation',
        'forum-reputation'  => 'fa-regular fa-hundred-points',
        'forum-upvoted'     => 'fa-regular fa-message-arrow-up',
        'forum-downvoted'   => 'fa-regular fa-message-arrow-down',
        'vote'              => 'fa-regular fa-check-to-slot',
        'unvote'            => 'fa-regular fa-xmark-to-slot',
        'note'              => 'fa-regular fa-note-sticky',
        'auto-bot'          => 'fa-regular fa-message-bot',
        'comments'          => 'fa-regular fa-comments',
        'comment'           => 'fa-regular fa-comment',
        'poll'              => 'fa-regular fa-square-poll-vertical',
        'games'             => 'fa-regular fa-dice',
        'print'             => 'fa-regular fa-print',
        'items'             => 'fa-regular fa-box-heart',
        'security'          => 'fa-regular fa-shield-cross',
        'infusion'          => 'fa-regular fa-magnet',
        'collection'        => 'fa-regular fa-gift',
        'coins'             => 'fa-regular fa-sack',
        'location'          => 'fa-regular fa-location-dot',
        'code'              => 'fa-regular fa-brackets-curly',
        'success'           => 'fa-regular fa-badge-check',
        'warning'           => 'fa-regular fa-triangle-exclamation',
        'danger'            => 'fa-regular fa-light-emergency-on',
        'donation'          => 'fa-regular fa-hands-holding-dollar',
        'import'            => 'fa-regular fa-up-to-line',
        'export'            => 'fa-regular fa-down-from-line',
        'time'              => 'fa-regular fa-fa-clock',
        'duration'          => 'fa-regular fa-clock-rotate-left',
        'locked'            => 'fa-regular fa-lock',
        'unlocked'          => 'fa-regular fa-lock-open',
        'login'             => 'fa-regular fa-right-to-bracket',
        'logout'            => 'fa-regular fa-right-from-bracket',
        'tech-support'      => 'fa-regular fa-headset',
        'maintenance'       => 'fa-regular fa-helmet-safety',
        'site-links'        => 'fa-regular fa-sitemap',
        'bug'               => 'fa-regular fa-bug',
        'contact'           => 'fa-regular fa-square-phone',
        'covid'             => 'fa-regular fa-virus-covid',
    ];

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
     * @param        $name
     * @param string $class
     *
     * @return string
     */
    public static function getIcon(string $name, string $class = "", string $tooltip = "") {
        $icon = (self::$glyphicons[$name]) ?? '';
        $tooltip = $tooltip ? 'data-toggle="tooltip" title="'.$tooltip.'"' : '';

        return '<i class="'.$icon.whitespace($class).'" '.$tooltip.'></i>';
    }

    public static function setIcon($name, $value) {
        self::$glyphicons[$name] = $value;
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
            $result = cdquery('smileys_cache', "SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS);
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
