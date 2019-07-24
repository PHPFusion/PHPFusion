<?php
/**
 * Logs an activity
 *
 * @param     $user_id
 * @param     $visibility
 * @param     $item_type
 * @param     $action
 * @param int $item_id
 * @param     $subject
 * @param     $content
 * @param     $date
 *
 * @return FALSE|int
 * @throws Exception
 */
function fusion_add_activity($user_id, $visibility, $item_type, $action, $item_id = 0, $subject, $content, $date) {
    /**
     * Documentation
     * Table column and data
     * action_id            auto
     * action_user_id       your id
     * action_visibility    -104 for personal, and user levels
     * action_item_type     activity, members, actions, update, admin
     * action_type          type to be paired by hook methods
     * action_item_id       id of the item if any
     * action_subject       title of the content
     * action_content       if there are content, place any text here
     * action_datestamp     time
     * action_spam          0 if not spam or 1 if reported spam
     */
    $data = [
        'action_id'         => 0,
        'action_user_id'    => $user_id,
        'action_visibility' => $visibility,
        'action_item_type'  => $item_type,
        'action_type'       => $action,
        'action_item_id'    => $item_id,
        'action_subject'    => stripinput($subject),
        'action_content'    => stripinput($content),
        'action_datestamp'  => $date
    ];
    //http://php-fusion.test/infusions/forum/viewthread.php?thread_id=38663

    if (!isnum($user_id)) {
        throw new \Exception('User ID is not an integer');
    }
    if (!isnum($visibility)) {
        throw new \Exception('Visibility is not an integer');
    }
    if (!isnum($item_id)) {
        throw new \Exception('Item id is not an integer');
    }

    return dbquery_insert(DB_USER_ACTIVITY, $data, 'save', ['keep_session' => TRUE]);
}

function fusion_get_activity_title($data) {
    return fusion_filter_current_hook('profile_activity_title', $data);
}

function fusion_get_activity_content($data) {
    return fusion_filter_current_hook('profile_activity_content', $data);
}