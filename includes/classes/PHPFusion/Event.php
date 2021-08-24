<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Event.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

/**
 * Class DomainEvent
 * The purpose of this class is to enable domain level event management
 * such as notifications and user level cron to run.
 *
 * @package PHPFusion\Event
 */
class Event {

    private static $event_instance = NULL;
    private static $handler;
    private $event_name;
    private $notices = [];

    private static $event_required = FALSE;
    protected static $event_time = [];
    private static $user_id = 0;
    private static $threshold_minute = 30;

    // This function executes a handler action. For hardcoded method.
    public function getEventClass($event_name) {
        if (isset(self::$handler[$event_name])) {
            return self::$handler[$event_name];
        }

        return NULL;
    }

    public function setEventName($event_name) {
        $this->event_name = $event_name;
    }

    /**
     * Returns the latest time you ever cached into your event records of a specific event handler type.
     *
     * @param $event_name
     *
     * @return int - unix timestamp
     */
    public static function getEventTime($event_name) {
        return isset(self::$event_time[$event_name]) ? (int)self::$event_time[$event_name] : 0;
    }

    /**
     * Singleton runtime instance
     *
     * @return null|static
     */
    public static function getInstance() {
        if (self::$event_instance === NULL && iMEMBER) {
            $user_id = fusion_get_userdata('user_id');
            self::$event_instance = new static();
            self::$event_instance->setUserId($user_id);
            self::$event_instance->cacheNotices();
            self::$event_instance->handleGlobalEvent();
        }

        return self::$event_instance;
    }

    public static function setUserId($user_id) {
        self::$user_id = $user_id;
    }

    protected static function getUserId() {
        return self::$user_id;
    }

    protected function handleGlobalEvent() {
        if (iMEMBER) {
            $event_files = makefilelist(INCLUDES.'event/', '.|..|index.php', TRUE);
            if (!empty($event_files)) {
                foreach ($event_files as $module) {
                    include INCLUDES.'event/'.$module;
                    $obj_name = ucfirst(str_replace('.php', '', $module)).'_Event';
                    $obj_ = new \ReflectionClass($obj_name);
                    $class = $obj_->newInstance(); // this will run a set event name.
                    $this->event_name = $class->event_name;
                    self::$handler[$this->event_name] = $class; // from here event name is accessible already
                    if (self::isEventRequired() == TRUE) { // we check if we need to fetch or not
                        if (method_exists($class, 'handle_event')) {
                            $class->handle_event();
                        }
                    }
                }
            }
        }
    }

    /**
     * Validates if a trigger is needed**
     *
     * @return bool - true will trigger the event.
     */
    public function isEventRequired() {
        // the first array keys.
        if (isset(self::$event_required[$this->event_name]) and self::$event_required[$this->event_name] === TRUE) { // this will be true if my last event already expired. but the last state of the triggered actions already in.
            return TRUE;
            // now I check for the tables and fetch again the unique ones. I need to compare and see if that is already fetched.
        } else if (!dbcount('(notice_id)', DB_USER_NOTIFY, 'notice_event=:event_name AND notice_to=:my_id AND notice_datestamp > :expiry_time',
            [
                ':expiry_time' => time() - (60 * self::$threshold_minute),
                ':my_id'       => self::getUserId(),
                ':event_name'  => $this->event_name,
            ])
        ) {
            return TRUE;
        }

        return FALSE;
    }

    // This will log event and notice.
    public function addNotice($to, $from, $message, $event_type, $time) {
        $insertArray = [
            'notice_to'        => $to,
            'notice_from'      => $from,
            'notice_message'   => $message,
            'notice_event'     => $event_type,
            'notice_timestamp' => time(),
            'notice_datestamp' => $time,
            'notice_read'      => 0,
        ];
        dbquery_insert(DB_USER_NOTIFY, $insertArray, 'save');
    }

    /**
     * Cache the last 15 notices of all types of event.
     *
     * @return array
     */
    private function cacheNotices() {
        // the rest you need to fetch manually.
        if (empty($this->notices[self::getUserId()])) {
            $notice_query = "SELECT * FROM ".DB_USER_NOTIFY." WHERE notice_to=:user_id AND notice_read=:read_status ORDER BY notice_datestamp DESC LIMIT 0,15";
            $notice_param = [
                ':user_id'     => self::getUserId(),
                ':read_status' => 0,
            ];
            $result = dbquery($notice_query, $notice_param);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    // the first one
                    $this->notices[self::getUserId()][$data['notice_event']][] = $data;

                    // Cache the latest event timer updated
                    if (!isset(self::$event_time[$data['notice_event']])) {
                        self::$event_time[$data['notice_event']] = $data['notice_timestamp'];
                    }
                    // Marked for update because already expired.
                    if ($data['notice_timestamp'] < (self::$threshold_minute * 60)) {
                        self::$event_required[$data['notice_event']] = TRUE;
                    }
                }
            } else {
                $this->notices[self::getUserId()] = [];
            }
        }

        return (array)$this->notices[self::getUserId()];
    }

    /*
     * Output Implementation Method,
     */
    public function renderNotice() {
        $sub_html = self::noNoticeTemplate();
        if (!empty($this->notices[self::getUserId()])) {
            $sub_html = '';
            foreach ($this->notices[self::getUserId()] as $nData) {
                foreach ($nData as $notice) {
                    $user = fusion_get_user($notice['notice_from']);
                    $sub_html .= strtr(self::childNoticeTemplate($notice), [
                        '{%avatar%}'       => display_avatar($user, '50px', '', TRUE, ''),
                        '{%profile_name%}' => profile_link($user['user_id'], $user['user_name'], $user['user_status']),
                        '{%message%}'      => ucfirst($notice['notice_message']),
                        '{%datetime%}'     => timer($notice['notice_datestamp'])
                    ]);
                }
            }
        }

        return strtr(self::parentNoticeTemplate($this->notices[self::getUserId()]), [
            '{%child_items%}' => $sub_html
        ]);
    }

    public static function parentNoticeTemplate($info) {
        return "<ul class='block'>{%child_items%}</ul>";
    }

    public static function childNoticeTemplate($info) {
        return "<li>
            <div class='clearfix'>
                <div class='pull-left m-r-10'>{%avatar%}</div>
                <div class='overflow-hide'>
                    <span class='notice-profile'>{%profile_name%}</span>
                    <span class='notice-time'>{%datetime%}</span>
                    <span class='notice-message'>{%message%}</span>
                </div>
            </div>
        </li>
        ";
    }

    public function noNoticeTemplate() {
        return "<li><div class='text-center'>There are no notice presently</div></li>";
    }
}
