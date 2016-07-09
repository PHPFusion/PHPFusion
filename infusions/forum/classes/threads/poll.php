<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Viewthread.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Forums\Threads;

/**
 * Class Poll
 * @package PHPFusion\Forums\Threads
 */
class Poll {

    /**
     * Permissions for Forum Poll
     * @var array
     */
    private static $permissions = array();

    /**
     * Object
     * @param array $thread_info
     */
    public function __construct(array $thread_info) {
        self::set_poll_permissions($thread_info['permissions']);
    }

    /**
     * Set Permissions Settings
     * @param array $thread_info
     */
    private static function set_poll_permissions(array $thread_info) {
        self::$permissions = $thread_info;
    }

    /**
     * Fetches Permissions Settings
     * @param $key
     * @return bool
     */
    private static function get_poll_permissions( $key ) {
        return (isset(self::$permissions[$key])) ? self::$permissions[$key] : FALSE;
    }

    /**
     * Generate Poll HTML
     * @param array $thread_data
     * @return string
     */
    public static function generate_poll( array $thread_data) {

        $locale = fusion_get_locale();
        $html = "";

        if (self::get_poll_permissions("can_access") && $thread_data['thread_poll'] == TRUE) {

            $poll_query = "
            SELECT poll_opts.*, poll.forum_poll_title, poll.forum_poll_votes
            FROM ".DB_FORUM_POLL_OPTIONS." poll_opts
            INNER JOIN ".DB_FORUM_POLLS." poll using (thread_id)
            WHERE poll.thread_id='".intval($thread_data['thread_id'])."'
            ";

            $poll_result = dbquery($poll_query);

            if (dbrows($poll_result) > 0) {
                $i = 0;

                // Construct poll data - model
                $poll = array();
                while ($pdata = dbarray($poll_result)) {
                    if ($i == 0) {
                        $poll['forum_poll_title'] = $pdata['forum_poll_title'];
                        $poll['forum_poll_votes'] = $pdata['forum_poll_votes'];
                        $poll['forum_poll_max_options'] = dbrows($poll_result);
                    }
                    $poll['forum_poll_options'][$pdata['forum_poll_option_id']] = $pdata;
                    $i++;
                }

                // SQL cast poll vote
                if (isset($_POST['poll_option']) && isnum($_POST['poll_option']) && $_POST['poll_option'] <= $poll['forum_poll_max_options']) {

                    if (self::get_poll_permissions("can_vote_poll") == TRUE) {
                        $pollInput['poll_option_id'] = stripinput($_POST['poll_option']);

                        if (\defender::safe()) {

                            dbquery("UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_votes=forum_poll_option_votes+1 WHERE thread_id='".intval($thread_data['thread_id'])."' AND forum_poll_option_id='".intval($pollInput['poll_option_id'])."'");
                            dbquery("UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes+1 WHERE thread_id='".intval($thread_data['thread_id'])."'");
                            dbquery("INSERT INTO ".DB_FORUM_POLL_VOTERS." (thread_id, forum_vote_user_id, forum_vote_user_ip, forum_vote_user_ip_type) VALUES ('".$thread_data['thread_id']."', '".fusion_get_userdata("user_id")."', '".USER_IP."', '".USER_IP_TYPE."')");
                            addNotice('success', $locale['forum_0614']);
                            redirect(INFUSIONS."forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']);

                        } else {
                            addNotice("danger", $locale['forum_0617']);
                        }
                    }
                }

                $poll_start = "";
                $poll_end = "";

                if (self::get_poll_permissions("can_vote_poll")) {
                    $poll_start = openform("poll_vote_form", "post",
                                           INFUSIONS."forum/viewthread.php?thread_id=".$thread_data['thread_id']);
                    $poll_end = form_button('vote', $locale['forum_2010'], 'vote',
                                            array('class' => 'btn btn-sm btn-primary m-l-20 '));
                    $poll_end .= closeform();
                }

                if (self::get_poll_permissions("can_edit_poll")) {

                    $html .= "<div class='pull-right btn-group'>\n";
                    $html .= "<a class='btn btn-sm btn-default' href='".INFUSIONS."forum/viewthread.php?action=editpoll&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."'>".$locale['forum_0603']."</a>\n";
                    $html .= "<a class='btn btn-sm btn-default' href='".INFUSIONS."forum/viewthread.php?action=deletepoll&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."' onclick='confirm('".$locale['forum_0616']."');'>".$locale['delete']."</a>\n";
                    $html .= "</div>\n";

                }

                if (!empty($poll)) {
                    $html .= $poll_start;

                    $html .= "<h2 class='strong m-t-0 m-b-10'><i class='fa fa-fw fa-pie-chart fa-lg'></i>".$locale['forum_0377'].": \"".$poll['forum_poll_title']."\"</h2>\n";
                    $html .= "<ul class='p-l-20 p-t-0'>\n";

                    if (!empty($poll['forum_poll_options'])) {
                        $i = 1;
                        $vote_options = $poll['forum_poll_options'];
                        foreach ($vote_options as $poll_option) {
                            if (self::get_poll_permissions("can_vote_poll") == TRUE) {
                                $html .= "<li><label for='opt-".$i."'><input id='opt-".$i."' type='radio' name='poll_option' value='".$i."' class='m-r-20'> <span class='m-l-10'>".$poll_option['forum_poll_option_text']."</span>\n</label></li>\n";
                            } else {
                                $option_votes = ($poll['forum_poll_votes'] ? number_format(100 / $poll['forum_poll_votes'] * $poll_option['forum_poll_option_votes']) : 0);
                                $html .= progress_bar($option_votes, $poll_option['forum_poll_option_text'], '',
                                                      '10px');
                            }
                            $i++;
                        }
                    }
                    $html .= "</ul>\n";
                    $html .= $poll_end;
                }
            }
        }
        return (string) $html;
    }
}