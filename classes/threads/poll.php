<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads/poll.php
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
 *
 * @package PHPFusion\Forums\Threads
 */
class Poll {

    /**
     * Permissions for Forum Poll
     *
     * @var array
     */
    private static $permissions = [];

    /**
     * Poll data
     *
     * @var array
     */
    private static $data = [];

    /**
     * @var array
     * openform         the oepn form tag and request url action link
     * closeform        the closing form tag
     * edit_button      ['title'] title of the button ['link'] edit url of the button
     * delete_button    same as above but for delete action
     * post_button      the post button
     * content          ['value'] - the option choice count, ['title'] - the title of the poll option, ['output'] - the
     * field/value
     */
    private static $poll_info = [
        'openform'      => '',
        'closeform'     => '',
        'edit_button'   => '',
        'delete_button' => '',
        'post_button'   => '',
        'content'       => [],
    ];

    /**
     * Forum Poll Locale
     *
     * @var array
     */
    private static $locale = [];

    /**
     * Object
     *
     * @param array $thread_info
     */
    public function __construct(array $thread_info) {
        self::$locale = fusion_get_locale('', FORUM_LOCALE);
        self::set_poll_permissions($thread_info['permissions']);
        self::set_thread_data($thread_info['thread']);
    }

    /**
     * Set Permissions Settings
     *
     * @param array $thread_info
     */
    private static function set_poll_permissions(array $thread_info) {
        self::$permissions = $thread_info;
    }

    /**
     * @param array $thread_data
     */
    private static function set_thread_data(array $thread_data) {
        self::$data = $thread_data;
    }

