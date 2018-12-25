<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

/*
Experimental Anti Bot Gateway that combine multiple methods to prevent auto bots.
*/
if (!session_status() == PHP_SESSION_ACTIVE) {
	session_start();
}

require_once BASEDIR."gateway/constants_include.php";
require_once BASEDIR."gateway/functions_include.php";

// Terminate and ban all excessive access atempts.
antiflood_countaccess();

// Flag for pass, just increment on amount of checks we add.
$multiplier = "0";
$reply_method = "";

// Don´t run twice
if (!isset($_POST['gateway_submit']) && !isset($_POST['Register']) && $_SESSION["validated"] !== "True") {

	$_SESSION["validated"] = "False";
	
	// Get some numbers up
	$a = rand(11, 20);
	$b = rand(1, 11);
	
	if ($a > 15) { 
		$antibot = intval($a+$b);
		$multiplier = "+";
		$reply_method = $locale['gateway_062'];
		$a = convertNumberToWord($a);
		$antibot = convertNumberToWord($antibot);
		$antibot = preg_replace('/\s+/', '', $antibot);
		$_SESSION["antibot"] = strtolower($antibot);
	} else {
		$antibot = intval($a-$b);
		$multiplier = "-";
		$reply_method = $locale['gateway_063'];
		$_SESSION["antibot"] = intval($antibot);
		$b = convertNumberToWord($b);
	}
		
	$a = str_rot47($a);
	$b = str_rot47($b);

	echo '<script type="text/javascript">

			var first = $a;
			var second = $b;

			function decode(x){
			 var s="";
			 for(var i=0;i<x.length;i++){
			  var j=x.charCodeAt(i);
			  if((j>=33)&&(j<=126)){
			   s+=String.fromCharCode(33+((j+14)%94));
			  } else {
			   s+=String.fromCharCode(j);
			  }
			 }
			 return s;
			};
	</script>';
	echo "<noscript>".$locale['gateway_052']."</noscript>";
	
	// Just add fields to random
	$honeypot_array = array();
	$honeypot_array = array($locale['gateway_053'], $locale['gateway_054'], $locale['gateway_055'], $locale['gateway_056'], $locale['gateway_057'], $locale['gateway_058'], $locale['gateway_059']);
	shuffle($honeypot_array); 
	$_SESSION["honeypot"] = $honeypot_array[3];

	opentable($locale['gateway_069']);
	echo "<form name='Fusion_Gateway' method='post' action='".FORM_REQUEST."' />";
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>";
	echo '<td width="1%" class="tbl1" style="text-align:center;white-space:nowrap"><strong>';
	// Try this first, JS Rot47 Encryption etc..
	echo '<script type="text/javascript">
		document.write("<h3>'.$locale['gateway_060'].' " + decode("'.$a.'") + " '.$multiplier.' " + decode("'.$b.'") + " '.$locale['gateway_061'].' '.$reply_method.'</h3>");
	</script>';
    echo '</td></tr>';
	echo "<tr><td width='1%' class='tbl2' style='text-align:center;white-space:nowrap'><strong><input type='text' style='text-align:center; width:300px;' onblur='if (this.value == \"\") {this.value = \"".$locale['gateway_064']."...\";}' onfocus='if (this.value == \"".$locale['gateway_064']."...\") {this.value = \"\";}' id='gateway_answer' name='gateway_answer' value='".$locale['gateway_064']."...' class='textbox' /></td></tr>";
	echo '<input type="hidden" name="'.$honeypot_array[3].'" />';
	echo '<tr><td width="1%" class="tbl1" style="text-align:center;white-space:nowrap"><input type="submit" name="gateway_submit" value="'.$locale['gateway_065'].'" class="'.($settings["bootstrap"] || defined("BOOTSTRAP") ? "button btn-primary m-t-10" : "button").'" />';
	echo "</table>";
	echo '</form>';
	closetable();
} 

if (isset($_POST['gateway_answer'])) {

	if (isset($_SESSION["honeypot"])) {
		$honeypot = $_SESSION["honeypot"];
	}
	
// if the honeypot is empty, run rest of the verify script	
	if (isset($_POST["$honeypot"]) && $_POST["$honeypot"] == "") {
		$antibot = stripinput(strtolower($_POST["gateway_answer"]));
		
		if (isset($_SESSION["antibot"])) {
			if ($_SESSION["antibot"] == $antibot){
				$_SESSION["validated"] = "True";
			} else {
				echo "<div class='well text-center'><h3>".$locale['gateway_066']."</h3></div>";
				echo "<input type='button' value='".$locale['gateway_067']."'    class='".($settings['bootstrap'] || defined('BOOTSTRAP') ? 'text-center btn btn-info spacer-xs' : 'button')."' onclick=\"location='".BASEDIR."register.php'\">";
				$_SESSION["validated"] = "False";
			}
		} else {
			$_SESSION["validated"] = "False";
		}
	}
}