<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: search_bbcode_include_var.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!function_exists("generate_search_opts")) {
    function generate_search_opts($textarea_name, $inputform_name) {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'search.php');

        $generated[] = "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[search=all]', '[/search]', '".$inputform_name."');return false;\"><span>".$locale['407']."</span></a>";

        if ($handle = opendir(BASEDIR."includes/search")) {
            while (FALSE !== ($file = readdir($handle))) {
                if (preg_match("/_include.php/i", $file)) {
                    $name = '';
                    $search_name = explode("_", $file);
                    $locale += fusion_get_locale('', LOCALE.LOCALESET."search/".$search_name[1].".php");
                    foreach ($locale as $key => $value) {
                        if (preg_match("/400/i", $key)) {
                            $name = $key;
                        }
                    }

                    if (isset($locale[$name])) {
                        $generated[] = "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '".$search_name[1]."', '[/search]', '".$inputform_name."');return false;\"><span>".$locale[$name]."</span></a>";
                    }
                }
            }
            closedir($handle);
        }

        $infusions = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
        if (!empty($infusions)) {
            foreach ($infusions as $infusions_to_check) {
                if (is_dir(INFUSIONS.$infusions_to_check.'/search/')) {
                    $inf_files = makefilelist(INFUSIONS.$infusions_to_check.'/search/', ".|..|index.php", TRUE, "files");

                    if (!empty($inf_files)) {
                        foreach ($inf_files as $file) {
                            if (preg_match("/_include.php/i", $file)) {
                                $name = '';
                                $search_name = explode("_", $file);

                                $locale_file = INFUSIONS.$infusions_to_check."/locale/English/search/".$search_name[1].".php";

                                if (is_file(INFUSIONS.$infusions_to_check.'/locale/'.LOCALESET."search/".$search_name[1].".php")) {
                                    $locale_file = INFUSIONS.$infusions_to_check.'/locale/'.LOCALESET."search/".$search_name[1].".php";
                                }

                                $locale += fusion_get_locale('', $locale_file);

                                foreach ($locale as $key => $value) {
                                    if (preg_match("/400/i", $key)) {
                                        $name = $key;
                                    }
                                }

                                if (isset($locale[$name])) {
                                    $generated[] = "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '".$search_name[1]."', '[/search]', '".$inputform_name."');return false;\"><span>".(!empty($locale[$name]) ? $locale[$name] : $name)."</span></a>";
                                }
                            }
                        }
                    }
                }
            }
        }

        return $generated;
    }
}

$__BBCODE__[] = [
    'description'    => $locale['bb_search_description'],
    'value'          => "search",
    'bbcode_start'   => "[search=".$locale['bb_search_where']."]",
    'bbcode_end'     => "[/search]",
    'usage'          => "[search=".$locale['bb_search_where']."]".$locale['bb_search_usage']."[/search]",
    'onclick'        => "return false;",
    'id'             => 'bbcode_search_'.$textarea_name,
    'dropdown_style' => 'min-width: 150px;',
    'dropdown'       => TRUE,
    'dropdown_items' => generate_search_opts($textarea_name, $inputform_name),
    'svg'            => "<i class='fas fa-search fa-lg'></i>"
];