    /**
     * Displays HTML Poll Form
     *
     * @param bool $edit
     *
     * @template display_forum_pollform
     * @throws \Exception
     */
    public static function render_poll_form($edit = FALSE) {

        $access_admin = $edit ? self::get_poll_permissions("can_edit_poll") : self::get_poll_permissions(
            "can_create_poll"
        );

        if ($access_admin === TRUE) { // if permitted to create new poll.

            $locale = self::$locale;

            $poll_field = [];

            $poll_data = [
                'thread_id'         => self::$data['thread_id'],
                'forum_poll_title'  => isset($_POST['forum_poll_title']) ? form_sanitizer(
                    $_POST['forum_poll_title'], '', 'forum_poll_title'
                ) : '',
                'forum_poll_start'  => time(), // time poll started
                'forum_poll_length' => 2, // how many poll options we have
                'forum_poll_votes'  => 0, // how many vote this poll has
            ];

            // counter of lengths
            $option_data[1] = '';
            $option_data[2] = '';
            // calculate poll lengths
            if (isset($_POST['poll_options'])) {
                // callback on post.
                foreach ($_POST['poll_options'] as $i => $value) {
                    $option_data[$i] = form_sanitizer($value, '', "poll_options[$i]");
                }
                // reindex the whole array with blank values.
                if (\defender::safe()) {
                    $option_data = array_values(array_filter($option_data));
                    array_unshift($option_data, NULL);
                    unset($option_data[0]);
                    $poll_data['forum_poll_length'] = count($option_data);
                }
            }
            // add a Blank Poll option
            if (isset($_POST['add_poll_option']) && \defender::safe()) {
                array_push($option_data, '');
            }

            if ($edit === TRUE) {

                $result = dbquery(
                    "SELECT * FROM ".DB_FORUM_POLLS." WHERE thread_id='".self::$data['thread_id']."'"
                );
                if (dbrows($result) > 0) {
                    if (isset($_POST['update_poll']) || isset($_POST['add_poll_option'])) {
                        $load = FALSE;
                        $poll_data += dbarray($result); // append if not available.
                    } else {
                        $load = TRUE;
                        $poll_data = dbarray($result); // call
                    }
                    if (isset($_POST['update_poll'])) {
                        $poll_data = [
                            'thread_id'         => self::$data['thread_id'],
                            'forum_poll_title'  => form_sanitizer($_POST['forum_poll_title'], '', 'forum_poll_title'),
                            'forum_poll_start'  => $poll_data['forum_poll_start'], // time poll started
                            'forum_poll_length' => $poll_data['forum_poll_length'], // how many poll options we have
                        ];
                        dbquery_insert(
                            DB_FORUM_POLLS, $poll_data, 'update', ['primary_key' => 'thread_id', 'no_unique' => TRUE]
                        );
                        $i = 1;
                        // populate data for matches
                        $poll_result = dbquery(
                            "SELECT forum_poll_option_id FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".self::$data['thread_id']."'"
                        );
                        while ($_data = dbarray($poll_result)) {
                            $_poll[$_data['forum_poll_option_id']] = $_data;
                            // Prune the emptied fields AND field is not required.
                            if (empty($option_data[$_data['forum_poll_option_id']]) && \defender::safe()) {
                                dbquery(
                                    "DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".self::$data['thread_id']."' AND forum_poll_option_id='".$_data['forum_poll_option_id']."'"
                                );
                            }
                        }
                        foreach ($option_data as $option_text) {
                            if ($option_text) {

                                if (\defender::safe()) {
                                    if (isset($_poll[$i])) { // has record
                                        dbquery(
                                            "UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_text='".$option_text."' WHERE thread_id='".self::$data['thread_id']."' AND forum_poll_option_id='".$i."'"
                                        );
                                    } else { // no record - create
                                        $array = [
                                            'thread_id'               => self::$data['thread_id'],
                                            'forum_poll_option_id'    => $i,
                                            'forum_poll_option_text'  => $option_text,
                                            'forum_poll_option_votes' => 0,
                                        ];
                                        dbquery_insert(DB_FORUM_POLL_OPTIONS, $array, 'save');
                                    }
                                }
                                $i++;
                            }
                        }
                        if (\defender::safe()) {
                            redirect(fusion_get_settings('siteurl')."infusions/forum/postify.php?post=editpoll&error=0&forum_id=".self::$data['forum_id']."&thread_id=".self::$data['thread_id']);
                        }
                    }
                    // how to make sure values containing options votes
                    $poll_field['openform'] = openform('pollform', 'post', FUSION_REQUEST);
                    $poll_field['openform'] .= "<div class='text-info m-b-20 m-t-10'>".str_replace(
                            '{REQUIRED}', '<span class=\'required\'>*</span>', $locale['forum_0613']
                        )."</div>\n";
                    $poll_field['poll_field'] = form_text(
                        'forum_poll_title', $locale['forum_0604'], $poll_data['forum_poll_title'],
                        [
                            'max_length'  => 255,
                            'placeholder' => $locale['forum_0604a'],
                            'inline'      => TRUE,
                            'required'    => TRUE
                        ]
                    );
                    if ($load == FALSE) {
                        for ($i = 1; $i <= count($option_data); $i++) {
                            $poll_field['poll_field'] .= form_text(
                                "poll_options[$i]", sprintf($locale['forum_0606'], $i), $option_data[$i],
                                [
                                    'max_length'  => 255,
                                    'placeholder' => $locale['forum_0605'],
                                    'inline'      => TRUE,
                                    'required'    => $i <= 2 ? TRUE : FALSE
                                ]
                            );
                        }
                    } else {
                        $poll_query = "
                        SELECT forum_poll_option_text, forum_poll_option_votes
                        FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".intval(self::$data['thread_id'])."' ORDER BY forum_poll_option_id ASC
                        ";
                        $result = dbquery($poll_query);
                        $i = 1;
                        while ($_pData = dbarray($result)) {
                            $poll_field['poll_field'] .= form_text(
                                "poll_options[$i]", $locale['forum_0605'].' '.$i, $_pData['forum_poll_option_text'],
                                [
                                    'max_length'  => 255,
                                    'placeholder' => 'Poll Options',
                                    'inline'      => 1,
                                    'required'    => $i <= 2 or $_pData['forum_poll_option_votes'] ? TRUE : FALSE
                                ]
                            );
                            $i++;
                        }
                    }
                    $poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
                    $poll_field['poll_field'] .= form_button(
                        'add_poll_option', $locale['forum_0608'], $locale['forum_0608'], ['class' => 'btn-default']
                    );
                    $poll_field['poll_field'] .= "</div>\n";
                    $poll_field['poll_button'] = form_button(
                        'update_poll', $locale['forum_2013'], $locale['forum_2013'], ['class' => 'btn-primary']
                    );
                    $poll_field['closeform'] = closeform();
                } else {
                    redirect(FORUM."index.php"); // redirect because the poll id is not available.
                }
            } else {

                // Save New Poll
                if (isset($_POST['add_poll'])) {
                    dbquery_insert(DB_FORUM_POLLS, $poll_data, 'save');
                    $poll_data['forum_poll_id'] = dblastid();
                    $i = 1;
                    foreach ($option_data as $option_text) {
                        if ($option_text) {
                            $poll_data['forum_poll_option_id'] = $i;
                            $poll_data['forum_poll_option_text'] = $option_text;
                            $poll_data['forum_poll_option_votes'] = 0;
                            dbquery_insert(DB_FORUM_POLL_OPTIONS, $poll_data, 'save');
                            $i++;
                        }
                    }
                    if (\defender::safe()) {
                        dbquery(
                            "UPDATE ".DB_FORUM_THREADS." SET thread_poll='1' WHERE thread_id='".self::$data['thread_id']."'"
                        );
                        redirect(fusion_get_settings('siteurl')."infusions/forum/postify.php?post=newpoll&error=0&forum_id=".self::$data['forum_id']."&thread_id=".self::$data['thread_id']);
                    }

                }

                // blank poll - no poll on edit or new thread
                $poll_field['openform'] = openform('pollform', 'post', FUSION_REQUEST, ['class' => 'spacer-xs']);
                $poll_field['poll_field'] = form_text(
                    'forum_poll_title', $locale['forum_0604'], $poll_data['forum_poll_title'],
                    [
                        'max_length'  => 255,
                        'placeholder' => $locale['forum_0604a'],
                        'inline'      => TRUE,
                        'required'    => TRUE
                    ]
                );
                for ($i = 1; $i <= count($option_data); $i++) {
                    $poll_field['poll_field'] .= form_text(
                        "poll_options[$i]", sprintf($locale['forum_0606'], $i), $option_data[$i],
                        [
                            'max_length'  => 255,
                            'placeholder' => $locale['forum_0605'],
                            'inline'      => 1,
                            'required'    => $i <= 2 ? TRUE : FALSE
                        ]
                    );
                }
                $poll_field['poll_field'] .= "<div class='col-xs-12 col-sm-offset-3'>\n";
                $poll_field['poll_field'] .= form_button(
                    'add_poll_option', $locale['forum_0608'], $locale['forum_0608'], ['class' => 'btn-default']
                );
                $poll_field['poll_field'] .= "</div>\n";
                $poll_field['poll_button'] = form_button(
                    'add_poll', $locale['forum_2011'], $locale['forum_2011'], ['class' => 'btn-primary']
                );
                $poll_field['closeform'] = closeform();
            }

            $info = [
                'title'       => (isset($_GET['action']) && $_GET['action'] == 'editpoll') ? $locale['forum_0603'] : $locale['forum_0366'],
                'description' => $locale['forum_2000'].self::$data['thread_subject'],
                'field'       => $poll_field
            ];

            display_forum_pollform($info);

        } else {
            redirect(FORUM);
        }
    }

