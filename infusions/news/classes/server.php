<?php

namespace PHPFusion\News;

abstract class NewsServer {

    public static $news_instance = NULL;

    public static function News() {
        if (empty(self::$news_instance)) {
            self::$news_instance = new NewsView();
        }
        return (object) self::$news_instance;
    }

    public static $news_settings = array();

    public static function get_news_settings() {
        if (empty( self::$news_settings )) {
            self::$news_settings = get_settings("news");
        }
        return self::$news_settings;
    }



}