<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Functions.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Infusions\Forum\Classes;

class ForumFunctions {
    /**
     * Appends increment integer on multiple files on same post
     *
     * @param $file
     *
     * @return string
     */
    public static function attachmentExists( $file ) {
        $dir = INFUSIONS."forum/attachments/";
        $count = 1;
        $fileName = substr($file, 0, strrpos($file, "."));
        $fileExt = strrchr($file, ".");
        while (file_exists($dir.$file)) {
            $file = $fileName."_".$count.$fileExt;
            $count++;
        }

        return $file;
    }

    /**
     * Display an image
     *
     * @param $file
     *
     * @return string
     */
    public static function displayImage($file) {
        $size = getimagesize(INFUSIONS."forum/attachments/".$file);
        if ($size[0] > 300 || $size[1] > 200) {
            if ($size[0] <= $size[1]) {
                $imgWidth = round(($size[0] * 200) / $size[1]);
                $imgHeight = 200;
            } else if ($size[0] > $size[1]) {
                $imgWidth = 300;
                $imgHeight = round(($size[1] * 300) / $size[0]);
            } else {
                $imgWidth = 300;
                $imgHeight = 200;
            }
        } else {
            $imgWidth = $size[0];
            $imgHeight = $size[1];
        }
        if ($size[0] != $imgWidth || $size[1] != $imgHeight) {
            $res = "<a href='".INFUSIONS."forum/attachments/".$file."'><img src='".INFUSIONS."forum/attachments/".$file."' width='".$imgWidth."' height='".$imgHeight."' style='border:0;' alt='".$file."' /></a>";
        } else {
            $res = "<img src='".INFUSIONS."forum/attachments/".$file."' width='".$imgWidth."' height='".$imgHeight."' style='border:0;' alt='".$file."' />";
        }

        return $res;
    }

    /**
     * Display attached image with a certain given width and height.
     *
     * @param        $file
     * @param int    $width
     * @param int    $height
     * @param string $rel
     *
     * @return string
     */
    public static function displayImageAttachments($file, $width = 200, $height = 200, $rel = "") {
        if (file_exists(INFUSIONS."forum/attachments/".$file)) {
            $size = getimagesize(INFUSIONS."forum/attachments/".$file);
            if ($size [0] > $height || $size [1] > $width) {
                if ($size [0] < $size [1]) {
                    $imgWidth = round(($size [0] * $width) / $size [1]);
                    $imgHeight = $width;
                } else if ($size [0] > $size [1]) {
                    $imgWidth = $height;
                    $imgHeight = round(($size [1] * $height) / $size [0]);
                } else {
                    $imgWidth = $height;
                    $imgHeight = $width;
                }
            } else {
                $imgWidth = $size [0];
                $imgHeight = $size [1];
            }
            $res = "<a target='_blank' href='".INFUSIONS."forum/attachments/".$file."' rel='attach_".$rel."' title='".$file."'><img src='".INFUSIONS."forum/attachments/".$file."' alt='".$file."' style='width:".$imgWidth."px; height:".$imgHeight."px;' /></a>\n";
        } else {
            $res = fusion_get_locale('forum_0188');
        }

        return $res;
    }
}
