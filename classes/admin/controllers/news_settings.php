<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/admin/controllers/news_settings.php
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

namespace PHPFusion\News;

use PHPFusion\BreadCrumbs;

class NewsSettingsAdmin extends NewsAdminModel {
    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function displayNewsAdmin() {
        pageAccess("N");
        require_once INCLUDES."mimetypes_include.php";

        $locale = self::get_newsAdminLocale();
        $news_settings = self::get_news_settings();

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN."settings_news.php".fusion_get_aidlink(), 'title' => $locale['news_settings']]);
        if (isset($_POST['savesettings'])) {
            $inputArray = [
                "news_allow_submission"       => form_sanitizer($_POST['news_allow_submission'], 0, "news_allow_submission"),
                "news_allow_submission_files" => form_sanitizer($_POST['news_allow_submission_files'], 0, "news_allow_submission_files"),
                "news_extended_required"      => isset($_POST['news_extended_required']) ? 1 : 0,
                "news_pagination"             => form_sanitizer($_POST['news_pagination'], 12, "news_pagination"),
                "news_image_link"             => form_sanitizer($_POST['news_image_link'], 0, 'news_image_link'),
                "news_image_frontpage"        => form_sanitizer($_POST['news_image_frontpage'], 0, 'news_image_frontpage'),
                "news_image_readmore"         => form_sanitizer($_POST['news_image_readmore'], 0, 'news_image_readmore'),
                "news_thumb_ratio"            => form_sanitizer($_POST['news_thumb_ratio'], 0, 'news_thumb_ratio'),
                "news_thumb_w"                => form_sanitizer($_POST['news_thumb_w'], 800, 'news_thumb_w'),
                "news_thumb_h"                => form_sanitizer($_POST['news_thumb_h'], 640, 'news_thumb_h'),
                "news_photo_w"                => form_sanitizer($_POST['news_photo_w'], 1920, 'news_photo_w'),
                "news_photo_h"                => form_sanitizer($_POST['news_photo_h'], 1080, 'news_photo_h'),
                "news_photo_max_w"            => form_sanitizer($_POST['news_photo_max_w'], 2048, 'news_photo_max_w'),
                "news_photo_max_h"            => form_sanitizer($_POST['news_photo_max_h'], 1365, 'news_photo_max_h'),
                "news_photo_max_b"            => form_sanitizer($_POST['calc_b'], 2097152, 'calc_b') * form_sanitizer($_POST['calc_c'], 1, 'calc_c'),
                "news_file_types"             => form_sanitizer($_POST['news_file_types'], '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z', "news_file_types"),
            ];
            if (\defender::safe()) {
                foreach ($inputArray as $settings_name => $settings_value) {
                    $inputSettings = [
                        "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "news",
                    ];
                    dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", ["primary_key" => "settings_name"]);
                }
                addNotice("success", $locale['900']);
                redirect(FUSION_REQUEST);
            } else {
                addNotice('danger', $locale['901']);
            }
        }
        $opts = ['0' => $locale['news_0201'], '1' => $locale['news_953']];
        $cat_opts = ['0' => $locale['news_959'], '1' => $locale['news_0301']];
        $thumb_opts = ['0' => $locale['news_955'], '1' => $locale['news_956']];
        $calc_opts = $locale['1020'];
        $calc_c = calculate_byte($news_settings['news_photo_max_b']);
        $calc_b = $news_settings['news_photo_max_b'] / $calc_c;

        echo "<div class='m-t-20'>";
        echo "<h2>".$locale['news_settings']."</h2><hr/>";
        echo "<div class='well'>".$locale['news_description']."</div>";

        echo openform('settingsform', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-8'>\n";
        // Main Settings
        echo "<div class='spacer-sm'>";
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-3'>\n";
        echo "<h4 class='m-0'>".$locale['news_0707']."</h4><i>".$locale['news_0708']."</i>\n<br/><br/>";
        echo "</div><div class='col-xs-12 col-sm-9'>\n";
        echo form_text('news_pagination', $locale['669c'], $news_settings['news_pagination'], [
            'inline'      => TRUE,
            'max_length'  => 4,
            'width'       => '150px',
            'inner_width' => '150px',
            'type'        => 'number'
        ]);
        echo form_select('news_allow_submission', $locale['news_0400'], $news_settings['news_allow_submission'], [
            'inline'      => TRUE,
            'options'     => [$locale['disable'], $locale['enable']],
            'width'       => '100%',
            'inner_width' => '100%'
        ]);
        echo form_select('news_allow_submission_files', $locale['news_0401'], $news_settings['news_allow_submission_files'], [
            'inline'      => TRUE,
            'options'     => [$locale['disable'], $locale['enable']],
            'width'       => '100%',
            'inner_width' => '100%'
        ]);
        echo form_checkbox('news_extended_required', $locale['news_0402'], $news_settings['news_extended_required'], [
            'inline' => TRUE
        ]);
        echo "</div>\n</div>\n";
        echo "</div>\n";
        echo "<hr/>\n";

        // Photo Settings
        echo "<div class='spacer-sm'>\n";
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-3'>\n";
        echo "<h4 class='m-0'>".$locale['news_0709']."</h4><i>".$locale['news_0710']."</i>\n<br/><br/>";
        echo "</div><div class='col-xs-12 col-sm-9'>\n";
        echo "<div class='display-block overflow-hide'>
        <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='news_thumb_w'>".$locale['news_601']."</label>
        <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('news_thumb_w', '', $news_settings['news_thumb_w'], [
                'class'         => 'pull-left m-r-10',
                'max_length'    => 4,
                'type'          => 'number',
                'width'         => '150px',
                'prepend'       => TRUE,
                'prepend_value' => $locale['news_0705']
            ]).
            form_text('news_thumb_h', '', $news_settings['news_thumb_h'], [
                'class'         => 'pull-left',
                'max_length'    => 4,
                'type'          => 'number',
                'width'         => '150px',
                'prepend'       => TRUE,
                'prepend_value' => $locale['news_0706']
            ])."
            </div>
        </div>";
        echo "<div class='display-block overflow-hide'>
        <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='news_photo_w'>".$locale['news_602']."</label>
        <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('news_photo_w', '', $news_settings['news_photo_w'], [
                'class'         => 'pull-left m-r-10',
                'max_length'    => 4,
                'type'          => 'number',
                'width'         => '150px',
                'prepend'       => TRUE,
                'prepend_value' => $locale['news_0705']
            ]).
            form_text('news_photo_h', '', $news_settings['news_photo_h'], [
                'class'         => 'pull-left',
                'max_length'    => 4,
                'type'          => 'number',
                'width'         => '150px',
                'prepend'       => TRUE,
                'prepend_value' => $locale['news_0706']
            ])."
            </div>
        </div>";
        echo "<div class='display-block overflow-hide'>
            <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='blog_thumb_w'>".$locale['news_603']."</label>
            <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
            ".form_text('news_photo_max_w', '', $news_settings['news_photo_max_w'], [
                'class'         => 'pull-left m-r-10',
                'max_length'    => 4,
                'type'          => 'number',
                'width'         => '150px',
                'prepend'       => TRUE,
                'prepend_value' => $locale['news_0705']
            ]).
            form_text('news_photo_max_h', '', $news_settings['news_photo_max_h'], [
                'class'         => 'pull-left',
                'max_length'    => 4,
                'type'          => 'number',
                'width'         => '150px',
                'prepend'       => TRUE,
                'prepend_value' => $locale['news_0706']
            ])."
            </div>
        </div>";
        echo "<div class='display-block overflow-hide'>
        <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='calc_b'>".$locale['news_605']."</label>
        <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('calc_b', '', $calc_b, [
                'required'   => TRUE,
                'type'       => 'number',
                'error_text' => $locale['error_rate'],
                'width'      => '100px',
                'max_length' => 2,
                'number_min' => 1,
                'class'      => 'pull-left m-r-10'
            ])."
        ".form_select('calc_c', '', $calc_c, [
                'options'     => $calc_opts,
                'placeholder' => $locale['choose'],
                'width'       => '180px',
                'inner_width' => '100%',
                'class'       => 'pull-left'
            ])."
            </div>
        </div>";
        echo "</div>\n</div>\n";
        echo "</div>\n";

        echo "</div>\n<div class='col-xs-9 col-xs-offset-3 col-sm-9 col-sm-offset-3 col-md-4 col-md-offset-0 col-lg-4'>\n";

        echo openside('');
        echo form_select('news_image_link', $locale['news_951'], $news_settings['news_image_link'], ['options' => $opts, 'width' => '100%', 'inner_width' => '100%']);
        echo form_select('news_image_frontpage', $locale['news_957'], $news_settings['news_image_frontpage'], ['options' => $cat_opts, 'width' => '100%', 'inner_width' => '100%']);
        echo form_select('news_image_readmore', $locale['news_958'], $news_settings['news_image_readmore'], ['options' => $cat_opts, 'width' => '100%', 'inner_width' => '100%']);
        echo form_select('news_thumb_ratio', $locale['news_954'], $news_settings['news_thumb_ratio'], ['options' => $thumb_opts, 'width' => '100%', 'inner_width' => '100%']);

        $mime = mimeTypes();
        $mime_opts = [];
        foreach ($mime as $m => $Mime) {
            $ext = ".$m";
            $mime_opts[$ext] = $ext;
        }
        sort($mime_opts);
        echo form_select('news_file_types', $locale['news_0271'], $news_settings['news_file_types'],
            [
                'options'     => $mime_opts,
                'error_text'  => $locale['error_type'],
                'placeholder' => $locale['choose'],
                'multiple'    => TRUE,
                'tags'        => TRUE,
                'width'       => '100%',
                'inner_width' => '100%',
                'delimiter'   => '|'
            ]);
        echo closeside();

        echo "</div></div>\n";
        echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
        echo closeform();

        echo '</div>';
    }
}
