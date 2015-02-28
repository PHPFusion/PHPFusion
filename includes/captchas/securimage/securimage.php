<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage.php<br />
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
 * @copyright 2007 Drew Phillips
 * @author drew010 <drew@drew-phillips.com>
 * @version 1.0.3.1a (March 24, 2008)
 * @package Securimage
 *
 */

/**
  ChangeLog

  1.0.3.1a
  - Modified for mySQL for PHP-Fusion by Digitanium
  
  1.0.3.1
  - Error reading from wordlist in some cases caused words to be cut off 1 letter short

  1.0.3
  - Removed shadow_text from code which could cause an undefined property error due to removal from previous version

  1.0.2
  - Audible CAPTCHA Code wav files
  - Create codes from a word list instead of random strings

  1.0
  - Added the ability to use a selected character set, rather than a-z0-9 only.
  - Added the multi-color text option to use different colors for each letter.
  - Switched to automatic session handling instead of using files for code storage
  - Added GD Font support if ttf support is not available.  Can use internal GD fonts or load new ones.
  - Added the ability to set line thickness
  - Added option for drawing arced lines over letters
  - Added ability to choose image type for output

*/

define('SI_IMAGE_JPEG', 1);
define('SI_IMAGE_PNG',  2);
define('SI_IMAGE_GIF',  3);

class Securimage {

  var $image_width = 140;
  var $image_height = 45;
  var $image_type = SI_IMAGE_PNG;
  var $code_length = 4;
  var $charset = 'ABCDEFGHKLMNPRSTUVWYZ23456789';
  var $wordlist_file = '';
  var $use_wordlist  = true;
  var $use_gd_font = false;
  var $gd_font_file = 'gdfonts/bubblebath.gdf';
  var $gd_font_size = 20;
  var $ttf_file = "./elephant.ttf";
  var $font_size = 24;
  var $text_angle_minimum = -20;
  var $text_angle_maximum = 20;
  var $text_x_start = 8;
  var $text_minimum_distance = 30;
  var $text_maximum_distance = 33;
  var $image_bg_color = "#e3daed";
  var $text_color = "#ff0000";
  var $use_multi_text = true;
  var $multi_text_color = "#0a68dd,#f65c47,#8d32fd";
  var $use_transparent_text = true;
  var $text_transparency_percentage = 15;
  var $draw_lines = true;
  var $line_color = "#80BFFF";
  var $line_distance = 5;
  var $line_thickness = 1;
  var $draw_angled_lines = false;
  var $draw_lines_over_text = false;
  var $arc_linethrough = true;
  var $arc_line_colors = "#8080ff";
  var $audio_path = './audio/';

  var $im;
  var $bgimg;
  var $code;
  var $code_entered;
  var $correct_code;

  function Securimage()
  {
  	if (!defined("DB_PREFIX")) { 
			require_once "../../../config.php";
			require_once "../../multisite_include.php";
			
			mysql_connect($db_host, $db_user, $db_pass);
			mysql_select_db($db_name);
		}
  }

  function show($background_image = "")
  {
    if($background_image != "" && is_readable($background_image)) {
      $this->bgimg = $background_image;
    }

    $this->doImage();
  }

  function check($code)
  {
    $this->code_entered = $code;
    $this->validate();
    return $this->correct_code;
  }

  function doImage()
  {
    if($this->use_transparent_text == true || $this->bgimg != "") {
      $this->im = imagecreatetruecolor($this->image_width, $this->image_height);
      $bgcolor = imagecolorallocate($this->im, hexdec(substr($this->image_bg_color, 1, 2)), hexdec(substr($this->image_bg_color, 3, 2)), hexdec(substr($this->image_bg_color, 5, 2)));
      imagefilledrectangle($this->im, 0, 0, imagesx($this->im), imagesy($this->im), $bgcolor);
    } else { //no transparency
      $this->im = imagecreate($this->image_width, $this->image_height);
      $bgcolor = imagecolorallocate($this->im, hexdec(substr($this->image_bg_color, 1, 2)), hexdec(substr($this->image_bg_color, 3, 2)), hexdec(substr($this->image_bg_color, 5, 2)));
    }

    if($this->bgimg != "") { $this->setBackground(); }

    $this->createCode();

    if (!$this->draw_lines_over_text && $this->draw_lines) $this->drawLines();

    $this->drawWord();

    if ($this->arc_linethrough == true) $this->arcLines();

    if ($this->draw_lines_over_text && $this->draw_lines) $this->drawLines();

    $this->output();

  }

