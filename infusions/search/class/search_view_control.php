<?php

class Search_View_Control extends Search {

    /*
     * Members Item
     */
    public static function view_member($value) {
        return strtr(parent::$search_item, [
            '{%item_url%}'=> BASEDIR.'profile.php?lookup='.$value['user_id'],
            '{%item_image%}' => display_avatar($value, '30px', '', FALSE, ''),
            '{%item_title%}' => $value['user_name'],
            '{%item_description%}' => getuserlevel($value['user_level'])
        ]);
    }

    /* News Item */
    public static function view_news($value) {
        require_once INFUSIONS.'news/classes/autoloader.php';
        $locale = fusion_get_locale();
        return strtr(parent::$search_item, [
            '{%item_url%}'=> INFUSIONS.'news/news.php?readmore='.$value['news_id'],
            '{%item_image%}' => \PHPFusion\News\News::get_NewsImage($value, TRUE, FALSE, '100'),
            '{%item_title%}' => $value['news_subject'],
            '{%item_description%}' => "<span class='small2'>".$locale['global_070'].$value['user_name']." ".$locale['global_071'].showdate("longdate", $value['news_datestamp'])."</span>\n"
        ]);
    }

    public static function view_page($value) {
        $locale = fusion_get_locale();
        return strtr(parent::$search_item, [
            '{%item_url%}'=> BASEDIR.'viewpage.php?id='.$value['news_id'],
            '{%item_image%}' => display_avatar($value, '30px', '', FALSE, ''),
            '{%item_title%}' => $value['page_title'],
            '{%item_description%}' => "<span class='small2'>".$locale['global_070'].$value['user_name']." ".$locale['global_071'].showdate("longdate", $value['news_datestamp'])."</span>\n"
        ]);
    }

    public static function view_sitemap($value) {
        $link_url = $value['link_url'] == 'index.php' ? fusion_get_settings('opening_page') : $value['link_url'];
        return strtr(parent::$search_item, [
            '{%item_url%}'=> BASEDIR.$link_url,
            '{%item_image%}' => "<img src='".IMAGES."php-fusion-icon.png' title='".$value['link_name']."'/>",
            '{%item_title%}' => $value['link_name'],
            '{%item_description%}' => $link_url
        ]);
    }



}