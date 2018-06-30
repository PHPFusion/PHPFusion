<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: vote.php
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
namespace PHPFusion\Forums\Postify;

use PHPFusion\Forums\Moderator;

/**
 * Vote Up and Down on Q&A Forum Type
 * Class Postify_vote
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_Vote extends Forum_Postify {

    public function execute() {
        // I'm voting. so i need the vote id.
        $thread_data = dbarray(dbquery("SELECT
              p.post_id, p.post_author,
              t.thread_id, t.forum_id, t.thread_lastpostid, t.thread_postcount, t.thread_subject, t.thread_locked,
              f.forum_lock, f.forum_post_ratings, f.forum_mods, f.forum_type
              FROM ".DB_FORUM_POSTS." p
              INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
              INNER JOIN ".DB_FORUMS." f ON f.forum_id=p.forum_id AND f.forum_type=:forum_type
              WHERE p.post_id=:post_id AND p.thread_id=:thread_id",
            [
                ':thread_id'  => $_GET['thread_id'],
                ':post_id'    => $_GET['post_id'],
                ':forum_type' => 4
            ]
        ));
        if (!empty($thread_data)) {
            Moderator::define_forum_mods($thread_data);
            // i can upvote as many post but each post only once.
            $thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];
            $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
            if ($this->check_forum_access($forum_index, $_GET['forum_id'], $_GET['thread_id'])) {
                $d = [
                    'forum_id'       => $thread_data['forum_id'],
                    'thread_id'      => $thread_data['thread_id'],
                    'post_id'        => $thread_data['post_id'],
                    'vote_user'      => fusion_get_userdata('user_id'),
                    'voter_id'       => fusion_get_userdata('user_id'),
                    'user_id'        => $thread_data['post_author'],
                    'vote_datestamp' => TIME,
                    'datestamp'      => TIME,
                ];
                if ($thread_data['forum_type'] == 4 && (iMOD || (checkgroup($thread_data['forum_post_ratings']) && $thread_data['forum_lock'] == FALSE && $thread_data['thread_locked'] == FALSE))) {
                    if ($thread_data['post_author'] !== fusion_get_userdata('user_id')) {
                        $vote_query = "
                        SELECT v.vote_id, r.rep_id
                        FROM ".DB_FORUM_VOTES." v
                        INNER JOIN ".DB_FORUM_USER_REP." r ON v.post_id=r.post_id AND v.vote_user=r.voter_id
                        WHERE v.vote_user=:my_id AND v.post_id=:post_id
                        ";
                        $vote_bind = [
                            ':my_id'   => fusion_get_userdata('user_id'),
                            ':post_id' => $thread_data['post_id']
                        ];
                        switch ($_GET['post']) {
                            case 'voteup':
                                if (fusion_get_userdata('user_reputation') >= self::$forum_settings['points_to_upvote']) {
                                    $vote_result = dbquery($vote_query, $vote_bind);
                                    if (dbrows($vote_result)) {
                                        $vote = dbarray($vote_result);
                                        // I have voted, I'm removing my vote
                                        dbquery("DELETE FROM ".DB_FORUM_VOTES." WHERE vote_id=:vote_id", [':vote_id' => $vote['vote_id']]);
                                        // Remove log points
                                        dbquery("DELETE FROM ".DB_FORUM_USER_REP." WHERE rep_id=:rep_id", [':rep_id' => $vote['rep_id']]);
                                        // remove points from the post author
                                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation-:points WHERE user_id=:post_author_id", [
                                            ':post_author_id' => $thread_data['post_author'],
                                            ':points'         => self::$forum_settings['upvote_points']
                                        ]);
                                        addNotice('success', self::$locale['forum_0515'], 'viewthread.php');
                                    } else {
                                        // I have not yet voted, I'm upvoting.
                                        $d['vote_points'] = 1;
                                        $d['points_gain'] = self::$forum_settings['upvote_points'];
                                        dbquery_insert(DB_FORUM_VOTES, $d, 'save');
                                        dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                                        addNotice('success', self::$locale['forum_0516'], 'viewthread.php');
                                    }
                                    redirect(self::$default_redirect_link);
                                } else {
                                    addNotice('danger', strtr(self::$locale['forum_0519'], ['{%action%}' => self::$locale['forum_0510'], '{%points%}' => format_word(self::$forum_settings['points_to_upvote'], self::$locale['fmt_points'])]), 'viewthread.php');
                                }
                                break;
                            case 'votedown':
                                if (fusion_get_userdata('user_reputation') >= self::$forum_settings['points_to_downvote'] && $thread_data['post_author'] !== fusion_get_userdata('user_id')) {
                                    $vote_result = dbquery($vote_query, $vote_bind);
                                    if (dbrows($vote_result)) {
                                        $vote = dbarray($vote_result);
                                        // I have voted, I'm removing my vote
                                        dbquery("DELETE FROM ".DB_FORUM_VOTES." WHERE vote_id=:vote_id", [':vote_id' => $vote['vote_id']]);
                                        // Remove log points
                                        dbquery("DELETE FROM ".DB_FORUM_USER_REP." WHERE rep_id=:rep_id", [':rep_id' => $vote['rep_id']]);
                                        // remove points from the post author
                                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:post_author_id", [
                                            ':post_author_id' => $thread_data['post_author'],
                                            ':points'         => self::$forum_settings['downvote_points']
                                        ]);
                                        addNotice('success', self::$locale['forum_0517'], 'viewthread.php');
                                    } else {
                                        // I have not yet voted, I'm downvoting.
                                        $d['vote_points'] = -1;
                                        $d['points_gain'] = -self::$forum_settings['downvote_points'];
                                        dbquery_insert(DB_FORUM_VOTES, $d, 'save');
                                        dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                                        addNotice('success', self::$locale['forum_0518'], 'viewthread.php');
                                    }
                                    redirect(self::$default_redirect_link);
                                } else {
                                    addNotice('danger', strtr(self::$locale['forum_0519'], [
                                        '{%action%}' => self::$locale['forum_0511'],
                                        '{%points%}' => format_word(self::$forum_settings['points_to_downvote'], self::$locale['fmt_points'])]), 'viewthread.php');
                                }
                                break;
                            default:
                                redirect(FORUM.'index.php');
                        }
                    } else {
                        // you cannot upvote or downvote on your own post.
                        addNotice('danger', self::$locale['forum_0802'], 'viewthread.php');
                    }
                } else {
                    addNotice('danger', self::$locale['forum_0520a'], 'viewthread.php');
                }
                // print_p(self::$default_redirect_link);
                redirect(self::$default_redirect_link);

            } else {
                redirect(FORUM.'index.php');
            }
        } else {
            redirect(FORUM.'index.php');
        }
    }
}
