<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: migrate.php
| Author: Frederick Chan MC (Hien)
| Co-Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";
pageAccess('MI');
if (isset($_POST['user_primary']) && !isnum($_POST['user_primary'])) die("Denied");
if (isset($_POST['user_migrate']) && !isnum($_POST['user_migrate'])) die("Denied");

add_breadcrumb(array('link'=>ADMIN.'blog.php'.$aidlink, 'title'=>'Migration Tool'));

$settings = fusion_get_settings();

if (isset($_POST['migrate'])) {
	$user_primary_id = stripinput($_POST['user_primary']);
	$user_temp_id = stripinput($_POST['user_migrate']);
	if ($user_primary_id == $user_temp_id) {
		echo "<div class='well text-center'>Cannot Replicate the Same User.</div>\n";
	} else {
		$result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id='$user_primary_id'");
		if (dbrows($result)>0) {
			$result2 = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id='$user_temp_id'");
			if (dbrows($result2)>0) {
				/* Start Execution */
				if (isset($_POST['forum']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREAD_NOTIFY, 'notify_user', 'Forum Tracked Threads');
					user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREADS, 'thread_author', 'Forum Threads');
					user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_POSTS, 'post_author', 'Forum Posts');
				//Delete votes
				$result = dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id='".$user_temp_id."'"); 
				// Update thread last post author
				dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastuser='".$user_primary_id."'	WHERE thread_lastuser='".$user_temp_id."'");
				//Update forums post count
				$result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
				if (dbrows($result)) {
					while ($data = dbarray($result)) {
						$result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
					}
				}
				}
				if (isset($_POST['comments'])== '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_COMMENTS, 'comment_name', 'Comments');
				}
				if (isset($_POST['ratings']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_RATINGS, 'rating_user', 'Ratings');
				}
				if (isset($_POST['shoutbox']) == '1') {
					$result = dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='shoutbox_panel'");
					if ($result >0) {
						require_once INFUSIONS."shoutbox_panel/infusion_db.php";
						user_posts_migrate($user_primary_id, $user_temp_id, DB_SHOUTBOX, 'shout_name', 'Shoutbox');
					}
				}
				if (isset($_POST['messages']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'user_id', 'Messages ( Need checks )');
					// Delete messages options
					$result = dbquery("DELETE FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='".$user_temp_id."'"); 
					}
				if (isset($_POST['articles']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_ARTICLES, 'article_name', 'Articles');
				}
				if (isset($_POST['news']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_NEWS, 'news_name', 'News');
				}
				if (isset($_POST['blog']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_BLOG, 'blog_name', 'Blog');
				}
				if (isset($_POST['downloads']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_DOWNLOADS, 'download_user', 'Downloads');
				}
				if (isset($_POST['photos']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_PHOTOS, 'photo_user', 'Photo');
				}
				if (isset($_POST['votes']) == '1') {
					user_posts_migrate($user_primary_id, $user_temp_id, DB_POLL_VOTES, 'vote_user', 'Poll Votes');
				}
				if (isset($_POST['user_level']) == '1') {
					user_rights_migrate($user_primary_id, $user_temp_id);
				}
				if (isset($_POST['del_user']) == '1') {
					//$result = dbquery("DELETE FROM ".DB_USERS." WHERE user_id='$user_temp_id'");
				} else {
					// suspend target user and create a system log.
					require_once INCLUDES."suspend_include.php";
//					$result = dbquery("UPDATE ".DB_USERS." SET user_status='7' WHERE user_id='$user_temp_id'");
//					suspend_log($user_temp_id, '7', "Migrated User");
				}
			} else {
				echo "<div class='well text-center'>Migrate User did not exist.</div>\n";
			}
		} else {
			echo "<div class='well text-center'>Primary User did not Exist.</div>\n";
		}
	}
}

opentable("User Migrate Console");
user_posts_migrate_console();
closetable();

