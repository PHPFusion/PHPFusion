<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: polls_archive.php
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
require_once "../../maincore.php";
require_once THEMES."templates/header.php";

add_to_title($locale['global_200'].$locale['global_138']);

$result = dbquery("SELECT * FROM ".DB_POLLS." WHERE poll_ended!='0' ORDER BY poll_id DESC");
if (dbrows($result)) {
	$view_list = "";
	while ($data = dbarray($result)) {
		$view_list .= "<option value='".$data['poll_id']."'>".$data['poll_title']."</option>\n";
	}
	opentable($locale['global_138']);
	echo "<div style='text-align:center'>\n";
	echo "<form name='pollsform' method='post' action='".FUSION_SELF."'>\n";
	echo $locale['global_139']."<br />\n";
	echo "<select name='viewpoll_id' class='textbox'>\n".$view_list."</select>\n";
	echo "<input type='submit' name='view' value='".$locale['global_140']."' class='button' />\n";
	echo "</form>\n</div>\n";
	closetable();
} else {
	redirect(BASEDIR."index.php");
}
if (isset($_POST['view']) && (isset($_POST['viewpoll_id']) && isnum($_POST['viewpoll_id']))) {
	$result = dbquery("SELECT * FROM ".DB_POLLS." WHERE poll_id='".$_POST['viewpoll_id']."' AND poll_ended!='0'");
	if (dbrows($result)) {
		$viewpoll_option = array();
		$data = dbarray($result);
		for ($i=0; $i <= 9; $i++) {
			if ($data["poll_opt_".$i]) { $viewpoll_option[$i] = $data["poll_opt_".$i]; }
		}
		$poll_archive = ""; $i = 0; $viewpoll_option_counted = count($viewpoll_option);
		$viewpoll_votes = dbcount("(vote_opt)", DB_POLL_VOTES, "poll_id='".$data['poll_id']."'");
		while ($i < $viewpoll_option_counted) {
			$viewnum_votes = dbcount("(vote_opt)", DB_POLL_VOTES, "vote_opt='$i' AND poll_id='".$data['poll_id']."'");
			$viewopt_votes = ($viewpoll_votes ? number_format(100 / $viewpoll_votes * $viewnum_votes) : 0);
			$poll_archive .= $viewpoll_option[$i]."<br />\n";
			$poll_archive .= "<img src='".get_image("pollbar")."' alt='".$viewpoll_option[$i]."' height='12' width='".$viewopt_votes."%' class='poll' /><br />\n";
			$poll_archive .= $viewopt_votes."% [".$viewnum_votes." ".($viewnum_votes == 1 ? $locale['global_133'] : $locale['global_134'])."]<br />\n";
			if (iADMIN) {
				$result = dbquery(
					"SELECT tp.*,user_id,user_name FROM ".DB_POLL_VOTES." tp
					LEFT JOIN ".DB_USERS." tu ON tp.vote_user=tu.user_id
					WHERE vote_opt='$i' AND poll_id='".$data['poll_id']."'"
				);
				if (dbrows($result)) {
					$a = 1;
					$poll_archive .= "<span class='small2'>";
					while ($data2 = dbarray($result)) {
						$poll_archive .= $data2['user_name'];
						if ($a == dbrows($result)) { $poll_archive .= "<br /><br />\n"; } else { $poll_archive .= ", "; }
						$a++;
					}
					$poll_archive .= "</span>";
				} else {
					$poll_archive .= "<br />\n";
				}
			}
			$i++;
		}
		opentable($locale['global_141']);
		echo "<table align='center' width='200' cellspacing='0' cellpadding='0' class='tbl'>\n<tr>\n";
		echo "<td>".$data['poll_title']."\n<hr />\n".$poll_archive."\n";
		echo "<div style='text-align:center'>\n".$locale['global_135'].$viewpoll_votes."<br />\n";
		echo $locale['global_136'].showdate("shortdate", $data['poll_started'])."<br />\n";
		echo $locale['global_137'].showdate("shortdate", $data['poll_ended'])."\n";
		echo "</div>\n</td>\n</tr>\n</table>\n";
		closetable();
	} else {
		redirect(FUSION_SELF);
	}
}

require_once THEMES."templates/footer.php";
?>