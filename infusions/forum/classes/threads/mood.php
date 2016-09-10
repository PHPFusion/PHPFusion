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
 * @package PHPFusion\Forums\Threads
 */
class ForumMood extends ForumServer {

    private static $mood_cache = array();
    public $info = array();
    private $post_data = array();
    private $post_id = 0;
    private $post_author = 0;

    /**
     * Used as post
     * @param $post_data
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
            $notify_data = array(
                'post_id' => form_sanitizer($_POST['post_id'], 0, 'post_id'),
                'notify_mood_id' => intval($_POST['post_mood']),
                'notify_datestamp' => time(),
                'notify_user' => form_sanitizer($_POST['post_author'], 0, 'post_author'),
                'notify_sender' => fusion_get_userdata('user_id'),
                'notify_status' => 1,
            );

            if (
                \defender::safe() &&
                // Mood exist check
                dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id='".$notify_data['notify_mood_id']."'") &&
                // No duplicate check
                !$this->mood_exists($notify_data['notify_sender'], $notify_data['notify_mood_id'],
                                    $notify_data['post_id'])
            ) {

                dbquery_insert(DB_POST_NOTIFY, $notify_data, 'save');
                $response = TRUE;
            }
        } elseif (isset($_POST['unpost_mood']) && isnum($_POST['unpost_mood'])) {
            // if is a valid mood
            // insert into post notify
            $notify_data = array(
                'post_id' => form_sanitizer($_POST['post_id'], 0, 'post_id'),
                'notify_mood_id' => intval($_POST['unpost_mood']),
                'notify_user' => form_sanitizer($_POST['post_author'], 0, 'post_author'),
                'notify_sender' => fusion_get_userdata('user_id'),
            );

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
        $locale = fusion_get_locale("", FORUM_ADMIN_LOCALE);
        $locale += fusion_get_locale("", FORUM_LOCALE);

        $last_datestamp = array();
        $mood_description = array();

        $mood_cache = $this->cache_mood();

        $response_query = "SELECT pn.*, u.user_id, u.user_name, u.user_avatar, u.user_status
        FROM ".DB_POST_NOTIFY." pn
        LEFT JOIN ".DB_USERS." u ON pn.notify_sender = u.user_id
        WHERE post_id='".$this->post_id."' GROUP BY pn.notify_mood_id ORDER BY pn.notify_mood_id ASC, pn.post_id ASC";

        $response_result = dbquery($response_query);

        if (dbrows($response_result)) {

            while ($m_data = dbarray($response_result)) {

                $user_output = "<a class='mood_sender' href='".FUSION_REQUEST."#post_".$this->post_id."'>\n".
                    profile_link($m_data['user_id'], $m_data['user_name'], $m_data['user_status'], "", FALSE).
                    "</a>";

                if (fusion_get_userdata('user_id') == $m_data['notify_sender']) {
                    $user_output = $locale['you'];
                }

                $reply_sender[$m_data['notify_mood_id']][] = $user_output;

                // The pairing errors are when `notify_mood_id` is illegally inserted or deleted
                // To code fallback on empty

                $last_datestamp[$m_data['notify_mood_id']] = $m_data['notify_datestamp'];

                $icon = isset($mood_cache[$m_data['notify_mood_id']]['mood_icon']) ?
                    $mood_cache[$m_data['notify_mood_id']]['mood_icon'] :
                    "fa fa-question fa-fw";

                $mood_icon[$m_data['notify_mood_id']] = "<i class='$icon'></i>";

                $description = isset($mood_cache[$m_data['notify_mood_id']]['mood_description']) ?
                    $mood_cache[$m_data['notify_mood_id']]['mood_description'] : $locale['forum_0529'];

                $mood_description[$m_data['notify_mood_id']] = $description;

            }

            $output_message = "";
            foreach ($mood_description as $mood_id => $mood_output) {

                $senders = implode(", ", $reply_sender[$mood_id]);
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

    private static function cache_mood() {
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
     * @return string
     */

    public function display_mood_buttons() {

        $html = '';
        if (!iMOD) {
            $html = openform('mood_form-'.$this->post_id, 'post', FUSION_REQUEST."#post_".$this->post_id);
        }

        $mood_cache = $this->cache_mood();

        if (!empty($mood_cache)) {
            foreach ($mood_cache as $mood_id => $mood_data) {
                //jQuery data model for ajax
                $html .= form_hidden('post_author', '', $this->post_author);
                $html .= form_hidden('post_id', '', $this->post_id);

                if (!$this->mood_exists($this->post_author, $mood_id, $this->post_id)) {
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
        }

        if (!iMOD) {
            $html .= closeform();
        }

        return (string)$html;
    }

}