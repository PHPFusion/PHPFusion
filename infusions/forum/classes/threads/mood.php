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
     * @param array $post_data
     *
     * @return $this
     */
    public function setPostData($post_data) {
        $this->post_id = $post_data['post_id'];
        $this->post_author = $post_data['post_author'];
        return $this;
    }

    public function postMood() {
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

            if (fusion_safe()) {
                $mood_exists = (bool)dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id='".$notify_data['notify_mood_id']."'");
                $has_reacted = (bool)$this->moodExists($notify_data['notify_sender'], $notify_data['notify_mood_id'], $notify_data['post_id']);

                if ($mood_exists === TRUE && $has_reacted === FALSE) {
                    dbquery_insert(DB_POST_NOTIFY, $notify_data, 'save', ['primary_key' => 'post_id']);
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
                fusion_safe() &&
                // Mood exist check
                dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id='".$notify_data['notify_mood_id']."'") &&
                // Exists record check
                $this->moodExists($notify_data['notify_sender'], $notify_data['notify_mood_id'],
                    $notify_data['post_id'])
            ) {
                dbquery("DELETE FROM ".DB_POST_NOTIFY." WHERE post_id=".$notify_data['post_id']."
                AND notify_mood_id=".$notify_data['notify_mood_id']."
                AND notify_user=".$notify_data['notify_user']."
                AND notify_sender=".$notify_data['notify_sender']);
                $response = TRUE;
            }
        }

        return $response;
    }

    public static function moodExists($sender_id, $mood_id, $post_id) {
        return dbcount('(notify_user)', DB_POST_NOTIFY,
            "notify_sender='".$sender_id."'
                         AND notify_mood_id='".$mood_id."'
                         AND post_id='$post_id'");
    }

    public function getMoodMessage() {
        $mood_users = [];
        $mood_users_count = [];
        $moods = [];

        $mood_cache = $this->cacheMood(FALSE);

        // Get the types of buttons
        $response_query = "SELECT pn.* FROM ".DB_POST_NOTIFY." pn WHERE post_id='".$this->post_id."' ORDER BY pn.notify_mood_id ASC, pn.post_id ASC";
        $response_result = dbquery($response_query);

        if (dbrows($response_result) > 0) {
            while ($m_data = dbarray($response_result)) {
                if ($user = fusion_get_user($m_data['notify_sender'])) {
                    $m_data['profile_link'] = profile_link($user['user_id'], $user['user_name'], $user['user_status'], 'mood_sender');
                }

                $mood_users[] = $m_data;
                $mood_users_count[$user['user_id']] = $m_data;
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

            $moods['users_count'] = count($mood_users_count);
            return $moods;
        }

        return NULL;
    }

    public function cacheMood($access = TRUE) {
        $mood_cache = [];
        $cache_result = dbquery("SELECT * FROM ".DB_FORUM_MOODS." WHERE ".($access ? groupaccess('mood_access').' AND' : '')." mood_status=1");
        if (dbrows($cache_result) > 0) {
            while ($data = dbarray($cache_result)) {
                $data['mood_name'] = QuantumFields::parseLabel($data['mood_name']);
                $data['mood_description'] = QuantumFields::parseLabel($data['mood_description']);
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
    public function displayMoodButtons() {
        $mood_cache = $this->cacheMood();
        $html = '';
        $my_id = fusion_get_userdata('user_id');
        if (!empty($mood_cache)) {

            $html .= openform('mood_form-'.$this->post_id, 'post', FUSION_REQUEST."#post_".$this->post_id);

            foreach ($mood_cache as $mood_id => $mood_data) {
                //jQuery data model for ajax
                $html .= form_hidden('post_author', '', $this->post_author, ['input_id' => 'post_author'.$mood_id.$this->post_id]);
                $html .= form_hidden('post_id', '', $this->post_id, ['input_id' => 'post_id'.$mood_id.$this->post_id]);

                if (!$this->moodExists($my_id, $mood_id, $this->post_id)) {
                    // Post Button
                    $html .=
                        "<button name='post_mood' id='".$this->post_id."-$mood_id' class='btn btn-sm btn-default m-r-5' data-mood='$mood_id' data-post='$this->post_id' value='".$mood_id."'>".
                        (!empty($mood_data['mood_icon']) ? "<i class='".$mood_data['mood_icon']."'></i> " : "").
                        QuantumFields::parseLabel($mood_data['mood_name']).
                        "</button>";
                } else {
                    // Unpost Button
                    $html .=
                        "<button name='unpost_mood' id='".$this->post_id."-$mood_id' class='btn btn-sm btn-default active m-r-5' data-mood='$mood_id' data-post='$this->post_id' value='".$mood_id."'>".
                        (!empty($mood_data['mood_icon']) ? "<i class='".$mood_data['mood_icon']."'></i> " : "").
                        QuantumFields::parseLabel($mood_data['mood_name']).
                        "</button>";
                }
            }
            $html .= closeform();
        }

        return $html;
    }
}
