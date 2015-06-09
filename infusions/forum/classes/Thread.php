<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Thread.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums;

/**
* @property string sortCol
*/

class Thread {
private $edit_reason = false;
private $first_post_id = 0;
private $last_post_id = 0;
private $post_count = 0;
private $imagetypes = array(".bmp", ".gif", ".iff", ".jpg", ".jpeg", ".png", ".psd", ".tiff", ".wbmp");
private $forum_index = array();
private $thread_info = array();
private $sortCol;

/**
 * @return array
 */
public function getThreadInfo() {
	return $this->thread_info;
}

public function setThreadInfo() {
	global $locale;
	self::prepare_ThreadInfo();
	// safe from this point onwards
	add_to_title($locale['global_200'].$locale['forum_0000']);
	$thread_data = $this->thread_info['thread'];
	if (!empty($thread_data)) {
		switch($this->thread_info['section']) {
			case 'oldest':
				$this->sortCol = 'post_datestamp ASC';
				break;
			case 'latest':
				$this->sortCol = 'post_datestamp DESC';
				break;
			case 'high':
				$this->sortCol = 'vote_points DESC';
				break;
			default:
				$this->sortCol = 'post_datestamp ASC';
		}
		$this->forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
		add_to_title($locale['global_201'].$thread_data['thread_subject']);
		add_breadcrumb(array('link'=>INFUSIONS.'forum/index.php', 'title'=>$locale['forum_0000']));
		forum_breadcrumbs($this->forum_index); 
		add_breadcrumb(array('link' => INFUSIONS.'forum/viewthread.php?forum_id='.$thread_data['forum_id'].'&amp;thread_id='.$thread_data['thread_id'], 'title' => $thread_data['thread_subject']));
		$this->thread_info['thread']['forum_link'] = INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$thread_data['forum_id']."&amp;forum_cat=".$thread_data['forum_cat']."&amp;forum_branch=".$thread_data['forum_branch'];
		self::set_ThreadPermissions();
		self::set_ThreadFilterlinks();
		self::increment_threadview();
		self::set_ThreadMods();
		self::set_Quickreply();
		self::set_ThreadButtons();
		self::set_ThreadPolls();
		self::set_ThreadAttach();
		self::set_PostInfo();
		self::set_ThreadJs();
		// execute in the end.
		self::set_ForumPostDB();
	}
}

/**
 * Set user viewing this thread
 * can know who is visiting here.
 */
private function set_thread_visitor() {
	global $userdata;
	if (iMEMBER) {
		$thread_match = $this->thread_info['thread_id']."\|".$this->thread_info['thread']['thread_lastpost']."\|".$this->thread_info['thread']['forum_id'];
		if (($this->thread_info['thread']['thread_lastpost'] > $this->thread_info['lastvisited']) && !preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads'])) {
			dbquery("UPDATE ".DB_USERS." SET user_threads='".$userdata['user_threads'].".".stripslashes($thread_match)."' WHERE user_id='".$userdata['user_id']."'");
		}
	}
}

/**
 * Increment Views based on Session.
 */
private function increment_threadview() {
	if (!isset($_SESSION['thread'][$this->thread_info['thread']['thread_id']])) {
		$_SESSION['thread'][$this->thread_info['thread']['thread_id']] = time();
		dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_views=thread_views+1 WHERE thread_id='".$this->thread_info['thread_id']."'");
	} else {
		$days_to_keep_session = 30;
		$time = $_SESSION['thread'][$this->thread_info['thread']['thread_id']];
		if ($time <= time()-($days_to_keep_session*3600*24)) unset($_SESSION['thread'][$this->thread_info['thread']['thread_id']]);
	}
}

/**
 * Permissions Access
 */
private function set_ThreadPermissions() {
	global $settings;
	$this->thread_info['permissions'] = array(
		'edit_lock' => $settings['forum_edit_lock'] ? 1 : 0,
		'can_post'=> iMOD or iSUPERADMIN ? 1 : (checkgroup($this->thread_info['thread']['forum_post']) && checkgroup($this->thread_info['thread']['forum_lock'])) ? 1 : 0,
		'can_reply' => iMOD or iSUPERADMIN ? 1 : (checkgroup($this->thread_info['thread']['forum_post']) && checkgroup($this->thread_info['thread']['forum_reply']) && !$this->thread_info['thread']['forum_lock']) ? 1 : 0,
		'can_rate' => ($this->thread_info['thread']['forum_type'] == 4 && ((iMOD or iSUPERADMIN) or ($this->thread_info['thread']['forum_allow_ratings'] && checkgroup($this->thread_info['thread']['forum_post_ratings']) && !$this->thread_info['thread']['forum_lock']))) ? 1 : 0,
		'can_view_poll' => checkgroup($this->thread_info['thread']['forum_poll']) ? 1 : 0,
		'can_vote_poll' => checkgroup($this->thread_info['thread']['forum_vote']) ? 1 : 0,
		'can_download_attach' => checkgroup($this->thread_info['thread']['forum_attach_download']) ? 1 : 0,
	);
}

private function set_ThreadJs() {
	global $locale;
	//javascript to footer
	$highlight_js = ""; $colorbox_js = ""; $edit_reason_js = '';
	/** javascript **/

	// highlight jQuery plugin
	if (isset($_GET['highlight'])) {
		$words = explode(" ", urldecode($_GET['highlight']));
		$higlight = "";
		$i = 1;
		$c_words = count($words);
		foreach ($words as $hlight) {
			$hlight = htmlentities($hlight, ENT_QUOTES);
			$higlight .= "'".$hlight."'";
			$higlight .= ($i < $c_words ? "," : "");
			$i++;
		}
		add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.highlight.js'></script>");
		$highlight_js .= "jQuery('.search_result').highlight([".$higlight."],{wordsOnly:true});";
		$highlight_js .= "jQuery('.highlight').css({backgroundColor:'#FFFF88'});"; //better via theme or settings
	}

	if ($this->edit_reason) {
		$edit_reason_js .="
		$('.reason_div').hide();
		$('div').find('.reason_button').css({cursor: 'pointer' });
		$('.reason_button').bind('click', function(e) {
			var target = $(this).data('target');
			$('#'+target).stop().slideToggle('fast');
		});
		";
	}

	// viewthread javascript, moved to footer
	$viewthread_js = "<script type='text/javascript'>";
	$viewthread_js .= "/*<![CDATA[*/";
	$viewthread_js .= "jQuery(document).ready(function(){";
	if (!empty($highlight_js) || !empty($colorbox_js) || !empty($edit_reason_js)) {
		$viewthread_js .= $highlight_js.$colorbox_js.$edit_reason_js;
	}
	$viewthread_js .= "jQuery('a[href=#top]').click(function(){";
	$viewthread_js .= "jQuery('html, body').animate({scrollTop:0}, 'slow');";
	$viewthread_js .= "return false;";
	$viewthread_js .= "});";
	$viewthread_js .= "});";
	// below functions could be made more unobtrusive thanks to jQuery, giving a more accessible cms
	$viewthread_js .= "function jumpforum(forum_id){";
	$viewthread_js .= "document.location.href='".INFUSIONS."forum/viewforum.php?forum_id='+forum_id;";
	$viewthread_js .= "}";
	if (iMOD) { // only moderators need this javascript
		$viewthread_js .= "function setChecked(frmName,chkName,val){";
		$viewthread_js .= "dml=document.forms[frmName];";
		$viewthread_js .= "len=dml.elements.length;";
		$viewthread_js .= "for(i=0;i<len;i++){";
		$viewthread_js .= "if(dml.elements[i].name==chkName){";
		$viewthread_js .= "dml.elements[i].checked=val;";
		$viewthread_js .= "}";
		$viewthread_js .= "}";
		$viewthread_js .= "}";
	}
	$viewthread_js .= "/*]]>*/";
	$viewthread_js .= "</script>";
	add_to_footer($viewthread_js);
}

/**
 * Moderator Options Form
 */
private function set_ThreadMods() {
	global $locale, $settings;
	if (iMOD) {
		// need to wrap with issets?
		$moderator = new Moderator();
		$moderator->setForumId($this->thread_info['forum_id']);
		$moderator->setThreadId($this->thread_info['thread']['thread_id']);
		$moderator->set_modActions();

		/**
		 * Thread moderation options
		 */
		$this->thread_info['mod_options'] = array(
											'renew' => $locale['forum_0207'],
											'delete' => $locale['forum_0201'],
											$this->thread_info['thread']['thread_locked'] ? "unlock" : "lock" => $this->thread_info['thread']['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
											$this->thread_info['thread']['thread_sticky'] ? "nonsticky" : "sticky" => $this->thread_info['thread']['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
											'move' => $locale['forum_0206']
										);

		$this->thread_info['form_action'] = $settings['site_seo'] ? FUSION_ROOT : ''.INFUSIONS."forum/viewthread.php?thread_id=".$this->thread_info['thread']['thread_id']."&amp;rowstart=".$_GET['rowstart'];
		$this->thread_info['open_post_form'] = openform('mod_form', 'post', $this->thread_info['form_action'], array('max_tokens' => 1,'notice' => 0));
		$this->thread_info['close_post_form'] = closeform();
		$this->thread_info['mod_form'] = "<div class='list-group-item'>\n
							<div class='btn-group m-r-10'>\n
								<a id='check' class='btn button btn-sm btn-default text-dark' href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',1);return false;\">".$locale['forum_0080']."</a>\n
								<a id='uncheck' class='btn button btn-sm btn-default text-dark' href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',0);return false;\">".$locale['forum_0081']."</a>\n
							</div>\n
							".form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], array('class' => 'btn-default btn-sm m-r-10'))."
							".form_button('delete_posts', $locale['forum_0177'], $locale['forum_0177'], array('class' => 'btn-default btn-sm'))."
							<div class='pull-right'>
							".form_button('go', $locale['forum_0208'], $locale['forum_0208'], array('class' => 'btn-default pull-right btn-sm m-t-0 m-l-10'))."
							".form_select('step', '', $this->thread_info['mod_options'], '', array('placeholder' => $locale['forum_0200'], 'width'=>'250px', 'allowclear'=>1, 'class'=>'m-b-0 m-t-5', 'inline'=>1))."
						</div>\n
					</div>\n";
	}
}

/**
 * Make filter links array
 */
private function set_ThreadFilterlinks() {
	// Filters
	global $locale;
	$this->thread_info['post-filters'][0] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;section=oldest', 'locale' => $locale['forum_0180']);
	$this->thread_info['post-filters'][1] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;section=latest', 'locale' => $locale['forum_0181']);
	if ($this->thread_info['permissions']['can_rate']) {
		$this->thread_info['allowed-post-filters'][2] = 'high';
		$this->thread_info['post-filters'][2] = array('value' => INFUSIONS.'forum/viewthread.php?thread_id='.$this->thread_info['thread_id'].'&amp;section=high', 'locale' => $locale['forum_0182']);
	}
}

/**
 * Thread Info Compiler
 */
private function prepare_ThreadInfo() {
	global $settings;
	if (!isset($_GET['thread_id'])) redirect(INFUSIONS.'forum/index.php');
	list($this->post_count, $this->last_post_id, $this->first_post_id) = dbarraynum(dbquery("SELECT COUNT(post_id), MAX(post_id), MIN(post_id) FROM ".DB_FORUM_POSTS." WHERE thread_id='".intval($_GET['thread_id'])."' AND post_hidden='0' GROUP BY thread_id"));
	if (!$this->post_count) redirect(INFUSIONS.'forum/index.php');
	$this->thread_info += array(
		'forum_id' => isset($_GET['forum_id']) && verify_forum($_GET['forum_id']) ? $_GET['forum_id'] : '',
		'forum_cat' => isset($_GET['forum_cat']) && verify_forum($_GET['forum_cat']) ? $_GET['forum_cat'] : '',
		'forum_branch' => isset($_GET['forum_branch']) && verify_forum($_GET['forum_branch']) ? $_GET['forum_branch'] : '',
		'thread_id' => isset($_GET['thread_id']) && verify_thread($_GET['thread_id']) ? $_GET['thread_id'] : '',
		'post_id' => isset($_GET['post_id']) && verify_post($_GET['post_id']) ? $_GET['post_id'] : '',
		'pid' => isset($_GET['pid']) && isnum($_GET['pid']) ? $_GET['pid'] : 0,
		'section' => isset($_GET['section']) ? $_GET['section'] : '',
		'mod_options' => array(),
		'form_action' => '',
		'open_post_form' => '',
		'close_post_form' => '',
		'mod_form' => '',
		'permissions' => array(
			'can_post'=>0,
			'can_poll' => 0,
			'can_vote' => 0,
			'can_rate' => 0,
			'can_view_poll' => 0,
		),
		'thread' => get_thread($_GET['thread_id']),
		'max_post_items' => $this->post_count,
		'post_firstpost' => $this->first_post_id,
		'post_lastpost' => $this->last_post_id,
		'posts_per_page' => $settings['posts_per_page'],
		'threads_per_page' => $settings['threads_per_page'],
		'lastvisited' => (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time(),
		'quick_reply_form' => '',
		'allowed_post_filters' => array('oldest', 'latest', 'high'),
		'attachtypes' => explode(",", $settings['attachtypes']),
	);
	$_GET['forum_id'] = $this->thread_info['forum_id'];
	$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->last_post_id ? $_GET['rowstart'] : 0;
}

private function set_PostInfo() {
	global $settings, $locale, $userdata;

	$user_field = array(
		'user_sig' => '',
		'user_web' => '',
	);
	$result = dbquery("SELECT field_name FROM ".DB_USER_FIELDS." WHERE field_name='user_sig' OR field_name='user_web'");
	while ($uf_data = dbarray($result)) {
		$user_field[$uf_data['field_name']] = TRUE;
	}
	$result = dbquery("SELECT p.forum_id, p.thread_id, p.post_id, p.post_message, p.post_showsig, p.post_smileys, p.post_author,
	p.post_datestamp, p.post_ip, p.post_ip_type, p.post_edituser, p.post_edittime, p.post_editreason,
	t.thread_id,
	u.user_id, u.user_name, u.user_status, u.user_avatar, u.user_level, u.user_posts, u.user_groups, u.user_joined, u.user_lastvisit, u.user_ip,
	".($user_field['user_sig'] ? " u.user_sig," : "").($user_field['user_web'] ? " u.user_web," : "")."
	u2.user_name AS edit_name, u2.user_status AS edit_status,
	SUM(v.vote_points) as vote_points
	FROM ".DB_FORUM_POSTS." p
	INNER JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
	LEFT JOIN ".DB_FORUM_VOTES." v ON v.post_id = p.post_id
	LEFT JOIN ".DB_USERS." u ON p.post_author = u.user_id
	LEFT JOIN ".DB_USERS." u2 ON p.post_edituser = u2.user_id AND post_edituser > '0'
	WHERE p.thread_id='".$_GET['thread_id']."' AND post_hidden='0' ".($this->thread_info['thread']['forum_type'] == '4' ? "OR p.post_id='".$this->thread_info['first_post_id']."'" : '')."
	GROUP by p.post_id ORDER BY $this->sortCol LIMIT ".$_GET['rowstart'].", ".$this->thread_info['posts_per_page']);
	$this->thread_info['post_rows'] = dbrows($result);
	if ($this->thread_info['post_rows'] > 0) {
		/* Set Threads Navigation */
		$this->thread_info['page_nav'] = format_word($this->thread_info['post_rows'], $locale['fmt_post']);
		if ($this->post_count > $this->thread_info['posts_per_page']) {
			$this->thread_info['page_nav'] .= "<div class='pull-right'>".makepagenav($_GET['rowstart'], $this->thread_info['posts_per_page'], $this->post_count, 3, INFUSIONS."forum/viewthread.php?forum_id=".$this->thread_info['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'].(isset($_GET['highlight']) ? "&amp;highlight=".urlencode($_GET['highlight']) : '')."&amp;")."</div>";
		}

		$i = 1;
		while ($pdata = dbarray($result)) {
			/**
			 * The Post
			 */
			$pdata['user_online'] = $pdata['user_lastvisit'] >= time()-3600 ? 1 : 0;
			$pdata['is_first_post'] = $pdata['post_id'] == $this->first_post_id ? 1 : 0;
			$pdata['is_last_post'] = $pdata['post_id'] == $this->last_post_id ? 1 : 0;

			// format post messages
			$pdata['post_message'] = $pdata['post_smileys'] ? parsesmileys($pdata['post_message']) : $pdata['post_messaage'];
			$pdata['post_message'] = nl2br(parseubb($pdata['post_message']));
			$pdata['post_message'] = (isset($_GET['highlight'])) ? "<div class='search_result'>".$pdata['post_message']."</div>\n" : $pdata['post_message'];

			/**
			 * User Stuffs, Sig, User Message, Web
			 */

			// Quote & Edit Link
			if (iMEMBER && ($this->thread_info['permissions']['can_post'] || $this->thread_info['permissions']['can_reply'])) {
				if (!$this->thread_info['thread']['thread_locked']) {
					$pdata['post_quote'] = array('link'=>INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id']."&amp;quote=".$pdata['post_id'], 'name'=>$locale['forum_0266']);
					if (iMOD || (($this->thread_info['permissions']['edit_lock'] && $pdata['is_last_post'] || !$this->thread_info['permissions']['edit_lock'])) && ($userdata['user_id'] == $pdata['post_author']) && ($settings['forum_edit_timelimit'] <= 0 || time()-$settings['forum_edit_timelimit']*60 < $pdata['post_datestamp'])) {
						$pdata['post_edit'] =  array('link'=>INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'], 'name'=>$locale['forum_0265']);
					}
				} elseif (iMOD) {
					$pdata['post_edit'] = array('link'=>INFUSIONS."forum/viewthread.php?action=edit&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'], 'name'=>$locale['forum_0265']);
				}
			}
			$pdata['user_profile_link'] = profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']);
			$pdata['user_avatar'] = display_avatar($pdata, '50px', '', '', 'img-rounded');
			// rank img
			if ($pdata['user_level'] <= USER_LEVEL_ADMIN) {
				$pdata['rank_img'] =  $settings['forum_ranks'] ? show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']) : getuserlevel($pdata['user_level']);
			} else {
				$is_mod = FALSE;
				if (!empty($pdata['mod_groups'])) {
					foreach ($pdata['mod_groups'] as $mod_group) {
						if (!$is_mod && preg_match("(^\.{$mod_group}$|\.{$mod_group}\.|\.{$mod_group}$)", $pdata['user_groups'])) {
							$is_mod = TRUE;
						}
					}
				}
				if ($settings['forum_ranks']) {
					$pdata['rank_img'] =  $is_mod ? show_forum_rank($pdata['user_posts'], 104, $pdata['user_groups']) : show_forum_rank($pdata['user_posts'], $pdata['user_level'], $pdata['user_groups']);
				} else {
					$pdata['rank_img'] =  $is_mod ? $locale['userf1'] : getuserlevel($pdata['user_level']);
				}
			}
			// IP
			$pdata['user_ip'] = (($settings['forum_ips'] && iMEMBER) || iMOD or USER_LEVEL_SUPER_ADMIN) ? $locale['forum_0268'].' '.$pdata['post_ip'] : '';
			// Post count
			$pdata['user_post_count'] = format_word($pdata['user_posts'], $locale['fmt_post']);
			// Print Thread
			$pdata['print'] = array('link'=>BASEDIR."print.php?type=F&amp;thread=".$_GET['thread_id']."&amp;post=".$pdata['post_id']."&amp;nr=".($i+$_GET['rowstart']), 'name'=>$locale['forum_0179']);
			// Website
			if ($pdata['user_web'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
				$user_web_url_prefix = !strstr($pdata['user_web'], "http://") ? "http://" : "";
				$pdata['user_web'] = array('link'=>$user_web_url_prefix.$pdata['user_web'], 'name'=>$locale['forum_0364']);
			}
			// PM link
			if (iMEMBER && $pdata['user_id'] != $userdata['user_id'] && (iADMIN || $pdata['user_status'] != 6 && $pdata['user_status'] != 5)) {
				$pdata['user_message'] = array('link'=>BASEDIR.'messages.php?msg_send='.$pdata['user_id'], 'name'=>$locale['send_message']);
			}
			// User Sig
			if ($pdata['user_sig'] && isset($pdata['post_showsig']) && $pdata['user_status'] != 6 && $pdata['user_status'] != 5) {
				$pdata['user_sig'] = nl2br(parseubb(parsesmileys(stripslashes($pdata['user_sig'])), "b|i|u||center|small|url|mail|img|color"));
			}


			// Voting - need up or down link - accessible to author also the vote

			// answered and on going questions.
			$pdata['vote_message'] = '';
			//echo $data['forum_type'] == 4 ? "<br/>\n".(number_format($data['thread_postcount']-1)).$locale['forum_0365']."" : ''; // answers

			// form components
			$pdata['post_checkbox'] = iMOD ? "<input type='checkbox' name='delete_post[]' value='".$pdata['post_id']."'/>" : '';

			$pdata['post_votebox'] = '';
			if ($this->thread_info['permissions']['can_rate']) { // can vote.
				if (checkgroup($this->thread_info['forum_vote'])) { // everyone can vote as long pass checkgroup.
					// check for own vote link.
					if ($pdata['user_id'] !== $userdata['user_id']) {
						$pdata['vote_up'] = array('link'=>INFUSIONS."forum/post.php?action=voteup&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'], 'name'=>$locale['forum_0265']);
						$pdata['vote_down'] = array('link'=>INFUSIONS."forum/post.php?action=votedown&amp;forum_id=".$pdata['forum_id']."&amp;thread_id=".$pdata['thread_id']."&amp;post_id=".$pdata['post_id'], 'name'=>$locale['forum_0265']);
					}
					$pdata['vote_points'] = !empty($pdata['vote_points']) ? $pdata['vote_points'] : 0;
				} else {
					$pdata['vote_points'] = !empty($pdata['vote_points']) ? $pdata['vote_points'] : 0;
				}

				$pdata['post_votebox'] = "<div class='text-center'>\n";
				$pdata['post_votebox'] .= (!empty($pdata['vote_up'])) ? "<a href='".$pdata['vote_up']['link']."' class='mid-opacity text-dark'>\n<i class='fa fa-arrow-up fa-2x'></i></a>" : "<i class='entypo up-dir low-opacity icon-sm'></i>";
				$pdata['post_votebox'] .= "<h4 class='m-0'>".$pdata['vote_points']."</h4>\n";
				$pdata['post_votebox'] .= (!empty($pdata['vote_down'])) ? "<a href='".$pdata['vote_down']['link']."' class='mid-opacity text-dark'>\n<i class='fa fa-arrow-down fa-2x'></i></a>" : "<i class='entypo down-dir low-opacity icon-sm'></i>";
				$pdata['post_votebox'] .= "</div>\n";
			}

			// Marker
			$pdata['marker'] = array('link'=>"#post_".$pdata['post_id'], 'name'=>"#".($i+$_GET['rowstart']), 'id'=>"post_".$pdata['post_id']);
			$pdata['post_marker'] = !empty($pdata['marker']) ? "<a class='marker' href='".$pdata['marker']['link']."' id='".$pdata['marker']['id']."'>".$pdata['marker']['name']."</a>" : '';
			$pdata['post_marker'] .= "<a title='".$locale['forum_0241']."' href='#top'><i class='entypo up-open'></i></a>\n";

			// Edit Reason - NOT WORKING?
			$pdata['post_edit_reason'] = '';
			if ($pdata['post_edittime']) {
				$edit_reason = "<div class='edit_reason m-t-10'>
								<small>".$locale['forum_0164'].profile_link($pdata['post_edituser'], $pdata['edit_name'], $pdata['edit_status']).$locale['forum_0167'].showdate("forumdate", $pdata['post_edittime'])."</small>\n";
				if ($pdata['post_editreason'] && iMEMBER) {
					$edit_reason .= "<br /><a id='reason_pid_".$pdata['post_id']."' rel='".$pdata['post_id']."' class='reason_button small' data-target='reason_div_pid_".$pdata['post_id']."'>";
					$edit_reason .= "<strong>".$locale['forum_0165']."</strong>";
					$edit_reason .= "</a>\n";
					$edit_reason .= "<div id='reason_div_pid_".$pdata['post_id']."' class='reason_div small'>".$pdata['post_editreason']."</div>\n";
				}
				$edit_reason .= "</div>\n";
				$pdata['post_edit_reason'] = $edit_reason;
				$this->edit_reason = true;
			}

			// Attachments - $image and $files
			$pdata['attach-files-count'] = 0;
			$pdata['attach-image-count'] = 0;
			$pdata['post_attachments'] = '';
			if (isset($this->thread_info['attachments'][$pdata['post_id']])) {
				require_once INCLUDES."mimetypes_include.php";
				$i_files = 1;	$i_image = 1;
				$pdata['attach-image'] = '';
				$pdata['attach-files'] = '';
				foreach($this->thread_info['attachments'][$pdata['post_id']] as $attach) {
					if (in_array($attach['attach_mime'], img_mimeTypes())) {
						$pdata['attach-image'] .= display_image_attach($attach['attach_name'], "100", "100", $pdata['post_id'])."\n";
						$i_image++;
					} else {
						$pdata['attach-files'] .= "<div class='display-inline-block'><i class='entypo attach'></i><a href='".FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;getfile=".$attach['attach_id']."'>".$attach['attach_name']."</a>&nbsp;";
						$pdata['attach-files'] .= "[<span class='small'>".parsebytesize(filesize(INFUSIONS."forum/attachments/".$attach['attach_name']))." / ".$attach['attach_count'].$locale['forum_0162']."</span>]</div>\n";
						$i_files++;
					}
				}
				if (!empty($pdata['attach-files'])) {
					$pdata['post_attachments'] .= "<div class='emulated-fieldset'>\n";
					$pdata['post_attachments'] .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).$locale['forum_0154'].($pdata['attach-files-count'] > 1 ? $locale['forum_0158'] : $locale['forum_0157'])."</span>\n";
					$pdata['post_attachments'] .= "<div class='attachments-list m-t-10'>".$pdata['attach-files']."</div>\n";
					$pdata['post_attachments'] .= "</div>\n";
				}
				if (!empty($pdata['attach_image'])) {
					$pdata['post_attachments'] .= "<div class='emulated-fieldset'>\n";
					$pdata['post_attachments'] .= "<span class='emulated-legend'>".profile_link($pdata['user_id'], $pdata['user_name'], $pdata['user_status']).$locale['forum_0154'].($pdata['attach-image-count'] > 1 ? $locale['forum_0156'] : $locale['forum_0155'])."</span>\n";
					$pdata['post_attachments'] .= "<div class='attachments-list'>".$pdata['attach-image']."</div>\n";
					$pdata['post_attachments'] .= "</div>\n";
					if (!defined('COLORBOX')) {
						define('COLORBOX', true);
						add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
						add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
						add_to_jquery("$('a[rel^=\"attach\"]').colorbox({ current: '".$locale['forum_0159']." {current} ".$locale['forum_0160']." {total}',width:'80%',height:'80%'});");
					}
				}
				$pdata['attach-files-count'] = $i_files;
				$pdata['attach-image-count'] = $i_image;
			}

			
			// Custom Post Message Link/Buttons
			
			$pdata['post_links']  = '';
			$pdata['post_links'] .= !empty($pdata['post_quote']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_quote']['name']."' href='".$pdata['post_quote']['link']."'>".$pdata['post_quote']['name']."</a>\n" : '';
			$pdata['post_links'] .= !empty($pdata['post_edit']) ? "<a class='btn btn-xs btn-default' title='".$pdata['post_edit']['name']."' href='".$pdata['post_edit']['link']."'>".$pdata['post_edit']['name']."</a>\n" : '';
			$pdata['post_links'] .= !empty($pdata['print']) ? "<a class='btn btn-xs btn-default' title='".$pdata['print']['name']."' href='".$pdata['print']['link']."'>".$pdata['print']['name']."</a>\n" : '';
			$pdata['post_links'] .= !empty($pdata['user_web']) ? "<a class='btn btn-xs btn-default' class='forum_user_actions' href='".$pdata['user_web']['link']."' target='_blank'>".$pdata['user_web']['name']."</a>\n" : '';
			$pdata['post_links'] .= !empty($pdata['user_message']) ? "<a class='btn btn-xs btn-default' href='".$pdata['user_message']['link']."' target='_blank'>".$pdata['user_message']['name']."</a>\n" : '';

			// Post Date
			$pdata['post_date'] = $locale['forum_0524']." ".timer($pdata['post_datestamp'])." - ".showdate('forumdate', $pdata['post_datestamp']);
			$this->thread_info['post_items'][$pdata['post_id']] = $pdata;
			$i++;
		}
	}
}


 // Quick Reply Form Sample
 
private function set_QuickReply() {
	global $userdata, $settings, $locale;

	if ($this->thread_info['permissions']['can_reply'] && $this->thread_info['thread']['forum_quick_edit']) {

		if (isset($_POST['postreply'])) {
			$info = $this->thread_info['thread'];
			if ($info['forum_type'] == 1) redirect(INFUSIONS.'forum/index.php');
			$info['lock_edit'] = $settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
			if (checkgroup($info['forum_reply']) && $this->thread_info['thread_id']) {
				$result = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_id='".$this->thread_info['thread_id']."' ".(iMOD || iSUPERADMIN ? '' : "AND thread_locked = '0'")." AND thread_hidden='0'");
				if (dbrows($result)) {
					$data = dbarray($result);
					add_to_title($locale['global_201'].$locale['forum_0503']);
					add_breadcrumb(array('link' => '', 'title' => $locale['forum_0503']));
					if (isset($_GET['quote']) && isnum($_GET['quote'])) {
						$quote_result = dbquery("SELECT a.post_message, b.user_name
									FROM ".DB_FORUM_POSTS." a
									INNER JOIN ".DB_USERS." b ON a.post_author=b.user_id
									WHERE thread_id='".intval($this->thread_info['thread_id'])."' and post_id='".intval($_GET['quote'])."'
									");
						if (dbrows($quote_result) > 0) {
						require_once INCLUDES."bbcode_include.php";
							$quote_data = dbarray($quote_result);
							$data['post_message'] = "[quote name=".$quote_data['user_name']." post=".$_GET['quote']."]".strip_bbcodes($quote_data['post_message'])."[/quote]\r\r";
						} else {
							redirect(INFUSIONS.'forum/index.php');
						}
					}
					if (isset($_POST['postreply']) or isset($_POST['previewpost'])) {
						include "post_actions.php";
					}
					$data['reply'] = 1;
				} else {
					redirect(INFUSIONS.'forum/index.php'); // no threads
				}
			}
			include "post_actions.php";
		}

		$html = "<!--sub_forum_thread-->\n";
		$form_action = ($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'];
		$html .= openform('qr_form', 'post', $form_action, array('class'=>'m-b-20 m-t-20', 'downtime' => 1));
		$html .= "<h4 class='m-t-20 pull-left'>".$locale['forum_0168']."</h4>\n";
		$html .= form_textarea('post_message', $locale['forum_0601'], '', array('bbcode' => 1, 'required' => 1, 'autosize'=>1, 'preview'=>1, 'form_name'=>'qr_form'));
		$html .= "<div class='m-t-10 pull-right'>\n";
		$html .= form_button('postreply', $locale['forum_0172'], $locale['forum_0172'], array('class' => 'btn-primary btn-sm m-r-10'));
		$html .= "</div>\n";
		$html .= "<div class='overflow-hide'>\n";
		$html .= form_checkbox('post_smileys', $locale['forum_0169'], '', array('class'=>'m-b-0'));
		if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
			$html .= form_checkbox('post_showsig', $locale['forum_0170'], '1', array('class'=>'m-b-0'));
		}
		if ($settings['thread_notify']) {
			$html .= form_checkbox('notify_me', $locale['forum_0171'], $this->thread_info['thread']['user_tracked'], array('class'=>'m-b-0'));
		}
		$html .= "</div>\n";
		$html .= closeform();

		$this->thread_info['quick_reply_form'] = $html;
	}
}

private function set_ThreadButtons(){
	global $locale;
	$this->thread_info['buttons'] = array(
		'print' => array('link'=>BASEDIR."print.php?type=F&amp;thread=".$this->thread_info['thread_id']."&amp;rowstart=".$_GET['rowstart'], 'name'=>$locale['forum_0178']),
		'newthread' => $this->thread_info['permissions']['can_post'] ? array('link'=>INFUSIONS."forum/newthread.php?forum_id=".$this->thread_info['thread']['forum_id'], 'name'=>$locale['forum_0264']) : array(),
		'reply' => $this->thread_info['permissions']['can_reply'] ? array('link'=>INFUSIONS."forum/viewthread.php?action=reply&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'], 'name'=>$locale['forum_0360']) : array(),
		'notify' => $this->thread_info['thread']['user_tracked'] ? array('link'=>INFUSIONS."forum/postify.php?post=off&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'], 'name'=>$locale['forum_0174']) : array('link'=>INFUSIONS."forum/postify.php?post=on&amp;forum_id=".$this->thread_info['thread']['forum_id']."&amp;thread_id=".$this->thread_info['thread']['thread_id'], 'name'=>$locale['forum_0175']),
	);
}

private function set_ThreadPolls() {
	global $userdata, $settings, $locale;

	if ($this->thread_info['thread']['thread_poll'] && $this->thread_info['permissions']['can_view_poll']) {

		if ($this->thread_info['permissions']['can_vote_poll']) {
			$poll_result = dbquery("SELECT tfp.forum_poll_title, tfp.forum_poll_votes, tfv.forum_vote_user_id
				FROM ".DB_FORUM_POLLS." tfp
				LEFT JOIN ".DB_FORUM_POLL_VOTERS." tfv
				ON tfp.thread_id=tfv.thread_id AND forum_vote_user_id='".$userdata['user_id']."'
				WHERE tfp.thread_id='".$_GET['thread_id']."'");
		} else {
			$poll_result = dbquery("SELECT tfp.forum_poll_title, tfp.forum_poll_votes FROM ".DB_FORUM_POLLS." tfp WHERE tfp.thread_id='".$_GET['thread_id']."'");
		}
		if (dbrows($poll_result) > 0) {
			$this->thread_info['poll'] = dbarray($poll_result);

			$p_options = dbquery("SELECT forum_poll_option_votes, forum_poll_option_text
				FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$_GET['thread_id']."'
				ORDER BY forum_poll_option_id ASC
				");
			$poll_option_rows = dbrows($p_options);
			$this->thread_info['poll']['max_option_id'] = $poll_option_rows;
			if ($poll_option_rows > 0) {
				while ($pdata = dbarray($p_options)) {
					$this->thread_info['poll']['poll_opts'][] = $pdata;
				}
			}

			$this->thread_info['permissions']['can_vote_poll'] = isset($this->thread_info['poll']['forum_vote_user_id']) ? 0 : 1;
			if ((isset($_POST['poll_option']) && isnum($_POST['poll_option']) && $_POST['poll_option'] <= $this->thread_info['poll']['max_option_id']) && $this->thread_info['permissions']['can_vote_poll'] && !defined('FUSION_NULL')) {
				dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_votes=forum_poll_option_votes+1 WHERE thread_id='".$_GET['thread_id']."' AND forum_poll_option_id='".$_POST['poll_option']."'");
				dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes+1 WHERE thread_id='".$_GET['thread_id']."'");
				dbquery("INSERT INTO ".DB_FORUM_POLL_VOTERS." (thread_id, forum_vote_user_id, forum_vote_user_ip, forum_vote_user_ip_type) VALUES ('".$this->thread_info['thread_id']."', '".$userdata['user_id']."', '".USER_IP."', '".USER_IP_TYPE."')");
				redirect(FUSION_SELF."?thread_id=".$this->thread_info['thread_id']);
			}

			$html = '';
			if ($this->thread_info['permissions']['can_vote_poll']) {
				$html = openform('voteform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').INFUSIONS."forum/viewthread.php?thread_id=".$this->thread_info['thread_id'], array('notice'=>0, 'downtime'=>1));
			}
			$html .= "<span class='text-bigger strong display-inline-block m-b-10'><i class='entypo chart-pie'></i>".$this->thread_info['poll']['forum_poll_title']."</span>\n";
			$html .= "<hr class='m-t-0 m-b-10'/>\n";
			$html .= "<ul class='p-l-20 p-t-0'>\n";
			$i = 1;
			if (!empty($this->thread_info['poll']['poll_opts'])) {
				foreach ($this->thread_info['poll']['poll_opts'] as $poll_option) {
					if ($this->thread_info['permissions']['can_vote_poll']) {
						$html .= "<li><label for='opt-".$i."'><input id='opt-".$i."' type='radio' name='poll_option' value='".$i."' class='m-r-20'> <span class='m-l-10'>".$poll_option['forum_poll_option_text']."</span>\n</label></li>\n";
					} else {
						$option_votes = ($this->thread_info['poll']['forum_poll_votes'] ? number_format(100/$this->thread_info['poll']['forum_poll_votes']*$poll_option['forum_poll_option_votes']) : 0);
						$html .= progress_bar($option_votes, $poll_option['forum_poll_option_text'], '', '10px');
					}
					$i++;
				}
			}
			$html .= "</ul>\n";
			if ($this->thread_info['permissions']['can_vote_poll']) {
				$html .= "<hr class='m-t-10 m-b-10'/>\n";
				$html .= form_button('vote', $locale['forum_2010'], 'vote', array('class'=>'btn btn-sm btn-primary m-l-20 '));
				$html .= closeform();
			}
			$html .= "</div>\n";
			$html .= "</div>\n";
			$this->thread_info['poll_form'] = $html;
		}
	}
}

// Forum Attachment Access - this have to move into post query perhaps.
 
private function set_ThreadAttach() {
	if ($this->thread_info['permissions']['can_download_attach']) {
		$a_result = dbquery("SELECT * FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id='".$this->thread_info['thread_id']."' ORDER BY post_id ASC");
		if (dbrows($a_result) > 0) {
			while ($a_data = dbarray($a_result)) {
				if (file_exists(INFUSIONS."forum/attachments/".$a_data['attach_name'])) {
					$this->thread_info['attachments'][$a_data['post_id']][] = $a_data;
				}
			}
		}
	}
}

/**
 * Set User Input Changes Switch - Save, Update, Edit.
 * @todo: have user to be able to delete own post.
 * @todo: consolidate post_actions.php into this class - 100% of forum actions are done in viewthread.php except for (new thread (for now..))
 */
private function set_ForumPostDB() {
	global $locale, $settings, $userdata;
	if (Functions::verify_thread($this->thread_info['thread_id'])) {
		$info = $this->thread_info['thread'];
		if ($info['forum_type'] == 1) redirect(INFUSIONS.'forum/index.php');
		$info['lock_edit'] = $settings['forum_edit_lock'] == 1 ? TRUE : FALSE;
		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'voteup':
					if (verify_thread($this->thread_info['thread_id']) && verify_post($_GET['post_id'])) {
						set_forumVotes($info, 1);
					}
					break;
				case 'votedown':
					if (verify_thread($this->thread_info['thread_id']) && verify_post($_GET['post_id'])) {
						set_forumVotes($info, -1);
					}
					break;
				case 'reply':
					if (checkgroup($info['forum_reply']) && $this->thread_info['thread_id']) {
						$result = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_id='".$this->thread_info['thread_id']."' ".(iMOD || iSUPERADMIN ? '' : "AND thread_locked = '0'")." AND thread_hidden='0'");
						if (dbrows($result)) {
							$data = dbarray($result);
							add_to_title($locale['global_201'].$locale['forum_0503']);
							add_breadcrumb(array('link' => '', 'title' => $locale['forum_0503']));
							if (isset($_GET['quote']) && isnum($_GET['quote'])) {
								$quote_result = dbquery("SELECT a.post_message, b.user_name
									FROM ".DB_FORUM_POSTS." a
									INNER JOIN ".DB_USERS." b ON a.post_author=b.user_id
									WHERE thread_id='".intval($this->thread_info['thread_id'])."' and post_id='".intval($_GET['quote'])."'
									");
								if (dbrows($quote_result) > 0) {
									$quote_data = dbarray($quote_result);
									$data['post_message'] = "[quote name=".$quote_data['user_name']." post=".$_GET['quote']."]".strip_bbcodes($quote_data['post_message'])."[/quote]\r\r";
								} else {
									redirect(INFUSIONS.'forum/index.php');
								}
							}
							if (isset($_POST['postreply']) or isset($_POST['previewpost'])) {
								include "post_actions.php";
							}
							$data['reply'] = 1;
							postform($data, $info);
						} else {
							redirect(INFUSIONS.'forum/index.php'); // no threads
							echo "yes";
						}
					}
					break;
				case 'edit':
					if (checkgroup($info['forum_reply']) && $this->thread_info['thread_id'] && isset($this->thread_info['post_items'][$this->thread_info['post_id']])) {
						$result = dbquery("SELECT tp.*, tt.thread_subject, tt.thread_poll, tt.thread_author, tt.thread_locked, MIN(tp2.post_id) AS first_post
							FROM ".DB_FORUM_POSTS." tp
							INNER JOIN ".DB_FORUM_THREADS." tt on tp.thread_id=tt.thread_id
							INNER JOIN ".DB_FORUM_POSTS." tp2 on tp.thread_id=tp2.thread_id
							WHERE tp.post_id='".$this->thread_info['post_id']."' AND tp.thread_id='".$this->thread_info['thread_id']."' AND tp.forum_id='".$this->thread_info['forum_id']."' GROUP BY tp2.post_id
							");
						if (dbrows($result) > 0) {
							$data = dbarray($result);
							if ($userdata['user_id'] != $data['post_author'] && !iMOD && !iSUPERADMIN) {
								redirect(INFUSIONS.'forum/index.php');
							}
							if ($data['post_locked'] && !iMOD) {
								redirect("postify.php?post=edit&error=5&forum_id=".$this->thread_info['forum_id']."&thread_id=".$this->thread_info['thread_id']."&post_id=".$this->thread_info['post_id']);
							}
							if (!iMOD && ($settings['forum_edit_timelimit'] > 0 && time()-$settings['forum_edit_timelimit']*60 > $data['post_datestamp'])) {
								redirect(INFUSIONS."forum/postify.php?post=edit&error=6&forum_id=".$this->thread_info['forum_id']."&thread_id=".$this->thread_info['thread_id']."&post_id=".$this->thread_info['post_id']);
							}
							$last_post = dbarray(dbquery("SELECT post_id
													FROM ".DB_FORUM_POSTS."
													WHERE thread_id='".$this->thread_info['thread_id']."' AND post_hidden='0'
													ORDER BY post_datestamp DESC LIMIT 1"));
							if (iMOD || !$data['thread_locked'] && (($info['forum_edit_lock'] && $last_post['post_id'] == $data['post_id'] && $userdata['user_id'] == $data['post_author']) || (!$info['forum_edit_lock'] && $userdata['user_id'] == $data['post_author']))) {
								$data['edit'] = 1;
								if ($info['forum_attach'] && checkgroup($info['forum_attach'])) {
									$result = dbquery("SELECT attach_id, attach_name FROM ".DB_FORUM_ATTACHMENTS." WHERE post_id='".$this->thread_info['post_id']."'");
									$counter = 0;
									if (dbrows($result)) {
										while ($adata = dbarray($result)) {
											$info['attachment'][$adata['attach_id']] = $adata['attach_name'];
											$counter++;
										}
										$info['attachmax_count'] = ($settings['attachmax_count']-$counter <= 0 ? "-2" : $settings['attachmax_count']-$counter);
									}
								}
								if ($info['forum_poll'] && checkgroup($info['forum_poll'])) {
									if ($data['thread_poll'] && ($data['post_author'] == $data['thread_author']) && ($userdata['user_id'] == $data['thread_author'] || iSUPERADMIN || iMOD)) {
										$result = dbquery("SELECT * FROM ".DB_FORUM_POLLS." WHERE thread_id='".$this->thread_info['thread_id']."'");
										if (dbrows($result) > 0) {
											$data += dbarray($result);
											$result = dbquery("SELECT forum_poll_option_text FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".$this->thread_info['thread_id']."' ORDER BY forum_poll_option_id ASC");
											while ($_pdata = dbarray($result)) {
												$data['poll_opts'][] = $_pdata['forum_poll_option_text'];
											}
										}
									}
								}
								if (isset($_POST['savechanges']) or isset($_POST['previewpost'])) {
									include "post_actions.php";
								}
								postform($data, $info);
							} else {
								redirect(INFUSIONS.'forum/index.php'); // edit rules failed.
							}
						} else {
							redirect(INFUSIONS.'forum/index.php'); // cannot find post_id.
						}
					}
					break;
			} 
		}
	} else {
		redirect(INFUSIONS.'forum/index.php');
	}
}
}
