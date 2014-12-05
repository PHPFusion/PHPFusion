<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: options.php
| Author: Nick Jones (Digitanium)
| Co-author: Grzegorz Lipok (Grzes)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/* No SEO on non-public page */
require_once dirname(__FILE__)."../../maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."forum.php";
if (!isset($_GET['thread_id']) || !isnum($_GET['thread_id'])) {
	redirect("index.php");
}
if (iMEMBER) {
	if (!isset($_GET['forum_id']) || !isnum($_GET['forum_id'])) {
		redirect("index.php");
	}
	$result = dbquery("SELECT f.forum_access, f.forum_moderators, t.thread_id FROM ".DB_THREADS." t
			INNER JOIN ".DB_FORUMS." f ON t.forum_id=f.forum_id
			WHERE t.thread_id='".$_GET['thread_id']."' LIMIT 1");
	if (dbrows($result)) {
		$data = dbarray($result);
		if (!checkgroup($data['forum_access'])) {
			redirect("index.php");
		}
		if (iSUPERADMIN) {
			define("iMOD", TRUE);
		}
		if (!defined("iMOD") && $data['forum_moderators']) {
			$mod_groups = explode(".", $data['forum_moderators']);
			foreach ($mod_groups as $mod_group) {
				if (!defined("iMOD") && checkgroup($mod_group)) {
					define("iMOD", TRUE);
				}
			}
		}
		if (!defined("iMOD")) {
			define("iMOD", FALSE);
		}
	} else {
		redirect("index.php");
	}
} else {
	define("iMOD", FALSE);
}
if (!iMOD) {
	redirect("index.php");
}
if (isset($_POST['step']) && $_POST['step'] != "") {
	$_GET['step'] = $_POST['step'];
}

if (isset($_POST['canceldelete'])) {
	redirect("viewthread.php?forum_id=".$_GET['forum_id']."&thread_id=".$_GET['thread_id']);
}

if (isset($_GET['step']) && $_GET['step'] == "renew") {
	$result = dbquery("SELECT p.post_id, p.post_author, p.post_datestamp FROM ".DB_POSTS." p
		INNER JOIN ".DB_THREADS." t ON p.thread_id=t.thread_id
		WHERE p.thread_id='".$_GET['thread_id']."' AND t.thread_hidden='0' AND p.post_hidden='0'
		ORDER BY p.post_datestamp DESC LIMIT 1");
	if (dbrows($result)) {
		$data = dbarray($result);
		$result = dbquery("UPDATE ".DB_POSTS." SET post_datestamp='".time()."' WHERE post_id='".$data['post_id']."'");
		$result = dbquery("UPDATE ".DB_THREADS." SET thread_lastpost='".time()."', thread_lastpostid='".$data['post_id']."', thread_lastuser='".$data['post_author']."' WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_lastuser='".$data['post_author']."' WHERE forum_id='".$_GET['forum_id']."'");
		opentable($locale['forum_0758']);
		echo "<div style='text-align:center'><br />\n".$locale['forum_0759']."<br /><br />\n";
		echo "<a href='viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='index.php'>".$locale['forum_0703']."</a><br /><br /></div>\n";
		closetable();
	} else {
		redirect("index.php");
	}
} elseif (isset($_GET['step']) && $_GET['step'] == "delete") {
	opentable($locale['forum_0700']);
	echo "<div style='text-align:center'><br />\n";
	if (!isset($_POST['deletethread'])) {
		echo "<form name='delform' method='post' action='".FUSION_SELF."?step=delete&amp;forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']."'>\n";
		echo $locale['forum_0704']."<br /><br />\n";
		echo "<input type='submit' name='deletethread' value='".$locale['yes']."' class='button' style='width:75px'>\n";
		echo "<input type='submit' name='canceldelete' value='".$locale['no']."' class='button' style='width:75px'><br /><br />\n";
		echo "</form>\n";
	} else {
		$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."' GROUP BY post_author");
		if (dbrows($result)) {
			while ($pdata = dbarray($result)) {
				$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts-".$pdata['num_posts']." WHERE user_id='".$pdata['post_author']."'");
			}
		}
		$tdata = dbarray(dbquery("SELECT thread_id,thread_lastpost,thread_lastuser FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."'"));
		$threads_count = dbcount("(forum_id)", DB_THREADS, "forum_id='".$_GET['forum_id']."'")-1;
		$result = dbquery("DELETE FROM ".DB_POSTS." WHERE thread_id='".$_GET['thread_id']."'");
		$del_posts = mysql_affected_rows();
		$result = dbquery("DELETE FROM ".DB_THREADS." WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("DELETE FROM ".DB_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("SELECT attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."'");
		if (dbrows($result) != 0) {
			while ($attach = dbarray($result)) {
				@unlink(FORUM."attachments/".$attach['attach_name']);
			}
		}
		$result = dbquery("DELETE FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$_GET['thread_id']."'");
		if ($threads_count > 0) {
			$result = dbquery("SELECT forum_id FROM ".DB_FORUMS." WHERE forum_id='".$_GET['forum_id']."' AND forum_lastpost='".$tdata['thread_lastpost']."' AND forum_lastuser='".$tdata['thread_lastuser']."'");
			if (dbrows($result)) {
				$result = dbquery("SELECT p.forum_id, p.post_author, p.post_datestamp FROM ".DB_POSTS." p
					INNER JOIN ".DB_THREADS." t ON p.thread_id=t.thread_id
					WHERE p.forum_id='".$_GET['forum_id']."' AND t.thread_hidden='0' AND p.post_hidden='0'
					ORDER BY p.post_datestamp DESC LIMIT 1");
				$pdata = dbarray($result);
				$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$pdata['post_datestamp']."', forum_postcount=forum_postcount-".$del_posts.", forum_threadcount=forum_threadcount-1, forum_lastuser='".$pdata['post_author']."' WHERE forum_id='".$_GET['forum_id']."'");
			}
		} else {
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_postcount=0, forum_threadcount=0, forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
		}
		echo $locale['forum_0701']."<br /><br />\n";
		echo "<a href='viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
		echo "<a href='index.php'>".$locale['forum_0703']."</a><br /><br />\n";
	}
	echo "</div>\n";
	closetable();
} elseif (isset($_GET['step']) && $_GET['step'] == "lock") {
	$result = dbquery("UPDATE ".DB_THREADS." SET thread_locked='1' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
	opentable($locale['forum_0710']);
	echo "<div class='well' style='text-align:center'><br />\n<strong>".$locale['forum_0711']."</strong><br /><br />\n";
	echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
	echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
	closetable();
} elseif (isset($_GET['step']) && $_GET['step'] == "unlock") {
	$result = dbquery("UPDATE ".DB_THREADS." SET thread_locked='0' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
	opentable($locale['forum_0720']);
	echo "<div class='well' style='text-align:center'><br />\n<strong>".$locale['forum_0721']."</strong><br /><br />\n";
	echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
	echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
	closetable();
} elseif (isset($_GET['step']) && $_GET['step'] == "sticky") {
	$result = dbquery("UPDATE ".DB_THREADS." SET thread_sticky='1' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
	opentable($locale['forum_0730']);
	echo "<div class='well' style='text-align:center'><br />\n<strong>".$locale['forum_0731']."</strong><br /><br />\n";
	echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
	echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
	closetable();
} elseif (isset($_GET['step']) && $_GET['step'] == "nonsticky") {
	$result = dbquery("UPDATE ".DB_THREADS." SET thread_sticky='0' WHERE thread_id='".$_GET['thread_id']."' AND thread_hidden='0'");
	opentable($locale['forum_0740']);
	echo "<div class='well' style='text-align:center'><br /><strong>".$locale['forum_0741']."</strong><br /><br />\n";
	echo "<a href='".FORUM."viewforum.php?forum_id=".$_GET['forum_id']."'>".$locale['forum_0702']."</a><br /><br />\n";
	echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br /></div>\n";
	closetable();
} elseif (isset($_GET['step']) && $_GET['step'] == "move") {
	opentable($locale['forum_0750']);
	if (isset($_POST['move_thread'])) {

		if (!isset($_POST['new_forum_id']) || !isnum($_POST['new_forum_id'])) {
			redirect("index.php");
		}
		if (!dbcount("(forum_id)", DB_FORUMS, "forum_id='".$_POST['new_forum_id']."' AND ".groupaccess('forum_access'))) {
			redirect("../index.php");
		}
		if (!dbcount("(thread_id)", DB_THREADS, "thread_id='".$_GET['thread_id']."' AND thread_hidden='0'")) {
			redirect("../index.php");
		}
		$result = dbquery("UPDATE ".DB_THREADS." SET forum_id='".$_POST['new_forum_id']."' WHERE thread_id='".$_GET['thread_id']."'");
		$result = dbquery("UPDATE ".DB_POSTS." SET forum_id='".$_POST['new_forum_id']."' WHERE thread_id='".$_GET['thread_id']."'");
		$post_count = dbcount("(post_id)", DB_POSTS, "thread_id='".$_GET['thread_id']."'");
		$result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_THREADS." WHERE forum_id='".$_GET['forum_id']."' AND thread_hidden='0' ORDER BY thread_lastpost DESC LIMIT 1");
		if (dbrows($result)) {
			$pdata2 = dbarray($result);
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$pdata2['thread_lastpost']."', forum_postcount=forum_postcount-".$post_count.", forum_threadcount=forum_threadcount-1, forum_lastuser='".$pdata2['thread_lastuser']."' WHERE forum_id='".$_GET['forum_id']."'");
		} else {
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_postcount=forum_postcount-".$post_count.", forum_threadcount=forum_threadcount-1, forum_lastuser='0' WHERE forum_id='".$_GET['forum_id']."'");
		}
		$result = dbquery("SELECT thread_lastpost, thread_lastuser FROM ".DB_THREADS." WHERE forum_id='".$_POST['new_forum_id']."' AND thread_hidden='0' ORDER BY thread_lastpost DESC LIMIT 1");
		if (dbrows($result)) {
			$pdata2 = dbarray($result);
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".$pdata2['thread_lastpost']."', forum_postcount=forum_postcount+".$post_count.", forum_threadcount=forum_threadcount+1, forum_lastuser='".$pdata2['thread_lastuser']."' WHERE forum_id='".$_POST['new_forum_id']."'");
		} else {
			$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='0', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+".$post_count.", forum_lastuser='0' WHERE forum_id='".$_POST['new_forum_id']."'");
		}
		echo "<div class='well' style='text-align:center'><br />\n<strong>".$locale['forum_0752']."</strong><br /><br />\n";
		echo "<a href='".FORUM."index.php'>".$locale['forum_0703']."</a><br /><br />\n</div>\n";
	} else {
		if (!defined("SELECT2")) {
			define("SELECT2", TRUE);
			add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
			add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
		}
		$forum_list = array();
		$current_cat = "";
		$result2 = dbquery("SELECT f.forum_id, f.forum_name, f2.forum_id AS forum_cat_id, f2.forum_name AS forum_cat_name
                    FROM ".DB_FORUMS." f
                    INNER JOIN ".DB_FORUMS." f2 ON f.forum_cat=f2.forum_id
                    WHERE ".groupaccess('f.forum_access')." AND f.forum_cat!='0' ORDER BY f2.forum_order ASC, f.forum_order ASC");
		// group.

		while ($data2 = dbarray($result2)) {
			// first, we sort the items out into parent-child array.
			if ($data2['forum_cat_name'] != $current_cat) {
				$forum_list[$data2['forum_cat_id']] = array('text' => $data2['forum_cat_name']);
				$forum_list[$data2['forum_cat_id']]['children'][] = array('id' => $data2['forum_id'],
																		  'text' => $data2['forum_name']);
			} else {
				$forum_list[$data2['forum_cat_id']]['children'][] = array('id' => $data2['forum_id'],
																		  'text' => $data2['forum_name']);
			}
			$current_cat = $data2['forum_cat_name'];
		}
		//print_p($forum_list);
		// next json encode every array into a single string
		$forum_opts = '';
		$i = 0;
		foreach ($forum_list as $array) {
			$forum_opts .= ($i == count($forum_list)-1) ? json_encode($array) : json_encode($array).",";
			$i++;
		}

		add_to_jquery("
		var this_data = [];
		this_data.push($forum_opts);
		$('#new_forum_id').select2({
		placeholder: '".$locale['choose']."',
		data : this_data
		});
    	");
		echo openform('moveform', 'moveform', 'post', FUSION_SELF."?step=move&forum_id=".$_GET['forum_id']."&amp;thread_id=".$_GET['thread_id']);
		echo "<div class='panel panel-default'>\n";
		echo "<div class='panel-body'>\n<br/>\n";
		echo form_hidden($locale['forum_0751'], 'new_forum_id', 'new_forum_id', '', array('title'=>1, 'inline'=>1));
		echo form_button($locale['forum_0750'], 'move_thread', 'move_thread', $locale['forum_0750'], array('class'=>'btn-primary flright pull-right col-sm-offset-3 col-md-offset-3 col-lg-offset-3'));
		echo "</div>\n</div\n";
		echo closeform();
	}
	closetable();
} else {
	redirect("index.php");
}

require_once THEMES."templates/footer.php";
?>