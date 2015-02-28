<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show.php<br />
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or any later version.<br /><br />
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.<br /><br />
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br /><br />
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2009 Drew Phillips
 * @author drew010 <drew@drew-phillips.com>
 * @version 2.0.1 BETA (December 6th, 2009)
 * @package Securimage
 *
 */

require "securimage.php";

$img = new securimage();

// Available TTF Fonts
$ttf_fonts = array(
	"AHGBold", 
	"arlrndbld", 
	"BasculaCollege", 
	"Cartoon_Regular", 
	"elephant", 
	"HappySans", 
	"Kingthings", 
	"LLCOOPER", 
	"Tusj"
);

// Available GD Fonts
$gd_fonts = array(
	"automatic", 
	"bubblebath", 
	"caveman", 
	"crass"
);

$use_gd_font = false;
$image_type = "PNG";
$image_types = array(
	"PNG" => "SI_IMAGE_PNG",
	"JPG" => "SI_IMAGE_JPG",
	"GIF" => "SI_IMAGE_GIF"
);

// Sepsific image settings
$img->image_width = 300;
$img->image_height = 57;
$img->image_type = $image_types[$image_type]; // Valid options: SI_IMAGE_PNG, SI_IMAGE_JPG, SI_IMAGE_GIF

// Spesific code settings
$img->code_lenght = 10;
//$img->charset = ""; // The character set for individual characters in the image
$img->wordlist_file = "./words/words.txt";
$img->use_wordlist = true;

// Spesific font settings
if ($use_gd_font) {
	$img->gd_font_file = "./gd_fonts/".$gd_fonts[rand(0,3)].".gdf";
	$img->gd_font_size = 30; // The approximate size of the font in pixels.
	$img->use_gd_font = true;
} else {
	$img->ttf_file = "./ttf_fonts/".$ttf_fonts[rand(0,8)].".ttf";
}

// Image distortion
//$img->perturbation = 0.1; // 1.0 = high distortion, higher numbers = more distortion
$img->text_angle_minimum = 10;
$img->text_angle_maximum = 20;

// Background
$img->background_directory = "./backgrounds/".$image_type;

// Text
//$img->text_color = new Securimage_Color("#000");
$img->use_multi_text = true;
//$img->multi_text_color = array();

// Transparent
$img->use_transparent_text = false;
$img->text_transparency_percentage = rand(10,40); // 100 = completely transparent
$img->draw_lines_over_text = false;

// Lines
$img->num_lines = rand(5,10);
//$img->line_color = new Securimage_Color("#0000CC");

if (isset($_GET['signature'])) {
	$search = array("&", "\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;");
	$replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
	$image_signature = str_replace($search, $replace, $_GET['signature']);
} else {
	$image_signature = "";
}

// Captcha signature
$img->image_signature = $image_signature;
$img->signature_font = "./ttf_fonts/AHGBold.ttf";
$img->signature_color = new Securimage_Color("#000");

$img->show();

?>