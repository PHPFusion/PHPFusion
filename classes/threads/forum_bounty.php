<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads/bounty.php
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

namespace PHPFusion\Infusions\Forum\Classes\Threads;

use PHPFusion\Infusions\Forum\Classes\Forum_Server;

/**
 * Class ForumMood
 *
 * @package PHPFusion\Forums\Threads
 */
class Forum_Bounty extends Forum_Server {

    /**
     * Permissions for forum bounty
     *
     * @var array
     */
    private static $permissions = [];

    /**
     * @var array
     */
    private static $data = [];
    private static $bounty_end = '';
    private static $locale = [];
    private static $post_data = [];

    /**
     * Forum_Bounty constructor.
     *
     * @param array $thread_info
     */
    public function __construct(array $thread_info) {
        self::set_bounty_permissions($thread_info['permissions']);

        self::set_thread_data($thread_info['thread']);

        self::set_thread_post_data($thread_info);

        self::$locale = fusion_get_locale('', FORUM_LOCALE);
    }

    /**
     * Displays the bounty start form
     * @param bool $edit
     *
     * @return string
     * @throws \ReflectionException
     */
    public function render_bounty_form($edit = FALSE) {
        $bounty_description = '';
        $bounty_points = 50;
        $points = [];
        // In order to prevent reputation point laundering, only author can start the bounty
        if ($edit ? self::get_bounty_permissions('can_edit_bounty') : self::get_bounty_permissions('can_start_bounty')) {

            $default = 50;

            $locale = fusion_get_locale('', FORUM_LOCALE);

            $total_user_rep = fusion_get_userdata('user_reputation');

            for ($i = 1; $i <= 3; $i++) {
                $current = $i * $default;
                $points[$current] = format_word($current, $locale['forum_2015']);
                if ($total_user_rep > $current)
                    continue;
            }

            if (post('save_bounty')) {

                $bounty_description = sanitizer('bounty_description', '', 'bounty_description');

                if (\Defender::safe()) {

                    if ($edit) {
                        dbquery('UPDATE '.DB_FORUM_THREADS.' SET thread_bounty_description=:desc WHERE thread_id=:thread_id',
                            [
                                ':desc'      => $bounty_description,
                                ':thread_id' => self::$data['thread_id']
                            ]
                        );
                    } else {
                        $bounty_points = sanitizer('bounty_points', $default, 'bounty_points');

                        $point_bal = fusion_get_userdata('user_reputation') - $bounty_points;

                        $user_id = fusion_get_userdata('user_id');

                        dbquery('UPDATE '.DB_USERS.' SET user_reputation=:point_balance WHERE user_id=:my_id', [
                            ':point_balance' => $point_bal,
                            ':my_id'         => $user_id
                        ]);

                        $bounty_arr = [
                            'thread_id'                 => self::$data['thread_id'],
                            'thread_bounty'             => $bounty_points,
                            'thread_bounty_user'        => fusion_get_userdata('user_id'),
                            'thread_bounty_description' => $bounty_description,
                            'thread_bounty_start'       => TIME,
                        ];

                        dbquery_insert(DB_FORUM_THREADS, $bounty_arr, 'update');
                    }

                    redirect(FORUM.'postify.php?post=bounty&error=0&forum_id='.self::$data['forum_id'].'&thread_id='.self::$data['thread_id']);
                }
            }

            if ($edit) {
                $bounty_description = self::$data['thread_bounty_description'];
            }

            $bounty_field['openform'] = openform('forum_bountyfrm', 'post');
            $bounty_field['closeform'] = closeform();
            $bounty_field['bounty_description'] = form_textarea('bounty_description', $locale['forum_2016'], $bounty_description, ['type' => 'bbcode']);
            $bounty_field['bounty_select'] = (!$edit ? form_select('bounty_points', $locale['forum_2017'], $bounty_points, [
                'options'          => $points,
                'select2_disabled' => TRUE,
            ]) : '');
            $bounty_field['bounty_button'] = form_button('save_bounty', $locale['forum_2018'], $locale['forum_2018'], ['class' => 'btn-primary']);
            $info = [
                'title'       => $locale['forum_2014'],
                'description' => $locale['forum_2000'].self::$data['thread_subject'],
                'field'       => $bounty_field
            ];
            return display_forum_bountyform($info);
        } else {
            redirect(FORUM.'index.php');
        }
    }

