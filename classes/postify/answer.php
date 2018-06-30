<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: answer.php
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

use PHPFusion\BreadCrumbs;
use PHPFusion\Forums\Moderator;

/**
 * Class Postify_Answer
 *
 * @status  Stable
 * @package PHPFusion\Forums\Postify
 */
class Postify_Answer extends Forum_Postify {

    public function execute() {
        // no need for permissions.
        $thread_data = dbarray(dbquery("
        SELECT t.thread_id, t.forum_id, t.thread_lastpostid, t.thread_postcount, t.thread_subject, p.post_id, p.post_author, p.post_answer,
        f.forum_mods, t.thread_answered, t.thread_author
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." f ON f.forum_id=t.forum_id
        INNER JOIN ".DB_FORUM_POSTS." p ON p.thread_id = t.thread_id
        WHERE t.thread_id=:thread_id AND p.post_id=:post_id",
                [
                    ':thread_id' => $_GET['thread_id'],
                    ':post_id'   => $_GET['post_id']
                ]
            )
        );
        if (!empty($thread_data)) {
            Moderator::define_forum_mods($thread_data);

            $title = '';
            $description = '';

            $thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];
            // if this is an author or is a forum moderator
            if (($thread_data['thread_author'] == fusion_get_userdata('user_id') || iMOD)) {
                add_to_title(self::$locale['global_201'].self::$locale['forum_4001']);
                BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_4001']]);

                // Accepting the answer
                // 3 scenarios
                // 1 - thread has been answered -- if current post id is not an answer, means moved answer.
                // 3. thread has been answered -- if current post is an answer, means remove answer.
                // 2 - thread has not been answered -- selecting is acccepting answer

                if ($thread_data['thread_answered']) {

                    // If post is an answer, remove the answer, and refund the points.
                    if ($thread_data['post_answer']) {

                        // Refunding points
                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation-:points WHERE user_id=:user_id", [
                            ':points'  => self::$forum_settings['answering_points'],
                            ':user_id' => $thread_data['post_author']
                        ]);

                        dbquery("DELETE FROM ".DB_FORUM_USER_REP." WHERE thread_id=:thread_id AND voter_id=:voter_id AND user_id=:user_id AND rep_answer=:answer",
                            [
                                //':post_id'     => $thread_data['post_id'],
                                ':thread_id' => $thread_data['thread_id'],
                                //':forum_id'    => $thread_data['forum_id'],
                                //':points_gain' => self::$forum_settings['answering_points'],
                                ':voter_id'  => fusion_get_userdata('user_id'),
                                ':user_id'   => $thread_data['post_author'],
                                ':answer'    => 1,
                            ]
                        );

                        // post item is the answer post_answer
                        dbquery("UPDATE ".DB_FORUM_POSTS." SET post_answer=:answer, post_locked=:locked WHERE post_id=:post_id",
                            [
                                ':answer'  => 0,
                                ':locked'  => 0,
                                ':post_id' => $thread_data['post_id']
                            ]
                        );
                        // update the thread
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_answered=:answer WHERE thread_id=:thread_id",
                            [
                                ':answer'    => 0,
                                ':thread_id' => $thread_data['thread_id']
                            ]
                        );

                        $title = self::$locale['forum_4004'];
                        $description = self::$locale['forum_4005'];

                    } else {

                        $c_result = dbquery("
                            SELECT r.rep_id, p.post_id, p.thread_id, p.post_author
                            FROM ".DB_FORUM_POSTS." p
                            LEFT JOIN  ".DB_FORUM_USER_REP." r ON r.post_id = p.post_id AND r.rep_answer=:answer AND r.user_id=:user_id
                            WHERE  p.thread_id=:thread_id AND p.post_answer=:answer01",
                            [
                                ':thread_id' => $thread_data['thread_id'],
                                ':user_id'   => $thread_data['post_author'],
                                ':answer'    => 1,
                                ':answer01'  => 1,
                            ]
                        );
                        if (dbrows($c_result)) {

                            $c_data = dbarray($c_result);

                            if ($c_data['post_author'] !== fusion_get_userdata('user_id')) {

                                // remove points from the previous user
                                dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation-:points WHERE user_id=:user_id", [
                                    ':points'  => self::$forum_settings['answering_points'],
                                    ':user_id' => $c_data['post_author']
                                ]);
                                // remove the current answer record
                                dbquery("DELETE FROM ".DB_FORUM_USER_REP." WHERE rep_id=:rep_id", [':rep_id' => $c_data['rep_id']]);
                            }

