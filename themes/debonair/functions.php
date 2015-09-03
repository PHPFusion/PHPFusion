<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Bootstrap Theme Functions
| Filename: functions.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
// Step 1 - determine the total side length
function total_side_span($value) {
	$count = 0;
	if (defined('LEFT') && LEFT) $count = $count+$value;
	if (defined('RIGHT') && RIGHT) $count = $count+$value;
	if ($count > 12) $count = 12;
	return $count;
}

// Step 2 - get the balance out of max 12 for center settings after deduction of total side_length
function center_grid_settings($side_grid_settings) {
	return array('desktop_size' => (12-total_side_span($side_grid_settings['desktop_size'])) > 0 ? 12-total_side_span($side_grid_settings['desktop_size']) : 12,
		'laptop_size' => (12-total_side_span($side_grid_settings['laptop_size'])) > 0 ? 12-total_side_span($side_grid_settings['laptop_size']) : 12,
		'tablet_size' => (12-total_side_span($side_grid_settings['tablet_size'])) > 0 ? 12-total_side_span($side_grid_settings['tablet_size']) : 12,
		'phone_size' => (12-total_side_span($side_grid_settings['phone_size'])) > 0 ? 12-total_side_span($side_grid_settings['phone_size']) : 12,);
}

// Step 3 - Output of various css grid class required
function html_prefix(array $array) {
	$array['phone_size'] = ($array['phone_size'] == 0) ? 'hidden-xs' : 'col-xs-'.$array['phone_size'];
	$array['tablet_size'] = ($array['tablet_size'] == 0) ? 'hidden-sm' : 'col-sm-'.$array['tablet_size'];
	$array['laptop_size'] = ($array['laptop_size'] == 0) ? 'hidden-md' : 'col-md-'.$array['laptop_size'];
	$array['desktop_size'] = ($array['desktop_size'] == 0) ? 'hidden-lg' : 'col-lg-'.$array['desktop_size'];
	return "".$array['phone_size']." ".$array['tablet_size']." ".$array['laptop_size']." ".$array['desktop_size']."";
}

/**
 * Serialization of choices
 * @param $input
 * @return $string
 */
function composeSelection($input) {
	$inputArray = "";
	if ($input !=="") {
		$inputArray['selected'] = $input;
		foreach(fusion_get_enabled_languages() as $lang) {
			$inputArray['options'][$lang] = isset($_POST[$input.'-'.$lang]) ? form_sanitizer($_POST[$input.'-'.$lang], 0, $input.'-'.$lang) : "";
		}
		return serialize($inputArray);
	}
	return $inputArray;
}

/**
 * Unserialization of choices
 * @param $input
 * @return array
 */
function uncomposeSelection($input) {
	if ($input !=="" && \PHPFusion\QuantumFields::is_serialized($input))
	{
		return (array) unserialize($input);
	}
	return array();
}

/** Opentable **/
if (!defined("ADMIN_PANEL")) {
	function opentable($title) {
		echo '<div class="txt-content">
                           <h3>'.$title.'</h3><p>';
	}
	/** Closetable **/
	function closetable() {
		echo "</p>
          </div>";
	}

	/** Openside **/
	function openside($title) {
		echo '<h3>'.$title.'</h3><p>';
	}

	/** Closeside **/
	function closeside() {
		echo '</p>';
	}
}


// End of bootstrap calculate
// Standard open and close functions
/*
function openside($title) {
	echo "<h4>$title</h4>\n";
	echo "<div class='list-group-item'>\n";
}
function closeside() {
	echo "</div>\n";
}
function opentable($title) {
	echo "<h3>$title</h3>\n";
}
function closetable() {
}
*/


/*
function render_news($subject, $news, $info) {
	echo '<div class="post-holder">

    <div class="post">
                           <!-- Start date-box -->
                           <div class="date-box">
                              <div> '.showdate('<strong>%d</strong> <span<>%m</span> ', $info["news_date"]).'</div>
                           </div>
                           <!-- End date-box -->
                           <div class="heading">
                              <h2><a href="'.BASEDIR.'news.php?readmore='.$info["news_id"].'">'.$subject.'</a></h2>
                              <div class="info">
                                 <p>'.showdate('%d.%m.%y um %H.%M', $info["news_date"]).' Uhr von '.profile_link($info["user_id"], $info["user_name"], $info["user_status"]).'</p>
                                 <em><a href="'.BASEDIR.'news.php?readmore='.$info["news_id"].'">'.$info["news_comments"].' comments</a></em> </div>
                           </div>
                           <div class="txt-content">
                              <p>'.$news.'</p>
                           </div>
                           <div class="more-holder"> <a href="'.BASEDIR.'news.php?readmore='.$info["news_id"].'" class="more-dark">Read More</a> </div>
                        </div>

    </div>';
}

function render_article($subject, $article, $info) {
	echo '<div class="post-holder">

    <div class="post">
                           <!-- Start date-box -->
                           <div class="date-box">
                              <div> '.showdate('<strong>%d</strong> <span<>%m</span> ', $info["article_date"]).'</div>
                           </div>
                           <!-- End date-box -->
                           <div class="heading">
                              <h2><a href="'.BASEDIR.'articles.php?article_id='.$info["article_id"].'">'.$subject.'</a></h2>
                              <div class="info">
                                 <p>'.showdate('%d.%m.%y um %H.%M', $info["article_date"]).' Uhr von '.profile_link($info["user_id"], $info["user_name"], $info["user_status"]).'</p>
                                 <em><a href="'.BASEDIR.'articles.php?article_id='.$info["article_id"].'">'.$info["article_comments"].' comments</a></em> </div>
                           </div>
                           <div class="txt-content">
                              <p>'.$article.'</p>
                           </div>
                           <div class="more-holder"> <a href="'.BASEDIR.'articles.php?article_id='.$info["article_id"].'" class="more-dark">Read More</a> </div>
                        </div>

    </div>';
}
*/