    /**
     * SQL action
     * Award bounty action
     *
     * @throws \Exception
     */
    public function award_bounty() {
        // via postify
        if (self::get_bounty_permissions('can_award_bounty')) {

            $post_id = get('post_id', FILTER_VALIDATE_INT);

            if ($post_id) {

                if (isset(self::$post_data['post_items'][$post_id])) {

                    $user_id = fusion_get_userdata('user_id');

                    $post_data = self::$post_data['post_items'][$post_id];

                    if ($post_data['post_author'] !== $user_id) {

                        // give the user the points.
                        dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:uid",
                            [
                                ':points'  => self::$data['thread_bounty'],
                                ':uid' => intval($post_data['post_author']),
                            ]
                        );

                        // log the points change
                        $d = [
                            'post_id'     => intval($post_data['post_id']),
                            'thread_id'   => intval($post_data['thread_id']),
                            'forum_id'    => intval($post_data['forum_id']),
                            'points_gain' => self::$data['thread_bounty'],
                            'rep_answer'  => 1,
                            'voter_id'    => intval($user_id),
                            'user_id'     => intval($post_data['post_author']),
                            'datestamp'   => TIME
                        ];

                        dbquery_insert(DB_FORUM_USER_REP, $d, 'save');

                        $message = strtr(self::$locale['forum_4106'], ['{%thread_link%}' => "[url=".fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".self::$data['thread_id']."]".self::$data['thread_subject']."[/url]"]);
                        send_pm($post_data['post_author'], 0, self::$locale['forum_4105'], stripinput($message));

                        // set the post as answered
                        dbquery("UPDATE ".DB_FORUM_POSTS." SET post_answer=1 WHERE post_id=:pid", [':pid' => intval($post_data['post_id']) ]);

                        // update thread as answered
                        dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_answered=:ta, thread_bounty=:bounty, thread_bounty_description=:desc, thread_bounty_user=:user, thread_bounty_start=:start WHERE thread_id=:thread_id",
                            [
                                ':ta'        => 1,
                                ':bounty'    => 0,
                                ':desc'      => '',
                                ':user'      => 0,
                                ':start'     => 0,
                                ':thread_id' => intval($post_data['thread_id'])
                            ]);
                        redirect(FORUM.'postify.php?post=award&amp;error=0&amp;forum_id='.$post_data['forum_id'].'&amp;thread_id='.$post_data['thread_id'].'&amp;post_id='.$post_data['post_id']);
                    }
                    redirect(FORUM.'postify.php?post=award&amp;error=7&amp;forum_id='.$post_data['forum_id'].'&amp;thread_id='.$post_data['thread_id'].'&amp;post_id='.$post_data['post_id']);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function displayForumBounty() {
        $html = '';
        if (self::$data['thread_bounty']) {
            $user = fusion_get_user(self::$data['thread_bounty_user']);

            $html .= (self::get_bounty_permissions('can_edit_bounty') ? "<div class='text-right spacer-xs'><a href='".FORUM."viewthread.php?action=editbounty&amp;thread_id=".get('thread_id', FILTER_VALIDATE_INT)."'><i class='fas fa-pencil-alt m-r-5'></i>".self::$locale['forum_4100']."</a></div>" : '');
            $html .= "<div class='well'>\n";
            $bounty_end = self::$bounty_end - TIME;
            $html .= "<p>".strtr(self::$locale['forum_4101'], [
                    '{%points%}'       => "<span class='label label-default strong m-l-5 m-r-5'>+".format_word(self::$data['thread_bounty'], self::$locale['fmt_points'])."</span>",
                    '{%profile_link%}' => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                    '{%countdown%}'    => countdown($bounty_end)
                ])."</p>\n";
            $html .= self::$locale['forum_4102'].'<br/>';
            $html .= "<p class='strong'>".self::$data['thread_bounty_description']."</p>\n";
            $html .= "</div>";
        }

        return $html;
    }

    /**
     * @param array $post_data
     */
    private static function set_thread_post_data(array $post_data) {
        self::$post_data = $post_data;
    }

    /**
     * @param array $thread_data
     */
    private static function set_thread_data(array $thread_data) {

        self::$data = $thread_data;

        // Cronjob on this thread
        self::$bounty_end = self::$data['thread_bounty_start'] + (7 * 24 * 3600);

        if (TIME > self::$bounty_end) {

            if (self::$data['thread_bounty']) { // have a bounty
                // find the highest post
                $result = dbquery("
                    SELECT v.post_id, p.post_author, u.user_id, u.user_name, u.user_status
                    FROM ".DB_FORUM_VOTES." v
                    INNER JOIN ".DB_FORUM_POSTS." p.post_id=v.post_id
                    INNER JOIN ".DB_USERS." u.user_id=p.post_author
                    WHERE v.thread_id=:thread_id AND v.vote_points>0 ORDER BY v.vote_points DESC LIMIT 0,1",
                    [':thread_id' => self::$data['thread_id']]
                );
                if (dbrows($result)) {
                    $data = dbarray($result);
                    // with the post id, we give the post user.
                    dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:post_author", [
                            ':points'      => floor(self::$data['thread_bounty'] / 2),
                            ':post_author' => $data['post_author']
                        ]
                    );
                    $subject = strtr(self::$locale['forum_4103'], ['{%user_name%}' => $data['user_name']]);
                    $message = strtr(self::$locale['forum_4014'], ['{%link_start%}' => "<a href='".FORUM."viewthread.php?thread_id=".self::$data['thread_id']."'>", '{%link_end%}' => "</a>"]);
                    send_pm(self::$data['thread_bounty_user'], 0, $subject, stripinput($message));

                    $subject = self::$locale['forum_4105'];
                    $message = strtr(self::$locale['forum_4106'], ['{%thread_link%}' => "[url=".fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".self::$data['thread_id']."]".self::$data['thread_subject']."[/url]"]);
                    send_pm($data['post_author'], 0, $subject, stripinput($message));
                }
                // consumes the bounty
                dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_bounty='', thread_bounty_description='', thread_bounty_start='0' WHERE thread_id=:thread_id",
                    [':thread_id' => self::$data['thread_id']]
                );
                // now anyone can post a bounty again.
            }
        }
    }

    /**
     * Set Permissions Settings
     *
     * @param array $thread_info
     */
    private static function set_bounty_permissions(array $thread_info) {
        self::$permissions = $thread_info;
    }

    /**
     * Fetches Permissions Settings
     *
     * @param $key
     *
     * @return bool
     */
    private static function get_bounty_permissions($key) {
        return (isset(self::$permissions[$key])) ? self::$permissions[$key] : FALSE;
    }
}
