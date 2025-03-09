<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: OutputHandler.php
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
namespace PHPFusion;

class OutputHandler {
    /**
     * Additional tags to the html head
     *
     * @var string
     */
    public static $pageHeadTags = "";
    /**
     * Additional contents to the footer
     *
     * @var string
     */
    public static $pageFooterTags = "";
    /**
     * Additional javascripts
     *
     * @var string
     */
    public static $jqueryCode = "";
    /**
     * Additional css
     *
     * @var string
     */
    public static $cssCode = "";
    /**
     * The title in the "title" tag
     *
     * @var string
     */
    public static $pageTitle = "";
    /**
     * Associative array of meta tags
     *
     * @var string[]
     */
    private static $pageMeta = [];
    /**
     * PHP code to execute using eval replace anything in the output
     *
     * @var callback[]
     */
    private static $outputHandlers = [];

    /**
     * Set the new title of the page
     *
     * @param string $title
     */
    public static function setTitle($title = "") {
        self::$pageTitle = $title;
    }

    /**
     * Append something to the title of the page
     *
     * @param string $addition
     */
    public static function addToTitle($addition = "") {
        self::$pageTitle .= $addition;
    }

    /**
     * Set a meta tag by name
     *
     * @param string $name
     * @param string $content
     */
    public static function setMeta($name, $content = "") {
        self::$pageMeta[$name] = $content;
    }

    /**
     * Append something to a meta tag
     *
     * @param string $name
     * @param string $addition
     */
    public static function addToMeta($name, $addition = "") {
        if (empty(self::$pageMeta)) {
            $settings = fusion_get_settings();
            self::$pageMeta = [
                'description' => $settings['description'],
                'keywords'    => $settings['keywords']
            ];
        }
        if (isset(self::$pageMeta[$name])) {
            self::$pageMeta[$name] .= ",".$addition;
        }
    }

    /**
     * Add content to the html head
     *
     * @param string $tag
     */
    public static function addToHead($tag = "") {
        if (!stristr(self::$pageHeadTags, $tag)) {
            self::$pageHeadTags .= $tag."\n";
        }
    }

    /**
     * Add content to the footer
     *
     * @param string $tag
     */
    public static function addToFooter($tag = "") {
        if (!stristr(self::$pageFooterTags, $tag)) {
            self::$pageFooterTags .= $tag."\n";
        }
    }

    /**
     * Add javascript source code to the output
     *
     * @param string $code
     */
    public static function addToJQuery($code = "") {
        if (!stristr(self::$jqueryCode, $code)) {
            self::$jqueryCode .= $code;
        }
    }

    /**
     * Add css code to the output
     *
     * @param string $code
     */
    public static function addToCss($code = "") {
        if (!stristr(self::$cssCode, $code)) {
            self::$cssCode .= $code;
        }
    }

    /**
     * Replace something in the output using regexp
     *
     * @param string $target    Regexp pattern without delimiters
     * @param string $replace   The new content
     * @param string $modifiers Regexp modifiers
     */
    public static function replaceInOutput($target, $replace, $modifiers = "") {
        self::$outputHandlers[] = function ($output) use ($target, $replace, $modifiers) {
            return preg_replace('^'.preg_quote($target, "^").'^'.$modifiers, $replace, $output);
        };
    }

    /**
     * Add a new output handler function
     *
     * @param callback $callback The name of a function or other callable object
     */
    public static function addHandler($callback) {
        if (is_callable($callback)) {
            self::$outputHandlers[] = $callback;
        }
    }

    /**
     * Get Current Page Title
     */
    public static function getTitle() {
        if (!empty(self::$pageTitle)) {
            return self::$pageTitle;
        }

        return "";
    }


    /**
     * Execute the output handlers
     *
     * @param string $output
     *
     * @return string
     * @global array $locale
     *
     */
    public static function handleOutput($output) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if (!empty(self::$pageHeadTags)) {
            $output = preg_replace("#</head>#", self::$pageHeadTags."</head>", $output, 1);
        }

        if (!empty(self::$cssCode)) {
            if ($settings['devmode'] == 0) {
                $minifier = new Minify\CSS(self::$cssCode);
                $css = $minifier->minify();
            } else {
                $css = self::$cssCode;
            }

            $output = preg_replace("#</head>#", "<style>".$css."</style></head>", $output, 1);
        }

        if (self::$pageTitle != $settings['sitename']) {
            self::$pageTitle = self::$pageTitle.(self::$pageTitle ? $locale['global_200'] : '').$settings['sitename'];
            $output = preg_replace("#<title>.*</title>#i", "<title>".self::$pageTitle."</title>", $output, 1);
        }

        if (!empty(self::$pageMeta)) {
            foreach (self::$pageMeta as $name => $content) {
                $output = preg_replace("#<meta (http-equiv|name)='$name' content='.*'(.*?)>#i", "<meta \\1='".$name."' content='".$content."'>", $output, 1);
            }
        }

        foreach (self::$outputHandlers as $handler) {
            $output = $handler($output);
        }

        return $output;
    }
}