    /**
     * Fetches Permissions Settings
     *
     * @param $key
     *
     * @return bool
     */
    private static function get_poll_permissions($key) {
        return (isset(self::$permissions[$key])) ? self::$permissions[$key] : FALSE;
    }

    /**
     * HTML output for Poll form
     *
     * @return array
     */
    public static function get_poll_info() {
        return (array)self::$poll_info;
    }

    /**
     * Generate Poll HTML
     *
     * @param array $thread_data
     *
     * @return string
     */
    public function generate_poll(array $thread_data) {

        $locale = self::$locale;

        $html = '';

        if (self::get_poll_permissions("can_access") && $thread_data['thread_poll'] == TRUE) {

            $poll_query = "
            SELECT poll_opts.*, poll.forum_poll_title, poll.forum_poll_votes
            FROM ".DB_FORUM_POLL_OPTIONS." poll_opts
            INNER JOIN ".DB_FORUM_POLLS." poll using (thread_id)
            WHERE poll.thread_id=:thread_id ORDER BY forum_poll_option_id ASC
            ";
            $poll_param = [':thread_id' => intval($thread_data['thread_id'])];

            $poll_result = dbquery($poll_query, $poll_param);

            if (dbrows($poll_result) > 0) {
                $i = 0;

                while ($pdata = dbarray($poll_result)) {
                    if ($i == 0) {
                        $poll['forum_poll_title'] = $pdata['forum_poll_title'];
                        $poll['forum_poll_votes'] = $pdata['forum_poll_votes'];
                        $poll['forum_poll_max_options'] = dbrows($poll_result);
                    }
                    $poll['forum_poll_options'][$pdata['forum_poll_option_id']] = $pdata;
                    $i++;
                }

                if (!empty($poll)) {

                    $can_vote_poll = self::get_poll_permissions('can_vote_poll');
                    //var_dump($can_vote_poll);
                    $can_edit_poll = self::get_poll_permissions('can_edit_poll');
                    //var_dump($can_edit_poll);

                    self::$poll_info['poll_title'] = $poll['forum_poll_title'];

                    // SQL cast poll vote
                    if (isset($_POST['poll_option']) && isnum(
                            $_POST['poll_option']
                        ) && $_POST['poll_option'] <= $poll['forum_poll_max_options']) {
                        // Vote Poll
                        if ($can_vote_poll) {

                            $pollInput['poll_option_id'] = stripinput($_POST['poll_option']);

                            if (\defender::safe()) {

                                dbquery(
                                    "UPDATE ".DB_FORUM_POLL_OPTIONS." SET forum_poll_option_votes=forum_poll_option_votes+1 WHERE thread_id='".intval(
                                        $thread_data['thread_id']
                                    )."' AND forum_poll_option_id='".intval($pollInput['poll_option_id'])."'"
                                );

                                dbquery(
                                    "UPDATE ".DB_FORUM_POLLS." SET forum_poll_votes=forum_poll_votes+1 WHERE thread_id='".intval(
                                        $thread_data['thread_id']
                                    )."'"
                                );

                                dbquery(
                                    "INSERT INTO ".DB_FORUM_POLL_VOTERS." (thread_id, forum_vote_user_id, forum_vote_user_ip, forum_vote_user_ip_type) VALUES ('".$thread_data['thread_id']."', '".fusion_get_userdata(
                                        "user_id"
                                    )."', '".USER_IP."', '".USER_IP_TYPE."')"
                                );

                                addNotice('success', $locale['forum_0614']);

                                redirect(INFUSIONS."forum/viewthread.php?thread_id=".$thread_data['thread_id']);

                            } else {

                                addNotice('danger', $locale['forum_0617']);
                            }
                        }
                    }

                    if ($can_vote_poll) {

                        // skips flood interval settings
                        self::$poll_info['openform'] = openform(
                            'poll_vote_form', 'post', '', [
                                'token_time' => TIME - fusion_get_settings('flood_interval')
                            ]
                        );

                        self::$poll_info['post_button'] = form_button(
                            'vote', $locale['forum_2010'], 'vote', ['class' => 'btn btn-primary m-l-20']
                        );

                        self::$poll_info['closeform'] = closeform();

                    }

                    if ($can_edit_poll === TRUE) {

                        self::$poll_info['edit_button'] = [
                            'link'  => INFUSIONS."forum/viewthread.php?action=editpoll&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id'],
                            'title' => $locale['forum_0603']
                        ];
                        self::$poll_info['delete_button'] = [
                            'link'  => INFUSIONS."forum/viewthread.php?action=deletepoll&forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id'],
                            'title' => $locale['delete']
                        ];

                        $html .= "<div class='pull-right btn-group'>\n";

                        $html .= "<a class='btn btn-sm btn-default' href='".self::$poll_info['edit_button']['link']."'>".self::$poll_info['edit_button']['title']."</a>\n";

                        $html .= "<a class='btn btn-sm btn-default' href='".self::$poll_info['delete_button']['link']."' onclick='confirm('".$locale['forum_0616']."');'>".self::$poll_info['delete_button']['title']."</a>\n";

                        $html .= "</div>\n";
                    }

                    $html .= self::$poll_info['openform'];

                    $html .= "<h2 class='strong m-t-0 m-b-10'>".$locale['forum_0314'].": ".self::$poll_info['poll_title']."</h2>\n";

                    $html .= "<ul class='p-l-20 p-t-0 block'>\n";

                    if (!empty($poll['forum_poll_options'])) {

                        $i = 1;
                        self::$poll_info['poll_options'] = $poll['forum_poll_options'];

                        foreach ($poll['forum_poll_options'] as $poll_option) {
                            if (self::get_poll_permissions("can_vote_poll") == TRUE) {
                                self::$poll_info['content'][$i] = [
                                    'value'  => $i,
                                    'title'  => $poll_option['forum_poll_option_text'],
                                    'output' => "<input id='opt-".$i."' type='radio' name='poll_option' value='".$i."' class='m-r-15'>",
                                ];
                                $html .= "<li><label for='opt-".$i."'>".self::$poll_info['content'][$i]['output'].self::$poll_info['content'][$i]['title']."</span>\n</label></li>\n";
                            } else {
                                $option_votes = ($poll['forum_poll_votes'] ? number_format(
                                    100 / $poll['forum_poll_votes'] * $poll_option['forum_poll_option_votes']
                                ) : 0);
                                self::$poll_info['content'][$i] = [
                                    'value'  => $i,
                                    'title'  => $poll_option['forum_poll_option_text'],
                                    'output' => $option_votes,
                                ];

                                $html .= progress_bar(
                                    self::$poll_info['content'][$i]['output'], self::$poll_info['content'][$i]['title'].' ['.$poll_option['forum_poll_option_votes'].'/'.$poll['forum_poll_votes'].']',
                                    ['height' => '10px']
                                );
                            }
                            $i++;
                        }
                    }
                    $html .= "</ul>\n";

                    $html .= self::$poll_info['post_button'].self::$poll_info['closeform'];
                }
            }
        }

        return (string)$html;
    }

    /**
     * Executes poll deletion
     */
    public function delete_poll() {
        if (!empty(self::$data['thread_poll']) && $this->get_poll_permissions("can_edit_poll")) {
            if (\defender::safe()) {
                dbquery("DELETE FROM ".DB_FORUM_POLLS." WHERE thread_id='".self::$data['thread_id']."'");
                dbquery("DELETE FROM ".DB_FORUM_POLL_OPTIONS." WHERE thread_id='".self::$data['thread_id']."'");
                dbquery("DELETE FROM ".DB_FORUM_POLL_VOTERS." WHERE thread_id='".self::$data['thread_id']."'");
                dbquery(
                    "UPDATE ".DB_FORUM_THREADS." SET thread_poll='0' WHERE thread_id='".self::$data['thread_id']."'"
                );
                redirect(
                    INFUSIONS."forum/postify.php?post=deletepoll&amp;error=4&forum_id=".self::$data['forum_id']."&amp;thread_id=".self::$data['thread_id']
                );
            }
        }
        redirect(
            INFUSIONS."forum/viewthread.php?forum_id=".self::$data['forum_id']."&amp;thread_id=".self::$data['thread_id']
        );
    }

}