function user_posts_migrate_console() {
global $aidlink,$locale;

//print_r($_POST);
$result = dbquery("SELECT user_id, user_name FROM ".DB_USERS."");
if (dbrows($result)>0) {
	while ($user_data = dbarray($result)) {
		$data[$user_data['user_id']] = "".$user_data['user_name']."";
	}
} else {
	$data['0'] = "No Users Exists.";
}
//echo "<form name='$aidlink' method='post' action='".FUSION_SELF.$aidlink."'>\n";
echo openform('inputform', 'post', "".FUSION_SELF.$aidlink."", array('max_tokens' => 1));
echo "<table style='width:100%' class='table table-striped'>\n";
echo "<thead>\n";
echo "<tr style='height:30px;'><th style='width:33%; text-align:left'>Select Primary Account</th><th style='width:33%; text-align:left;'>Migrate From Account</th><th class='text-left'>&nbsp;</th>\n</tr>\n";
echo "</thead>\n";
echo "<tbody>\n";
echo "<tr>\n";
echo "<td>\n";
echo form_user_select('user_primary', '', isset($_POST['user_primary']) && isnum($_POST['user_primary'] ? : ''), array('placeholder' => 'Select User'));
echo "</td>\n";
echo "<td>\n";
echo form_user_select('user_migrate', '', isset($_POST['user_migrate']) && isnum($_POST['user_migrate'] ? : ''), array('placeholder' => 'Select User'));
echo "</td>\n";
echo "<td>\n";
echo form_button('migrate', 'Migrate Account', 'Migrate Account', array('inline' => '1', 'class' => 'btn btn-sm btn-primary'));
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>Move Items Posts</td>";
echo "<td colspan='2'>\n";
echo "<input type='checkbox' name='forum' value='1' ".(isset($_POST['forum']) == '1' ? 'checked' : '')."> <small>Move Forum Posts</small><br/>\n"; // cat, notify, and thread
echo "<input type='checkbox' name='comments' value='1' ".(isset($_POST['comments']) == '1' ? 'checked' : '')."> <small>Move Comments Posts</small><br/>";
echo "<input type='checkbox' name='ratings' value='1' ".(isset($_POST['ratings']) == '1' ? 'checked' : '')."> <small>Move Ratings Posts</small><br/>";
$shoutbox = dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='shoutbox_panel'");
if ($shoutbox >0) {
echo "<input type='checkbox' name='shoutbox' value='1' ".(isset($_POST['shoutbox']) == '1' ? 'checked' : '')."> <small>Move Shoutbox Posts</small><br/>";
}
echo "<input type='checkbox' name='messages' value='1' ".(isset($_POST['messages']) == '1' ? 'checked' : '')."> <small>Move Messages ( ViP )</small><br/>";
echo "<input type='checkbox' name='articles' value='1' ".(isset($_POST['articles']) == '1' ? 'checked' : '')."> <small>Move Articles Posts</small><br/>";
echo "<input type='checkbox' name='news' value='1' ".(isset($_POST['news']) == '1' ? 'checked' : '')."> <small>Move News Posts</small><br/>";
echo "<input type='checkbox' name='blog' value='1' ".(isset($_POST['blog']) == '1' ? 'checked' : '')."> <small>Move Blogs</small><br/>";
echo "<input type='checkbox' name='downloads' value='1' ".(isset($_POST['downloads']) == '1' ? 'checked' : '')."> <small>Move Downloads Posts</small><br/>";
echo "<input type='checkbox' name='photos' value='1' ".(isset($_POST['photos']) == '1' ? 'checked' : '')."> <small>Move Photos Posts</small><br/>"; // photo album and photo-user
echo "<input type='checkbox' name='votes' value='1' ".(isset($_POST['votes']) == '1' ? 'checked' : '')."> <small>Move Votes Posts</small><br/>";
echo "<input type='checkbox' name='user_level' value='1' ".(isset($_POST['user_level']) == '1' ? 'checked' : '')."> <small>Move User Level and Permissions</small><br/>";
echo "</td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>Delete User After?</td>";
echo "<td colspan='3'>\n";
echo "<input type='checkbox' name='del_user' value='1'> <small>Delete Migrated User After Migrating Posts (Not Recommended)</small><br/>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</tbody>\n";
echo "</table>\n";
echo closeform();
}

function user_posts_migrate($user_primary_id, $user_temp_id, $db, $user_column, $name) {
$users = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='$user_temp_id'"));
$p_user = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='$user_primary_id'"));
$rows = dbcount("($user_column)", $db, "$user_column='$user_temp_id'");
if (($rows)>0) {
	$result = dbquery("UPDATE ".$db." SET $user_column='$user_primary_id' WHERE $user_column='$user_temp_id'");
	if (!$result) {
		echo "<div class='well text-center'>Result are not updated due to a SQL error.</div>";
	} else {
		echo "<div class='well text-center'>$rows ".($rows > 1 ? 'records' : 'record')." has been copied on <strong>$name</strong> from User ".$users['user_name']."  to User ".$p_user['user_name'].".</div>";
	}
	} else {
		echo "<div class='well text-center'>\nNo records is found on <strong>$name</strong> to be copied to primary account.</div>\n";
	}
}