                            dbquery("UPDATE ".DB_FORUM_POSTS." SET post_answer=:answer, post_locked=:locked WHERE post_id=:post_id",
                                [
                                    ':answer'  => 0,
                                    ':locked'  => 0,
                                    ':post_id' => $c_data['post_id']
                                ]
                            );
                            // update the thread
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_answered=:answer WHERE thread_id=:thread_id",
                                [
                                    ':answer'    => 1,
                                    ':thread_id' => $c_data['thread_id']
                                ]
                            );

                            // Give points to the current user if its not self.
                            if ($thread_data['post_author'] !== fusion_get_userdata('user_id')) {
                                dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:user_id", [
                                    ':points'  => self::$forum_settings['answering_points'],
                                    ':user_id' => $thread_data['post_author']
                                ]);
                                $d = [
                                    'post_id'     => $thread_data['post_id'],
                                    'thread_id'   => $thread_data['thread_id'],
                                    'forum_id'    => $thread_data['forum_id'],
                                    'points_gain' => self::$forum_settings['answering_points'],
                                    'voter_id'    => fusion_get_userdata('user_id'),
                                    'user_id'     => $thread_data['post_author'],
                                    'rep_answer'  => 1
                                ];
                                dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                            }

                            // post item is the answer post_answer
                            dbquery("UPDATE ".DB_FORUM_POSTS." SET post_answer=:answer, post_locked=:locked WHERE post_id=:post_id",
                                [
                                    ':answer'  => 1,
                                    ':locked'  => 1,
                                    ':post_id' => $thread_data['post_id']
                                ]
                            );
                            // update the thread
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_answered=:answer WHERE thread_id=:thread_id",
                                [
                                    ':answer'    => 1,
                                    ':thread_id' => $thread_data['thread_id']
                                ]
                            );

                            $title = self::$locale['forum_4006'];
                            $description = self::$locale['forum_4007'];
                        }
                    }
                } else {

                    if ($thread_data['post_author'] !== fusion_get_userdata('user_id')) {
                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:user_id", [
                            ':points'  => self::$forum_settings['answering_points'],
                            ':user_id' => $thread_data['post_author']
                        ]);
                        // entry for answers
                        $d = [
                            'post_id'     => $thread_data['post_id'],
                            'thread_id'   => $thread_data['thread_id'],
                            'forum_id'    => $thread_data['forum_id'],
                            'points_gain' => self::$forum_settings['answering_points'],
                            'voter_id'    => fusion_get_userdata('user_id'),
                            'user_id'     => $thread_data['post_author'],
                            'rep_answer'  => 1
                        ];
                        dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                    }

                    // post item is the answer post_answer
                    dbquery("UPDATE ".DB_FORUM_POSTS." SET post_answer=:answer, post_locked=:locked WHERE post_id=:post_id",
                        [
                            ':answer'  => 1,
                            ':locked'  => 1,
                            ':post_id' => $thread_data['post_id']
                        ]
                    );
                    // update the thread
                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_answered=:answer WHERE thread_id=:thread_id",
                        [
                            ':answer'    => 1,
                            ':thread_id' => $thread_data['thread_id']
                        ]
                    );
                    $title = self::$locale['forum_4002'];
                    $description = self::$locale['forum_4003'];

                }
            }

            render_postify(
                [
                    'title'       => $title,
                    'description' => $description,
                    'error'       => $this->get_postify_error_message(),
                    'link'        => $this->get_postify_uri()
                ]
            );
        } else {
            redirect(FORUM.'index.php');
        }
    }
}
