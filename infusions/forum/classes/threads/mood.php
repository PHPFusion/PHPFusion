<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: mood.php
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
use PHPFusion\QuantumFields;

/**
 * Class ForumMood
 *
 * @package PHPFusion\Forums\Threads
 */
class Forum_Mood extends ForumServer {
    public $info = [];
    private $post_id = 0;
    private $post_author = 0;

    /**
     * Used as post
     *
     * @param $post_data
     *
     * @return $this
     */
    public function set_PostData($post_data) {
        $this->post_id = $post_data['post_id'];
        $this->post_author = $post_data['post_author'];
        return $this;
    }

    public function post_mood() {
        $response = FALSE;

        // this is general single static output
        if (isset($_POST['post_mood']) && isnum($_POST['post_mood'])) {
            // if is a valid mood
            // insert into post notify
            $notify_data = [
                'post_id'          => form_sanitizer($_POST['post_id'], 0, 'post_id'),
                'notify_mood_id'   => intval($_POST['post_mood']),
                'notify_datestamp' => time(),
                'notify_user'      => form_sanitizer($_POST['post_author'], 0, 'post_author'),
                'notify_sender'    => fusion_get_userdata('user_id'),
                'notify_status'    => 1,
            ];

            if (\defender::safe()) {

                $mood_exists = dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id='".$notify_data['notify_mood_id']."'") ? TRUE : FALSE;

                $has_reacted = $this->mood_exists($notify_data['notify_sender'], $notify_data['notify_mood_id'],
                    $notify_data['post_id']) ? TRUE : FALSE;

                if ($mood_exists === TRUE && $has_reacted === FALSE) {
                    dbquery_insert(DB_POST_NOTIFY, $notify_data, 'save');
                    $response = TRUE;
                }
            }

        } else if (isset($_POST['unpost_mood']) && isnum($_POST['unpost_mood'])) {
            // if is a valid mood
            // insert into post notify
            $notify_data = [
                'post_id'        => form_sanitizer($_POST['post_id'], 0, 'post_id'),
                'notify_mood_id' => intval($_POST['unpost_mood']),
                'notify_user'    => form_sanitizer($_POST['post_author'], 0, 'post_author'),
                'notify_sender'  => fusion_get_userdata('user_id'),
            ];

            if (
                \defender::safe() &&
                // Mood exist check
                dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id='".$notify_data['notify_mood_id']."'") &&
                // Exists record check
                $this->mood_exists($notify_data['notify_sender'], $notify_data['notify_mood_id'],
                    $notify_data['post_id'])
            ) {
                dbquery("DELETE FROM ".DB_POST_NOTIFY." WHERE post_id=".$notify_data['post_id']."
                AND notify_mood_id=".$notify_data['notify_mood_id']."
                AND notify_user=".$notify_data['notify_user']."
                AND notify_sender=".$notify_data['notify_sender']);
                $response = TRUE;
            }
        }

        return (boolean)$response;
    }

    public static function mood_exists($sender_id, $mood_id, $post_id) {
        return dbcount('(notify_user)', DB_POST_NOTIFY,
            "notify_sender='".$sender_id."'
                         AND notify_mood_id='".$mood_id."'
                         AND post_id='$post_id'");
    }

    public function get_mood_message() {

        // whether any user has reacted to this post
        $locale = fusion_get_locale("", [FORUM_ADMIN_LOCALE, FORUM_LOCALE]);

        $mood_users = [];
        $moods = [];

        $mood_cache = $this->cache_mood();

        // Get the types of buttons
        $response_query = "SELECT pn.* FROM ".DB_POST_NOTIFY." pn WHERE post_id='".$this->post_id."' ORDER BY pn.notify_mood_id ASC, pn.post_id ASC";
        $response_result = dbquery($response_query);

        if (dbrows($response_result) > 0) {
            while ($m_data = dbarray($response_result)) {
                if ($user = fusion_get_user($m_data['notify_sender'])) {
                    $m_data['profile_link'] = profile_link($user['user_id'], $user['user_name'], $user['user_status'], 'mood_sender');
                }

                $mood_users[] = $m_data;
            }

            foreach ($mood_cache as $id => $data) {
                $mood = [];
                foreach ($mood_users as $data2) {
                    if ($id == $data2['notify_mood_id']) {
                        $mood[] = $data2;
                    }
                }

                $moods[$id] = $data;
                $moods[$id]['users'] = $mood;
            }

            $users = '';
            foreach ($moods as $id => $data) {
                if (!empty($data['users'])) {
                    $users .= '<div class="mood_users" title="'.$data['mood_name'].'">';
                        $users .= '<i class="'.$data['mood_icon'].' fa-fw"></i> ';
                        $users .= implode(', ', array_map(function ($user) { return $user['profile_link']; }, $data['users']));
                    $users .= '</div>';
                }
            }

            $count = format_word(count($mood_users), $locale['fmt_user']);
            $output_message = '<div class="forum-mood">';
            $output_message .= '<a data-toggle="collapse" aria-expanded="false" aria-controls="#moods'.$this->post_id.'" href="#moods'.$this->post_id.'">'.$count.' '.$locale['forum_0528'].' <span class="caret"></span></a>';
            $output_message .= '<div id="moods'.$this->post_id.'" class="moods collapse">'.$users.'</div>';
            $output_message .= '</div>';

            return (string)$output_message;
        }

        return NULL;
    }

    public function cache_mood() {
        $mood_cache = [];
        $cache_result = dbquery("SELECT * FROM ".DB_FORUM_MOODS." WHERE ".groupaccess('mood_access')." AND mood_status=1");
        if (dbrows($cache_result) > 0) {
            while ($data = dbarray($cache_result)) {
                $data['mood_name'] = QuantumFields::parse_label($data['mood_name']);
                $data['mood_description'] = QuantumFields::parse_label($data['mood_description']);
                $data['mood_icon'] = !empty($data['mood_icon']) ? $data['mood_icon'] : 'fa fa-question';
                $mood_cache[$data['mood_id']] = $data;
            }
        }

        return $mood_cache;
    }

    /**
     * Mood should be present.
     * Static calls for caching, so only single query
     * Label parsing
     *
     * @return string
     */

    public function display_mood_buttons() {
        $mood_cache = $this->cache_mood();
        $html = '';
        $my_id = fusion_get_userdata('user_id');
        if (!empty($mood_cache)) {

            $html .= openform('mood_form-'.$this->post_id, 'post', FUSION_REQUEST."#post_".$this->post_id);

            foreach ($mood_cache as $mood_id => $mood_data) {
                //jQuery data model for ajax
                $html .= form_hidden('post_author', '', $this->post_author, ['input_id' => 'post_author'.$mood_id.$this->post_id]);
                $html .= form_hidden('post_id', '', $this->post_id, ['input_id' => 'post_id'.$mood_id.$this->post_id]);

                if (!$this->mood_exists($my_id, $mood_id, $this->post_id)) {
                    // Post Button
                    $html .=
                        "<button name='post_mood' id='".$this->post_id."-$mood_id' class='btn btn-sm btn-default m-r-5' data-mood='$mood_id' data-post='$this->post_id' value='".$mood_id."'>".
                        (!empty($mood_data['mood_icon']) ? "<i class='".$mood_data['mood_icon']."'></i> " : "").
                        QuantumFields::parse_label($mood_data['mood_name']).
                        "</button>";
                } else {
                    // Unpost Button
                    $html .=
                        "<button name='unpost_mood' id='".$this->post_id."-$mood_id' class='btn btn-sm btn-default active m-r-5' data-mood='$mood_id' data-post='$this->post_id' value='".$mood_id."'>".
                        (!empty($mood_data['mood_icon']) ? "<i class='".$mood_data['mood_icon']."'></i> " : "").
                        QuantumFields::parse_label($mood_data['mood_name']).
                        "</button>";
                }
            }
            $html .= closeform();
        }

        return (string)$html;
    }

}
