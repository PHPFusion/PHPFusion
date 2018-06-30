<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: threads/mood.php
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

    private static $mood_cache = [];
    public $info = [];
    private $post_data = [];
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
        $this->post_data = $post_data;
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

        $last_datestamp = [];
        $mood_description = [];
        $mood_user = [];
        $mood_icon = [];

        $mood_cache = $this->cache_mood();

        // Get the types of buttons
        $response_query = "SELECT pn.* FROM ".DB_POST_NOTIFY." pn WHERE post_id='".$this->post_id."' ORDER BY pn.notify_mood_id ASC, pn.post_id ASC";

        $response_result = dbquery($response_query);

        if (dbrows($response_result) > 0) {

            while ($m_data = dbarray($response_result)) {
                $icon = isset($mood_cache[$m_data['notify_mood_id']]['mood_icon']) ? $mood_cache[$m_data['notify_mood_id']]['mood_icon'] : "fa fa-question fa-fw";
                $mood_icon[$m_data['notify_mood_id']] = "<i class='$icon'></i>";
                $description = isset($mood_cache[$m_data['notify_mood_id']]['mood_description']) ? $mood_cache[$m_data['notify_mood_id']]['mood_description'] : $locale['forum_0529'];
                $mood_description[$m_data['notify_mood_id']] = $description;
                if ($user = fusion_get_user($m_data['notify_sender'])) {
                    $user_list[$m_data['notify_mood_id']][$user['user_id']] = profile_link($user['user_id'], $user['user_name'], $user['user_status'], 'mood_sender');
                }
                $last_datestamp[$m_data['notify_mood_id']] = $m_data['notify_datestamp'];
            }

            $my_id = fusion_get_userdata('user_id');
            if (!empty($user_list)) {
                foreach ($user_list as $mood_id => $short_list) {
                    if (isset($short_list[$my_id])) {
                        unset($short_list[$my_id]);
                        $short_list[0] = $locale['you'];
                    }
                    if (count($short_list) > 3) {
                        $count = (count($short_list) - 3)." ".$locale['forum_0530'];
                        $short_list = array_slice($short_list, 0, 3);
                        $short_list[] = $count;
                    }
                    ksort($short_list);
                    $mood_user[$mood_id] = implode(', ', $short_list);
                }
            }


            $output_message = "";
            foreach ($mood_description as $mood_id => $mood_output) {
                $senders = $mood_user[$mood_id];
                $output_message .= sprintf(
                        $locale['forum_0528'],
                        $mood_icon[$mood_id],
                        $senders,
                        $mood_output,
                        timer($last_datestamp[$mood_id]))."
                        <br/>";
            }

            return (string)$output_message;
        }
    }

    public static function cache_mood() {
        if (empty(self::$mood_cache)) {
            $cache_query = "SELECT * FROM ".DB_FORUM_MOODS." m WHERE ".groupaccess('mood_access')." AND mood_status=1";
            $cache_result = dbquery($cache_query);
            if (dbrows($cache_result) > 0) {
                while ($data = dbarray($cache_result)) {
                    $data['mood_name'] = QuantumFields::parse_label($data['mood_name']);
                    $data['mood_description'] = QuantumFields::parse_label($data['mood_description']);
                    self::$mood_cache[$data['mood_id']] = $data;
                }
            }
        }

        return self::$mood_cache;
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
                $html .= form_hidden('post_author', '', $this->post_author);
                $html .= form_hidden('post_id', '', $this->post_id);

                if (!$this->mood_exists($my_id, $mood_id, $this->post_id)) {
                    // Post Button
                    $html .=
                        "<button name='post_mood' id='".$this->post_id."-$mood_id' class='btn btn-sm btn-default m-r-5' data-mood='$mood_id' data-post='$this->post_id' value='".$mood_id."'>".
                        (!empty($mood_data['mood_icon']) ? "<i class='".$mood_data['mood_icon']."'></i>" : "").
                        QuantumFields::parse_label($mood_data['mood_name']).
                        "</button>";
                } else {
                    // Unpost Button
                    $html .=
                        "<button name='unpost_mood' id='".$this->post_id."-$mood_id' class='btn btn-sm btn-default active m-r-5' data-mood='$mood_id' data-post='$this->post_id' value='".$mood_id."'>".
                        (!empty($mood_data['mood_icon']) ? "<i class='".$mood_data['mood_icon']."'></i>" : "").
                        QuantumFields::parse_label($mood_data['mood_name']).
                        "</button>";
                }
            }
            $html .= closeform();
        }

        return (string)$html;
    }

}
