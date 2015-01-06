<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: output_handling_include.php
| Author: Max Toball (Matonor)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion {
	//TODO: PHPDoc comments
	class OutputHandler {
		private static $pageMeta = array();
		private static $pageTitle = "";
		private static $pageReplacements = "";
		private static $outputHandlers = "";
		
		public static $pageHeadTags = "";
		public static $pageFooterTags = "";
		public static $jqueryTags = "";
		
		public static function setTitle($title = "") {
			self::$pageTitle = $title;
		}
		
		public static function addToTitle($addition = "") {
			self::$pageTitle .= preg_replace("/".$GLOBALS['locale']['global_200']."/", '', $addition, 1);
		}
		
		public static function setMeta($name, $content = "") {
			self::$pageMeta[$name] = $content;
		}
		
		public static function addToMeta($name, $addition = "") {
			$settings = \fusion_get_settings();
			if (empty(self::$pageMeta)) {
				self::$pageMeta =  array(
					"description" => $settings['description'], 
					"keywords" => $settings['keywords']
				);
			}
			if (isset(self::$pageMeta[$name])) {
				self::$pageMeta[$name] .= $addition;
			}
		}
		
		public static function addToHead($tag = "") {
			if (!\stristr(self::$pageHeadTags, $tag)) {
				self::$pageHeadTags .= $tag."\n";
			}
		}
		
		public static function addToFooter($tag = "") {
			if (!stristr(self::$pageFooterTags, $tag)) {
				self::$pageFooterTags .= $tag."\n";
			}
		}
		
		public static function replaceInOutput($target, $replace, $modifiers = "") {
			self::$pageReplacements .= "\$output = preg_replace('^$target^$modifiers', '$replace', \$output);";
		}
		
		public static function addHandler($name) {
			if (!empty($name)) {
				self::$outputHandlers .= "\$output = $name(\$output);";
			}
		}
		
		public static function addPermalinkHandler($name) {
			//TODO: test it
			$settings = \fusion_get_settings();
			if (!empty($name) && $settings['site_seo']) {
				self::$outputHandlers .= "\$permalink->AddHandler(\"$name\");";
			}
		}
		
		public static function addToJQuery ($tag = "") {
			self::$jqueryTags .= $tag;
		}
		
		public static function addToBreadCrumbs(array $link = array()) {
			//TODO: remove global variable
			global $breadcrumbs;
			if (!empty($link)) {
				$breadcrumbs[] = $link;
			}
		}
		
		public static function handleOutput($output) {
			//TODO: remove global variables
			global $permalink;
			$settings = \fusion_get_settings();
			
			if (!empty(self::$pageHeadTags)) {
				$output = preg_replace("#</head>#", self::$pageHeadTags."</head>", $output, 1);
			}

			if (self::$pageTitle != $settings['sitename']) {
				$output = preg_replace("#<title>.*</title>#i", "<title>".self::$pageTitle.$GLOBALS['locale']['global_200'].$settings['sitename']."</title>", $output, 1);
			}

			if (!empty(self::$pageMeta)) {
				foreach (self::$pageMeta as $name => $content) {
					$output = preg_replace("#<meta (http-equiv|name)='$name' content='.*' />#i", "<meta \\1='".$name."' content='".$content."' />", $output, 1);
				}
			}

			if (!empty(self::$pageReplacements)) {
				eval(self::$pageReplacements);
			}
			if (!empty(self::$outputHandlers)) {
				eval(self::$outputHandlers);
			}

			return $output;
		}
	}
}
namespace {
	use PHPFusion\OutputHandler;
	function set_title($title = "") {
		OutputHandler::setTitle($title);
	}

	function add_to_title($addition = "") {
		OutputHandler::addToTitle($addition);
	}

	function set_meta($name, $content = "") {
		OutputHandler::setMeta($name, $content);
	}

	function add_to_meta($name, $addition = "") {
		OutputHandler::addToMeta($name, $addition);
	}

	function add_to_head($tag = "") {
		OutputHandler::addToHead($tag);
	}

	function add_to_footer($tag = "") {
		OutputHandler::addToFooter($tag);
	}

	function replace_in_output($target, $replace, $modifiers = "") {
		OutputHandler::replaceInOutput($target, $replace, $modifiers);
	}

	function add_handler($name) {
		OutputHandler::addHandler($name);
	}

	function add_permalink_handler($name) {
		OutputHandler::addPermalinkHandler($name);
	}

	function handle_output($output) {
		return OutputHandler::handleOutput($output);
	}

	function add_to_jquery($tag = "") {
		OutputHandler::addToJQuery($tag);
	}

	// Add links to breadcrumbs array
	function add_to_breadcrumbs(array $link=array()) {
		OutputHandler::addToBreadCrumbs($link);
	}
}
?>