function user_rights_migrate($user_primary_id, $user_temp_id) {
$result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='$user_temp_id'");
if (dbrows($result)>0) {
	$data = dbarray($result);
	$result2 = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='$user_primary_id'");
	if (dbrows($result2)>0) {
		$cdata = dbarray($result2);
		// Copy User Rights
		$old_user_rights = explode(".", $data['user_rights']); // array
		$new_user_rights = explode(".", $cdata['user_rights']); // array
		if (is_array($old_user_rights)) { // something on old user.
			if (empty($new_user_rights['0'])) { // nothing on new user and something on old user, so we just copy the whole thing over.
				$result = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights']."' WHERE user_id='$user_primary_id'");
				if (!$result) {
					echo "<div class='well text-center'>Access rights result are not updated due to a SQL error.</div>\n";
				} else {
					echo "<div class='well text-center'>".count($old_user_rights)." new <strong>access rights</strong> are updated from User ".$data['user_name']." to User ".$cdata['user_name'].".</div>\n";
				}
			} else {
				// both user have rights, need to compre...
				$rights_dump = array();
				foreach($old_user_rights as $arr=>$value) {
					// compare and create a var dump
					if (!in_array($value, $new_user_rights)) {
						$rights_dump[] = $value;
					}
				}
				$new_rights = array_merge($rights_dump, $new_user_rights);
				$rights = implode($new_rights, '.');
				$result = dbquery("UPDATE ".DB_USERS." SET user_rights='$rights' WHERE user_id='$user_primary_id'");
				if (!$result) {
					echo "<div class='well text-center'>Result are not updated due to a SQL error.</div>\n";
				} else {
					echo "<div class='well text-center'>".count($rights_dump)." new <strong>access rights</strong> are updated from User ".$data['user_name']." to User ".$cdata['user_name'].".</div>\n";
				}
			}
		} // no need else as nothing to copy.

		// Next, we need to copy user groups.
		$old_user_groups = explode(".", $data['user_groups']);
		$new_user_groups = explode(".", $data['user_groups']);
		if (is_array($old_user_groups)) { // something on old user.
			if (empty($new_user_groups['0'])) { // nothing on new user and something on old user, so we just copy the whole thing over.
				$result = dbquery("UPDATE ".DB_USERS." SET user_groups='".$data['user_groups']."' WHERE user_id='$user_primary_id'");
				if (!$result) {
					echo "<div class='well text-center'>User Groups result are not updated due to a SQL error.</div>\n";
				} else {
					echo "<div class='well text-center'>".count($old_user_groups)." new <strong>user groups</strong> are updated from User ".$data['user_name']." to User ".$cdata['user_name'].".</div>\n";
				}
			} else {
				// both user have groups, need to compre...
				$group_dump = array();
				foreach($old_user_groups as $arr=>$value) {
					// compare and create a var dump
					if (!in_array( $value , $new_user_groups )) {
						$group_dump[] = $value;
					}
				}
				$new_group = array_merge($group_dump, $new_user_groups);
				$groups = implode($new_group, '.');
				$result = dbquery("UPDATE ".DB_USERS." SET user_groups='$groups' WHERE user_id='$user_primary_id'");
				if (!$result) {
					echo "<div class='well text-center'>User Groups result are not updated due to a SQL error.</div>\n";
				} else {
					echo "<div class='well text-center'>".count($group_dump)." new <strong>user group</strong> are updated from User ".$data['user_name']." to User ".$cdata['user_name'].".</div>\n";
				}
			}
		} // no need else as nothing to copy.

		// user level compare
		if ($data['user_level'] > $cdata['user_level']) {
			$result = dbquery("UPDATE ".DB_USERS." SET user_level='".$data['user_level']."' WHERE user_id='$user_primary_id'");
			if (!$result) {
				echo "<div class='well text-center'>User Level are not updated due to a SQL error.</div>\n";
			} else {
				echo "<div class='well text-center'><strong>New User Level - ".$data['user_level']."</strong> are updated from User ".$data['user_name']." to User ".$cdata['user_name'].".</div>\n";
			}
		} else {
			echo "<div class='well text-center'>User Level are not updated because they are similar or less than the primary user account.</div>\n";
		}
	} else {
		// primary user must be found.
		echo "<div class='well text-center'>There are no user with ID $user_primary_id.</div>\n";
	}
	} else {
		// target user must be found.
		echo "<div class='well text-center'>There are no user with ID $user_temp_id.</div>\n";
	}
}
require_once THEMES."templates/footer.php";
?>