<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news/news_preview.php
| Author: Frederick Chan MC
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

/**
 * Real time preview
 * Class News_Preview
 *
 * @package PHPFusion\News
 */
class News_Preview extends News {
    private $news_data = [];

    public function set_PreviewData($data) {
        $this->news_data = $data;
    }

    public function __construct() {
        parent::__construct();
    }

    public function display_preview() {
        self::$locale = fusion_get_locale('', NEWS_ADMIN_LOCALE);
        if (!empty($this->news_data)) {
            add_to_head("
            <style>
            .modal-dialog.modal-lg.preview {
            min-width:90%;
            }
            .modal-dialog.modal-lg.preview > .modal-content > .modal-body {
            padding: 0;
            }
            </style>
            ");
            $modal = openmodal('news_preview', self::$locale['news_0141'], ['class' => 'modal-lg preview']);
            $http_query = http_build_query($this->news_data).'&readmore='.$this->news_data['news_id'].'&rowstart=0';
            ob_start();
            echo "<iframe src='".NEWS_CLASS."news/news_preview.php?$http_query' style='height:80vh; width:100%; border:0; margin-bottom:-5px'></iframe>";
            $modal .= ob_get_clean();
            $modal .= closemodal();
            add_to_footer($modal);
            add_to_jquery("
            window.closeModal = function(){
            $('#news_preview-Modal').modal('hide');
            };
            ");
        }
    }

    public static function get_PreviewInfo() {
        $data = $_GET;

        if (empty($data)) {
            die();
        } else {
            echo "<script>
            $('body').on('click', 'a', function(e) {
               e.preventDefault();
            });
            </script>";
        }
        self::$locale = fusion_get_locale('', NEWS_LOCALE);
        $default_info = [
            'news_item'     => '',
            'news_filter'   => [],
            'news_category' => [],
        ];
        $info = array_merge_recursive($default_info, self::get_NewsFilter());
        $info = array_merge_recursive($info, self::get_NewsCategory());

        if (!empty($data['news_image_full_default'])) {
            $photo_result = dbquery("SELECT * FROM ".DB_NEWS_IMAGES." WHERE news_image_id=:image_id", [':image_id' => $data['news_image_full_default']]);
            if (dbrows($photo_result)) {
                $data += dbarray($photo_result);
            }
        }

        $category_result = dbquery("SELECT * FROM ".DB_NEWS_CATS." WHERE news_cat_id=:cat_id", [':cat_id' => $data['news_cat']]);
        if (dbrows($category_result)) {
            $data += dbarray($category_result);
        } else {
            $data['news_cat_id'] = 0;
        }
        $data += fusion_get_userdata();
        unset($data['user_password']);
        unset($data['user_admin_password']);
        unset($data['user_salt']);
        unset($data['user_admin_salt']);
        $data['news_id'] = 0;
        $data['submit_id'] = 0;
        $data['news_reads'] = 0;
        $data['count_comment'] = 0;
        $data['sum_rating'] = 0;
        $data['count_votes'] = 0;
        $data['news_show_comments'] = 0;
        $data['news_show_ratings'] = 0;
        $newsData = self::get_NewsData($data);
        $info['news_item'] = $newsData;

        return (array)$info;
    }
}
