<?php
namespace PHPFusion\Infusions\Forum\Classes\Threads;

class ThreadLogs {

    private $thread_id = 0;
    private $post_id = 0;
    private $user_id = 0;
    private $locale = [];

    public function __construct($thread_id, $post_id = 0) {
        $this->thread_id = $thread_id;
        $this->post_id = $post_id;
        $this->locale = fusion_get_locale();
        $this->user_id = fusion_get_userdata('user_id');
    }

    private function getLogActions($key = NULL, $set = FALSE) {
        $arr = [
            1 => 'add',
            2 => 'remove',
            3 => 'change',
        ];

        if ($set === TRUE) {
            $arr = array_flip($arr);
            if (!isset($arr[$key])) {
                // Debugging purposes, no need to translate.
                throw new \Exception('Item type is invalid');
            }
        }

        return $key === NULL ? $arr : (isset($arr[$key]) ? $arr[$key] : NULL);
    }

    private function getLogItemType($key = NULL, $set = FALSE) {
        // keep adding the list
        $arr = [
            1 => 'subject',
            2 => 'tags',
            3 => 'content',
            4 => 'lock',
            5 => 'sticky',
        ];

        if ($set === TRUE) {
            $arr = array_flip($arr);

            if (!isset($arr[$key])) {
                // Debugging purposes, no need to translate.
                throw new \Exception('Item type is invalid');
            }
        }

        return $key === NULL ? $arr : (isset($arr[$key]) ? $arr[$key] : NULL);
    }

    private function getLogVisibility() {
        // make a new settings for this to hide iMOD actions. for now, all moderator actions are hidden, and can only be shown in moderator logs.
        // we will need a forum_mods
        if (defined('iMOD') or iADMIN) {
            return 1;
        }
        return 0;
    }

    /**
     * Logs a change in the thread
     *
     * @param $item_type
     * @param $old
     * @param $new
     *
     * @return bool|FALSE|int
     * @throws \Exception
     */
    public function doLogAction($item_type, $old, $new) {

        if ($old !== $new) {

            $log_action = 'change';
            if (empty($old)) {
                $log_action = 'add';
            } elseif (empty($new)) {
                $log_action = 'remove';
            }

            $logdata = [
                'thread_id'             => $this->thread_id,
                'post_id'               => $this->post_id,
                'thread_log_action'     => $this->getLogActions($log_action),
                'thread_log_item_type'  => $this->getLogItemType($item_type, TRUE),
                'thread_log_new_value'  => $new,
                'thread_log_old_value'  => $old,
                'thread_log_user'       => $this->user_id,
                'thread_log_visibility' => $this->getLogVisibility(),
                'thread_log_datestamp'  => TIME,
            ];

            return dbquery_insert(DB_FORUM_THREAD_LOGS, $logdata, 'save', ['keep_session' => TRUE]);
        }

        return FALSE;
    }

}
