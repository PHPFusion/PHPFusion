<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ratings_include.php
| Author: Nick Jones (Digitanium)
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

include LOCALE.LOCALESET."ratings.php";

function showratings($rating_type, $rating_item_id, $rating_link) {

	global $settings, $locale, $userdata;
	
	if ($settings['ratings_enabled'] == "1") {
		if (iMEMBER) {
			$d_rating = dbarray(dbquery("SELECT rating_vote,rating_datestamp FROM ".DB_RATINGS." WHERE rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."' AND rating_user='".$userdata['user_id']."'"));
			if (isset($_POST['post_rating'])) {
				if (isnum($_POST['rating']) && $_POST['rating'] > 0 && $_POST['rating'] < 6 && !isset($d_rating['rating_vote'])) {
					$result = dbquery("INSERT INTO ".DB_RATINGS." (rating_item_id, rating_type, rating_user, rating_vote, rating_datestamp, rating_ip, rating_ip_type) VALUES ('$rating_item_id', '$rating_type', '".$userdata['user_id']."', '".$_POST['rating']."', '".time()."', '".USER_IP."', '".USER_IP_TYPE."')");
				}
				redirect($rating_link);
			} elseif (isset($_POST['remove_rating'])) {
				$result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='$rating_item_id' AND rating_type='$rating_type' AND rating_user='".$userdata['user_id']."'");
				redirect($rating_link);
			}
		}
		$ratings = array(5 => $locale['r120'], 4 => $locale['r121'], 3 => $locale['r122'], 2 => $locale['r123'], 1 => $locale['r124']);
	
		opentable($locale['r100']);
		if (!iMEMBER) {
			echo "<div style='text-align:center'>".$locale['r104']."</div>\n";
		} elseif (isset($d_rating['rating_vote'])) {
			echo "<div style='text-align:center'>\n";
			echo "<form name='removerating' method='post' action='".$rating_link."'>\n";
			echo sprintf($locale['r105'], $ratings[$d_rating['rating_vote']], showdate("longdate", $d_rating['rating_datestamp']))."<br /><br />\n";
			echo "<input type='submit' name='remove_rating' value='".$locale['r102']."' class='button' />\n";
			echo "</form>\n</div>\n";
		} else {
			echo "<div style='text-align:center'>\n";
			echo "<form name='postrating' method='post' action='".$rating_link."'>\n";
			echo $locale['r106'].": <select name='rating' class='textbox'>\n";
			echo "<option value='0'>".$locale['r107']."</option>\n";
			foreach($ratings as $rating=>$rating_info) {
				echo "<option value='".$rating."'>$rating_info</option>\n";
			}
			echo "</select>\n";
			echo "<input type='submit' name='post_rating' value='".$locale['r103']."' class='button' />\n";
			echo "</form>\n</div>";
		}
		echo "<hr />";
		$tot_votes = dbcount("(rating_item_id)", DB_RATINGS, "rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."'");
		if($tot_votes){
			echo "<table cellpadding='0' cellspacing='1' class='tbl-border center'>\n";
			foreach($ratings as $rating=>$rating_info) {
				$num_votes = dbcount("(rating_item_id)", DB_RATINGS, "rating_item_id='".$rating_item_id."' AND rating_type='".$rating_type."' AND rating_vote='".$rating."'");
				$pct_rating = number_format(100 / $tot_votes * $num_votes);
				if ($num_votes == 0) {
					$votecount = "[".$locale['r108']."]";
				} elseif ($num_votes == 1) {
					$votecount = "[1 ".$locale['r109']."]";
				} else {
					$votecount = "[".$num_votes." ".$locale['r110']."]";
				}
				$class = ($rating % 2==0?"tbl1":"tbl2");
				echo "<tr>\n";
				echo "<td class='$class'>".$rating_info."</td>\n";
				echo "<td width='250' class='$class'><img src='".get_image("pollbar")."' alt='".$rating_info."' height='12' width='".$pct_rating."%' class='poll' /></td>\n";
				echo "<td class='$class'>".$pct_rating."%</td>\n";
				echo "<td class='$class'>".$votecount."</td>\n";
				echo "</tr>\n";
			}
			echo "</table>\n";
		} else {
			echo "<div style='text-align:center'>".$locale['r101']."</div>\n";
		}
		closetable();
	}
}
?>