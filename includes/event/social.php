<?php

// The handler name is called Social_Event.

class Social_Event extends \PHPFusion\Event {

    public function __construct() {
        $this->set_EventName('follow');
    }

    public function handle_event() {
        // find people who followed me and notify me.
        $follow_me_query = "SELECT * FROM ".DB_SOCIAL_FOLLOW." WHERE follow_user=:my_id AND follow_datestamp >:last_event GROUP BY user_id";
        $follow_me_param = [
            ':my_id' => $this->get_UserID(),
            ':last_event' => $this->get_EventTime('follow'),
        ];
        $result = dbquery($follow_me_query, $follow_me_param);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $user = fusion_get_user($data['user_id']);
                $this->addNotice(fusion_get_userdata('user_id'), $data['user_id'], $user['user_name'].' has started to follow you', 'follow', $data['follow_datestamp']);
            }
        }

        // what else? liked.
    }

}