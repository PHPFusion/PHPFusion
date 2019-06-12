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
namespace PHPFusion\Forums\Threads;

use PHPFusion\Forums\ForumServer;

/**
 * Class ForumMood
 *
 * @package PHPFusion\Forums\Threads
 */
class Forum_Bounty extends ForumServer {

    /**
     * Permissions for Forum Poll
     *
     * @var array
     */
    private static $permissions = [];
    private static $data = [];

    public function render_bounty_form($edit = FALSE) {
        $bounty_description = '';
        $bounty_points = 50;
        $points = '';
        $locale = fusion_get_locale("", FORUM_LOCALE);
        // In order to prevent reputation point laundering, only author can start the bounty
        if ($edit ? self::get_bounty_permissions('can_edit_bounty') : self::get_bounty_permissions('can_start_bounty')) {
            $default = 50;
            for ($i = 1; $i <= 10; $i++) {
                $points[$i * $default] = format_word($i * $default, $locale['forum_2015']);
            }
            if (isset($_POST['save_bounty'])) {
                $bounty_description = form_sanitizer($_POST['bounty_description'], '', 'bounty_description');
                if (\defender::safe()) {
                    if ($edit) {
                        dbquery('UPDATE '.DB_FORUM_THREADS.' SET thread_bounty_description=:thread_bounty_description WHERE thread_id=:thread_id',
                            [
                                ':thread_bounty_description' => $bounty_description,
                                ':thread_id'                 => self::$data['thread_id']
                            ]
                        );
                    } else {
                        $bounty_points = form_sanitizer(isset($_POST['bounty_points']) ? $_POST['bounty_points'] : 0, $default, 'bounty_points');
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

            $bounty_field['openform'] = openform('set_bountyfrm', 'post', FUSION_REQUEST, ['class' => 'spacer-xs']);
            $bounty_field['closeform'] = closeform();
            $bounty_field['bounty_description'] = form_textarea('bounty_description', $locale['forum_2016'], $bounty_description, ['type' => 'bbcode']);
            $bounty_field['bounty_select'] = (!$edit ? form_select('bounty_points', $locale['forum_2017'], $bounty_points, ['options' => $points]) : '');
            $bounty_field['bounty_button'] = form_button('save_bounty', $locale['forum_2018'], $locale['forum_2018'], ['class' => 'btn-primary']);
            $info = [
                'title'       => $locale['forum_2014'],
                'description' => $locale['forum_2000'].self::$data['thread_subject'],
                'field'       => $bounty_field
            ];
            display_forum_bountyform($info);
        } else {
            redirect(FORUM.'index.php');
        }
    }

    public function award_bounty() {
        // via postify
        if (self::get_bounty_permissions('can_award_bounty')) {
            if (isset(self::$post_data['post_items'][$_GET['post_id']])) {

                $post_data = self::$post_data['post_items'][$_GET['post_id']];

                if ($post_data['post_author'] !== fusion_get_userdata('user_id')) {
                    // give the user the points.
                    dbquery("UPDATE ".DB_USERS." SET user_reputation=user_reputation+:points WHERE user_id=:user_id",
                        [
                            ':points'  => self::$data['thread_bounty'],
                            ':user_id' => $post_data['post_author'],
                        ]
                    );
                    // log the points change
                    $d = [
                        'post_id'     => $post_data['post_id'],
                        'thread_id'   => $post_data['thread_id'],
                        'forum_id'    => $post_data['forum_id'],
                        'points_gain' => self::$data['thread_bounty'],
                        'voter_id'    => fusion_get_userdata('user_id'),
                        'user_id'     => $post_data['post_author'],
                    ];
                    dbquery_insert(DB_FORUM_USER_REP, $d, 'save');
                    $title = self::$locale['forum_4105'];
                    $message = strtr(self::$locale['forum_4106'], ['{%thread_link%}' => "[url=".fusion_get_settings('siteurl')."infusions/forum/viewthread.php?thread_id=".self::$data['thread_id']."]".self::$data['thread_subject']."[/url]"]);
                    send_pm($post_data['post_author'], 0, $title, stripinput($message));
                    dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_bounty=:bounty, thread_bounty_description=:desc, thread_bounty_user=:user, thread_bounty_start=:start WHERE thread_id=:thread_id",
                        [
                            ':bounty'    => 0,
                            ':desc'      => '',
                            ':user'      => 0,
                            ':start'     => 0,
                            ':thread_id' => $post_data['thread_id']
                        ]);
                    redirect(FORUM.'postify.php?post=award&amp;error=0&amp;forum_id='.$post_data['forum_id'].'&amp;thread_id='.$post_data['thread_id'].'&amp;post_id='.$post_data['post_id']);
                }
                redirect(FORUM.'postify.php?post=award&amp;error=7&amp;forum_id='.$post_data['forum_id'].'&amp;thread_id='.$post_data['thread_id'].'&amp;post_id='.$post_data['post_id']);
            }
        }
    }

    // What happens when bounty ends?
    public function display_bounty() {
        $html = '';
        if (self::$data['thread_bounty']) {
            $user = fusion_get_user(self::$data['thread_bounty_user']);
            $html = "<div class='list-group-item-info p-15'>\n";
            $html .= (self::get_bounty_permissions('can_edit_bounty') ? "<span class='spacer-xs'><a href='".FORUM."viewthread.php?action=editbounty&amp;thread_id=".$_GET['thread_id']."'>".self::$locale['forum_4100']."</a></span>" : '');
            $bounty_end = self::$bounty_end - TIME;
            $html .= "<h4>".strtr(self::$locale['forum_4101'], [
                    '{%points%}'       => "<span class='label label-primary'>+".format_word(self::$data['thread_bounty'], self::$locale['fmt_points'])."</span>",
                    '{%profile_link%}' => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                    '{%countdown%}'    => countdown($bounty_end)
                ])."</h4>\n";
            $html .= self::$locale['forum_4102'];
            $html .= "<p class='spacer-xs text-dark'>".self::$data['thread_bounty_description']."</strong>\n</p>";
            $html .= "</div>";
        }

        return $html;
    }

    private static $bounty_end = '';
    private static $locale = [];
    private static $post_data = [];

    /**
     * Object
     *
     * @param array $thread_info
     */
    public function __construct(array $thread_info) {
        self::set_bounty_permissions($thread_info['permissions']);
        self::set_thread_data($thread_info['thread']);
        self::set_thread_post_data($thread_info);
        self::$locale = fusion_get_locale('', FORUM_LOCALE);
    }

    private static function set_thread_post_data(array $post_data) {
        self::$post_data = $post_data;
    }

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