  function setBackground()
  {
    $dat = @getimagesize($this->bgimg);
    if($dat == false) { return; }

    switch($dat[2]) {
      case 1:  $newim = @imagecreatefromgif($this->bgimg); break;
      case 2:  $newim = @imagecreatefromjpeg($this->bgimg); break;
      case 3:  $newim = @imagecreatefrompng($this->bgimg); break;
      case 15: $newim = @imagecreatefromwbmp($this->bgimg); break;
      case 16: $newim = @imagecreatefromxbm($this->bgimg); break;
      default: return;
    }

    if(!$newim) return;

    imagecopy($this->im, $newim, 0, 0, 0, 0, $this->image_width, $this->image_height);
  }

  function arcLines()
  {
    $colors = explode(',', $this->arc_line_colors);
    imagesetthickness($this->im, 3);

    $color = $colors[rand(0, sizeof($colors) - 1)];
    $linecolor = imagecolorallocate($this->im, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));

    $xpos   = $this->text_x_start + ($this->font_size * 2) + rand(-5, 5);
    $width  = $this->image_width / 2.66 + rand(3, 10);
    $height = $this->font_size * 2.14 - rand(3, 10);

    if ( rand(0,100) % 2 == 0 ) {
      $start = rand(0,66);
      $ypos  = $this->image_height / 2 - rand(5, 15);
      $xpos += rand(5, 15);
    } else {
      $start = rand(180, 246);
      $ypos  = $this->image_height / 2 + rand(5, 15);
    }

    $end = $start + rand(75, 110);

    imagearc($this->im, $xpos, $ypos, $width, $height, $start, $end, $linecolor);

