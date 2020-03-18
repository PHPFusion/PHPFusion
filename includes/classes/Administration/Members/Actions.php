<?php

namespace PHPFusion\Administration\Members;
/**
 * Class Members_Action
 * All function are in the form of multiples user_id
 *
 * @package Administration\Members\Sub_Controllers
 */
class Actions extends Members {

    private $action_user_id = [];

    private $action = 0;

    private $users = [];

    private $cancel_link = '';

    /**
     * Action Script Configurations
     *
     * @var array
     */
    private $action_map = [
        parent::USER_BAN          => [
            'check_operator'       => '!==', // Make it fluid.
            'check_value'          => self::USER_BAN,
            'title'                => 'ME_500',
            'a_message'            => 'ME_550',
            'user_status_change'   => parent::USER_BAN,
            'user_status_log_func' => 'suspend_log',
            'reason'               => TRUE,
            'email'                => TRUE,
            'email_title'          => 'email_ban_subject',
            'email_message'        => 'email_ban_message',
        ],
        // OK
        parent::USER_REINSTATE    => [
            'check_operator'       => '>',
            'check_value'          => parent::USER_MEMBER,
            'title'                => 'ME_501',
            'a_message'            => 'ME_551',
            'user_status_change'   => parent::USER_MEMBER,
            'reason'               => TRUE,
            'user_status_log_func' => 'unsuspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_activate_subject',
            'email_message'        => 'email_activate_message',
        ],
        // OK
        parent::USER_SUSPEND      => [
            'check_operator'       => '!==',
            'check_value'          => self::USER_SUSPEND,
            'title'                => 'ME_503',
            'a_message'            => 'ME_553',
            'user_status_change'   => parent::USER_SUSPEND,
            'action_time'          => TRUE,
            'reason'               => TRUE,
            'user_status_log_func' => 'suspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_suspend_subject',
            'email_message'        => 'email_suspend_message',
        ],
        // OK
        parent::USER_SECURITY_BAN => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_SECURITY_BAN,
            'title'                => 'ME_504',
            'a_message'            => 'ME_554',
            'user_status_change'   => parent::USER_SECURITY_BAN,
            'user_status_log_func' => 'suspend_log',
            'email'                => TRUE,
            'email_title'          => 'email_secban_subject',
            'email_message'        => 'email_secban_message',
        ],
        // OK
        parent::USER_CANCEL       => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_CANCEL,
            'title'                => 'ME_505',
            'a_message'            => 'ME_555',
            'user_status_change'   => parent::USER_CANCEL,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log',
        ],
        // OK
        parent::USER_ANON         => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_ANON,
            'title'                => 'ME_506',
            'a_message'            => 'ME_556',
            'user_status_change'   => parent::USER_ANON,
            'action_time'          => TRUE,
            'user_status_log_func' => 'suspend_log'
        ],
        // OK
        parent::USER_DEACTIVATE   => [
            'check_operator'       => '!==',
            'check_value'          => parent::USER_DEACTIVATE,
            'title'                => 'ME_502',
            'a_message'            => 'ME_552',
            'user_status_change'   => parent::USER_DEACTIVATE,
            'user_status_log_func' => 'suspend_log',
            'action_time'          => TRUE,
            'email'                => TRUE,
            'email_title'          => 'email_deactivate_subject',
            'email_message'        => 'email_deactivate_message',
        ],
        'delete'                  => [
            'check_operator'       => '!==',
            'check_value'          => 'delete',
            'title'                => 'ME_454',
            'a_message'            => 'ME_455',
            'user_status_change'   => 'delete',
            'user_status_log_func' => '',
            'action_time'          => FALSE,
            'email'                => FALSE,
            'email_title'          => '',
            'email_message'        => '',
        ]
    ];

    /**
     * Set a user id
     *
     * @param array $value
     */
    public function set_userID(array $value = []) {
        $user_id = [];
        foreach ($value as $id) {
            if (isnum($id)) {
                $user_id[$id] = $id;
            }
        }
        $this->action_user_id = $user_id;
    }

    /**
     * Set an action for this user class - 1 for ban, etc
     *
     * @param $value
     */
    public function set_action($value) {
        $this->action = $value;
    }

    /**
     * Set an abort link
     *
     * @param $value
     */
    public function setCancelLink($value) {
        $this->cancel_link = $value;
    }

    public function getActionArr() {
        return $this->action_map;
    }

    public function execute() {
        $locale = fusion_get_locale();
        $form = '';
        $users_list = '';
        if (post('cancel')) {
            redirect($this->cancel_link ?: FUSION_REQUEST);
        }

        // Cache affected users
        $query = "SELECT user_id, user_name, user_avatar, user_email, user_level, user_password, user_status FROM ".DB_USERS." WHERE user_id IN (".implode(',', $this->action_user_id).") AND user_level > ".USER_LEVEL_SUPER_ADMIN." GROUP BY user_id";
        $result = dbquery($query);
        if (dbrows($result)) {
            while ($u_data = dbarray($result)) {
                if ($this->isUser($u_data['user_status'], $this->action_map[$this->action]['check_value'], $this->action_map[$this->action]['check_operator'])) {
                    $this->users[$u_data['user_id']] = $u_data;
                }
            }
        }

        if (!empty($this->users)) {
            $u_name = [];

            if (post('post_action')) {

                $settings = fusion_get_settings();
                $userdata = fusion_get_userdata();
                $reason = '';

                if (!empty($this->action_map[$this->action]['reason'])) {
                    $reason = sanitizer('reason', '', 'reason');
                }

                $duration = 0;
                if (!empty($this->action_map[$this->action]['action_time'])) {
                    $duration = sanitizer('duration', 1, 'duration');
                    $duration = ($duration * 86400) + TIME;
                }

                if (fusion_safe()) {

                    foreach ($this->users as $user_id => $u_data) {

                        dbquery("UPDATE ".DB_USERS." SET user_status=:user_status, user_actiontime=:action_time WHERE user_id=:user_id", [
                            ':user_status' => $this->action_map[$this->action]['user_status_change'],
                            ':action_time' => $duration,
                            ':user_id'     => $user_id
                        ]);

                        // Executes log
                        if (!empty($this->action_map[$this->action]['user_status_log_func'])) {
                            $log_value = ($this->action_map[$this->action]['user_status_log_func'] == 'suspend_log' ? $this->action : $u_data['user_status']);
                            $this->action_map[$this->action]['user_status_log_func']($user_id, $log_value, $reason);
                        }

                        // Email users
                        if (!empty($this->action_map[$this->action]['email'])) {
                            $email_locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/members_email.php');
                            $subject = strtr($email_locale[$this->action_map[$this->action]['email_title']],
                                [
                                    '[SITENAME]' => $settings['sitename']
                                ]
                            );
                            $message = strtr($email_locale[$this->action_map[$this->action]['email_message']],
                                [
                                    '[USER_NAME]'           => $u_data['user_name'],
                                    '[REASON]'              => $reason,
                                    '[SITENAME]'            => $settings['sitename'],
                                    '[ADMIN_USERNAME]'      => $userdata['user_name'],
                                    '[SITEUSERNAME]'        => $settings['siteusername'],
                                    '[DATE]'                => showdate('longdate', $duration),
                                    '[DEACTIVATION_PERIOD]' => $settings['deactivation_period'],
                                    '[REACTIVATION_LINK]'   => $settings['siteurl']."reactivate.php?user_id=".$u_data['user_id']."&code=".md5($duration.$u_data['user_password'])
                                ]
                            );

                            sendemail($u_data['user_name'], $u_data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
                        }

                        $u_name[] = $u_data['user_name'];
                    }

                    addNotice('success', sprintf($locale['ME_432'], implode(', ', $u_name), $locale[$this->action_map[$this->action]['a_message']]), 'all');

                    redirect(FUSION_REQUEST);
                }
            }

            if (!post('post_action') || !fusion_safe()) {
                $height = '45px';
                foreach ($this->users as $user_data) {
                    $users_list .= strtr($this->user_block_template(),
                        [
                            '{%user_avatar%}' => display_avatar($user_data, $height, '', '', ''),
                            '{%height%}'      => $height,
                            '{%user_name%}'   => $user_data['user_name']
                        ]
                    );
                }
                if (isset($this->action_map[$this->action]['action_time'])) {
                    $form .= form_text('duration', $locale['ME_435'], '', ['type' => 'number', 'append' => TRUE, 'append_value' => $locale['ME_436'], 'required' => TRUE, 'inner_width' => '120px']);
                }
                if (!empty($this->action_map[$this->action]['reason'])) {
                    $form .= form_textarea('reason', $locale['ME_433'], '', ['required' => TRUE, 'placeholder' => $locale['ME_434']]);
                }
                $form .= form_hidden('action', '', $this->action);
                // the user id is multiple
                foreach ($this->action_user_id as $user_id) {
                    $form .= form_hidden('user_id[]', '', $user_id);
                }
                $form .= form_button('post_action', $locale['update'], $this->action, ['class' => 'btn-primary']);
                $form .= form_button('cancel', $locale['cancel'], 'cancel');

                $modal = openmodal('uAdmin_modal', $locale[$this->action_map[$this->action]['title']].$locale['ME_413'], ['static' => TRUE]);
                $modal .= openform('uAdmin_frm', 'post', FUSION_REQUEST);
                $modal .= strtr($this->action_form_template(), [
                    '{%message%}'    => sprintf($locale['ME_431'], $locale[$this->action_map[$this->action]['a_message']]),
                    '{%users_list%}' => $users_list,
                    '{%form%}'       => $form,
                ]);
                $modal .= closeform();
                $modal .= closemodal();
                add_to_footer($modal);
            }

        } else {
            // addNotice('danger', $locale['ME_430']);
            redirect(clean_request('', ['step', 'uid', 'user_id'], FALSE));
        }
    }

    // this is the mapping for all other actions except delete

    /**
     * Checks user status against action map check value
     *
     * @param $var1
     * @param $var2
     * @param $case
     *
     * @return bool
     */
    public function isUser($var1, $var2, $case) {
        switch ($case) {
            case '>':
                return ($var1 > $var2);
                break;
            case '<':
                return ($var1 < $var2);
                break;
            case '==':
                return ($var1 == $var2);
                break;
            case '!==':
                return ($var1 !== $var2);
                break;
        }

        return FALSE;
    }

    private function user_block_template() {
        return "
        <div class='display-inline-block panel panel-default panel-body p-0'>\n
        <div class='pull-left m-r-10'>{%user_avatar%}</div>\n
        <div class='overflow-hide'>\n
        <span class='va' style='height:{%height%};'></span>\n
        <span class='va p-r-15'>\n<strong>{%user_name%}</strong>\n</span>\n
        </div>\n
        </div>\n
        ";
    }

    private function action_form_template() {
        return "
        <p><strong>{%message%}</strong></p>
        {%users_list%}
        <hr/>
        {%form%}
        ";
    }

    /**
     * @param $user_data
     */
    public function deleteUser($user_data) {
        $locale = fusion_get_locale();
        $user_name = $user_data['user_name'];
        $user_id = $user_data['user_id'];

        if (check_post('delete_user')) {
            $locale = fusion_get_locale();
            $result = dbquery("SELECT user_id, user_avatar, user_name FROM ".DB_USERS." WHERE user_id=:user_id AND user_level >:user_level",
                [
                    ':user_id'    => $user_id,
                    ':user_level' => USER_LEVEL_SUPER_ADMIN
                ]
            );
            $rows = dbrows($result);
            if ($rows != '0') {
                // Delete avatar
                $data = dbarray($result);
                $user_id = $data['user_id'];
                $user_name = $data['user_name'];

                if ($data['user_avatar'] != "" && file_exists(IMAGES."avatars/".$data['user_avatar'])) {
                    @unlink(IMAGES."avatars/".$data['user_avatar']);
                }

                /**
                 * @todo: Need to store user content reference column in a table for each infusions
                 */
                // Delete content
                dbquery("DELETE FROM ".DB_USERS." WHERE user_id=:user_id", [':user_id' => $user_id]);
                dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_name=:comment_name", [':comment_name' => $user_id]);
                dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_user=:rating_user", [':rating_user' => $user_id]);
                dbquery("DELETE FROM ".DB_SUSPENDS." WHERE suspended_user=:suspended_user", [':suspended_user' => $user_id]);
                dbquery("DELETE FROM ".DB_MESSAGES." WHERE message_to=:message_to OR message_from=:message_from", [':message_to' => $user_id, ':message_from' => $user_id]);

                if (defined('GALLERY_EXIST')) {
                    // Delete photos
                    $result = dbquery("SELECT album_id, photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_user=:photo_user", [':photo_user' => $user_id]);
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            $result = dbquery("DELETE FROM ".DB_PHOTOS." WHERE photo_user=:user_id", [':user_id' => $user_id]);
                            @unlink(IMAGES_G.$data['photo_filename']);
                            @unlink(IMAGES_G_T.$data['photo_thumb1']);
                            @unlink(IMAGES_G_T.$data['photo_thumb2']);
                        }
                    }
                }
                if (defined('ARTICLES_EXIST')) {
                    dbquery("DELETE FROM ".DB_ARTICLES." WHERE article_name=:article_name", [':article_name' => $user_id]);
                }
                if (defined('NEWS_EXIST')) {
                    dbquery("DELETE FROM ".DB_NEWS." WHERE news_name=:news_name", [':news_name' => $user_id]);
                }
                if (defined('MEMBER_POLL_PANEL_EXIST')) {
                    dbquery("DELETE FROM ".DB_POLL_VOTES." WHERE vote_user=:vote_user", [':vote_user' => $user_id]);
                }
                if (defined('FORUM_EXIST')) {
                    dbquery("DELETE FROM ".DB_FORUM_THREADS." WHERE thread_author=:thread_author", [':thread_author' => $user_id]);
                    dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE post_author=:post_author", [':post_author' => $user_id]);
                    dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE notify_user=:notify_user", [':notify_user' => $user_id]);
                    dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE forum_vote_user_id=:forum_vote_user_id", [':forum_vote_user_id' => $user_id]);
                    $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_lastuser=:thread_lastuser", [':thread_lastuser' => $user_id]);
                    if (dbrows($threads)) {
                        while ($thread = dbarray($threads)) {
                            // Update thread last post author, date and id
                            $last_thread_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id ORDER BY post_id DESC LIMIT 0,1", [':thread_id' => $thread['thread_id']]));
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpost=:thread_lastpost, thread_lastpostid=:thread_lastpostid, thread_lastuser=:thread_lastuser WHERE thread_id=:thread_id",
                                [
                                    ':thread_lastpost'   => $last_thread_post['post_datestamp'],
                                    ':thread_lastpostid' => $last_thread_post['post_id'],
                                    ':thread_lastuser'   => $last_thread_post['post_author'],
                                    ':thread_id'         => $thread['thread_id']
                                ]);
                            // Update thread posts count
                            $posts_count = dbcount("(post_id)", DB_FORUM_POSTS, "thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                            dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_postcount=:thread_postcount WHERE thread_id=:thread_id", [':thread_postcount' => $posts_count, ':thread_id' => $thread['thread_id']]);
                            // Update forum threads count and posts count
                            list($threadcount, $postcount) = dbarraynum(dbquery("SELECT COUNT(thread_id), SUM(thread_postcount) FROM ".DB_FORUM_THREADS." WHERE forum_id=:forum_id AND thread_lastuser=:thread_lastuser AND thread_hidden=:thread_hidden", [':forum_id' => $thread['forum_id'], ':thread_lastuser' => $user_id, ':thread_hidden' => '0']));
                            if (isnum($threadcount) && isnum($postcount)) {
                                dbquery("UPDATE ".DB_FORUMS." SET forum_postcount=:forum_postcount, forum_threadcount=:forum_threadcount WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                                    [
                                        ':forum_postcount'   => $postcount,
                                        ':forum_threadcount' => $threadcount,
                                        ':forum_id'          => $thread['forum_id'],
                                        ':forum_lastuser'    => $user_id
                                    ]);
                            }
                        }
                    }
                    $forums = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_lastuser=:forum_lastuser", [':forum_lastuser' => $user_id]);
                    if (dbrows($forums)) {
                        while ($forum = dbarray($forums)) {
                            // find the user one before the current user's post
                            $last_forum_post = dbarray(dbquery("SELECT post_id, post_author, post_datestamp FROM ".DB_FORUM_POSTS." WHERE forum_id=:forum_id ORDER BY post_id DESC LIMIT 0,1", [':forum_id' => $forum['forum_id']]));
                            dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost=:forum_lastpost, forum_lastuser=:forum_lastuser WHERE forum_id=:forum_id AND forum_lastuser=:forum_lastuser",
                                [
                                    ':forum_lastpost' => $last_forum_post['post_datestamp'],
                                    ':forum_id'       => $forum['forum_id'],
                                    ':forum_lastuser' => $user_id
                                ]);
                        }
                    }
                    // Delete all threads that has been started by the user.
                    $threads = dbquery("SELECT * FROM ".DB_FORUM_THREADS." WHERE thread_author=:thread_author", [':thread_author' => $user_id]);
                    if (dbrows($threads)) {
                        while ($thread = dbarray($threads)) {
                            // Delete the posts made by other users in threads started by deleted user
                            if ($thread['thread_postcount'] > 0) {
                                dbquery("DELETE FROM ".DB_FORUM_POSTS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                            }
                            // Delete polls in threads and their associated poll options and votes cast by other users in threads started by deleted user
                            if ($thread['thread_poll'] == 1) {
                                dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                                dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                                dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id=:thread_id", [':thread_id' => $thread['thread_id']]);
                            }
                        }
                    }
                    $count_posts = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
                    if (dbrows($count_posts)) {
                        while ($data = dbarray($count_posts)) {
                            // Update the posts count for all users
                            dbquery("UPDATE ".DB_USERS." SET user_posts=:user_posts WHERE user_id=:user_id", [':user_posts' => $data['num_posts'], ':user_id' => $data['post_author']]);
                        }
                    }
                }
                redirect(FUSION_SELF.fusion_get_aidlink());
            } else {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }

        echo "<div class='spacer-xs'><strong>".$locale['ME_454']."</strong> ".nl2br(sprintf($locale['ME_455'], "<strong>$user_name</strong>"))."</div>\n";
        echo openform('mod_form', 'post');
        echo "<div class='spacer-sm'>\n";
        echo form_button('delete_user', $locale['ME_456'], $locale['ME_456'], ['class' => 'btn-danger m-r-10']);
        echo form_button('cancel', $locale['cancel'], $locale['cancel']);
        echo "</div>\n";
        echo closeform();

        /*
         * if ($this->user_data['user_id']) {
            add_to_jquery("
            $('#delete_user').bind('click', function(e) {
            $('#warning_delete_user').modal('show');
            });
            $('#delete_user_verify').bind('keyup change paste', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if ( $(this).val() == '".$this->user_data['user_name']."' ) {
                    $('button#delete_user_confirm').removeAttr('disabled');
                    $('button#delete_user_confirm').removeClass('disabled');
                }
            });
            ");
            $modal = openmodal('warning_delete_user','Are you very sure?', ['button_id'=>'delete_user']);
            $modal .= openform('deleteUserFrm', 'post');
            $modal .= "<div>Please read the following very carefully.</div>";
            $modal .= "<div class='spacer-xs'>This action <strong>is irreversible</strong>. This will permanently delete the <strong>".$this->user_data['user_name']."</strong> user, posts, comments and all other associated contents.</div>";
            $modal .= form_text('delete_user_verify', 'Please type "'.$this->user_data['user_name'].'" to confirm.', '', ['required'=>true]);
            $modal .= form_button('delete_user_confirm', 'Proceed and delete user', 'del', ['class'=>'btn-danger btn-block', 'deactivate'=>true]);
            $modal .= closeform();
            $modal .= closemodal();
            add_to_footer($modal);
        }
         */

    }

}
