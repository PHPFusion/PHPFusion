<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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

        $user_id = fusion_get_userdata('user_id');
        $user_rep = fusion_get_userdata('user_reputation');
        $thread_id = get('thread_id', FILTER_VALIDATE_INT);
        $post_id = get('post_id', FILTER_VALIDATE_INT);

        // I'm voting. so I need the vote id.
        $thread_data = dbarray(dbquery("SELECT
              p.post_id, p.post_author,
              t.thread_id, t.forum_id, t.thread_lastpostid, t.thread_postcount, t.thread_subject, t.thread_locked,
              f.forum_lock, f.forum_post_ratings, f.forum_mods, f.forum_type
              FROM ".DB_FORUM_POSTS." p
              INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
              INNER JOIN ".DB_FORUMS." f ON f.forum_id=p.forum_id
              WHERE p.post_id=:pid AND p.thread_id=:tid",
            [
                ':tid'  => $thread_id,
                ':pid'    => $post_id,
            ]
        ));

        if (!empty($thread_data)) {
            Moderator::defineForumMods($thread_data);
            // I can upvote as many post but each post only once.
            $thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];
            $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');

            if ($this->checkForumAccess($forum_index, $_GET['forum_id'], $_GET['thread_id'])) {
                $d = [
                    'forum_id'       => $thread_data['forum_id'],
                    'thread_id'      => $thread_data['thread_id'],
                    'post_id'        => $thread_data['post_id'],
                    'vote_user'      => $user_id,
                    'voter_id'       => $user_id,
                    'user_id'        => $thread_data['post_author'],
                    'vote_datestamp' => time(),
                    'datestamp'      => time(),
                ];

                if (iMOD || (checkgroup($thread_data['forum_post_ratings']) &&
                        $thread_data['forum_lock'] == FALSE && $thread_data['thread_locked'] == FALSE)) {

                    if ($thread_data['post_author'] !== $user_id ) {

                        $vote_query = "
                        SELECT v.*, r.*
                        FROM ".DB_FORUM_VOTES." v
                        INNER JOIN ".DB_FORUM_USER_REP." r ON v.post_id=r.post_id AND v.vote_user=r.voter_id
                        WHERE v.vote_user=:my_id AND v.post_id=:post_id
                        ";

                        $vote_bind = [
                            ':my_id'   => $user_id,
                            ':post_id' => $thread_data['post_id']
                        ];

                        $vote_result = dbquery($vote_query, $vote_bind);

                        $vote_type = get('post');

                        switch ($vote_type) {
                            case 'voteup':
                                if ($user_rep >= self::$forum_settings['points_to_upvote']) {
                                    $vote = dbarray($vote_result);
                                    if (!empty($vote['vote_points']) && $vote['vote_points'] !== '-1') {
                                        // I have voted, I'm removing my vote
                                        dbquery("DELETE FROM ".DB_FORUM_VOTES." WHERE vote_id=:vote_id", [':vote_id' => $vote['vote_id']]);
                                        // Remove log points
                                        dbquery("DELETE FROM ".DB_FORUM_USER_REP." WHERE rep_id=:rep_id", [':rep_id' => $vote['rep_id']]);
                                        // remove points from the post author
                                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation-:points WHERE user_id=:post_author_id", [
                                            ':post_author_id' => $thread_data['post_author'],
                                            ':points'         => self::$forum_settings['upvote_points']
                                        ]);
                                        addnotice('success', self::$locale['forum_0515'], 'viewthread.php');
                                    } else {
                                        // I have not yet voted, I'm upvoting.
                                        $d['vote_points'] = 1;
                                        $d['points_gain'] = self::$forum_settings['upvote_points'];

                                        if (!empty($vote['vote_points']) && $vote['vote_points'] == '-1') {
                                            dbquery("UPDATE ".DB_FORUM_VOTES." SET vote_points=:points WHERE vote_id=:voteid AND vote_user=:myid", [
                                                ':voteid' => $vote['vote_id'],
                                                ':points' => $d['vote_points'],
                                                ':myid'   => $user_id
                                            ]);

                                            dbquery("UPDATE ".DB_FORUM_USER_REP." SET points_gain=:points WHERE rep_id=:repid AND voter_id=:myid", [
                                                ':repid'  => $vote['rep_id'],
                                                ':points' => $d['points_gain'],
                                                ':myid'   => $user_id
                                            ]);
                                        } else {
                                            dbquery_insert(DB_FORUM_VOTES, $d, 'save');
                                            dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                                        }

                                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:post_author_id", [
                                            ':post_author_id' => $thread_data['post_author'],
                                            ':points'         => self::$forum_settings['upvote_points']
                                        ]);

                                        addnotice('success', self::$locale['forum_0516'], 'viewthread.php');
                                    }
                                    redirect(self::$default_redirect_link);
                                } else {
                                    addnotice('danger', strtr(self::$locale['forum_0519'], ['{%action%}' => self::$locale['forum_0510'], '{%points%}' => format_word(self::$forum_settings['points_to_upvote'], self::$locale['fmt_points'])]), 'viewthread.php');
                                }
                                break;

                            case 'votedown':
                                if ($user_rep >= self::$forum_settings['points_to_downvote'] && $thread_data['post_author'] !== $user_id) {
                                    $vote = dbarray($vote_result);
                                    if (!empty($vote['vote_points']) && $vote['vote_points'] !== '1') {
                                        // I have voted, I'm removing my vote
                                        dbquery("DELETE FROM ".DB_FORUM_VOTES." WHERE vote_id=:vote_id", [':vote_id' => $vote['vote_id']]);
                                        // Remove log points
                                        dbquery("DELETE FROM ".DB_FORUM_USER_REP." WHERE rep_id=:rep_id", [':rep_id' => $vote['rep_id']]);
                                        // remove points from the post author
                                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:post_author_id", [
                                            ':post_author_id' => $thread_data['post_author'],
                                            ':points'         => self::$forum_settings['downvote_points']
                                        ]);
                                        addnotice('success', self::$locale['forum_0517'], 'viewthread.php');
                                    } else {
                                        // I have not yet voted, I'm downvoting.
                                        $d['vote_points'] = -1;
                                        $d['points_gain'] = -self::$forum_settings['downvote_points'];

                                        if (!empty($vote['vote_points']) && $vote['vote_points'] == '1') {
                                            dbquery("UPDATE ".DB_FORUM_VOTES." SET vote_points=:points WHERE vote_id=:voteid AND vote_user=:myid", [
                                                ':voteid' => $vote['vote_id'],
                                                ':points' => $d['vote_points'],
                                                ':myid'   => $user_id
                                            ]);

                                            dbquery("UPDATE ".DB_FORUM_USER_REP." SET points_gain=:points WHERE rep_id=:repid AND voter_id=:myid", [
                                                ':repid'  => $vote['rep_id'],
                                                ':points' => $d['points_gain'],
                                                ':myid'   => $user_id
                                            ]);
                                        } else {
                                            dbquery_insert(DB_FORUM_VOTES, $d, 'save');
                                            dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                                        }

                                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation-:points WHERE user_id=:post_author_id", [
                                            ':post_author_id' => $thread_data['post_author'],
                                            ':points'         => self::$forum_settings['downvote_points']
                                        ]);

                                        addnotice('success', self::$locale['forum_0518'], 'viewthread.php');
                                    }
                                    redirect(self::$default_redirect_link);

                                } else {
                                    addnotice('danger', strtr(self::$locale['forum_0519'], [
                                        '{%action%}' => self::$locale['forum_0511'],
                                        '{%points%}' => format_word(self::$forum_settings['points_to_downvote'], self::$locale['fmt_points'])]), 'viewthread.php');
                                }
                                break;
                            default:
                                //addNotice('danger', 'Invalid vote actions');
                                redirect(FORUM.'index.php');
                        }
                    } else {
                        // you cannot upvote or downvote on your own post.
                        addnotice('danger', self::$locale['forum_0802'], 'viewthread.php');
                    }
                } else {
                    // this one does not exist.
                    addnotice('danger', self::$locale['forum_0529a'], 'viewthread.php');
                }
                // print_p(self::$default_redirect_link);
                redirect(self::$default_redirect_link);

            } else {
                //addNotice('danger', 'You do not have access to this thread.');
                redirect(FORUM.'index.php');
            }
        } else {
            //addNotice('danger', 'Thread does not exist');
            redirect(FORUM.'index.php');
        }
    }
}