    $color = $colors[rand(0, sizeof($colors) - 1)];
    $linecolor = imagecolorallocate($this->im, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));

    if ( rand(1,75) % 2 == 0 ) {
      $start = rand(45, 111);
      $ypos  = $this->image_height / 2 - rand(5, 15);
      $xpos += rand(5, 15);
    } else {
      $start = rand(200, 250);
      $ypos  = $this->image_height / 2 + rand(5, 15);
    }

    $end = $start + rand(75, 100);

    imagearc($this->im, $this->image_width * .75, $ypos, $width, $height, $start, $end, $linecolor);
  }

  function drawLines()
  {
    $linecolor = imagecolorallocate($this->im, hexdec(substr($this->line_color, 1, 2)), hexdec(substr($this->line_color, 3, 2)), hexdec(substr($this->line_color, 5, 2)));
    imagesetthickness($this->im, $this->line_thickness);

    for($x = 1; $x < $this->image_width; $x += $this->line_distance) {
      imageline($this->im, $x, 0, $x, $this->image_height, $linecolor);
    }

    for($y = 11; $y < $this->image_height; $y += $this->line_distance) {
      imageline($this->im, 0, $y, $this->image_width, $y, $linecolor);
    }

    if ($this->draw_angled_lines == true) {
      for ($x = -($this->image_height); $x < $this->image_width; $x += $this->line_distance) {
        imageline($this->im, $x, 0, $x + $this->image_height, $this->image_height, $linecolor);
      }

      for ($x = $this->image_width + $this->image_height; $x > 0; $x -= $this->line_distance) {
        imageline($this->im, $x, 0, $x - $this->image_height, $this->image_height, $linecolor);
      }
    }
  }

  function drawWord()
  {
    if ($this->use_gd_font == true) {
      if (!is_int($this->gd_font_file)) {
        $font = @imageloadfont($this->gd_font_file);
        if ($font == false) {
          trigger_error("Failed to load GD Font file {$this->gd_font_file} ", E_USER_WARNING);
          return;
        }
      } else {
        $font = $this->gd_font_file;
      }

      $color = imagecolorallocate($this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)));
      imagestring($this->im, $font, $this->text_x_start, ($this->image_height / 2) - ($this->gd_font_size / 2), $this->code, $color);

    } else { //ttf font
      if($this->use_transparent_text == true) {
        $alpha = intval($this->text_transparency_percentage / 100 * 127);
        $font_color = imagecolorallocatealpha($this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)), $alpha);
      } else {
        $font_color = imagecolorallocate($this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)));
      }

      $x = $this->text_x_start;
      $strlen = strlen($this->code);
      $y_min = ($this->image_height / 2) + ($this->font_size / 2) - 2;
      $y_max = ($this->image_height / 2) + ($this->font_size / 2) + 2;
      $colors = explode(',', $this->multi_text_color);

      for($i = 0; $i < $strlen; ++$i) {
        $angle = rand($this->text_angle_minimum, $this->text_angle_maximum);
        $y = rand($y_min, $y_max);
        if ($this->use_multi_text == true) {
          $idx = rand(0, sizeof($colors) - 1);
          $r = substr($colors[$idx], 1, 2);
          $g = substr($colors[$idx], 3, 2);
          $b = substr($colors[$idx], 5, 2);
          if($this->use_transparent_text == true) {
            $font_color = imagecolorallocatealpha($this->im, "0x$r", "0x$g", "0x$b", $alpha);
          } else {
            $font_color = imagecolorallocate($this->im, "0x$r", "0x$g", "0x$b");
          }
        }
        @imagettftext($this->im, $this->font_size, $angle, $x, $y, $font_color, $this->ttf_file, $this->code{$i});

        $x += rand($this->text_minimum_distance, $this->text_maximum_distance);
      }
    }
  }

  function createCode()
  {
    $this->code = false;

    if ($this->use_wordlist && is_readable($this->wordlist_file)) {
      $this->code = $this->readCodeFromFile();
    }

    if ($this->code == false) {
      $this->code = $this->generateCode($this->code_length);
    }

    $this->saveData();
  }

  function generateCode($len)
  {
    $code = '';

    for($i = 1, $cslen = strlen($this->charset); $i <= $len; ++$i) {
      $code .= strtoupper( $this->charset{rand(0, $cslen - 1)} );
    }
    return $code;
  }

  function readCodeFromFile()
  {
    $fp = @fopen($this->wordlist_file, 'rb');
    if (!$fp) return false;

    $fsize = filesize($this->wordlist_file);
    if ($fsize < 32) return false;

    if ($fsize < 128) {
      $max = $fsize;
    } else {
      $max = 128;
    }

    fseek($fp, rand(0, $fsize - $max), SEEK_SET);
    $data = fread($fp, 128);
    fclose($fp);
    $data = preg_replace("/\r?\n/", "\n", $data);

    $start = strpos($data, "\n", rand(0, 100)) + 1;
    $end   = strpos($data, "\n", $start);

    return strtolower(substr($data, $start, $end - $start)); // return substring in 128 bytes
  }

  function output()
  {
    header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    switch($this->image_type)
    {
      case SI_IMAGE_JPEG:
        header("Content-Type: image/jpeg");
        imagejpeg($this->im, null, 90);
        break;

      case SI_IMAGE_GIF:
        header("Content-Type: image/gif");
        imagegif($this->im);
        break;

      default:
        header("Content-Type: image/png");
        imagepng($this->im);
        break;
    }

    imagedestroy($this->im);
  }

  function getAudibleCode()
  {
    $letters = array();
    $code    = $this->getCode();

    if ($code == '') {
      $this->createCode();
      $code = $this->getCode();
    }

    for($i = 0; $i < strlen($code); ++$i) {
      $letters[] = $code{$i};
    }

    return $this->generateWAV($letters);
  }

  function saveData()
  {
		$result = mysql_query("DELETE FROM ".DB_CAPTCHA." WHERE captcha_ip='".$_SERVER['REMOTE_ADDR']."'");
		$result = mysql_query("INSERT INTO ".DB_CAPTCHA." (captcha_datestamp, captcha_ip, captcha_string) VALUES('".time()."', '".$_SERVER['REMOTE_ADDR']."', '".strtolower($this->code)."')");
  }

  function validate()
  {
		if (preg_check("/^[0-9a-z]+$/", strtolower(trim($this->code_entered)))) {
			$result = dbquery("SELECT * FROM ".DB_CAPTCHA." WHERE captcha_ip='".USER_IP."' AND captcha_string='".strtolower(trim($this->code_entered))."'");
			if (dbrows($result)) {
				$result = dbquery("DELETE FROM ".DB_CAPTCHA." WHERE captcha_ip='".USER_IP."' AND captcha_string='".strtolower(trim($this->code_entered))."'");
				$this->correct_code = true;
			} else {
				$this->correct_code = false;
			}
		} else {
			$this->correct_code = false;
		}
  }

  function getCode()
  {
		$result = mysql_query("SELECT * FROM ".DB_CAPTCHA." WHERE captcha_ip='".$_SERVER['REMOTE_ADDR']."'");
		if (mysql_num_rows($result)) {
			$data = mysql_fetch_assoc($result);
			return $data['captcha_string'];
		} else {
			return "";
		}
  }

  function checkCode()
  {
    return $this->correct_code;
  }

  function generateWAV($letters)
  {
    $first = true;
    $data_len    = 0;
    $files       = array();
    $out_data    = '';

    foreach ($letters as $letter) {
      $filename = $this->audio_path . strtoupper($letter) . '.wav';

      $fp = fopen($filename, 'rb');

      $file = array();

      $data = fread($fp, filesize($filename));

      $header = substr($data, 0, 36);
      $body   = substr($data, 44);


      $data = unpack('NChunkID/VChunkSize/NFormat/NSubChunk1ID/VSubChunk1Size/vAudioFormat/vNumChannels/VSampleRate/VByteRate/vBlockAlign/vBitsPerSample', $header);

      $file['sub_chunk1_id']   = $data['SubChunk1ID'];
      $file['bits_per_sample'] = $data['BitsPerSample'];
      $file['channels']        = $data['NumChannels'];
      $file['format']          = $data['AudioFormat'];
      $file['sample_rate']     = $data['SampleRate'];
      $file['size']            = $data['ChunkSize'] + 8;
      $file['data']            = $body;

      if ( ($p = strpos($file['data'], 'LIST')) !== false) {
        $info         = substr($file['data'], $p + 4, 8);
        $data         = unpack('Vlength/Vjunk', $info);
        $file['data'] = substr($file['data'], 0, $p);
        $file['size'] = $file['size'] - (strlen($file['data']) - $p);
      }

      $files[] = $file;
      $data    = null;
      $header  = null;
      $body    = null;

      $data_len += strlen($file['data']);

      fclose($fp);
    }

    $out_data = '';
    for($i = 0; $i < sizeof($files); ++$i) {
      if ($i == 0) {
        $out_data .= pack('C4VC8', ord('R'), ord('I'), ord('F'), ord('F'), $data_len + 36, ord('W'), ord('A'), ord('V'), ord('E'), ord('f'), ord('m'), ord('t'), ord(' '));

        $out_data .= pack('VvvVVvv',
                          16,
                          $files[$i]['format'],
                          $files[$i]['channels'],
                          $files[$i]['sample_rate'],
                          $files[$i]['sample_rate'] * (($files[$i]['bits_per_sample'] * $files[$i]['channels']) / 8),
                          ($files[$i]['bits_per_sample'] * $files[$i]['channels']) / 8,
                          $files[$i]['bits_per_sample'] );

        $out_data .= pack('C4', ord('d'), ord('a'), ord('t'), ord('a'));

        $out_data .= pack('V', $data_len);
      }

      $out_data .= $files[$i]['data'];
    }

    return $out_data;
  }
}
?>
